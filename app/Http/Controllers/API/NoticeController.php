<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;
use Carbon\Carbon;

class NoticeController extends Controller
{
    // Public media subdirectory under public/
    private const MEDIA_SUBDIR = 'assets/media/notices';

    /* -------------------------
     |  Helpers
     * ------------------------- */

    private function actor(Request $r): array
    {
        return [
            'role' => $r->attributes->get('auth_role'),
            'type' => $r->attributes->get('auth_tokenable_type'),
            'id'   => (int) ($r->attributes->get('auth_tokenable_id') ?? 0),
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

    private function appUrl(): string
    {
        return rtrim(config('app.url'), '/');
    }

    private function mediaBasePublicPath(): string
    {
        return public_path();
    }

    private function toPublicUrl(string $relativePath): string
    {
        $base = $this->appUrl();
        $rel  = ltrim(str_replace('\\','/',$relativePath), '/');
        return $base . '/' . $rel;
    }

    private function ensureDir(string $path): string
    {
        if (! File::exists($path)) {
            File::ensureDirectoryExists($path, 0755, true);
        }
        return $path;
    }

    private function uniqueSlug(string $title): string
    {
        $base = Str::slug(Str::limit($title, 120, ''));
        if ($base === '') $base = 'notice';
        $slug = $base;
        $i = 1;
        while (DB::table('notices')->where('slug', $slug)->exists()) {
            $slug = $base.'-'.(++$i);
        }
        return $slug;
    }

    private function genUuid(): string
    {
        return (string) Str::uuid();
    }

    private function jsonDecode($value): array
    {
        if (!$value) return [];
        try { $a = json_decode($value, true, 512, JSON_THROW_ON_ERROR); }
        catch (\Throwable $e) { return []; }
        return is_array($a) ? $a : [];
    }

    private function logWithActor(string $message, Request $r, array $context = [])
    {
        $actor = $this->actor($r);
        Log::info($message, array_merge([
            'actor_id' => $actor['id'],
            'actor_role' => $actor['role'],
        ], $context));
    }

    /**
     * Append uploaded files AND library_urls[] (remote URL attachments) to existing stored attachments.
     *
     * @param Request $r
     * @param string $storageFolder Folder name for uploaded files
     * @param array $existing Existing decoded attachments array
     * @return array Merged stored attachments array
     */
    protected function appendFilesAndLibraryUrls(Request $r, string $storageFolder, array $existing = []): array
    {
        // Normalize existing attachments to array
        $stored = $existing ?: [];
        if (!is_array($stored)) $stored = [];

        // Build dedupe keys set (by url or sha256)
        $seenUrl = [];
        $seenSha = [];

        foreach ($stored as $att) {
            if (!empty($att['url'])) $seenUrl[(string)$att['url']] = true;
            if (!empty($att['sha256'])) $seenSha[(string)$att['sha256']] = true;
        }

        // 1) Handle uploaded files (attachments[] / attachments)
        $files = [];
        if ($r->hasFile('attachments')) {
            $files = is_array($r->file('attachments')) ? $r->file('attachments') : [$r->file('attachments')];
        }

        if (!empty($files)) {
            // Store under public/assets/media/notices/{storageFolder}
            $destBase = $this->mediaBasePublicPath() . DIRECTORY_SEPARATOR . self::MEDIA_SUBDIR . DIRECTORY_SEPARATOR . $storageFolder;
            $this->ensureDir($destBase);

            foreach ($files as $file) {
                if (!$file || !$file->isValid()) continue;

                // Move & compute metadata
                $ext  = strtolower($file->getClientOriginalExtension() ?: 'bin');
                $fid  = Str::lower(Str::random(10));
                $name = $fid.'.'.$ext;

                try {
                    $file->move($destBase, $name);
                } catch (\Throwable $e) {
                    Log::error('Failed to move uploaded notice file (append helper): '.$e->getMessage(), ['dest'=>$destBase,'file'=>$name]);
                    continue;
                }

                $absPath = $destBase . DIRECTORY_SEPARATOR . $name;
                $relPath = self::MEDIA_SUBDIR . '/' . $storageFolder . '/' . $name;
                $mime = $file->getClientMimeType() ?: (is_file($absPath) ? mime_content_type($absPath) : 'application/octet-stream');
                $size = @filesize($absPath) ?: 0;
                $sha  = is_file($absPath) ? hash_file('sha256', $absPath) : null;
                $url  = $this->toPublicUrl($relPath);

                // Dedupe by sha if present
                if ($sha && isset($seenSha[$sha])) {
                    // Already present, skip
                    continue;
                }
                if ($url && isset($seenUrl[$url])) {
                    // Exact same URL already present, skip
                    continue;
                }

                // Register seen keys
                if ($sha) $seenSha[$sha] = true;
                if ($url) $seenUrl[$url] = true;

                $stored[] = [
                    'id'          => $fid,
                    'disk'        => 'public',
                    'path'        => $relPath,
                    'url'         => $url,
                    'mime'        => $mime,
                    'ext'         => $ext,
                    'size'        => $size,
                    'sha256'      => $sha,
                    'uploaded_at' => Carbon::now()->toIso8601String(),
                ];
            }
        }

        // 2) Handle library_urls[] (URL-based attachments)
        $libUrls = $r->input('library_urls', []);
        if ($libUrls && !is_array($libUrls)) $libUrls = [$libUrls];

        foreach ($libUrls as $u) {
            if (!$u || !is_string($u)) continue;
            $u = trim($u);
            if ($u === '') continue;

            // Normalize URL (avoid duplicates by exact URL)
            if (isset($seenUrl[$u])) continue;

            // Create id from hash of URL so it's stable and unique-ish
            $id = 'lib-' . substr(hash('sha256', $u), 0, 12);

            // Attempt to detect extension/mime (best-effort)
            $ext = pathinfo(parse_url($u, PHP_URL_PATH) ?: '', PATHINFO_EXTENSION);
            $ext = strtolower((string)$ext);
            $mime = '';
            if ($ext) {
                // Best-effort mime map for common types (optional)
                $map = [
                    'pdf'=>'application/pdf',
                    'png'=>'image/png',
                    'jpg'=>'image/jpeg','jpeg'=>'image/jpeg',
                    'mp4'=>'video/mp4','webm'=>'video/webm',
                    'gif'=>'image/gif'
                ];
                if (isset($map[$ext])) $mime = $map[$ext];
            }

            $stored[] = [
                'id'          => $id,
                'disk'        => 'external',
                'path'        => $u,
                'url'         => $u,
                'mime'        => $mime,
                'ext'         => $ext,
                'size'        => null,
                'sha256'      => null,
                'uploaded_at' => Carbon::now()->toIso8601String(),
            ];

            // Mark seen
            $seenUrl[$u] = true;
        }

        // Final: ensure uniqueness in $stored by url then by sha (stable)
        $uniq = [];
        $out = [];
        foreach ($stored as $att) {
            $key = '';
            if (!empty($att['url'])) $key = 'u:'.(string)$att['url'];
            elseif (!empty($att['sha256'])) $key = 's:'.(string)$att['sha256'];
            elseif (!empty($att['path'])) $key = 'p:'.(string)$att['path'];
            else $key = 'i:'.(string)($att['id'] ?? Str::random(6));

            if (isset($uniq[$key])) continue;
            $uniq[$key] = true;
            $out[] = $att;
        }

        return $out;
    }

    /* =========================================================
     |  Index (list) — filters for dropdown-driven page
     * ========================================================= */
    public function index(Request $r)
{
    if ($res = $this->requireRole($r, ['admin','superadmin','instructor'])) return $res;

    $q = DB::table('notices as n')
        ->leftJoin('course_modules as cm', 'cm.id', '=', 'n.course_module_id')
        ->leftJoin('batches as b', 'b.id', '=', 'n.batch_id')
        ->whereNull('n.deleted_at');

    if ($r->filled('course_id'))        $q->where('n.course_id', (int)$r->course_id);
    if ($r->filled('course_module_id')) $q->where('n.course_module_id', (int)$r->course_module_id);
    if ($r->filled('batch_id'))         $q->where('n.batch_id', (int)$r->batch_id);
    if ($r->filled('visibility_scope')) $q->where('n.visibility_scope', $r->visibility_scope);
    if ($r->filled('status'))           $q->where('n.status', $r->status);
    if ($r->filled('priority'))         $q->where('n.priority', $r->priority);

    if ($r->filled('search')) {
        $s = '%'.trim($r->search).'%';
        $q->where(function($w) use ($s){
            $w->where('n.title', 'like', $s)
              ->orWhere('n.message_html', 'like', $s);
        });
    }

    $per  = max(1, min(100, (int)($r->per_page ?? 20)));
    $page = max(1, (int)($r->page ?? 1));

    $total = (clone $q)->count();

    $rows = $q->orderByDesc('n.created_at')
        ->offset(($page-1)*$per)
        ->limit($per)
        ->select(
            'n.*',
            'cm.title as module_title',
            'b.badge_title as batch_title',   // adjust if your column is different
            'b.badge_title as batch_name'     // optional extra alias for frontend
        )
        ->get();

    return response()->json([
        'data' => $rows,
        'meta' => ['page'=>$page,'per_page'=>$per,'total'=>$total]
    ]);
}

    /* =========================================================
     |  Create
     * ========================================================= */
    public function store(Request $r)
    {
        if ($res = $this->requireRole($r, ['admin','superadmin','instructor'])) return $res;
        $actor = $this->actor($r);

        $v = Validator::make($r->all(), [
            'course_id'         => 'required|integer|exists:courses,id',
            'course_module_id'  => 'nullable|integer|exists:course_modules,id',
            'batch_id'          => 'nullable|integer|exists:batches,id',
            'visibility_scope'  => 'nullable|in:course,batch,module',
            'title'             => 'required|string|max:255',
            'message_html'      => 'nullable|string',
            'priority'          => 'nullable|in:low,normal,high',
            'status'            => 'nullable|in:draft,published,archived',
            'attachments.*'     => 'nullable|file|max:51200',
            'library_urls.*'    => 'nullable|url', // NEW: Library URLs support
            'created_at_ip'     => 'nullable|ip',
        ], [
            'attachments.*.max' => 'Each attachment must be <= 50 MB.'
        ]);

        if ($v->fails()) {
            return response()->json(['errors'=>$v->errors()], 422);
        }

        $uuid = $this->genUuid();
        $slug = $this->uniqueSlug($r->title);

        // Default visibility changed to 'course' to allow course-only notices
        $visibility = $r->input('visibility_scope', 'course');

        // Compute batch id (nullable) — avoid casting empty -> 0
        $batchId = $r->filled('batch_id') ? (int) $r->input('batch_id') : null;

        // Storage folder: use batch id when present, otherwise a course-scoped folder
        $storageFolder = $batchId !== null ? (string)$batchId : 'course_' . (int)$r->input('course_id');

        // Build attachments using helper (handles uploaded files + library_urls)
        $stored = $this->appendFilesAndLibraryUrls($r, $storageFolder, []);

        $now = Carbon::now();
        $id = DB::table('notices')->insertGetId([
            'uuid'              => $uuid,
            'course_id'         => (int)$r->course_id,
            'course_module_id'  => $r->input('course_module_id') ? (int)$r->course_module_id : null,
            'batch_id'          => $batchId, // NULL when not provided
            'visibility_scope'  => $visibility,
            'title'             => $r->title,
            'slug'              => $slug,
            'message_html'      => $r->input('message_html'),
            'attachments_json'  => $stored ? json_encode($stored) : null,
            'priority'          => $r->input('priority', 'normal'),
            'status'            => $r->input('status', 'draft'),
            'created_by'        => $actor['id'] ?: 0,
            'created_at'        => $now,
            'updated_at'        => $now,
        ]);

        return response()->json([
            'message' => 'Notice created',
            'id'      => $id,
            'uuid'    => $uuid,
            'slug'    => $slug,
            'attachments' => $stored,
        ], 201);
    }

    /* =========================================================
     |  Update (fields + add/remove attachments)
     * ========================================================= */
    public function update(Request $r, $id)
    {
        if ($res = $this->requireRole($r, ['admin','superadmin','instructor'])) return $res;

        $row = DB::table('notices')->where('id', (int)$id)->whereNull('deleted_at')->first();
        if (!$row) return response()->json(['error' => 'Not found'], 404);

        $v = Validator::make($r->all(), [
            'visibility_scope'     => 'nullable|in:course,batch,module',
            'title'                => 'sometimes|required|string|max:255',
            'message_html'         => 'nullable|string',
            'priority'             => 'nullable|in:low,normal,high',
            'status'               => 'nullable|in:draft,published,archived',
            'attachments.*'        => 'nullable|file|max:51200',
            'library_urls.*'       => 'nullable|url', // NEW: Library URLs support
            'remove_attachments'   => 'sometimes|array',
            'remove_attachments.*' => 'string',
            'regenerate_slug'      => 'sometimes|boolean',
        ]);

        if ($v->fails()) return response()->json(['errors' => $v->errors()], 422);

        $update = [];

        if ($r->filled('title')) {
            $update['title'] = $r->title;
            if ($r->boolean('regenerate_slug', false)) {
                $update['slug'] = $this->uniqueSlug($r->title);
            }
        }

        if ($r->exists('message_html')) $update['message_html'] = $r->input('message_html');
        if ($r->filled('visibility_scope')) $update['visibility_scope'] = $r->input('visibility_scope');
        if ($r->filled('priority')) $update['priority'] = $r->input('priority');
        if ($r->filled('status')) $update['status'] = $r->input('status');

        $stored = $this->jsonDecode($row->attachments_json ?? null);
        if (!is_array($stored)) $stored = [];

        // Handle removals
        $toRemove = $r->input('remove_attachments', []);
        if (!is_array($toRemove)) $toRemove = [$toRemove];

        if (!empty($toRemove)) {
            $remSet = array_flip(array_map('strval', $toRemove));
            $kept = [];
            foreach ($stored as $att) {
                $attId = isset($att['id']) ? (string)$att['id'] : (string)($att['path'] ?? ($att['url'] ?? ''));
                if ($attId !== '' && isset($remSet[$attId])) {
                    // Attempt to remove file (best-effort) - only for locally stored files
                    try {
                        if (!empty($att['path']) && $att['disk'] === 'public') {
                            // If path is public assets relative path: remove from public
                            $p = $att['path'];
                            if (strpos($p, self::MEDIA_SUBDIR) === 0 || strpos($p, 'notices/') === 0) {
                                $candidate = $this->mediaBasePublicPath() . DIRECTORY_SEPARATOR . str_replace('/', DIRECTORY_SEPARATOR, ltrim($p, '/'));
                                if (File::exists($candidate) && is_file($candidate)) {
                                    File::delete($candidate);
                                }
                            }
                        }
                        // Don't delete external URLs (disk = 'external')
                    } catch (\Throwable $ex) {
                        Log::warning('Failed to delete notice attachment during update', ['path' => $att['path'] ?? null, 'error' => $ex->getMessage()]);
                    }
                    continue; // removed
                }
                $kept[] = $att;
            }
            $stored = $kept;
        }

        // Append new attachments using helper (handles uploaded files + library_urls)
        $storageFolder = $row->batch_id ? (string) $row->batch_id : 'course_' . (int) ($row->course_id ?? 0);
        $stored = $this->appendFilesAndLibraryUrls($r, $storageFolder, $stored);

        // Persist updated attachments
        $update['attachments_json'] = $stored ? json_encode($stored) : null;
        $update['updated_at'] = Carbon::now();

        DB::table('notices')->where('id', (int)$id)->update($update);

        return response()->json([
            'message' => 'Notice updated',
            'id'      => (int)$id,
            'attachments' => $stored,
        ]);
    }
    public function archive(Request $r, $id)
{
    if ($res = $this->requireRole($r, ['admin','superadmin','instructor'])) return $res;

    $row = DB::table('notices')->where('id', (int)$id)->whereNull('deleted_at')->first();
    if (!$row) return response()->json(['error' => 'Not found'], 404);

    DB::table('notices')->where('id', (int)$id)->update([
        'status'     => 'archived',
        'updated_at' => now(),
    ]);

    return response()->json(['message' => 'Notice archived']);
}
public function unarchive(Request $r, $id)
{
    if ($res = $this->requireRole($r, ['admin','superadmin','instructor'])) return $res;

    $row = DB::table('notices')->where('id', (int)$id)->whereNull('deleted_at')->first();
    if (!$row) return response()->json(['error' => 'Not found'], 404);

    DB::table('notices')->where('id', (int)$id)->update([
        'status'     => 'published',   // or draft if you want
        'updated_at' => now(),
    ]);

    return response()->json(['message' => 'Notice unarchived']);
}

    /* =========================================================
     |  Soft delete
     * ========================================================= */
    public function destroy(Request $r, $id)
    {
        if ($res = $this->requireRole($r, ['admin','superadmin','instructor'])) return $res;

        $row = DB::table('notices')->where('id', (int)$id)->whereNull('deleted_at')->first();
        if (!$row) return response()->json(['error'=>'Not found'], 404);

        DB::table('notices')->where('id', (int)$id)->update([
            'deleted_at' => Carbon::now()
        ]);

        return response()->json(['message'=>'Notice moved to bin']);
    }

    /* =========================================================
     |  Force delete (hard)
     * ========================================================= */
    public function forceDelete(Request $r, $id)
    {
        if ($res = $this->requireRole($r, ['admin','superadmin'])) return $res;

        $row = DB::table('notices')->where('id', (int)$id)->first();
        if (!$row) return response()->json(['error'=>'Not found'], 404);

        // Delete attached files (best-effort) - only local files
        $attachments = $this->jsonDecode($row->attachments_json ?? null);
        foreach ($attachments as $a) {
            try {
                // Only delete locally stored files, not external URLs
                if (($a['disk'] ?? '') === 'public' && !empty($a['path'])) {
                    $p = $a['path'];
                    if (strpos($p, self::MEDIA_SUBDIR) === 0 || strpos($p, 'notices/') === 0) {
                        $absPath = $this->mediaBasePublicPath() . DIRECTORY_SEPARATOR . str_replace('/', DIRECTORY_SEPARATOR, ltrim($p, '/'));
                    } else {
                        $absPath = storage_path('app/' . ltrim($p, '/'));
                    }
                    if (is_file($absPath)) {
                        @unlink($absPath);
                    }
                }
            } catch (\Throwable $ex) {
                Log::warning('forceDelete: failed to remove notice attachment', ['error'=>$ex->getMessage(),'attachment'=>$a]);
            }
        }

        DB::table('notices')->where('id', (int)$id)->delete();

        return response()->json(['message'=>'Notice permanently deleted']);
    }

    /* =========================================================
     |  Bin listing (soft-deleted) — admin only
     * ========================================================= */
    public function indexDeleted(Request $r)
{
    if ($res = $this->requireRole($r, ['admin','superadmin'])) return $res;

    $q = DB::table('notices as n')
        ->leftJoin('course_modules as cm', 'cm.id', '=', 'n.course_module_id')
        ->leftJoin('batches as b', 'b.id', '=', 'n.batch_id')
        ->whereNotNull('n.deleted_at');

    if ($r->filled('course_id'))        $q->where('n.course_id', (int)$r->course_id);
    if ($r->filled('course_module_id')) $q->where('n.course_module_id', (int)$r->course_module_id);
    if ($r->filled('batch_id'))         $q->where('n.batch_id', (int)$r->batch_id);
    if ($r->filled('search')) {
        $s = '%'.trim($r->search).'%';
        $q->where(function($w) use ($s){
            $w->where('n.title', 'like', $s)
              ->orWhere('n.message_html', 'like', $s);
        });
    }

    $per  = max(1, min(100, (int)($r->per_page ?? 20)));
    $page = max(1, (int)($r->page ?? 1));

    $total = (clone $q)->count();

    $rows = $q->orderByDesc('n.deleted_at')
        ->offset(($page-1)*$per)
        ->limit($per)
        ->select(
            'n.*',
            'cm.title as module_title',
            'b.badge_title as batch_title',
            'b.badge_title as batch_name'
        )
        ->get();

    return response()->json([
        'data' => $rows,
        'meta' => ['page'=>$page,'per_page'=>$per,'total'=>$total]
    ]);
}

    /* =========================================================
     |  Restore from bin — admin only
     * ========================================================= */
    public function restore(Request $r, $id)
    {
        if ($res = $this->requireRole($r, ['admin','superadmin'])) return $res;

        $row = DB::table('notices')->where('id', (int)$id)->whereNotNull('deleted_at')->first();
        if (!$row) return response()->json(['error'=>'Not found'], 404);

        DB::table('notices')->where('id', (int)$id)->update(['deleted_at' => null, 'updated_at' => Carbon::now()]);

        return response()->json(['message'=>'Notice restored']);
    }

    /* =========================================================
     |  Show one by uuid
     * ========================================================= */
    public function showByUuid(Request $r, string $uuid)
    {
        if ($res = $this->requireRole($r, ['admin','superadmin','instructor'])) return $res;

        $row = DB::table('notices')->where('uuid', $uuid)->whereNull('deleted_at')->first();
        if (!$row) return response()->json(['error'=>'Not found'], 404);

        $row->attachments = $this->jsonDecode($row->attachments_json ?? null);
        return response()->json($row);
    }

    /* =========================================================
     |  Stream attachment (inline view)
     * ========================================================= */
    public function streamInline(Request $r, string $uuid, string $fileId)
    {
        // Roles allowed to view attachments (adjust as needed)
        if ($res = $this->requireRole($r, ['admin','superadmin','instructor','student'])) return $res;

        $row = DB::table('notices')->where('uuid', $uuid)->whereNull('deleted_at')->first();
        if (!$row) return response()->json(['error' => 'Not found'], 404);

        $attachments = $this->jsonDecode($row->attachments_json ?? null);
        $file = null;
        foreach ($attachments as $a) {
            if (($a['id'] ?? null) === $fileId) { 
                $file = $a; 
                break; 
            }
        }
        
        if (!$file) return response()->json(['error' => 'File not found'], 404);
        
        // For external URLs (library files), redirect to the URL instead of streaming
        if (($file['disk'] ?? '') === 'external' && !empty($file['url'])) {
            return response()->json([
                'message' => 'External file URL',
                'url' => $file['url'],
                'redirect' => true
            ]);
        }

        // Resolve absolute path for local files
        $absPath = null;
        if (!empty($file['path'])) {
            $p = $file['path'];
            if (strpos($p, self::MEDIA_SUBDIR) === 0 || strpos($p, 'notices/') === 0) {
                $absPath = $this->mediaBasePublicPath() . DIRECTORY_SEPARATOR . str_replace('/', DIRECTORY_SEPARATOR, ltrim($p, '/'));
            } else {
                $absPath = storage_path('app/' . ltrim($p, '/'));
            }
        }

        if (!$absPath || !is_file($absPath)) {
            return response()->json(['error'=>'File missing on server'], 410);
        }

        $mime = $file['mime'] ?? mime_content_type($absPath) ?? 'application/octet-stream';
        $ext  = $file['ext'] ?? pathinfo($absPath, PATHINFO_EXTENSION);
        $name = "view.{$ext}";

        $headers = [
            'Content-Type'              => $mime,
            'Content-Disposition'       => 'inline; filename="'.$name.'"',
            'X-Content-Type-Options'    => 'nosniff',
            'Cache-Control'             => 'no-store, no-cache, must-revalidate, max-age=0',
            'Pragma'                    => 'no-cache',
            'Expires'                   => '0',
        ];

        return response()->file($absPath, $headers);
    }

    /* =========================================================
     |  View Notices by Batch (RBAC-aware)
     * ========================================================= */
     public function viewByBatch(Request $r, string $batchKey)
     {
         // ---- roles
         $role = (string) $r->attributes->get('auth_role');
         $uid  = (int) ($r->attributes->get('auth_tokenable_id') ?? 0);
         if (!$role || !in_array($role, ['superadmin','admin','instructor','student'], true)) {
             return response()->json(['error' => 'Unauthorized Access'], 403);
         }
         $isAdminLike = in_array($role, ['superadmin','admin'], true);
         $isInstructor = $role === 'instructor';
         $isStudent    = $role === 'student';

         // resolve batch
         $bq = DB::table('batches')->whereNull('deleted_at');
         if (ctype_digit($batchKey)) {
             $bq->where('id', (int)$batchKey);
         } elseif (Str::isUuid($batchKey)) {
             $bq->where('uuid', $batchKey);
         } elseif (Schema::hasColumn('batches','slug')) {
             $bq->where('slug', $batchKey);
         } else {
             return response()->json(['error' => 'Batch not found'], 404);
         }
         $batch = $bq->first();
         if (!$batch) return response()->json(['error' => 'Batch not found'], 404);

         // pivot detection
         $biUserCol = Schema::hasColumn('batch_instructors','user_id')
             ? 'user_id'
             : (Schema::hasColumn('batch_instructors','instructor_id') ? 'instructor_id' : null);

         $bsUserCol = Schema::hasColumn('batch_students','user_id')
             ? 'user_id'
             : (Schema::hasColumn('batch_students','student_id') ? 'student_id' : null);

         // RBAC: instructor assigned?
         if ($isInstructor) {
             if (!$biUserCol) {
                 return response()->json(['error'=>'Schema issue: batch_instructors needs user_id OR instructor_id'], 500);
             }
             $assigned = DB::table('batch_instructors')
                 ->where('batch_id', $batch->id)
                 ->whereNull('deleted_at')
                 ->where($biUserCol, $uid)
                 ->exists();
             if (!$assigned) return response()->json(['error' => 'Forbidden'], 403);
         }

         if ($isStudent) {
             if (!$bsUserCol) {
                 return response()->json(['error'=>'Schema issue: batch_students needs user_id OR student_id'], 500);
             }
             $enrolled = DB::table('batch_students')
                 ->where('batch_id', $batch->id)
                 ->whereNull('deleted_at')
                 ->where($bsUserCol, $uid)
                 ->exists();
             if (!$enrolled) return response()->json(['error' => 'Forbidden'], 403);
         }

         // load course
         $course = DB::table('courses')
             ->where('id', $batch->course_id)
             ->whereNull('deleted_at')
             ->first();
         if (!$course) {
             return response()->json(['error' => 'Course not found for this batch'], 404);
         }

         // load modules for this course (students see published only)
         $isStaff = $isAdminLike || $isInstructor;
         $modQ = DB::table('course_modules')
             ->where('course_id', $course->id)
             ->whereNull('deleted_at')
             ->orderBy('order_no')->orderBy('id');
         if (!$isStaff) $modQ->where('status', 'published');
         $modules = $modQ->get();

         // load notices for this batch
         $nQ = DB::table('notices as n')
             ->leftJoin('course_modules as cm', 'cm.id', '=', 'n.course_module_id')
             ->leftJoin('users as creator', 'creator.id', '=', 'n.created_by')
             ->where('n.batch_id', $batch->id)
             ->whereNull('n.deleted_at')
             ->whereNull('cm.deleted_at')
             ->select(
                 'n.id',
                 'n.uuid',
                 'n.title',
                 'n.slug',
                 'n.message_html',
                 'n.visibility_scope',
                 // select the DB column attachments_json but alias to attachments so existing code works
                 'n.attachments_json as attachments',
                 'n.priority',
                 'n.status',
                 'n.course_module_id',
                 'n.created_by',
                 'cm.title as module_title',
                 'cm.uuid as module_uuid',
                 'n.created_at',
                 'n.updated_at',
                 'creator.name as created_by_name'
             )
             ->orderBy('cm.order_no')
             ->orderBy('n.created_at', 'desc');

        if (!$isStaff) {
            $nQ->where('cm.status', 'published');
        }

        $notices = $nQ->get();

        // group by module
        $byModule = [];
        foreach ($notices as $notice) {
            $moduleId = $notice->course_module_id;
            if (!isset($byModule[$moduleId])) {
                $byModule[$moduleId] = [
                    'module' => [
                        'id' => $notice->course_module_id ? (int)$notice->course_module_id : null,
                        'uuid' => $notice->module_uuid,
                        'title' => $notice->module_title,
                    ],
                    'notices' => []
                ];
            }

            $attachments = $this->jsonDecode($notice->attachments ?? null);
            $nData = [
                'id' => (int)$notice->id,
                'uuid' => $notice->uuid,
                'title' => $notice->title,
                'slug' => $notice->slug,
                'message_html' => $notice->message_html,
                'visibility_scope' => $notice->visibility_scope,
                'priority' => $notice->priority,
                'status' => $notice->status,
                'attachments' => $attachments,
                'created_by' => $notice->created_by ? (int)$notice->created_by : null,
                'created_by_name' => $notice->created_by_name,
                'created_at' => $notice->created_at,
                'updated_at' => $notice->updated_at,
            ];

            // Add stream URLs for local files, keep external URLs as-is
            foreach ($nData['attachments'] as &$attachment) {
                if (isset($attachment['id']) && ($attachment['disk'] ?? '') !== 'external') {
                    $attachment['stream_url'] = $this->appUrl() . "/api/notices/stream/{$notice->uuid}/{$attachment['id']}";
                }
            }

            $byModule[$moduleId]['notices'][] = $nData;
        }

        $modulesWithNotices = array_values($byModule);

        // instructors list (if pivot exists)
        $instructors = collect();
        if ($biUserCol) {
            $instructors = DB::table('batch_instructors as bi')
                ->join('users as u', function($j) use ($biUserCol){
                    $j->on('u.id', '=', DB::raw("bi.$biUserCol"));
                })
                ->where('bi.batch_id', $batch->id)
                ->whereNull('bi.deleted_at')
                ->whereNull('u.deleted_at')
                ->select('u.id','u.uuid','u.name','u.email','u.role')
                ->get()
                ->map(fn($u) => [
                    'id'    => (int)$u->id,
                    'uuid'  => $u->uuid,
                    'name'  => $u->name,
                    'email' => $u->email,
                    'role'  => $u->role,
                ])
                ->values();
        }

        $studentsCount = DB::table('batch_students')
            ->where('batch_id', $batch->id)
            ->whereNull('deleted_at')
            ->count();

        $noticesCount = DB::table('notices')
            ->where('batch_id', $batch->id)
            ->whereNull('deleted_at')
            ->count();

        $payload = [
            'batch' => (array)$batch,
            'course' => [
                'id' => (int)$course->id,
                'uuid' => $course->uuid,
                'title' => $course->title,
                'slug' => $course->slug,
            ],
            'modules_with_notices' => $modulesWithNotices,
            'all_modules' => $modules->map(fn($m) => [
                'id' => (int)$m->id,
                'uuid' => $m->uuid,
                'title' => $m->title,
                'status' => $m->status,
                'order_no' => (int)$m->order_no,
            ])->values(),
            'instructors' => $instructors,
            'stats' => [
                'students_count' => (int)$studentsCount,
                'notices_count' => (int)$noticesCount,
                'modules_count' => count($modules),
                'you_are_instructor' => $isInstructor,
                'you_are_student' => $isStudent,
            ],
            'permissions' => [
                'can_view_unpublished_modules' => $isAdminLike || $isInstructor,
                'can_view_unpublished_notices' => $isAdminLike || $isInstructor,
                'can_create_notices' => $isAdminLike || $isInstructor,
            ],
        ];

        $this->logWithActor('[Notices View By Batch] payload prepared', $r, [
            'batch_id' => (int)$batch->id,
            'course_id' => (int)$course->id,
            'notices_count' => $noticesCount,
            'modules_count' => count($modulesWithNotices),
            'role' => $role,
        ]);

        return response()->json(['data' => $payload]);
     }

    /* =========================================================
     |  Create notice by batch (resolve batch by id|uuid|slug)
     * ========================================================= */
    public function storeByBatch(Request $r, string $batchKey)
    {
        if ($res = $this->requireRole($r, ['admin','superadmin','instructor'])) return $res;
        $actor = $this->actor($r);
        $role = $actor['role'];
        $uid  = $actor['id'];

        // resolve batch
        $bq = DB::table('batches')->whereNull('deleted_at');
        if (ctype_digit($batchKey)) {
            $bq->where('id', (int)$batchKey);
        } elseif (Str::isUuid($batchKey)) {
            $bq->where('uuid', $batchKey);
        } elseif (Schema::hasColumn('batches', 'slug')) {
            $bq->where('slug', $batchKey);
        } else {
            return response()->json(['error' => 'Batch not found'], 404);
        }
        $batch = $bq->first();
        if (!$batch) return response()->json(['error' => 'Batch not found'], 404);

        if ($role === 'instructor') {
            $biUserCol = Schema::hasColumn('batch_instructors','user_id')
                ? 'user_id'
                : (Schema::hasColumn('batch_instructors','instructor_id') ? 'instructor_id' : null);
            if (!$biUserCol) {
                return response()->json(['error'=>'Schema issue: batch_instructors needs user_id OR instructor_id'], 500);
            }
            $assigned = DB::table('batch_instructors')
                ->where('batch_id', $batch->id)
                ->whereNull('deleted_at')
                ->where($biUserCol, $uid)
                ->exists();
            if (!$assigned) return response()->json(['error' => 'Forbidden'], 403);
        }

        $courseId = (int) ($batch->course_id ?? 0);

        $v = Validator::make($r->all(), [
            'course_module_id' => 'sometimes|nullable|integer|exists:course_modules,id',
            'module_uuid'      => 'sometimes|nullable|uuid|exists:course_modules,uuid',
            'title'            => 'required|string|max:255',
            'message_html'     => 'nullable|string',
            'visibility_scope' => 'nullable|in:course,batch,module',
            'priority'         => 'nullable|in:low,normal,high',
            'status'           => 'nullable|in:draft,published,archived',
            'attachments.*'    => 'nullable|file|max:51200',
            'library_urls.*'   => 'nullable|url', // NEW: Library URLs support
        ], [
            'attachments.*.max' => 'Each attachment must be <= 50 MB.'
        ]);
        if ($v->fails()) return response()->json(['errors' => $v->errors()], 422);

        $moduleId = null;
        if ($r->filled('course_module_id')) {
            $module = DB::table('course_modules')->where('id', (int)$r->course_module_id)->whereNull('deleted_at')->first();
            if (!$module) {
                return response()->json(['errors' => ['course_module_id' => ['Course module not found']]], 422);
            }
            $moduleId = (int)$module->id;
        } elseif ($r->filled('module_uuid')) {
            $modUuid = $r->input('module_uuid');
            if ($modUuid && Str::isUuid($modUuid)) {
                $module = DB::table('course_modules')->where('uuid', $modUuid)->whereNull('deleted_at')->first();
                if (!$module) {
                    return response()->json(['errors' => ['module_uuid' => ['Course module (uuid) not found']]], 422);
                }
                $moduleId = (int)$module->id;
            }
        } else {
            $modsQuery = DB::table('course_modules')
                ->where('course_id', $courseId)
                ->whereNull('deleted_at')
                ->orderBy('order_no')->orderBy('id');
            $modules = $modsQuery->get();
            if ($modules->count() === 1) {
                $moduleId = (int)$modules->first()->id;
            } else {
                $published = $modules->firstWhere('status', 'published');
                if ($published) $moduleId = (int)$published->id;
            }
        }

        if ($moduleId !== null) {
            $moduleCheck = DB::table('course_modules')->where('id', $moduleId)->whereNull('deleted_at')->first();
            if (!$moduleCheck || (int)$moduleCheck->course_id !== $courseId) {
                return response()->json(['errors' => ['course_module_id' => ['Course module does not belong to this batch\'s course']]], 422);
            }
        }

        $uuid = $this->genUuid();
        $slug = $this->uniqueSlug($r->title);
        $visibility = $r->input('visibility_scope', 'batch');

        // Build attachments using helper (handles uploaded files + library_urls)
        $stored = $this->appendFilesAndLibraryUrls($r, (string)$batch->id, []);

        $now = Carbon::now();
        $id = DB::table('notices')->insertGetId([
            'uuid'              => $uuid,
            'course_id'         => $courseId,
            'course_module_id'  => $moduleId !== null ? $moduleId : null,
            'batch_id'          => (int)$batch->id,
            'visibility_scope'  => $visibility,
            'title'             => $r->title,
            'slug'              => $slug,
            'message_html'      => $r->input('message_html'),
            'attachments_json'  => $stored ? json_encode($stored) : null,
            'priority'          => $r->input('priority', 'normal'),
            'status'            => $r->input('status', 'draft'),
            'created_by'        => $actor['id'] ?: 0,
            'created_at'        => $now,
            'updated_at'        => $now,
        ]);

        return response()->json([
            'message' => 'Notice created',
            'id'      => $id,
            'uuid'    => $uuid,
            'slug'    => $slug,
            'attachments' => $stored,
        ], 201);
    }

    /* =========================================================
     |  Bin by Batch (soft-deleted notices for a given batch)
     * ========================================================= */
    public function binByBatch(Request $r, string $batchKey)
    {
        if ($res = $this->requireRole($r, ['admin','superadmin','instructor'])) return $res;
        $actor = $this->actor($r);
        $uid   = $actor['id'];
        $role  = $actor['role'];

        $bq = DB::table('batches')->whereNull('deleted_at');
        if (ctype_digit($batchKey)) {
            $bq->where('id', (int)$batchKey);
        } elseif (Str::isUuid($batchKey)) {
            $bq->where('uuid', $batchKey);
        } elseif (Schema::hasColumn('batches', 'slug')) {
            $bq->where('slug', $batchKey);
        } else {
            return response()->json(['error' => 'Batch not found'], 404);
        }
        $batch = $bq->first();
        if (!$batch) return response()->json(['error' => 'Batch not found'], 404);

        if ($role === 'instructor') {
            $biUserCol = Schema::hasColumn('batch_instructors','user_id')
                ? 'user_id'
                : (Schema::hasColumn('batch_instructors','instructor_id') ? 'instructor_id' : null);

            if (!$biUserCol) {
                return response()->json(['error' => 'Schema issue: batch_instructors needs user_id or instructor_id'], 500);
            }

            $assigned = DB::table('batch_instructors')
                ->where('batch_id', $batch->id)
                ->whereNull('deleted_at')
                ->where($biUserCol, $uid)
                ->exists();

            if (!$assigned) return response()->json(['error' => 'Forbidden'], 403);
        }

        $items = DB::table('notices')
            ->where('batch_id', $batch->id)
            ->whereNotNull('deleted_at')
            ->orderBy('deleted_at', 'desc')
            ->get()
            ->map(function($row){
                if (is_string($row->attachments_json)) {
                    try { $row->attachments = json_decode($row->attachments_json, true) ?: []; }
                    catch (\Throwable $e) { $row->attachments = []; }
                } else {
                    $row->attachments = [];
                }
                return $row;
            });

        return response()->json([
            'message' => 'Deleted items (bin) for batch',
            'batch_uuid' => $batch->uuid,
            'data' => $items,
        ], 200);
    }
}
