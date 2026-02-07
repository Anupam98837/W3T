<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;

class TopicController extends Controller
{
    /** Base public path for topic images */
    private string $imgDirPublic = 'assets/media/images/topics';

    /** Ensure directory exists (public/…) */
    private function ensureImgDir(): void
    {
        $full = public_path($this->imgDirPublic);
        if (!File::exists($full)) {
            File::makeDirectory($full, 0755, true);
        }
    }

    /** Save uploaded image; return relative public path like assets/media/images/topics/abc.jpg */
    private function saveImage($file, ?string $hintSlug = null): string
    {
        $this->ensureImgDir();
        $ext      = strtolower($file->getClientOriginalExtension() ?: 'jpg');
        $basename = ($hintSlug ?: 'topic') . '-' . now()->format('YmdHis') . '-' . Str::random(6) . '.' . $ext;
        $file->move(public_path($this->imgDirPublic), $basename);
        return $this->imgDirPublic . '/' . $basename;
    }

    /** Delete old image if on disk (silently ignore failures) */
    private function deleteImageIfExists(?string $path): void
    {
        if (!$path) return;
        $full = public_path($path);
        if (File::exists($full)) {
            @File::delete($full);
        }
    }

    /** Generate unique slug from title (optionally ignore a given id) */
    private function uniqueSlug(string $title, ?int $ignoreId = null): string
    {
        $base = Str::slug($title);
        $slug = $base;
        $i = 1;

        while (
            DB::table('topics')
                ->when($ignoreId, fn($q) => $q->where('id', '!=', $ignoreId))
                ->where('slug', $slug)
                ->exists()
        ) {
            $slug = $base . '-' . $i++;
        }
        return $slug;
    }

    /** Fetch one topic by id (optionally include trashed) */
    private function findById(int $id, bool $withTrashed = false)
    {
        return DB::table('topics')
            ->when(!$withTrashed, fn($q) => $q->whereNull('deleted_at'))
            ->where('id', $id)
            ->first();
    }

