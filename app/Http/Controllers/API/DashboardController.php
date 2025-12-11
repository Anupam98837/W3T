<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

class DashboardController extends Controller
{
    /* =========================
     | Utilities
     ========================== */

    private function now(): Carbon
    {
        return Carbon::now();
    }

    private function tableExists(string $table): bool
    {
        try {
            return Schema::hasTable($table);
        } catch (\Throwable $e) {
            return false;
        }
    }

    private function hasCol(string $table, string $column): bool
    {
        try {
            return Schema::hasColumn($table, $column);
        } catch (\Throwable $e) {
            return false;
        }
    }

    /**
     * Return first existing column name from candidates or null.
     */
    private function pickCol(string $table, array $candidates): ?string
    {
        foreach ($candidates as $c) {
            if ($this->hasCol($table, $c)) {
                return $c;
            }
        }
        return null;
    }

    /**
     * Helper for columns like quizz_id / quiz_id in batch_quizzes.
     * Returns whichever exists, or null.
     */
    private function batchQuizFk(): ?string
    {
        if (!$this->tableExists('batch_quizzes')) {
            return null;
        }
        // prefer quizz_id (old schema), fall back to quiz_id (new schema)
        return $this->pickCol('batch_quizzes', ['quizz_id', 'quiz_id']);
    }

    /**
     * add "alias.col as asName" if exists, else "NULL as asName" (DB::raw)
     */
    private function selectCandidate(string $alias, string $table, array $candidates, string $as)
    {
        $col = $this->pickCol($table, $candidates);
        return $col ? "$alias.$col as $as" : DB::raw("NULL as $as");
    }

    /** add "alias.col" if exists; returns string|null */
    private function selectIfExists(string $alias, string $table, string $col, ?string $as = null)
    {
        if (!$this->hasCol($table, $col)) {
            return null;
        }
        return $as ? "$alias.$col as $as" : "$alias.$col";
    }

    /** groupBy those expressions that are not null strings */
    private function groupBySome($q, array $exprs)
    {
        $g = array_values(array_filter($exprs, fn($s) => is_string($s) && trim($s) !== ''));
        if ($g) {
            $q->groupBy(...$g);
        }
        return $q;
    }

    /** order by alias.col if it exists else by alias.id */
    private function orderByIf($q, string $alias, string $table, string $col, string $dir = 'desc')
    {
        if ($this->hasCol($table, $col)) {
            $q->orderBy("$alias.$col", $dir);
        } else {
            $q->orderBy("$alias.id", $dir);
        }
        return $q;
    }

    /** clamp list size */
    private function lim(int $n, int $max = 50): int
    {
        return max(1, min($n, $max));
    }

    private function actor(Request $r): array
    {
        return [
            'role' => (string) $r->attributes->get('auth_role', ''),
            'id'   => (int) ($r->attributes->get('auth_tokenable_id') ?? 0),
            'uuid' => (string) $r->attributes->get('auth_user_uuid', ''),
        ];
    }

    /** Apply soft-delete guard if the table has deleted_at */
    private function whereNotDeleted($q, string $table, ?string $alias = null)
    {
        $a = $alias ?: $table;
        if ($this->hasCol($table, 'deleted_at')) {
            $q->whereNull("$a.deleted_at");
        }
        return $q;
    }

    private function ungradedSubmissionsBase()
    {
        // Caller always checks table existence before using this.
        $q = DB::table('assignment_submissions');
        $this->whereNotDeleted($q, 'assignment_submissions');

        if ($this->hasCol('assignment_submissions', 'status')) {
            $q->where('assignment_submissions.status', 'submitted');
        }
        if ($this->hasCol('assignment_submissions', 'marks')) {
            $q->whereNull('assignment_submissions.marks');
        } elseif ($this->hasCol('assignment_submissions', 'graded_at')) {
            $q->whereNull('assignment_submissions.graded_at');
        }

        return $q;
    }

    /** Safe select list with column-existence guards */
    private function safeSelect(string $table, array $cols): array
    {
        $out = [];
        foreach ($cols as $c) {
            if (stripos($c, ' as ') !== false) {
                // handle "table.col as alias"
                [$left, $alias] = preg_split('/\s+as\s+/i', $c);
                $p2   = explode('.', $left);
                $tbl2 = count($p2) > 1 ? $p2[0] : $table;
                $col2 = count($p2) > 1 ? $p2[1] : $left;
                if ($this->hasCol($tbl2, $col2)) {
                    $out[] = $c;
                }
            } else {
                $parts = explode('.', $c);
                $tbl   = count($parts) > 1 ? $parts[0] : $table;
                $col   = count($parts) > 1 ? $parts[1] : $c;
                if ($this->hasCol($tbl, $col)) {
                    $out[] = $c;
                }
            }
        }
        return $out ?: ['*'];
    }

    /* =========================
     | Public endpoints
     ========================== */

    public function summary(Request $r)
    {
        $me = $this->actor($r);

        try {
            switch ($me['role']) {
                case 'superadmin':
                case 'admin':
                    $data = $this->summaryAdmin();
                    break;
                case 'instructor':
                    $data = $this->summaryInstructor($me['id']);
                    break;
                case 'student':
                default:
                    $data = $this->summaryStudent($me['id']);
                    break;
            }

            $user = null;
            if ($this->tableExists('users')) {
                $q = DB::table('users');
                $this->whereNotDeleted($q, 'users');
                $cols = $this->safeSelect('users', [
                    'users.id',
                    'users.uuid',
                    'users.name',
                    'users.email',
                    'users.role',
                    'users.created_at',
                ]);
                $user = $q->where('users.id', $me['id'])->first($cols);
            }

            return response()->json([
                'ok'   => true,
                'role' => $me['role'],
                'user' => $user,
                'data' => $data,
                'time' => $this->now()->toDateTimeString(),
            ]);
        } catch (\Throwable $e) {
            Log::error('dashboard.summary error: ' . $e->getMessage(), [
                'trace' => $e->getTraceAsString(),
            ]);

            return response()->json(
                ['ok' => false, 'error' => 'Failed to build dashboard summary'],
                500
            );
        }
    }

