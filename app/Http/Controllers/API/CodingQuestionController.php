<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class CodingQuestionController extends Controller
{
    /* =========================================================
     * Helpers
     * ========================================================= */

    /** Find one question by ID (optionally including soft-deleted) */
    private function findById(int $id, bool $withTrashed = false)
    {
        Log::info('[Questions/findById] Start', ['id' => $id, 'withTrashed' => $withTrashed]);
        $q = DB::table('coding_questions');
        if (!$withTrashed) $q->whereNull('deleted_at');
        $row = $q->where('id', $id)->first();
        Log::info('[Questions/findById] Done', ['found' => (bool)$row]);
        Log::debug('[Questions/findById] Row', ['row' => $row]);
        return $row;
    }

    /** Generate unique slug within a module (optionally ignoring a question id) */
    private function uniqueSlug(string $title, int $moduleId, ?int $ignoreId = null): string
    {
        Log::info('[Questions/uniqueSlug] Start', ['title' => $title, 'module_id' => $moduleId, 'ignore_id' => $ignoreId]);
        $base = Str::slug($title);
        $slug = $base;
        $i    = 1;

        $query = fn($s) => DB::table('coding_questions')
            ->when($ignoreId, fn($q) => $q->where('id', '!=', $ignoreId))
            ->where('module_id', $moduleId)
            ->where('slug', $s)
            ->exists();

        while ($query($slug)) {
            $slug = $base . '-' . $i++;
        }

        Log::info('[Questions/uniqueSlug] Result', ['slug' => $slug]);
        return $slug;
    }

    /** Return a question with children aggregated: languages, snippets, tests */
    private function hydrateQuestion(int $id): ?array
    {
        Log::info('[Questions/hydrateQuestion] Start', ['id' => $id]);
        $q = $this->findById($id, true);
        if (!$q) {
            Log::warning('[Questions/hydrateQuestion] Not found', ['id' => $id]);
            return null;
        }

        $langs = DB::table('question_languages')
            ->where('question_id', $id)
            ->orderBy('sort_order')->get()->map(fn($r) => (array)$r)->values()->all();

        $snips = DB::table('question_snippets')
            ->where('question_id', $id)
            ->orderBy('sort_order')->get()->map(fn($r) => (array)$r)->values()->all();

        $tests = DB::table('question_tests')
            ->where('question_id', $id)
            ->orderBy('sort_order')->orderBy('id')->get()->map(fn($r) => (array)$r)->values()->all();

        $out = (array)$q;
        $out['languages'] = $langs;
        $out['snippets']  = $snips;
        $out['tests']     = $tests;

        Log::info('[Questions/hydrateQuestion] Done');
        Log::debug('[Questions/hydrateQuestion] Payload', [
            'question'  => $out,
            'langs_cnt' => count($langs),
            'snips_cnt' => count($snips),
            'tests_cnt' => count($tests),
        ]);

        return $out;
    }

    /** Upsert per-language rows; optionally prune rows not present in payload */
    /** Upsert per-language rows; optionally prune rows not present in payload */
   private function upsertLanguages(int $questionId, array $items, bool $pruneMissing = false): void
{
    Log::info('[Questions/upsertLanguages] Start', [
        'question_id' => $questionId,
        'count'       => count($items),
        'prune'       => $pruneMissing,
    ]);
    Log::debug('[Questions/upsertLanguages] Items', ['items' => $items]);

    // Validate uniqueness of language_key in payload
    $seen = [];
    foreach ($items as $i => $it) {
        if (!isset($it['language_key'])) {
            Log::error('[Questions/upsertLanguages] Missing language_key', ['index' => $i]);
            throw new \InvalidArgumentException("languages[$i].language_key is required");
        }
        $k = $it['language_key'];
        if (isset($seen[$k])) {
            Log::error('[Questions/upsertLanguages] Duplicate language_key', ['language_key' => $k]);
            throw new \InvalidArgumentException("Duplicate language_key '$k' in languages");
        }
        $seen[$k] = true;
    }

    // Fetch existing for prune decisions
    $existing = DB::table('question_languages')
        ->where('question_id', $questionId)
        ->pluck('id', 'language_key')->all();

    // Helper: normalize list fields (array | JSON string | scalar) -> JSON string or null
    $normalizeList = function ($v) {
        if (is_null($v) || $v === '') return null;

        if (is_array($v)) {
            return json_encode(array_values(array_filter($v, fn($x) => $x !== null && $x !== '')), JSON_UNESCAPED_UNICODE);
        }

        // If string, try JSON-decode; if it decodes to an array, re-encode normalized
        if (is_string($v)) {
            $trim = trim($v);
            if ($trim === '') return null;

            // if it's already JSON array, keep it normalized
            $decoded = json_decode($trim, true);
            if (json_last_error() === JSON_ERROR_NONE && is_array($decoded)) {
                return json_encode(array_values(array_filter($decoded, fn($x) => $x !== null && $x !== '')), JSON_UNESCAPED_UNICODE);
            }

            // fallback: treat as single token string => wrap into array
            return json_encode([$trim], JSON_UNESCAPED_UNICODE);
        }

        // Anything else -> cast to string and wrap
        return json_encode([strval($v)], JSON_UNESCAPED_UNICODE);
    };

    $now = now();

    foreach ($items as $it) {
        $payload = [
            'question_id'       => $questionId,
            'language_key'      => $it['language_key'],
            'runtime_key'       => $it['runtime_key']       ?? null,
            'source_filename'   => $it['source_filename']   ?? null,
            'compile_cmd'       => $it['compile_cmd']       ?? null,
            'run_cmd'           => $it['run_cmd']           ?? null,
            'time_limit_ms'     => $it['time_limit_ms']     ?? null,
            'memory_limit_kb'   => $it['memory_limit_kb']   ?? null,
            'stdout_kb_max'     => $it['stdout_kb_max']     ?? null,
            'line_limit'        => $it['line_limit']        ?? null,
            'byte_limit'        => $it['byte_limit']        ?? null,
            'max_inputs'        => $it['max_inputs']        ?? null,
            'max_stdin_tokens'  => $it['max_stdin_tokens']  ?? null,
            'max_args'          => $it['max_args']          ?? null,
            'allow_label'       => $it['allow_label']       ?? null,
            'allow'             => $normalizeList($it['allow']        ?? null),
            'forbid_regex'      => $normalizeList($it['forbid_regex'] ?? null),
            'is_enabled'        => isset($it['is_enabled']) ? (bool)$it['is_enabled'] : true,
            'sort_order'        => $it['sort_order']        ?? 0,
            'updated_at'        => $now,
        ];

        if (isset($existing[$it['language_key']])) {
            // UPDATE existing row (do not touch uuid)
            DB::table('question_languages')
                ->where('id', $existing[$it['language_key']])
                ->update($payload);
            Log::info('[Questions/upsertLanguages] Updated', ['language_key' => $it['language_key']]);
        } else {
            // INSERT new row => must provide uuid
            $insertPayload = $payload;
            $insertPayload['uuid']       = $it['uuid'] ?? (string) Str::uuid();
            $insertPayload['created_at'] = $now;

            DB::table('question_languages')->insert($insertPayload);
            Log::info('[Questions/upsertLanguages] Inserted', ['language_key' => $it['language_key']]);
            Log::debug('[Questions/upsertLanguages] PayloadApplied', ['payload' => $insertPayload]);
            continue;
        }

        Log::debug('[Questions/upsertLanguages] PayloadApplied', ['payload' => $payload]);
    }

    if ($pruneMissing) {
        $keep = array_column($items, 'language_key');
        $deleted = DB::table('question_languages')
            ->where('question_id', $questionId)
            ->whereNotIn('language_key', $keep)
            ->delete();
        Log::info('[Questions/upsertLanguages] Pruned', ['deleted' => $deleted]);
    }

    Log::info('[Questions/upsertLanguages] Done');
}

    /** Upsert snippets by language_key; optionally prune */
   /** Upsert snippets by language_key; optionally prune */
private function upsertSnippets(int $questionId, array $items, bool $pruneMissing = false): void
{
    Log::info('[Questions/upsertSnippets] Start', [
        'question_id' => $questionId,
        'count'       => count($items),
        'prune'       => $pruneMissing,
    ]);
    Log::debug('[Questions/upsertSnippets] Items', ['items' => $items]);

    $existing = DB::table('question_snippets')
        ->where('question_id', $questionId)
        ->pluck('id', 'language_key')->all();

    $seen = [];
    $now  = now();

    foreach ($items as $i => $it) {
        if (!isset($it['language_key']) || !isset($it['template'])) {
            Log::error('[Questions/upsertSnippets] Invalid item', ['index' => $i, 'item' => $it]);
            throw new \InvalidArgumentException("snippets[$i] requires language_key and template");
        }
        $k = $it['language_key'];
        if (isset($seen[$k])) {
            Log::error('[Questions/upsertSnippets] Duplicate language_key', ['language_key' => $k]);
            throw new \InvalidArgumentException("Duplicate language_key '$k' in snippets");
        }
        $seen[$k] = true;

        $payload = [
            'question_id'  => $questionId,
            'language_key' => $k,
            'entry_hint'   => $it['entry_hint'] ?? null,
            'template'     => $it['template'],
            'is_default'   => isset($it['is_default']) ? (bool)$it['is_default'] : false,
            'sort_order'   => $it['sort_order'] ?? 0,
            'updated_at'   => $now,
        ];

        if (isset($existing[$k])) {
            // UPDATE existing (keep uuid as-is)
            DB::table('question_snippets')->where('id', $existing[$k])->update($payload);
            Log::info('[Questions/upsertSnippets] Updated', ['language_key' => $k]);
        } else {
            // INSERT new => must set uuid
            $insertPayload = $payload;
            $insertPayload['uuid']       = $it['uuid'] ?? (string) Str::uuid();
            $insertPayload['created_at'] = $now;

            DB::table('question_snippets')->insert($insertPayload);
            Log::info('[Questions/upsertSnippets] Inserted', ['language_key' => $k]);
            Log::debug('[Questions/upsertSnippets] PayloadApplied', ['payload' => $insertPayload]);
            continue;
        }

        Log::debug('[Questions/upsertSnippets] PayloadApplied', ['payload' => $payload]);
    }

    if ($pruneMissing) {
        $keep = array_column($items, 'language_key');
        $deleted = DB::table('question_snippets')
            ->where('question_id', $questionId)
            ->whereNotIn('language_key', $keep)
            ->delete();
        Log::info('[Questions/upsertSnippets] Pruned', ['deleted' => $deleted]);
    }

    Log::info('[Questions/upsertSnippets] Done');
}

    /** Replace (or upsert) tests; optionally prune others. Supports id-based updates too. */
    private function upsertTests(int $questionId, array $items, bool $pruneMissing = false): void
    {
        Log::info('[Questions/upsertTests] Start', [
            'question_id' => $questionId,
            'count'       => count($items),
            'prune'       => $pruneMissing,
        ]);
        Log::debug('[Questions/upsertTests] Items', ['items' => $items]);

        // Existing map by id for updates (kept mainly for reference/logs)
        $existingIds = DB::table('question_tests')
            ->where('question_id', $questionId)
            ->pluck('id')->all();
        Log::debug('[Questions/upsertTests] Existing IDs', ['ids' => $existingIds]);

        $keptIds = [];

        foreach ($items as $i => $it) {
            // Accept both create (no id) and update (with id)
            $payload = [
                'question_id'                  => $questionId,
                'visibility'                   => $it['visibility'] ?? 'hidden',
                'input'                        => $it['input']      ?? null,
                'expected'                     => $it['expected']   ?? null,
                'score'                        => $it['score']      ?? 1,
                'is_active'                    => isset($it['is_active']) ? (bool)$it['is_active'] : true,
                'sort_order'                   => $it['sort_order'] ?? 0,
                'time_limit_ms_override'       => $it['time_limit_ms_override']     ?? null,
                'memory_limit_kb_override'     => $it['memory_limit_kb_override']   ?? null,
                'updated_at'                   => now(),
            ];

            if (!empty($it['id'])) {
                DB::table('question_tests')
                    ->where('id', (int)$it['id'])
                    ->where('question_id', $questionId)
                    ->update($payload);
                $keptIds[] = (int)$it['id'];
                Log::info('[Questions/upsertTests] Updated', ['id' => (int)$it['id']]);
            } else {
                $payload['created_at'] = now();
                $newId = DB::table('question_tests')->insertGetId($payload);
                $keptIds[] = $newId;
                Log::info('[Questions/upsertTests] Inserted', ['id' => $newId]);
            }
            Log::debug('[Questions/upsertTests] PayloadApplied', ['payload' => $payload]);
        }

        if ($pruneMissing) {
            if (!empty($keptIds)) {
                $deleted = DB::table('question_tests')
                    ->where('question_id', $questionId)
                    ->whereNotIn('id', $keptIds)
                    ->delete();
                Log::info('[Questions/upsertTests] Pruned', ['deleted' => $deleted, 'kept' => $keptIds]);
            } else {
                $deletedAll = DB::table('question_tests')->where('question_id', $questionId)->delete();
                Log::info('[Questions/upsertTests] PrunedAll', ['deleted' => $deletedAll]);
            }
        }

        Log::info('[Questions/upsertTests] Done');
    }

    /* =========================================================
     * Endpoints
     * ========================================================= */

    /** List with filters & pagination */
    public function index(Request $r)
    {
        Log::info('[Questions/index] Start', [
            'query' => $r->all(),
            'ip'    => $r->ip(),
        ]);

        try {
            $perPage = (int)$r->input('per_page', 20);
            $q = DB::table('coding_questions')->whereNull('deleted_at');

            if ($r->filled('topic_id'))   { $q->where('topic_id',  (int)$r->input('topic_id')); }
            if ($r->filled('module_id'))  { $q->where('module_id', (int)$r->input('module_id')); }
            if ($r->filled('status'))     { $q->where('status', $r->input('status')); }
            if ($r->filled('difficulty')) { $q->where('difficulty', $r->input('difficulty')); }
            if ($r->boolean('only_trashed')) {
                $q = DB::table('coding_questions')->whereNotNull('deleted_at');
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

            Log::info('[Questions/index] Success', [
                'total'     => $paginator->total(),
                'per_page'  => $paginator->perPage(),
                'page'      => $paginator->currentPage(),
            ]);
            Log::debug('[Questions/index] PageData', ['data' => $paginator]);

            return response()->json([
                'status' => 'success',
                'data'   => $paginator,
            ], 200);
        } catch (\Throwable $e) {
            Log::error('[Questions/index] Failed', ['err' => $e->getMessage()]);
            return response()->json(['status'=>'error','message'=>'Failed to fetch questions.'], 500);
        }
    }

    /** Create question (+ optional nested children) */
    public function store(Request $r)
    {
        Log::info('[Questions/store] Start', ['ip' => $r->ip()]);
        Log::debug('[Questions/store] Incoming', ['payload' => $r->all()]);

        // Base validation for question
        $rules = [
            'topic_id'     => 'required|integer|exists:topics,id',
            'module_id'    => 'required|integer|exists:coding_modules,id',
            'title'        => 'required|string|min:2|max:200',
            'slug'         => 'nullable|string|min:2|max:200', // auto if empty
            'difficulty'   => 'nullable|in:easy,medium,hard',
            'status'       => 'nullable|in:active,draft,archived',
            'sort_order'   => 'nullable|integer|min:0',
            'description'  => 'nullable|string',

            'compare_mode'     => 'nullable|in:exact,icase,float_abs,float_rel,token',
            'trim_output'      => 'nullable|boolean',
            'whitespace_mode'  => 'nullable|in:none,trim,squash',
            'float_abs_tol'    => 'nullable|numeric',
            'float_rel_tol'    => 'nullable|numeric',

            // Nested payloads
            'languages'                         => 'nullable|array',
            'languages.*.language_key'          => 'required_with:languages|string|max:50',
            'languages.*.runtime_key'           => 'nullable|string|max:80',
            'languages.*.source_filename'       => 'nullable|string|max:120',
            'languages.*.compile_cmd'           => 'nullable|string|max:255',
            'languages.*.run_cmd'               => 'nullable|string|max:255',
            'languages.*.time_limit_ms'         => 'nullable|integer|min:1',
            'languages.*.memory_limit_kb'       => 'nullable|integer|min:1',
            'languages.*.stdout_kb_max'         => 'nullable|integer|min:1',
            'languages.*.line_limit'            => 'nullable|integer|min:1',
            'languages.*.byte_limit'            => 'nullable|integer|min:1',
            'languages.*.max_inputs'            => 'nullable|integer|min:1',
            'languages.*.max_stdin_tokens'      => 'nullable|integer|min:1',
            'languages.*.max_args'              => 'nullable|integer|min:1',
            'languages.*.allow_label'           => 'nullable|string|max:50',
            'languages.*.allow'                 => 'nullable',
            'languages.*.forbid_regex'          => 'nullable',
            'languages.*.is_enabled'            => 'nullable|boolean',
            'languages.*.sort_order'            => 'nullable|integer|min:0',

            'snippets'                           => 'nullable|array',
            'snippets.*.language_key'            => 'required_with:snippets|string|max:50',
            'snippets.*.entry_hint'              => 'nullable|string|max:200',
            'snippets.*.template'                => 'required_with:snippets|string',
            'snippets.*.is_default'              => 'nullable|boolean',
            'snippets.*.sort_order'              => 'nullable|integer|min:0',

            'tests'                               => 'nullable|array',
            'tests.*.visibility'                  => 'nullable|in:sample,hidden',
            'tests.*.input'                       => 'nullable|string',
            'tests.*.expected'                    => 'nullable|string',
            'tests.*.score'                       => 'nullable|integer',
            'tests.*.is_active'                   => 'nullable|boolean',
            'tests.*.sort_order'                  => 'nullable|integer|min:0',
            'tests.*.time_limit_ms_override'      => 'nullable|integer|min:1',
            'tests.*.memory_limit_kb_override'    => 'nullable|integer|min:1',

            'prune_missing_children'              => 'nullable|boolean',
        ];

        $v = Validator::make($r->all(), $rules);
        if ($v->fails()) {
            Log::warning('[Questions/store] Validation failed', ['errors' => $v->errors()]);
            return response()->json(['status'=>'error','message'=>$v->errors()->first(),'errors'=>$v->errors()], 422);
        }

        try {
            $data  = $v->validated();
            Log::debug('[Questions/store] Validated', ['data' => $data]);

            $slug  = $data['slug'] ?? $this->uniqueSlug($data['title'], (int)$data['module_id']);
            $now   = now();

            DB::beginTransaction();
            Log::info('[Questions/store] Transaction begin');

            // Insert question
            $qid = DB::table('coding_questions')->insertGetId([
                'topic_id'        => (int)$data['topic_id'],
                'module_id'       => (int)$data['module_id'],
                'uuid'            => (string) Str::uuid(),
                'title'           => $data['title'],
                'slug'            => $slug,
                'difficulty'      => $data['difficulty']  ?? 'medium',
                'status'          => $data['status']      ?? 'active',
                'sort_order'      => $data['sort_order']  ?? 0,
                'description'     => $data['description'] ?? null,
                'compare_mode'    => $data['compare_mode']    ?? 'exact',
                'trim_output'     => isset($data['trim_output']) ? (bool)$data['trim_output'] : true,
                'whitespace_mode' => $data['whitespace_mode'] ?? 'trim',
                'float_abs_tol'   => $data['float_abs_tol']   ?? null,
                'float_rel_tol'   => $data['float_rel_tol']   ?? null,
                'created_at'      => $now,
                'updated_at'      => $now,
            ]);
            Log::info('[Questions/store] Question inserted', ['id' => $qid]);

            $prune = (bool)($data['prune_missing_children'] ?? false);

            if (!empty($data['languages'])) { $this->upsertLanguages($qid, $data['languages'], $prune); }
            if (!empty($data['snippets']))  { $this->upsertSnippets($qid,  $data['snippets'],  $prune); }
            if (!empty($data['tests']))     { $this->upsertTests($qid,     $data['tests'],     $prune); }

            DB::commit();
            Log::info('[Questions/store] Transaction commit', ['id' => $qid]);

            $full = $this->hydrateQuestion($qid);
            Log::info('[Questions/store] Success', ['id' => $qid]);

            return response()->json(['status'=>'success','message'=>'Question created successfully.','data'=>$full], 201);

        } catch (\Throwable $e) {
            DB::rollBack();
            Log::error('[Questions/store] Failed, rollback', ['err' => $e->getMessage()]);
            return response()->json(['status'=>'error','message'=>'Failed to create question.'], 500);
        }
    }

    /** Show (by id or slug; includes trashed) */
    public function show($idOrSlug)
    {
        Log::info('[Questions/show] Start', ['idOrSlug' => $idOrSlug]);
        try {
            if (is_numeric($idOrSlug)) {
                $row = DB::table('coding_questions')->where('id', (int)$idOrSlug)->first();
            } else {
                $row = DB::table('coding_questions')->where('slug', $idOrSlug)->first();
            }

            if (!$row) {
                Log::warning('[Questions/show] Not found', ['idOrSlug' => $idOrSlug]);
                return response()->json(['status'=>'error','message'=>'Question not found.'], 404);
            }

            $full = $this->hydrateQuestion((int)$row->id);
            Log::info('[Questions/show] Success', ['id' => (int)$row->id]);

            return response()->json(['status'=>'success','data'=>$full], 200);
        } catch (\Throwable $e) {
            Log::error('[Questions/show] Failed', ['err' => $e->getMessage()]);
            return response()->json(['status'=>'error','message'=>'Failed to fetch question.'], 500);
        }
    }

    /** Update question (+ optional nested children) */
    public function update(Request $r, $id)
    {
        Log::info('[Questions/update] Start', ['id' => (int)$id, 'ip' => $r->ip()]);
        Log::debug('[Questions/update] Incoming', ['payload' => $r->all()]);

        $rules = [
            'topic_id'     => 'nullable|integer|exists:topics,id',
            'module_id'    => 'nullable|integer|exists:coding_modules,id',
            'title'        => 'nullable|string|min:2|max:200',
            'slug'         => 'nullable|string|min:2|max:200',
            'difficulty'   => 'nullable|in:easy,medium,hard',
            'status'       => 'nullable|in:active,draft,archived',
            'sort_order'   => 'nullable|integer|min:0',
            'description'  => 'nullable|string',

            'compare_mode'     => 'nullable|in:exact,icase,float_abs,float_rel,token',
            'trim_output'      => 'nullable|boolean',
            'whitespace_mode'  => 'nullable|in:none,trim,squash',
            'float_abs_tol'    => 'nullable|numeric',
            'float_rel_tol'    => 'nullable|numeric',

            'languages'                         => 'nullable|array',
            'languages.*.language_key'          => 'required_with:languages|string|max:50',
            'languages.*.runtime_key'           => 'nullable|string|max:80',
            'languages.*.source_filename'       => 'nullable|string|max:120',
            'languages.*.compile_cmd'           => 'nullable|string|max:255',
            'languages.*.run_cmd'               => 'nullable|string|max:255',
            'languages.*.time_limit_ms'         => 'nullable|integer|min:1',
            'languages.*.memory_limit_kb'       => 'nullable|integer|min:1',
            'languages.*.stdout_kb_max'         => 'nullable|integer|min:1',
            'languages.*.line_limit'            => 'nullable|integer|min:1',
            'languages.*.byte_limit'            => 'nullable|integer|min:1',
            'languages.*.max_inputs'            => 'nullable|integer|min:1',
            'languages.*.max_stdin_tokens'      => 'nullable|integer|min:1',
            'languages.*.max_args'              => 'nullable|integer|min:1',
            'languages.*.allow_label'           => 'nullable|string|max:50',
            'languages.*.allow'                 => 'nullable',
            'languages.*.forbid_regex'          => 'nullable',
            'languages.*.is_enabled'            => 'nullable|boolean',
            'languages.*.sort_order'            => 'nullable|integer|min:0',

            'snippets'                           => 'nullable|array',
            'snippets.*.language_key'            => 'required_with:snippets|string|max:50',
            'snippets.*.entry_hint'              => 'nullable|string|max:200',
            'snippets.*.template'                => 'required_with:snippets|string',
            'snippets.*.is_default'              => 'nullable|boolean',
            'snippets.*.sort_order'              => 'nullable|integer|min:0',

            'tests'                               => 'nullable|array',
            'tests.*.id'                          => 'nullable|integer|exists:question_tests,id',
            'tests.*.visibility'                  => 'nullable|in:sample,hidden',
            'tests.*.input'                       => 'nullable|string',
            'tests.*.expected'                    => 'nullable|string',
            'tests.*.score'                       => 'nullable|integer',
            'tests.*.is_active'                   => 'nullable|boolean',
            'tests.*.sort_order'                  => 'nullable|integer|min:0',
            'tests.*.time_limit_ms_override'      => 'nullable|integer|min:1',
            'tests.*.memory_limit_kb_override'    => 'nullable|integer|min:1',

            'prune_missing_children'              => 'nullable|boolean',
        ];

        $v = Validator::make($r->all(), $rules);
        if ($v->fails()) {
            Log::warning('[Questions/update] Validation failed', ['id' => (int)$id, 'errors' => $v->errors()]);
            return response()->json(['status'=>'error','message'=>$v->errors()->first(),'errors'=>$v->errors()], 422);
        }

        try {
            $row = $this->findById((int)$id, true);
            if (!$row) {
                Log::warning('[Questions/update] Not found', ['id' => (int)$id]);
                return response()->json(['status'=>'error','message'=>'Question not found.'], 404);
            }

            $data    = $v->validated();
            Log::debug('[Questions/update] Validated', ['data' => $data]);

            $payload = [];

            // Title/slug logic (slug unique within module)
            $moduleId = (int)($data['module_id'] ?? $row->module_id);

            if (array_key_exists('title', $data) && $data['title'] !== $row->title) {
                $payload['title'] = $data['title'];
                // If slug not provided, regenerate; if provided, validate uniqueness
                if (isset($data['slug'])) {
                    $payload['slug'] = $data['slug'];
                } else {
                    $payload['slug'] = $this->uniqueSlug($data['title'], $moduleId, (int)$id);
                }
            }
            if (array_key_exists('slug', $data) && (!isset($payload['slug']) || $payload['slug'] !== $data['slug'])) {
                // If slug set explicitly, ensure uniqueness by bumping if necessary
                $payload['slug'] = $this->uniqueSlug($data['slug'], $moduleId, (int)$id);
            }

            // Simple fields
            foreach (['topic_id','module_id','difficulty','status','sort_order','description',
                      'compare_mode','whitespace_mode'] as $f) {
                if (array_key_exists($f, $data)) $payload[$f] = $data[$f];
            }
            if (array_key_exists('trim_output', $data))   $payload['trim_output']   = (bool)$data['trim_output'];
            if (array_key_exists('float_abs_tol', $data)) $payload['float_abs_tol'] = $data['float_abs_tol'];
            if (array_key_exists('float_rel_tol', $data)) $payload['float_rel_tol'] = $data['float_rel_tol'];

            DB::beginTransaction();
            Log::info('[Questions/update] Transaction begin', ['id' => (int)$id]);

            if (!empty($payload)) {
                $payload['updated_at'] = now();
                DB::table('coding_questions')->where('id', (int)$id)->update($payload);
                Log::info('[Questions/update] Question updated', ['id' => (int)$id]);
                Log::debug('[Questions/update] PayloadApplied', ['payload' => $payload]);
            } else {
                Log::info('[Questions/update] No base fields changed', ['id' => (int)$id]);
            }

            $prune = (bool)($data['prune_missing_children'] ?? false);

            if (!empty($data['languages'])) { $this->upsertLanguages((int)$id, $data['languages'], $prune); }
            if (!empty($data['snippets']))  { $this->upsertSnippets((int)$id,  $data['snippets'],  $prune); }
            if (!empty($data['tests']))     { $this->upsertTests((int)$id,     $data['tests'],     $prune); }

            DB::commit();
            Log::info('[Questions/update] Transaction commit', ['id' => (int)$id]);

            $full = $this->hydrateQuestion((int)$id);
            Log::info('[Questions/update] Success', ['id' => (int)$id]);

            return response()->json(['status'=>'success','message'=>'Question updated successfully.','data'=>$full], 200);

        } catch (\Throwable $e) {
            DB::rollBack();
            Log::error('[Questions/update] Failed, rollback', ['id' => (int)$id, 'err' => $e->getMessage()]);
            return response()->json(['status'=>'error','message'=>'Failed to update question.'], 500);
        }
    }

    /** Soft delete */
    public function destroy($id)
    {
        Log::info('[Questions/destroy] Start', ['id' => (int)$id]);
        try {
            $row = $this->findById((int)$id, true);
            if (!$row) {
                Log::warning('[Questions/destroy] Not found', ['id' => (int)$id]);
                return response()->json(['status'=>'error','message'=>'Question not found.'], 404);
            }

            DB::table('coding_questions')->where('id', (int)$id)->update([
                'deleted_at' => now(),
                'updated_at' => now(),
            ]);

            Log::info('[Questions/destroy] Soft deleted', ['id' => (int)$id]);

            return response()->json(['status'=>'success','message'=>'Question deleted.'], 200);
        } catch (\Throwable $e) {
            Log::error('[Questions/destroy] Failed', ['id' => (int)$id, 'err' => $e->getMessage()]);
            return response()->json(['status'=>'error','message'=>'Failed to delete question.'], 500);
        }
    }

    /** Restore from trash */
    public function restore($id)
    {
        Log::info('[Questions/restore] Start', ['id' => (int)$id]);
        try {
            $row = DB::table('coding_questions')->where('id', (int)$id)->whereNotNull('deleted_at')->first();
            if (!$row) {
                Log::warning('[Questions/restore] Not found or not trashed', ['id' => (int)$id]);
                return response()->json(['status'=>'error','message'=>'Question not found or not trashed.'], 404);
            }

            DB::table('coding_questions')->where('id', (int)$id)->update([
                'deleted_at' => null,
                'updated_at' => now(),
            ]);

            $full = $this->hydrateQuestion((int)$id);
            Log::info('[Questions/restore] Restored', ['id' => (int)$id]);

            return response()->json(['status'=>'success','message'=>'Question restored.','data'=>$full], 200);
        } catch (\Throwable $e) {
            Log::error('[Questions/restore] Failed', ['id' => (int)$id, 'err' => $e->getMessage()]);
            return response()->json(['status'=>'error','message'=>'Failed to restore question.'], 500);
        }
    }

    /** Toggle status active/draft (archived stays archived unless explicitly set) */
    public function toggleStatus($id)
    {
        Log::info('[Questions/toggleStatus] Start', ['id' => (int)$id]);
        try {
            $row = $this->findById((int)$id, true);
            if (!$row) {
                Log::warning('[Questions/toggleStatus] Not found', ['id' => (int)$id]);
                return response()->json(['status'=>'error','message'=>'Question not found.'], 404);
            }

            $new = ($row->status === 'active') ? 'draft' : 'active';
            DB::table('coding_questions')->where('id', (int)$id)->update([
                'status'     => $new,
                'updated_at' => now(),
            ]);

            Log::info('[Questions/toggleStatus] Updated', ['id' => (int)$id, 'new_status' => $new]);

            return response()->json(['status'=>'success','message'=>'Status updated.','new_status'=>$new], 200);
        } catch (\Throwable $e) {
            Log::error('[Questions/toggleStatus] Failed', ['id' => (int)$id, 'err' => $e->getMessage()]);
            return response()->json(['status'=>'error','message'=>'Failed to toggle status.'], 500);
        }
    }

    /**
     * Reorder questions by sort_order.
     * Accepts either:
     *  - { "order": [5,2,9] } -> assigns 0..n by given order
     *  - { "items": [{id:5,sort_order:10}, ...] }
     * Optional filter to scope reorder within a module:
     *  - module_id: number
     */
    public function reorder(Request $r)
    {
        Log::info('[Questions/reorder] Start', ['ip' => $r->ip()]);
        Log::debug('[Questions/reorder] Incoming', ['payload' => $r->all()]);

        $v = Validator::make($r->all(), [
            'order'              => 'sometimes|array|min:1',
            'order.*'            => 'integer|exists:coding_questions,id',
            'items'              => 'sometimes|array|min:1',
            'items.*.id'         => 'required|integer|exists:coding_questions,id',
            'items.*.sort_order' => 'required|integer|min:0',
            'module_id'          => 'nullable|integer|exists:coding_modules,id',
        ]);

        if ($v->fails()) {
            Log::warning('[Questions/reorder] Validation failed', ['errors' => $v->errors()]);
            return response()->json(['status'=>'error','message'=>$v->errors()->first(),'errors'=>$v->errors()], 422);
        }

        try {
            $p = $v->validated();
            Log::debug('[Questions/reorder] Validated', ['data' => $p]);

            DB::beginTransaction();
            Log::info('[Questions/reorder] Transaction begin');

            if (!empty($p['order'])) {
                foreach ($p['order'] as $idx => $qid) {
                    // If module_id provided, ensure row belongs before updating
                    if (!empty($p['module_id'])) {
                        $row = DB::table('coding_questions')->where('id', (int)$qid)->first();
                        if (!$row || (int)$row->module_id !== (int)$p['module_id']) {
                            Log::warning('[Questions/reorder] Skip due to module mismatch', ['qid' => $qid, 'module_id' => $p['module_id'] ?? null]);
                            continue;
                        }
                    }
                    DB::table('coding_questions')->where('id', (int)$qid)->update([
                        'sort_order' => (int)$idx,
                        'updated_at' => now(),
                    ]);
                    Log::info('[Questions/reorder] Updated order', ['id' => (int)$qid, 'sort_order' => (int)$idx]);
                }
            } elseif (!empty($p['items'])) {
                foreach ($p['items'] as $it) {
                    if (!empty($p['module_id'])) {
                        $row = DB::table('coding_questions')->where('id', (int)$it['id'])->first();
                        if (!$row || (int)$row->module_id !== (int)$p['module_id']) {
                            Log::warning('[Questions/reorder] Skip due to module mismatch', ['id' => $it['id'], 'module_id' => $p['module_id'] ?? null]);
                            continue;
                        }
                    }
                    DB::table('coding_questions')->where('id', (int)$it['id'])->update([
                        'sort_order' => (int)$it['sort_order'],
                        'updated_at' => now(),
                    ]);
                    Log::info('[Questions/reorder] Updated order', ['id' => (int)$it['id'], 'sort_order' => (int)$it['sort_order']]);
                }
            } else {
                DB::rollBack();
                Log::warning('[Questions/reorder] No reorder data provided');
                return response()->json(['status'=>'error','message'=>'No reorder data provided.'], 422);
            }

            DB::commit();
            Log::info('[Questions/reorder] Transaction commit');

            return response()->json(['status'=>'success','message'=>'Sort order updated.'], 200);

        } catch (\Throwable $e) {
            DB::rollBack();
            Log::error('[Questions/reorder] Failed, rollback', ['err' => $e->getMessage()]);
            return response()->json(['status'=>'error','message'=>'Failed to update sort order.'], 500);
        }
    }
}
