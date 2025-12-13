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

class JudgeController extends Controller
{
    /**
     * ✅ RUN (no DB write)
     * Runs ONLY sample tests by default (safe for UI "Run").
     */
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
            return response()->json([
                'status'  => 'error',
                'message' => $v->errors()->first(),
            ], 422);
        }

        $p = $v->validated();

        $question = $this->resolveQuestion($p['question_id'] ?? null, $p['question_uuid'] ?? null);
        if (!$question) {
            return response()->json(['status'=>'error','message'=>'Question not found'], 404);
        }

        // validate language enabled (optional but recommended)
        $langRow = DB::table('question_languages')
            ->where('question_id', $question->id)
            ->where('language_key', strtolower(trim($p['language'])))
            ->where('is_enabled', 1)
            ->first();

        if (!$langRow) {
            return response()->json([
                'status'  => 'error',
                'message' => 'Language not enabled for this question.',
            ], 422);
        }

        $onlySamples = array_key_exists('only_samples', $p) ? (bool)$p['only_samples'] : true;

        $testsQ = DB::table('question_tests')
            ->where('question_id', $question->id)
            ->where('is_active', 1);

        if ($onlySamples) {
            $testsQ->where('visibility', 'sample');
        }

        $tests = $testsQ->orderBy('sort_order')->get();

        if ($tests->isEmpty()) {
            return response()->json([
                'status'  => 'error',
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
            if (!empty($exec['runtime_error'])) {
                $pass = false;
            }

            $results[] = [
                'test_id'    => $test->id,
                'visibility' => $test->visibility,
                'pass'       => $pass,
                'runtime'    => $exec['runtime_error'] ?? null,

                // ✅ for RUN we can show input/expected/output for sample tests
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

    /**
     * ✅ SUBMIT (DB write + attempt limit + stats)
     * Runs ALL active tests, stores attempt + result.
     */
    public function submit(Request $r)
    {
        $v = Validator::make($r->all(), [
            'question_id'   => 'nullable|integer|exists:coding_questions,id',
            'question_uuid' => 'nullable|string',
            'batch_uuid'    => 'nullable|string',
            'language'      => 'required|string',
            'code'          => 'required|string|min:1',
        ]);

        if ($v->fails()) {
            return response()->json([
                'status'  => 'error',
                'message' => $v->errors()->first(),
            ], 422);
        }

        $p = $v->validated();

        // ✅ must be authenticated (CheckRole middleware sets this)
        $userId = (int)($r->attributes->get('auth_tokenable_id') ?? 0);
        $role   = (string)($r->attributes->get('auth_role') ?? '');

        if ($userId <= 0) {
            return response()->json([
                'status'  => 'error',
                'message' => 'Unauthorized.',
            ], 403);
        }

        $question = $this->resolveQuestion($p['question_id'] ?? null, $p['question_uuid'] ?? null);
        if (!$question) {
            return response()->json(['status'=>'error','message'=>'Question not found'], 404);
        }

        // validate language enabled
        $langKey = strtolower(trim($p['language']));
        $langRow = DB::table('question_languages')
            ->where('question_id', $question->id)
            ->where('language_key', $langKey)
            ->where('is_enabled', 1)
            ->first();

        if (!$langRow) {
            return response()->json([
                'status'  => 'error',
                'message' => 'Language not enabled for this question.',
            ], 422);
        }

        // Batch context (optional)
        $batchId = null;
        $bcqId   = null;
        $batchAttemptAllowed = null;

        if (!empty($p['batch_uuid'])) {
            $batch = DB::table('batches')->where('uuid', $p['batch_uuid'])->first();
            if (!$batch) {
                return response()->json(['status'=>'error','message'=>'Batch not found'], 404);
            }
            $batchId = (int)$batch->id;

            $bcq = DB::table('batch_coding_questions')
                ->where('batch_id', $batchId)
                ->where('question_id', $question->id)
                ->whereNull('deleted_at')
                ->first();

            if (!$bcq) {
                // For students, must be assigned. For admin/instructor you may allow.
                if (in_array($role, ['student'])) {
                    return response()->json([
                        'status'  => 'error',
                        'message' => 'This question is not assigned to the batch.',
                    ], 403);
                }
            } else {
                $bcqId = (int)$bcq->id;
                $batchAttemptAllowed = $bcq->attempt_allowed;

                // basic availability checks (if you use these)
                if (!empty($bcq->available_from) && now()->lt($bcq->available_from) && $role === 'student') {
                    return response()->json(['status'=>'error','message'=>'Not available yet.'], 403);
                }
                if (!empty($bcq->available_until) && now()->gt($bcq->available_until) && $role === 'student') {
                    return response()->json(['status'=>'error','message'=>'Submission window closed.'], 403);
                }
                if (($bcq->status ?? 'active') !== 'active' && $role === 'student') {
                    return response()->json(['status'=>'error','message'=>'Inactive assignment.'], 403);
                }
                if (!($bcq->publish_to_students ?? false) && $role === 'student') {
                    return response()->json(['status'=>'error','message'=>'Not published to students.'], 403);
                }
            }
        }

        // Attempt limit: effective = min(question.total_attempts, batch_attempt_allowed)
        $qAllowed = (int)($question->total_attempts ?? 1);
        $bAllowed = is_null($batchAttemptAllowed) ? $qAllowed : (int)$batchAttemptAllowed;
        $effectiveAllowed = min($qAllowed, $bAllowed);

        // Count previous attempts (context-aware)
        $attemptCountQ = DB::table('coding_attempts')
            ->where('question_id', $question->id)
            ->where('user_id', $userId)
            ->whereNull('deleted_at');

        if ($batchId) {
            $attemptCountQ->where('batch_id', $batchId);
            if ($bcqId) $attemptCountQ->where('batch_coding_question_id', $bcqId);
        } else {
            $attemptCountQ->whereNull('batch_id')->whereNull('batch_coding_question_id');
        }

        $usedAttempts = (int)$attemptCountQ->count();
        $attemptNo    = $usedAttempts + 1;

        if ($effectiveAllowed > 0 && $attemptNo > $effectiveAllowed) {
            return response()->json([
                'status'  => 'error',
                'message' => "Attempt limit reached. Allowed: {$effectiveAllowed}.",
            ], 429);
        }

        // Fetch all active tests (submit evaluates everything)
        $tests = DB::table('question_tests')
            ->where('question_id', $question->id)
            ->where('is_active', 1)
            ->orderBy('sort_order')
            ->get();

        if ($tests->isEmpty()) {
            return response()->json(['status'=>'error','message'=>'No active tests found.'], 404);
        }

        $now = now();

        DB::beginTransaction();
        try {
            // Create attempt
            $attemptUuid = (string) Str::uuid();

            $attemptId = DB::table('coding_attempts')->insertGetId([
                'uuid'                    => $attemptUuid,
                'question_id'             => $question->id,
                'user_id'                 => $userId,
                'batch_id'                => $batchId,
                'batch_coding_question_id'=> $bcqId,
                'attempt_no'              => $attemptNo,

                'language_key'            => $langKey,
                'judge_language_id'       => null, // optional
                'source_code'             => $p['code'],

                'judge_vendor'            => 'judge0',
                'judge_request_id'        => null,

                'status'                  => 'submitted',
                'server_started_at'       => $now,
                'submitted_at'            => $now,

                'ip'                      => $r->ip(),
                'user_agent'              => (string) $r->userAgent(),
                'auth_snapshot'           => json_encode([
                    'role' => $role,
                ], JSON_UNESCAPED_UNICODE),

                'created_at'              => $now,
                'updated_at'              => $now,
            ]);

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
                if (!empty($exec['runtime_error'])) {
                    $pass = false;
                }

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

                // ✅ store full detail in JSON, but DON'T leak hidden expected/input back to client
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

                    // keep outputs for sample only (safe)
                    'output'     => ($test->visibility === 'sample') ? $actual : null,
                    'stderr'     => ($test->visibility === 'sample') ? ($exec['stderr'] ?? null) : null,
                    'compile'    => ($test->visibility === 'sample') ? ($exec['compile_output'] ?? null) : null,
                ];
            }

            $totalTests = count($cases);
            $percentage = ($marksTotal > 0) ? round(($marksObtained * 100) / $marksTotal, 2) : null;

            $resultJson = [
                'question_id'   => $question->id,
                'attempt_no'    => $attemptNo,
                'total_tests'   => $totalTests,
                'passed_tests'  => $passedTests,
                'failed_tests'  => $failedTests,
                'marks_total'   => $marksTotal,
                'marks_obtained'=> $marksObtained,
                'percentage'    => $percentage,
                'all_pass'      => $allPass,
                'cases'         => $cases,
            ];

            // Update attempt with results
            DB::table('coding_attempts')
                ->where('id', $attemptId)
                ->update([
                    'status'           => 'evaluated',
                    'evaluated_at'     => now(),
                    'test_results_json'=> json_encode($resultJson, JSON_UNESCAPED_UNICODE),
                    'total_tests'      => $totalTests,
                    'passed_tests'     => $passedTests,
                    'failed_tests'     => $failedTests,
                    'total_runtime_ms' => $totalRuntimeMs ?: null,
                    'max_memory_kb'    => $maxMemoryKb ?: null,
                    'updated_at'       => now(),
                ]);

            // Insert coding_results (stats table)
            DB::table('coding_results')->insert([
                'uuid'                    => (string) Str::uuid(),
                'attempt_id'              => $attemptId,
                'question_id'             => $question->id,
                'user_id'                 => $userId,
                'batch_id'                => $batchId,
                'batch_coding_question_id'=> $bcqId,
                'marks_total'             => $marksTotal,
                'marks_obtained'          => $marksObtained,
                'total_tests'             => $totalTests,
                'passed_tests'            => $passedTests,
                'failed_tests'            => $failedTests,
                'percentage'              => $percentage,
                'all_pass'                => $allPass ? 1 : 0,
                'evaluated_at'            => now(),
                'created_at'              => now(),
                'updated_at'              => now(),
            ]);

            DB::commit();

            // ✅ Response: show only sample-case details
            $sampleResults = array_values(array_filter($cases, fn($c) => ($c['visibility'] ?? '') === 'sample'));

            return response()->json([
                'status'        => 'success',
                'mode'          => 'submit',
                'attempt_uuid'  => $attemptUuid,
                'attempt_no'    => $attemptNo,
                'all_pass'      => $allPass,
                'stats'         => [
                    'marks_total'    => $marksTotal,
                    'marks_obtained' => $marksObtained,
                    'percentage'     => $percentage,
                    'total_tests'    => $totalTests,
                    'passed_tests'   => $passedTests,
                    'failed_tests'   => $failedTests,
                ],
                'sample_results'=> $sampleResults,
            ], 200);

        } catch (\Throwable $e) {
            DB::rollBack();
            Log::error('[Judge/submit] Failed', ['err' => $e->getMessage()]);
            return response()->json([
                'status'  => 'error',
                'message' => 'Submission failed.',
            ], 500);
        }
    }

    /** Resolve question by id or uuid */
    private function resolveQuestion(?int $id, ?string $uuid)
    {
        $q = DB::table('coding_questions')
            ->select('id','title','total_attempts','compare_mode','trim_output','whitespace_mode','float_abs_tol','float_rel_tol');

        if ($id) return $q->where('id', $id)->first();

        if ($uuid && Str::isUuid($uuid)) {
            return $q->where('uuid', $uuid)->first();
        }

        return null;
    }

    /**
     * Execute code using Judge0 Extra CE (RapidAPI)
     * Returns: stdout/stderr/compile_output + time/memory/status
     */
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

        // Per-day request counter (optional)
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
            $time    = $json['time'] ?? null;      // seconds string
            $memory  = $json['memory'] ?? null;    // KB int

            $timeMs = null;
            if (!is_null($time) && is_numeric($time)) {
                $timeMs = (int) round(((float)$time) * 1000);
            }

            // runtime/compile detection
            $errMsg = $stderr ?: $compile;
            $runtimeError = null;

            if (!empty($errMsg)) {
                $runtimeError = $errMsg;
            }

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
        $trimOutput     = (bool) ($question->trim_output ?? true);
        $whitespaceMode = $question->whitespace_mode ?: 'trim';

        $floatAbsTol = is_null($question->float_abs_tol) ? 1e-6 : (float) $question->float_abs_tol;
        $floatRelTol = is_null($question->float_rel_tol) ? 1e-6 : (float) $question->float_rel_tol;

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
