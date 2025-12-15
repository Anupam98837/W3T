<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Storage;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;

class LandingPageController extends Controller
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

     public function updatesIndex(Request $request)
    {
        $this->logInfo('updatesIndex: fetching updates');

        $perPage = (int) $request->input('per_page', 20);
        if ($perPage <= 0) {
            $perPage = 20;
        }
        $page = (int) $request->input('page', 1);
        if ($page <= 0) {
            $page = 1;
        }

        $q = trim((string) $request->input('q', ''));

        $query = DB::table('landingpage_updates')
            ->whereNull('deleted_at');

        if ($q !== '') {
            $query->where(function ($sub) use ($q) {
                $sub->where('title', 'like', "%{$q}%")
                    ->orWhere('description', 'like', "%{$q}%");
            });
        }

        $total = (clone $query)->count();

        $updates = $query
            ->orderBy('display_order')
            ->orderBy('created_at', 'desc')
            ->forPage($page, $perPage)
            ->get();

        $this->logInfo('updatesIndex: fetched updates', [
            'count'   => $updates->count(),
            'total'   => $total,
            'page'    => $page,
            'perPage' => $perPage,
            'q'       => $q,
        ]);

        return response()->json([
            'status' => 'success',
            'data'   => $updates,
            'pagination' => [
                'total'      => $total,
                'page'       => $page,
                'per_page'   => $perPage,
                'last_page'  => (int) ceil($total / max(1, $perPage)),
            ],
        ]);
    }

    /**
     * Admin: create a new update.
     * - JSON (AJAX / API): returns JSON
     * - Normal web: redirects with flash message
     */
    public function updatesStore(Request $request)
    {
        $validated = $request->validate([
            'title'         => ['required', 'string'],
            'description'   => ['nullable', 'string'],
            'display_order' => ['nullable', 'integer', 'min:0'],
        ]);

        try {
            $this->logInfo('updatesStore: inserting update', ['payload' => $validated]);

            $id = DB::table('landingpage_updates')->insertGetId([
                'uuid'          => (string) Str::uuid(),
                'title'         => $validated['title'],
                'description'   => $validated['description'] ?? null,
                'display_order' => $validated['display_order'] ?? 0,
                'created_by'    => Auth::id(),
                'created_at'    => now(),
                'updated_at'    => now(),
                'deleted_at'    => null,
            ]);

            $this->logInfo('updatesStore: insert success', ['id' => $id]);

        } catch (\Throwable $e) {
            $this->logError($e, 'updatesStore: insert failed', ['payload' => $validated]);

            if ($request->expectsJson()) {
                return response()->json([
                    'status'  => 'error',
                    'message' => 'Failed to create update.',
                    'error'   => $e->getMessage(),
                ], 500);
            }

            throw $e;
        }

        // If request expects JSON (your JS fetch has Accept: application/json)
        if ($request->expectsJson()) {
            $new = DB::table('landingpage_updates')->where('id', $id)->first();

            return response()->json([
                'status'  => 'success',
                'message' => 'Update created successfully.',
                'data'    => $new,
            ], 201);
        }

        // Fallback for normal web form
        return redirect()
            ->route('landing.updates.index')
            ->with('success', 'Update created successfully.');
    }

    /**
     * Admin: update an existing update.
     * - JSON (AJAX / API): returns JSON
     * - Normal web: redirects with flash message
     */
    public function updatesUpdate(Request $request, $id)
    {
        $validated = $request->validate([
            'title'         => ['required', 'string'],
            'description'   => ['nullable', 'string'],
            'display_order' => ['nullable', 'integer', 'min:0'],
        ]);

        try {
            $this->logInfo('updatesUpdate: updating update', ['id' => $id, 'payload' => $validated]);

            DB::table('landingpage_updates')
                ->where('id', $id)
                ->whereNull('deleted_at')
                ->update([
                    'title'         => $validated['title'],
                    'description'   => $validated['description'] ?? null,
                    'display_order' => $validated['display_order'] ?? 0,
                    'updated_at'    => now(),
                ]);

            $this->logInfo('updatesUpdate: update success', ['id' => $id]);

        } catch (\Throwable $e) {
            $this->logError($e, 'updatesUpdate: update failed', ['id' => $id, 'payload' => $validated]);

            if ($request->expectsJson()) {
                return response()->json([
                    'status'  => 'error',
                    'message' => 'Failed to update update.',
                    'error'   => $e->getMessage(),
                ], 500);
            }

            throw $e;
        }

        if ($request->expectsJson()) {
            $updated = DB::table('landingpage_updates')->where('id', $id)->first();

            return response()->json([
                'status'  => 'success',
                'message' => 'Update updated successfully.',
                'data'    => $updated,
            ], 200);
        }

        return redirect()
            ->route('landing.updates.index')
            ->with('success', 'Update updated successfully.');
    }

    /**
     * Admin: soft-delete an update (set deleted_at).
     * - JSON (AJAX / API): returns JSON
     * - Normal web: redirects with flash message
     */
    public function updatesDestroy(Request $request, $id)
    {
        try {
            $this->logInfo('updatesDestroy: soft deleting update', ['id' => $id]);

            DB::table('landingpage_updates')
                ->where('id', $id)
                ->whereNull('deleted_at')
                ->update([
                    'deleted_at' => now(),
                    'updated_at' => now(),
                ]);

            $this->logInfo('updatesDestroy: delete success', ['id' => $id]);

        } catch (\Throwable $e) {
            $this->logError($e, 'updatesDestroy: delete failed', ['id' => $id]);

            if ($request->expectsJson()) {
                return response()->json([
                    'status'  => 'error',
                    'message' => 'Failed to delete update.',
                    'error'   => $e->getMessage(),
                ], 500);
            }

            throw $e;
        }

        if ($request->expectsJson()) {
            return response()->json([
                'status'  => 'success',
                'message' => 'Update deleted successfully.',
            ], 200);
        }

        return redirect()
            ->route('landing.updates.index')
            ->with('success', 'Update deleted successfully.');
    }

    /**
     * Admin: reorder updates (used by drag & drop).
     * Expects: { ids: [1,4,2,...] }
     */
    public function updates_reorder(Request $request)
    {
        $data = $request->validate([
            'ids'   => ['required', 'array', 'min:1'],
            'ids.*' => ['integer', 'distinct'],
        ]);

        $ids = $data['ids'];

        try {
            $this->logInfo('updates_reorder: reordering updates', ['ids' => $ids]);

            DB::transaction(function () use ($ids) {
                $now = now();

                foreach ($ids as $index => $id) {
                    DB::table('landingpage_updates')
                        ->where('id', $id)
                        ->whereNull('deleted_at')
                        ->update([
                            'display_order' => $index, // or $index + 1 if you prefer 1-based
                            'updated_at'    => $now,
                        ]);
                }
            });

            return response()->json([
                'status'  => 'success',
                'message' => 'Updates reordered successfully.',
            ], 200);

        } catch (\Throwable $e) {
            $this->logError($e, 'updates_reorder: failed', ['ids' => $ids]);

            return response()->json([
                'status'  => 'error',
                'message' => 'Failed to reorder updates.',
                'error'   => $e->getMessage(),
            ], 500);
        }
    }
    // contacts
    public function contact_index(Request $request)
    {
        try {
            $this->logInfo('contact_index: fetching contacts', [
                'page'     => $request->input('page'),
                'per_page' => $request->input('per_page'),
                'q'        => $request->input('q'),
                'sort'     => $request->input('sort'),
            ]);

            $perPage = (int) $request->input('per_page', 20);
            $page    = (int) $request->input('page', 1);
            $q       = trim((string) $request->input('q', ''));
            $sort    = $request->input('sort', '-created_at'); // "-col" = desc

            $query = DB::table('landingpage_master_contact')
                ->whereNull('deleted_at');

            /* =====================
               Filtering / Search
            ===================== */
            if ($q !== '') {
                $query->where(function ($qq) use ($q) {
                    $qq->where('contact_key', 'like', "%{$q}%")
                        ->orWhere('value', 'like', "%{$q}%")
                        ->orWhere('icon', 'like', "%{$q}%");
                });
            }

            /* =====================
               Sorting
            ===================== */
            $dir = ($sort[0] === '-') ? 'desc' : 'asc';
            $col = ltrim($sort, '-');

            // allow only specific columns
            if (!in_array($col, ['contact_key', 'value', 'display_order', 'created_at'], true)) {
                $col = 'created_at';
            }

            /* =====================
               Pagination
            ===================== */
            $total = $query->count();
            $rows  = $query->orderBy($col, $dir)
                ->offset(($page - 1) * $perPage)
                ->limit($perPage)
                ->get();

            $this->logInfo('contact_index: fetched contacts', [
                'count' => $rows->count(),
                'total' => $total,
            ]);

            return response()->json([
                'status'     => 'success',
                'message'    => 'Contacts fetched successfully.',
                'data'       => $rows,
                'pagination' => [
                    'page'      => $page,
                    'per_page'  => $perPage,
                    'total'     => $total,
                ],
            ], 200);
        } catch (\Throwable $e) {
            $this->logError($e, 'contact_index: failed to fetch contacts');

            return response()->json([
                'status'     => 'error',
                'message'    => 'Failed to fetch contacts.',
                'error'      => $e->getMessage(), // remove in production if needed
                'data'       => [],
                'pagination' => null,
            ], 500);
        }
    }

    // ========= CREATE =========
    public function contact_store(Request $request)
    {
        $validated = $request->validate([
            'contact_key'   => 'required|string|max:255',
            'value'         => 'required|string|max:255',
            'icon'          => 'nullable|string|max:255',
            'image_path'    => 'nullable|string|max:255',
            'display_order' => 'nullable|integer|min:0',
        ]);

        try {
            $this->logInfo('contact_store: inserting contact', ['payload' => $validated]);

            $id = DB::table('landingpage_master_contact')->insertGetId([
                'uuid'          => DB::raw('(UUID())'),
                'contact_key'   => $validated['contact_key'],
                'value'         => $validated['value'],
                'icon'          => $validated['icon'] ?? null,
                'image_path'    => $validated['image_path'] ?? null,
                'display_order' => $validated['display_order'] ?? 0,
                'created_by'    => Auth::id(),
                'created_at'    => now(),
                'updated_at'    => now(),
            ]);

            $this->logInfo('contact_store: insert success', ['id' => $id]);

            return response()->json([
                'success' => true,
                'message' => 'Contact created successfully.',
                'id'      => $id,
            ], 201);
        } catch (\Throwable $e) {
            $this->logError($e, 'contact_store: insert failed', ['payload' => $validated]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to create contact.',
            ], 500);
        }
    }

    // ========= UPDATE =========
    public function contact_update(Request $request, $id)
    {
        $validated = $request->validate([
            'contact_key'   => 'sometimes|required|string|max:255',
            'value'         => 'sometimes|required|string|max:255',
            'icon'          => 'nullable|string|max:255',
            'image_path'    => 'nullable|string|max:255',
            'display_order' => 'nullable|integer|min:0',
        ]);

        try {
            $this->logInfo('contact_update: updating contact', ['id' => $id, 'payload' => $validated]);

            $exists = DB::table('landingpage_master_contact')
                ->where('id', $id)
                ->whereNull('deleted_at')
                ->exists();

            if (!$exists) {
                $this->logInfo('contact_update: contact not found', ['id' => $id]);

                return response()->json([
                    'success' => false,
                    'message' => 'Contact not found.',
                ], 404);
            }

            $validated['updated_at'] = now();

            DB::table('landingpage_master_contact')
                ->where('id', $id)
                ->update($validated);

            $this->logInfo('contact_update: update success', ['id' => $id]);

            return response()->json([
                'success' => true,
                'message' => 'Contact updated successfully.',
            ]);
        } catch (\Throwable $e) {
            $this->logError($e, 'contact_update: update failed', ['id' => $id]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to update contact.',
            ], 500);
        }
    }

    // ========= DELETE (soft) =========
    public function contact_destroy($id)
    {
        try {
            $this->logInfo('contact_destroy: deleting contact', ['id' => $id]);

            $exists = DB::table('landingpage_master_contact')
                ->where('id', $id)
                ->whereNull('deleted_at')
                ->exists();

            if (!$exists) {
                $this->logInfo('contact_destroy: contact not found', ['id' => $id]);

                return response()->json([
                    'success' => false,
                    'message' => 'Contact not found.',
                ], 404);
            }

            DB::table('landingpage_master_contact')
                ->where('id', $id)
                ->update([
                    'deleted_at' => now(),
                    'updated_at' => now(),
                ]);

            $this->logInfo('contact_destroy: delete success', ['id' => $id]);

            return response()->json([
                'success' => true,
                'message' => 'Contact deleted successfully.',
            ]);
        } catch (\Throwable $e) {
            $this->logError($e, 'contact_destroy: delete failed', ['id' => $id]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to delete contact.',
            ], 500);
        }
    }

    public function contactsDisplay()
    {
        $this->logInfo('contactsDisplay: fetching display contacts');

        $contacts = DB::table('landingpage_master_contact')
            ->whereNull('deleted_at')
            ->orderBy('display_order')
            ->orderBy('created_at', 'desc')
            ->get([
                'id',
                'contact_key',
                'value',
                'icon',
                'image_path',
                'display_order',
                'created_at',
            ]);

        $this->logInfo('contactsDisplay: fetched contacts', ['count' => $contacts->count()]);

        return response()->json([
            'status' => 'success',
            'data'   => $contacts,
        ]);
    }
