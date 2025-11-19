<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Response;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Carbon\Carbon;

class ExamController extends Controller
{
    /* ============================================
     | Auth helpers (student via personal tokens)
     |============================================ */
    private const USER_TYPE = 'App\\Models\\User';

    private function getUserFromToken(Request $request): ?object
    {
        $header = (string) $request->header('Authorization', '');
        $token  = null;
        if (stripos($header, 'Bearer ') === 0) {
            $token = trim(substr($header, 7));
        } else {
            $token = trim($header);
        }
        if ($token === '') return null;

        $hashed = hash('sha256', $token);

        $pat = DB::table('personal_access_tokens')
            ->where('token', $hashed)
            ->where('tokenable_type', self::USER_TYPE)
            ->first();

        if (!$pat) return null;

        // expiry (optional column)
        if (isset($pat->expires_at) && $pat->expires_at !== null) {
            try {
                if (now()->greaterThan(Carbon::parse($pat->expires_at))) {
                    DB::table('personal_access_tokens')->where('id', $pat->id)->delete();
                    return null;
                }
            } catch (\Throwable $e) {
                return null;
            }
        }

        $user = DB::table('users')
            ->where('id', $pat->tokenable_id)
            ->whereNull('deleted_at')
            ->first();

        if (!$user) return null;
        if (isset($user->status) && $user->status !== 'active') return null;

        return $user; // contains role; we’ll prefer role 'student' for taking exam
    }

    private function isStudent(object $user): bool
    {
        $role = mb_strtolower(preg_replace('/[^a-z0-9]+/i', '', (string)($user->role ?? '')));
        return in_array($role, ['student','std','stu'], true);
    }

    private function quizByKey(string|int $key): ?object
    {
        $q = DB::table('quizz')->whereNull('deleted_at');
        if (is_numeric($key)) $q->where('id', (int)$key);
        else $q->where('uuid', (string)$key);

        return $q->first();
    }

    private function normalizeText(?string $s): string
    {
        $s = (string)$s;
        $s = html_entity_decode($s, ENT_QUOTES | ENT_HTML5, 'UTF-8');
        $s = preg_replace('/\s+/u', ' ', trim($s));
        return mb_strtolower($s, 'UTF-8');
    }

