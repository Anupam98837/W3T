<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\File;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Schema;
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
<<<<<<< HEAD


=======
>>>>>>> c91667b7c50beb5791b8e3fbcbc07f95ef790c11
public function viewQuizzesByBatch(Request $r, string $batchKey)
{
    $role = (string) ($r->attributes->get('auth_role') ?? '');
    $uid  = (int) ($r->attributes->get('auth_tokenable_id') ?? 0);

    if (!$role || !in_array($role, ['superadmin','admin','instructor','student'], true)) {
        return response()->json(['error' => 'Unauthorized Access'], 403);
    }

    $isStudent = $role === 'student';

    // resolve batch (id | uuid | slug)
    $bq = DB::table('batches')->whereNull('deleted_at');
    if (ctype_digit($batchKey)) {
        $bq->where('id', (int)$batchKey);
    } elseif (Str::isUuid($batchKey)) {
        $bq->where('uuid', $batchKey);
    } elseif (Schema::hasColumn('batches','slug')) {
        $bq->where('slug', $batchKey);
    }
    $batch = $bq->first();
    if (!$batch) {
        return response()->json(['error' => 'Batch not found'], 404);
    }

    // student must be enrolled
    if ($isStudent) {
        $bsUserCol = Schema::hasColumn('batch_students','user_id')
            ? 'user_id'
            : (Schema::hasColumn('batch_students','student_id') ? 'student_id' : null);

        if (!$bsUserCol) {
            return response()->json(['error'=>'Schema issue: batch_students needs user_id OR student_id'], 500);
        }

        $enrolled = DB::table('batch_students')
            ->where('batch_id', $batch->id)
            ->whereNull('deleted_at')
            ->where($bsUserCol, $uid)
            ->exists();

        if (!$enrolled) {
            return response()->json(['error' => 'Forbidden'], 403);
        }
    }

    $now = now();

    // Base query - ensure only assigned quizzes (inner join)
    $query = DB::table('batch_quizzes as bq')
        ->join('quizz as q', 'q.id', '=', 'bq.quiz_id')
        ->leftJoin('users as creator', 'creator.id', '=', 'q.created_by') // Join with users table for creator
        ->where('bq.batch_id', $batch->id)
        ->whereNull('bq.deleted_at')
        ->whereNull('q.deleted_at')
        ->where('bq.status', 'active')
        ->where('bq.assign_status', 1);

    // student visibility rules
    if ($isStudent) {
        $query->where('bq.publish_to_students', 1);
        $query->where('q.status', 'active');

        $query->where(function ($qb) use ($now) {
            $qb->whereNull('bq.available_from')
               ->orWhere('bq.available_from', '<=', $now);
        });
        $query->where(function ($qb) use ($now) {
            $qb->whereNull('bq.available_until')
               ->orWhere('bq.available_until', '>=', $now);
        });
    }

    // select fields
    $select = [
        // Quiz identification & content
        'q.id as id',
        'q.uuid as uuid',
        'q.quiz_name as title',
        'q.quiz_description as excerpt',
        'q.quiz_img',
        'q.instructions',
        'q.note',
        'q.is_public',
        'q.result_set_up_type',
        'q.result_release_date',
        'q.total_time',
        'q.total_questions',
        'q.is_question_random',
        'q.is_option_random',
        'q.status as quiz_status',
        'q.created_by', // Include created_by field

        // Creator information
        'creator.name as created_by_name', // Get creator's name

        // Batch quiz relationship info
        'bq.id as batch_quiz_id',
<<<<<<< HEAD
        'bq.uuid as batch_quizzes_uuid',
=======
        'bq.uuid as batch_quizzes_uuid',   // <-- ADDED: send batch_quizzes UUID
>>>>>>> c91667b7c50beb5791b8e3fbcbc07f95ef790c11
        'bq.display_order',
        'bq.available_from',
        'bq.available_until',
        'bq.assigned_at',
        'bq.publish_to_students',
        'bq.attempt_allowed',
        'bq.status as batch_status',
        'bq.assign_status as assign_status_flag',
    ];

    $query->select($select)
          ->orderBy('bq.display_order', 'asc')
          ->orderBy('bq.assigned_at', 'desc');

    // pagination
    $perPage = (int) $r->query('per_page', 20);
    $page    = (int) max(1, $r->query('page', 1));

    $paginator = $query->paginate($perPage, ['*'], 'page', $page);

    // Work on the raw items collection
    $rawItems = collect($paginator->items());

    // =======================
    // attempt usage maps
    // =======================
    $attemptUsageByQuiz       = [];
    $attemptUsageByBatchQuiz  = [];

    if ($isStudent && $rawItems->isNotEmpty()) {
        $quizIds      = $rawItems->pluck('id')->filter()->unique()->all();
        $batchQuizIds = $rawItems->pluck('batch_quiz_id')->filter()->unique()->all();

        if (!empty($quizIds)) {
            // how many results this user has per quiz (global)
            $attemptUsageByQuiz = DB::table('quizz_results')
                ->select('quiz_id', DB::raw('COUNT(*) as used'))
                ->where('user_id', $uid)
                ->whereIn('quiz_id', $quizIds)
                ->groupBy('quiz_id')
                ->pluck('used', 'quiz_id')
                ->toArray();
        }

        if (!empty($batchQuizIds)) {
            // how many results this user has per batch_quiz
            $attemptUsageByBatchQuiz = DB::table('quizz_attempt_batch as qab')
                ->join('quizz_results as r', 'r.attempt_id', '=', 'qab.attempt_id')
                ->select('qab.batch_quiz_id', DB::raw('COUNT(*) as used'))
                ->where('qab.user_id', $uid)
                ->whereIn('qab.batch_quiz_id', $batchQuizIds)
                ->groupBy('qab.batch_quiz_id')
                ->pluck('used', 'batch_quiz_id')
                ->toArray();
        }
    }

    // Map items
    $items = $rawItems->map(function ($quiz) use ($isStudent, $attemptUsageByQuiz, $attemptUsageByBatchQuiz) {

        // ==========================
        // attempts (allowed/used/rem)
        // ==========================
        $attemptAllowed   = null;
        $attemptUsed      = null;
        $attemptRemaining = null;

        if ($isStudent) {
            // batch-level configured limit
            $configuredAllowed = $quiz->attempt_allowed !== null
                ? (int) $quiz->attempt_allowed
                : 0;

            // how many attempts already used in this batch
            $attemptUsed = isset($attemptUsageByBatchQuiz[$quiz->batch_quiz_id])
                ? (int) $attemptUsageByBatchQuiz[$quiz->batch_quiz_id]
                : 0;

            if ($configuredAllowed > 0) {
                // finite limit for this batch
                $attemptAllowed   = $configuredAllowed;
                $attemptRemaining = max(0, $attemptAllowed - $attemptUsed);
            } else {
                // treat 0 / null as "unlimited" for now
                $attemptAllowed   = null;
                $attemptRemaining = null;
            }
        } else {
            // for admin/instructor just expose the configured value
            $attemptAllowed = $quiz->attempt_allowed !== null
                ? (int) $quiz->attempt_allowed
                : null;
        }

        return [
            'id'    => $quiz->id,
            'uuid'  => $quiz->uuid,
            'title' => $quiz->title,
            'excerpt' => $quiz->excerpt,

            // Quiz details
            'quiz_img'           => $quiz->quiz_img,
            'instructions'       => $quiz->instructions,
            'note'               => $quiz->note,
            'is_public'          => $quiz->is_public === 'yes' || $quiz->is_public === 1 || $quiz->is_public === true,
            'result_set_up_type' => $quiz->result_set_up_type,
            'result_release_date'=> $quiz->result_release_date
                ? \Carbon\Carbon::parse($quiz->result_release_date)->toDateTimeString()
                : null,
            'total_time'         => $quiz->total_time !== null ? (int)$quiz->total_time : null,
            'total_questions'    => $quiz->total_questions !== null ? (int)$quiz->total_questions : null,
            'is_question_random' => isset($quiz->is_question_random) ? (bool)$quiz->is_question_random : null,
            'is_option_random'   => isset($quiz->is_option_random) ? (bool)$quiz->is_option_random : null,
            'quiz_status'        => $quiz->quiz_status,

            // Creator information
            'created_by'      => $quiz->created_by ? (int)$quiz->created_by : null,
            'created_by_name' => $quiz->created_by_name,

            // Batch quiz fields
<<<<<<< HEAD
            'assigned'            => (bool) $quiz->assign_status_flag,
            'batch_quiz_id'       => $quiz->batch_quiz_id !== null ? (int)$quiz->batch_quiz_id : null,
            'batch_quizzes_uuid'  => isset($quiz->batch_quizzes_uuid) ? (string)$quiz->batch_quizzes_uuid : null,
            'display_order'       => $quiz->display_order !== null ? (int)$quiz->display_order : null,
            'batch_status'        => $quiz->batch_status ?? null,
=======
            'assigned' => (bool) $quiz->assign_status_flag,
            'batch_quiz_id' => $quiz->batch_quiz_id !== null ? (int)$quiz->batch_quiz_id : null,
            'batch_quizzes_uuid' => isset($quiz->batch_quizzes_uuid) ? (string)$quiz->batch_quizzes_uuid : null, // <-- ADDED
            'display_order' => $quiz->display_order !== null ? (int)$quiz->display_order : null,
            'batch_status' => $quiz->batch_status ?? null,
>>>>>>> c91667b7c50beb5791b8e3fbcbc07f95ef790c11
            'publish_to_students' => (bool)$quiz->publish_to_students,
            'available_from'      => $quiz->available_from
                ? \Carbon\Carbon::parse($quiz->available_from)->toDateTimeString()
                : null,
            'available_until'     => $quiz->available_until
                ? \Carbon\Carbon::parse($quiz->available_until)->toDateTimeString()
                : null,
            'assigned_at'         => $quiz->assigned_at
                ? \Carbon\Carbon::parse($quiz->assigned_at)->toDateTimeString()
                : null,

            // Attempts info (for UI)
            'attempt_allowed'   => $attemptAllowed,
            'attempt_used'      => $attemptUsed,
            'attempt_remaining' => $attemptRemaining,
        ];
    });

    return response()->json([
        'success' => true,
        'data'    => $items->values(),
        'pagination' => [
            'total'         => (int)$paginator->total(),
            'per_page'      => (int)$paginator->perPage(),
            'current_page'  => (int)$paginator->currentPage(),
            'last_page'     => (int)$paginator->lastPage(),
        ],
    ]);
}