    public function widget(Request $r, string $slug)
    {
        $me = $this->actor($r);

        try {
            switch ($me['role']) {
                case 'superadmin':
                case 'admin':
                    $payload = $this->widgetAdmin($slug);
                    break;
                case 'instructor':
                    $payload = $this->widgetInstructor($slug, $me['id']);
                    break;
                case 'student':
                default:
                    $payload = $this->widgetStudent($slug, $me['id']);
                    break;
            }

            return response()->json([
                'ok'   => true,
                'slug' => $slug,
                'data' => $payload,
            ]);
        } catch (\Throwable $e) {
            Log::error('dashboard.widget error: ' . $e->getMessage(), ['slug' => $slug]);

            return response()->json(
                ['ok' => false, 'error' => 'Failed to load widget'],
                500
            );
        }
    }

    /* =========================
     | Admin / SuperAdmin
     ========================== */

    private function summaryAdmin(): array
    {
        // ---- counts ----
        $usersTotal = 0;
        if ($this->tableExists('users')) {
            $q = DB::table('users');
            $this->whereNotDeleted($q, 'users');
            $usersTotal = $q->count();
        }

        $coursesTotal = 0;
        if ($this->tableExists('courses')) {
            $q = DB::table('courses');
            $this->whereNotDeleted($q, 'courses');
            $coursesTotal = $q->count();
        }

        $batchesActive = $this->countActiveBatches();

        $quizzesTotal = 0;
        if ($this->tableExists('quizz')) {
            $q = DB::table('quizz');
            $this->whereNotDeleted($q, 'quizz');
            $quizzesTotal = $q->count();
        }

        $pendingGrades = 0;
        if ($this->tableExists('assignment_submissions')) {
            $pendingGrades = (clone $this->ungradedSubmissionsBase())->count();
        }

        // ---- lists ----
        $recentUsers = collect();
        if ($this->tableExists('users')) {
            $q = DB::table('users');
            $this->whereNotDeleted($q, 'users');
            $cols = $this->safeSelect('users', [
                'users.id',
                'users.uuid',
                'users.name',
                'users.email',
                'users.role',
                'users.created_at',
            ]);
            $recentUsers = $q->orderByDesc('users.created_at')
                ->limit($this->lim(8))
                ->get($cols);
        }

        $recentCourses = collect();
        if ($this->tableExists('courses')) {
            $q = DB::table('courses');
            $this->whereNotDeleted($q, 'courses');
            $cols = $this->safeSelect('courses', [
                'courses.id',
                'courses.uuid',
                'courses.title',
                'courses.slug',
                'courses.created_at',
            ]);
            $recentCourses = $q->orderByDesc('courses.created_at')
                ->limit($this->lim(8))
                ->get($cols);
        }

        $recentBatches = collect();
        if ($this->tableExists('batches') && $this->tableExists('courses')) {
            $q = DB::table('batches as b')
                ->leftJoin('courses as c', 'c.id', '=', 'b.course_id');

            $this->whereNotDeleted($q, 'batches', 'b');
            $this->whereNotDeleted($q, 'courses', 'c');

            $selects   = [];
            $selects[] = 'b.id';
            $selects[] = $this->selectCandidate(
                'b',
                'batches',
                ['name', 'title', 'batch_name', 'label'],
                'batch_name'
            );
            $selects[] = $this->selectCandidate(
                'b',
                'batches',
                ['code', 'short_code', 'slug'],
                'batch_code'
            );
            if ($this->hasCol('batches', 'created_at')) {
                $selects[] = 'b.created_at';
            }
            if ($this->hasCol('batches', 'uuid')) {
                $selects[] = 'b.uuid';
            }
            $selects[] = $this->selectCandidate(
                'c',
                'courses',
                ['title', 'name', 'course_title'],
                'course_title'
            );

            $q->select($selects);
            $this->orderByIf($q, 'b', 'batches', 'created_at', 'desc');
            $recentBatches = $q->limit($this->lim(8))->get();
        }

        $latestNotices = collect();
        if ($this->tableExists('notices')) {
            $q = DB::table('notices');
            $this->whereNotDeleted($q, 'notices');
            $cols = $this->safeSelect('notices', [
                'notices.id',
                'notices.uuid',
                'notices.title',
                'notices.created_at',
            ]);
            $latestNotices = $q->orderByDesc('notices.created_at')
                ->limit($this->lim(8))
                ->get($cols);
        }

        return [
            'counts'  => [
                'users_total'      => $usersTotal,
                'courses_total'    => $coursesTotal,
                'batches_active'   => $batchesActive,
                'quizzes_total'    => $quizzesTotal,
                'pending_to_grade' => $pendingGrades,
            ],
            'widgets' => [
                'recent_users'   => $recentUsers,
                'recent_courses' => $recentCourses,
                'recent_batches' => $recentBatches,
                'latest_notices' => $latestNotices,
            ],
        ];
    }

