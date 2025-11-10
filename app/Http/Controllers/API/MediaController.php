<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;
use Carbon\Carbon;

class MediaController extends Controller
{
    /* =========================================================
     |  Helpers
     * ========================================================= */

    /** Current actor from CheckRole middleware */
    private function actor(Request $r): array
    {
        return [
            'id'   => (int) ($r->attributes->get('auth_tokenable_id') ?? 0),
            'role' => (string) ($r->attributes->get('auth_role') ?? ''),
        ];
    }

    /** Absolute APP URL (no trailing slash) */
    private function appUrl(): string
    {
        return rtrim((string) config('app.url'), '/');
    }

    /** Ensure public/AllMedia exists; return path */
    private function mediaDir(): string
    {
        $dir = public_path('AllMedia');
        if (!File::exists($dir)) {
            File::makeDirectory($dir, 0755, true, true);
        }
        return $dir;
    }

    /** Guess category from mime/ext */
    private function categorize(?string $mime, ?string $ext): string
    {
        $mime = strtolower((string) $mime);
        $ext  = strtolower((string) $ext);

        if (str_starts_with($mime, 'image/')) return 'image';
        if (str_starts_with($mime, 'video/')) return 'video';
        if (str_starts_with($mime, 'audio/')) return 'audio';

        $doc = ['pdf','doc','docx','xls','xlsx','ppt','pptx','txt','rtf','csv','md','json','xml'];
        $arc = ['zip','rar','7z','tar','gz','bz2'];
        if (in_array($ext, $doc, true)) return 'document';
        if (in_array($ext, $arc, true)) return 'archive';
        return 'other';
    }

    /** Basic activity logger (optional—but keeps parity with the rest of W3T) */
    private function logActivity(int $userId, string $action, array $payload = []): void
    {
        try {
            DB::table('user_data_activity_log')->insert([
                'user_id'    => $userId,
                'action'     => $action,
                'payload'    => json_encode($payload, JSON_UNESCAPED_SLASHES),
                'ip_address' => request()->ip(),
                'created_at' => Carbon::now(),
            ]);
        } catch (\Throwable $e) {
            Log::warning('media.activity.log.fail', ['e' => $e->getMessage()]);
        }
    }

    /** Allow id or uuid lookup */
    private function findMediaRow(string $idOrUuid, bool $withTrashed = false): ?object
    {
        $q = DB::table('media');
        if ($withTrashed) $q->whereRaw('1=1'); else $q->whereNull('deleted_at');

        if (ctype_digit($idOrUuid)) {
            $q->where('id', (int)$idOrUuid);
        } else {
            $q->where('uuid', $idOrUuid);
        }
        return $q->first();
    }