public function contact_reorder(Request $request)
{
    // Expect: { "ids": [3, 5, 2, 10] } in the new order
    $data = $request->validate([
        'ids'   => ['required', 'array', 'min:1'],
        'ids.*' => ['integer', 'distinct'],
    ]);

    $ids = $data['ids'];

    try {
        $this->logInfo('contact_reorder: reordering contacts', [
            'ids'     => $ids,
            'user_id' => optional($request->user())->id,
            'ip'      => $request->ip(),
        ]);

        DB::transaction(function () use ($ids) {
            $now = now();

            foreach ($ids as $index => $id) {
                DB::table('landingpage_master_contact')
                    ->where('id', $id)
                    ->whereNull('deleted_at')
                    ->update([
                        'display_order' => $index, // or $index + 1 if you prefer 1-based
                        'updated_at'    => $now,
                    ]);
            }
        });

        return response()->json([
            'status'  => 'success',
            'message' => 'Contacts reordered successfully.',
        ], 200);
    } catch (\Throwable $e) {
        $this->logError($e, 'contact_reorder: failed', [
            'ids' => $ids,
        ]);

        return response()->json([
            'status'  => 'error',
            'message' => 'Failed to reorder contacts.',
            'error'   => $e->getMessage(), // hide in prod if needed
        ], 500);
    }
}
// Hero Image
    public function hero_index(Request $request)
    {
        try {
            $this->logInfo('hero_index: fetching hero images', [
                'page'     => $request->input('page'),
                'per_page' => $request->input('per_page'),
                'q'        => $request->input('q'),
                'sort'     => $request->input('sort'),
            ]);

            $perPage = (int) $request->input('per_page', 20);
            $page    = (int) $request->input('page', 1);
            $q       = trim((string) $request->input('q', ''));
            $sort    = $request->input('sort', '-display_order'); // default: lowest order first

            $query = DB::table('landingpage_hero_image')
                ->whereNull('deleted_at');

            // search on title or url
            if ($q !== '') {
                $query->where(function ($qq) use ($q) {
                    $qq->where('img_title', 'like', "%{$q}%")
                        ->orWhere('image_url', 'like', "%{$q}%");
                });
            }

            // sorting
            $dir = ($sort[0] === '-') ? 'desc' : 'asc';
            $col = ltrim($sort, '-');

            if (!in_array($col, ['img_title', 'image_url', 'display_order', 'created_at'], true)) {
                $col = 'display_order'; // fallback
            }

            $total = $query->count();

            $rows = $query->orderBy($col, $dir)
                ->orderBy('id') // stable
                ->offset(($page - 1) * $perPage)
                ->limit($perPage)
                ->get();

            $this->logInfo('hero_index: fetched hero images', [
                'count' => $rows->count(),
                'total' => $total,
            ]);

            return response()->json([
                'status'     => 'success',
                'message'    => 'Hero images fetched successfully.',
                'data'       => $rows,
                'pagination' => [
                    'page'      => $page,
                    'per_page'  => $perPage,
                    'total'     => $total,
                ],
            ], 200);
        } catch (\Throwable $e) {
            $this->logError($e, 'hero_index: failed to fetch hero images');

            return response()->json([
                'status'     => 'error',
                'message'    => 'Failed to fetch hero images.',
                'error'      => $e->getMessage(),   // hide in prod if you want
                'data'       => [],
                'pagination' => null,
            ], 500);
        }
    }

    public function hero_store(Request $request)
    {
        // validation
        $validated = $request->validate([
            'img_title'      => ['nullable', 'string', 'max:255'],
            'image_url'      => ['required', 'string', 'max:1024'], // you can change to 'url' if always external
            'display_order'  => ['nullable', 'integer', 'min:0'],
        ]);

        try {
            $this->logInfo('hero_store: inserting hero image', ['payload' => $validated]);

            $now = now();

            $id = DB::table('landingpage_hero_image')->insertGetId([
                'uuid'          => (string) Str::uuid(),        // or rely on DB default
                'img_title'     => $validated['img_title'] ?? null,
                'image_url'     => $validated['image_url'],
                'display_order' => $validated['display_order'] ?? 0,
                'created_by'    => Auth::id(),
                'created_at'    => $now,
                'updated_at'    => $now,
                'deleted_at'    => null,
            ]);

            $row = DB::table('landingpage_hero_image')
                ->where('id', $id)
                ->first();

            $this->logInfo('hero_store: insert success', ['id' => $id]);

            return response()->json([
                'status'  => 'success',
                'message' => 'Hero image created successfully.',
                'data'    => $row,
            ], 201);
        } catch (\Throwable $e) {
            $this->logError($e, 'hero_store: insert failed', ['payload' => $validated]);

            return response()->json([
                'status'  => 'error',
                'message' => 'Failed to create hero image.',
                'error'   => $e->getMessage(),
            ], 500);
        }
    }

    public function hero_update(Request $request, $id)
    {
        // validation
        $validated = $request->validate([
            'img_title'      => ['nullable', 'string', 'max:255'],
            'image_url'      => ['sometimes', 'required', 'string', 'max:1024'],
            'display_order'  => ['nullable', 'integer', 'min:0'],
        ]);

        try {
            $this->logInfo('hero_update: updating hero image', ['id' => $id, 'payload' => $validated]);

            $row = DB::table('landingpage_hero_image')
                ->where('id', $id)
                ->whereNull('deleted_at')
                ->first();

            if (!$row) {
                $this->logInfo('hero_update: hero image not found', ['id' => $id]);

                return response()->json([
                    'status'  => 'error',
                    'message' => 'Hero image not found.',
                ], 404);
            }

            $updateData = [
                'img_title'     => array_key_exists('img_title', $validated)
                    ? $validated['img_title']
                    : $row->img_title,
                'display_order' => array_key_exists('display_order', $validated)
                    ? ($validated['display_order'] ?? 0)
                    : $row->display_order,
                'updated_at'    => now(),
            ];

            if (array_key_exists('image_url', $validated)) {
                $updateData['image_url'] = $validated['image_url'];
            }

            DB::table('landingpage_hero_image')
                ->where('id', $id)
                ->update($updateData);

            $fresh = DB::table('landingpage_hero_image')
                ->where('id', $id)
                ->first();

            $this->logInfo('hero_update: update success', ['id' => $id]);

            return response()->json([
                'status'  => 'success',
                'message' => 'Hero image updated successfully.',
                'data'    => $fresh,
            ], 200);
        } catch (\Throwable $e) {
            $this->logError($e, 'hero_update: update failed', ['id' => $id]);

            return response()->json([
                'status'  => 'error',
                'message' => 'Failed to update hero image.',
                'error'   => $e->getMessage(),
            ], 500);
        }
    }

    public function hero_destroy($id)
    {
        try {
            $this->logInfo('hero_destroy: deleting hero image', ['id' => $id]);

            $row = DB::table('landingpage_hero_image')
                ->where('id', $id)
                ->whereNull('deleted_at')
                ->first();

            if (!$row) {
                $this->logInfo('hero_destroy: hero image not found', ['id' => $id]);

                return response()->json([
                    'status'  => 'error',
                    'message' => 'Hero image not found or already deleted.',
                ], 404);
            }

            DB::table('landingpage_hero_image')
                ->where('id', $id)
                ->update([
                    'deleted_at' => now(),
                    'updated_at' => now(),
                ]);

            $this->logInfo('hero_destroy: delete success', ['id' => $id]);

            return response()->json([
                'status'  => 'success',
                'message' => 'Hero image deleted successfully.',
            ], 200);
        } catch (\Throwable $e) {
            $this->logError($e, 'hero_destroy: delete failed', ['id' => $id]);

            return response()->json([
                'status'  => 'error',
                'message' => 'Failed to delete hero image.',
                'error'   => $e->getMessage(),
            ], 500);
        }
    }

    public function hero_display()
    {
        $this->logInfo('hero_display: fetching display hero images');

        $rows = DB::table('landingpage_hero_image')
            ->whereNull('deleted_at')
            ->orderBy('display_order')
            ->orderBy('created_at', 'desc')
            ->get([
                'id',
                'uuid',
                'img_title',
                'image_url',
                'display_order',
                'created_at',
            ]);

        $this->logInfo('hero_display: fetched hero images', ['count' => $rows->count()]);

        return response()->json([
            'status' => 'success',
            'data'   => $rows,
        ]);
    }
    public function hero_reorder(Request $request)
{
    $data = $request->validate([
        'ids'   => ['required', 'array', 'min:1'],
        'ids.*' => ['integer', 'distinct'],
    ]);

    $ids = $data['ids'];

    try {
        $this->logInfo('hero_reorder: reordering hero images', ['ids' => $ids]);

        DB::transaction(function () use ($ids) {
            $now = now();

            foreach ($ids as $index => $id) {
                DB::table('landingpage_hero_image')
                    ->where('id', $id)
                    ->whereNull('deleted_at')
                    ->update([
                        'display_order' => $index, // or $index + 1
                        'updated_at'    => $now,
                    ]);
            }
        });

        return response()->json([
            'status'  => 'success',
            'message' => 'Hero images reordered successfully.',
        ], 200);
    } catch (\Throwable $e) {
        $this->logError($e, 'hero_reorder: failed', ['ids' => $ids]);

        return response()->json([
            'status'  => 'error',
            'message' => 'Failed to reorder hero images.',
            'error'   => $e->getMessage(),
        ], 500);
    }
}

   public function upload(Request $request)
{
    $request->validate([
        'file' => ['required', 'image', 'max:5120'],
    ]);

    $file = $request->file('file');
    $filename = uniqid().'.'.$file->getClientOriginalExtension();

    // Path: public/uploads/hero-images/xxxx.jpg
    $file->move(public_path('uploads/hero-images'), $filename);

    $url = asset('uploads/hero-images/'.$filename);

    return response()->json([
        'url'  => $url,
        'path' => 'uploads/hero-images/'.$filename,
    ], 201);
}

    /**
     * GET /api/media/images
     * Simple image library for the "From Library" modal.
     *
     * Currently returns all hero_images; you can later expand
     * to a dedicated media table if needed.
     */
   public function library(Request $request)
{
    $q = $request->query('search');

    $query = DB::table('course_featured_media')
        ->whereNotNull('featured_url')
        ->where('featured_url', '<>', '');

    if ($q) {
        $query->where(function ($sub) use ($q) {
            $sub->where('featured_url', 'like', "%{$q}%");
        });
    }

    $images = $query
        ->orderBy('created_at', 'desc')
        ->get();

    $data = $images->map(function ($img) {
        $url  = $img->featured_url;
        $name = basename(parse_url($url, PHP_URL_PATH));

        return [
            'url'  => $url,
            'name' => $name ?: 'Image',
            'size' => null,
        ];
    });

    return response()->json([
        'data' => $data,
    ]);
}
//Categories
  public function categories_index(Request $request)
{
    Log::info('[LandingPage] categories_index: fetching categories', [
        'user_id'  => Auth::id(),
        'ip'       => $request->ip(),
        'page'     => $request->input('page'),
        'per_page' => $request->input('per_page'),
        'q'        => $request->input('q'),
        'sort'     => $request->input('sort'),
    ]);

    try {
        $perPage = (int) $request->input('per_page', 20);
        $page    = (int) $request->input('page', 1);
        $q       = trim((string) $request->input('q', ''));
        $sort    = $request->input('sort', '-created_at'); // "-col" = desc

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

        // sorting
        $dir = ($sort[0] === '-') ? 'desc' : 'asc';
        $col = ltrim($sort, '-');

        // 🔽 allowed sort columns
        if (!in_array($col, ['title', 'description', 'icon', 'created_at'], true)) {
            $col = 'created_at';
        }

        $total = $query->count();

        $rows = $query->orderBy($col, $dir)
            ->orderBy('id') // stable
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
            ],
        ], 200);

    } catch (\Throwable $e) {

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
            'uuid'        => (string) Str::uuid(),
            'created_by'  => Auth::id(),
            'title'       => $validated['title'],
            'icon'        => $validated['icon'] ?? null,
            'description' => $validated['description'] ?? null,
            'created_at'  => $now,
            'updated_at'  => $now,
            'deleted_at'  => null,
        ]);

        $row = DB::table('course_categories')
            ->where('id', $id)
            ->first();

        return response()->json([
            'status'  => 'success',
            'message' => 'Category created successfully.',
            'data'    => $row,
        ], 201);

    } catch (\Throwable $e) {

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

public function categories_update(Request $request, $id)
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

        return response()->json([
            'status'  => 'success',
            'message' => 'Category updated successfully.',
            'data'    => $fresh,
        ], 200);

    } catch (\Throwable $e) {

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

public function categories_destroy(Request $request, $id)
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

        DB::table('course_categories')
            ->where('id', $id)
            ->update([
                'deleted_at' => now(),
                'updated_at' => now(),
            ]);

        return response()->json([
            'status'  => 'success',
            'message' => 'Category deleted successfully.',
        ], 200);

    } catch (\Throwable $e) {

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

public function categories_display()
{
    Log::info('[LandingPage] categories_display: public display fetch', [
        'user_id' => Auth::id(),
    ]);

    $rows = DB::table('course_categories')
        ->whereNull('deleted_at')
        ->orderBy('created_at', 'desc')
        ->get([
            'id',
            'uuid',
            'title',       // 🔹 now included
            'icon',
            'description',
            'created_at',
        ]);

    return response()->json([
        'status' => 'success',
        'data'   => $rows,
    ]);
}
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
        DB::transaction(function () use ($ids) {
            $now = now();

            foreach ($ids as $index => $id) {
                DB::table('course_categories')
                    ->where('id', $id)
                    ->whereNull('deleted_at')
                    ->update([
                        'display_order' => $index, // or $index + 1
                        'updated_at'    => $now,
                    ]);
            }
        });

        return response()->json([
            'status'  => 'success',
            'message' => 'Categories reordered successfully.',
        ], 200);

    } catch (\Throwable $e) {

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
//Featured Courses
public function featuredCourses_index(Request $request)
{
    Log::info('[LandingPage] featuredCourses_index: fetching featured courses (from courses table)', [
        'user_id'  => Auth::id(),
        'ip'       => $request->ip(),
        'page'     => $request->input('page'),
        'per_page' => $request->input('per_page'),
        'q'        => $request->input('q'),
        'sort'     => $request->input('sort'),
    ]);

    try {

        $perPage = (int) $request->input('per_page', 20);
        $page    = (int) $request->input('page', 1);
        $q       = trim((string) $request->input('q', ''));
        $sort    = (string) $request->input('sort', '-featured_rank'); // "-col" => desc

        // base query on courses table
        $query = DB::table('courses')
            ->whereNull('deleted_at');

        // Optional search (by id, title, slug)
        if ($q !== '') {
            $query->where(function ($qq) use ($q) {
                $qq->where('id', 'like', "%{$q}%")
                   ->orWhere('title', 'like', "%{$q}%")
                   ->orWhere('slug', 'like', "%{$q}%");
            });
        }

        // Sorting
        $dir = (strlen($sort) && $sort[0] === '-') ? 'desc' : 'asc';
        $col = ltrim($sort, '-');

        if (!in_array($col, ['featured_rank', 'order_no', 'title', 'created_at', 'price_amount'], true)) {
            $col = 'featured_rank';
        }

        $total = $query->count();

        $rows = $query
            ->orderBy($col, $dir)
            ->orderBy('id')
            ->offset(($page - 1) * $perPage)
            ->limit($perPage)
            ->get([
                'id',
                'uuid',
                'title',
                'slug',
                'status',
                'course_type',
                'price_amount',
                'price_currency',
                'is_featured',
                'featured_rank',
                'order_no',
                'category_id',
                'created_at',
            ]);

        // Split into featured vs non-featured on the current page
        $featured     = $rows->filter(fn ($r) => (int) $r->is_featured === 1)->values();
        $nonFeatured  = $rows->filter(fn ($r) => (int) $r->is_featured !== 1)->values();

        Log::info('[LandingPage] featuredCourses_index: fetched', [
            'user_id'        => Auth::id(),
            'ip'             => $request->ip(),
            'count'          => $rows->count(),
            'total'          => $total,
            'featured_count' => $featured->count(),
            'non_count'      => $nonFeatured->count(),
        ]);

        return response()->json([
            'status'  => 'success',
            'message' => 'Courses fetched successfully.',
            'data'    => [
                'featured'      => $featured,
                'non_featured'  => $nonFeatured,
            ],
            'pagination' => [
                'page'      => $page,
                'per_page'  => $perPage,
                'total'     => $total,
            ],
        ]);

    } catch (\Throwable $e) {

        Log::error('[LandingPage] ERROR: featuredCourses_index', [
            'user_id' => Auth::id(),
            'ip'      => $request->ip(),
            'error'   => $e->getMessage(),
        ]);

        return response()->json([
            'status'  => 'error',
            'message' => 'Failed to fetch courses.',
            'error'   => $e->getMessage(),
        ], 500);
    }
}
public function featuredCourses_display()
{
    Log::info('[LandingPage] featuredCourses_display: public fetch (from courses + featured media tables)');

    // 1) Subquery: for each course, pick the latest media row (by id)
    $mediaSub = DB::table('course_featured_media as cfm2')
        ->selectRaw('cfm2.course_id, MAX(cfm2.id) as latest_media_id')
        ->groupBy('cfm2.course_id');

    $rows = DB::table('courses')
        ->leftJoin('course_categories as lc', 'lc.id', '=', 'courses.category_id')
        // join the subquery to know which media row is "latest" for each course
        ->leftJoinSub($mediaSub, 'm', function ($join) {
            $join->on('m.course_id', '=', 'courses.id');
        })
        // join the actual media table again to get the URL of that latest row
        ->leftJoin('course_featured_media as cfm', function ($join) {
            $join->on('cfm.course_id', '=', 'courses.id')
                 ->on('cfm.id', '=', 'm.latest_media_id');
        })
        ->whereNull('courses.deleted_at')
        ->where('courses.status', 'published')
        ->orderBy('courses.featured_rank')
        ->orderBy('courses.order_no')
        ->orderBy('courses.id')
        ->get([
            'courses.id',
            'courses.uuid',
            'courses.title',
            'courses.slug',
            'courses.status',
            'courses.course_type',
            'courses.price_amount',
            'courses.price_currency',
            'courses.is_featured',
            'courses.featured_rank',
            'courses.order_no',
            'courses.category_id',
            'courses.short_description',
            'courses.level',
            'courses.created_at',
            'lc.title as category_title',

            // 👇 the chosen image for this course (latest media)
            'cfm.featured_url as image_url',
        ]);

    // 1 course = 1 row already
    $featured    = $rows->filter(fn ($r) => (int) $r->is_featured === 1)->values();
    $nonFeatured = $rows->filter(fn ($r) => (int) $r->is_featured !== 1)->values();

    return response()->json([
        'status' => 'success',
        'data'   => [
            'featured'      => $featured,
            'non_featured'  => $nonFeatured,
        ],
    ]);
}

public function toggleFeatured(Request $request, $courseKey)
{
    Log::info('[LandingPage] toggleFeatured called', [
        'course_key'   => $courseKey,
        'is_featured'  => $request->input('is_featured'),
        'user_id'      => optional($request->user())->id,
        'ip'           => $request->ip(),
    ]);

    $data = $request->validate([
        'is_featured' => ['required', 'boolean'],
    ]);

    $isFeatured = $data['is_featured'] ? 1 : 0;

    $baseQuery = DB::table('courses')
        ->whereNull('deleted_at') // remove if no soft-deletes
        ->where(function ($q) use ($courseKey) {
            $q->where('uuid', $courseKey)
              ->orWhere('id', $courseKey);
        });

    $updateData = [
        'is_featured' => $isFeatured,
        'updated_at'  => now(),
    ];

    // ✅ When turning OFF, set rank to 0 (NOT NULL) instead of null
    if ($isFeatured === 0) {
        $updateData['featured_rank'] = 0;
    }

    $updated = $baseQuery->update($updateData);

    if (!$updated) {
        Log::warning('[LandingPage] toggleFeatured: course not found or not updated', [
            'course_key' => $courseKey,
        ]);

        return response()->json([
            'status'  => 'error',
            'message' => 'Course not found or not updated.',
        ], 404);
    }

    $course = DB::table('courses')
        ->where(function ($q) use ($courseKey) {
            $q->where('uuid', $courseKey)
              ->orWhere('id', $courseKey);
        })
        ->first();

    Log::info('[LandingPage] toggleFeatured: updated', [
        'course_key'    => $courseKey,
        'is_featured'   => $isFeatured,
        'featured_rank' => $course->featured_rank ?? null,
    ]);

    return response()->json([
        'status'  => 'success',
        'message' => $isFeatured
            ? 'Course marked as featured.'
            : 'Course un-featured.',
        'data'    => $course,
    ]);
}
public function featuredCourses_reorder(Request $request)
{
    $data = $request->validate([
        'ids'   => ['required', 'array', 'min:1'],
        'ids.*' => ['integer', 'distinct'],
    ]);

    $ids = $data['ids'];

    Log::info('[LandingPage] featuredCourses_reorder: reordering featured courses', [
        'user_id' => Auth::id(),
        'ip'      => $request->ip(),
        'ids'     => $ids,
    ]);

    try {
        DB::transaction(function () use ($ids) {
            $now = now();

            // 1) Reset all featured flags & ranks
            DB::table('courses')
                ->whereNull('deleted_at')
                ->update([
                    'is_featured'   => 0,
                    'featured_rank' => 0,
                    'updated_at'    => $now,
                ]);

            // 2) Apply new order for given IDs
            foreach ($ids as $index => $id) {
                DB::table('courses')
                    ->where('id', $id)
                    ->whereNull('deleted_at')
                    ->update([
                        'is_featured'   => 1,
                        'featured_rank' => $index + 1, // 1-based rank
                        'updated_at'    => $now,
                    ]);
            }
        });

        return response()->json([
            'status'  => 'success',
            'message' => 'Featured courses reordered successfully.',
        ], 200);

    } catch (\Throwable $e) {

        Log::error('[LandingPage] ERROR: featuredCourses_reorder: failed', [
            'user_id' => Auth::id(),
            'ip'      => $request->ip(),
            'ids'     => $ids,
            'error'   => $e->getMessage(),
            'line'    => $e->getLine(),
            'file'    => $e->getFile(),
        ]);

        return response()->json([
            'status'  => 'error',
            'message' => 'Failed to reorder featured courses.',
            'error'   => $e->getMessage(),
        ], 500);
    }
}

}