    /** Decode extras json to array when present */
    private function decodeExtrasOnObject(?object $row): ?object
    {
        if ($row && isset($row->extras) && is_string($row->extras)) {
            $decoded = json_decode($row->extras, true);
            $row->extras = $decoded === null ? $row->extras : $decoded;
        }
        return $row;
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
                'module'             => 'Topic',
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
            Log::error('[Topic] user_data_activity_log insert failed', ['error' => $e->getMessage()]);
        }
    }

    /** -----------------------
     *  List (with filters)
     *  ---------------------- */
    public function index(Request $r)
    {
        try {
            $perPage = (int)$r->input('per_page', 20);
            $q = DB::table('topics')->whereNull('deleted_at');

            if ($r->filled('status')) {
                $q->where('status', $r->input('status'));
            }
            if ($r->boolean('only_trashed')) {
                // If explicitly only trashed, override default filter
                $q = DB::table('topics')->whereNotNull('deleted_at');
            }
            if ($r->filled('q')) {
                $term = $r->input('q');
                $q->where(function ($qq) use ($term) {
                    $qq->where('title', 'like', "%{$term}%")
                       ->orWhere('slug', 'like', "%{$term}%")
                       ->orWhere('description', 'like', "%{$term}%");
                });
            }

            $q->orderBy('sort_order')->orderByDesc('created_at');

            $paginator = $q->paginate($perPage);

            // (Optional) decode extras on each item
            $paginator->getCollection()->transform(function ($row) {
                return $this->decodeExtrasOnObject($row);
            });

            return response()->json([
                'status' => 'success',
                'data'   => $paginator,
            ], 200);
        } catch (\Throwable $e) {
            Log::error('Topic index failed', ['err' => $e->getMessage()]);
            return response()->json([
                'status'  => 'error',
                'message' => 'Failed to fetch topics.',
            ], 500);
        }
    }

    /** -----------------------
     *  Create
     *  ---------------------- */
    public function store(Request $r)
    {
        $v = Validator::make($r->all(), [
            'title'       => 'required|string|min:2|max:200',
            'description' => 'nullable|string',
            'status'      => 'nullable|in:active,inactive,archived',
            'sort_order'  => 'nullable|integer|min:0',
            'image'       => 'nullable|file|mimes:jpg,jpeg,png,webp,gif|max:3072',
            'extras'      => 'nullable|array',
        ]);

        if ($v->fails()) {
            return response()->json(['status'=>'error','message'=>$v->errors()->first(),'errors'=>$v->errors()], 422);
        }

        try {
            $data = $v->validated();
            $slug = $this->uniqueSlug($data['title']);

            $imagePath = null;
            if ($r->hasFile('image')) {
                $imagePath = $this->saveImage($r->file('image'), $slug);
            }

            $insert = [
                'uuid'        => (string) Str::uuid(),
                'title'       => $data['title'],
                'slug'        => $slug,
                'description' => $data['description'] ?? null,
                'image_path'  => $imagePath,
                'status'      => $data['status'] ?? 'active',
                'sort_order'  => $data['sort_order'] ?? 0,
                'extras'      => array_key_exists('extras', $data) ? json_encode($data['extras']) : null,
                'created_at'  => now(),
                'updated_at'  => now(),
            ];

            $id = DB::table('topics')->insertGetId($insert);
            $topic = $this->decodeExtrasOnObject($this->findById($id, true));

            // ✅ ADDED ACTIVITY LOG
            $this->logActivity(
                $r,
                'store',
                'Created topic: "'.($topic->title ?? $data['title']).'"',
                'topics',
                (int)$id,
                array_keys($insert),
                null,
                $topic ? (array)$topic : null
            );

            return response()->json([
                'status'  => 'success',
                'message' => 'Topic created successfully.',
                'data'    => $topic,
            ], 201);
        } catch (\Throwable $e) {
            Log::error('Topic store failed', ['err' => $e->getMessage()]);
            return response()->json(['status'=>'error','message'=>'Failed to create topic.'], 500);
        }
    }

    /** -----------------------
     *  Show (by id or slug; includes trashed)
     *  ---------------------- */
    public function show($idOrSlug)
    {
        try {
            if (is_numeric($idOrSlug)) {
                $topic = DB::table('topics')->where('id', (int)$idOrSlug)->first();
            } else {
                $topic = DB::table('topics')->where('slug', $idOrSlug)->first();
            }

            if (!$topic) {
                return response()->json(['status'=>'error','message'=>'Topic not found.'], 404);
            }

            $topic = $this->decodeExtrasOnObject($topic);
            return response()->json(['status'=>'success','data'=>$topic], 200);
        } catch (\Throwable $e) {
            return response()->json(['status'=>'error','message'=>'Failed to fetch topic.'], 500);
        }
    }

    /** -----------------------
     *  Update
     *  ---------------------- */
    public function update(Request $r, $id)
    {
        $v = Validator::make($r->all(), [
            'title'        => 'nullable|string|min:2|max:200',
            'description'  => 'nullable|string',
            'status'       => 'nullable|in:active,inactive,archived',
            'sort_order'   => 'nullable|integer|min:0',
            'image'        => 'nullable|file|mimes:jpg,jpeg,png,webp,gif|max:3072',
            'delete_image' => 'nullable|boolean',
            'extras'       => 'nullable|array',
        ]);

        if ($v->fails()) {
            return response()->json(['status'=>'error','message'=>$v->errors()->first(),'errors'=>$v->errors()], 422);
        }

        try {
            $topic = $this->findById((int)$id, true);
            if (!$topic) {
                return response()->json(['status'=>'error','message'=>'Topic not found.'], 404);
            }

            $before = $this->decodeExtrasOnObject(clone $topic);

            $data = $v->validated();
            $payload = [];

            if (isset($data['title']) && $data['title'] !== $topic->title) {
                $payload['title'] = $data['title'];
                $payload['slug']  = $this->uniqueSlug($data['title'], (int)$id);
            }
            if (array_key_exists('description', $data)) $payload['description'] = $data['description'];
            if (array_key_exists('status', $data))      $payload['status']      = $data['status'];
            if (array_key_exists('sort_order', $data))  $payload['sort_order']  = $data['sort_order'];
            if (array_key_exists('extras', $data))      $payload['extras']      = json_encode($data['extras']);

            // Image handling
            $deleteImage = (bool)($data['delete_image'] ?? false);
            if ($deleteImage && $topic->image_path) {
                $this->deleteImageIfExists($topic->image_path);
                $payload['image_path'] = null;
            }
            if ($r->hasFile('image')) {
                // replace old
                if (!empty($topic->image_path)) {
                    $this->deleteImageIfExists($topic->image_path);
                }
                $hint = $payload['slug'] ?? $topic->slug;
                $payload['image_path'] = $this->saveImage($r->file('image'), $hint);
            }

            if (empty($payload)) {
                return response()->json(['status'=>'success','message'=>'No changes.','data'=>$this->decodeExtrasOnObject($topic)], 200);
            }

            $payload['updated_at'] = now();
            DB::table('topics')->where('id', (int)$id)->update($payload);

            $fresh = $this->decodeExtrasOnObject($this->findById((int)$id, true));

            // ✅ ADDED ACTIVITY LOG
            $this->logActivity(
                $r,
                'update',
                'Updated topic: "'.($fresh->title ?? $before->title ?? 'N/A').'"',
                'topics',
                (int)$id,
                array_keys($payload),
                $before ? (array)$before : null,
                $fresh ? (array)$fresh : null
            );

            return response()->json([
                'status'  => 'success',
                'message' => 'Topic updated successfully.',
                'data'    => $fresh,
            ], 200);

        } catch (\Throwable $e) {
            Log::error('Topic update failed', ['err' => $e->getMessage()]);
            return response()->json(['status'=>'error','message'=>'Failed to update topic.'], 500);
        }
    }

    /** -----------------------
     *  Delete (soft)
     *  ---------------------- */
    public function destroy($id)
    {
        try {
            $topic = $this->findById((int)$id, true);
            if (!$topic) {
                return response()->json(['status'=>'error','message'=>'Topic not found.'], 404);
            }

            $before = $this->decodeExtrasOnObject(clone $topic);

            DB::table('topics')->where('id', (int)$id)->update([
                'deleted_at' => now(),
                'updated_at' => now(),
            ]);

            $after = $this->decodeExtrasOnObject($this->findById((int)$id, true));

            // ✅ ADDED ACTIVITY LOG
            $this->logActivity(
                request(), // no Request param in signature; keep minimal impact
                'destroy',
                'Deleted topic: "'.($before->title ?? 'N/A').'"',
                'topics',
                (int)$id,
                ['deleted_at'],
                $before ? (array)$before : null,
                $after ? (array)$after : null
            );

            return response()->json(['status'=>'success','message'=>'Topic deleted.'], 200);
        } catch (\Throwable $e) {
            Log::error('Topic delete failed', ['err' => $e->getMessage()]);
            return response()->json(['status'=>'error','message'=>'Failed to delete topic.'], 500);
        }
    }

    /** -----------------------
     *  Restore (from trash)
     *  ---------------------- */
    public function restore($id)
    {
        try {
            $topic = DB::table('topics')->where('id', (int)$id)->whereNotNull('deleted_at')->first();
            if (!$topic) {
                return response()->json(['status'=>'error','message'=>'Topic not found or not trashed.'], 404);
            }

            DB::table('topics')->where('id', (int)$id)->update([
                'deleted_at' => null,
                'updated_at' => now(),
            ]);

            $fresh = $this->decodeExtrasOnObject($this->findById((int)$id, true));

            return response()->json(['status'=>'success','message'=>'Topic restored.','data'=>$fresh], 200);
        } catch (\Throwable $e) {
            return response()->json(['status'=>'error','message'=>'Failed to restore topic.'], 500);
        }
    }

    /** -----------------------
     *  Toggle status (active/inactive)
     *  ---------------------- */
    public function toggleStatus($id)
    {
        try {
            $topic = $this->findById((int)$id, true);
            if (!$topic) {
                return response()->json(['status'=>'error','message'=>'Topic not found.'], 404);
            }

            $new = ($topic->status === 'active') ? 'inactive' : 'active';
            DB::table('topics')->where('id', (int)$id)->update([
                'status'     => $new,
                'updated_at' => now(),
            ]);

            return response()->json([
                'status'     => 'success',
                'message'    => 'Status updated.',
                'new_status' => $new,
            ], 200);
        } catch (\Throwable $e) {
            return response()->json(['status'=>'error','message'=>'Failed to toggle status.'], 500);
        }
    }

    /** -----------------------
     *  Reorder (by sort_order)
     *  ---------------------- */
    public function reorder(Request $r)
    {
        $v = Validator::make($r->all(), [
            'order'              => 'sometimes|array|min:1',
            'order.*'            => 'integer|exists:topics,id',
            'items'              => 'sometimes|array|min:1',
            'items.*.id'         => 'required|integer|exists:topics,id',
            'items.*.sort_order' => 'required|integer|min:0',
        ]);

        if ($v->fails()) {
            return response()->json(['status'=>'error','message'=>$v->errors()->first(),'errors'=>$v->errors()], 422);
        }

        try {
            $payload = $v->validated();

            DB::beginTransaction();
            if (!empty($payload['order'])) {
                foreach ($payload['order'] as $idx => $topicId) {
                    DB::table('topics')->where('id', (int)$topicId)->update([
                        'sort_order' => (int)$idx,
                        'updated_at' => now(),
                    ]);
                }
            } elseif (!empty($payload['items'])) {
                foreach ($payload['items'] as $it) {
                    DB::table('topics')->where('id', (int)$it['id'])->update([
                        'sort_order' => (int)$it['sort_order'],
                        'updated_at' => now(),
                    ]);
                }
            } else {
                DB::rollBack();
                return response()->json(['status'=>'error','message'=>'No reorder data provided.'], 422);
            }
            DB::commit();

            return response()->json(['status'=>'success','message'=>'Sort order updated.'], 200);
        } catch (\Throwable $e) {
            DB::rollBack();
            Log::error('Topic reorder failed', ['err' => $e->getMessage()]);
            return response()->json(['status'=>'error','message'=>'Failed to update sort order.'], 500);
        }
    }

    /** -----------------------
     *  Replace image only
     *  ---------------------- */
    public function changeImage(Request $r, $id)
    {
        $v = Validator::make($r->all(), [
            'image' => 'required|file|mimes:jpg,jpeg,png,webp,gif|max:3072',
        ]);

        if ($v->fails()) {
            return response()->json(['status'=>'error','message'=>$v->errors()->first(),'errors'=>$v->errors()], 422);
        }

        try {
            $topic = $this->findById((int)$id, true);
            if (!$topic) {
                return response()->json(['status'=>'error','message'=>'Topic not found.'], 404);
            }

            if (!empty($topic->image_path)) {
                $this->deleteImageIfExists($topic->image_path);
            }

            $hint = $topic->slug;
            $newPath = $this->saveImage($r->file('image'), $hint);

            DB::table('topics')->where('id', (int)$id)->update([
                'image_path' => $newPath,
                'updated_at' => now(),
            ]);

            return response()->json([
                'status'  => 'success',
                'message' => 'Image updated.',
                'data'    => ['image_path' => $newPath],
            ], 200);
        } catch (\Throwable $e) {
            Log::error('Topic changeImage failed', ['err' => $e->getMessage()]);
            return response()->json(['status'=>'error','message'=>'Failed to update image.'], 500);
        }
    }

    /** -----------------------
     *  Remove image only
     *  ---------------------- */
    public function removeImage($id)
    {
        try {
            $topic = $this->findById((int)$id, true);
            if (!$topic) {
                return response()->json(['status'=>'error','message'=>'Topic not found.'], 404);
            }

            if (!empty($topic->image_path)) {
                $this->deleteImageIfExists($topic->image_path);
            }

            DB::table('topics')->where('id', (int)$id)->update([
                'image_path' => null,
                'updated_at' => now(),
            ]);

            return response()->json(['status'=>'success','message'=>'Image removed.'], 200);
        } catch (\Throwable $e) {
            return response()->json(['status'=>'error','message'=>'Failed to remove image.'], 500);
        }
    }
}