    private function countActiveBatches(): int
    {
        if (!$this->tableExists('batches')) {
            return 0;
        }

        $q = DB::table('batches');
        $this->whereNotDeleted($q, 'batches');

        if ($this->hasCol('batches', 'status')) {
            $q->whereIn('status', ['active', 'running', 'ongoing']);
        }

        return (int) $q->count();
    }

    private function widgetAdmin(string $slug)
    {
        switch ($slug) {
            case 'recent-users':
                if (!$this->tableExists('users')) {
                    return collect();
                }
                $q = DB::table('users');
                $this->whereNotDeleted($q, 'users');
                $cols = $this->safeSelect('users', [
                    'users.id',
                    'users.uuid',
                    'users.name',
                    'users.email',
                    'users.role',
                    'users.created_at',
                ]);
                return $q->orderByDesc('users.created_at')
                    ->limit($this->lim(20))
                    ->get($cols);

            case 'latest-notices':
                if (!$this->tableExists('notices')) {
                    return collect();
                }
                $q = DB::table('notices');
                $this->whereNotDeleted($q, 'notices');
                $cols = $this->safeSelect('notices', [
                    'notices.id',
                    'notices.uuid',
                    'notices.title',
                    'notices.created_at',
                ]);
                return $q->orderByDesc('notices.created_at')
                    ->limit($this->lim(20))
                    ->get($cols);

            default:
                return [];
        }
    }

    /* =========================
     | Instructor
     ========================== */

    private function myBatchIdsForInstructor(int $uid): array
    {
        if (!$this->tableExists('batch_instructors')) {
            return [];
        }

        $linkCol = $this->pickCol('batch_instructors', ['instructor_id', 'user_id']);
        if (!$linkCol) {
            return [];
        }

        $q = DB::table('batch_instructors')->where($linkCol, $uid);
        $this->whereNotDeleted($q, 'batch_instructors');

        return $q->pluck('batch_id')->unique()->values()->all();
    }

