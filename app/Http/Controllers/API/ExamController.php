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

    private function questionType(int $questionId): string
{
    return (string) (DB::table('quizz_questions')->where('id',$questionId)->value('question_type') ?? 'mcq');
}

private function batchQuizByKey(string|int $key, int $quizId): ?object
{
    $q = DB::table('batch_quizzes')
        ->where('quiz_id', $quizId);

    if (is_numeric($key)) {
        $q->where('id', (int) $key);
    } else {
        $q->where('uuid', (string) $key);
    }

    return $q->first();
}


private function isStudentInBatch(int $batchId, int $userId): bool
{
    return DB::table('batch_students')
        ->where('batch_id', $batchId)
        ->where('user_id', $userId)
        ->where('enrollment_status', 'enrolled')
        ->whereNull('deleted_at')
        ->exists();
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
        'quiz'       => ['required'],      // uuid or id of quizz
        'batch_quiz' => ['nullable'],      // uuid or id of batch_quizzes (when coming from batch)
    ]);
    if ($v->fails()) {
        return response()->json(['success'=>false,'errors'=>$v->errors()], 422);
    }

    // ---- Resolve quiz ----
    $quiz = $this->quizByKey($request->input('quiz'));
    if (!$quiz) {
        return response()->json(['success'=>false,'message'=>'Quiz not found'], 404);
    }
    if (($quiz->status ?? 'active') !== 'active') {
        return response()->json(['success'=>false,'message'=>'Quiz is not active'], 409);
    }

    // ---- Resolve optional batch context ----
    $batchKey  = $request->input('batch_quiz');          // can be uuid or id or null
    $batchQuiz = null;                                   // row from batch_quizzes
    $batch     = null;                                   // row from batches

    if (!empty($batchKey)) {
        $batchQuiz = $this->batchQuizByKey($batchKey, (int) $quiz->id);
        if (!$batchQuiz) {
            return response()->json([
                'success'=>false,
                'message'=>'Batch quiz not found for this quiz'
            ], 404);
        }

        // Load batch row
        $batch = DB::table('batches')
            ->where('id', $batchQuiz->batch_id)
            ->whereNull('deleted_at')
            ->first();

        if (!$batch) {
            return response()->json([
                'success'=>false,
                'message'=>'Batch not available'
            ], 404);
        }

        // Ensure this student belongs to that batch
        if (!$this->isStudentInBatch((int)$batchQuiz->batch_id, (int)$user->id)) {
            return response()->json([
                'success'=>false,
                'message'=>'You are not enrolled in this batch'
            ], 403);
        }
    }

    // ============================================
    //  Global attempt limit (quiz.total_attempts)
    // ============================================
    $globalAllowed = max(1, (int)($quiz->total_attempts ?? 1));

    $globalUsed = (int) DB::table('quizz_results')
        ->where('quiz_id', $quiz->id)
        ->where('user_id', $user->id)
        ->count();

    if ($globalUsed >= $globalAllowed) {
        return response()->json([
            'success' => false,
            'message' => "Attempt limit reached for this quiz ({$globalUsed}/{$globalAllowed})"
        ], 429);
    }

    // ============================================
    //  Batch-wise attempt limit (batch_quizzes.attempt_allowed)
    //  NOTE: batch_attempt_allowed is always <= globalAllowed
    // ============================================
    $batchAllowed = null;
    $batchUsed    = null;

    if ($batchQuiz) {
        $configured = (int)($batchQuiz->attempt_allowed ?? 0);

        if ($configured <= 0) {
            // if not set, fall back to global
            $batchAllowed = $globalAllowed;
        } else {
            // hard guard: cannot exceed quiz total_attempts
            $batchAllowed = min($configured, $globalAllowed);
        }

        // Count how many SUBMITTED attempts exist for this user in THIS batch_quiz
        // (join quizz_attempt_batch + quizz_results on attempt_id)
        $batchUsed = (int) DB::table('quizz_attempt_batch as qab')
            ->join('quizz_results as r', 'r.attempt_id', '=', 'qab.attempt_id')
            ->where('qab.batch_quiz_id', $batchQuiz->id)
            ->where('qab.user_id', $user->id)
            ->count();

        if ($batchUsed >= $batchAllowed) {
            return response()->json([
                'success' => false,
                'message' => "Attempt limit reached for this batch ({$batchUsed}/{$batchAllowed})"
            ], 429);
        }
    }

    // ============================================
    //  If a running attempt exists for this CONTEXT,
    //  return it (idempotent start)
    // ============================================
    if ($batchQuiz) {
        // when coming via batch, only pick attempts linked to this batch_quiz
        $running = DB::table('quizz_attempts as qa')
            ->join('quizz_attempt_batch as qab', 'qab.attempt_id', '=', 'qa.id')
            ->where('qa.quiz_id', $quiz->id)
            ->where('qa.user_id', $user->id)
            ->where('qa.status', 'in_progress')
            ->where('qab.batch_quiz_id', $batchQuiz->id)
            ->orderByDesc('qa.id')
            ->select('qa.*')
            ->first();
    } else {
        // standalone quiz context: attempts that are NOT mapped to any batch
        $running = DB::table('quizz_attempts as qa')
            ->leftJoin('quizz_attempt_batch as qab', 'qab.attempt_id', '=', 'qa.id')
            ->where('qa.quiz_id', $quiz->id)
            ->where('qa.user_id', $user->id)
            ->where('qa.status', 'in_progress')
            ->whereNull('qab.id')
            ->orderByDesc('qa.id')
            ->select('qa.*')
            ->first();
    }

    $now         = Carbon::now();
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

                    // extra info (front-end can ignore safely)
                    'batch_quiz_id'      => $batchQuiz->id ?? null,
                    'batch_attempt_used' => $batchUsed,
                    'batch_attempt_allowed' => $batchAllowed,
                ]
            ], 200);
        }
    }

    // ============================================
    //  Build per-attempt layout (same as before)
    // ============================================
    $qRows = DB::table('quizz_questions')
        ->where('quiz_id', $quiz->id)
        ->orderBy('question_order')
        ->get(['id','question_type']);

    if ($qRows->isEmpty()) {
        return response()->json(['success'=>false,'message'=>'Quiz has no questions'], 422);
    }

    $questionIds = $qRows->pluck('id')->map(fn($v) => (int)$v)->all();

    if (($quiz->is_question_random ?? 'no') === 'yes') {
        shuffle($questionIds);
    }

    $qTypes = [];
    foreach ($qRows as $qRow) {
        $qTypes[(int)$qRow->id] = (string)($qRow->question_type ?? '');
    }

    $aRows = DB::table('quizz_question_answers')
        ->whereIn('belongs_question_id', $questionIds)
        ->orderBy('belongs_question_id')
        ->orderBy('answer_order')
        ->get(['id','belongs_question_id','answer_order']);

    $answersByQ = $aRows->groupBy('belongs_question_id');

    $optionsOrder = [];
    foreach ($questionIds as $qid) {
        $answers = $answersByQ[$qid] ?? collect();
        if ($answers->isEmpty()) {
            $optionsOrder[$qid] = [];
            continue;
        }

        $answerIds = $answers->pluck('id')->map(fn($v)=> (int)$v)->all();
        $qType     = $qTypes[$qid] ?? '';

        if (($quiz->is_option_random ?? 'no') === 'yes' && $qType !== 'fill_in_the_blank') {
            shuffle($answerIds);
        }

        $optionsOrder[$qid] = $answerIds;
    }

    // ============================================
    //  Insert attempt row
    // ============================================
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
        'questions_order'     => json_encode($questionIds, JSON_UNESCAPED_UNICODE),
        'options_order'       => json_encode($optionsOrder, JSON_UNESCAPED_UNICODE),
        'created_at'          => $now,
        'updated_at'          => $now,
    ]);

    // ============================================
    //  Link attempt to batch (if any)
    // ============================================
