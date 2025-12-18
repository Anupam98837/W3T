<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use Carbon\Carbon;

class CodingResultController extends Controller
{
    /* ============================================
     | Auth helpers (same pattern as ExamController)
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

        if (!empty($pat->expires_at) && Carbon::now()->gt(Carbon::parse($pat->expires_at))) {
            return null;
        }

        $user = DB::table('users')
            ->where('id', $pat->tokenable_id)
            ->whereNull('deleted_at')
            ->first();

        if (!$user || ($user->status ?? '') !== 'active') return null;

        return $user;
    }

    private function isStudent(object $user): bool
    {
        $role = strtolower(preg_replace('/[^a-z0-9]+/i', '', $user->role ?? ''));
        return in_array($role, ['student', 'std', 'stu'], true);
    }

    /* ============================================
     | GET /api/coding/results
     | List all results for logged-in student
     |============================================ */
    public function myResults(Request $request)
    {
        $user = $this->getUserFromToken($request);
        if (!$user || !$this->isStudent($user)) {
            return response()->json(['success'=>false,'message'=>'Unauthorized'], 401);
        }

        $rows = DB::table('coding_results as r')
            ->where('r.user_id', $user->id)
            ->whereNull('r.deleted_at')
            ->orderByDesc('r.evaluated_at')
            ->get();

        $results = $rows->map(fn ($r) => $this->formatResult($r));

        return response()->json([
            'success' => true,
            'results' => $results,
        ], 200);
    }

    /* ============================================
     | GET /api/coding/results/attempt/{attemptUuid}
     |============================================ */
    public function byAttempt(Request $request, string $attemptUuid)
    {
        $user = $this->getUserFromToken($request);
        if (!$user || !$this->isStudent($user)) {
            return response()->json(['success'=>false,'message'=>'Unauthorized'], 401);
        }

        $attempt = DB::table('coding_attempts')
            ->where('uuid', $attemptUuid)
            ->where('user_id', $user->id)
            ->first();

        if (!$attempt) {
            return response()->json(['success'=>false,'message'=>'Attempt not found'], 404);
        }

        $result = DB::table('coding_results')
            ->where('attempt_id', $attempt->id)
            ->first();

        if (!$result) {
            return response()->json(['success'=>false,'message'=>'Result not evaluated yet'], 404);
        }

        return response()->json([
            'success' => true,
            'result'  => $this->formatResult($result),
        ], 200);
    }
/* ==========================================================
 | GET /api/coding/results/{resultUuid}/detail
 | FULL RESULT VIEW
 |========================================================== */
public function detail(Request $request, string $resultUuid)
{
    $user = $this->getUserFromToken($request);
    
    $row = DB::table('coding_results as r')
        ->join('coding_questions as q', 'q.id', '=', 'r.question_id')
        ->join('coding_attempts as a', 'a.id', '=', 'r.attempt_id')
        ->join('users as u', 'u.id', '=', 'r.user_id') // ✅ student join
        ->where('r.uuid', $resultUuid)
        ->select([
            'r.*',
            'q.title as question_title',
            'q.description as question_description',
            'q.difficulty',

            'a.source_code',
            'a.language_key',
            'a.test_results_json',
            'a.total_runtime_ms',          // ✅ source of truth
            'a.created_at as started_at',
            'a.updated_at as finished_at',

            'u.name as student_name',      // ✅ student info
            'u.email as student_email',
        ])
        ->first();

    if (!$row) {
        return response()->json(['success'=>false,'message'=>'Result not found'], 404);
    }

    /* =======================
       QUESTION TESTS
    ======================= */
    $questionTests = DB::table('question_tests')
        ->where('question_id', $row->question_id)
        ->where('is_active', 1)
        ->orderBy('sort_order')
        ->get()
        ->values();

    /* =======================
       EXECUTION RESULTS
       (FROM test_results_json)
    ======================= */
    $resultsJson = json_decode($row->test_results_json ?? '{}', true);
    $execCases   = array_values($resultsJson['cases'] ?? []);

    /* =======================
       MERGE (INDEX SAFE)
    ======================= */
    $testcases = [];

    foreach ($questionTests as $i => $qt) {
        $exec = $execCases[$i] ?? null;

        $passed = $exec ? !empty($exec['pass']) : false;

        /* ---------- failure reason ---------- */
        $failureReason = null;
        if (!$passed && $exec) {
            if (!empty($exec['compile'])) {
                $failureReason = 'Compilation Error';
            } elseif (!empty($exec['runtime'])) {
                $failureReason = 'Runtime Error';
            } elseif (($exec['status'] ?? '') === 'TLE') {
                $failureReason = 'Time Limit Exceeded';
            } else {
                $failureReason = 'Wrong Answer';
            }
        }

        $testcases[] = [
            'test_id'        => $qt->id,
            'visibility'     => $qt->visibility,

            'status'         => $passed ? 'passed' : 'failed',

            'score'          => (int)$qt->score,
            'earned_score'   => $passed ? (int)$qt->score : 0,

            'time_ms'        => (int)($exec['time_ms'] ?? 0),

            'failure_reason' => $failureReason,

            'input'          => $qt->visibility === 'sample' ? $qt->input : null,
            'expected'       => $qt->visibility === 'sample' ? $qt->expected : null,
            'output'         => $qt->visibility === 'sample'
                ? ($exec['output'] ?? null)
                : null,
        ];
    }

    return response()->json([
        'success' => true,

        // ✅ student details
        'student' => [
            'name'  => $row->student_name,
            'email' => $row->student_email,
        ],

        'question' => [
            'id'          => (int)$row->question_id,
            'title'       => $row->question_title,
            'description' => $row->question_description,
            'difficulty'  => $row->difficulty,
        ],

        'submission' => [
            'language'       => $row->language_key,
            'submitted_code' => $row->source_code,
        ],

        'result' => [
            'marks_obtained' => (int)$row->marks_obtained,
            'marks_total'    => (int)$row->marks_total,
            'percentage'     => (float)$row->percentage,
            'total_tests'    => (int)$row->total_tests,
            'passed_tests'   => (int)$row->passed_tests,
            'failed_tests'   => (int)$row->failed_tests,
            'all_pass'       => (bool)$row->all_pass,
        ],

        // ✅ total time from coding_attempts table
        'timing' => [
            'total_time_ms' => (int)$row->total_runtime_ms,
            'started_at'    => Carbon::parse($row->started_at)->toDateTimeString(),
            'finished_at'   => Carbon::parse($row->finished_at)->toDateTimeString(),
        ],

        'testcases' => $testcases,
    ], 200);
}
    /* ============================================
     | Internal formatter
     |============================================ */
    private function formatResult(object $r): array
    {
        return [
            'result_uuid'   => (string)$r->uuid,
            'attempt_id'    => (int)$r->attempt_id,
            'question_id'   => (int)$r->question_id,

            'marks_total'    => (int)$r->marks_total,
            'marks_obtained' => (int)$r->marks_obtained,
            'percentage'     => (float)($r->percentage ?? 0),

            'total_tests'  => (int)$r->total_tests,
            'passed_tests' => (int)$r->passed_tests,
            'failed_tests' => (int)$r->failed_tests,
            'all_pass'     => (bool)$r->all_pass,

            'evaluated_at' => $r->evaluated_at
                ? Carbon::parse($r->evaluated_at)->toDateTimeString()
                : null,
        ];
    }
    private function isStaff(object $user): bool
{
    $role = strtolower(preg_replace('/[^a-z0-9]+/i', '', $user->role ?? ''));
    return in_array($role, ['admin','superadmin','instructor'], true);
}