    private function summaryInstructor(int $uid): array
    {
        $now      = $this->now()->toDateTimeString();
        $batchIds = $this->myBatchIdsForInstructor($uid);
        $myBatches = count($batchIds);

        // ---- counts ----
        $studentCount = 0;
        if ($this->tableExists('batch_students') && $myBatches > 0) {
            $studentCol = $this->pickCol('batch_students', ['student_id', 'user_id']) ?? 'student_id';
            $q          = DB::table('batch_students')->whereIn('batch_id', $batchIds);
            $this->whereNotDeleted($q, 'batch_students');
            if ($this->hasCol('batch_students', 'enrollment_status')) {
                $q->where('enrollment_status', 'enrolled');
            }
            $studentCount = (int) $q->distinct($studentCol)->count($studentCol);
        }

        $toGrade = 0;
        if ($this->tableExists('assignment_submissions') && $myBatches > 0) {
            $toGrade = (clone $this->ungradedSubmissionsBase())
                ->whereIn('assignment_submissions.batch_id', $batchIds)
                ->count();
        }

        $upcomingQuizzes = 0;
        if ($this->tableExists('batch_quizzes') && $myBatches > 0) {
            $q = DB::table('batch_quizzes')->whereIn('batch_id', $batchIds);
            $this->whereNotDeleted($q, 'batch_quizzes');
            if ($this->hasCol('batch_quizzes', 'start_at')) {
                $q->where(function ($w) use ($now) {
                    $w->where('start_at', '>=', $now);
                    if (Schema::hasColumn('batch_quizzes', 'end_at')) {
                        $w->orWhere('end_at', '>=', $now);
                    }
                });
            } elseif ($this->hasCol('batch_quizzes', 'schedule_at')) {
                $q->where('schedule_at', '>=', $now);
            }
            $upcomingQuizzes = (int) $q->count();
        }

        // ---- widgets ----

        // My batches with student counts
        $myBatchList = collect();
        if ($this->tableExists('batches')
            && $this->tableExists('courses')
            && $this->tableExists('batch_students')
            && $myBatches > 0
        ) {
            $studentCol = $this->pickCol('batch_students', ['student_id', 'user_id']) ?? 'student_id';

            $q = DB::table('batches as b')
                ->leftJoin('courses as c', 'c.id', '=', 'b.course_id')
                ->leftJoin('batch_students as bs', 'bs.batch_id', '=', 'b.id')
                ->whereIn('b.id', $batchIds);

            $this->whereNotDeleted($q, 'batches', 'b');
            $this->whereNotDeleted($q, 'courses', 'c');
            $this->whereNotDeleted($q, 'batch_students', 'bs');

            $selects   = [];
            $selects[] = 'b.id';
            $selects[] = $this->selectCandidate(
                'b',
                'batches',
                ['name', 'title', 'batch_name', 'label'],
                'batch_name'
            );
            $selects[] = $this->selectCandidate(
                'b',
                'batches',
                ['code', 'short_code', 'slug'],
                'batch_code'
            );
            $selects[] = DB::raw('COUNT(DISTINCT bs.' . $studentCol . ') as student_count');
            if ($this->hasCol('batches', 'created_at')) {
                $selects[] = 'b.created_at';
            }
            $selects[] = $this->selectCandidate(
                'c',
                'courses',
                ['title', 'name', 'course_title'],
                'course_title'
            );

            $q->select($selects);

            $group = ['b.id'];
            if ($this->hasCol('batches', 'created_at')) {
                $group[] = 'b.created_at';
            }
            if ($this->hasCol('courses', 'title')) {
                $group[] = 'c.title';
            } elseif ($this->hasCol('courses', 'name')) {
                $group[] = 'c.name';
            } elseif ($this->hasCol('courses', 'course_title')) {
                $group[] = 'c.course_title';
            }

            $this->groupBySome($q, $group);
            $this->orderByIf($q, 'b', 'batches', 'created_at', 'desc');

            $myBatchList = $q->limit($this->lim(8))->get();
        }

        // Pending grading queue
        $pendingGradingQueue = collect();
        if ($this->tableExists('assignment_submissions')
            && $this->tableExists('assignments')
            && $myBatches > 0
        ) {
            $q = (clone $this->ungradedSubmissionsBase())
                ->whereIn('assignment_submissions.batch_id', $batchIds)
                ->leftJoin('assignments as a', 'a.id', '=', 'assignment_submissions.assignment_id');

            $this->whereNotDeleted($q, 'assignments', 'a');

            $selects   = [
                'assignment_submissions.assignment_id',
                'a.batch_id',
                DB::raw('COUNT(*) as pending_count'),
                DB::raw('MAX(assignment_submissions.created_at) as last_submitted_at'),
            ];
            $selects[] = $this->selectCandidate(
                'a',
                'assignments',
                ['title', 'name'],
                'assignment_title'
            );

            $q->select($selects);

            $group = ['assignment_submissions.assignment_id', 'a.batch_id'];
            if ($this->hasCol('assignments', 'title')) {
                $group[] = 'a.title';
            } elseif ($this->hasCol('assignments', 'name')) {
                $group[] = 'a.name';
            }

            $this->groupBySome($q, $group)
                ->orderByDesc('pending_count');

            $pendingGradingQueue = $q->limit($this->lim(8))->get();
        }

        // Upcoming quizzes list (this was throwing the error)
        $nextQuizzes = collect();
        if ($this->tableExists('batch_quizzes')
            && $this->tableExists('quizz')
            && $myBatches > 0
        ) {
            $quizFk = $this->batchQuizFk();   // quizz_id or quiz_id
            if ($quizFk) {
                $q = DB::table('batch_quizzes as bq')
                    ->join('quizz as q', 'q.id', '=', 'bq.' . $quizFk)
                    ->whereIn('bq.batch_id', $batchIds);

                $this->whereNotDeleted($q, 'batch_quizzes', 'bq');
                $this->whereNotDeleted($q, 'quizz', 'q');

                $selects = [
                    'bq.id',
                    'bq.batch_id',
                    "bq.$quizFk as quizz_id",
                ];
                if ($this->hasCol('batch_quizzes', 'start_at')) {
                    $selects[] = 'bq.start_at';
                }
                if ($this->hasCol('batch_quizzes', 'end_at')) {
                    $selects[] = 'bq.end_at';
                }
                if ($this->hasCol('batch_quizzes', 'schedule_at')) {
                    $selects[] = 'bq.schedule_at';
                }
                $selects[] = $this->selectCandidate(
                    'q',
                    'quizz',
                    ['title', 'name'],
                    'title'
                );

                $q->select($selects);

                if ($this->hasCol('batch_quizzes', 'start_at')) {
                    $q->where('bq.start_at', '>=', $now)->orderBy('bq.start_at', 'asc');
                } elseif ($this->hasCol('batch_quizzes', 'schedule_at')) {
                    $q->where('bq.schedule_at', '>=', $now)->orderBy('bq.schedule_at', 'asc');
                } else {
                    $q->orderByDesc('bq.id');
                }

                $nextQuizzes = $q->limit($this->lim(8))->get();
            }
        }

        $recentBatchMessages = collect();
        if ($this->tableExists('batch_messages') && $myBatches > 0) {
            $q = DB::table('batch_messages')->whereIn('batch_id', $batchIds);
            $this->whereNotDeleted($q, 'batch_messages');
            $cols = $this->safeSelect('batch_messages', [
                'id',
                'batch_id',
                'title',
                'message',
                'created_at',
            ]);
            $recentBatchMessages = $q->orderByDesc('created_at')
                ->limit($this->lim(8))
                ->get($cols);
        }

        return [
            'counts'  => [
                'my_batches'           => $myBatches,
                'my_students'          => $studentCount,
                'submissions_to_grade' => $toGrade,
                'upcoming_quizzes'     => $upcomingQuizzes,
            ],
            'widgets' => [
                'my_batches'       => $myBatchList,
                'pending_to_grade' => $pendingGradingQueue,
                'upcoming_quizzes' => $nextQuizzes,
                'recent_messages'  => $recentBatchMessages,
            ],
        ];
    }