if ($batchQuiz) {
    DB::table('quizz_attempt_batch')->insert([
        'uuid'          => (string) Str::uuid(),
        'quiz_id'       => (int) $quiz->id,
        'batch_id'      => (int) $batchQuiz->batch_id,
        'batch_quiz_id' => (int) $batchQuiz->id,
        'attempt_id'    => (int) $attemptId,
        'user_id'       => (int) $user->id,
        'came_from'     => 'batch',
        'metadata'      => json_encode([
            'batch_quiz_uuid' => $batchQuiz->uuid ?? null,
            'quiz_uuid'       => $quiz->uuid ?? null,
            'ip'              => $request->ip(),   // store IP inside JSON if you want
        ], JSON_UNESCAPED_UNICODE),
        'created_at'    => $now,
        'updated_at'    => $now,
    ]);
}


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

            // extra info (safe to ignore on UI)
            'batch_quiz_id'      => $batchQuiz->id ?? null,
            'batch_attempt_used' => $batchUsed,
            'batch_attempt_allowed' => $batchAllowed,
        ]
    ], 201);
}


public function myAttemptsForQuiz(Request $request, string $quizKey)
{
    // ---------- 1. Auth: only students via personal token ----------
    $user = $this->getUserFromToken($request);
    if (!$user || !$this->isStudent($user)) {
        return response()->json([
            'success' => false,
            'message' => 'Unauthorized (student token required)'
        ], 401);
    }

    // ---------- 2. Resolve quiz (id | uuid) ----------
    $quiz = $this->quizByKey($quizKey);
    if (!$quiz) {
        return response()->json([
            'success' => false,
            'message' => 'Quiz not found'
        ], 404);
    }

    // ---------- 3. Optional batch_quiz context ----------
    $batchKey  = $request->query('batch_quiz');   // id | uuid | null
    $batchQuiz = null;

    if (!empty($batchKey)) {
        $batchQuiz = $this->batchQuizByKey($batchKey, (int) $quiz->id);
        if (!$batchQuiz) {
            return response()->json([
                'success' => false,
                'message' => 'Batch quiz not found for this quiz'
            ], 404);
        }

        // Guard: student must belong to that batch
        if (!$this->isStudentInBatch((int)$batchQuiz->batch_id, (int)$user->id)) {
            return response()->json([
                'success' => false,
                'message' => 'You are not enrolled in this batch'
            ], 403);
        }
    }

    // ---------- 4. Build query: attempts (+ results) ----------
    $q = DB::table('quizz_attempts as qa')
        ->where('qa.quiz_id', $quiz->id)
        ->where('qa.user_id', $user->id);

    if ($batchQuiz) {
        // Attempts linked to this batch_quiz
        $q->join('quizz_attempt_batch as qab', function ($join) use ($batchQuiz, $user) {
            $join->on('qab.attempt_id', '=', 'qa.id')
                 ->where('qab.batch_quiz_id', (int)$batchQuiz->id)
                 ->where('qab.user_id', (int)$user->id);
        });
    } else {
        // Standalone quiz: attempts NOT linked to any batch for this user
        $q->leftJoin('quizz_attempt_batch as qab', function ($join) use ($user) {
            $join->on('qab.attempt_id', '=', 'qa.id')
                 ->where('qab.user_id', (int)$user->id);
        })->whereNull('qab.id');
    }

    // Attach results (if any)
    $q->leftJoin('quizz_results as qr', 'qr.attempt_id', '=', 'qa.id');

    $rows = $q->orderByDesc('qa.created_at')
        ->orderByDesc('qa.id')
        ->select([
            'qa.id as attempt_id',
            'qa.uuid as attempt_uuid',
            'qa.status',
            'qa.started_at',
            'qa.finished_at',
            'qa.server_deadline_at',
            'qa.total_time_sec',
            'qa.created_at',
            'qa.updated_at',

            'qr.id as result_id',
            'qr.marks_obtained',
            'qr.total_marks',
            'qr.percentage',
            'qr.attempt_number',
            'qr.publish_to_student',
        ])
        ->get();

    // Whether, *right now*, results are allowed to be visible (immediately / scheduled)
    $allowViewNow = $this->shouldPublishToStudent((int)$quiz->id);

    // ---------- 5. Map to clean payload ----------
    $attempts = $rows->map(function ($row) use ($allowViewNow) {
        $canView = false;
        if ($row->result_id) {
            $canView = ((int)$row->publish_to_student === 1) || $allowViewNow;
        }

        return [
            'attempt_id'         => (int) $row->attempt_id,
            'attempt_uuid'       => (string) $row->attempt_uuid,
            'status'             => (string) $row->status,
            'started_at'         => $row->started_at
                                    ? Carbon::parse($row->started_at)->toDateTimeString()
                                    : null,
            'finished_at'        => $row->finished_at
                                    ? Carbon::parse($row->finished_at)->toDateTimeString()
                                    : null,
            'created_at'         => $row->created_at
                                    ? Carbon::parse($row->created_at)->toDateTimeString()
                                    : null,
            'server_deadline_at' => $row->server_deadline_at
                                    ? Carbon::parse($row->server_deadline_at)->toDateTimeString()
                                    : null,
            'total_time_sec'     => (int) ($row->total_time_sec ?? 0),

            'result' => $row->result_id ? [
                'result_id'          => (int) $row->result_id,
                'marks_obtained'     => (int) $row->marks_obtained,
                'total_marks'        => (int) $row->total_marks,
                'percentage'         => $row->total_marks
                    ? (float) ($row->percentage ?? round(
                        $row->marks_obtained / max(1, $row->total_marks) * 100,
                        2
                    ))
                    : 0.0,
                'attempt_number'     => (int) ($row->attempt_number ?? 0),
                'publish_to_student' => (int) $row->publish_to_student,
                'can_view_detail'    => $canView,
            ] : null,
        ];
    })->values();

    // Quiz meta (helps UI)
    $totalMarks = (int) DB::table('quizz_questions')
        ->where('quiz_id', $quiz->id)
        ->sum('question_mark');

    return response()->json([
        'success'  => true,
        'quiz'     => [
            'id'                     => (int) $quiz->id,
            'uuid'                   => (string) $quiz->uuid,
            'name'                   => (string) ($quiz->quiz_name ?? 'Quiz'),
            'total_marks'            => $totalMarks,
            'total_attempts_allowed' => (int) ($quiz->total_attempts ?? 1),
        ],
        'batch'    => $batchQuiz ? [
            'batch_quiz_id'   => (int) $batchQuiz->id,
            'batch_quiz_uuid' => (string) $batchQuiz->uuid,
            'batch_id'        => (int) $batchQuiz->batch_id,
        ] : null,
        'attempts' => $attempts,
    ], 200);
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

    // 1) Fetch all questions + answers for this quiz
    $rows = DB::table('quizz_questions as q')
        ->leftJoin('quizz_question_answers as a', 'a.belongs_question_id', '=', 'q.id')
        ->where('q.quiz_id', $attempt->quiz_id)
        ->orderBy('q.question_order')    // base order; we’ll override by layout if present
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

    // 2) Build map: question_id => question data (+ answers)
    $questionsById = [];
    foreach ($rows as $r) {
        $qid = (int)$r->question_id;

        if (!isset($questionsById[$qid])) {
            $questionsById[$qid] = [
                'question_id'                 => $qid,
                'question_title'              => $r->question_title,
                'question_description'        => $r->question_description,
                'question_type'               => $r->question_type,
                'question_mark'               => (int)$r->question_mark,
                'question_order'              => (int)$r->question_order,
                'has_multiple_correct_answer' => ((int)$r->correct_count > 1),
                'answers'                     => [],
            ];
        }

        if ($r->answer_id !== null) {
            $questionsById[$qid]['answers'][] = [
                'answer_id'   => (int)$r->answer_id,
                'answer_title'=> $r->answer_title,
                'answer_order'=> (int)($r->answer_order ?? 0),
            ];
        }
    }

    // 3) Student’s saved selections (unchanged)
    $saved = DB::table('quizz_attempt_answers')
        ->where('attempt_id', $attempt->id)
        ->pluck('selected_raw', 'question_id');

    $selections = [];
    foreach ($saved as $qid => $json) {
        try { $selections[$qid] = json_decode($json, true); }
        catch (\Throwable $e) { $selections[$qid] = null; }
    }

    // 4) Decode stored layout (if any)
    $orderedQuestions = [];

    $layoutQuestions = null;
    $layoutOptions   = null;

    if (!empty($attempt->questions_order)) {
        try { $layoutQuestions = json_decode($attempt->questions_order, true); }
        catch (\Throwable $e) { $layoutQuestions = null; }
    }

    if (!empty($attempt->options_order)) {
        try { $layoutOptions = json_decode($attempt->options_order, true); }
        catch (\Throwable $e) { $layoutOptions = null; }
    }

    $hasLayout = is_array($layoutQuestions) && !empty($layoutQuestions);

    if ($hasLayout) {
        // Use the stored question order
        $usedQids = [];
        foreach ($layoutQuestions as $qid) {
            $qid = (int)$qid;
            if (!isset($questionsById[$qid])) continue;

            $qArr = $questionsById[$qid];

            // Reorder answers using stored options order (if present)
            if (is_array($layoutOptions)) {
                $keyInt = $qid;
                $keyStr = (string)$qid;
                $ansOrder = $layoutOptions[$keyInt] ?? ($layoutOptions[$keyStr] ?? null);

                if (is_array($ansOrder) && !empty($qArr['answers'])) {
                    $answersById = [];
                    foreach ($qArr['answers'] as $ans) {
                        $answersById[(int)$ans['answer_id']] = $ans;
                    }

                    $newAnswers = [];
                    foreach ($ansOrder as $aid) {
                        $aid = (int)$aid;
                        if (isset($answersById[$aid])) {
                            $newAnswers[] = $answersById[$aid];
                            unset($answersById[$aid]);
                        }
                    }

                    // Append any answers that weren't in the stored order
                    foreach ($answersById as $aRow) {
                        $newAnswers[] = $aRow;
                    }

                    $qArr['answers'] = $newAnswers;
                }
            }

            $orderedQuestions[] = $qArr;
            $usedQids[$qid] = true;
        }

        // In case there are questions without layout entries (edge-case)
        foreach ($questionsById as $qid => $qArr) {
            if (!isset($usedQids[$qid])) {
                $orderedQuestions[] = $qArr;
            }
        }
    } else {
        // Fallback: old attempts / layout missing → original behavior
        $orderedQuestions = array_values($questionsById);
    }

    return response()->json([
        'success'=>true,
        'attempt'=>[
            'status'          => $attempt->status,
            'time_left_sec'   => $this->timeLeftSec($attempt),
            'server_end_at'   => (string)$attempt->server_deadline_at,
        ],
        'questions'=> $orderedQuestions,
        'selections'=> $selections
    ], 200);
}


    /* ============================================
 | POST /api/exam/attempts/{attempt}/focus
 | body: { question_id:int }
 | Attributes elapsed time to previous question and
 | switches focus/start time to the given question.
 |============================================ */