    public function AllStudentResults(
    Request $request,
    string $batchUuid,
    string $questionUuid
) {
    $user = $this->getUserFromToken($request);
   
    /* -------------------------------
       Resolve batch & question
    -------------------------------- */
    $batch = DB::table('batches')->where('uuid', $batchUuid)->first();
    $question = DB::table('coding_questions')->where('uuid', $questionUuid)->first();

    if (!$batch || !$question) {
        return response()->json(['success'=>false,'message'=>'Invalid batch or question'], 404);
    }

    /* -------------------------------
       All students assigned to batch
    -------------------------------- */
    $students = DB::table('batch_students as bs')
        ->join('users as u', 'u.id', '=', 'bs.user_id')
        ->where('bs.batch_id', $batch->id)
        ->whereNull('u.deleted_at')
        ->select('u.id','u.name','u.email')
        ->get();

    /* -------------------------------
       Attempts for THIS batch + question
    -------------------------------- */
    $attempts = DB::table('coding_attempts as a')
        ->leftJoin('coding_results as r', 'r.attempt_id', '=', 'a.id')
        ->where('a.batch_id', $batch->id)
        ->where('a.question_id', $question->id)
        ->whereNull('a.deleted_at')
        ->orderBy('a.attempt_no')
        ->select([
            'a.user_id',
            'a.uuid as attempt_uuid',
            'a.attempt_no',
            'a.submitted_at',
            'r.uuid as result_uuid',
            'r.all_pass',
        ])
        ->get()
        ->groupBy('user_id');

    $participated = [];
    $notParticipated = [];

    foreach ($students as $s) {
        if ($attempts->has($s->id)) {
            $participated[] = [
                'student_id' => $s->id,
                'name'       => $s->name,
                'email'      => $s->email,
                'attempts'   => $attempts[$s->id]->map(function ($a) {
                    return [
                        'attempt_no'   => (int)$a->attempt_no,
                        'attempt_uuid' => $a->attempt_uuid,
                        'result_uuid'  => $a->result_uuid,
                        'status'       => $a->all_pass ? 'PASS' : 'FAIL',
                        'submitted_at' => $a->submitted_at
                            ? Carbon::parse($a->submitted_at)->toDateTimeString()
                            : null,
                    ];
                })->values(),
            ];
        } else {
            $notParticipated[] = [
                'student_id' => $s->id,
                'name'       => $s->name,
                'email'      => $s->email,
            ];
        }
    }

    return response()->json([
        'success'          => true,
        'batch'            => $batch->uuid,
        'question'         => $question->uuid,
        'participated'     => $participated,
        'not_participated' => $notParticipated,
    ]);
}

}
