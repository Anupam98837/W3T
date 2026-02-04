<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Str;
use Throwable;
use Illuminate\Support\Facades\Schema;
use Carbon\Carbon;

class JudgeController extends Controller
{
    /* =========================================================
     * Helpers
     * ========================================================= */

    private function hasCol(string $table, string $col): bool
    {
        static $cache = [];
        $k = $table . '.' . $col;
        if (isset($cache[$k])) return $cache[$k];
        try {
            return $cache[$k] = Schema::hasColumn($table, $col);
        } catch (\Throwable $e) {
            return $cache[$k] = false;
        }
    }

    /**
     * Effective time limit in seconds:
     * - If batch_coding_questions.time_limit_sec exists and > 0 -> batch limit
     * - Else use coding_questions.time_limit_sec if you have it
     * - Else use coding_questions.total_time_min * 60
     * If both question + batch exist, use the stricter (min).
     */
    private function getTimeLimitSec(?object $question, ?object $bcqRow): int
    {
        $batchSec = (int)($bcqRow->time_limit_sec ?? 0);

        $questionSec = 0;

        // If you add a direct time_limit_sec column to coding_questions in the future:
        if ($question && $this->hasCol('coding_questions', 'time_limit_sec')) {
            $questionSec = (int)($question->time_limit_sec ?? 0);
        }

        // ✅ Current system uses total_time_min in coding_questions:
        if ($questionSec <= 0 && $question) {
            $min = (int)($question->total_time_min ?? 0);
            $questionSec = $min > 0 ? ($min * 60) : 0;
        }

        if ($batchSec > 0 && $questionSec > 0) {
            return max(0, min($batchSec, $questionSec));
        }

        return max(0, max($batchSec, $questionSec));
    }

    private function countAttemptsForContext(int $userId, int $questionId, ?int $batchId, ?int $bcqId): int
    {
        $q = DB::table('coding_attempts')
            ->where('question_id', $questionId)
            ->where('user_id', $userId)
            ->whereNull('deleted_at');

        if ($batchId) {
            $q->where('batch_id', $batchId);
            if ($bcqId) $q->where('batch_coding_question_id', $bcqId);
        } else {
            $q->whereNull('batch_id')->whereNull('batch_coding_question_id');
        }

        return (int)$q->count();
    }

