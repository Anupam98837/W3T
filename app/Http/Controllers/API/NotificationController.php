<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

use Illuminate\Validation\Rule;

class NotificationController extends Controller
{
    // ─── Helpers ──────────────────────────────────────────────────────────────

    private function actor(Request $request): array
    {
        $id = $request->attributes->get('auth_actor_id')
            ?? $request->attributes->get('auth_id')
            ?? $request->attributes->get('auth_tokenable_id');

        return [
            'id'   => $id ? (int) $id : null,
            'role' => $request->attributes->get('auth_role'),
        ];
    }

    private function decodeReceivers(?string $json): array
    {
        $val = json_decode($json ?? '[]', true);
        return is_array($val) ? array_map(fn($r) => [
            'id'      => (int)($r['id']      ?? 0),
            'role'    => (string)($r['role'] ?? ''),
            'read'    => (int)($r['read']    ?? 0),
            'read_at' => $r['read_at']       ?? null,
        ], $val) : [];
    }

    private function isReadBy(array $receivers, int $userId, ?string $role): bool
    {
        foreach ($receivers as $r) {
            if ((int)$r['id'] === $userId && ($role === null || $r['role'] === $role)) {
                return (int)$r['read'] === 1;
            }
        }
        return false;
    }

    /**
     * WHY JSON_SEARCH instead of JSON_CONTAINS:
     *
     * JSON_CONTAINS(receivers, JSON_OBJECT('id', 1, 'role', 'admin'), '$')
     * requires the stored object to be a SUBSET match — but MySQL's JSON_CONTAINS
     * for objects checks that every key in the needle exists in the haystack object,
     * NOT in the array elements. It does NOT search inside array elements properly
     * across all MySQL/MariaDB versions.
     *
     * JSON_SEARCH(receivers, 'one', '1', NULL, '$[*].id') IS NOT NULL
     * correctly walks the array and finds any element where .id === '1'.
     * Note: JSON_SEARCH always compares as strings, so we cast actorId to string.
     *
     * PHP then does the precise id+role confirmation in isReadBy().
     */
    private function whereHasReceiver($q, int $actorId, ?string $role): void
    {
        // SQL pre-filter: find rows where this actor id appears in any receiver
        $q->whereRaw(
            "JSON_SEARCH(receivers, 'one', ?, NULL, '\$[*].id') IS NOT NULL",
            [(string)$actorId]
        );

        // If role is known, also pre-filter by role presence (PHP confirms the pairing)
        if ($role !== null && $role !== '') {
            $q->whereRaw(
                "JSON_SEARCH(receivers, 'one', ?, NULL, '\$[*].role') IS NOT NULL",
                [$role]
            );
        }
    }

    private function hydrateRow(object $row): object
    {
        $row->receivers = $this->decodeReceivers($row->receivers ?? null);
        $row->metadata  = json_decode($row->metadata ?? '{}', true) ?: (object)[];
        return $row;
    }

    private function saveReceivers(int $id, array $receivers): void
    {
        DB::table('notifications')->where('id', $id)->update([
            'receivers'  => json_encode($receivers, JSON_UNESCAPED_UNICODE),
            'updated_at' => now(),
        ]);
    }

    private function paginationMeta(int $total, int $limit, int $page): array
    {
        return [
            'total'        => $total,
            'per_page'     => $limit,
            'current_page' => $page,
            'last_page'    => (int)ceil($total / max(1, $limit)),
        ];
    }