    /* ============================================
     | POST /api/exam/start
     | body: { quiz: "uuid|id" }
     | Creates a running attempt with a strict server deadline
     |============================================ */
    public function start(Request $request)
    {
        $user = $this->getUserFromToken($request);
        if (!$user || !$this->isStudent($user)) {
            return response()->json(['success'=>false,'message'=>'Unauthorized (student token required)'], 401);
        }

        $v = Validator::make($request->all(), [
            'quiz' => ['required'] // uuid or id
        ]);
        if ($v->fails()) {
            return response()->json(['success'=>false,'errors'=>$v->errors()], 422);
        }

        $quiz = $this->quizByKey($request->input('quiz'));
        if (!$quiz) {
            return response()->json(['success'=>false,'message'=>'Quiz not found'], 404);
        }
        if (($quiz->status ?? 'active') !== 'active') {
            return response()->json(['success'=>false,'message'=>'Quiz is not active'], 409);
        }

        // Enforce per-quiz attempts
        $allowed = (int)($quiz->total_attempts ?? 1);
        $used    = (int) DB::table('quizz_results')
                        ->where('quiz_id', $quiz->id)
                        ->where('user_id', $user->id)
                        ->count();
        if ($used >= $allowed) {
            return response()->json([
                'success'=>false,
                'message'=>"Attempt limit reached ({$used}/{$allowed})"
            ], 429);
        }

        // If a running attempt exists, return it (idempotent start)
        $running = DB::table('quizz_attempts')
            ->where('quiz_id', $quiz->id)
            ->where('user_id', $user->id)
            ->where('status', 'running')
            ->orderByDesc('id')
            ->first();

        $now = Carbon::now();
        $durationMin = (int)($quiz->total_time ?? 0); // minutes
        if ($durationMin <= 0) {
            return response()->json(['success'=>false,'message'=>'Quiz has no total_time set'], 422);
        }
        $deadline = $now->copy()->addMinutes($durationMin);

        if ($running) {
            // If already timed out, auto finalize then create new (if attempts left)
            if ($now->gte(Carbon::parse($running->server_deadline_at))) {
                $this->autoFinalize($running);
            } else {
                return response()->json([
                    'success'=>true,
                    'attempt'=>[
                        'attempt_uuid'       => $running->uuid,
                        'quiz_id'            => (int)$quiz->id,
                        'quiz_uuid'          => (string)$quiz->uuid,
                        'quiz_name'          => (string)($quiz->quiz_name ?? 'Quiz'),
                        'total_time_sec'     => $durationMin * 60,
                        'server_end_at'      => (string)$running->server_deadline_at,
                        'time_left_sec'      => max(0, Carbon::parse($running->server_deadline_at)->diffInSeconds($now, false) * -1),
                    ]
                ], 200);
            }
        }

        $attemptUuid = (string) Str::uuid();
        $attemptId = DB::table('quizz_attempts')->insertGetId([
            'uuid'                => $attemptUuid,
            'quiz_id'             => (int)$quiz->id,
            'quiz_uuid'           => (string)($quiz->uuid ?? null),
            'user_id'             => (int)$user->id,
            'status'              => 'in_progress', 
            'total_time_sec'      => $durationMin * 60,
            'started_at'          => $now,
            'server_deadline_at'  => $deadline,
            'current_question_id' => null,
            'current_q_started_at'=> null,
            'last_activity_at'    => $now,
            'created_at'          => $now,
            'updated_at'          => $now,
        ]);

        return response()->json([
            'success'=>true,
            'attempt'=>[
                'attempt_id'         => $attemptId,
                'attempt_uuid'       => $attemptUuid,
                'quiz_id'            => (int)$quiz->id,
                'quiz_uuid'          => (string)$quiz->uuid,
                'quiz_name'          => (string)($quiz->quiz_name ?? 'Quiz'),
                'total_time_sec'     => $durationMin * 60,
                'server_end_at'      => (string)$deadline,
                'time_left_sec'      => max(0, $deadline->diffInSeconds($now, false) * -1),
            ]
        ], 201);
    }

    /* ============================================
     | GET /api/exam/attempts/{attempt}/questions
     | Returns all questions (without is_correct)
     |============================================ */
    public function questions(Request $request, string $attemptUuid)
    {
        $user = $this->getUserFromToken($request);
        if (!$user) return response()->json(['success'=>false,'message'=>'Unauthorized'], 401);

        $attempt = DB::table('quizz_attempts')->where('uuid', $attemptUuid)->first();
        if (!$attempt || (int)$attempt->user_id !== (int)$user->id) {
            return response()->json(['success'=>false,'message'=>'Attempt not found'], 404);
        }

        // auto-finalize if timed out
        if ($this->deadlinePassed($attempt)) {
            $attempt = $this->autoFinalize($attempt, true); // returns fresh
        }

        $rows = DB::table('quizz_questions as q')
            ->leftJoin('quizz_question_answers as a', 'a.belongs_question_id', '=', 'q.id')
            ->where('q.quiz_id', $attempt->quiz_id)
            ->orderBy('q.question_order')
            ->orderBy('a.answer_order')
            ->select([
                'q.id as question_id',
                'q.question_title',
                'q.question_description',
                'q.answer_explanation',
                'q.question_type',
                'q.question_mark',
                'q.question_order',
                DB::raw("(
                    SELECT COUNT(*) FROM quizz_question_answers
                    WHERE belongs_question_id = q.id AND is_correct = 1
                ) as correct_count"),

                'a.id as answer_id',
                'a.answer_title',
                'a.answer_order',
            ])
            ->get();

        $questions = [];
        foreach ($rows as $r) {
            $qid = (int)$r->question_id;
            if (!isset($questions[$qid])) {
                $questions[$qid] = [
                    'question_id'   => $qid,
                    'question_title'=> $r->question_title,
                    'question_description'=> $r->question_description,
                    'question_type' => $r->question_type,
                    'question_mark' => (int)$r->question_mark,
                    'question_order'=> (int)$r->question_order,
                    'has_multiple_correct_answer' => ((int)$r->correct_count > 1),
                    'answers' => [],
                ];
            }
            if ($r->answer_id !== null) {
                $questions[$qid]['answers'][] = [
                    'answer_id'   => (int)$r->answer_id,
                    'answer_title'=> $r->answer_title,
                    'answer_order'=> (int)($r->answer_order ?? 0),
                ];
            }
        }

        // student’s saved selections (to allow restore)
        $saved = DB::table('quizz_attempt_answers')
            ->where('attempt_id', $attempt->id)
            ->pluck('selected_raw', 'question_id');

        $selections = [];
        foreach ($saved as $qid => $json) {
            try { $selections[$qid] = json_decode($json, true); }
            catch (\Throwable $e) { $selections[$qid] = null; }
        }

        return response()->json([
            'success'=>true,
            'attempt'=>[
                'status'          => $attempt->status,
                'time_left_sec'   => $this->timeLeftSec($attempt),
                'server_end_at'   => (string)$attempt->server_deadline_at,
            ],
            'questions'=> array_values($questions),
            'selections'=> $selections
        ], 200);
    }