<<<<<<< HEAD

=======
>>>>>>> c91667b7c50beb5791b8e3fbcbc07f95ef790c11
/**
 * DELETED INDEX (GET /api/quizz/deleted)
 * Lists soft-deleted quizzes (supports ?q=search, ?per_page=, ?page=, ?batch_uuid=, ?batch_id=)
 */
public function deletedIndex(Request $r)
{
    // Only admins / super_admins can list deleted quizzes
    if ($resp = $this->requireRole($r, ['admin','super_admin'])) return $resp;

    $page    = max(1, (int)$r->query('page', 1));
    $perPage = max(1, min(200, (int)$r->query('per_page', 20)));
    $qText   = trim((string)$r->query('q', ''));
    $batchUuid = $r->query('batch_uuid');
    $batchId   = $r->query('batch_id');

    // Base query: soft-deleted quizzes
    $q = DB::table('quizz as q')
        ->leftJoin('users as creator', 'creator.id', '=', 'q.created_by')
        ->whereNotNull('q.deleted_at');

    // Optional text search
    if ($qText !== '') {
        $q->where(function($w) use ($qText) {
            $w->where('q.quiz_name', 'like', "%{$qText}%")
              ->orWhere('q.quiz_description', 'like', "%{$qText}%")
              ->orWhere('q.uuid', 'like', "%{$qText}%");
        });
    }

    // Optional batch scoping - resolve batch via uuid or id and join batch_quizzes
    if ($batchUuid || $batchId) {
        $bq = DB::table('batches')->whereNull('deleted_at');
        if ($batchId && ctype_digit((string)$batchId)) {
            $bq->where('id', (int)$batchId);
        } elseif ($batchUuid && Str::isUuid((string)$batchUuid)) {
            $bq->where('uuid', $batchUuid);
        } elseif ($batchId && !ctype_digit((string)$batchId) && Schema::hasColumn('batches','slug')) {
            // allow slug via batch_id param if provided as string
            $bq->where('slug', $batchId);
        }
        $batch = $bq->first();
        if ($batch) {
            // join with batch_quizzes to narrow to those which were assigned to the batch
            $q->join('batch_quizzes as bq', function($join) use ($batch) {
                $join->on('bq.quiz_id', '=', 'q.id')->where('bq.batch_id', '=', $batch->id);
            });
            $q->whereNull('bq.deleted_at');
        } else {
            // if batch param was provided but not found, return empty set
            return response()->json([
                'success' => true,
                'data' => [],
                'pagination' => ['total' => 0, 'per_page' => $perPage, 'current_page' => $page, 'last_page' => 0]
            ]);
        }
    }

    // Select fields; include counts via subqueries (avoids N+1)
    $select = [
        'q.id',
        'q.uuid',
        'q.quiz_name as title',
        'q.quiz_description as excerpt',
        'q.quiz_img',
        'q.total_time',
        'q.total_questions',
        'q.total_attempts',
        'q.is_public',
        'q.result_set_up_type',
        'q.status',
        'q.created_by',
        'creator.name as created_by_name',
        'q.deleted_at',
        // subqueries
        DB::raw('(SELECT COUNT(*) FROM quizz_questions qq WHERE qq.quiz_id = q.id) AS question_count'),
        DB::raw('(SELECT COUNT(DISTINCT user_id) FROM quizz_results qr WHERE qr.quiz_id = q.id) AS student_count'),
    ];

    $query = $q->select($select)
               ->orderBy('q.deleted_at', 'desc');

    $paginator = $query->paginate($perPage, ['*'], 'page', $page);

    // Map items to consistent shape
    $items = collect($paginator->items())->map(function ($row) {
        return [
            'id' => (int) $row->id,
            'uuid' => $row->uuid,
            'title' => $row->title,
            'excerpt' => $row->excerpt,
            'quiz_img' => $row->quiz_img,
            'total_time' => $row->total_time !== null ? (int)$row->total_time : null,
            'total_questions' => $row->total_questions !== null ? (int)$row->total_questions : null,
            'total_attempts' => $row->total_attempts !== null ? (int)$row->total_attempts : null,
            'is_public' => $row->is_public === 'yes' || $row->is_public === 1 || $row->is_public === true,
            'result_set_up_type' => $row->result_set_up_type,
            'status' => $row->status,
            'created_by' => $row->created_by ? (int)$row->created_by : null,
            'created_by_name' => $row->created_by_name ?? null,
            'deleted_at' => $row->deleted_at ? \Carbon\Carbon::parse($row->deleted_at)->toDateTimeString() : null,
            'question_count' => isset($row->question_count) ? (int)$row->question_count : 0,
            'student_count' => isset($row->student_count) ? (int)$row->student_count : 0,
        ];
    })->values();

    return response()->json([
        'success' => true,
        'data' => $items,
        'pagination' => [
            'total' => (int) $paginator->total(),
            'per_page' => (int) $paginator->perPage(),
            'current_page' => (int) $paginator->currentPage(),
            'last_page' => (int) $paginator->lastPage(),
        ],
    ]);
}

}