    // ─── GET /api/notifications ───────────────────────────────────────────────
    // ?count_only=1 | ?unread=1 | &role= &type= &priority= &status=
    // &limit= &page= &before_id= &since_id=


public function index(Request $req)
{
    $t0 = microtime(true);

    $actor   = $this->actor($req);
    $actorId = (int)($actor['id'] ?? 0);

    // Resolve role early (before logs)
    $role = $req->filled('role') ? (string)$req->role : ($actor['role'] ?? null);

    Log::debug('notifications.index.start', [
        'actor_id'    => $actorId,
        'role'        => $role,
        'query'       => $req->only(['count_only','unread','type','priority','status','limit','page','before_id','since_id','role']),
        'ip'          => $req->ip(),
        'ua'          => substr((string)$req->userAgent(), 0, 120),
    ]);

    if (!$actorId) {
        Log::warning('notifications.index.unauthorized', [
            'role' => $role,
            'ip'   => $req->ip(),
        ]);
        return response()->json(['message' => 'Unauthorized'], 401);
    }

    try {
        // ── count_only: isolated active-only query, early return ──
        if ($req->boolean('count_only')) {
            $cq = DB::table('notifications')->where('status', 'active');
            $this->whereHasReceiver($cq, $actorId, $role);

            $count = 0;
            $scanned = 0;

            $cq->orderByDesc('id')->cursor()->each(function ($row) use (&$count, &$scanned, $actorId, $role) {
                $scanned++;
                if (!$this->isReadBy($this->decodeReceivers($row->receivers ?? null), $actorId, $role)) {
                    $count++;
                }
            });

            Log::info('notifications.index.count_only.done', [
                'actor_id'  => $actorId,
                'role'      => $role,
                'scanned'   => $scanned,
                'unread'    => $count,
                'ms'        => (int) round((microtime(true) - $t0) * 1000),
            ]);

            return response()->json(['unread' => $count]);
        }

        // ── listing query ──
        $q = DB::table('notifications')->whereIn('status', ['active', 'archived']);
        $this->whereHasReceiver($q, $actorId, $role);

        if ($req->filled('type'))     $q->where('type',     (string)$req->type);
        if ($req->filled('priority')) $q->where('priority', (string)$req->priority);
        if ($req->filled('status'))   $q->where('status',   (string)$req->status);

        $beforeId = (int)$req->get('before_id', 0);
        $sinceId  = (int)$req->get('since_id',  0);
        if ($beforeId > 0) $q->where('id', '<', $beforeId);
        if ($sinceId  > 0) $q->where('id', '>', $sinceId);

        $q->orderByDesc('id');

        $limit = min(100, max(10, (int)$req->get('limit', 20)));
        $page  = max(1,           (int)$req->get('page',  1));

        Log::debug('notifications.index.query.built', [
            'actor_id'   => $actorId,
            'role'       => $role,
            'filters'    => [
                'type'     => $req->filled('type') ? (string)$req->type : null,
                'priority' => $req->filled('priority') ? (string)$req->priority : null,
                'status'   => $req->filled('status') ? (string)$req->status : null,
                'before_id'=> $beforeId ?: null,
                'since_id' => $sinceId ?: null,
                'unread'   => $req->boolean('unread'),
            ],
            'limit'      => $limit,
            'page'       => $page,
        ]);

        // ── unread only: SQL pre-filters, PHP confirms ──
        if ($req->boolean('unread')) {
            $window = min(1000, max($limit * 5, 200));
            $items  = [];
            $checked = 0;

            foreach ($q->limit($window)->get() as $row) {
                $checked++;
                $row = $this->hydrateRow($row);
                if (!$this->isReadBy($row->receivers, $actorId, $role)) {
                    $items[] = $row;
                }
            }

            $total = count($items);
            $slice = array_slice($items, ($page - 1) * $limit, $limit);

            Log::info('notifications.index.unread.done', [
                'actor_id'   => $actorId,
                'role'       => $role,
                'window'     => $window,
                'checked'    => $checked,
                'unread_total_in_window' => $total,
                'returned'   => count($slice),
                'limit'      => $limit,
                'page'       => $page,
                'ms'         => (int) round((microtime(true) - $t0) * 1000),
            ]);

            return response()->json([
                'data'       => array_values($slice),
                'pagination' => $this->paginationMeta($total, $limit, $page),
            ]);
        }

        // ── normal paginated listing ──
        $p = $q->paginate($limit, ['*'], 'page', $page);
        $p->getCollection()->transform(fn($row) => $this->hydrateRow($row));

        Log::info('notifications.index.list.done', [
            'actor_id'   => $actorId,
            'role'       => $role,
            'returned'   => count($p->items()),
            'total'      => $p->total(),
            'per_page'   => $p->perPage(),
            'page'       => $p->currentPage(),
            'last_page'  => $p->lastPage(),
            'ms'         => (int) round((microtime(true) - $t0) * 1000),
        ]);

        return response()->json([
            'data'       => $p->items(),
            'pagination' => [
                'total'        => $p->total(),
                'per_page'     => $p->perPage(),
                'current_page' => $p->currentPage(),
                'last_page'    => $p->lastPage(),
            ],
        ]);

    } catch (\Throwable $e) {
        Log::error('notifications.index.exception', [
            'actor_id' => $actorId,
            'role'     => $role,
            'message'  => $e->getMessage(),
            'file'     => $e->getFile(),
            'line'     => $e->getLine(),
            'ms'       => (int) round((microtime(true) - $t0) * 1000),
        ]);

        return response()->json([
            'message' => 'Server error',
        ], 500);
    }
}
    // ─── POST /api/notifications ──────────────────────────────────────────────

    public function store(Request $req)
    {
        $data = $req->validate([
            'title'            => ['required', 'string', 'max:255'],
            'message'          => ['required', 'string'],
            'receivers'        => ['required', 'array', 'min:1'],
            'receivers.*.id'   => ['required', 'integer', 'min:1'],
            'receivers.*.role' => ['required', 'string', 'max:64'],
            'metadata'         => ['sometimes', 'array'],
            'type'             => ['sometimes', 'string', 'max:50'],
            'link_url'         => ['sometimes', 'nullable', 'string', 'max:1024'],
            'priority'         => ['sometimes', Rule::in(['low', 'normal', 'high', 'urgent'])],
            'status'           => ['sometimes', Rule::in(['active', 'archived', 'deleted'])],
        ]);

        $receivers = array_map(fn($r) => [
            'id'      => (int)$r['id'],
            'role'    => (string)$r['role'],
            'read'    => 0,
            'read_at' => null,
        ], $data['receivers']);

        $now = now();
        $id  = DB::table('notifications')->insertGetId([
            'title'      => $data['title'],
            'message'    => $data['message'],
            'receivers'  => json_encode($receivers, JSON_UNESCAPED_UNICODE),
            'metadata'   => isset($data['metadata']) ? json_encode($data['metadata'], JSON_UNESCAPED_UNICODE) : null,
            'type'       => $data['type']     ?? 'general',
            'link_url'   => $data['link_url'] ?? null,
            'priority'   => $data['priority'] ?? 'normal',
            'status'     => $data['status']   ?? 'active',
            'created_at' => $now,
            'updated_at' => $now,
        ]);

        return response()->json(
            $this->hydrateRow(DB::table('notifications')->where('id', $id)->first()),
            201
        );
    }

