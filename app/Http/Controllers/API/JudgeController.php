<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Cache;

class JudgeController extends Controller
{
    /**
     * Run submission against all tests
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

        // Fetch question + tests
        $question = DB::table('coding_questions')->where('id', $payload['question_id'])->first();

        $tests = DB::table('question_tests')
            ->where('question_id', $payload['question_id'])
            ->where('is_active', 1)
            ->orderBy('sort_order')
            ->get();

        if (!$question || $tests->isEmpty()) {
            Log::error("Question or tests not found", ['question_id' => $payload['question_id']]);
            return response()->json([
                'status' => 'error',
                'message' => 'Question or tests not found',
            ], 404);
        }

        $results = [];
        $allPass = true;

        foreach ($tests as $idx => $test) {
            Log::info("Running test case", [
                'index'   => $idx,
                'test_id' => $test->id,
                'input'   => $test->input,
                'expected'=> $test->expected,
            ]);

            $output = $this->runWithJudge0($payload['language'], $payload['code'], $test->input);
            $pass = trim($output) === trim($test->expected);

            $results[] = [
                'test_id'   => $test->id,
                'visibility'=> $test->visibility,
                'input'     => $test->input,
                'expected'  => $test->expected,
                'output'    => $output,
                'pass'      => $pass,
            ];

            $allPass = $allPass && $pass;
        }

        $final = [
            'status'   => 'success',
            'question' => $question->title,
            'all_pass' => $allPass,
            'results'  => $results,
        ];

        Log::info("Judge submission completed", $final);

        return response()->json($final, 200);
    }

    /**
     * Execute code using Judge0 CE / Extra CE (RapidAPI)
     */
    private function runWithJudge0($lang, $code, $input)
    {
        // === Normal CE IDs (commented out) ===
        
        // $languageMap = [
        //     'c'      => 50,   // C (GCC 9.2.0)
        //     'cpp'    => 54,   // C++ (G++ 9.2.0)
        //     'java'   => 62,   // Java (OpenJDK 13)
        //     'python' => 71,   // Python 3.8.1
        // ];
        

        // ✅ Extra CE IDs (active) — update with /languages output
        $languageMap = [
            'c'      => 1,   // C (Clang 10.0.1)
            'cpp'    => 2,   // C++ (Clang 10.0.1)
            'java'   => 4,   // Java (OpenJDK 14.0.1)
            'python' => 25,  // Python for ML (3.11.2)
            // optionally add multiple versions if you want:
            // 'python3.12' => 31,
            // 'python3.13' => 32,
        ];
        
        $langId = $languageMap[strtolower($lang)] ?? null;

        if (!$langId) {
            Log::error("Unknown language requested", ['language' => $lang]);
            return "Unsupported language: $lang";
        }

        $url = "https://" . env('JUDGE0_HOST', 'judge0-ce.p.rapidapi.com') . "/submissions?base64_encoded=false&wait=true";

        $headers = [
            'X-RapidAPI-Key'  => env('JUDGE0_KEY'),
            'X-RapidAPI-Host' => env('JUDGE0_HOST', 'judge0-ce.p.rapidapi.com'),
        ];

        $payload = [
            'language_id' => $langId,
            'source_code' => $code,
            'stdin'       => $input,
        ];

        // === Request counter ===
        $count = Cache::increment('judge0_requests_today');
        Log::info("Judge0 request count (today): {$count}");

        // First attempt
        $res = Http::withHeaders($headers)->post($url, $payload);
        $json = $res->json();

        // Log headers if available
        $rateHeaders = [
            'x-ratelimit-requests-limit'     => $res->header('x-ratelimit-requests-limit'),
            'x-ratelimit-requests-remaining' => $res->header('x-ratelimit-requests-remaining'),
        ];
        Log::info("Judge0 rate headers", $rateHeaders);

        // Retry once if rate-limited
        if (isset($json['message']) && str_contains($json['message'], 'Too many requests')) {
            Log::warning("Rate limited by Judge0, retrying after 1s...");
            sleep(1);
            $res = Http::withHeaders($headers)->post($url, $payload);
            $json = $res->json();
        }

        Log::info("Judge0 response", $json);

        return $json['stdout']
            ?? $json['stderr']
            ?? $json['compile_output']
            ?? '';
    }
}