    private function widgetInstructor(string $slug, int $uid)
    {
        $batchIds = $this->myBatchIdsForInstructor($uid);
        $now      = $this->now()->toDateTimeString();

        switch ($slug) {
            case 'my-batches':
                if (!($this->tableExists('batches')
                        && $this->tableExists('courses')
                        && $this->tableExists('batch_students'))
                    || empty($batchIds)
                ) {
                    return collect();
                }

                $studentCol = $this->pickCol('batch_students', ['student_id', 'user_id']) ?? 'student_id';

                $q = DB::table('batches as b')
                    ->leftJoin('courses as c', 'c.id', '=', 'b.course_id')
                    ->leftJoin('batch_students as bs', 'bs.batch_id', '=', 'b.id')
                    ->whereIn('b.id', $batchIds);

                $this->whereNotDeleted($q, 'batches', 'b');
                $this->whereNotDeleted($q, 'courses', 'c');
                $this->whereNotDeleted($q, 'batch_students', 'bs');

                $selects = [
                    'b.id',
                    $this->selectCandidate(
                        'b',
                        'batches',
                        ['name', 'title', 'batch_name', 'label'],
                        'batch_name'
                    ),
                    $this->selectCandidate(
                        'b',
                        'batches',
                        ['code', 'short_code', 'slug'],
                        'batch_code'
                    ),
                    DB::raw('COUNT(DISTINCT bs.' . $studentCol . ') as student_count'),
                ];
                if ($this->hasCol('batches', 'created_at')) {
                    $selects[] = 'b.created_at';
                }
                $selects[] = $this->selectCandidate(
                    'c',
                    'courses',
                    ['title', 'name', 'course_title'],
                    'course_title'
                );
                $q->select($selects);

                $group = ['b.id'];
                if ($this->hasCol('batches', 'created_at')) {
                    $group[] = 'b.created_at';
                }
                if ($this->hasCol('courses', 'title')) {
                    $group[] = 'c.title';
                } elseif ($this->hasCol('courses', 'name')) {
                    $group[] = 'c.name';
                } elseif ($this->hasCol('courses', 'course_title')) {
                    $group[] = 'c.course_title';
                }

                $this->groupBySome($q, $group);
                $this->orderByIf($q, 'b', 'batches', 'created_at', 'desc');

                return $q->limit($this->lim(20))->get();

            case 'pending-grades':
                if (!($this->tableExists('assignment_submissions') && $this->tableExists('assignments'))
                    || empty($batchIds)
                ) {
                    return collect();
                }

                $q = (clone $this->ungradedSubmissionsBase())
                    ->whereIn('assignment_submissions.batch_id', $batchIds)
                    ->leftJoin('assignments as a', 'a.id', '=', 'assignment_submissions.assignment_id');

                $this->whereNotDeleted($q, 'assignments', 'a');

                $q->select([
                    'assignment_submissions.assignment_id',
                    'a.batch_id',
                    DB::raw('COUNT(*) as pending_count'),
                    DB::raw('MAX(assignment_submissions.created_at) as last_submitted_at'),
                    $this->selectCandidate(
                        'a',
                        'assignments',
                        ['title', 'name'],
                        'assignment_title'
                    ),
                ]);

                $group = ['assignment_submissions.assignment_id', 'a.batch_id'];
                if ($this->hasCol('assignments', 'title')) {
                    $group[] = 'a.title';
                } elseif ($this->hasCol('assignments', 'name')) {
                    $group[] = 'a.name';
                }

                $this->groupBySome($q, $group)->orderByDesc('pending_count');

                return $q->limit($this->lim(20))->get();

            case 'upcoming-quizzes':
                if (!($this->tableExists('batch_quizzes') && $this->tableExists('quizz'))
                    || empty($batchIds)
                ) {
                    return collect();
                }

                $quizFk = $this->batchQuizFk();
                if (!$quizFk) {
                    return collect();
                }

                $q = DB::table('batch_quizzes as bq')
                    ->join('quizz as q', 'q.id', '=', 'bq.' . $quizFk)
                    ->whereIn('bq.batch_id', $batchIds);

                $this->whereNotDeleted($q, 'batch_quizzes', 'bq');
                $this->whereNotDeleted($q, 'quizz', 'q');

                $selects = [
                    'bq.id',
                    'bq.batch_id',
                    "bq.$quizFk as quizz_id",
                ];
                if ($this->hasCol('batch_quizzes', 'start_at')) {
                    $selects[] = 'bq.start_at';
                }
                if ($this->hasCol('batch_quizzes', 'end_at')) {
                    $selects[] = 'bq.end_at';
                }
                if ($this->hasCol('batch_quizzes', 'schedule_at')) {
                    $selects[] = 'bq.schedule_at';
                }
                $selects[] = $this->selectCandidate(
                    'q',
                    'quizz',
                    ['title', 'name'],
                    'title'
                );

                $q->select($selects);

                if ($this->hasCol('batch_quizzes', 'start_at')) {
                    $q->where('bq.start_at', '>=', $now)->orderBy('bq.start_at', 'asc');
                } elseif ($this->hasCol('batch_quizzes', 'schedule_at')) {
                    $q->where('bq.schedule_at', '>=', $now)->orderBy('bq.schedule_at', 'asc');
                } else {
                    $q->orderByDesc('bq.id');
                }

                return $q->limit($this->lim(20))->get();

            case 'recent-messages':
                if (!$this->tableExists('batch_messages') || empty($batchIds)) {
                    return collect();
                }
                $q = DB::table('batch_messages')->whereIn('batch_id', $batchIds);
                $this->whereNotDeleted($q, 'batch_messages');
                $cols = $this->safeSelect('batch_messages', [
                    'id',
                    'batch_id',
                    'title',
                    'message',
                    'created_at',
                ]);
                return $q->orderByDesc('created_at')
                    ->limit($this->lim(20))
                    ->get($cols);

            default:
                return [];
        }
    }

    /* =========================
     | Student
     ========================== */

    private function myBatchIdsForStudent(int $uid): array
    {
        if (!$this->tableExists('batch_students')) {
            return [];
        }

        $linkCol = $this->pickCol('batch_students', ['student_id', 'user_id']);
        if (!$linkCol) {
            return [];
        }

        $q = DB::table('batch_students')->where($linkCol, $uid);
        $this->whereNotDeleted($q, 'batch_students');

        // **IMPORTANT CHANGE**: do NOT filter by enrollment_status anymore
        // (you said: doesn't matter verified/enrolled, just show data)
        // if ($this->hasCol('batch_students','enrollment_status')) {
        //     $q->where('enrollment_status','enrolled');
        // }

        return $q->pluck('batch_id')->unique()->values()->all();
    }

