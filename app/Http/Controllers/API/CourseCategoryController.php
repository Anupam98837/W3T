<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Throwable;

class CourseCategoryController extends Controller
{
    /**
     * Small logging helpers (do NOT change behavior, only log context).
     */
    private function logInfo($msg, array $ctx = []): void
    {
        Log::info('[LandingPage] ' . $msg, array_merge([
            'user_id' => Auth::id(),
            'ip'      => request()->ip(),
        ], $ctx));
    }

    private function logError(\Throwable $e, $msg, array $ctx = []): void
    {
        Log::error('[LandingPage] ERROR: ' . $msg, array_merge([
            'user_id' => Auth::id(),
            'ip'      => request()->ip(),
            'error'   => $e->getMessage(),
            'line'    => $e->getLine(),
            'file'    => $e->getFile(),
        ], $ctx));
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
        $actorId   = (int) ($request->attributes->get('auth_tokenable_id') ?? (Auth::id() ?? 0));

        try {
            DB::table('user_data_activity_log')->insert([
                'performed_by'       => $actorId ?: 0,
                'performed_by_role'  => $actorRole ?: null,
                'ip'                 => $request->ip(),
                'user_agent'         => (string) $request->userAgent(),
                'activity'           => $activity,
                'module'             => 'CourseCategory',
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
            Log::error('[LandingPage] user_data_activity_log insert failed', ['error' => $e->getMessage()]);
        }
    }

    /**
     * Admin: List categories with search, sort & pagination.
     */
    public function categories_index(Request $request)
    {
        Log::info('[LandingPage] categories_index: fetching categories', [
            'user_id'  => Auth::id(),
            'ip'       => $request->ip(),
            'page'     => $request->input('page'),
            'per_page' => $request->input('per_page'),
            'q'        => $request->input('q'),
        ]);

        try {
            $perPage = (int) $request->input('per_page', 20);
            $perPage = $perPage > 0 ? $perPage : 20;

            $page = (int) $request->input('page', 1);
            $page = $page > 0 ? $page : 1;

            $q = trim((string) $request->input('q', ''));

            $query = DB::table('course_categories')
                ->whereNull('deleted_at');

            // 🔎 search on title / description / icon
            if ($q !== '') {
                $query->where(function ($qq) use ($q) {
                    $qq->where('title', 'like', "%{$q}%")
                        ->orWhere('description', 'like', "%{$q}%")
                        ->orWhere('icon', 'like', "%{$q}%");
                });
            }

            $total = $query->count();

            // Order by display_order (non-null first), then by id for stable ordering
            // ISNULL(display_order) ensures rows with a value appear before NULLs.
            $rows = $query
                ->orderByRaw('ISNULL(display_order), display_order')
                ->orderBy('id')
                ->offset(($page - 1) * $perPage)
                ->limit($perPage)
                ->get();

            Log::info('[LandingPage] categories_index: fetched categories', [
                'user_id' => Auth::id(),
                'ip'      => $request->ip(),
                'count'   => $rows->count(),
                'total'   => $total,
            ]);

            return response()->json([
                'status'     => 'success',
                'message'    => 'Categories fetched successfully.',
                'data'       => $rows,
                'pagination' => [
                    'page'      => $page,
                    'per_page'  => $perPage,
                    'total'     => $total,
                    'from'      => $total ? (($page - 1) * $perPage + 1) : null,
                    'to'        => $total ? (($page - 1) * $perPage + $rows->count()) : null,
                ],
            ], 200);
        } catch (Throwable $e) {
            Log::error('[LandingPage] ERROR: categories_index: fetch failed', [
                'user_id' => Auth::id(),
                'ip'      => $request->ip(),
                'error'   => $e->getMessage(),
                'line'    => $e->getLine(),
                'file'    => $e->getFile(),
            ]);

            return response()->json([
                'status'     => 'error',
                'message'    => 'Failed to fetch categories.',
                'error'      => $e->getMessage(), // hide in prod if needed
                'data'       => [],
                'pagination' => null,
            ], 500);
        }
    }

    /**
     * Admin: Create a new category.
     */
    public function categories_store(Request $request)
    {
        $validated = $request->validate([
            'title'       => ['required', 'string', 'max:255'],
            'icon'        => ['nullable', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
        ]);

        Log::info('[LandingPage] categories_store: inserting category', [
            'user_id' => Auth::id(),
            'ip'      => $request->ip(),
            'payload' => $validated,
        ]);

        try {
            $now = now();

            $id = DB::table('course_categories')->insertGetId([
                'uuid'          => (string) Str::uuid(),
                'created_by'    => Auth::id(),
                'title'         => $validated['title'],
                'icon'          => $validated['icon'] ?? null,
                'description'   => $validated['description'] ?? null,
                'display_order' => null,
                'created_at'    => $now,
                'updated_at'    => $now,
                'deleted_at'    => null,
            ]);

            $row = DB::table('course_categories')
                ->where('id', $id)
                ->first();

            // ✅ ACTIVITY LOG (POST -> store)
            $this->logActivity(
                $request,
                'store',
                'Created course category: ' . ($row->title ?? 'N/A'),
                'course_categories',
                $id,
                ['title','icon','description','display_order'],
                null,
                $row ? (array)$row : null
            );

            return response()->json([
                'status'  => 'success',
                'message' => 'Category created successfully.',
                'data'    => $row,
            ], 201);
        } catch (Throwable $e) {
            Log::error('[LandingPage] ERROR: categories_store: insert failed', [
                'user_id' => Auth::id(),
                'ip'      => $request->ip(),
                'error'   => $e->getMessage(),
                'line'    => $e->getLine(),
                'file'    => $e->getFile(),
                'payload' => $validated,
            ]);

            return response()->json([
                'status'  => 'error',
                'message' => 'Failed to create category.',
                'error'   => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Admin: Update an existing category.
     */
    public function categories_update(Request $request, int $id)
    {
        $validated = $request->validate([
            'title'       => ['required', 'string', 'max:255'],
            'icon'        => ['nullable', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
        ]);

        Log::info('[LandingPage] categories_update: updating category', [
            'user_id' => Auth::id(),
            'ip'      => $request->ip(),
            'id'      => $id,
            'payload' => $validated,
        ]);

        try {
            $row = DB::table('course_categories')
                ->where('id', $id)
                ->whereNull('deleted_at')
                ->first();

            if (!$row) {
                return response()->json([
                    'status'  => 'error',
                    'message' => 'Category not found.',
                ], 404);
            }

            $before = (array) $row;

            $updateData = [
                'title'       => $validated['title'],
                'icon'        => array_key_exists('icon', $validated)
                    ? ($validated['icon'] ?? null)
                    : $row->icon,
                'description' => array_key_exists('description', $validated)
                    ? ($validated['description'] ?? null)
                    : $row->description,
                'updated_at'  => now(),
            ];

            DB::table('course_categories')
                ->where('id', $id)
                ->update($updateData);

            $fresh = DB::table('course_categories')
                ->where('id', $id)
                ->first();

            // ✅ ACTIVITY LOG (PUT/PATCH -> update)
            $this->logActivity(
                $request,
                'update',
                'Updated course category: ' . ($fresh->title ?? $row->title ?? 'N/A'),
                'course_categories',
                $id,
                array_keys($updateData),
                $before,
                $fresh ? (array)$fresh : null
            );

            return response()->json([
                'status'  => 'success',
                'message' => 'Category updated successfully.',
                'data'    => $fresh,
            ], 200);
        } catch (Throwable $e) {
            Log::error('[LandingPage] ERROR: categories_update: update failed', [
                'user_id' => Auth::id(),
                'ip'      => $request->ip(),
                'id'      => $id,
                'error'   => $e->getMessage(),
                'line'    => $e->getLine(),
                'file'    => $e->getFile(),
                'payload' => $validated,
            ]);

            return response()->json([
                'status'  => 'error',
                'message' => 'Failed to update category.',
                'error'   => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Admin: Soft delete a category.
     */
    public function categories_destroy(Request $request, int $id)
    {
        Log::info('[LandingPage] categories_destroy: soft-deleting category', [
            'user_id' => Auth::id(),
            'ip'      => $request->ip(),
            'id'      => $id,
        ]);

        try {
            $row = DB::table('course_categories')
                ->where('id', $id)
                ->whereNull('deleted_at')
                ->first();

            if (!$row) {
                return response()->json([
                    'status'  => 'error',
                    'message' => 'Category not found or already deleted.',
                ], 404);
            }

            $before = (array) $row;

            DB::table('course_categories')
                ->where('id', $id)
                ->update([
                    'deleted_at' => now(),
                    'updated_at' => now(),
                ]);

            $after = DB::table('course_categories')->where('id', $id)->first();

            // ✅ ACTIVITY LOG (DELETE -> destroy)
            $this->logActivity(
                $request,
                'destroy',
                'Deleted course category: ' . ($row->title ?? 'N/A'),
                'course_categories',
                $id,
                ['deleted_at','updated_at'],
                $before,
                $after ? (array)$after : null
            );

            return response()->json([
                'status'  => 'success',
                'message' => 'Category deleted successfully.',
            ], 200);
        } catch (Throwable $e) {
            Log::error('[LandingPage] ERROR: categories_destroy: delete failed', [
                'user_id' => Auth::id(),
                'ip'      => $request->ip(),
                'id'      => $id,
                'error'   => $e->getMessage(),
                'line'    => $e->getLine(),
                'file'    => $e->getFile(),
            ]);

            return response()->json([
                'status'  => 'error',
                'message' => 'Failed to delete category.',
                'error'   => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Public: Display categories (for landing page, etc.).
     */
    public function categories_display(Request $request)
    {
        Log::info('[LandingPage] categories_display: public display fetch', [
            'user_id' => Auth::id(),
            'ip'      => $request->ip(),
        ]);

        try {
            $rows = DB::table('course_categories')
                ->whereNull('deleted_at')
                // Use display_order first (nulls last), then created_at desc
                ->orderByRaw('CASE WHEN display_order IS NULL THEN 1 ELSE 0 END ASC')
                ->orderBy('display_order')
                ->orderBy('created_at', 'desc')
                ->get([
                    'id',
                    'uuid',
                    'title',
                    'icon',
                    'description',
                    'display_order',
                    'created_at',
                ]);

            return response()->json([
                'status' => 'success',
                'data'   => $rows,
            ], 200);
        } catch (Throwable $e) {
            Log::error('[LandingPage] ERROR: categories_display: fetch failed', [
                'user_id' => Auth::id(),
                'ip'      => $request->ip(),
                'error'   => $e->getMessage(),
                'line'    => $e->getLine(),
                'file'    => $e->getFile(),
            ]);

            return response()->json([
                'status'  => 'error',
                'message' => 'Failed to fetch categories for display.',
                'error'   => $e->getMessage(),
                'data'    => [],
            ], 500);
        }
    }

    /**
     * Admin: Reorder categories by IDs array.
     *
     * Example payload:
     * { "ids": [5, 3, 7, 1] }
     */
    public function categories_reorder(Request $request)
    {
        $data = $request->validate([
            'ids'   => ['required', 'array', 'min:1'],
            'ids.*' => ['integer', 'distinct'],
        ]);

        $ids = $data['ids'];

        Log::info('[LandingPage] categories_reorder: reordering', [
            'user_id' => Auth::id(),
            'ip'      => $request->ip(),
            'ids'     => $ids,
        ]);

        try {
            // ✅ ACTIVITY LOG (PATCH -> update)
            $beforeRows = DB::table('course_categories')
                ->whereIn('id', $ids)
                ->get()
                ->map(fn($r) => (array)$r)
                ->all();

            DB::transaction(function () use ($ids) {
                $now = now();

                foreach ($ids as $index => $id) {
                    DB::table('course_categories')
                        ->where('id', $id)
                        ->whereNull('deleted_at')
                        ->update([
                            'display_order' => $index, // or $index + 1 if you prefer 1-based
                            'updated_at'    => $now,
                        ]);
                }
            });

            $afterRows = DB::table('course_categories')
                ->whereIn('id', $ids)
                ->get()
                ->map(fn($r) => (array)$r)
                ->all();

            $this->logActivity(
                $request,
                'update',
                'Reordered course categories',
                'course_categories',
                null,
                ['display_order','updated_at'],
                ['before' => $beforeRows],
                ['after' => $afterRows]
            );

            return response()->json([
                'status'  => 'success',
                'message' => 'Categories reordered successfully.',
            ], 200);
        } catch (Throwable $e) {
            Log::error('[LandingPage] ERROR: categories_reorder: failed', [
                'user_id' => Auth::id(),
                'ip'      => $request->ip(),
                'ids'     => $ids,
                'error'   => $e->getMessage(),
                'line'    => $e->getLine(),
                'file'    => $e->getFile(),
            ]);

            return response()->json([
                'status'  => 'error',
                'message' => 'Failed to reorder categories.',
                'error'   => $e->getMessage(),
            ], 500);
        }
    }
}
