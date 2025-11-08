<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Carbon\Carbon;

class StudyMaterialController extends Controller
{
    /* =========================================================
     |  Auth / Actor helpers  (same pattern you use elsewhere)
     * ========================================================= */
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

    private function ensureDir(string $path): string
    {
        if (!File::exists($path)) {
            File::makeDirectory($path, 0775, true);
        }
        return $path;
    }

    private function uniqueSlug(string $title): string
    {
        $base = Str::slug(Str::limit($title, 120, ''));
        if ($base === '') $base = 'study-material';
        $slug = $base;
        $i = 1;
        while (DB::table('study_materials')->where('slug', $slug)->exists()) {
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

    /* =========================================================
     |  List (filters for dropdown-driven page)
     * ========================================================= */
    public function index(Request $r)
    {
        if ($res = $this->requireRole($r, ['admin','superadmin'])) return $res;

        $q  = DB::table('study_materials')->whereNull('deleted_at');

        if ($r->filled('course_id'))        $q->where('course_id', (int)$r->course_id);
        if ($r->filled('course_module_id')) $q->where('course_module_id', (int)$r->course_module_id);
        if ($r->filled('batch_id'))         $q->where('batch_id', (int)$r->batch_id);
        if ($r->filled('search')) {
            $s = '%'.trim($r->search).'%';
            $q->where(function($w) use ($s){
                $w->where('title', 'like', $s)->orWhere('description', 'like', $s);
            });
        }

        $per = max(1, min(100, (int)($r->per_page ?? 20)));
        $page = max(1, (int)($r->page ?? 1));

        $total = (clone $q)->count();
        $rows = $q->orderByDesc('created_at')
                  ->offset(($page-1)*$per)
                  ->limit($per)
                  ->get();

        return response()->json([
            'data' => $rows,
            'meta' => ['page'=>$page,'per_page'=>$per,'total'=>$total]
        ]);
    }

    /* =========================================================
     |  Create
     |  - saves files under storage/app/batchStudyMaterial/{batch_id}
     |  - stores full streaming URL in attachment JSON
     * ========================================================= */
    public function store(Request $r)
    {
        if ($res = $this->requireRole($r, ['admin','superadmin'])) return $res;
        $actor = $this->actor($r);

        $v = Validator::make($r->all(), [
            'course_id'         => 'required|integer|exists:courses,id',
            'course_module_id'  => 'required|integer|exists:course_modules,id',
            'batch_id'          => 'required|integer|exists:batches,id',
            'title'             => 'required|string|max:255',
            'description'       => 'nullable|string',
            'view_policy'       => 'nullable|in:inline_only,downloadable',
            // accept multiple inputs: attachments[] or attachments
            'attachments.*'     => 'nullable|file|max:51200', // 50MB each
        ], [
            'attachments.*.max' => 'Each attachment must be <= 50 MB.'
        ]);

        if ($v->fails()) {
            return response()->json(['errors'=>$v->errors()], 422);
        }

        $uuid = $this->genUuid();
        $slug = $this->uniqueSlug($r->title);
        $policy = $r->input('view_policy', 'inline_only');

        // Collect files (support both "attachments" and "attachments[]")
        $files = [];
        if ($r->hasFile('attachments')) {
            $files = is_array($r->file('attachments')) ? $r->file('attachments') : [$r->file('attachments')];
        }

        $stored = [];
        if (!empty($files)) {
            $root = $this->ensureDir(storage_path('app/batchStudyMaterial/'.(int)$r->batch_id));
            foreach ($files as $file) {
                if (!$file || !$file->isValid()) continue;

                $ext  = strtolower($file->getClientOriginalExtension() ?: 'bin');
                $fid  = Str::lower(Str::random(10));
                $name = $fid.'.'.$ext;

                // move uploaded file to our private folder
                $file->move($root, $name);

                $absPath = $root.DIRECTORY_SEPARATOR.$name;
                $relPath = 'batchStudyMaterial/'.(int)$r->batch_id.'/'.$name;

                // mime detection (fallback)
                $mime = $file->getClientMimeType() ?: mime_content_type($absPath) ?: 'application/octet-stream';
                $size = @filesize($absPath) ?: 0;
                $sha  = hash_file('sha256', $absPath);

                // full streaming URL that frontend will use in <iframe>/<img>/<video>
                $url  = $this->appUrl()."/api/study-materials/stream/{$uuid}/{$fid}";

                $stored[] = [
                    'id'          => $fid,
                    'disk'        => 'local',
                    'path'        => $relPath,                // relative to storage/app
                    'url'         => $url,                    // full link (stream)
                    'mime'        => $mime,
                    'ext'         => $ext,
                    'size'        => $size,
                    'sha256'      => $sha,
                    'uploaded_at' => Carbon::now()->toIso8601String(),
                ];
            }
        }

        $now = Carbon::now();
        $id = DB::table('study_materials')->insertGetId([
            'uuid'               => $uuid,
            'course_id'          => (int)$r->course_id,
            'course_module_id'   => (int)$r->course_module_id,
            'batch_id'           => (int)$r->batch_id,
            'title'              => $r->title,
            'slug'               => $slug,
            'description'        => $r->input('description'),
            'attachment'         => $stored ? json_encode($stored) : null,
            'attachment_count'   => count($stored),
            'view_policy'        => $policy,
            'created_by'         => $actor['id'] ?: 0,
            'created_at'         => $now,
            'updated_at'         => $now,
        ]);

        return response()->json([
            'message' => 'Study material created',
            'id'      => $id,
            'uuid'    => $uuid,
            'slug'    => $slug,
            'attachments' => $stored,
        ], 201);
    }

    /* =========================================================
     |  Update basic fields and (optionally) add more files
     * ========================================================= */
    public function update(Request $r, $id)
    {
        if ($res = $this->requireRole($r, ['admin','superadmin'])) return $res;

        $row = DB::table('study_materials')->where('id', (int)$id)->whereNull('deleted_at')->first();
        if (!$row) return response()->json(['error' => 'Not found'], 404);

        $v = Validator::make($r->all(), [
            'title'            => 'sometimes|required|string|max:255',
            'description'      => 'nullable|string',
            'view_policy'      => 'nullable|in:inline_only,downloadable',
            'attachments.*'    => 'nullable|file|max:51200',
        ]);

        if ($v->fails()) return response()->json(['errors'=>$v->errors()], 422);

        $update = [];
        if ($r->filled('title')) {
            $update['title'] = $r->title;
            // If title changes, keep slug stable unless asked
            if ($r->boolean('regenerate_slug', false)) {
                $update['slug'] = $this->uniqueSlug($r->title);
            }
        }
        if ($r->exists('description')) $update['description'] = $r->input('description');
        if ($r->filled('view_policy')) $update['view_policy'] = $r->input('view_policy');

        // add more files (append)
        $stored = $this->jsonDecode($row->attachment);
        if ($r->hasFile('attachments')) {
            $files = is_array($r->file('attachments')) ? $r->file('attachments') : [$r->file('attachments')];
            $root  = $this->ensureDir(storage_path('app/batchStudyMaterial/'.(int)$row->batch_id));
            foreach ($files as $file) {
                if (!$file || !$file->isValid()) continue;

                $ext  = strtolower($file->getClientOriginalExtension() ?: 'bin');
                $fid  = Str::lower(Str::random(10));
                $name = $fid.'.'.$ext;
                $file->move($root, $name);

                $absPath = $root.DIRECTORY_SEPARATOR.$name;
                $relPath = 'batchStudyMaterial/'.(int)$row->batch_id.'/'.$name;

                $mime = $file->getClientMimeType() ?: mime_content_type($absPath) ?: 'application/octet-stream';
                $size = @filesize($absPath) ?: 0;
                $sha  = hash_file('sha256', $absPath);
                $url  = $this->appUrl()."/api/study-materials/stream/{$row->uuid}/{$fid}";

                $stored[] = [
                    'id'          => $fid,
                    'disk'        => 'local',
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
        $update['attachment'] = $stored ? json_encode($stored) : null;
        $update['attachment_count'] = count($stored);
        $update['updated_at'] = Carbon::now();

        DB::table('study_materials')->where('id', (int)$id)->update($update);

        return response()->json([
            'message' => 'Study material updated',
            'id'      => (int)$id,
            'attachments' => $stored,
        ]);
    }

    /* =========================================================
     |  Soft delete
     * ========================================================= */
    public function destroy(Request $r, $id)
    {
        if ($res = $this->requireRole($r, ['admin','superadmin'])) return $res;

        $row = DB::table('study_materials')->where('id', (int)$id)->whereNull('deleted_at')->first();
        if (!$row) return response()->json(['error'=>'Not found'], 404);

        DB::table('study_materials')->where('id', (int)$id)->update([
            'deleted_at' => Carbon::now()
        ]);

        return response()->json(['message'=>'Study material moved to bin']);
    }

    /* =========================================================
     |  Show one (by uuid) – useful for a “View” page manifest
     * ========================================================= */
    public function showByUuid(Request $r, string $uuid)
    {
        if ($res = $this->requireRole($r, ['admin','superadmin'])) return $res;

        $row = DB::table('study_materials')->where('uuid', $uuid)->whereNull('deleted_at')->first();
        if (!$row) return response()->json(['error'=>'Not found'], 404);

        $row->attachment = $this->jsonDecode($row->attachment);
        return response()->json($row);
    }

    /* =========================================================
     |  Stream inline (view-only)
     |  - returns file with Content-Disposition:inline
     |  - no symlink, no CLI needed, works on shared hosting
     * ========================================================= */
    public function streamInline(Request $r, string $uuid, string $fileId)
    {
        // Roles: allow admins now; extend later (instructor/student) if needed
        if ($res = $this->requireRole($r, ['admin','superadmin'])) return $res;

        $row = DB::table('study_materials')->where('uuid', $uuid)->whereNull('deleted_at')->first();
        if (!$row) return response()->json(['error'=>'Not found'], 404);

        $attachments = $this->jsonDecode($row->attachment);
        $file = null;
        foreach ($attachments as $a) {
            if (($a['id'] ?? null) === $fileId) { $file = $a; break; }
        }
        if (!$file) return response()->json(['error'=>'File not found'], 404);

        // Enforce policy
        if (($row->view_policy ?? 'inline_only') === 'inline_only') {
            // ok — we’ll force inline below
        }

        $absPath = storage_path('app/'.($file['path'] ?? ''));
        if (!is_file($absPath)) {
            return response()->json(['error'=>'File missing on server'], 410);
        }

        $ext  = $file['ext']  ?? 'bin';
        $mime = $file['mime'] ?? 'application/octet-stream';
        $name = "view.{$ext}"; // browser-visible filename, but disposition is inline

        // Important: inline view, disable download prompt as much as possible
        $headers = [
            'Content-Type'              => $mime,
            'Content-Disposition'       => 'inline; filename="'.$name.'"',
            'X-Content-Type-Options'    => 'nosniff',
            'Cache-Control'             => 'no-store, no-cache, must-revalidate, max-age=0',
            'Pragma'                    => 'no-cache',
            'Expires'                   => '0',
        ];

        // Use Laravel's binary file response with inline disposition
        return response()->download($absPath, $name, $headers, 'inline');
    }
}