    private function summaryStudent(int $uid): array
    {
        $now      = $this->now()->toDateTimeString();
        $batchIds = $this->myBatchIdsForStudent($uid);
        $myBatches = count($batchIds);

        $courseCount = 0;
        if ($this->tableExists('batches') && $this->tableExists('courses') && $myBatches > 0) {
            $q = DB::table('batches as b')
                ->join('courses as c', 'c.id', '=', 'b.course_id')
                ->whereIn('b.id', $batchIds);
            $this->whereNotDeleted($q, 'batches', 'b');
            $this->whereNotDeleted($q, 'courses', 'c');
            $courseCount = (int) $q->distinct('c.id')->count('c.id');
        }

        $pendingAssign = 0;
        if ($this->tableExists('assignments')
            && $this->tableExists('assignment_submissions')
            && $myBatches > 0
        ) {
            $subStudentCol = $this->pickCol('assignment_submissions', ['student_id', 'user_id']) ?? 'student_id';

            $q = DB::table('assignments as a')
                ->whereIn('a.batch_id', $batchIds);

            $this->whereNotDeleted($q, 'assignments', 'a');

            $q->leftJoin('assignment_submissions as s', function ($join) use ($uid, $subStudentCol) {
                $join->on('s.assignment_id', '=', 'a.id')
                    ->where("s.$subStudentCol", '=', $uid);
            });

            $this->whereNotDeleted($q, 'assignment_submissions', 's');

            $q->where(function ($w) {
                $w->whereNull('s.id');
                if (Schema::hasColumn('assignment_submissions', 'status')) {
                    $w->orWhere('s.status', '<>', 'graded');
                }
            });

            $pendingAssign = (int) $q->count('a.id');
        }

        $activeUpcomingQuizzes = 0;
        if ($this->tableExists('batch_quizzes') && $myBatches > 0) {
            $q = DB::table('batch_quizzes as bq')->whereIn('bq.batch_id', $batchIds);
            $this->whereNotDeleted($q, 'batch_quizzes', 'bq');
            if ($this->hasCol('batch_quizzes', 'start_at')) {
                $q->where(function ($w) use ($now) {
                    $w->where('bq.start_at', '>=', $now);
                    if (Schema::hasColumn('batch_quizzes', 'end_at')) {
                        $w->orWhere('bq.end_at', '>=', $now);
                    }
                });
            } elseif ($this->hasCol('batch_quizzes', 'schedule_at')) {
                $q->where('bq.schedule_at', '>=', $now);
            }
            $activeUpcomingQuizzes = (int) $q->count();
        }

        $myCourses = collect();
        if ($this->tableExists('batches') && $this->tableExists('courses') && $myBatches > 0) {
            $q = DB::table('batches as b')
                ->join('courses as c', 'c.id', '=', 'b.course_id')
                ->whereIn('b.id', $batchIds);
            $this->whereNotDeleted($q, 'batches', 'b');
            $this->whereNotDeleted($q, 'courses', 'c');

            $selects = [
                'b.id as batch_id',
                $this->selectCandidate(
                    'b',
                    'batches',
                    ['name', 'title', 'batch_name', 'label'],
                    'batch_name'
                ),
                $this->selectCandidate(
                    'b',
                    'batches',
                    ['code', 'short_code', 'slug'],
                    'batch_code'
                ),
                $this->selectCandidate(
                    'c',
                    'courses',
                    ['title', 'name', 'course_title'],
                    'course_title'
                ),
            ];
            $q->select($selects);

            if ($this->hasCol('courses', 'title')) {
                $q->orderBy('c.title');
            } elseif ($this->hasCol('courses', 'name')) {
                $q->orderBy('c.name');
            } else {
                $q->orderBy('b.id', 'desc');
            }

            $myCourses = $q->limit($this->lim(8))->get();
        }

        $upcomingAssignments = collect();
        if ($this->tableExists('assignments') && $myBatches > 0) {
            $q = DB::table('assignments as a')->whereIn('a.batch_id', $batchIds);
            $this->whereNotDeleted($q, 'assignments', 'a');

            if ($this->hasCol('assignments', 'due_at')) {
                $q->where('a.due_at', '>=', $now)->orderBy('a.due_at', 'asc');
            } else {
                $q->orderByDesc('a.created_at');
            }

            $selects   = ['a.id', 'a.batch_id'];
            $selects[] = $this->selectCandidate(
                'a',
                'assignments',
                ['title', 'name'],
                'title'
            );
            if ($this->hasCol('assignments', 'due_at')) {
                $selects[] = 'a.due_at';
            }
            if ($this->hasCol('assignments', 'created_at')) {
                $selects[] = 'a.created_at';
            }
            if ($this->hasCol('assignments', 'uuid')) {
                $selects[] = 'a.uuid';
            }

            $q->select($selects);

            $upcomingAssignments = $q->limit($this->lim(8))->get();
        }

        $upcomingQuizzes = collect();
        if ($this->tableExists('batch_quizzes')
            && $this->tableExists('quizz')
            && $myBatches > 0
        ) {
            $quizFk = $this->batchQuizFk();
            if ($quizFk) {
                $q = DB::table('batch_quizzes as bq')
                    ->join('quizz as q', 'q.id', '=', 'bq.' . $quizFk)
                    ->whereIn('bq.batch_id', $batchIds);

                $this->whereNotDeleted($q, 'batch_quizzes', 'bq');
                $this->whereNotDeleted($q, 'quizz', 'q');

                $selects = [
                    'bq.id',
                    'bq.batch_id',
                    "bq.$quizFk as quizz_id",
                ];
                if ($this->hasCol('batch_quizzes', 'start_at')) {
                    $selects[] = 'bq.start_at';
                }
                if ($this->hasCol('batch_quizzes', 'end_at')) {
                    $selects[] = 'bq.end_at';
                }
                if ($this->hasCol('batch_quizzes', 'schedule_at')) {
                    $selects[] = 'bq.schedule_at';
                }
                $selects[] = $this->selectCandidate(
                    'q',
                    'quizz',
                    ['title', 'name'],
                    'title'
                );

                $q->select($selects);

                if ($this->hasCol('batch_quizzes', 'start_at')) {
                    $q->where('bq.start_at', '>=', $now)->orderBy('bq.start_at', 'asc');
                } elseif ($this->hasCol('batch_quizzes', 'schedule_at')) {
                    $q->where('bq.schedule_at', '>=', $now)->orderBy('bq.schedule_at', 'asc');
                } else {
                    $q->orderByDesc('bq.id');
                }

                $upcomingQuizzes = $q->limit($this->lim(8))->get();
            }
        }

        $recentNotices = collect();
        if ($this->tableExists('notices')) {
            if ($this->hasCol('notices', 'batch_id') && !empty($batchIds)) {
                $q = DB::table('notices')->whereIn('batch_id', $batchIds);
                $this->whereNotDeleted($q, 'notices');
                $cols = $this->safeSelect('notices', [
                    'id',
                    'uuid',
                    'title',
                    'batch_id',
                    'created_at',
                ]);
                $recentNotices = $q->orderByDesc('created_at')
                    ->limit($this->lim(8))
                    ->get($cols);
            } else {
                $q = DB::table('notices');
                $this->whereNotDeleted($q, 'notices');
                $cols = $this->safeSelect('notices', [
                    'id',
                    'uuid',
                    'title',
                    'created_at',
                ]);
                $recentNotices = $q->orderByDesc('created_at')
                    ->limit($this->lim(8))
                    ->get($cols);
            }
        }

        $myActivity = [
            'recent_submissions'   => collect(),
            'recent_quiz_attempts' => collect(),
        ];

        if ($this->tableExists('assignment_submissions')) {
            $subStudentCol = $this->pickCol('assignment_submissions', ['student_id', 'user_id']) ?? 'student_id';
            $q             = DB::table('assignment_submissions')
                ->where($subStudentCol, $uid);
            $this->whereNotDeleted($q, 'assignment_submissions');
            $cols = $this->safeSelect('assignment_submissions', [
                'id',
                'assignment_id',
                'batch_id',
                'status',
                'marks',
                'created_at',
            ]);
            $myActivity['recent_submissions'] = $q->orderByDesc('created_at')
                ->limit($this->lim(8))
                ->get($cols);
        }

        if ($this->tableExists('quizz_attempts')) {
            $attemptUserCol = $this->pickCol('quizz_attempts', ['student_id', 'user_id']) ?? 'student_id';
            $q              = DB::table('quizz_attempts')
                ->where($attemptUserCol, $uid);
            $this->whereNotDeleted($q, 'quizz_attempts');
            $cols = $this->safeSelect('quizz_attempts', [
                'id',
                'quizz_id',
                'batch_id',
                'status',
                'score',
                'created_at',
            ]);
            $myActivity['recent_quiz_attempts'] = $q->orderByDesc('created_at')
                ->limit($this->lim(8))
                ->get($cols);
        }

        return [
            'counts'  => [
                'my_batches'              => $myBatches,
                'my_active_courses'       => $courseCount,
                'pending_assignments'     => $pendingAssign,
                'active_upcoming_quizzes' => $activeUpcomingQuizzes,
            ],
            'widgets' => [
                'my_courses'           => $myCourses,
                'upcoming_assignments' => $upcomingAssignments,
                'upcoming_quizzes'     => $upcomingQuizzes,
                'recent_notices'       => $recentNotices,
                'my_activity'          => $myActivity,
            ],
        ];
    }

