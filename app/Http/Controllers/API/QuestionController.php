<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\Rule;
use Illuminate\Support\Str;

class QuestionController extends Controller
{
    /* =========================================================
     |  Auth / Actor helpers  (mirrors QuizzController)
     * ========================================================= */
    private function actor(Request $request): array
    {
        return [
            'role' => $request->attributes->get('auth_role'),
            'type' => $request->attributes->get('auth_tokenable_type'),
            'id'   => (int) ($request->attributes->get('auth_tokenable_id') ?? 0),
        ];
    }

    private function requireRole(Request $r, array $allowed)
    {
        $a = $this->actor($r);
        if (!$a['role'] || !in_array($a['role'], $allowed, true)) {
            return response()->json(['error' => 'Unauthorized Access'], 403);
        }
        return null;
    }

    private function logWithActor(string $msg, Request $r, array $extra = []): void
    {
        $a = $this->actor($r);
        Log::info($msg, array_merge([
            'actor_role' => $a['role'],
            'actor_type' => $a['type'],
            'actor_id'   => $a['id'],
        ], $extra));
    }

    /* =========================================================
     |  Activity Log + Notifications (same schema as QuizzController)
     * ========================================================= */
    private function logActivity(
        Request $request,
        string $activity, // store | update | destroy
        string $note,
        string $tableName,
        ?int $recordId = null,
        ?array $changedFields = null,
        ?array $oldValues = null,
        ?array $newValues = null
    ): void {
        $a = $this->actor($request);

        try {
            DB::table('user_data_activity_log')->insert([
                'performed_by'       => $a['id'] ?: 0,
                'performed_by_role'  => $a['role'] ?: null,
                'ip'                 => $request->ip(),
                'user_agent'         => (string) $request->userAgent(),
                'activity'           => $activity,
                'module'             => 'QuizzQuestion',
                'table_name'         => $tableName,
                'record_id'          => $recordId,
                'changed_fields'     => $changedFields ? json_encode(array_values($changedFields), JSON_UNESCAPED_UNICODE) : null,
                'old_values'         => $oldValues ? json_encode($oldValues, JSON_UNESCAPED_UNICODE) : null,
                'new_values'         => $newValues ? json_encode($newValues, JSON_UNESCAPED_UNICODE) : null,
                'log_note'           => $note,
                'created_at'         => now(),
                'updated_at'         => now(),
            ]);
        } catch (\Throwable $e) {
            Log::error('[QuizzQuestion] user_data_activity_log insert failed', ['error' => $e->getMessage()]);
        }
    }