    // ─── PATCH /api/notifications/{id?} ──────────────────────────────────────
    // body: { action: "read"|"read_all"|"archive"|"delete", read?: bool, role?: string, ids?: int[] }

    public function update(Request $req, $id = null)     {
            Log::debug('update.hit', ['id' => $id, 'action' => $req->get('action'), 'body' => $req->all()]);

        $actor   = $this->actor($req);
        $actorId = (int)($actor['id'] ?? 0);
        if (!$actorId) return response()->json(['message' => 'Unauthorized'], 401);

        $action = (string)$req->get('action', 'read');
        $role   = $req->filled('role') ? (string)$req->role : ($actor['role'] ?? null);

        // ── archive / delete ──
        if (in_array($action, ['archive', 'delete'], true)) {
            $newStatus = $action === 'archive' ? 'archived' : 'deleted';
            $affected  = DB::table('notifications')
                ->where('id', (int)$id)
                ->update(['status' => $newStatus, 'updated_at' => now()]);
            if (!$affected) return response()->json(['message' => 'Not found'], 404);
            return response()->json(['ok' => true]);
        }

        // ── read_all ──
       // ── read_all: chunked batch to avoid per-row queries & timeout ──
if ($action === 'read_all') {
    $q = DB::table('notifications')
        ->where('status', 'active')
        ->select(['id', 'receivers']);
    $this->whereHasReceiver($q, $actorId, $role);

    $updated = 0;
    $readAt  = now()->toIso8601String();
    $now     = now();

    $q->orderByDesc('id')->chunk(200, function ($rows) use (&$updated, $actorId, $role, $readAt, $now) {
        $toUpdate = [];

        foreach ($rows as $row) {
            $receivers = $this->decodeReceivers($row->receivers ?? null);
            $changed   = false;

            foreach ($receivers as &$rec) {
                if ((int)$rec['id'] === $actorId
                    && ($role === null || $rec['role'] === $role)
                    && (int)$rec['read'] === 0
                ) {
                    $rec['read']    = 1;
                    $rec['read_at'] = $readAt;
                    $changed        = true;
                    $updated++;
                }
            }
            unset($rec);

            if ($changed) {
                $toUpdate[$row->id] = json_encode($receivers, JSON_UNESCAPED_UNICODE);
            }
        }

        if (empty($toUpdate)) return;

        // Build one CASE WHEN query for the whole chunk instead of N updates
        $ids      = array_keys($toUpdate);
        $bindings = array_values($toUpdate);
        $whens    = implode(' ', array_map(fn($id) => "WHEN {$id} THEN ?", $ids));

        DB::statement(
            "UPDATE notifications SET receivers = CASE id {$whens} END, updated_at = ? WHERE id IN ("
            . implode(',', $ids) . ")",
            [...$bindings, $now]
        );
    });

    return response()->json(['ok' => true, 'updated' => $updated]);
}
        // ── read (single or many) ──
        $read = $req->boolean('read', true);
        $ids  = $req->has('ids') ? array_map('intval', (array)$req->ids) : [(int)$id];
        $ids  = array_values(array_filter($ids));

        if (empty($ids)) return response()->json(['message' => 'No ids provided'], 422);

        $updated = 0;
        DB::table('notifications')->whereIn('id', $ids)->cursor()->each(
            function ($row) use (&$updated, $actorId, $role, $read) {
                $receivers = $this->decodeReceivers($row->receivers ?? null);
                $changed   = false;
                foreach ($receivers as &$rec) {
                    if ((int)$rec['id'] === $actorId && ($role === null || $rec['role'] === $role)) {
                        $newVal = $read ? 1 : 0;
                        if ((int)$rec['read'] !== $newVal) {
                            $rec['read']    = $newVal;
                            $rec['read_at'] = $read ? now()->toIso8601String() : null;
                            $changed = true;
                            $updated++;
                        }
                    }
                }
                unset($rec);
                if ($changed) $this->saveReceivers($row->id, $receivers);
            }
        );

        if (count($ids) === 1) {
            $out = DB::table('notifications')->where('id', $ids[0])->first();
            return response()->json([
                'ok'           => true,
                'updated'      => $updated,
                'notification' => $out ? $this->hydrateRow($out) : null,
            ]);
        }

        return response()->json(['ok' => true, 'updated' => $updated]);
    }
}