    /* ============================================
     | POST /api/exam/attempts/{attempt}/answer
     | body: { question_id:int, selected:int|int[]|string }
     | Persists selection + time slice (server-side)
     |============================================ */
    public function saveAnswer(Request $request, string $attemptUuid)
    {
        $user = $this->getUserFromToken($request);
        if (!$user) return response()->json(['success'=>false,'message'=>'Unauthorized'], 401);

        $v = Validator::make($request->all(), [
            'question_id' => ['required','integer','min:1'],
            'selected'    => ['nullable'], // int | int[] | string
        ]);
        if ($v->fails()) return response()->json(['success'=>false,'errors'=>$v->errors()], 422);

        $attempt = DB::table('quizz_attempts')->where('uuid', $attemptUuid)->first();
        if (!$attempt || (int)$attempt->user_id !== (int)$user->id) {
            return response()->json(['success'=>false,'message'=>'Attempt not found'], 404);
        }
       if ($attempt->status !== 'in_progress') {
    return response()->json(['success'=>false,'message'=>'Attempt is not running'], 409);
}

        $now = Carbon::now();

        // is deadline passed?
        if ($this->deadlinePassed($attempt)) {
            $attempt = $this->autoFinalize($attempt, true);
            return response()->json([
                'success'=>false,
                'message'=>'Time over — attempt auto-submitted',
                'attempt'=>['status'=>$attempt->status,'time_left_sec'=>0]
            ], 409);
        }

        $questionId = (int)$request->input('question_id');

        // Validate question belongs to this quiz
        $exists = DB::table('quizz_questions')
            ->where('id', $questionId)
            ->where('quiz_id', $attempt->quiz_id)
            ->exists();
        if (!$exists) {
            return response()->json(['success'=>false,'message'=>'Invalid question'], 422);
        }

        DB::beginTransaction();
        try {
            // Attribute elapsed time to previous current_question (if any)
            if ($attempt->current_question_id && $attempt->current_q_started_at) {
                $prevQ = (int)$attempt->current_question_id;
                $slice = max(0, $now->diffInSeconds(Carbon::parse($attempt->current_q_started_at)));
                if ($slice > 0) {
                    // upsert time on prev question row
                    $row = DB::table('quizz_attempt_answers')
                        ->where('attempt_id', $attempt->id)
                        ->where('question_id', $prevQ)
                        ->lockForUpdate()
                        ->first();

                    if ($row) {
                        DB::table('quizz_attempt_answers')
                            ->where('id', $row->id)
                            ->update([
                                'time_spent_sec' => (int)$row->time_spent_sec + $slice,
                                'updated_at'     => $now,
                            ]);
                    } else {
                        DB::table('quizz_attempt_answers')->insert([
                            'attempt_id'     => $attempt->id,
                            'question_id'    => $prevQ,
                            'selected_raw'   => json_encode(null),
                            'time_spent_sec' => $slice,
                            'created_at'     => $now,
                            'updated_at'     => $now,
                        ]);
                    }
                }
            }

            // Upsert current question selection
            $selected = $request->input('selected', null);
            $selectedJson = json_encode($selected);

            $row = DB::table('quizz_attempt_answers')
                ->where('attempt_id', $attempt->id)
                ->where('question_id', $questionId)
                ->lockForUpdate()
                ->first();

            if ($row) {
                DB::table('quizz_attempt_answers')
                    ->where('id', $row->id)
                    ->update([
                        'selected_raw' => $selectedJson,
                        'updated_at'   => $now,
                    ]);
            } else {
                DB::table('quizz_attempt_answers')->insert([
                    'attempt_id'     => $attempt->id,
                    'question_id'    => $questionId,
                    'selected_raw'   => $selectedJson,
                    'time_spent_sec' => 0,
                    'created_at'     => $now,
                    'updated_at'     => $now,
                ]);
            }

            // Switch "current question" to the one user is now on
            DB::table('quizz_attempts')->where('id', $attempt->id)->update([
                'current_question_id'  => $questionId,
                'current_q_started_at' => $now,
                'last_activity_at'     => $now,
                'updated_at'           => $now,
            ]);

            DB::commit();

            // refresh attempt minimal
            $attempt->current_question_id = $questionId;
            $attempt->current_q_started_at= $now;

            return response()->json([
                'success'=>true,
                'attempt'=>[
                    'time_left_sec' => $this->timeLeftSec($attempt),
                    'server_end_at' => (string)$attempt->server_deadline_at,
                ]
            ], 200);
        } catch (\Throwable $e) {
            DB::rollBack();
            Log::error('[Exam saveAnswer] failed', ['e'=>$e->getMessage()]);
            return response()->json(['success'=>false,'message'=>'Failed to save answer'], 500);
        }
    }