public function focus(Request $request, string $attemptUuid)
{
    $user = $this->getUserFromToken($request);
    if (!$user) return response()->json(['success'=>false,'message'=>'Unauthorized'], 401);

    $v = Validator::make($request->all(), [
        'question_id' => ['required','integer','min:1'],
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
    if ($this->deadlinePassed($attempt)) {
        $attempt = $this->autoFinalize($attempt, true);
        return response()->json([
            'success'=>false,
            'message'=>'Time over — attempt auto-submitted',
            'attempt'=>['status'=>$attempt->status,'time_left_sec'=>0]
        ], 409);
    }

    $questionId = (int)$request->input('question_id');

    // Validate the question belongs to this quiz
    $qExists = DB::table('quizz_questions')
        ->where('id', $questionId)
        ->where('quiz_id', $attempt->quiz_id)
        ->exists();
    if (!$qExists) return response()->json(['success'=>false,'message'=>'Invalid question'], 422);

    DB::beginTransaction();
    try {
        // Attribute elapsed time to the previously focused question
        if ($attempt->current_question_id && $attempt->current_q_started_at) {
            $prevQ  = (int)$attempt->current_question_id;
            $slice  = max(0, $now->diffInSeconds(Carbon::parse($attempt->current_q_started_at)));
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
                        'question_type'  => $this->questionType($prevQ),
                        'selected_raw'   => json_encode(null),
                        'time_spent_sec' => $slice,
                        'created_at'     => $now,
                        'updated_at'     => $now,
                    ]);
                }
            }
        }

        // Switch focus to the requested question
        DB::table('quizz_attempts')->where('id', $attempt->id)->update([
            'current_question_id'  => $questionId,
            'current_q_started_at' => $now,
            'last_activity_at'     => $now,
            'updated_at'           => $now,
        ]);

        DB::commit();

        // Update local attempt snapshot for time_left calc
        $attempt->current_question_id  = $questionId;
        $attempt->current_q_started_at = $now;

        return response()->json([
            'success'=>true,
            'attempt'=>[
                'time_left_sec' => $this->timeLeftSec($attempt),
                'server_end_at' => (string)$attempt->server_deadline_at,
            ]
        ], 200);

    } catch (\Throwable $e) {
        DB::rollBack();
        Log::error('[Exam focus] failed', ['e'=>$e->getMessage()]);
        return response()->json(['success'=>false,'message'=>'Failed to set focus'], 500);
    }
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
        'selected'    => ['nullable'],
        'time_spent'  => ['nullable','integer','min:0'], // 🔹 NEW
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
    if ($this->deadlinePassed($attempt)) {
        $attempt = $this->autoFinalize($attempt, true);
        return response()->json([
            'success'=>false,
            'message'=>'Time over — attempt auto-submitted',
            'attempt'=>['status'=>$attempt->status,'time_left_sec'=>0]
        ], 409);
    }

    $questionId = (int)$request->input('question_id');

    // Fetch & validate question + type (REQUIRED)
    $qRow = DB::table('quizz_questions')
        ->where('id', $questionId)
        ->where('quiz_id', $attempt->quiz_id)
        ->first(['id','question_type']);
    if (!$qRow) return response()->json(['success'=>false,'message'=>'Invalid question'], 422);

    $qType = (string)($qRow->question_type ?? '');

    // 🔹 time slice sent from frontend
    $sliceFromClient = (int) $request->input('time_spent', 0);
    if ($sliceFromClient < 0) $sliceFromClient = 0;

    DB::beginTransaction();
    try {
        /* ============================================
         * 1) Apply client-reported time to THIS question
         * ============================================ */
        if ($sliceFromClient > 0) {
            $rowTime = DB::table('quizz_attempt_answers')
                ->where('attempt_id', $attempt->id)
                ->where('question_id', $questionId)
                ->lockForUpdate()
                ->first();

            if ($rowTime) {
                DB::table('quizz_attempt_answers')->where('id', $rowTime->id)->update([
                    'time_spent_sec' => (int)$rowTime->time_spent_sec + $sliceFromClient,
                    'updated_at'     => $now,
                ]);
            } else {
                DB::table('quizz_attempt_answers')->insert([
                    'attempt_id'     => $attempt->id,
                    'question_id'    => $questionId,
                    'question_type'  => $qType ?: $this->questionType($questionId),
                    'selected_raw'   => json_encode(null),
                    'time_spent_sec' => $sliceFromClient,
                    'created_at'     => $now,
                    'updated_at'     => $now,
                ]);
            }
        }

        /* =================================================
         * 2) (fallback) server-side slice ONLY if no client
         * ================================================= */
        if ($sliceFromClient === 0 && $attempt->current_question_id && $attempt->current_q_started_at) {
            $prevQ  = (int)$attempt->current_question_id;
            $slice  = max(0, $now->diffInSeconds(Carbon::parse($attempt->current_q_started_at)));
            if ($slice > 0) {
                $rowPrev = DB::table('quizz_attempt_answers')
                    ->where('attempt_id', $attempt->id)
                    ->where('question_id', $prevQ)
                    ->lockForUpdate()
                    ->first();

                if ($rowPrev) {
                    DB::table('quizz_attempt_answers')->where('id', $rowPrev->id)->update([
                        'time_spent_sec' => (int)$rowPrev->time_spent_sec + $slice,
                        'updated_at'     => $now,
                    ]);
                } else {
                    DB::table('quizz_attempt_answers')->insert([
                        'attempt_id'     => $attempt->id,
                        'question_id'    => $prevQ,
                        'question_type'  => $this->questionType($prevQ),
                        'selected_raw'   => json_encode(null),
                        'time_spent_sec' => $slice,
                        'created_at'     => $now,
                        'updated_at'     => $now,
                    ]);
                }
            }
        }

        /* ============================================
         * 3) Upsert current selection (your original code)
         * ============================================ */
        $selectedJson = json_encode($request->input('selected', null), JSON_UNESCAPED_UNICODE);

        $row = DB::table('quizz_attempt_answers')
            ->where('attempt_id', $attempt->id)
            ->where('question_id', $questionId)
            ->lockForUpdate()
            ->first();

        if ($row) {
            $upd = ['selected_raw'=>$selectedJson, 'updated_at'=>$now];
            if (empty($row->question_type) && $qType !== '') $upd['question_type'] = $qType;
            DB::table('quizz_attempt_answers')->where('id',$row->id)->update($upd);
        } else {
            DB::table('quizz_attempt_answers')->insert([
                'attempt_id'     => $attempt->id,
                'question_id'    => $questionId,
                'question_type'  => $qType ?: $this->questionType($questionId),
                'selected_raw'   => $selectedJson,
                'time_spent_sec' => 0,
                'created_at'     => $now,
                'updated_at'     => $now,
            ]);
        }

        // 4) Switch current question
        DB::table('quizz_attempts')->where('id', $attempt->id)->update([
            'current_question_id'  => $questionId,
            'current_q_started_at' => $now,
            'last_activity_at'     => $now,
            'updated_at'           => $now,
        ]);

        DB::commit();

        $attempt->current_question_id  = $questionId;
        $attempt->current_q_started_at = $now;

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

     private function writeAttemptAnswerDerived(int $attemptId, array $breakdown): void
{
    $now = now();
    foreach ($breakdown as $row) {
        $qid = (int) $row['question_id'];
        $upd = [
            'is_correct'          => $row['is_correct'],
            'awarded_mark'        => (int) $row['awarded_mark'],
            'selected_answer_ids' => isset($row['selected_answer_ids']) ? json_encode($row['selected_answer_ids'], JSON_UNESCAPED_UNICODE) : null,
            'selected_text'       => isset($row['selected_text']) ? (string)$row['selected_text'] : null,
            'updated_at'          => $now,
        ];

        $exists = DB::table('quizz_attempt_answers')
            ->where('attempt_id', $attemptId)
            ->where('question_id', $qid)
            ->exists();

        if ($exists) {
            DB::table('quizz_attempt_answers')
              ->where('attempt_id', $attemptId)->where('question_id', $qid)
              ->update($upd + ['answered_at' => DB::raw('COALESCE(answered_at, NOW())')]);
        } else {
            DB::table('quizz_attempt_answers')->insert([
                'attempt_id'          => $attemptId,
                'question_id'         => $qid,
                'question_type'       => DB::table('quizz_questions')->where('id',$qid)->value('question_type') ?? 'mcq',
                'selected_raw'        => null,
                'time_spent_sec'      => 0,
                'answered_at'         => $now,
                'created_at'          => $now,
                'updated_at'          => $now,
            ] + $upd);
        }
    }
}



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
                            'question_type'  => $this->questionType($prevQ),
                            'selected_raw'   => json_encode(null),
                            'time_spent_sec' => $slice,
                            'created_at'     => $now,
                            'updated_at'     => $now,
                        ]);
                    }
                }
            }

            // Score & persist result