    private function persistNotification(array $payload): void
    {
        $title     = (string)($payload['title'] ?? 'Notification');
        $message   = (string)($payload['message'] ?? '');
        $receivers = array_values(array_map(function($x){
            return [
                'id'   => isset($x['id']) ? (int)$x['id'] : null,
                'role' => (string)($x['role'] ?? 'unknown'),
                'read' => (int)($x['read'] ?? 0),
            ];
        }, $payload['receivers'] ?? []));

        $metadata = $payload['metadata'] ?? [];
        $type     = (string)($payload['type'] ?? 'general');
        $linkUrl  = $payload['link_url'] ?? null;
        $priority = in_array(($payload['priority'] ?? 'normal'), ['low','normal','high','urgent'], true)
            ? $payload['priority'] : 'normal';
        $status   = in_array(($payload['status'] ?? 'active'), ['active','archived','deleted'], true)
            ? $payload['status'] : 'active';

        DB::table('notifications')->insert([
            'title'      => $title,
            'message'    => $message,
            'receivers'  => json_encode($receivers, JSON_UNESCAPED_UNICODE),
            'metadata'   => $metadata ? json_encode($metadata, JSON_UNESCAPED_UNICODE) : null,
            'type'       => $type,
            'link_url'   => $linkUrl,
            'priority'   => $priority,
            'status'     => $status,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    private function adminReceivers(array $excludeIds = []): array
    {
        $exclude = array_flip(array_map('intval', $excludeIds));

        $rows = DB::table('users')
            ->select('id', 'role', 'status')
            ->whereNull('deleted_at')
            ->whereIn('role', ['admin','super_admin'])
            ->where('status', '=', 'active')
            ->get();

        $out = [];
        foreach ($rows as $r) {
            $id = (int)$r->id;
            if (isset($exclude[$id])) continue;

            $role = in_array($r->role, ['admin','super_admin'], true) ? $r->role : 'admin';
            $out[] = ['id' => $id, 'role' => $role, 'read' => 0];
        }
        return $out;
    }

    /* =========================================================
     |  Local helpers
     * ========================================================= */
    private function mapType(string $type): string
    {
        return match ($type) {
            'multiple_choice', 'single_choice' => 'mcq',
            default                             => $type,  // 'mcq' | 'true_false' | 'fill_in_the_blank'
        };
    }

    private function resolveQuizIdAndUuid(Request $r): ?array
    {
        // Accept either ?quiz_id=123 OR ?quiz=uuid|id (or body on POST)
        $quizId  = $r->input('quiz_id') ?: $r->query('quiz_id');
        $quizKey = $r->input('quiz')    ?: $r->query('quiz');

        if ($quizId && ctype_digit((string)$quizId)) {
            $row = DB::table('quizz')->where('id', (int)$quizId)->whereNull('deleted_at')->first();
            return $row ? ['id' => (int)$row->id, 'uuid' => (string)$row->uuid] : null;
        }

        if ($quizKey) {
            $row = DB::table('quizz')->whereNull('deleted_at')
                ->when(ctype_digit($quizKey), fn($q) => $q->where('id', (int)$quizKey),
                                     fn($q) => $q->where('uuid', $quizKey))
                ->first();
            return $row ? ['id' => (int)$row->id, 'uuid' => (string)$row->uuid] : null;
        }

        return null;
    }

    private function resolveQuestionIdByKey(string|int $key): ?int
    {
        if (is_numeric($key)) {
            $exists = DB::table('quizz_questions')->where('id', (int)$key)->exists();
            return $exists ? (int)$key : null;
        }
        $row = DB::table('quizz_questions')->where('uuid', (string)$key)->first();
        return $row ? (int)$row->id : null;
    }

    private function recalcAndPersistQuizQuestionCount(int $quizId): void
    {
        try {
            $cnt = (int) DB::table('quizz_questions')->where('quiz_id', $quizId)->count();
            DB::table('quizz')->where('id', $quizId)->update([
                'total_questions' => $cnt,
                'updated_at'      => now(),
            ]);
        } catch (\Throwable $e) {
            Log::warning('[QuizzQuestion] Failed to update quizz.total_questions', [
                'quiz_id' => $quizId,
                'error'   => $e->getMessage()
            ]);
        }
    }

    private const DIFFICULTIES = ['easy','medium','hard'];

    /* =========================================================
     |  GET /api/quizz/questions?quiz_id=…  OR ?quiz=uuid|id
     |  List questions (+answers) for a quiz
     * ========================================================= */
    public function index(Request $request)
    {
        if ($resp = $this->requireRole($request, ['admin','super_admin'])) return $resp;

        $quiz = $this->resolveQuizIdAndUuid($request);
        if (!$quiz) {
            return response()->json(['success'=>false,'message'=>'Missing or invalid quiz / quiz_id'], 422);
        }

        // NEW: optional difficulty filter (easy|medium|hard), case-insensitive
        // NEW: optional difficulty filter (easy|medium|hard)
        $difficulty = strtolower((string) $request->query('difficulty', ''));
        $difficulty = in_array($difficulty, ['easy','medium','hard'], true) ? $difficulty : null;

        $rows = DB::table('quizz_questions as q')
            ->leftJoin('quizz_question_answers as a', 'q.id', '=', 'a.belongs_question_id')
            ->where('q.quiz_id', $quiz['id'])
            ->when($difficulty, fn($qb) => $qb->where('q.question_difficulty', $difficulty)) // <-- fix
            ->orderBy('q.question_order')
            ->orderBy('a.answer_order')
            ->select([
                'q.id   as question_id',
                'q.uuid as question_uuid',
                'q.quiz_id',
                'q.quiz_uuid',
                'q.question_title',
                'q.question_description',
                'q.answer_explanation',
                'q.question_type',
                'q.question_mark',
                'q.question_difficulty as question_difficulty',     // <-- fix
                'q.question_settings',
                'q.question_order',
                'q.created_at as question_created_at',

                'a.id   as answer_id',
                'a.uuid as answer_uuid',
                'a.answer_title',
                'a.is_correct',
                'a.answer_order',
                'a.belongs_question_type',
                'a.belongs_question_uuid',
                'a.image_id',
                'a.answer_two_gap_match',
                'a.answer_view_format',
                'a.answer_settings',
            ])
            ->get();

        $questions = [];
        foreach ($rows as $r) {
            $qid = (int) $r->question_id;
            if (! isset($questions[$qid])) {
                $questions[$qid] = [
                    'question_id'          => $qid,
                    'question_uuid'        => $r->question_uuid,
                    'quiz_id'              => (int) $r->quiz_id,
                    'quiz_uuid'            => (string) $r->quiz_uuid,
                    'question_title'       => $r->question_title,
                    'question_description' => $r->question_description,
                    'answer_explanation'   => $r->answer_explanation,
                    'question_type'        => $r->question_type,
                    'question_mark'        => (int) $r->question_mark,
                    'question_difficulty'  => $r->question_difficulty ?: 'medium', // <-- NEW: surfaced to API
                    'question_settings'    => $r->question_settings,
                    'question_order'       => (int) $r->question_order,
                    'created_at'           => $r->question_created_at,
                    'answers'              => [],
                ];
            }
            if ($r->answer_id !== null) {
                $questions[$qid]['answers'][] = [
                    'answer_id'             => (int) $r->answer_id,
                    'answer_uuid'           => $r->answer_uuid,
                    'answer_title'          => $r->answer_title,
                    'is_correct'            => (bool) $r->is_correct,
                    'answer_order'          => (int) ($r->answer_order ?? 0),
                    'belongs_question_type' => $r->belongs_question_type,
                    'belongs_question_uuid' => $r->belongs_question_uuid,
                    'image_id'              => $r->image_id ? (int)$r->image_id : null,
                    'answer_two_gap_match'  => $r->answer_two_gap_match,
                    'answer_view_format'    => $r->answer_view_format,
                    'answer_settings'       => $r->answer_settings,
                ];
            }
        }

        return response()->json([
            'success' => true,
            'data'    => array_values($questions),
        ]);
    }

    /* =========================================================
     |  GET /api/quizz/questions/{key}   (key = id or uuid)
     |  One question (+answers)
     * ========================================================= */
    public function show(Request $request, $key)
    {
        if ($resp = $this->requireRole($request, ['admin','super_admin'])) return $resp;

        $id = $this->resolveQuestionIdByKey($key);
        if (!$id) {
            return response()->json(['success'=>false,'message'=>"No question found with key {$key}."], 404);
        }

        $rows = DB::table('quizz_questions as q')
            ->leftJoin('quizz_question_answers as a', 'q.id', '=', 'a.belongs_question_id')
            ->where('q.id', $id)
            ->orderBy('a.answer_order')
            ->select([
                'q.id   as question_id',
                'q.uuid as question_uuid',
                'q.quiz_id',
                'q.quiz_uuid',
                'q.question_title',
                'q.question_description',
                'q.answer_explanation',
                'q.question_type',
                'q.question_mark',
                'q.question_difficulty as question_difficulty',     // <-- fix
                'q.question_settings',
                'q.question_order',
                'q.created_at as question_created_at',

                'a.id   as answer_id',
                'a.uuid as answer_uuid',
                'a.answer_title',
                'a.is_correct',
                'a.answer_order',
                'a.belongs_question_type',
                'a.belongs_question_uuid',
                'a.image_id',
                'a.answer_two_gap_match',
                'a.answer_view_format',
                'a.answer_settings',
            ])
            ->get();

        if ($rows->isEmpty()) {
            return response()->json(['success'=>false,'message'=>"No question found with ID {$id}."], 404);
        }

        $first = $rows->first();
        $question = [
            'question_id'          => (int) $first->question_id,
            'question_uuid'        => (string) $first->question_uuid,
            'quiz_id'              => (int) $first->quiz_id,
            'quiz_uuid'            => (string) $first->quiz_uuid,
            'question_title'       => $first->question_title,
            'question_description' => $first->question_description,
            'answer_explanation'   => $first->answer_explanation,
            'question_type'        => $first->question_type,
            'question_mark'        => (int) $first->question_mark,
            'question_difficulty'  => (string) $first->question_difficulty,
            'question_settings'    => $first->question_settings,
            'question_order'       => (int) $first->question_order,
            'created_at'           => $first->question_created_at,
            'answers'              => [],
        ];

        foreach ($rows as $r) {
            if ($r->answer_id !== null) {
                $question['answers'][] = [
                    'answer_id'             => (int) $r->answer_id,
                    'answer_uuid'           => $r->answer_uuid,
                    'answer_title'          => $r->answer_title,
                    'is_correct'            => (bool) $r->is_correct,
                    'answer_order'          => (int) ($r->answer_order ?? 0),
                    'belongs_question_type' => $r->belongs_question_type,
                    'belongs_question_uuid' => $r->belongs_question_uuid,
                    'image_id'              => $r->image_id ? (int)$r->image_id : null,
                    'answer_two_gap_match'  => $r->answer_two_gap_match,
                    'answer_view_format'    => $r->answer_view_format,
                    'answer_settings'       => $r->answer_settings,
                ];
            }
        }

        return response()->json(['success'=>true,'data'=>$question]);
    }

    /* =========================================================
     |  POST /api/quizz/questions
     |  Create question + answers
     |  Body may include either quiz_id or quiz (uuid|id)
     * ========================================================= */
    public function store(Request $request)
    {
        if ($resp = $this->requireRole($request, ['admin','super_admin'])) return $resp;
        $this->logWithActor('[Questions Store] begin', $request);

        $quiz = $this->resolveQuizIdAndUuid($request);
        if (!$quiz) {
            return response()->json(['success'=>false,'message'=>'quiz_id or quiz (uuid|id) is required'], 422);
        }

        $rules = [
            'question_title'         => ['required','string'],
            'question_description'   => ['nullable','string'],
            'answer_explanation'     => ['nullable','string'],
            'question_type'          => ['required', Rule::in(['mcq','multiple_choice','single_choice','true_false','fill_in_the_blank'])],
            'question_mark'          => ['required','integer','min:1'],
            'question_difficulty'    => ['nullable','string', Rule::in(self::DIFFICULTIES)],
            'question_settings'      => ['nullable','array'],
            'question_order'         => ['required','integer','min:1'],

            'answers'                        => ['required','array','min:1'],
            'answers.*.answer_title'         => ['nullable','string'],
            'answers.*.is_correct'           => ['required','boolean'],
            'answers.*.answer_order'         => ['nullable','integer'],
            'answers.*.belongs_question_type'=> ['nullable','string'],
            'answers.*.image_id'             => ['nullable','integer'],
            'answers.*.answer_two_gap_match' => ['nullable','string'],
            'answers.*.answer_view_format'   => ['nullable','string'],
            'answers.*.answer_settings'      => ['nullable','array'],
        ];
        $data = $request->validate($rules);

        $a = $this->actor($request);
        $ip = $request->ip();

        DB::beginTransaction();
        try {
            // 1) Insert question (with uuid + quiz_uuid + audit)
            $questionUuid = (string) Str::uuid();
            $qid = DB::table('quizz_questions')->insertGetId([
                'uuid'                 => $questionUuid,
                'quiz_id'              => $quiz['id'],
                'quiz_uuid'            => $quiz['uuid'] ?? null,
                'question_title'       => $data['question_title'],
                'question_description' => $data['question_description'] ?? null,
                'answer_explanation'   => $data['answer_explanation'] ?? null,
                'question_type'        => $this->mapType($data['question_type']),
                'question_mark'        => (int) $data['question_mark'],
                'question_difficulty'  => strtolower($data['question_difficulty'] ?? 'medium'), // <-- fix
                'question_settings'    => isset($data['question_settings'])
                                            ? json_encode($data['question_settings'], JSON_UNESCAPED_UNICODE)
                                            : null,
                'question_order'       => (int) $data['question_order'],
                'created_by'           => $a['id'] ?: null,
                'updated_by'           => $a['id'] ?: null,
                'created_at_ip'        => $ip,
                'created_at'           => now(),
                'updated_at'           => now(),
            ]);

            // 2) Insert answers (with uuid + belongs_question_uuid + audit)
            $rows = [];
            foreach ($data['answers'] as $ans) {
                $rows[] = [
                    'uuid'                    => (string) Str::uuid(),
                    'belongs_question_id'     => $qid,
                    'belongs_question_uuid'   => $questionUuid,
                    'belongs_question_type'   => $ans['belongs_question_type'] ?? $this->mapType($data['question_type']),
                    'answer_title'            => $ans['answer_title'] ?? null,
                    'is_correct'              => (bool) $ans['is_correct'],
                    'answer_order'            => isset($ans['answer_order']) ? (int)$ans['answer_order'] : 0,
                    'image_id'                => isset($ans['image_id']) ? (int)$ans['image_id'] : null,
                    'answer_two_gap_match'    => $ans['answer_two_gap_match'] ?? null,
                    'answer_view_format'      => $ans['answer_view_format'] ?? null,
                    'answer_settings'         => isset($ans['answer_settings'])
                                                    ? json_encode($ans['answer_settings'], JSON_UNESCAPED_UNICODE)
                                                    : null,
                    'created_by'              => $a['id'] ?: null,
                    'updated_by'              => $a['id'] ?: null,
                    'created_at_ip'           => $ip,
                    'created_at'              => now(),
                    'updated_at'              => now(),
                ];
            }
            if (!empty($rows)) {
                DB::table('quizz_question_answers')->insert($rows);
            }

            // 3) Recalculate quiz question count
            $this->recalcAndPersistQuizQuestionCount((int)$quiz['id']);

            DB::commit();

            $fresh = DB::table('quizz_questions')->where('id', $qid)->first();

            // ✅ ADDED LOG (no other change)
            $this->logActivity(
                $request,
                'store',
                'Created question: "'.($fresh->question_title ?? 'N/A').'"',
                'quizz_questions',
                $qid,
                ['quiz_id','question_title','question_type','question_mark','question_order','question_difficulty'],
                null,
                $fresh ? (array)$fresh : null
            );

            $this->persistNotification([
                'title'     => 'Question created',
                'message'   => 'A new question was added to a quiz.',
                'receivers' => $this->adminReceivers(),
                'metadata'  => [
                    'action'   => 'created',
                    'question' => ['id'=>$qid,'uuid'=>$questionUuid,'quiz_id'=>(int)$quiz['id'],'quiz_uuid'=>$quiz['uuid'] ?? null],
                ],
                'type'      => 'quizz_question',
                'priority'  => 'normal',
                'status'    => 'active',
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Question created successfully.',
                'data'    => ['question_id' => $qid, 'question_uuid' => $questionUuid]
            ], 201);

        } catch (\Throwable $e) {
            DB::rollBack();
            Log::error('[Questions Store] failed', ['error'=>$e->getMessage()]);
            return response()->json([
                'success' => false,
                'message' => 'Failed to create question.',
                'error'   => config('app.debug') ? $e->getMessage() : null
            ], 500);
        }
    }

    /* =========================================================
     |  PUT/PATCH /api/quizz/questions/{key}   (key = id or uuid)
     |  Update question (+replace answers if provided)
     * ========================================================= */
    public function update(Request $request, $key)
    {
        if ($resp = $this->requireRole($request, ['admin','super_admin'])) return $resp;

        $rules = [
            'question_title'            => ['sometimes','required','string'],
            'question_description'      => ['nullable','string'],
            'answer_explanation'        => ['nullable','string'],
            'question_type'             => ['sometimes','required', Rule::in(['mcq','multiple_choice','single_choice','true_false','fill_in_the_blank'])],
            'question_mark'             => ['sometimes','required','integer','min:1'],
            'question_difficulty'       => ['sometimes','string', Rule::in(self::DIFFICULTIES)],
            'question_settings'         => ['nullable','array'],
            'question_order'            => ['sometimes','required','integer','min:1'],

            'answers'                        => ['sometimes','array','min:1'],
            'answers.*.answer_title'         => ['nullable','string'],
            'answers.*.is_correct'           => ['required_with:answers','boolean'],
            'answers.*.answer_order'         => ['nullable','integer'],
            'answers.*.belongs_question_type'=> ['nullable','string'],
            'answers.*.image_id'             => ['nullable','integer'],
            'answers.*.answer_two_gap_match' => ['nullable','string'],
            'answers.*.answer_view_format'   => ['nullable','string'],
            'answers.*.answer_settings'      => ['nullable','array'],
        ];
        $data = $request->validate($rules);

        $a  = $this->actor($request);
        $ip = $request->ip();

        DB::beginTransaction();
        try {
            $id = $this->resolveQuestionIdByKey($key);
            if (!$id) {
                return response()->json(['success'=>false,'message'=>'Question not found'], 404);
            }

            $before = DB::table('quizz_questions')->where('id', $id)->first();
            if (!$before) {
                return response()->json(['success'=>false,'message'=>'Question not found'], 404);
            }

            $upd = [];
            if (array_key_exists('question_title', $data))       $upd['question_title']       = $data['question_title'];
            if (array_key_exists('question_description', $data)) $upd['question_description'] = $data['question_description'];
            if (array_key_exists('answer_explanation', $data))   $upd['answer_explanation']   = $data['answer_explanation'];
            if (array_key_exists('question_mark', $data))        $upd['question_mark']        = (int) $data['question_mark'];
            if (array_key_exists('question_difficulty', $data)) {
                $upd['question_difficulty'] = strtolower($data['question_difficulty']); // <-- fix
            }
            if (array_key_exists('question_order', $data))       $upd['question_order']       = (int) $data['question_order'];

            if (array_key_exists('question_type', $data)) {
                $upd['question_type'] = $this->mapType($data['question_type']);
            }
            if (array_key_exists('question_settings', $data)) {
                $upd['question_settings'] = $data['question_settings'] !== null
                    ? json_encode($data['question_settings'], JSON_UNESCAPED_UNICODE)
                    : null;
            }

            if (!empty($upd)) {
                $upd['updated_at'] = now();
                $upd['updated_by'] = $a['id'] ?: null;
                DB::table('quizz_questions')->where('id', $id)->update($upd);
            }

            // Replace answers if provided (and save audit)
            if (array_key_exists('answers', $data)) {
                $questionUuid = DB::table('quizz_questions')->where('id', $id)->value('uuid');

                DB::table('quizz_question_answers')->where('belongs_question_id', $id)->delete();

                $rows = [];
                $belongsType = $data['question_type'] ?? ($upd['question_type'] ?? ($before->question_type ?? 'mcq'));
                $belongsType = $this->mapType($belongsType);

                foreach ($data['answers'] as $ans) {
                    $rows[] = [
                        'uuid'                    => (string) Str::uuid(),
                        'belongs_question_id'     => $id,
                        'belongs_question_uuid'   => $questionUuid ?: null,
                        'belongs_question_type'   => $ans['belongs_question_type'] ?? $belongsType,
                        'answer_title'            => $ans['answer_title'] ?? null,
                        'is_correct'              => (bool) $ans['is_correct'],
                        'answer_order'            => isset($ans['answer_order']) ? (int)$ans['answer_order'] : 0,
                        'image_id'                => isset($ans['image_id']) ? (int)$ans['image_id'] : null,
                        'answer_two_gap_match'    => $ans['answer_two_gap_match'] ?? null,
                        'answer_view_format'      => $ans['answer_view_format'] ?? null,
                        'answer_settings'         => isset($ans['answer_settings'])
                                                        ? json_encode($ans['answer_settings'], JSON_UNESCAPED_UNICODE)
                                                        : null,
                        'created_by'              => $a['id'] ?: null,
                        'updated_by'              => $a['id'] ?: null,
                        'created_at_ip'           => $ip,
                        'created_at'              => now(),
                        'updated_at'              => now(),
                    ];
                }
                if (!empty($rows)) {
                    DB::table('quizz_question_answers')->insert($rows);
                }
            }

            DB::commit();

            $after = DB::table('quizz_questions')->where('id', $id)->first();

            // ✅ ADDED LOG (no other change)
            $this->logActivity(
                $request,
                'update',
                'Updated question: "'.($after->question_title ?? $before->question_title).'"',
                'quizz_questions',
                $id,
                array_keys($upd ?: ['answers']),
                $before ? (array)$before : null,
                $after ? (array)$after : null
            );

            $this->persistNotification([
                'title'     => 'Question updated',
                'message'   => 'A question was updated.',
                'receivers' => $this->adminReceivers(),
                'metadata'  => [
                    'action'   => 'updated',
                    'question' => ['id'=>$id,'uuid'=>$after->uuid ?? $before->uuid,'quiz_id'=>(int)($after->quiz_id ?? $before->quiz_id),'quiz_uuid'=>($after->quiz_uuid ?? $before->quiz_uuid)],
                ],
                'type'      => 'quizz_question',
                'priority'  => 'low',
                'status'    => 'active',
            ]);

            return response()->json(['success'=>true,'message'=>'Question updated successfully.'], 200);

        } catch (\Throwable $e) {
            DB::rollBack();
            Log::error('[Questions Update] failed', ['error'=>$e->getMessage()]);
            return response()->json(['success'=>false,'message'=>'Failed to update question.'], 500);
        }
    }

    /* =========================================================
     |  DELETE /api/quizz/questions/{key}   (key = id or uuid)
     |  Hard delete question + answers, then refresh quiz count
     * ========================================================= */
    public function destroy(Request $request, $key)
    {
        if ($resp = $this->requireRole($request, ['admin','super_admin'])) return $resp;

        DB::beginTransaction();
        try {
            $id = $this->resolveQuestionIdByKey($key);
            if (!$id) {
                return response()->json(['success'=>false,'message'=>'Question not found'], 404);
            }

            $question = DB::table('quizz_questions')->where('id', $id)->first();
            if (!$question) {
                return response()->json(['success'=>false,'message'=>'Question not found'], 404);
            }

            $quizId = (int) $question->quiz_id;

            DB::table('quizz_question_answers')->where('belongs_question_id', $id)->delete();
            $deleted = DB::table('quizz_questions')->where('id', $id)->delete();

            if (!$deleted) {
                throw new \RuntimeException('Question deletion failed');
            }

            $this->recalcAndPersistQuizQuestionCount($quizId);

            DB::commit();

            // ✅ ADDED LOG (no other change)
            $this->logActivity(
                $request,
                'destroy',
                'Deleted question: "'.($question->question_title ?? 'N/A').'"',
                'quizz_questions',
                $id,
                null,
                $question ? (array)$question : null,
                null
            );

            $this->persistNotification([
                'title'     => 'Question deleted',
                'message'   => 'A question was removed from a quiz.',
                'receivers' => $this->adminReceivers(),
                'metadata'  => [
                    'action'   => 'deleted',
                    'question' => ['id'=>$id,'uuid'=>$question->uuid ?? null,'quiz_id'=>$quizId,'quiz_uuid'=>$question->quiz_uuid ?? null],
                ],
                'type'      => 'quizz_question',
                'priority'  => 'normal',
                'status'    => 'active',
            ]);

            return response()->json(['success'=>true,'message'=>'Question deleted successfully.'], 200);

        } catch (\Throwable $e) {
            DB::rollBack();
            Log::error('[Questions Destroy] failed', ['error'=>$e->getMessage(), 'key'=>$key]);
            return response()->json([
                'success' => false,
                'message' => 'Failed to delete question.',
                'error'   => config('app.debug') ? $e->getMessage() : null
            ], 500);
        }
    }
}
