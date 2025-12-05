<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Cache;
use Throwable;

class JudgeController extends Controller
{
    /**
     * Run submission against all active tests for a question.
     */
    public function submit(Request $r)
    {
        $v = Validator::make($r->all(), [
            'question_id' => 'required|integer|exists:coding_questions,id',
            'language'    => 'required|string',
            'code'        => 'required|string|min:1',
        ]);

        if ($v->fails()) {
            Log::warning("Judge validation failed", ['errors' => $v->errors()]);
            return response()->json([
                'status'  => 'error',
                'message' => $v->errors()->first(),
            ], 422);
        }

        $payload = $v->validated();
        Log::info("Judge submission started", $payload);

        // Fetch question + its checker config
        $question = DB::table('coding_questions')
            ->select(
                'id','title',
                'compare_mode','trim_output','whitespace_mode',
                'float_abs_tol','float_rel_tol'
            )
            ->where('id', $payload['question_id'])
            ->first();

        $tests = DB::table('question_tests')
            ->where('question_id', $payload['question_id'])
            ->where('is_active', 1)
            ->orderBy('sort_order')
            ->get();

        if (!$question || $tests->isEmpty()) {
            Log::error("Question or tests not found", ['question_id' => $payload['question_id']]);
            return response()->json([
                'status'  => 'error',
                'message' => 'Question or tests not found',
            ], 404);
        }

        $results = [];
        $allPass = true;

        foreach ($tests as $idx => $test) {
            Log::info("Running test case", [
                'index'   => $idx,
                'test_id' => $test->id,
            ]);

            $exec = $this->runWithJudge0(
                $payload['language'],
                $payload['code'],
                $test->input
            );

            // If Judge0 failed badly, mark this test as failed with error message
            $output  = $exec['output'];
            $runtime = $exec['runtime_error'] ?? null;

            $pass = $this->compareOutputs(
                $question,
                (string) $output,
                (string) $test->expected
            );

            if ($runtime !== null) {
                // runtime / compile error – force fail, but include message
                $pass = false;
            }

            $results[] = [
                'test_id'    => $test->id,
                'visibility' => $test->visibility,
                'input'      => $test->input,
                'expected'   => $test->expected,
                'output'     => $output,
                'runtime'    => $runtime,   // nullable, extra info for UI
                'pass'       => $pass,
            ];

            $allPass = $allPass && $pass;
        }

        $final = [
            'status'   => 'success',
            'question' => $question->title,
            'all_pass' => $allPass,
            'results'  => $results,
        ];

        Log::info("Judge submission completed", [
            'question_id' => $question->id,
            'all_pass'    => $allPass,
            'tests_count' => count($results),
        ]);

        return response()->json($final, 200);
    }