$scored  = $this->scoreAttempt($attempt->id);
$this->writeAttemptAnswerDerived($attempt->id, $scored['answers']);
$publish = $this->shouldPublishToStudent((int)$attempt->quiz_id);

$cnt = $scored['counters'];
$pct = $scored['total_marks'] ? round($scored['marks_obtained'] / $scored['total_marks'] * 100, 2) : 0;

$resultId = DB::table('quizz_results')->insertGetId([
    'uuid'               => (string) Str::uuid(),
    'attempt_id'         => (int) $attempt->id,
    'quiz_id'            => (int) $attempt->quiz_id,
    'user_id'            => (int) $attempt->user_id,
    'marks_obtained'     => (int) $scored['marks_obtained'],
    'total_marks'        => (int) $scored['total_marks'],
    'marks_total'        => (int) $scored['total_marks'],   // keeps both columns in sync
    'total_questions'    => (int) $cnt['total_questions'],
    'total_correct'      => (int) $cnt['total_correct'],
    'total_incorrect'    => (int) $cnt['total_incorrect'],
    'total_skipped'      => (int) $cnt['total_skipped'],
    'percentage'         => $pct,
    'attempt_number'     => (int) $this->attemptNumberForUser((int)$attempt->quiz_id, (int)$attempt->user_id) + 1,
    'students_answer'    => json_encode($scored['answers'], JSON_UNESCAPED_UNICODE),
    'publish_to_student' => $publish ? 1 : 0,
    'result_set_up_type' => DB::table('quizz')->where('id',$attempt->quiz_id)->value('result_set_up_type') ?? 'Immediately',
    'result_release_date'=> DB::table('quizz')->where('id',$attempt->quiz_id)->value('result_release_date'),
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
if ($q->question_type === 'fill_in_the_blank') {
    $ex = $this->fibExplain($q, $qAns, $stuSel);
    $correct     = $ex['correct'];
    $stuDisplay  = $ex['student_display'];
    $corrDisplay = $ex['correct_display'];
} else {
    $correct = false;
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
                            'question_type'  => $this->questionType($prevQ),
                            'selected_raw'   => json_encode(null),
                            'time_spent_sec' => $slice,
                            'created_at'     => $now,
                            'updated_at'     => $now,
                        ]);
                    }
                }
            }

            $scored  = $this->scoreAttempt($attempt->id);
