<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class CodingModuleController extends Controller
{
    /** Generate unique slug per topic (optionally ignore a given id) */
    private function uniqueSlug(string $title, int $topicId, ?int $ignoreId = null): string
    {
        $base = Str::slug($title);
        $slug = $base;
        $i = 1;

        while (
            DB::table('coding_modules')
                ->where('topic_id', $topicId)
                ->when($ignoreId, fn($q) => $q->where('id', '!=', $ignoreId))
                ->where('slug', $slug)
                ->exists()
        ) {
            $slug = $base . '-' . $i++;
        }
        return $slug;
    }

    /** Fetch one module by id (optionally include trashed) */
    private function findById(int $id, bool $withTrashed = false)
    {
        return DB::table('coding_modules')
            ->when(!$withTrashed, fn($q) => $q->whereNull('deleted_at'))
            ->where('id', $id)
            ->first();
    }

    /** -----------------------
     *  List (with filters)
     *  ---------------------- */
    public function index(Request $r)
    {
        try {
            $perPage = (int)$r->input('per_page', 20);
            $q = DB::table('coding_modules')->whereNull('deleted_at');

            if ($r->filled('topic_id')) {
                $q->where('topic_id', $r->input('topic_id'));
            }
            if ($r->filled('status')) {
                $q->where('status', $r->input('status'));
            }
            if ($r->boolean('only_trashed')) {
                $q = DB::table('coding_modules')->whereNotNull('deleted_at');
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

            return response()->json([
                'status' => 'success',
                'data'   => $paginator,
            ], 200);
        } catch (\Throwable $e) {
            Log::error('Module index failed', ['err' => $e->getMessage()]);
            return response()->json([
                'status'  => 'error',
                'message' => 'Failed to fetch modules.',
            ], 500);
        }
    }

    /** -----------------------
     *  Create
     *  ---------------------- */
    public function store(Request $r)
    {
        $v = Validator::make($r->all(), [
            'topic_id'    => 'required|integer|exists:topics,id',
            'title'       => 'required|string|min:2|max:200',
            'description' => 'nullable|string',
            'status'      => 'nullable|in:active,inactive,archived',
            'sort_order'  => 'nullable|integer|min:0',
            'extras'      => 'nullable|array',
        ]);

        if ($v->fails()) {
            return response()->json(['status'=>'error','message'=>$v->errors()->first(),'errors'=>$v->errors()], 422);
        }

        try {
            $data = $v->validated();
            $slug = $this->uniqueSlug($data['title'], $data['topic_id']);

            $insert = [
                'topic_id'    => $data['topic_id'],
                'uuid'        => (string) Str::uuid(),
                'title'       => $data['title'],
                'slug'        => $slug,
                'description' => $data['description'] ?? null,
                'status'      => $data['status'] ?? 'active',
                'sort_order'  => $data['sort_order'] ?? 0,
                'extras'      => array_key_exists('extras', $data) ? json_encode($data['extras']) : null,
                'created_at'  => now(),
                'updated_at'  => now(),
            ];

            $id = DB::table('coding_modules')->insertGetId($insert);
            $module = $this->findById($id, true);

            return response()->json([
                'status'  => 'success',
                'message' => 'Module created successfully.',
                'data'    => $module,
            ], 201);
        } catch (\Throwable $e) {
            Log::error('Module store failed', ['err' => $e->getMessage()]);
            return response()->json(['status'=>'error','message'=>'Failed to create module.'], 500);
        }
    }

    /** -----------------------
     *  Show (by id or slug; includes trashed)
     *  ---------------------- */
    public function show($idOrSlug)
    {
        try {
            if (is_numeric($idOrSlug)) {
                $module = DB::table('coding_modules')->where('id', (int)$idOrSlug)->first();
            } else {
                $module = DB::table('coding_modules')->where('slug', $idOrSlug)->first();
            }

            if (!$module) {
                return response()->json(['status'=>'error','message'=>'Module not found.'], 404);
            }

            return response()->json(['status'=>'success','data'=>$module], 200);
        } catch (\Throwable $e) {
            return response()->json(['status'=>'error','message'=>'Failed to fetch module.'], 500);
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
            'extras'       => 'nullable|array',
        ]);

        if ($v->fails()) {
            return response()->json(['status'=>'error','message'=>$v->errors()->first(),'errors'=>$v->errors()], 422);
        }

        try {
            $module = $this->findById((int)$id, true);
            if (!$module) {
                return response()->json(['status'=>'error','message'=>'Module not found.'], 404);
            }

            $data = $v->validated();
            $payload = [];

            if (isset($data['title']) && $data['title'] !== $module->title) {
                $payload['title'] = $data['title'];
                $payload['slug']  = $this->uniqueSlug($data['title'], $module->topic_id, (int)$id);
            }
            if (array_key_exists('description', $data)) $payload['description'] = $data['description'];
            if (array_key_exists('status', $data))      $payload['status']      = $data['status'];
            if (array_key_exists('sort_order', $data))  $payload['sort_order']  = $data['sort_order'];
            if (array_key_exists('extras', $data))      $payload['extras']      = json_encode($data['extras']);

            if (empty($payload)) {
                return response()->json(['status'=>'success','message'=>'No changes.','data'=>$module], 200);
            }

            $payload['updated_at'] = now();
            DB::table('coding_modules')->where('id', (int)$id)->update($payload);

            $fresh = $this->findById((int)$id, true);

            return response()->json([
                'status'  => 'success',
                'message' => 'Module updated successfully.',
                'data'    => $fresh,
            ], 200);

        } catch (\Throwable $e) {
            Log::error('Module update failed', ['err' => $e->getMessage()]);
            return response()->json(['status'=>'error','message'=>'Failed to update module.'], 500);
        }
    }

    /** -----------------------
     *  Delete (soft)
     *  ---------------------- */
    public function destroy($id)
    {
        try {
            $module = $this->findById((int)$id, true);
            if (!$module) {
                return response()->json(['status'=>'error','message'=>'Module not found.'], 404);
            }

            DB::table('coding_modules')->where('id', (int)$id)->update([
                'deleted_at' => now(),
                'updated_at' => now(),
            ]);

            return response()->json(['status'=>'success','message'=>'Module deleted.'], 200);
        } catch (\Throwable $e) {
            Log::error('Module delete failed', ['err' => $e->getMessage()]);
            return response()->json(['status'=>'error','message'=>'Failed to delete module.'], 500);
        }
    }

    /** -----------------------
     *  Restore (from trash)
     *  ---------------------- */
    public function restore($id)
    {
        try {
            $module = DB::table('coding_modules')->where('id', (int)$id)->whereNotNull('deleted_at')->first();
            if (!$module) {
                return response()->json(['status'=>'error','message'=>'Module not found or not trashed.'], 404);
            }

            DB::table('coding_modules')->where('id', (int)$id)->update([
                'deleted_at' => null,
                'updated_at' => now(),
            ]);

            $fresh = $this->findById((int)$id, true);

            return response()->json(['status'=>'success','message'=>'Module restored.','data'=>$fresh], 200);
        } catch (\Throwable $e) {
            return response()->json(['status'=>'error','message'=>'Failed to restore module.'], 500);
        }
    }

    /** -----------------------
     *  Toggle status (active/inactive)
     *  ---------------------- */
    public function toggleStatus($id)
    {
        try {
            $module = $this->findById((int)$id, true);
            if (!$module) {
                return response()->json(['status'=>'error','message'=>'Module not found.'], 404);
            }

            $new = ($module->status === 'active') ? 'inactive' : 'active';
            DB::table('coding_modules')->where('id', (int)$id)->update([
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
            'order.*'            => 'integer|exists:coding_modules,id',
            'items'              => 'sometimes|array|min:1',
            'items.*.id'         => 'required|integer|exists:coding_modules,id',
            'items.*.sort_order' => 'required|integer|min:0',
        ]);

        if ($v->fails()) {
            return response()->json(['status'=>'error','message'=>$v->errors()->first(),'errors'=>$v->errors()], 422);
        }

        try {
            $payload = $v->validated();

            DB::beginTransaction();
            if (!empty($payload['order'])) {
                foreach ($payload['order'] as $idx => $moduleId) {
                    DB::table('coding_modules')->where('id', (int)$moduleId)->update([
                        'sort_order' => (int)$idx,
                        'updated_at' => now(),
                    ]);
                }
            } elseif (!empty($payload['items'])) {
                foreach ($payload['items'] as $it) {
                    DB::table('coding_modules')->where('id', (int)$it['id'])->update([
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
            Log::error('Module reorder failed', ['err' => $e->getMessage()]);
            return response()->json(['status'=>'error','message'=>'Failed to update sort order.'], 500);
        }
    }
}