    /**
     * Execute code using Judge0 CE / Extra CE (RapidAPI)
     * Returns ['output' => string, 'runtime_error' => ?string]
     */
    private function runWithJudge0(string $lang, string $code, string $input): array
    {
        // ✅ Extra CE IDs (your map)
        $languageMap = [
            'c'      => 1,   // C (Clang 10.0.1)
            'cpp'    => 2,   // C++ (Clang 10.0.1)
            'java'   => 4,   // Java (OpenJDK 14.0.1)
            'python' => 25,  // Python for ML (3.11.2)
            // add more if needed...
        ];

        $normalized = strtolower(trim($lang));
        $langId = $languageMap[$normalized] ?? null;

        if (!$langId) {
            Log::error("Unknown language requested", ['language' => $lang]);
            return [
                'output'        => "Unsupported language: {$lang}",
                'runtime_error' => "Unsupported language",
            ];
        }

        $host = env('JUDGE0_HOST', 'judge0-ce.p.rapidapi.com');

        $url = "https://{$host}/submissions?base64_encoded=false&wait=true";

        $headers = [
            'X-RapidAPI-Key'  => env('JUDGE0_KEY'),
            'X-RapidAPI-Host' => $host,
        ];

        $body = [
            'language_id' => $langId,
            'source_code' => $code,
            'stdin'       => $input,
        ];

        // === Per-day request counter (safer) ===
        try {
            $key = 'judge0_requests_' . now()->toDateString();
            if (!Cache::has($key)) {
                Cache::put($key, 0, now()->endOfDay());
            }
            $count = Cache::increment($key);
            Log::info("Judge0 request count today", ['count' => $count]);
        } catch (Throwable $e) {
            Log::warning("Failed to increment Judge0 request counter", ['error' => $e->getMessage()]);
        }

        try {
            // add reasonable timeout
            $res = Http::timeout(20)
                ->withHeaders($headers)
                ->post($url, $body);

            $json = $res->json() ?? [];

            // Log rate limit headers if present
            $rateHeaders = [
                'x-ratelimit-requests-limit'     => $res->header('x-ratelimit-requests-limit'),
                'x-ratelimit-requests-remaining' => $res->header('x-ratelimit-requests-remaining'),
            ];
            Log::info("Judge0 rate headers", $rateHeaders);

            // Retry once if explicit rate-limit message
            if (isset($json['message']) && str_contains($json['message'], 'Too many requests')) {
                Log::warning("Rate limited by Judge0, retrying after 1s...");
                sleep(1);

                $res  = Http::timeout(20)
                    ->withHeaders($headers)
                    ->post($url, $body);
                $json = $res->json() ?? [];
            }

            Log::info("Judge0 response payload", $json);

            // Prefer stdout; if empty, use stderr/compile_output
            $stdout  = $json['stdout']         ?? '';
            $stderr  = $json['stderr']         ?? '';
            $compile = $json['compile_output'] ?? '';

            if ($stdout !== null && $stdout !== '') {
                return [
                    'output'        => $stdout,
                    'runtime_error' => null,
                ];
            }

            // If no stdout, but there is stderr / compile error, surface it as runtime_error
            $errMsg = $stderr ?: $compile;

            return [
                'output'        => $errMsg ?: '',
                'runtime_error' => $errMsg ?: 'Execution/compile error',
            ];
        } catch (Throwable $e) {
            Log::error("Judge0 HTTP error", ['error' => $e->getMessage()]);
            return [
                'output'        => "Judge0 error: " . $e->getMessage(),
                'runtime_error' => "Judge0 error",
            ];
        }
    }

    /**
     * Compare outputs using question's checker configuration.
     *
     * Fields used from coding_questions:
     * - compare_mode: exact|icase|token|float_abs|float_rel
     * - trim_output: 0/1
     * - whitespace_mode: trim|squash|none
     * - float_abs_tol, float_rel_tol (nullable)
     */
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

        // whitespace_mode
        if ($whitespaceMode === 'squash') {
            $actual   = preg_replace('/\s+/', ' ', $actual);
            $expected = preg_replace('/\s+/', ' ', $expected);
        } elseif ($whitespaceMode === 'trim') {
            // already trimmed above; nothing extra
        } else {
            // none: compare as-is
        }

        switch ($compareMode) {
            case 'icase':
                return mb_strtolower($actual) === mb_strtolower($expected);

            case 'token':
                $aTokens = preg_split('/\s+/', $actual, -1, PREG_SPLIT_NO_EMPTY) ?: [];
                $eTokens = preg_split('/\s+/', $expected, -1, PREG_SPLIT_NO_EMPTY) ?: [];
                return $aTokens === $eTokens;

            case 'float_abs':
                // Simple: compare single float or throw fail if not numeric
                if (!is_numeric($actual) || !is_numeric($expected)) {
                    return false;
                }
                $a = (float) $actual;
                $e = (float) $expected;
                return abs($a - $e) <= $floatAbsTol;

            case 'float_rel':
                if (!is_numeric($actual) || !is_numeric($expected)) {
                    return false;
                }
                $a = (float) $actual;
                $e = (float) $expected;
                if ($e == 0.0) {
                    // fall back to abs comparison when expected is zero
                    return abs($a - $e) <= $floatAbsTol;
                }
                return abs($a - $e) / abs($e) <= $floatRelTol;

            case 'exact':
            default:
                return $actual === $expected;
        }
    }
}