    private function widgetStudent(string $slug, int $uid)
    {
        $batchIds = $this->myBatchIdsForStudent($uid);
        $now      = $this->now()->toDateTimeString();

        switch ($slug) {
            case 'my-courses':
                if (!($this->tableExists('batches') && $this->tableExists('courses'))
                    || empty($batchIds)
                ) {
                    return collect();
                }

                $q = DB::table('batches as b')
                    ->join('courses as c', 'c.id', '=', 'b.course_id')
                    ->whereIn('b.id', $batchIds);
                $this->whereNotDeleted($q, 'batches', 'b');
                $this->whereNotDeleted($q, 'courses', 'c');

                $q->select([
                    'b.id as batch_id',
                    $this->selectCandidate(
                        'b',
                        'batches',
                        ['name', 'title', 'batch_name', 'label'],
                        'batch_name'
                    ),
                    $this->selectCandidate(
                        'b',
                        'batches',
                        ['code', 'short_code', 'slug'],
                        'batch_code'
                    ),
                    $this->selectCandidate(
                        'c',
                        'courses',
                        ['title', 'name', 'course_title'],
                        'course_title'
                    ),
                ]);

                if ($this->hasCol('courses', 'title')) {
                    $q->orderBy('c.title');
                } elseif ($this->hasCol('courses', 'name')) {
                    $q->orderBy('c.name');
                } else {
                    $q->orderBy('b.id', 'desc');
                }

                return $q->limit($this->lim(20))->get();

            case 'upcoming-assignments':
                if (!$this->tableExists('assignments') || empty($batchIds)) {
                    return collect();
                }
                $q = DB::table('assignments as a')->whereIn('a.batch_id', $batchIds);
                $this->whereNotDeleted($q, 'assignments', 'a');

                if ($this->hasCol('assignments', 'due_at')) {
                    $q->where('a.due_at', '>=', $now)->orderBy('a.due_at', 'asc');
                } else {
                    $q->orderByDesc('a.created_at');
                }

                $q->select([
                    'a.id',
                    'a.batch_id',
                    $this->selectCandidate(
                        'a',
                        'assignments',
                        ['title', 'name'],
                        'title'
                    ),
                    $this->hasCol('assignments', 'due_at')
                        ? 'a.due_at'
                        : DB::raw('NULL as due_at'),
                    $this->hasCol('assignments', 'created_at')
                        ? 'a.created_at'
                        : DB::raw('NULL as created_at'),
                ]);

                return $q->limit($this->lim(20))->get();

            case 'upcoming-quizzes':
                if (!($this->tableExists('batch_quizzes') && $this->tableExists('quizz'))
                    || empty($batchIds)
                ) {
                    return collect();
                }

                $quizFk = $this->batchQuizFk();
                if (!$quizFk) {
                    return collect();
                }

                $q = DB::table('batch_quizzes as bq')
                    ->join('quizz as q', 'q.id', '=', 'bq.' . $quizFk)
                    ->whereIn('bq.batch_id', $batchIds);
                $this->whereNotDeleted($q, 'batch_quizzes', 'bq');
                $this->whereNotDeleted($q, 'quizz', 'q');

                $selects = [
                    'bq.id',
                    'bq.batch_id',
                    "bq.$quizFk as quizz_id",
                ];
                if ($this->hasCol('batch_quizzes', 'start_at')) {
                    $selects[] = 'bq.start_at';
                }
                if ($this->hasCol('batch_quizzes', 'end_at')) {
                    $selects[] = 'bq.end_at';
                }
                if ($this->hasCol('batch_quizzes', 'schedule_at')) {
                    $selects[] = 'bq.schedule_at';
                }
                $selects[] = $this->selectCandidate(
                    'q',
                    'quizz',
                    ['title', 'name'],
                    'title'
                );

                $q->select($selects);

                if ($this->hasCol('batch_quizzes', 'start_at')) {
                    $q->where('bq.start_at', '>=', $now)->orderBy('bq.start_at', 'asc');
                } elseif ($this->hasCol('batch_quizzes', 'schedule_at')) {
                    $q->where('bq.schedule_at', '>=', $now)->orderBy('bq.schedule_at', 'asc');
                } else {
                    $q->orderByDesc('bq.id');
                }

                return $q->limit($this->lim(20))->get();

            case 'recent-notices':
                if (!$this->tableExists('notices')) {
                    return collect();
                }
                if ($this->hasCol('notices', 'batch_id') && !empty($batchIds)) {
                    $q = DB::table('notices')->whereIn('batch_id', $batchIds);
                    $this->whereNotDeleted($q, 'notices');
                    $cols = $this->safeSelect('notices', [
                        'id',
                        'uuid',
                        'title',
                        'batch_id',
                        'created_at',
                    ]);
                    return $q->orderByDesc('created_at')
                        ->limit($this->lim(20))
                        ->get($cols);
                }
                $q = DB::table('notices');
                $this->whereNotDeleted($q, 'notices');
                $cols = $this->safeSelect('notices', [
                    'id',
                    'uuid',
                    'title',
                    'created_at',
                ]);
                return $q->orderByDesc('created_at')
                    ->limit($this->lim(20))
                    ->get($cols);

            case 'my-activity':
                $out = [
                    'recent_submissions'   => collect(),
                    'recent_quiz_attempts' => collect(),
                ];

                if ($this->tableExists('assignment_submissions')) {
                    $subStudentCol = $this->pickCol('assignment_submissions', ['student_id', 'user_id']) ?? 'student_id';
                    $q             = DB::table('assignment_submissions')
                        ->where($subStudentCol, $uid);
                    $this->whereNotDeleted($q, 'assignment_submissions');
                    $cols = $this->safeSelect('assignment_submissions', [
                        'id',
                        'assignment_id',
                        'batch_id',
                        'status',
                        'marks',
                        'created_at',
                    ]);
                    $out['recent_submissions'] = $q->orderByDesc('created_at')
                        ->limit($this->lim(20))
                        ->get($cols);
                }

                if ($this->tableExists('quizz_attempts')) {
                    $attemptUserCol = $this->pickCol('quizz_attempts', ['student_id', 'user_id']) ?? 'student_id';
                    $q              = DB::table('quizz_attempts')
                        ->where($attemptUserCol, $uid);
                    $this->whereNotDeleted($q, 'quizz_attempts');
                    $cols = $this->safeSelect('quizz_attempts', [
                        'id',
                        'quizz_id',
                        'batch_id',
                        'status',
                        'score',
                        'created_at',
                    ]);
                    $out['recent_quiz_attempts'] = $q->orderByDesc('created_at')
                        ->limit($this->lim(20))
                        ->get($cols);
                }

                return $out;

            default:
                return [];
        }
    }
}