    /* =========================================================
     * START (creates/resumes attempt BEFORE exam UI starts)
     * ========================================================= */
public function start(Request $r)
{
    $v = Validator::make($r->all(), [
        'question_id'   => 'nullable|integer|exists:coding_questions,id',
        'question_uuid' => 'nullable|string',
        'batch_uuid'    => 'nullable|string',
        'attempt_uuid'  => 'nullable|string',
    ]);

    if ($v->fails()) {
        return response()->json(['status' => 'error', 'message' => $v->errors()->first()], 422);
    }

    $p = $v->validated();

    $userId = (int)($r->attributes->get('auth_tokenable_id') ?? 0);
    $role   = (string)($r->attributes->get('auth_role') ?? '');

    if ($userId <= 0) {
        return response()->json(['status' => 'error', 'message' => 'Unauthorized.'], 403);
    }

    $question = $this->resolveQuestion($p['question_id'] ?? null, $p['question_uuid'] ?? null);
    if (!$question) {
        return response()->json(['status' => 'error', 'message' => 'Question not found'], 404);
    }

    $batchId = null;
    $bcqId   = null;
    $bcqRow  = null;
    $batchAttemptAllowed = null;

    // ✅ NEW
    $courseModuleId = null;

    if (!empty($p['batch_uuid'])) {
        $batch = DB::table('batches')->where('uuid', $p['batch_uuid'])->first();
        if (!$batch) return response()->json(['status' => 'error', 'message' => 'Batch not found'], 404);

        $batchId = (int)$batch->id;

        $bcqRow = DB::table('batch_coding_questions')
            ->where('batch_id', $batchId)
            ->where('question_id', $question->id)
            ->whereNull('deleted_at')
            ->first();

        if (!$bcqRow && $role === 'student') {
            return response()->json(['status' => 'error', 'message' => 'This question is not assigned to the batch.'], 403);
        }

        if ($bcqRow) {
            $bcqId = (int)$bcqRow->id;
            $batchAttemptAllowed = $bcqRow->attempt_allowed ?? null;

            // ✅ NEW: read course_module_id from batch_coding_questions
            if (isset($bcqRow->course_module_id) && !empty($bcqRow->course_module_id)) {
                $courseModuleId = (int)$bcqRow->course_module_id;
            }
        }
    }

    $now = now();

    // ✅ Attempt limit computed here (so UI can block BEFORE exam starts)
    $qAllowed = (int)($question->total_attempts ?? 1);
    $bAllowed = is_null($batchAttemptAllowed) ? $qAllowed : (int)$batchAttemptAllowed;
    $effectiveAllowed = min($qAllowed, $bAllowed);

    // 1) resume via attempt_uuid (ONLY if in_progress)
    $attempt = null;
    if (!empty($p['attempt_uuid']) && Str::isUuid($p['attempt_uuid'])) {
        $q = DB::table('coding_attempts')
            ->where('uuid', $p['attempt_uuid'])
            ->where('user_id', $userId)
            ->where('question_id', $question->id)
            ->where('status', 'in_progress')
            ->whereNull('deleted_at');

        if ($batchId) {
            $q->where('batch_id', $batchId);
            if ($bcqId) $q->where('batch_coding_question_id', $bcqId);
        } else {
            $q->whereNull('batch_id')->whereNull('batch_coding_question_id');
        }

        // ✅ NEW: resume only within same course_module_id (batch context)
        if ($courseModuleId && $this->hasCol('coding_attempts', 'course_module_id')) {
            $q->where('course_module_id', $courseModuleId);
        }

        $attempt = $q->first();
    }

    // 2) reuse latest in_progress attempt (refresh-safe)
    if (!$attempt) {
        $q = DB::table('coding_attempts')
            ->where('user_id', $userId)
            ->where('question_id', $question->id)
            ->where('status', 'in_progress')
            ->whereNull('deleted_at')
            ->orderByDesc('id');

        if ($batchId) {
            $q->where('batch_id', $batchId);
            if ($bcqId) $q->where('batch_coding_question_id', $bcqId);
        } else {
            $q->whereNull('batch_id')->whereNull('batch_coding_question_id');
        }

        // ✅ NEW: reuse only within same course_module_id (batch context)
        if ($courseModuleId && $this->hasCol('coding_attempts', 'course_module_id')) {
            $q->where('course_module_id', $courseModuleId);
        }

        $attempt = $q->first();
    }

    // ✅ Attempt counters (module-aware, without changing your helper signature)
    $usedAttemptsQ = DB::table('coding_attempts')
        ->where('user_id', $userId)
        ->where('question_id', $question->id)
        ->whereNull('deleted_at');

    if ($batchId) {
        $usedAttemptsQ->where('batch_id', $batchId);
        if ($bcqId) $usedAttemptsQ->where('batch_coding_question_id', $bcqId);
    } else {
        $usedAttemptsQ->whereNull('batch_id')->whereNull('batch_coding_question_id');
    }

    if ($courseModuleId && $this->hasCol('coding_attempts', 'course_module_id')) {
        $usedAttemptsQ->where('course_module_id', $courseModuleId);
    }

    $usedAttempts = (int)$usedAttemptsQ->count();

    // If existing attempt exists, return it
    if ($attempt) {
        $expiresAt = !empty($attempt->expires_at) ? Carbon::parse($attempt->expires_at) : null;
        $remaining = $expiresAt ? max(0, $now->diffInSeconds($expiresAt, false)) : null;

        $attemptNo = (int)($attempt->attempt_no ?? $usedAttempts);

        return response()->json([
            'status' => 'success',
            'data' => [
                'attempt_uuid'       => $attempt->uuid,
                'attempt_no'         => $attemptNo,
                'attempts_allowed'   => $effectiveAllowed,
                'attempts_used'      => $usedAttempts,
                'attempts_remaining' => $effectiveAllowed > 0 ? max(0, $effectiveAllowed - $usedAttempts) : null,

                'server_now'         => $now->toIso8601String(),
                'started_at'         => $attempt->server_started_at ? Carbon::parse($attempt->server_started_at)->toIso8601String() : null,
                'expires_at'         => $expiresAt ? $expiresAt->toIso8601String() : null,
                'time_limit_seconds' => (int)($attempt->time_limit_sec ?? 0),
                'remaining_seconds'  => $remaining,

                // (optional) debug
                'course_module_id'   => $courseModuleId,
            ]
        ], 200);
    }

    // ✅ If no in_progress attempt, check if we can create a new one
    $attemptNo = $usedAttempts + 1;

    if ($effectiveAllowed > 0 && $attemptNo > $effectiveAllowed) {
        return response()->json([
            'status' => 'error',
            'code'   => 'attempt_limit_reached',
            'message'=> "Attempt limit reached. Allowed: {$effectiveAllowed}.",
            'data'   => [
                'attempts_allowed'   => $effectiveAllowed,
                'attempts_used'      => $usedAttempts,
                'attempts_remaining' => 0,
                'course_module_id'   => $courseModuleId,
            ]
        ], 429);
    }

    $timeLimitSec = $this->getTimeLimitSec($question, $bcqRow);
    $expiresAt = ($timeLimitSec > 0) ? $now->copy()->addSeconds($timeLimitSec) : null;

    $attemptUuid = (string)Str::uuid();

    $insert = [
        'uuid'                     => $attemptUuid,
        'question_id'              => $question->id,
        'user_id'                  => $userId,
        'batch_id'                 => $batchId,
        'batch_coding_question_id' => $bcqId,
        'attempt_no'               => $attemptNo,
        'status'                   => 'in_progress',
        'ip'                       => $r->ip(),
        'user_agent'               => (string)$r->userAgent(),
        'auth_snapshot'            => json_encode(['role' => $role], JSON_UNESCAPED_UNICODE),
        'created_at'               => $now,
        'updated_at'               => $now,
    ];

    // ✅ NEW: persist course_module_id
    if ($this->hasCol('coding_attempts', 'course_module_id')) {
        $insert['course_module_id'] = $courseModuleId;
    }

    if ($this->hasCol('coding_attempts', 'server_started_at')) {
        $insert['server_started_at'] = $now;
    }
    if ($this->hasCol('coding_attempts', 'time_limit_sec')) {
        $insert['time_limit_sec'] = $timeLimitSec ?: null;
    }
    if ($this->hasCol('coding_attempts', 'expires_at')) {
        $insert['expires_at'] = $expiresAt;
    }

    DB::table('coding_attempts')->insert($insert);

    return response()->json([
        'status' => 'success',
        'data' => [
            'attempt_uuid'       => $attemptUuid,
            'attempt_no'         => $attemptNo,
            'attempts_allowed'   => $effectiveAllowed,
            'attempts_used'      => $usedAttempts + 1,
            'attempts_remaining' => $effectiveAllowed > 0 ? max(0, $effectiveAllowed - ($usedAttempts + 1)) : null,

            'server_now'         => $now->toIso8601String(),
            'started_at'         => $now->toIso8601String(),
            'expires_at'         => $expiresAt ? $expiresAt->toIso8601String() : null,
            'time_limit_seconds' => $timeLimitSec,
            'remaining_seconds'  => $timeLimitSec > 0 ? $timeLimitSec : null,

            // (optional) debug
            'course_module_id'   => $courseModuleId,
        ]
    ], 200);
}

/* =========================================================
 * RUN (no DB write)
 * ========================================================= */

public function run(Request $r)
{
    $v = Validator::make($r->all(), [
        'question_id'   => 'nullable|integer|exists:coding_questions,id',
        'question_uuid' => 'nullable|string',
        'language'      => 'required|string',
        'code'          => 'required|string|min:1',
        'only_samples'  => 'nullable|boolean',
    ]);

    if ($v->fails()) {
        return response()->json(['status' => 'error', 'message' => $v->errors()->first()], 422);
    }

    $p = $v->validated();

    $question = $this->resolveQuestion($p['question_id'] ?? null, $p['question_uuid'] ?? null);
    if (!$question) return response()->json(['status' => 'error', 'message' => 'Question not found'], 404);

    $langRow = DB::table('question_languages')
        ->where('question_id', $question->id)
        ->where('language_key', strtolower(trim($p['language'])))
        ->where('is_enabled', 1)
        ->first();

    if (!$langRow) {
        return response()->json(['status' => 'error', 'message' => 'Language not enabled for this question.'], 422);
    }

    $onlySamples = array_key_exists('only_samples', $p) ? (bool)$p['only_samples'] : true;

    $testsQ = DB::table('question_tests')
        ->where('question_id', $question->id)
        ->where('is_active', 1);

    if ($onlySamples) $testsQ->where('visibility', 'sample');

    $tests = $testsQ->orderBy('sort_order')->get();

    if ($tests->isEmpty()) {
        return response()->json([
            'status' => 'error',
            'message' => $onlySamples ? 'No sample tests found.' : 'No active tests found.',
        ], 404);
    }

    $results = [];
    $allPass = true;

    foreach ($tests as $test) {
        $exec = $this->runWithJudge0($p['language'], $p['code'], (string)($test->input ?? ''));

        $actual   = (string)($exec['stdout'] ?? $exec['output'] ?? '');
        $expected = (string)($test->expected ?? '');

        $pass = $this->compareOutputs($question, $actual, $expected);
        if (!empty($exec['runtime_error'])) $pass = false;

        $results[] = [
            'test_id'    => $test->id,
            'visibility' => $test->visibility,
            'pass'       => $pass,
            'runtime'    => $exec['runtime_error'] ?? null,
            'input'      => $test->input,
            'expected'   => $test->expected,
            'output'     => $actual,
            'time'       => $exec['time'] ?? null,
            'memory'     => $exec['memory'] ?? null,
            'status'     => $exec['status'] ?? null,
        ];

        $allPass = $allPass && $pass;
    }

    return response()->json([
        'status'   => 'success',
        'mode'     => 'run',
        'question' => $question->title,
        'all_pass' => $allPass,
        'results'  => $results,
    ], 200);
}