    /* =========================================================
     |  GET /api/media  (list)
     * ========================================================= */
    public function index(Request $request)
    {
        $v = Validator::make($request->all(), [
            'q'               => 'nullable|string|max:255',
            'category'        => 'nullable|string|in:image,video,audio,document,archive,other',
            'status'          => 'nullable|string|in:active,archived',
            'usage_tag'       => 'nullable|string|max:50',
            'sort'            => 'nullable|string|max:64',    // e.g., -created_at, title
            'per_page'        => 'nullable|integer|min:1|max:200',
            'page'            => 'nullable|integer|min:1',
            'include_deleted' => 'nullable|boolean',
            'only_deleted'    => 'nullable|boolean',
        ]);

        if ($v->fails()) {
            return response()->json(['success' => false, 'errors' => $v->errors()], 422);
        }

        $per  = (int) $request->input('per_page', 20);
        $page = (int) $request->input('page', 1);
        $q    = (string) $request->input('q', '');
        $cat  = $request->input('category');
        $stat = $request->input('status');
        $tag  = $request->input('usage_tag');
        $sort = (string) $request->input('sort', '-created_at');

        $includeDeleted = filter_var($request->input('include_deleted', false), FILTER_VALIDATE_BOOLEAN);
        $onlyDeleted    = filter_var($request->input('only_deleted', false), FILTER_VALIDATE_BOOLEAN);

        $builder = DB::table('media as m')
            ->leftJoin('users as u', 'u.id', '=', 'm.created_by')
            ->select(
                'm.*',
                DB::raw("COALESCE(u.name, CONCAT('User#', m.created_by)) as created_by_name")
            );

        if ($onlyDeleted) {
            $builder->whereNotNull('m.deleted_at');
        } elseif (!$includeDeleted) {
            $builder->whereNull('m.deleted_at');
        }

        if ($cat)  $builder->where('m.category', $cat);
        if ($stat) $builder->where('m.status', $stat);
        if ($tag)  $builder->where('m.usage_tag', $tag);

        if ($q !== '') {
            // Try FULLTEXT, fallback to LIKE
            try {
                $builder->whereRaw('MATCH(m.title, m.description, m.alt_text) AGAINST (? IN NATURAL LANGUAGE MODE)', [$q]);
            } catch (\Throwable $e) {
                $like = '%'.$q.'%';
                $builder->where(function($w) use ($like) {
                    $w->where('m.title', 'like', $like)
                      ->orWhere('m.description', 'like', $like)
                      ->orWhere('m.alt_text', 'like', $like);
                });
            }
        }

        // Sorting
        $dir = 'asc'; $col = 'm.created_at';
        if ($sort) {
            $dir = str_starts_with($sort, '-') ? 'desc' : 'asc';
            $col = ltrim($sort, '+-');
            // allowlist
            $allowed = ['id','created_at','title','category','size_bytes','status'];
            $col = in_array($col, $allowed, true) ? "m.$col" : 'm.created_at';
        }
        $builder->orderBy($col, $dir)->orderBy('m.id', 'desc');

        // Pagination
        $total = (clone $builder)->count();
        $items = $builder->forPage($page, $per)->get();

        return response()->json([
            'success'    => true,
            'data'       => $items,
            'pagination' => [
                'total'        => $total,
                'per_page'     => $per,
                'current_page' => $page,
                'last_page'    => (int) ceil($total / max(1, $per)),
            ],
            'filters_echo' => [
                'q' => $q, 'category' => $cat, 'status' => $stat, 'usage_tag' => $tag,
                'include_deleted' => $includeDeleted, 'only_deleted' => $onlyDeleted,
            ]
        ]);
    }

    /* =========================================================
     |  POST /api/media  (upload & create)
     * ========================================================= */
    public function store(Request $request)
    {
        $actor = $this->actor($request);

        $v = Validator::make($request->all(), [
            // Accept very broadly (central library). Size limit can be tuned.
            'file'        => 'required|file|max:102400', // 100MB
            'title'       => 'nullable|string|max:255',
            'description' => 'nullable|string',
            'alt_text'    => 'nullable|string|max:255',
            'usage_tag'   => 'nullable|string|max:50',
            'status'      => 'nullable|string|in:active,archived',
            'metadata'    => 'nullable|array',
        ]);

        if ($v->fails()) {
            return response()->json(['success' => false, 'errors' => $v->errors()], 422);
        }

        $file = $request->file('file');
        if (!$file || !$file->isValid()) {
            return response()->json(['success' => false, 'error' => 'Invalid file upload'], 422);
        }

        $uuid   = (string) Str::uuid();
        $ext    = strtolower($file->getClientOriginalExtension() ?: $file->extension() ?: '');
        $ext    = preg_replace('/[^a-z0-9]+/i', '', $ext) ?? '';
        $fname  = $uuid . ($ext ? ('.' . $ext) : '');
        $dir    = $this->mediaDir();
        $fpath  = $dir . DIRECTORY_SEPARATOR . $fname;

        // Move file to public/AllMedia
        $file->move($dir, $fname);

        $mime   = File::mimeType($fpath) ?: $file->getClientMimeType();
        $size   = (int) filesize($fpath);
        $cat    = $this->categorize($mime, $ext);

        // Dimensions (images)
        $width = null; $height = null; $duration = null;
        if ($cat === 'image') {
            try {
                $dim = @getimagesize($fpath);
                if (is_array($dim)) { $width = (int) $dim[0]; $height = (int) $dim[1]; }
            } catch (\Throwable $e) { /* ignore */ }
        }

        $absUrl = $this->appUrl() . '/AllMedia/' . $fname;

        // Insert
        $row = [
            'uuid'             => $uuid,
            'title'            => $request->input('title'),
            'description'      => $request->input('description'),
            'alt_text'         => $request->input('alt_text'),
            'category'         => $cat,
            'mime_type'        => $mime,
            'ext'              => $ext ?: null,
            'size_bytes'       => $size,
            'width'            => $width,
            'height'           => $height,
            'duration_seconds' => $duration, // null unless you later add ffprobe
            'url'              => $absUrl,
            'usage_tag'        => $request->input('usage_tag'),
            'metadata'         => $request->filled('metadata') ? json_encode($request->input('metadata'), JSON_UNESCAPED_SLASHES) : null,
            'status'           => $request->input('status', 'active'),
            'created_by'       => $actor['id'] ?: null,
            'updated_by'       => $actor['id'] ?: null,
            'created_at'       => Carbon::now(),
            'updated_at'       => Carbon::now(),
        ];

        try {
            $id = DB::table('media')->insertGetId($row);
            $created = DB::table('media')->where('id', $id)->first();
            $this->logActivity($actor['id'], 'media.create', ['id' => $id, 'uuid' => $uuid, 'url' => $absUrl]);
            return response()->json(['success' => true, 'data' => $created], 201);
        } catch (\Throwable $e) {
            // Cleanup file on DB failure
            try { if (File::exists($fpath)) File::delete($fpath); } catch (\Throwable $e2) {}
            Log::error('media.store.db_fail', ['e' => $e->getMessage()]);
            return response()->json(['success' => false, 'error' => 'Failed to save media'], 500);
        }
    }