$this->writeAttemptAnswerDerived($attempt->id, $scored['answers']);
$publish = $this->shouldPublishToStudent((int)$attempt->quiz_id);

$cnt = $scored['counters'];
$pct = $scored['total_marks'] ? round($scored['marks_obtained'] / $scored['total_marks'] * 100, 2) : 0;

$resultId = DB::table('quizz_results')->insertGetId([
    'uuid'               => (string) Str::uuid(),
    'attempt_id'         => (int) $attempt->id,
    'quiz_id'            => (int) $attempt->quiz_id,
    'user_id'            => (int) $attempt->user_id,
    'marks_obtained'     => (int) $scored['marks_obtained'],
    'total_marks'        => (int) $scored['total_marks'],
    'marks_total'        => (int) $scored['total_marks'],
    'total_questions'    => (int) $cnt['total_questions'],
    'total_correct'      => (int) $cnt['total_correct'],
    'total_incorrect'    => (int) $cnt['total_incorrect'],
    'total_skipped'      => (int) $cnt['total_skipped'],
    'percentage'         => $pct,
    'attempt_number'     => (int) $this->attemptNumberForUser((int)$attempt->quiz_id, (int)$attempt->user_id) + 1,
    'students_answer'    => json_encode($scored['answers'], JSON_UNESCAPED_UNICODE),
    'publish_to_student' => $publish ? 1 : 0,
    'result_set_up_type' => DB::table('quizz')->where('id',$attempt->quiz_id)->value('result_set_up_type') ?? 'Immediately',
    'result_release_date'=> DB::table('quizz')->where('id',$attempt->quiz_id)->value('result_release_date'),
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
/* ===== FIB helpers (keep these anywhere above scoreAttempt) ===== */

/** Count blanks using {dash} or fall back to answer_order counts. */
private function fibCountGaps(object $q, $answers): int
{
    $title = (string)($q->question_title ?? '');
    $desc  = (string)($q->question_description ?? '');
    $n = 0;
    try {
        $n += preg_match_all('/\{dash\}/i', $title);
        $n += preg_match_all('/\{dash\}/i', $desc);
    } catch (\Throwable $e) {}
    if ($n > 0) return (int)$n;

    if ($answers && method_exists($answers, 'pluck')) {
        $orders = $answers->pluck('answer_order')
            ->filter(fn($v) => is_numeric($v) && (int)$v > 0)
            ->unique()->count();
        if ($orders > 0) return (int)$orders;
        return max(1, (int)$answers->count());
    }
    return 1;
}

/**
 * Explain + check a FIB question.
 * Supports:
 *   (A) per-gap: match each blank to allowed answers for that gap (same answer_order)
 *   (B) combined: join all blanks and match against any one answer row
 */
private function fibExplain(object $q, $answers, $selRaw): array
{
    $stuArr = [];
    if (is_array($selRaw))       $stuArr = array_values(array_map('strval', $selRaw));
    else if (trim((string)$selRaw) !== '') $stuArr = [ (string)$selRaw ];

    $gaps = $this->fibCountGaps($q, $answers);

    $distinctOrders = 0;
    if ($answers && method_exists($answers, 'pluck')) {
        $distinctOrders = (int) $answers->pluck('answer_order')
            ->filter(fn($v) => is_numeric($v) && (int)$v > 0)
            ->unique()->count();
    }
    $perGap = ($gaps > 1) || ($distinctOrders > 1);
    $norm = fn($s) => $this->normalizeText((string)$s);

    if (!$perGap) {
        // Combined
        $stuCombined = $norm(is_array($selRaw) ? implode(' ', $selRaw) : (string)$selRaw);
        $correct = false; $correctText = '';
        if ($answers) {
            foreach ($answers as $a) {
                $src = (string) ($a->answer_two_gap_match ?? $a->answer_title ?? '');
                if ($correctText === '' && $src !== '') $correctText = $src;
                if ($stuCombined !== '' && $norm($src) !== '' && $stuCombined === $norm($src)) {
                    $correct = true; break;
                }
            }
        }
        return [
            'correct'         => $correct,
            'student_display' => is_array($selRaw) ? implode(' | ', $stuArr) : (string)$selRaw,
            'correct_display' => $correctText,
        ];
    }

    // Per-gap
    $allOk = true; $studentParts = []; $correctParts = [];
    for ($i = 1; $i <= $gaps; $i++) {
        $allowed = ($answers && method_exists($answers, 'where')) ? $answers->where('answer_order', $i) : null;
        if (!$allowed || (method_exists($allowed, 'isEmpty') && $allowed->isEmpty())) {
            $row = ($answers && method_exists($answers, 'values')) ? $answers->values()->get($i-1) : null;
            $allowed = $row ? collect([$row]) : collect();
        }

        $stuRaw = (string)($stuArr[$i-1] ?? '');
        $studentParts[] = $stuRaw;
        $first = (method_exists($allowed, 'first') ? $allowed->first() : null);
        $correctParts[] = $first ? (string)($first->answer_two_gap_match ?? $first->answer_title ?? '') : '';

        $stu = $norm($stuRaw);
        $hit = false;
        if ($stu !== '' && $allowed) {
            foreach ($allowed as $a) {
                $needle = $norm($a->answer_two_gap_match ?? $a->answer_title ?? '');
                if ($needle !== '' && $stu === $needle) { $hit = true; break; }
            }
        }
        if (!$hit) $allOk = false;
    }
    return [
        'correct'         => $allOk,
        'student_display' => implode(' | ', $studentParts),
        'correct_display' => implode(' | ', $correctParts),
    ];
}



 private function scoreAttempt(int $attemptId): array
{
    $attempt = DB::table('quizz_attempts')->where('id', $attemptId)->first();
    if (!$attempt) return ['marks_obtained'=>0,'total_marks'=>0,'answers'=>[],'counters'=>['total_questions'=>0,'total_correct'=>0,'total_incorrect'=>0,'total_skipped'=>0]];

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

    $totalQuestions = (int) $qRows->count();
    $totalCorrect = 0; $totalIncorrect = 0; $totalSkipped = 0;

    $snapshot = []; // per question

    foreach ($qRows as $q) {
        $qid = (int)$q->id;
        $answers = $aRows[$qid] ?? collect();
        $selRaw  = null;

        if (isset($saved[$qid])) {
            try { $selRaw = json_decode($saved[$qid]->selected_raw ?? 'null', true); }
            catch (\Throwable $e) { $selRaw = null; }
        }

        $type = (string)$q->question_type;
        $awarded = 0; $isCorrect = false;
        $selectedIds = null; $selectedText = null;

        if ($type === 'fill_in_the_blank') {
            $fib = $this->fibExplain($q, $answers, $selRaw);
            $isCorrect   = $fib['correct'];
            $awarded     = $isCorrect ? (int)$q->question_mark : 0;
            $selectedIds = null;
            $selectedText= $fib['student_display'];
        } else {
            $correctIds = $answers->where('is_correct',1)->pluck('id')->values()->all();
            if (count($correctIds) > 1) {
                $l = is_array($selRaw) ? array_values(array_map('intval', $selRaw)) : [];
                sort($l);
                $r = $correctIds; sort($r);
                $isCorrect = ($l === $r);
                $selectedIds = $l;
            } else {
                $isCorrect = ((int)$selRaw === (int)($correctIds[0] ?? -1));
                $selectedIds = is_null($selRaw) ? null : [(int)$selRaw];
            }
            if ($isCorrect) $awarded = (int)$q->question_mark;

            // pretty text for report
            if ($type !== 'fill_in_the_blank') {
                if (is_array($selectedIds) && !empty($selectedIds)) {
                    $labels = [];
                    foreach ($answers as $a) if (in_array((int)$a->id, $selectedIds, true)) $labels[] = (string)$a->answer_title;
                    $selectedText = implode(', ', $labels);
                }
            }
        }

        // counters
        if ($selRaw === null || (is_array($selRaw) && count(array_filter($selRaw, fn($v)=>trim((string)$v) !== '')) === 0)) {
            $totalSkipped++;
        } else {
            $isCorrect ? $totalCorrect++ : $totalIncorrect++;
        }

        $marksObtained += $awarded;

        $snapshot[] = [
            'question_id'         => $qid,
            'selected'            => $selRaw,
            'is_correct'          => $isCorrect ? 1 : 0,
            'awarded_mark'        => $awarded,
            'selected_answer_ids' => $selectedIds,
            'selected_text'       => $selectedText,
        ];
    }

    return [
        'marks_obtained' => $marksObtained,
        'total_marks'    => $totalMarks,
        'answers'        => $snapshot,
        'counters'       => [
            'total_questions' => $totalQuestions,
            'total_correct'   => $totalCorrect,
            'total_incorrect' => $totalIncorrect,
            'total_skipped'   => $totalSkipped,
        ],
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

    public function resultDetail(Request $request, int $resultId)
{
    // ---------- 1. Auth ----------
    $user = $this->getUserFromToken($request);
    if (!$user || !$this->isStudent($user)) {
        return response()->json([
            'success' => false,
            'message' => 'Unauthorized (student token required)'
        ], 401);
    }

    // ---------- 2. Load result + attempt + quiz ----------
    $row = DB::table('quizz_results as r')
        ->join('quizz_attempts as a', 'a.id', '=', 'r.attempt_id')
        ->join('quizz as q', 'q.id', '=', 'r.quiz_id')
        ->where('r.id', $resultId)
        ->where('r.user_id', $user->id)          // ensure this result belongs to this student
        ->select([
            'r.id as result_id',
            'r.quiz_id',
            'r.attempt_id',
            'r.user_id',
            'r.marks_obtained',
            'r.total_marks',
            'r.total_questions',
            'r.total_correct',
            'r.total_incorrect',
            'r.total_skipped',
            'r.percentage',
            'r.attempt_number',
            'r.students_answer',
            'r.publish_to_student',
            'r.result_set_up_type',
            'r.result_release_date',
            'r.created_at as result_created_at',

            'a.uuid as attempt_uuid',
            'a.status as attempt_status',
            'a.started_at',
            'a.finished_at',
            'a.total_time_sec',
            'a.server_deadline_at',

            'q.quiz_name',
            'q.quiz_description',
            'q.total_time',
            'q.total_questions as quiz_total_questions',
        ])
        ->first();

    if (!$row) {
        return response()->json([
            'success' => false,
            'message' => 'Result not found'
        ], 404);
    }

    // ---------- 3. Check if result is viewable now ----------
    $allowViewNow = $this->shouldPublishToStudent((int) $row->quiz_id);
    if (!( (int)$row->publish_to_student === 1 || $allowViewNow )) {
        return response()->json([
            'success' => false,
            'message' => 'Result is not yet published for students'
        ], 403);
    }

    // ---------- 4. Decode stored per-question snapshot ----------
    try {
        $snapshot = json_decode($row->students_answer ?? '[]', true) ?: [];
    } catch (\Throwable $e) {
        $snapshot = [];
    }
    $snapByQ = [];
    foreach ($snapshot as $s) {
        if (isset($s['question_id'])) {
            $snapByQ[(int)$s['question_id']] = $s;
        }
    }

    // ---------- 5. Load questions + answer options ----------
    $questions = DB::table('quizz_questions')
        ->where('quiz_id', $row->quiz_id)
        ->orderBy('question_order')
        ->get();

    $answerRows = DB::table('quizz_question_answers')
        ->whereIn('belongs_question_id', $questions->pluck('id'))
        ->orderBy('belongs_question_id')
        ->orderBy('answer_order')
        ->get()
        ->groupBy('belongs_question_id');

    // time_spent per question
    $timeRows = DB::table('quizz_attempt_answers')
        ->where('attempt_id', $row->attempt_id)
        ->get()
        ->keyBy('question_id');

    $questionPayload = [];
    $totalTimeUsed = 0;

    foreach ($questions as $q) {
        $qid      = (int) $q->id;
        $ansRows  = $answerRows[$qid] ?? collect();
        $snap     = $snapByQ[$qid] ?? null;
        $timeRow  = $timeRows[$qid] ?? null;

        if ($timeRow) {
            $totalTimeUsed += (int) $timeRow->time_spent_sec;
        }

        $questionPayload[] = [
            'question_id'         => $qid,
            'order'               => (int) $q->question_order,
            'title'               => $q->question_title,
            'description'         => $q->question_description,
            'type'                => $q->question_type,
            'mark'                => (int) $q->question_mark,
            'time_spent_sec'      => $timeRow ? (int) $timeRow->time_spent_sec : 0,
            'is_correct'          => $snap ? (int) ($snap['is_correct'] ?? 0) : 0,
            'awarded_mark'        => $snap ? (int) ($snap['awarded_mark'] ?? 0) : 0,
            'selected_answer_ids' => $snap['selected_answer_ids'] ?? null,
            'selected_text'       => $snap['selected_text'] ?? null,

            'answers'             => $ansRows->map(function ($a) {
                return [
                    'answer_id'    => (int) $a->id,
                    'title'        => $a->answer_title,
                    'is_correct'   => (int) $a->is_correct,
                    'answer_order' => (int) ($a->answer_order ?? 0),
                ];
            })->values(),
        ];
    }

    // ---------- 6. Response payload ----------
    return response()->json([
        'success'  => true,
        'quiz'     => [
            'id'               => (int) $row->quiz_id,
            'name'             => $row->quiz_name,
            'description'      => $row->quiz_description,
            'total_questions'  => (int) ($row->quiz_total_questions ?? $row->total_questions),
            'total_time'       => (int) ($row->total_time ?? 0), // minutes
        ],
        'attempt'  => [
            'attempt_id'         => (int) $row->attempt_id,
            'attempt_uuid'       => (string) $row->attempt_uuid,
            'status'             => $row->attempt_status,
            'started_at'         => $row->started_at
                                     ? Carbon::parse($row->started_at)->toDateTimeString()
                                     : null,
            'finished_at'        => $row->finished_at
                                     ? Carbon::parse($row->finished_at)->toDateTimeString()
                                     : null,
            'total_time_sec'     => (int) $row->total_time_sec,
            'server_deadline_at' => $row->server_deadline_at
                                     ? Carbon::parse($row->server_deadline_at)->toDateTimeString()
                                     : null,
            'time_used_sec'      => $totalTimeUsed,
        ],
        'result'   => [
            'result_id'       => (int) $row->result_id,
            'marks_obtained'  => (int) $row->marks_obtained,
            'total_marks'     => (int) $row->total_marks,
            'percentage'      => $row->total_marks
                ? (float) round($row->marks_obtained / max(1, $row->total_marks) * 100, 2)
                : 0.0,
            'attempt_number'  => (int) ($row->attempt_number ?? 0),
            'total_questions' => (int) $row->total_questions,
            'total_correct'   => (int) $row->total_correct,
            'total_incorrect' => (int) $row->total_incorrect,
            'total_skipped'   => (int) $row->total_skipped,
        ],
        'questions' => $questionPayload,
    ], 200);
}

public function export(Request $request, int $resultId)
{
    // Auth (same as resultDetail)
    $user = $this->getUserFromToken($request);
    if (!$user || !$this->isStudent($user)) {
        return response()->json(['success'=>false,'message'=>'Unauthorized (student token required)'], 401);
    }

    // Load row & enforce ownership
    $row = DB::table('quizz_results as r')
        ->join('quizz as q', 'q.id', '=', 'r.quiz_id')
        ->join('quizz_attempts as a', 'a.id', '=', 'r.attempt_id')
        ->where('r.id', $resultId)
        ->where('r.user_id', $user->id)
        ->select([
            'r.*',
            'a.started_at','a.finished_at','a.total_time_sec',
            'q.quiz_name','q.quiz_description','q.total_time'
        ])->first();

    if (!$row) {
        return response()->json(['success'=>false,'message'=>'Result not found'], 404);
    }

    // respect publish logic
    $allowViewNow = $this->shouldPublishToStudent((int)$row->quiz_id);
    if (!((int)$row->publish_to_student === 1 || $allowViewNow)) {
        return response()->json(['success'=>false,'message'=>'Result is not yet published for students'], 403);
    }

    // Load question snapshot for the document (optional/simple)
    $answers = json_decode($row->students_answer ?? '[]', true) ?: [];
    $questions = DB::table('quizz_questions')
        ->where('quiz_id', $row->quiz_id)
        ->orderBy('question_order')
        ->get();
    $answerRows = DB::table('quizz_question_answers')
        ->whereIn('belongs_question_id', $questions->pluck('id'))
        ->orderBy('belongs_question_id')->orderBy('answer_order')
        ->get()->groupBy('belongs_question_id');

    // Build a minimal normalized structure for export
    $mapSnap = [];
    foreach ($answers as $a) if (isset($a['question_id'])) $mapSnap[(int)$a['question_id']] = $a;

    $items = [];
    foreach ($questions as $q) {
        $qid = (int)$q->id;
        $snap = $mapSnap[$qid] ?? null;
        $corr = $answerRows[$qid] ?? collect();
        $correctLabels = $corr->where('is_correct',1)->pluck('answer_title')->values()->all();
        $items[] = [
            'no'         => (int)$q->question_order,
            'title'      => (string)$q->question_title,
            'mark'       => (int)$q->question_mark,
            'is_correct' => (int)($snap['is_correct'] ?? 0),
            'awarded'    => (int)($snap['awarded_mark'] ?? 0),
            'your'       => isset($snap['selected_text']) ? (string)$snap['selected_text'] : '',
            'correct'    => implode(', ', $correctLabels),
        ];
    }

    $format = strtolower($request->query('format','docx'));
    $safeName = 'exam_result_'.$resultId;

    if ($format === 'docx') {
        // Prefer PhpWord if available
        if (class_exists(\PhpOffice\PhpWord\PhpWord::class)) {
            $phpWord  = new \PhpOffice\PhpWord\PhpWord();
            $section  = $phpWord->addSection();
            $fontH    = ['bold' => true, 'size' => 16];
            $fontB    = ['bold' => true];
            $fontM    = ['size' => 11];

            $section->addText('Exam Result', $fontH);
            $section->addTextBreak(1);

            $section->addText('Quiz: '.$row->quiz_name, $fontB);
            $section->addText('Student: '.($user->name ?? ('#'.$user->id)), $fontM);
            $section->addText('Score: '.$row->marks_obtained.' / '.$row->total_marks.'  ('.($row->total_marks ? round($row->marks_obtained/max(1,$row->total_marks)*100,2) : 0).'%)', $fontM);
            if ($row->started_at)  $section->addText('Started at: '.\Carbon\Carbon::parse($row->started_at)->toDayDateTimeString(), $fontM);
            if ($row->finished_at) $section->addText('Finished at: '.\Carbon\Carbon::parse($row->finished_at)->toDayDateTimeString(), $fontM);
            $section->addTextBreak(1);

            // Table
            $table = $section->addTable(['borderSize' => 6, 'borderColor' => 'cccccc', 'cellMargin' => 60]);
            $table->addRow();
            foreach (['Q#','Question','Your Answer','Correct Answer','Marks'] as $col) {
                $table->addCell(2000)->addText($col, ['bold'=>true]);
            }
            foreach ($items as $it) {
                $table->addRow();
                $table->addCell(800)->addText((string)$it['no']);
                $table->addCell(7000)->addText(strip_tags((string)$it['title']));
                $table->addCell(4000)->addText($it['your'] !== '' ? $it['your'] : '—');
                $table->addCell(4000)->addText($it['correct'] !== '' ? $it['correct'] : '—');
                $table->addCell(1800)->addText($it['awarded'].' / '.$it['mark']);
            }

            $tmp = tempnam(sys_get_temp_dir(), 'res_').'.docx';
            $phpWord->save($tmp, 'Word2007');

            return response()->download($tmp, $safeName.'.docx')->deleteFileAfterSend(true);
        }

        // Fallback: Word-compatible HTML (.doc)
        $html = view('exports.result_doc_fallback', [
            'row' => $row, 'user' => $user, 'items' => $items
        ])->render();

        return response($html, 200, [
            'Content-Type' => 'application/msword',
            'Content-Disposition' => 'attachment; filename="'.$safeName.'.doc"',
        ]);
    }

    // PDF: simple HTML fallback (users can “Save as PDF”)
    $html = view('exports.result_pdf_fallback', [
        'row' => $row, 'user' => $user, 'items' => $items
    ])->render();

    // If you use dompdf, replace the below with real PDF generation.
    return response($html, 200, [
        'Content-Type' => 'text/html',
        'Content-Disposition' => 'attachment; filename="'.$safeName.'.html"',
    ]);
}




}