    /* =========================================================
     * SUBMIT (DB write) - robust reuse of in_progress attempts
     * ========================================================= */

    public function submit(Request $r)
    {
        $v = Validator::make($r->all(), [
            'question_id'   => 'nullable|integer|exists:coding_questions,id',
            'question_uuid' => 'nullable|string',
            'batch_uuid'    => 'nullable|string',
            'attempt_uuid'  => 'nullable|string',
            'auto_submit'   => 'nullable|boolean',
            'language'      => 'required|string',
            'code'          => 'required|string|min:1',
        ]);

        if ($v->fails()) {
            return response()->json(['status' => 'error', 'message' => $v->errors()->first()], 422);
        }

        $p = $v->validated();

        $userId = (int)($r->attributes->get('auth_tokenable_id') ?? 0);
        $role   = (string)($r->attributes->get('auth_role') ?? '');

        if ($userId <= 0) {
            return response()->json(['status' => 'error', 'message' => 'Unauthorized.'], 403);
        }

        $autoSubmit = (bool)($p['auto_submit'] ?? false);

        $question = $this->resolveQuestion($p['question_id'] ?? null, $p['question_uuid'] ?? null);
        if (!$question) return response()->json(['status' => 'error', 'message' => 'Question not found'], 404);

        $langKey = strtolower(trim($p['language']));
        $langRow = DB::table('question_languages')
            ->where('question_id', $question->id)
            ->where('language_key', $langKey)
            ->where('is_enabled', 1)
            ->first();

        if (!$langRow) {
            return response()->json(['status' => 'error', 'message' => 'Language not enabled for this question.'], 422);
        }

        // batch context
        $batchId = null;
        $bcqId   = null;
        $batchAttemptAllowed = null;
        $bcqRow  = null;

        if (!empty($p['batch_uuid'])) {
            $batch = DB::table('batches')->where('uuid', $p['batch_uuid'])->first();
            if (!$batch) return response()->json(['status' => 'error', 'message' => 'Batch not found'], 404);

            $batchId = (int)$batch->id;

            $bcqRow = DB::table('batch_coding_questions')
                ->where('batch_id', $batchId)
                ->where('question_id', $question->id)
                ->whereNull('deleted_at')
                ->first();

            if (!$bcqRow) {
                if ($role === 'student') {
                    return response()->json(['status' => 'error', 'message' => 'This question is not assigned to the batch.'], 403);
                }
            } else {
                $bcqId = (int)$bcqRow->id;
                $batchAttemptAllowed = $bcqRow->attempt_allowed;

                if (!empty($bcqRow->available_from) && now()->lt($bcqRow->available_from) && $role === 'student') {
                    return response()->json(['status' => 'error', 'message' => 'Not available yet.'], 403);
                }
                if (!empty($bcqRow->available_until) && now()->gt($bcqRow->available_until) && $role === 'student') {
                    return response()->json(['status' => 'error', 'message' => 'Submission window closed.'], 403);
                }
                if (($bcqRow->status ?? 'active') !== 'active' && $role === 'student') {
                    return response()->json(['status' => 'error', 'message' => 'Inactive assignment.'], 403);
                }
                if (!($bcqRow->publish_to_students ?? false) && $role === 'student') {
                    return response()->json(['status' => 'error', 'message' => 'Not published to students.'], 403);
                }
            }
        }

        $now = now();

        // ✅ expiry enforcement flags
        $hasExpires = $this->hasCol('coding_attempts', 'expires_at');

        // ✅ find existing attempt by attempt_uuid (preferred)
        $existingAttempt = null;
        if (!empty($p['attempt_uuid']) && Str::isUuid($p['attempt_uuid'])) {
            $q = DB::table('coding_attempts')
                ->where('uuid', $p['attempt_uuid'])
                ->where('user_id', $userId)
                ->where('question_id', $question->id)
                ->whereNull('deleted_at');

            if ($batchId) {
                $q->where('batch_id', $batchId);
                if ($bcqId) $q->where('batch_coding_question_id', $bcqId);
            } else {
                $q->whereNull('batch_id')->whereNull('batch_coding_question_id');
            }

            $existingAttempt = $q->first();
        }

        // ✅ IMPORTANT FIX:
        // if frontend forgot attempt_uuid, reuse the latest in_progress attempt anyway
        if (!$existingAttempt) {
            $q = DB::table('coding_attempts')
                ->where('user_id', $userId)
                ->where('question_id', $question->id)
                ->where('status', 'in_progress')
                ->whereNull('deleted_at')
                ->orderByDesc('id');

            if ($batchId) {
                $q->where('batch_id', $batchId);
                if ($bcqId) $q->where('batch_coding_question_id', $bcqId);
            } else {
                $q->whereNull('batch_id')->whereNull('batch_coding_question_id');
            }

            $existingAttempt = $q->first();
        }

        // prevent double-submit
        if ($existingAttempt) {
            if (($existingAttempt->status ?? '') === 'evaluated' || !empty($existingAttempt->evaluated_at)) {
                return response()->json(['status' => 'error', 'message' => 'This attempt is already evaluated.'], 409);
            }

            if ($role === 'student' && $hasExpires && !empty($existingAttempt->expires_at)) {
                $exp = Carbon::parse($existingAttempt->expires_at);
                if ($now->gt($exp) && !$autoSubmit) {
                    return response()->json(['status' => 'error', 'message' => 'Time is up. Auto submission only.'], 403);
                }
            }
        }

        $tests = DB::table('question_tests')
            ->where('question_id', $question->id)
            ->where('is_active', 1)
            ->orderBy('sort_order')
            ->get();

        if ($tests->isEmpty()) {
            return response()->json(['status' => 'error', 'message' => 'No active tests found.'], 404);
        }

        DB::beginTransaction();
        try {
            $attemptUuid = null;
            $attemptId   = null;
            $attemptNo   = null;

            // calculate time limit for new attempts created here (fallback)
            $timeLimitSec = $this->getTimeLimitSec($question, $bcqRow);

            if ($existingAttempt) {
                $attemptUuid = (string)$existingAttempt->uuid;
                $attemptId   = (int)$existingAttempt->id;
                $attemptNo   = (int)($existingAttempt->attempt_no ?? 1);

                DB::table('coding_attempts')
                    ->where('id', $attemptId)
                    ->update([
                        'language_key'      => $langKey,
                        'source_code'       => $p['code'],
                        'status'            => 'submitted',
                        'submitted_at'      => $now,
                        'server_started_at' => $existingAttempt->server_started_at ?? $now,
                        'ip'                => $r->ip(),
                        'user_agent'        => (string)$r->userAgent(),
                        'auth_snapshot'     => json_encode(['role' => $role], JSON_UNESCAPED_UNICODE),
                        'updated_at'        => $now,
                    ]);

            } else {
                // Attempt limit
                $qAllowed = (int)($question->total_attempts ?? 1);
                $bAllowed = is_null($batchAttemptAllowed) ? $qAllowed : (int)$batchAttemptAllowed;
                $effectiveAllowed = min($qAllowed, $bAllowed);

                $usedAttempts = $this->countAttemptsForContext($userId, $question->id, $batchId, $bcqId);
                $attemptNo = $usedAttempts + 1;

                if ($effectiveAllowed > 0 && $attemptNo > $effectiveAllowed) {
                    DB::rollBack();
                    return response()->json([
                        'status'  => 'error',
                        'message' => "Attempt limit reached. Allowed: {$effectiveAllowed}.",
                    ], 429);
                }

                $attemptUuid = (string)Str::uuid();

                $insert = [
                    'uuid'                     => $attemptUuid,
                    'question_id'              => $question->id,
                    'user_id'                  => $userId,
                    'batch_id'                 => $batchId,
                    'batch_coding_question_id' => $bcqId,
                    'attempt_no'               => $attemptNo,

                    'language_key'             => $langKey,
                    'judge_language_id'        => null,
                    'source_code'              => $p['code'],

                    'judge_vendor'             => 'judge0',
                    'judge_request_id'         => null,

                    'status'                   => 'submitted',
                    'server_started_at'        => $now,
                    'submitted_at'             => $now,

                    'ip'                       => $r->ip(),
                    'user_agent'               => (string)$r->userAgent(),
                    'auth_snapshot'            => json_encode(['role' => $role], JSON_UNESCAPED_UNICODE),

                    'created_at'               => $now,
                    'updated_at'               => $now,
                ];

                if ($this->hasCol('coding_attempts', 'time_limit_sec')) {
                    $insert['time_limit_sec'] = $timeLimitSec ?: null;
                }
                if ($this->hasCol('coding_attempts', 'expires_at') && $timeLimitSec > 0) {
                    $insert['expires_at'] = $now->copy()->addSeconds($timeLimitSec);
                }

                $attemptId = DB::table('coding_attempts')->insertGetId($insert);
            }

            // Evaluate tests
            $cases = [];
            $passedTests = 0;
            $failedTests = 0;
            $marksTotal = 0;
            $marksObtained = 0;
            $allPass = true;

            $totalRuntimeMs = 0;
            $maxMemoryKb = 0;

            foreach ($tests as $test) {
                $score = (int)($test->score ?? 1);
                $marksTotal += $score;

                $exec = $this->runWithJudge0($langKey, $p['code'], (string)($test->input ?? ''));

                $actual   = (string)($exec['stdout'] ?? $exec['output'] ?? '');
                $expected = (string)($test->expected ?? '');

                $pass = $this->compareOutputs($question, $actual, $expected);
                if (!empty($exec['runtime_error'])) $pass = false;

                if ($pass) {
                    $passedTests++;
                    $marksObtained += $score;
                } else {
                    $failedTests++;
                }

                $allPass = $allPass && $pass;

                $timeMs = isset($exec['time_ms']) ? (int)$exec['time_ms'] : null;
                $memKb  = isset($exec['memory']) ? (int)$exec['memory'] : null;
                if (!is_null($timeMs)) $totalRuntimeMs += $timeMs;
                if (!is_null($memKb)) $maxMemoryKb = max($maxMemoryKb, $memKb);

                $isSample = (($test->visibility ?? '') === 'sample');

                $cases[] = [
                    'test_id'    => $test->id,
                    'visibility' => $test->visibility,
                    'score'      => $score,
                    'pass'       => $pass,

                    'status'     => $exec['status'] ?? null,
                    'time'       => $exec['time'] ?? null,
                    'time_ms'    => $timeMs,
                    'memory'     => $exec['memory'] ?? null,
                    'runtime'    => $exec['runtime_error'] ?? null,

                    'input'      => $isSample ? ($test->input ?? null) : null,
                    'expected'   => $isSample ? ($test->expected ?? null) : null,
                    'output'     => $isSample ? $actual : null,
                    'stderr'     => $isSample ? ($exec['stderr'] ?? null) : null,
                    'compile'    => $isSample ? ($exec['compile_output'] ?? null) : null,
                ];
            }

            $totalTests = count($cases);
            $percentage = ($marksTotal > 0) ? round(($marksObtained * 100) / $marksTotal, 2) : null;

            $resultJson = [
                'question_id'    => $question->id,
                'attempt_no'     => $attemptNo,
                'total_tests'    => $totalTests,
                'passed_tests'   => $passedTests,
                'failed_tests'   => $failedTests,
                'marks_total'    => $marksTotal,
                'marks_obtained' => $marksObtained,
                'percentage'     => $percentage,
                'all_pass'       => $allPass,
                'cases'          => $cases,
            ];

            DB::table('coding_attempts')
                ->where('id', $attemptId)
                ->update([
                    'status'            => 'evaluated',
                    'evaluated_at'      => $now,
                    'test_results_json' => json_encode($resultJson, JSON_UNESCAPED_UNICODE),
                    'total_tests'       => $totalTests,
                    'passed_tests'      => $passedTests,
                    'failed_tests'      => $failedTests,
                    'total_runtime_ms'  => $totalRuntimeMs ?: null,
                    'max_memory_kb'     => $maxMemoryKb ?: null,
                    'updated_at'        => $now,
                ]);

            DB::table('coding_results')->where('attempt_id', $attemptId)->delete();
                $resultUuid = (string) Str::uuid();

            DB::table('coding_results')->insert([
                'uuid'                     => $resultUuid,
                'attempt_id'               => $attemptId,
                'question_id'              => $question->id,
                'user_id'                  => $userId,
                'batch_id'                 => $batchId,
                'batch_coding_question_id' => $bcqId,
                'marks_total'              => $marksTotal,
                'marks_obtained'           => $marksObtained,
                'total_tests'              => $totalTests,
                'passed_tests'             => $passedTests,
                'failed_tests'             => $failedTests,
                'percentage'               => $percentage,
                'all_pass'                 => $allPass ? 1 : 0,
                'evaluated_at'             => $now,
                'created_at'               => $now,
                'updated_at'               => $now,
            ]);

            DB::commit();

            $sampleResults = array_values(array_filter($cases, fn($c) => ($c['visibility'] ?? '') === 'sample'));
            
            return response()->json([
                'status'       => 'success',
                'mode'         => 'submit',
                'result_uuid'   => $resultUuid,
                'attempt_uuid' => $attemptUuid,
                'attempt_no'   => $attemptNo,
                'all_pass'     => $allPass,
                'stats'        => [
                    'marks_total'    => $marksTotal,
                    'marks_obtained' => $marksObtained,
                    'percentage'     => $percentage,
                    'total_tests'    => $totalTests,
                    'passed_tests'   => $passedTests,
                    'failed_tests'   => $failedTests,
                ],
                'results'        => $sampleResults,
                'sample_results' => $sampleResults,
            ], 200);

        } catch (\Throwable $e) {
            DB::rollBack();
            Log::error('[Judge/submit] Failed', ['err' => $e->getMessage()]);
            return response()->json(['status' => 'error', 'message' => 'Submission failed.'], 500);
        }
    }

