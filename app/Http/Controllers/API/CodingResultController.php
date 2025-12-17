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

        if (isset($pat->expires_at) && $pat->expires_at !== null) {
            if (Carbon::now()->greaterThan(Carbon::parse($pat->expires_at))) {
                return null;
            }
        }

        $user = DB::table('users')
            ->where('id', $pat->tokenable_id)
            ->whereNull('deleted_at')
            ->first();

        if (!$user) return null;
        if (isset($user->status) && $user->status !== 'active') return null;

        return $user;
    }

    private function isStudent(object $user): bool
    {
        $role = mb_strtolower(preg_replace('/[^a-z0-9]+/i', '', (string)($user->role ?? '')));
        return in_array($role, ['student','std','stu'], true);
    }

    /* ============================================
     | GET /api/coding/results
     | List all coding results for logged-in student
     |============================================ */
    public function myResults(Request $request)
    {
        $user = $this->getUserFromToken($request);
        if (!$user || !$this->isStudent($user)) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized (student token required)'
            ], 401);
        }

        $rows = DB::table('coding_results as r')
            ->where('r.user_id', $user->id)
            ->whereNull('r.deleted_at')
            ->orderByDesc('r.evaluated_at')
            ->orderByDesc('r.id')
            ->get([
                'r.id',
                'r.uuid',
                'r.attempt_id',
                'r.question_id',
                'r.batch_id',
                'r.batch_coding_question_id',
                'r.marks_total',
                'r.marks_obtained',
                'r.total_tests',
                'r.passed_tests',
                'r.failed_tests',
                'r.percentage',
                'r.all_pass',
                'r.evaluated_at',
                'r.created_at',
            ]);

        $results = $rows->map(function ($r) {
            return [
                'result_id'        => (int) $r->id,
                'result_uuid'      => (string) $r->uuid,
                'attempt_id'       => (int) $r->attempt_id,
                'question_id'      => (int) $r->question_id,
                'batch_id'         => $r->batch_id ? (int)$r->batch_id : null,
                'marks_obtained'   => (int) $r->marks_obtained,
                'marks_total'      => (int) $r->marks_total,
                'percentage'       => (float) ($r->percentage ?? 0),
                'total_tests'      => (int) $r->total_tests,
                'passed_tests'     => (int) $r->passed_tests,
                'failed_tests'     => (int) $r->failed_tests,
                'all_pass'         => (bool) $r->all_pass,
                'evaluated_at'     => $r->evaluated_at
                    ? Carbon::parse($r->evaluated_at)->toDateTimeString()
                    : null,
            ];
        });

        return response()->json([
            'success' => true,
            'results' => $results,
        ], 200);
    }

    /* ============================================
     | GET /api/coding/results/attempt/{uuid}
     | Get result by coding attempt UUID
     |============================================ */
    public function byAttempt(Request $request, string $attemptUuid)
    {
        $user = $this->getUserFromToken($request);
        if (!$user || !$this->isStudent($user)) {
            return response()->json(['success'=>false,'message'=>'Unauthorized'], 401);
        }

        $attempt = DB::table('coding_attempts')
            ->where('uuid', $attemptUuid)
            ->first();

        if (!$attempt || (int)$attempt->user_id !== (int)$user->id) {
            return response()->json(['success'=>false,'message'=>'Attempt not found'], 404);
        }

        $result = DB::table('coding_results')
            ->where('attempt_id', $attempt->id)
            ->whereNull('deleted_at')
            ->first();

        if (!$result) {
            return response()->json([
                'success' => false,
                'message' => 'Result not yet evaluated'
            ], 404);
        }

        return response()->json([
            'success' => true,
            'result'  => $this->formatResult($result),
        ], 200);
    }

    /* ============================================
     | GET /api/coding/results/{id}/detail
     | Full judge breakdown (metadata)
     |============================================ */
    public function detail(Request $request, int $resultId)
    {
        $user = $this->getUserFromToken($request);
        if (!$user || !$this->isStudent($user)) {
            return response()->json(['success'=>false,'message'=>'Unauthorized'], 401);
        }

        $row = DB::table('coding_results')
            ->where('id', $resultId)
            ->where('user_id', $user->id)
            ->whereNull('deleted_at')
            ->first();

        if (!$row) {
            return response()->json([
                'success' => false,
                'message' => 'Result not found'
            ], 404);
        }

        $metadata = null;
        if (!empty($row->metadata)) {
            try {
                $metadata = json_decode($row->metadata, true);
            } catch (\Throwable $e) {
                $metadata = null;
            }
        }

        return response()->json([
            'success' => true,
            'result'  => $this->formatResult($row),
            'judge'   => $metadata,
        ], 200);
    }

    /* ============================================
     | GET /api/coding/results/{id}/export
     | Export coding result (JSON / HTML)
     |============================================ */
    public function export(Request $request, int $resultId)
    {
        $user = $this->getUserFromToken($request);
        if (!$user || !$this->isStudent($user)) {
            return response()->json(['success'=>false,'message'=>'Unauthorized'], 401);
        }

        $row = DB::table('coding_results')
            ->where('id', $resultId)
            ->where('user_id', $user->id)
            ->whereNull('deleted_at')
            ->first();

        if (!$row) {
            return response()->json(['success'=>false,'message'=>'Result not found'], 404);
        }

        $metadata = null;
        if (!empty($row->metadata)) {
            try {
                $metadata = json_decode($row->metadata, true);
            } catch (\Throwable $e) {}
        }

        return response()->json([
            'success' => true,
            'result'  => $this->formatResult($row),
            'judge'   => $metadata,
            'exported_at' => Carbon::now()->toDateTimeString(),
        ], 200);
    }

    /* ============================================
     | Internal formatter (single source of truth)
     |============================================ */
    private function formatResult(object $r): array
    {
        return [
            'result_id'        => (int) $r->id,
            'result_uuid'      => (string) $r->uuid,
            'attempt_id'       => (int) $r->attempt_id,
            'question_id'      => (int) $r->question_id,
            'user_id'          => (int) $r->user_id,
            'batch_id'         => $r->batch_id ? (int)$r->batch_id : null,
            'batch_coding_question_id' => $r->batch_coding_question_id
                ? (int)$r->batch_coding_question_id
                : null,

            'marks_obtained'   => (int) $r->marks_obtained,
            'marks_total'      => (int) $r->marks_total,
            'percentage'       => (float) ($r->percentage ?? 0),

            'total_tests'      => (int) $r->total_tests,
            'passed_tests'     => (int) $r->passed_tests,
            'failed_tests'     => (int) $r->failed_tests,
            'all_pass'         => (bool) $r->all_pass,

            'evaluated_at'     => $r->evaluated_at
                ? Carbon::parse($r->evaluated_at)->toDateTimeString()
                : null,
        ];
    }
}