    /* =========================================================
     |  DELETE /api/media/{idOrUuid}
     |     ?hard=true   → force delete + remove physical file
     * ========================================================= */
    public function destroy(Request $request, string $idOrUuid)
    {
        $actor = $this->actor($request);
        $hard  = filter_var($request->query('hard', false), FILTER_VALIDATE_BOOLEAN);

        $row = $this->findMediaRow($idOrUuid, true);
        if (!$row) {
            return response()->json(['success' => false, 'error' => 'Not found'], 404);
        }

        // Physical file path (from URL)
        $fpath = null;
        try {
            $path = parse_url((string)$row->url, PHP_URL_PATH) ?? '';
            if ($path !== '') {
                $fpath = public_path(ltrim($path, '/'));
            }
        } catch (\Throwable $e) {
            $fpath = null;
        }

        if ($hard) {
            // Hard delete: remove DB row and file
            try {
                DB::table('media')->where('id', $row->id)->delete(); // soft or hard? We want hard:
                DB::table('media')->where('id', $row->id)->delete(); // ensure removed if soft
                DB::table('media')->where('id', $row->id)->forceDelete(); // in case of softDeletes trait behavior
            } catch (\Throwable $e) {
                // On pure query builder tables, forceDelete() is not available—fallback:
                DB::table('media')->where('id', $row->id)->delete();
                DB::table('media')->where('id', $row->id)->whereNotNull('deleted_at')->delete();
            }

            // Remove file
            try { if ($fpath && File::exists($fpath)) File::delete($fpath); } catch (\Throwable $e) {}

            $this->logActivity($actor['id'], 'media.delete.force', ['id' => $row->id, 'uuid' => $row->uuid, 'url' => $row->url]);
            return response()->json(['success' => true, 'deleted' => 'hard', 'id' => $row->id]);
        }

        // Soft delete
        DB::table('media')->where('id', $row->id)->update([
            'deleted_at' => Carbon::now(),
            'updated_by' => $actor['id'] ?: null,
            'updated_at' => Carbon::now(),
        ]);
        $this->logActivity($actor['id'], 'media.delete.soft', ['id' => $row->id, 'uuid' => $row->uuid]);
        return response()->json(['success' => true, 'deleted' => 'soft', 'id' => $row->id]);
    }
}