    /* =========================================================
     * Question resolve + judge plumbing (unchanged)
     * ========================================================= */

    private function resolveQuestion(?int $id, ?string $uuid)
    {
        $cols = [
            'id',
            'title',
            'total_attempts',
            'total_time_min', // ✅ needed for timer
            'compare_mode',
            'trim_output',
            'whitespace_mode',
            'float_abs_tol',
            'float_rel_tol',
        ];

        // safe optional future column
        if ($this->hasCol('coding_questions', 'time_limit_sec')) {
            $cols[] = 'time_limit_sec';
        }

        $q = DB::table('coding_questions')->select($cols);

        if ($id) return $q->where('id', $id)->first();

        if ($uuid && Str::isUuid($uuid)) {
            return $q->where('uuid', $uuid)->first();
        }

        return null;
    }

    private function runWithJudge0(string $lang, string $code, string $input): array
    {
        $languageMap = [
            'c'      => 1,
            'cpp'    => 2,
            'java'   => 4,
            'python' => 25,
        ];

        $normalized = strtolower(trim($lang));
        $langId = $languageMap[$normalized] ?? null;

        if (!$langId) {
            return [
                'output'        => "Unsupported language: {$lang}",
                'runtime_error' => "Unsupported language",
            ];
        }

        $host = env('JUDGE0_HOST', 'judge0-extra-ce.p.rapidapi.com');
        $url  = "https://{$host}/submissions?base64_encoded=false&wait=true";

        $headers = [
            'X-RapidAPI-Key'  => env('JUDGE0_KEY'),
            'X-RapidAPI-Host' => $host,
        ];

        $body = [
            'language_id' => $langId,
            'source_code' => $code,
            'stdin'       => $input,
        ];

        try {
            $key = 'judge0_requests_' . now()->toDateString();
            if (!Cache::has($key)) Cache::put($key, 0, now()->endOfDay());
            Cache::increment($key);
        } catch (Throwable $e) {}

        try {
            $res  = Http::timeout(25)->withHeaders($headers)->post($url, $body);
            $json = $res->json() ?? [];

            $stdout  = $json['stdout'] ?? '';
            $stderr  = $json['stderr'] ?? '';
            $compile = $json['compile_output'] ?? '';

            $status  = $json['status']['description'] ?? ($json['status'] ?? null);
            $time    = $json['time'] ?? null;
            $memory  = $json['memory'] ?? null;

            $timeMs = null;
            if (!is_null($time) && is_numeric($time)) {
                $timeMs = (int) round(((float)$time) * 1000);
            }

            $errMsg = $stderr ?: $compile;
            $runtimeError = !empty($errMsg) ? $errMsg : null;

            return [
                'stdout'         => $stdout,
                'stderr'         => $stderr,
                'compile_output' => $compile,
                'output'         => $stdout !== '' ? $stdout : ($errMsg ?: ''),
                'runtime_error'  => $runtimeError,
                'status'         => $status,
                'time'           => $time,
                'time_ms'        => $timeMs,
                'memory'         => $memory,
            ];
        } catch (Throwable $e) {
            return [
                'output'        => "Judge0 error: " . $e->getMessage(),
                'runtime_error' => "Judge0 error",
            ];
        }
    }

