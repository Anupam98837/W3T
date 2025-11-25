<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\File;
use Illuminate\Validation\Rule;
use Illuminate\Support\Str;

class QuizzController extends Controller
{
    /* =========================
     * Auth/Role helpers
     * ========================= */
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

    /* =========================
     * Activity Log
     * ========================= */
    private function logActivity(
        Request $request,
        string $activity, // store | update | destroy | status | restore | force
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
                'module'             => 'Quizz',
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
            Log::error('[Quizz] user_data_activity_log insert failed', ['error' => $e->getMessage()]);
        }
    }

    /* =========================
     * Notifications (DB-only)
     * ========================= */
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

    /* =========================
     * Helpers
     * ========================= */
    private function ensureImageFolder(): string
    {
        $destDir = public_path('assets/images/quizz');
        File::ensureDirectoryExists($destDir, 0755, true);
        return $destDir;
    }

    private function findByKey(string $key)
    {
        $q = DB::table('quizz')->whereNull('deleted_at');
        if (ctype_digit($key)) $q->where('id', (int)$key); else $q->where('uuid', $key);
        return $q->first();
    }

    private function findAnyByKey(string $key)
    {
        $q = DB::table('quizz'); // includes soft-deleted
        if (ctype_digit($key)) $q->where('id', (int)$key); else $q->where('uuid', $key);
        return $q->first();
    }

    /* =========================
     * CREATE (POST /api/quizz)
     * ========================= */
    public function store(Request $request)
    {
        if ($resp = $this->requireRole($request, ['admin','super_admin'])) return $resp;
        $this->logWithActor('[Quizz Store] begin', $request);

        $data = $request->validate([
            'quiz_name'            => ['required','string','max:255'],
            'quiz_description'     => ['sometimes','nullable','string'],
            'instructions'         => ['sometimes','nullable','string'],
            'note'                 => ['sometimes','nullable','string'],
            'is_public'            => ['sometimes','string','in:yes,no'],
            'result_set_up_type'   => ['sometimes','string', Rule::in(['Immediately','Now','Schedule'])],
            'result_release_date'  => ['sometimes','nullable','date'],
            'total_time'           => ['sometimes','nullable','integer','min:1'],
            'total_attempts'       => ['sometimes','nullable','integer','min:1'],

             // 🔽 NEW: randomization flags
            'is_question_random'   => ['sometimes','string', Rule::in(['yes','no'])],
            'is_option_random'     => ['sometimes','string', Rule::in(['yes','no'])],

            // image via file OR URL
            'quiz_img'             => ['sometimes','file','image','mimes:jpeg,png,jpg,gif,webp,avif','max:4096'],
            'quiz_img_url'         => ['sometimes','nullable','url'],

            // metadata
            'metadata'             => ['sometimes','nullable','array'],

            // lifecycle
            'status'               => ['sometimes', Rule::in(['active','archived'])],
        ]);

        // Determine image path
        $imgPath = null;
        if (!empty($data['quiz_img_url'])) {
            $imgPath = (string)$data['quiz_img_url'];
        } elseif ($request->hasFile('quiz_img')) {
            $destDir = $this->ensureImageFolder();
            $ext     = strtolower($request->file('quiz_img')->getClientOriginalExtension() ?: 'jpg');
            $fname   = 'quizz_' . time() . '_' . Str::random(6) . '.' . $ext;
            $request->file('quiz_img')->move($destDir, $fname);
            $imgPath = 'assets/images/quizz/' . $fname;
        }

        $a    = $this->actor($request);
        $now  = now();
        $uuid = (string) Str::uuid();

        $insert = [
            'uuid'                 => $uuid,
            'created_by'           => $a['id'] ?: null,
            'quiz_name'            => $data['quiz_name'],
            'quiz_description'     => $data['quiz_description'] ?? null,
            'quiz_img'             => $imgPath,
            'instructions'         => $data['instructions'] ?? null,
            'note'                 => $data['note'] ?? null,
            'is_public'            => $data['is_public'] ?? 'no',
            'result_set_up_type'   => $data['result_set_up_type'] ?? 'Immediately',
            'result_release_date'  => $data['result_release_date'] ?? null,
            'total_time'           => $data['total_time'] ?? null,
            'total_attempts'       => $data['total_attempts'] ?? 1,
              // 🔽 NEW: store flags (backed by your toggles)
            'is_question_random'   => $data['is_question_random'] ?? 'no',
            'is_option_random'     => $data['is_option_random'] ?? 'no',
            'status'               => $data['status'] ?? 'active',
            'created_at'           => $now,
            'created_at_ip'        => $request->ip(),
            'updated_at'           => $now,
            'deleted_at'           => null,
            'metadata'             => isset($data['metadata'])
                                        ? json_encode($data['metadata'], JSON_UNESCAPED_UNICODE)
                                        : json_encode(new \stdClass()),
        ];

        $id = DB::table('quizz')->insertGetId($insert);
        $fresh = DB::table('quizz')->where('id', $id)->first();

        // Enrich UI counts (best-effort)
        if ($fresh) {
            try { $fresh->question_count = DB::table('quizz_questions')->where('quiz_id', $fresh->id)->count(); }
            catch (\Throwable $e) { $fresh->question_count = 0; }
            try { $fresh->student_count  = DB::table('quizz_results')->where('quiz_id', $fresh->id)->distinct('user_id')->count('user_id'); }
            catch (\Throwable $e) { $fresh->student_count = 0; }
        }

        // Activity + Notification
        $this->logActivity($request, 'store', 'Created quiz "'.$insert['quiz_name'].'"', 'quizz', $id, array_keys($insert), null, $fresh ? (array)$fresh : null);

        $link = rtrim((string) config('app.url'), '/') . '/admin/quizz/'.$id;
        $this->persistNotification([
            'title'     => 'Quiz created',
            'message'   => '“'.$insert['quiz_name'].'” has been created.',
            'receivers' => $this->adminReceivers(),
            'metadata'  => [
                'action' => 'created',
                'quiz'   => ['id' => $id, 'uuid' => $uuid, 'name' => $insert['quiz_name'], 'status' => $insert['status']],
                'created_by' => $a,
            ],
            'type'      => 'quizz',
            'link_url'  => $link,
            'priority'  => 'normal',
            'status'    => 'active',
        ]);

        $this->logWithActor('[Quizz Store] success', $request, ['quiz_id' => $id, 'uuid' => $uuid]);

        return response()->json([
            'status'  => 'success',
            'message' => 'Quiz created successfully',
            'data'    => $fresh,
        ], 201);
    }

    /* =========================
     * LIST (GET /api/quizz)
     * ========================= */
    public function index(Request $r)
    {
        if ($resp = $this->requireRole($r, ['admin','super_admin'])) return $resp;

        $page        = max(1, (int)$r->query('page', 1));
        $perPage     = max(1, min(100, (int)$r->query('per_page', 20)));
        $qText       = trim((string)$r->query('q', ''));
        $status      = $r->query('status');             // active|archived
        $isPub       = $r->query('is_public');          // yes|no
        $sort        = (string)$r->query('sort', '-created_at'); // created_at|quiz_name|status|...
        $onlyDeleted = (int)$r->query('only_deleted', 0);

        $q = DB::table('quizz');
        if ($onlyDeleted) {
            $q->whereNotNull('deleted_at');
        } else {
            $q->whereNull('deleted_at');
        }

        if ($qText !== '') {
            $q->where(function($w) use ($qText){
                $w->where('quiz_name','like',"%$qText%")
                  ->orWhere('quiz_description','like',"%$qText%");
            });
        }
        if ($status) $q->where('status', $status);
        if ($isPub)  $q->where('is_public', $isPub);

        $dir = 'asc'; $col = $sort;
        if (str_starts_with($sort, '-')) { $dir = 'desc'; $col = ltrim($sort, '-'); }
        if (!in_array($col, ['created_at','quiz_name','status','is_public','total_time'], true)) { $col='created_at'; $dir='desc'; }

        $total = (clone $q)->count();
        $rows  = $q->orderBy($col, $dir)->offset(($page-1)*$perPage)->limit($perPage)->get();

        foreach ($rows as $row) {
            try { $row->question_count = DB::table('quizz_questions')->where('quiz_id', $row->id)->count(); }
            catch (\Throwable $e) { $row->question_count = 0; }
            try { $row->student_count  = DB::table('quizz_results')->where('quiz_id', $row->id)->distinct('user_id')->count('user_id'); }
            catch (\Throwable $e) { $row->student_count = 0; }
        }

        return response()->json([
            'data' => $rows,
            'pagination' => ['page'=>$page,'per_page'=>$perPage,'total'=>$total]
        ]);
    }

    /* =========================
     * SHOW (GET /api/quizz/{id|uuid})
     * ========================= */
    public function show(Request $r, string $key)
    {
        if ($resp = $this->requireRole($r, ['admin','super_admin'])) return $resp;

        $row = $this->findByKey($key);
        if (!$row) return response()->json(['error'=>'Quiz not found'], 404);

        try { $row->question_count = DB::table('quizz_questions')->where('quiz_id', $row->id)->count(); }
        catch (\Throwable $e) { $row->question_count = 0; }
        try { $row->student_count  = DB::table('quizz_results')->where('quiz_id', $row->id)->distinct('user_id')->count('user_id'); }
        catch (\Throwable $e) { $row->student_count = 0; }

        return response()->json(['data'=>$row]);
    }

    /* =========================
     * UPDATE (PUT/PATCH /api/quizz/{id|uuid})
     * ========================= */
    public function update(Request $request, string $key)
    {
        if ($resp = $this->requireRole($request, ['admin','super_admin'])) return $resp;

        $rowQ = DB::table('quizz')->whereNull('deleted_at');
        if (ctype_digit($key)) $rowQ->where('id',(int)$key); else $rowQ->where('uuid',$key);
        $row = $rowQ->first();
        if (!$row) return response()->json(['error'=>'Quiz not found'], 404);

        $id = (int)$row->id;

        $data = $request->validate([
            'quiz_name'            => ['sometimes','string','max:255'],
            'quiz_description'     => ['sometimes','nullable','string'],
            'instructions'         => ['sometimes','nullable','string'],
            'note'                 => ['sometimes','nullable','string'],
            'is_public'            => ['sometimes','string', Rule::in(['yes','no'])],
            'result_set_up_type'   => ['sometimes','string', Rule::in(['Immediately','Now','Schedule'])],
            'result_release_date'  => ['sometimes','nullable','date'],
            'total_time'           => ['sometimes','nullable','integer','min:1'],
            'total_attempts'       => ['sometimes','nullable','integer','min:1'],
            'status'               => ['sometimes', Rule::in(['active','archived'])],
             // 🔽 NEW: randomization flags
            'is_question_random'   => ['sometimes','string', Rule::in(['yes','no'])],
            'is_option_random'     => ['sometimes','string', Rule::in(['yes','no'])],

            // image via file OR URL
            'quiz_img'             => ['sometimes','file','image','mimes:jpeg,png,jpg,gif,webp,avif','max:4096'],
            'quiz_img_url'         => ['sometimes','nullable','url'],

            'metadata'             => ['sometimes','nullable','array'],
        ]);

        $upd = [];
        foreach ($data as $k => $v) {
            if (in_array($k, ['quiz_img','quiz_img_url'], true)) continue; // handled below
            if ($k === 'metadata') $v = $v !== null ? json_encode($v, JSON_UNESCAPED_UNICODE) : json_encode(new \stdClass());
            $upd[$k] = $v;
        }

        // image update precedence: URL > File
        if (!empty($data['quiz_img_url'])) {
            $upd['quiz_img'] = (string)$data['quiz_img_url'];
        } elseif ($request->hasFile('quiz_img')) {
            $destDir = $this->ensureImageFolder();
            $ext     = strtolower($request->file('quiz_img')->getClientOriginalExtension() ?: 'jpg');
            $fname   = 'quizz_' . time() . '_' . Str::random(6) . '.' . $ext;
            $request->file('quiz_img')->move($destDir, $fname);
            $upd['quiz_img'] = 'assets/images/quizz/' . $fname;
        }

        if (empty($upd)) {
            return response()->json(['status'=>'noop','message'=>'Nothing to update'], 200);
        }

        $upd['updated_at'] = now();
        DB::table('quizz')->where('id',$id)->update($upd);

        $fresh = DB::table('quizz')->where('id',$id)->first();
        if ($fresh) {
            try { $fresh->question_count = DB::table('quizz_questions')->where('quiz_id', $fresh->id)->count(); }
            catch (\Throwable $e) { $fresh->question_count = 0; }
            try { $fresh->student_count  = DB::table('quizz_results')->where('quiz_id', $fresh->id)->distinct('user_id')->count('user_id'); }
            catch (\Throwable $e) { $fresh->student_count = 0; }
        }

        $this->logActivity($request, 'update', 'Updated quiz "'.($fresh->quiz_name ?? $row->quiz_name).'"', 'quizz', $id, array_keys($upd), (array)$row, $fresh ? (array)$fresh : null);

        return response()->json(['status'=>'success','message'=>'Quiz updated','data'=>$fresh]);
    }

    /* =========================
     * STATUS (PATCH /api/quizz/{id|uuid}/status)
     * ========================= */
    public function updateStatus(Request $request, string $key)
    {
        if ($resp = $this->requireRole($request, ['admin','super_admin'])) return $resp;

        $row = $this->findByKey($key);
        if (!$row) return response()->json(['error'=>'Quiz not found'], 404);

        $data = $request->validate([
            'status' => ['required', Rule::in(['active','archived'])],
        ]);

        DB::table('quizz')->where('id', $row->id)->update([
            'status'     => $data['status'],
            'updated_at' => now(),
        ]);

        $this->logActivity($request, 'status', 'Changed quiz status to '.$data['status'], 'quizz', (int)$row->id, ['status'], (array)$row, null);

        return response()->json(['status'=>'success','message'=>'Status updated']);
    }

    /* =========================
     * DELETE (soft) (DELETE /api/quizz/{id|uuid})
     * ========================= */
    public function destroy(Request $request, string $key)
    {
        if ($resp = $this->requireRole($request, ['admin','super_admin'])) return $resp;

        $row = $this->findByKey($key);
        if (!$row) return response()->json(['error'=>'Quiz not found'], 404);

        $before = (array)$row;

        DB::table('quizz')->where('id', $row->id)->update([
            'status'     => 'archived',
            'deleted_at' => now(),
            'updated_at' => now(),
        ]);

        $this->logActivity($request,'destroy','Archived/Deleted quiz "'.$row->quiz_name.'"','quizz',(int)$row->id,['status','deleted_at'],$before,null);

        return response()->json(['status'=>'success','message'=>'Quiz archived']);
    }

    /* =========================
     * RESTORE (PATCH /api/quizz/{id|uuid}/restore)
     * ========================= */
    public function restore(Request $request, string $key)
    {
        if ($resp = $this->requireRole($request, ['admin','super_admin'])) return $resp;

        $row = $this->findAnyByKey($key); // include soft-deleted
        if (!$row) return response()->json(['error' => 'Quiz not found'], 404);

        if ($row->deleted_at === null) {
            return response()->json(['status'=>'noop','message'=>'Quiz is not deleted'], 409);
        }

        $data = $request->validate([
            'status' => ['sometimes', Rule::in(['active','archived'])],
        ]);
        $newStatus = $data['status'] ?? 'active';

        DB::table('quizz')->where('id', $row->id)->update([
            'deleted_at' => null,
            'status'     => $newStatus,
            'updated_at' => now(),
        ]);

        $fresh = DB::table('quizz')->where('id', $row->id)->first();

        $this->logActivity(
            $request,
            'restore',
            'Restored quiz "'.($fresh->quiz_name ?? 'N/A').'"',
            'quizz',
            (int)$row->id,
            ['deleted_at','status'],
            (array)$row,
            $fresh ? (array)$fresh : null
        );

        $this->persistNotification([
            'title'     => 'Quiz restored',
            'message'   => '“'.($fresh->quiz_name ?? 'Quiz').'” has been restored.',
            'receivers' => $this->adminReceivers(),
            'metadata'  => [
                'action' => 'restored',
                'quiz'   => ['id' => (int)$row->id, 'uuid' => $row->uuid ?? null, 'status' => $newStatus],
                'restored_by' => $this->actor($request),
            ],
            'type'      => 'quizz',
            'priority'  => 'normal',
            'status'    => 'active',
        ]);

        return response()->json([
            'status'  => 'success',
            'message' => 'Quiz restored',
            'data'    => $fresh,
        ]);
    }

    /* =========================
     * FORCE DELETE (DELETE /api/quizz/{id|uuid}/force)
     * ========================= */
    public function forceDelete(Request $request, string $key)
    {
        if ($resp = $this->requireRole($request, ['admin','super_admin'])) return $resp;

        $row = $this->findAnyByKey($key);
        if (!$row) return response()->json(['error'=>'Quiz not found'], 404);

        $before = (array)$row;

        // Hard delete record
        DB::table('quizz')->where('id', $row->id)->delete();

        // Optional: cascade clean-ups (best-effort)
        try { DB::table('quizz_questions')->where('quiz_id', $row->id)->delete(); } catch (\Throwable $e) {}
        try { DB::table('quizz_results')->where('quiz_id', $row->id)->delete(); } catch (\Throwable $e) {}
        try { DB::table('quizz_notes')->where('quiz_id', $row->id)->delete(); } catch (\Throwable $e) {}

        $this->logActivity($request,'force','Permanently deleted quiz "'.($row->quiz_name ?? 'N/A').'"','quizz',(int)$row->id,null,$before,null);

        return response()->json(['status'=>'success','message'=>'Quiz permanently deleted']);
    }

    /* =========================
     * NOTES
     *  - GET  /api/quizz/{key}/notes
     *  - POST /api/quizz/{key}/notes {note:string}
     * ========================= */
    public function listNotes(Request $request, string $key)
    {
        if ($resp = $this->requireRole($request, ['admin','super_admin'])) return $resp;

        $row = $this->findAnyByKey($key);
        if (!$row) return response()->json(['error'=>'Quiz not found'], 404);

        $notes = DB::table('quizz_notes')
            ->where('quiz_id', $row->id)
            ->orderBy('created_at', 'desc')
            ->get();

        return response()->json(['status'=>'success','data'=>$notes]);
    }

    public function addNote(Request $request, string $key)
    {
        if ($resp = $this->requireRole($request, ['admin','super_admin'])) return $resp;

        $row = $this->findAnyByKey($key);
        if (!$row) return response()->json(['error'=>'Quiz not found'], 404);

        $data = $request->validate([
            'note' => ['required','string'],
        ]);

        $a = $this->actor($request);

        $id = DB::table('quizz_notes')->insertGetId([
            'quiz_id'          => (int)$row->id,
            'note'             => $data['note'],
            'created_by'       => $a['id'] ?: null,
            'created_by_role'  => $a['role'] ?: null,
            'created_at'       => now(),
            'updated_at'       => now(),
        ]);

        $this->logActivity($request,'update','Added note to quiz "'.($row->quiz_name ?? 'N/A').'"','quizz',(int)$row->id,['note'],null,['note'=>$data['note']]);

        $note = DB::table('quizz_notes')->where('id',$id)->first();

        return response()->json(['status'=>'success','message'=>'Note added','data'=>$note]);
    }
}