    /* ============================================
     | POST /api/exam/attempts/{attempt}/submit
     | Finalize attempt, score server-side, persist result
     |============================================ */
    public function submit(Request $request, string $attemptUuid)
    {
        $user = $this->getUserFromToken($request);
        if (!$user) return response()->json(['success'=>false,'message'=>'Unauthorized'], 401);

        $attempt = DB::table('quizz_attempts')->where('uuid', $attemptUuid)->first();
        if (!$attempt || (int)$attempt->user_id !== (int)$user->id) {
            return response()->json(['success'=>false,'message'=>'Attempt not found'], 404);
        }

        // If already closed, return summary
        if (in_array($attempt->status, ['submitted','auto_submitted'], true)) {
            $summary = $this->resultSummaryForAttempt($attempt);
            return response()->json(['success'=>true] + $summary, 200);
        }

        // If timed out, auto-finalize first
        if ($this->deadlinePassed($attempt)) {
            $attempt = $this->autoFinalize($attempt, true);
            $summary = $this->resultSummaryForAttempt($attempt);
            return response()->json(['success'=>true] + $summary, 200);
        }

        // attribute final time slice for the currently open question
        $now = Carbon::now();
        DB::beginTransaction();
        try {
            if ($attempt->current_question_id && $attempt->current_q_started_at) {
                $prevQ = (int)$attempt->current_question_id;
                $slice = max(0, $now->diffInSeconds(Carbon::parse($attempt->current_q_started_at)));
                if ($slice > 0) {
                    $row = DB::table('quizz_attempt_answers')
                        ->where('attempt_id', $attempt->id)
                        ->where('question_id', $prevQ)
                        ->lockForUpdate()
                        ->first();

                    if ($row) {
                        DB::table('quizz_attempt_answers')->where('id', $row->id)->update([
                            'time_spent_sec' => (int)$row->time_spent_sec + $slice,
                            'updated_at'     => $now,
                        ]);
                    } else {
                        DB::table('quizz_attempt_answers')->insert([
                            'attempt_id'     => $attempt->id,
                            'question_id'    => $prevQ,
                            'selected_raw'   => json_encode(null),
                            'time_spent_sec' => $slice,
                            'created_at'     => $now,
                            'updated_at'     => $now,
                        ]);
                    }
                }
            }

            // Score & persist result
            $scored = $this->scoreAttempt($attempt->id);
            $publish = $this->shouldPublishToStudent((int)$attempt->quiz_id);

            // store result snapshot
            $resultId = DB::table('quizz_results')->insertGetId([
                'quiz_id'            => (int)$attempt->quiz_id,
                'user_id'            => (int)$attempt->user_id,
                'marks_obtained'     => (int)$scored['marks_obtained'],
                'total_marks'        => (int)$scored['total_marks'],
                'attempt_number'     => (int)$this->attemptNumberForUser((int)$attempt->quiz_id, (int)$attempt->user_id) + 1,
                'students_answer'    => json_encode($scored['answers'], JSON_UNESCAPED_UNICODE),
                'publish_to_student' => $publish ? 1 : 0,
                'created_at'         => $now,
                'updated_at'         => $now,
            ]);

            DB::table('quizz_attempts')->where('id', $attempt->id)->update([
                'status'       => 'submitted',
                'finished_at'  => $now,
                'updated_at'   => $now,
                'result_id'    => $resultId,
            ]);

            DB::commit();

            // fresh attempt row
            $attempt = DB::table('quizz_attempts')->where('id', $attempt->id)->first();
            $summary = $this->resultSummaryForAttempt($attempt);

            return response()->json(['success'=>true] + $summary, 201);
        } catch (\Throwable $e) {
            DB::rollBack();
            Log::error('[Exam submit] failed', ['e'=>$e->getMessage()]);
            return response()->json(['success'=>false,'message'=>'Failed to submit'], 500);
        }
    }

