<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

class AboutUsController extends Controller
{
    private const MEDIA_SUBDIR = 'assets/media/about-us';

    /**
     * Fetch single About Us entry
     */
    private function getAboutUsEntry(): ?object
    {
        return DB::table('about_us')->orderBy('id', 'asc')->first();
    }

    /**
     * Check if record exists
     */
    private function recordExists(): bool
    {
        return DB::table('about_us')->exists();
    }

    /* =========================================================
     | Activity Log (added)
     * ========================================================= */
    private function logActivity(
        Request $request,
        string $activity, // store | update | destroy
        string $note,
        string $tableName,
        ?int $recordId = null,
        ?array $changedFields = null,
        ?array $oldValues = null,
        ?array $newValues = null
    ): void {
        $actorRole = $request->attributes->get('auth_role');
        $actorId   = (int) ($request->attributes->get('auth_tokenable_id') ?? 0);

        try {
            DB::table('user_data_activity_log')->insert([
                'performed_by'       => $actorId ?: 0,
                'performed_by_role'  => $actorRole ?: null,
                'ip'                 => $request->ip(),
                'user_agent'         => (string) $request->userAgent(),
                'activity'           => $activity,
                'module'             => 'AboutUs',
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
            Log::error('[AboutUs] user_data_activity_log insert failed', ['error' => $e->getMessage()]);
        }
    }

    /**
     * Handle image upload
     */
    private function uploadImage(?object $existing, Request $request): ?string
    {
        if (!$request->hasFile('image')) {
            return $existing->image ?? null;
        }

        // Ensure directory exists
        $dest = public_path(self::MEDIA_SUBDIR);
        if (!is_dir($dest)) {
            mkdir($dest, 0755, true);
        }

        // Delete old image (best-effort)
        if ($existing && $existing->image) {
            $oldPath = public_path($existing->image);
            if (is_file($oldPath)) {
                @unlink($oldPath);
            }
        }

        $file = $request->file('image');
        $ext  = strtolower($file->getClientOriginalExtension() ?: 'webp');
        $name = 'about_' . time() . '_' . uniqid() . '.' . $ext;

        $file->move($dest, $name);

        // Store RELATIVE public path
        return self::MEDIA_SUBDIR . '/' . $name;
    }

    /**
     * GET /api/about-us
     */
    public function index()
    {
        $about = $this->getAboutUsEntry();

        if (!$about) {
            return response()->json([
                'success' => false,
                'message' => 'About Us data not found'
            ], 404);
        }

        return response()->json([
            'success' => true,
            'about' => [
                'id'         => (int) $about->id,
                'title'      => $about->title,
                'content'    => $about->content,
                'image'      => $about->image ? url($about->image) : null,
                'created_at' => Carbon::parse($about->created_at)->toDateTimeString(),
                'updated_at' => Carbon::parse($about->updated_at)->toDateTimeString(),
            ]
        ]);
    }

    /**
     * POST /api/about-us
     * Create or Update
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'title'   => ['required', 'string', 'max:255'],
            'content' => ['required', 'string'],
            'image'   => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:2048'],
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors'  => $validator->errors()
            ], 422);
        }

        $now = Carbon::now();
        $existing = $this->getAboutUsEntry();
        $before = $existing ? (array)$existing : null;

        $imagePath = $this->uploadImage($existing, $request);

        if ($existing) {
            // UPDATE
            DB::table('about_us')->where('id', $existing->id)->update([
                'title'      => $request->title,
                'content'    => $request->content,
                'image'      => $imagePath,
                'updated_at' => $now,
            ]);

            $about = $this->getAboutUsEntry();

            // ✅ ACTIVITY LOG (POST -> update)
            $this->logActivity(
                $request,
                'update',
                'Updated About Us (via POST)',
                'about_us',
                (int)$existing->id,
                ['title', 'content', 'image'],
                $before,
                $about ? (array)$about : null
            );

            return response()->json([
                'success' => true,
                'message' => 'About Us updated successfully',
                'about'   => $about
            ], 200);
        }

        // CREATE
        DB::table('about_us')->insert([
            'title'      => $request->title,
            'content'    => $request->content,
            'image'      => $imagePath,
            'created_at' => $now,
            'updated_at' => $now,
        ]);

        $created = $this->getAboutUsEntry();

        // ✅ ACTIVITY LOG (POST -> store)
        $this->logActivity(
            $request,
            'store',
            'Created About Us',
            'about_us',
            $created ? (int)$created->id : null,
            ['title', 'content', 'image'],
            null,
            $created ? (array)$created : null
        );

        return response()->json([
            'success' => true,
            'message' => 'About Us created successfully',
            'about'   => $created
        ], 201);
    }

    /**
     * GET /api/about-us/check
     */
    public function check()
    {
        $about = $this->getAboutUsEntry();

        return response()->json([
            'success' => true,
            'exists'  => (bool) $about,
            'mode'    => $about ? 'edit' : 'create',
            'about'   => $about ? [
                'id'      => $about->id,
                'title'   => $about->title,
                'content' => $about->content,
                'image'   => $about->image ? url($about->image) : null,
            ] : null
        ]);
    }

    /**
     * DELETE /api/about-us
     */
    public function destroy()
    {
        $about = $this->getAboutUsEntry();

        if (!$about) {
            return response()->json([
                'success' => false,
                'message' => 'No About Us record found'
            ], 404);
        }

        $before = (array)$about;

        if ($about->image && file_exists(public_path($about->image))) {
            unlink(public_path($about->image));
        }

        DB::table('about_us')->where('id', $about->id)->delete();

        // ✅ ACTIVITY LOG (DELETE -> destroy)
        $this->logActivity(
            request(),
            'destroy',
            'Deleted About Us',
            'about_us',
            (int)$about->id,
            null,
            $before,
            null
        );

        return response()->json([
            'success' => true,
            'message' => 'About Us deleted successfully'
        ]);
    }

    /**
     * PUT /api/about-us
     * Separate update endpoint (optional)
     */
    public function update(Request $request)
    {
        $about = $this->getAboutUsEntry();

        if (!$about) {
            return response()->json([
                'success' => false,
                'message' => 'About Us not found'
            ], 404);
        }

        $validator = Validator::make($request->all(), [
            'title'   => ['required', 'string', 'max:255'],
            'content' => ['required', 'string'],
            'image'   => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:2048'],
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors'  => $validator->errors()
            ], 422);
        }

        $before = (array)$about;

        $imagePath = $this->uploadImage($about, $request);

        DB::table('about_us')->where('id', $about->id)->update([
            'title'      => $request->title,
            'content'    => $request->content,
            'image'      => $imagePath,
            'updated_at' => now(),
        ]);

        $fresh = $this->getAboutUsEntry();

        // ✅ ACTIVITY LOG (PUT/PATCH -> update)
        $this->logActivity(
            $request,
            'update',
            'Updated About Us (via PUT/PATCH)',
            'about_us',
            (int)$about->id,
            ['title', 'content', 'image'],
            $before,
            $fresh ? (array)$fresh : null
        );

        return response()->json([
            'success' => true,
            'message' => 'About Us updated successfully',
            'about'   => $fresh
        ]);
    }
}