    private function compareOutputs(object $question, string $actual, string $expected): bool
    {
        $compareMode    = $question->compare_mode    ?: 'exact';
        $trimOutput     = (bool)($question->trim_output ?? true);
        $whitespaceMode = $question->whitespace_mode ?: 'trim';

        $floatAbsTol = is_null($question->float_abs_tol) ? 1e-6 : (float)$question->float_abs_tol;
        $floatRelTol = is_null($question->float_rel_tol) ? 1e-6 : (float)$question->float_rel_tol;

        if ($trimOutput) {
            $actual   = trim($actual);
            $expected = trim($expected);
        }

        if ($whitespaceMode === 'squash') {
            $actual   = preg_replace('/\s+/', ' ', $actual);
            $expected = preg_replace('/\s+/', ' ', $expected);
        }

        switch ($compareMode) {
            case 'icase':
                return mb_strtolower($actual) === mb_strtolower($expected);

            case 'token':
                $aTokens = preg_split('/\s+/', $actual, -1, PREG_SPLIT_NO_EMPTY) ?: [];
                $eTokens = preg_split('/\s+/', $expected, -1, PREG_SPLIT_NO_EMPTY) ?: [];
                return $aTokens === $eTokens;

            case 'float_abs':
                if (!is_numeric($actual) || !is_numeric($expected)) return false;
                return abs((float)$actual - (float)$expected) <= $floatAbsTol;

            case 'float_rel':
                if (!is_numeric($actual) || !is_numeric($expected)) return false;
                $a = (float)$actual; $e = (float)$expected;
                if ($e == 0.0) return abs($a - $e) <= $floatAbsTol;
                return abs($a - $e) / abs($e) <= $floatRelTol;

            case 'exact':
            default:
                return $actual === $expected;
        }
    }
}