    /* ============================================
     | GET /api/exam/attempts/{attempt}/status
     | Returns time_left; auto-finalizes if expired
     |============================================ */
    public function status(Request $request, string $attemptUuid)
    {
        $user = $this->getUserFromToken($request);
        if (!$user) return response()->json(['success'=>false,'message'=>'Unauthorized'], 401);

        $attempt = DB::table('quizz_attempts')->where('uuid', $attemptUuid)->first();
        if (!$attempt || (int)$attempt->user_id !== (int)$user->id) {
            return response()->json(['success'=>false,'message'=>'Attempt not found'], 404);
        }

        if ($this->deadlinePassed($attempt) && $attempt->status === 'in_progress') {
    $attempt = $this->autoFinalize($attempt, true);
}

        return response()->json([
            'success'=>true,
            'attempt'=>[
                'status'        => $attempt->status,
                'time_left_sec' => $this->timeLeftSec($attempt),
                'server_end_at' => (string)$attempt->server_deadline_at,
            ]
        ], 200);
    }

    /* ============================================
     | GET /api/exam/results/{id}/answer-sheet
     | Simple HTML stream (print → PDF in browser)
     |============================================ */
    public function answerSheet(int $resultId)
    {
        $res = DB::table('quizz_results')->where('id', $resultId)->first();
        if (!$res) abort(404, 'Result not found');

        $user  = DB::table('users')->where('id', $res->user_id)->first();
        $quiz  = DB::table('quizz')->where('id', $res->quiz_id)->first();
        $ans   = json_decode($res->students_answer ?? '[]', true) ?: [];

        $questions = DB::table('quizz_questions')
            ->where('quiz_id', $res->quiz_id)
            ->orderBy('question_order')
            ->get();

        $answerRows = DB::table('quizz_question_answers')
            ->whereIn('belongs_question_id', $questions->pluck('id'))
            ->orderBy('belongs_question_id')
            ->orderBy('answer_order')
            ->get()
            ->groupBy('belongs_question_id');

        $totalMarks = (int) $questions->sum('question_mark');
        $pct = $totalMarks ? round(((int)$res->marks_obtained / $totalMarks) * 100) : 0;
        $passFail = $pct >= 60 ? 'PASS' : 'FAIL';

        return Response::streamDownload(function () use ($user,$quiz,$ans,$questions,$answerRows,$res,$totalMarks,$pct,$passFail) {
            $safe = fn($t) => htmlspecialchars((string)$t, ENT_QUOTES, 'UTF-8');
            echo "<!doctype html><html><head><meta charset='utf-8'><title>Answer Sheet</title>
<style>
@page { size:A4; margin:0 }
body{font-family:Arial,Helvetica,sans-serif;margin:0;padding:20px;color:#222}
h1,h2{margin:6px 0}
.card{background:#fff;border:1px solid #e5e7eb;border-radius:8px;padding:14px;margin-bottom:12px}
.badge{display:inline-block;padding:4px 8px;border-radius:14px;background:#eef2ff}
.meta{font-size:13px;color:#666}
.sep{height:1px;background:#eee;margin:10px 0}
.correct{color:#166534}
.wrong{color:#991b1b}
</style></head><body>";

            echo "<div class='card'><h1>{$safe($quiz->quiz_name ?? 'Exam')}</h1>
<div class='meta'>Student: {$safe($user->name ?? ('#'.$user->id))}</div>
<div class='meta'>Score: <b>{$res->marks_obtained}/{$totalMarks}</b> • {$pct}% • <span class='badge'>{$passFail}</span></div>
</div>";

            foreach ($questions as $q) {
                $qAns = $answerRows[$q->id] ?? collect();
                // correct ids
                $correctIds = $qAns->where('is_correct', 1)->pluck('id')->values()->all();

                // student selection
                $stuSel = null;
                foreach ($ans as $row) {
                    if ((int)($row['question_id'] ?? 0) === (int)$q->id) {
                        $stuSel = $row['selected'] ?? null;
                        break;
                    }
                }

                // derive correctness (for print)
                $correct = false;
                if ($q->question_type === 'fill_in_the_blank') {
                    $correctText = '';
                    foreach ($qAns as $a) {
                        $src = $a->answer_two_gap_match ?? $a->answer_title;
                        $correctText = $src ? (string)$src : $correctText;
                        if ($this->normalizeText((string)$stuSel) === $this->normalizeText($src)) {
                            $correct = true; break;
                        }
                    }
                    $stuDisplay = (string)$stuSel;
                    $corrDisplay= $correctText;
                } else {
                    if (is_array($stuSel)) {
                        $l = $stuSel; sort($l);
                        $r = $correctIds; sort($r);
                        $correct = ($l === $r);
                        $labels = [];
                        foreach ($qAns as $a) if (in_array($a->id, $l)) $labels[] = $a->answer_title;
                        $stuDisplay = implode(', ', $labels);
                    } else {
                        $correct = ((int)$stuSel === (int)($correctIds[0] ?? -1));
                        $label = '';
                        foreach ($qAns as $a) if ((int)$a->id === (int)$stuSel) { $label = $a->answer_title; break; }
                        $stuDisplay = $label;
                    }
                    $corrDisplay = '';
                    foreach ($qAns as $a) if (in_array($a->id, $correctIds)) { $corrDisplay = $a->answer_title; break; }
                }

                echo "<div class='card'><div><b>Q{$q->question_order}.</b> {$safe($q->question_title)}</div>
<div class='meta'>Marks: {$q->question_mark} • Type: {$q->question_type}</div><div class='sep'></div>";
                if ($correct) {
                    echo "<div class='correct'><b>Correct</b></div>";
                } else {
                    echo "<div class='wrong'><b>Incorrect</b></div>";
                    echo "<div>Correct: {$safe($corrDisplay)}</div>";
                }
                if ($stuDisplay !== '') echo "<div>Your answer: {$safe($stuDisplay)}</div>";
                echo "</div>";
            }
            echo "</body></html>";
        }, "answer_sheet_{$resultId}.html", ['Content-Type' => 'text/html']);
    }

    /* ==================== internals ==================== */

    private function deadlinePassed(object $attempt): bool
    {
        try { return Carbon::now()->gte(Carbon::parse($attempt->server_deadline_at)); }
        catch (\Throwable $e) { return false; }
    }

    private function timeLeftSec(object $attempt): int
    {
        try {
            $left = Carbon::now()->diffInSeconds(Carbon::parse($attempt->server_deadline_at), false);
            return max(0, $left);
        } catch (\Throwable $e) {
            return 0;
        }
    }

    private function autoFinalize(object $attempt, bool $refresh = false): object
    {
        $now = Carbon::now();

        DB::beginTransaction();
        try {
            // attribute last open slice (if any) up to deadline
            if ($attempt->current_question_id && $attempt->current_q_started_at) {
                $prevQ = (int)$attempt->current_question_id;
                $slice = max(0, Carbon::parse($attempt->server_deadline_at)->diffInSeconds(Carbon::parse($attempt->current_q_started_at), false));
                $slice = max(0, min($slice, $attempt->total_time_sec)); // guard
                if ($slice > 0) {
                    $row = DB::table('quizz_attempt_answers')
                        ->where('attempt_id', $attempt->id)
                        ->where('question_id', $prevQ)
                        ->lockForUpdate()
                        ->first();
                    if ($row) {
                        DB::table('quizz_attempt_answers')->where('id', $row->id)->update([
                            'time_spent_sec' => (int)$row->time_spent_sec + $slice,
                            'updated_at'     => $now,
                        ]);
                    } else {
                        DB::table('quizz_attempt_answers')->insert([
                            'attempt_id'     => $attempt->id,
                            'question_id'    => $prevQ,
                            'selected_raw'   => json_encode(null),
                            'time_spent_sec' => $slice,
                            'created_at'     => $now,
                            'updated_at'     => $now,
                        ]);
                    }
                }
            }

            $scored = $this->scoreAttempt($attempt->id);
            $publish = $this->shouldPublishToStudent((int)$attempt->quiz_id);

            $resultId = DB::table('quizz_results')->insertGetId([
                'quiz_id'            => (int)$attempt->quiz_id,
                'user_id'            => (int)$attempt->user_id,
                'marks_obtained'     => (int)$scored['marks_obtained'],
                'total_marks'        => (int)$scored['total_marks'],
                'attempt_number'     => (int)$this->attemptNumberForUser((int)$attempt->quiz_id, (int)$attempt->user_id) + 1,
                'students_answer'    => json_encode($scored['answers'], JSON_UNESCAPED_UNICODE),
                'publish_to_student' => $publish ? 1 : 0,
                'created_at'         => $now,
                'updated_at'         => $now,
            ]);

            DB::table('quizz_attempts')->where('id', $attempt->id)->update([
                'status'      => 'auto_submitted',
                'finished_at' => $now,
                'updated_at'  => $now,
                'result_id'   => $resultId,
            ]);

            DB::commit();

            return $refresh
                ? DB::table('quizz_attempts')->where('id',$attempt->id)->first()
                : $attempt;

        } catch (\Throwable $e) {
            DB::rollBack();
            Log::error('[Exam autoFinalize] failed', ['e'=>$e->getMessage()]);
            return $attempt;
        }
    }

    private function attemptNumberForUser(int $quizId, int $userId): int
    {
        return (int) DB::table('quizz_results')
            ->where('quiz_id', $quizId)
            ->where('user_id', $userId)
            ->count();
    }

    private function shouldPublishToStudent(int $quizId): bool
    {
        $q = DB::table('quizz')->where('id', $quizId)->first(['result_set_up_type','result_release_date']);
        $type = (string)($q->result_set_up_type ?? 'Immediately');
        if ($type === 'Immediately' || $type === 'Now') return true;
        if ($type === 'Schedule' && !empty($q->result_release_date)) {
            try {
                return Carbon::now()->gte(Carbon::parse($q->result_release_date));
            } catch (\Throwable $e) { return false; }
        }
        return false;
    }

    /**
     * Score attempt entirely server-side.
     * Returns: ['marks_obtained'=>int,'total_marks'=>int,'answers'=>[{question_id,selected}...]]
     */
    private function scoreAttempt(int $attemptId): array
    {
        $attempt = DB::table('quizz_attempts')->where('id', $attemptId)->first();
        if (!$attempt) return ['marks_obtained'=>0,'total_marks'=>0,'answers'=>[]];

        $qRows = DB::table('quizz_questions')
            ->where('quiz_id', $attempt->quiz_id)
            ->orderBy('question_order')
            ->get();

        $aRows = DB::table('quizz_question_answers')
            ->whereIn('belongs_question_id', $qRows->pluck('id'))
            ->orderBy('belongs_question_id')
            ->orderBy('answer_order')
            ->get()
            ->groupBy('belongs_question_id');

        $saved = DB::table('quizz_attempt_answers')
            ->where('attempt_id', $attemptId)
            ->get()
            ->keyBy('question_id');

        $marksObtained = 0;
        $totalMarks    = (int) $qRows->sum('question_mark');
        $snapshot      = [];

        foreach ($qRows as $q) {
            $qid = (int)$q->id;
            $selRaw = null;
            if (isset($saved[$qid])) {
                try { $selRaw = json_decode($saved[$qid]->selected_raw ?? 'null', true); }
                catch (\Throwable $e) { $selRaw = null; }
            }
            $snapshot[] = ['question_id'=>$qid,'selected'=>$selRaw];

            $answers = $aRows[$qid] ?? collect();
            $type    = (string)$q->question_type;

            if ($type === 'fill_in_the_blank') {
                $stu = $this->normalizeText(is_array($selRaw) ? implode(' ', $selRaw) : (string)$selRaw);
                $correct = false;
                foreach ($answers as $a) {
                    $needle = $this->normalizeText($a->answer_two_gap_match ?? $a->answer_title);
                    if ($stu !== '' && $needle !== '' && $stu === $needle) { $correct = true; break; }
                }
                if ($correct) $marksObtained += (int)$q->question_mark;
                continue;
            }

            // MCQ / true_false
            $correctIds = $answers->where('is_correct',1)->pluck('id')->values()->all();
            if (count($correctIds) > 1) {
                // multiple correct
                $l = is_array($selRaw) ? array_values(array_map('intval',$selRaw)) : [];
                sort($l);
                $r = $correctIds; sort($r);
                if ($l === $r) $marksObtained += (int)$q->question_mark;
            } else {
                // single correct
                if ((int)$selRaw === (int)($correctIds[0] ?? -1)) {
                    $marksObtained += (int)$q->question_mark;
                }
            }
        }

        return [
            'marks_obtained' => $marksObtained,
            'total_marks'    => $totalMarks,
            'answers'        => $snapshot,
        ];
    }

    private function resultSummaryForAttempt(object $attempt): array
    {
        $res = $attempt->result_id
            ? DB::table('quizz_results')->where('id', $attempt->result_id)->first()
            : null;

        return [
            'attempt' => [
                'status'        => $attempt->status,
                'finished_at'   => (string)($attempt->finished_at ?? ''),
                'result_id'     => $attempt->result_id ?? null,
            ],
            'result' => $res ? [
                'result_id'      => $res->id,
                'marks_obtained' => (int)$res->marks_obtained,
                'total_marks'    => (int)$res->total_marks,
                'percentage'     => ($res->total_marks ? round($res->marks_obtained / $res->total_marks * 100) : 0),
                'publish_to_student' => (int)$res->publish_to_student,
            ] : null,
        ];
    }
}
