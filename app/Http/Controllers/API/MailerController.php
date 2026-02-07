<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use Throwable;

class MailerController extends Controller
{
    /* =========================================================
     |  Identity / owner helpers
     |=========================================================*/

    /** Get polymorphic owner (FQCN + id) from CheckRole. */
    private function owner(Request $r): array
    {
        $type = (string) $r->attributes->get('auth_tokenable_type'); // e.g. App\Models\User
        $id   = (int)    $r->attributes->get('auth_tokenable_id');

        if (!$type || !$id) {
            abort(response()->json(['status'=>'error','message'=>'Unauthorized'], 403));
        }
        return [$type, $id];
    }

    /** Who is performing the action (for auditing). */
    private function actor(Request $r): array
    {
        $id   = (int) $r->attributes->get('auth_tokenable_id');
        $role = (string) $r->attributes->get('auth_role', '');
        $name = null;

        try {
            $row = DB::table('users')->where('id', $id)->first();
            $name = $row?->name ?? $row?->email ?? "#{$id}";
        } catch (\Throwable $e) {
            $name = "#{$id}";
        }
        return [$id, $name, $role];
    }

    /** Base query scoped to this owner. */
    private function base(string $ownerType, int $ownerId)
    {
        return DB::table('mailer_settings')
            ->where('owner_type', $ownerType)
            ->where('owner_id', $ownerId);
    }

    /* =========================================================
     |  Input normalization / validation
     |=========================================================*/

    /** Normalize aliases and encryption. */
    private function normalize(Request $r): void
    {
        $r->merge([
            'mailer'        => $r->input('mailer', $r->input('driver', 'smtp')),
            'from_address'  => $r->input('from_address', $r->input('fromAddress')),
            'from_name'     => $r->input('from_name',    $r->input('fromName')),
            'reply_to_address' => $r->input('reply_to_address', $r->input('replyToAddress')),
            'reply_to_name'    => $r->input('reply_to_name',    $r->input('replyToName')),
        ]);

        $enc = strtolower((string) $r->input('encryption', ''));
        if ($enc === '' || $enc === 'none') $enc = null;
        if ($enc === 'starttls') $enc = 'tls';

        $r->merge(['encryption' => $enc]);
    }

    /** Validation rules (SMTP requires host/port/username; password required on create). */
    private function validator(Request $r, bool $isUpdate = false)
    {
        $rules = [
            'label'           => 'nullable|string|max:100',
            'mailer'          => 'required|string|in:smtp,sendmail,ses,mailgun,postmark,log,array',
            'host'            => 'nullable|string|max:191',
            'port'            => 'nullable|integer|min:1|max:65535',
            'username'        => 'nullable|string|max:191',
            'password'        => 'nullable|string',
            'encryption'      => 'nullable|in:tls,ssl',
            'timeout'         => 'nullable|integer|min:1|max:3600',
            'from_address'    => 'required|email|max:191',
            'from_name'       => 'required|string|max:191',
            'reply_to_address'=> 'nullable|email|max:191',
            'reply_to_name'   => 'nullable|string|max:191',
            'is_default'      => 'sometimes|boolean',
            'status'          => 'sometimes|in:active,inactive',
        ];

        if (strtolower((string) $r->input('mailer')) === 'smtp') {
            $rules['host']     = 'required|string|max:191';
            $rules['port']     = 'required|integer|min:1|max:65535';
            $rules['username'] = 'required|string|max:191';
            if (!$isUpdate) {
                $rules['password'] = 'required|string';
            }
        }

        return Validator::make($r->all(), $rules);
    }

    /** Build DB payload; encrypt password when present; keep existing when masked/empty on update. */
    private function buildPayload(Request $r, bool $isUpdate = false, ?string $existingEncryptedPwd = null): array
    {
        $payload = [
            'label'            => $r->input('label'),
            'mailer'           => strtolower((string) $r->input('mailer', 'smtp')),
            'host'             => $r->input('host'),
            'port'             => $r->filled('port') ? (int) $r->input('port') : null,
            'username'         => $r->input('username'),
            'encryption'       => $r->input('encryption'), // null|tls|ssl
            'timeout'          => $r->filled('timeout') ? (int) $r->input('timeout') : null,
            'from_address'     => $r->input('from_address'),
            'from_name'        => $r->input('from_name'),
            'reply_to_address' => $r->input('reply_to_address'),
            'reply_to_name'    => $r->input('reply_to_name'),
            'status'           => $r->input('status', 'active'),
        ];

        $pwd = $r->input('password');
        if ($isUpdate) {
            if ($pwd !== null && $pwd !== '' && $pwd !== '******') {
                $payload['password'] = Crypt::encryptString($pwd);
            } elseif ($existingEncryptedPwd !== null) {
                $payload['password'] = $existingEncryptedPwd; // retain
            }
        } else {
            if ($pwd !== null && $pwd !== '') {
                $payload['password'] = Crypt::encryptString($pwd);
            }
        }

        return $payload;
    }

    /** Hide secrets before returning/logging. */
    private function redact(array $row): array
    {
        if (array_key_exists('password', $row)) {
            $row['password'] = '******';
        }
        return $row;
    }

    /* =========================================================
     |  Auditing (lightweight)
     |=========================================================*/

    private function audit(Request $r, string $activity, ?int $recordId, ?array $changed = null, ?array $old = null, ?array $new = null): void
    {
        try {
            [$by, , $role] = $this->actor($r);
            DB::table('user_data_activity_log')->insert([
                'performed_by'      => $by,
                'performed_by_role' => $role,
                'ip'                => $r->ip(),
                'user_agent'        => (string) $r->userAgent(),
                'activity'          => $activity,              // index|show|store|update|default|destroy
                'module'            => 'Mailer',
                'table_name'        => 'mailer_settings',
                'record_id'         => $recordId,
                'changed_fields'    => $changed ? json_encode(array_values(array_unique($changed)), JSON_UNESCAPED_UNICODE) : null,
                'old_values'        => $old ? json_encode($old, JSON_UNESCAPED_UNICODE) : null,
                'new_values'        => $new ? json_encode($new, JSON_UNESCAPED_UNICODE) : null,
                'log_note'          => null,
                'created_at'        => now(),
                'updated_at'        => now(),
            ]);
        } catch (\Throwable $e) {
            Log::warning('Mailer audit write failed', ['err' => $e->getMessage()]);
        }
    }

    /* =========================================================
     |  Endpoints
     |=========================================================*/

    /** GET /api/mailer */
    public function index(Request $request)
    {
        [$ownerType, $ownerId] = $this->owner($request);

        $q          = trim((string) $request->query('q', ''));
        $driver     = strtolower((string) $request->query('driver', ''));
        $encryption = strtolower((string) $request->query('encryption', ''));
        $status     = strtolower((string) $request->query('status', ''));
        $rows       = min(100, max(1, (int) $request->query('rows', 50)));

        $qb = $this->base($ownerType, $ownerId)
            ->select('id','label','mailer','host','port','username','encryption','timeout','from_address','from_name','reply_to_address','reply_to_name','is_default','status','created_at')
            ->orderByDesc('is_default')
            ->orderByDesc('id');

        if ($driver !== '')     $qb->where('mailer', $driver);
        if ($encryption !== '') $encryption === 'none' ? $qb->whereNull('encryption') : $qb->where('encryption', $encryption);
        if ($status !== '')     $qb->where('status', $status);

        if ($q !== '') {
            $like = "%{$q}%";
            $qb->where(function ($w) use ($like) {
                $w->where('label','like',$like)
                  ->orWhere('mailer','like',$like)
                  ->orWhere('host','like',$like)
                  ->orWhere('username','like',$like)
                  ->orWhere('from_address','like',$like)
                  ->orWhere('from_name','like',$like);
            });
        }

        $list = $qb->limit($rows)->get()->map(function ($row) {
            $row->password = '******';
            return $row;
        });

        $this->audit($request, 'index', null);
        return response()->json(['status'=>'success','message'=>'Mailer settings fetched.','data'=>$list]);
    }

    /** POST /api/mailer */
    public function store(Request $request)
    {
        [$ownerType, $ownerId] = $this->owner($request);
        [$by] = $this->actor($request);

        $this->normalize($request);
        $v = $this->validator($request, false);
        if ($v->fails()) {
            $this->audit($request, 'store', null, ['validation_failed'], null, [
                'errors' => $v->errors(),
            ]);
            return response()->json(['status'=>'error','message'=>'Validation failed','errors'=>$v->errors()], 422);
        }

        $payload = $this->buildPayload($request, false) + [
            'owner_type' => $ownerType,
            'owner_id'   => $ownerId,
            'is_default' => 0,
            'created_by' => $by,
            'updated_by' => $by,
            'created_at' => now(),
            'updated_at' => now(),
        ];

        try {
            DB::beginTransaction();

            // If this owner has no mailers yet, make this the default automatically.
            $hasAny = $this->base($ownerType, $ownerId)->exists();
            if (!$hasAny) {
                $payload['is_default'] = 1;
            }

            $id = DB::table('mailer_settings')->insertGetId($payload);

            DB::commit();

            $fresh = (array) $this->base($ownerType, $ownerId)->where('id', $id)->first();
            $this->audit($request, 'store', $id, array_keys($payload), null, $this->redact($fresh));

            $fresh['password'] = '******';
            return response()->json(['status'=>'success','message'=>'Mailer setting created.','data'=>(object)$fresh], 201);
        } catch (Throwable $e) {
            DB::rollBack();
            $this->audit($request, 'store', null, ['exception'], null, [
                'error' => $e->getMessage(),
            ]);
            Log::error('Mailer create failed', ['err'=>$e->getMessage()]);
            return response()->json(['status'=>'error','message'=>'Could not create','error'=>$e->getMessage()], 500);
        }
    }

    /** GET /api/mailer/{id} */
    public function show(Request $request, int $id)
    {
        [$ownerType, $ownerId] = $this->owner($request);

        $row = $this->base($ownerType, $ownerId)->where('id', $id)->first();
        if (!$row) {
            return response()->json(['status'=>'error','message'=>'Not found'], 404);
        }

        $this->audit($request, 'show', $id);
        $row->password = '******';
        return response()->json(['status'=>'success','message'=>'Mailer setting retrieved.','data'=>$row]);
    }

    /** PUT /api/mailer/{id} */
    public function update(Request $request, int $id)
    {
        [$ownerType, $ownerId] = $this->owner($request);
        [$by] = $this->actor($request);

        $existing = $this->base($ownerType, $ownerId)->where('id', $id)->first();
        if (!$existing) {
            $this->audit($request, 'update', $id, ['not_found'], null, null);
            return response()->json(['status'=>'error','message'=>'Not found'], 404);
        }

        $this->normalize($request);
        $v = $this->validator($request, true);
        if ($v->fails()) {
            $this->audit($request, 'update', $id, ['validation_failed'], $this->redact((array) $existing), [
                'errors' => $v->errors(),
            ]);
            return response()->json(['status'=>'error','message'=>'Validation failed','errors'=>$v->errors()], 422);
        }

        $payload = $this->buildPayload($request, true, $existing->password);
        $payload['updated_by'] = $by;
        $payload['updated_at'] = now();

        $makeDefault = $request->boolean('is_default', false);

        try {
            DB::beginTransaction();

            if ($makeDefault) {
                $this->base($ownerType, $ownerId)->update(['is_default' => 0]);
                $payload['is_default'] = 1;
            }

            $this->base($ownerType, $ownerId)->where('id', $id)->update($payload);

            DB::commit();

            $fresh = (array) $this->base($ownerType, $ownerId)->where('id', $id)->first();
            $this->audit(
                $request,
                'update',
                $id,
                array_keys($payload),
                $this->redact((array) $existing),
                $this->redact($fresh)
            );

            $fresh['password'] = '******';
            return response()->json(['status'=>'success','message'=>'Mailer setting updated.','data'=>(object)$fresh]);
        } catch (Throwable $e) {
            DB::rollBack();
            $this->audit($request, 'update', $id, ['exception'], $this->redact((array) $existing), [
                'error' => $e->getMessage(),
            ]);
            Log::error('Mailer update failed', ['err'=>$e->getMessage()]);
            return response()->json(['status'=>'error','message'=>'Could not update','error'=>$e->getMessage()], 500);
        }
    }

    /** PUT /api/mailer/{id}/default */
    public function setDefault(Request $request, int $id)
    {
        [$ownerType, $ownerId] = $this->owner($request);

        $exists = $this->base($ownerType, $ownerId)->where('id', $id)->exists();
        if (!$exists) {
            $this->audit($request, 'default', $id, ['not_found'], null, null);
            return response()->json(['status'=>'error','message'=>'Not found'], 404);
        }

        try {
            DB::beginTransaction();
            // Toggle atomically: set given row to default, all others off
            $this->base($ownerType, $ownerId)->update(['is_default' => 0, 'updated_at' => now()]);
            $this->base($ownerType, $ownerId)->where('id', $id)->update(['is_default' => 1, 'updated_at' => now()]);
            DB::commit();

            $list = $this->base($ownerType, $ownerId)
                ->select('id','label','mailer','host','port','username','encryption','timeout','from_address','from_name','reply_to_address','reply_to_name','is_default','status')
                ->orderByDesc('is_default')->orderByDesc('id')->get()
                ->map(function ($r) { $r->password = '******'; return $r; });

            $this->audit($request, 'default', $id, ['is_default'], null, ['id'=>$id,'is_default'=>1]);
            return response()->json(['status'=>'success','message'=>'Default mailer set.','data'=>$list]);
        } catch (Throwable $e) {
            DB::rollBack();
            $this->audit($request, 'default', $id, ['exception'], null, [
                'error' => $e->getMessage(),
            ]);
            Log::error('Mailer setDefault failed', ['err'=>$e->getMessage()]);
            return response()->json(['status'=>'error','message'=>'Could not set default','error'=>$e->getMessage()], 500);
        }
    }

    /** DELETE /api/mailer/{id} */
    public function destroy(Request $request, int $id)
    {
        [$ownerType, $ownerId] = $this->owner($request);

        $row = $this->base($ownerType, $ownerId)->where('id', $id)->first();
        if (!$row) {
            $this->audit($request, 'destroy', $id, ['not_found'], null, null);
            return response()->json(['status'=>'error','message'=>'Not found'], 404);
        }

        try {
            DB::beginTransaction();

            // Hard delete (keeps unique(label) simple). If you want soft delete, set deleted_at instead.
            DB::table('mailer_settings')->where('id', $id)->delete();

            // If the deleted one was default → promote the latest remaining (if any)
            if ((int) $row->is_default === 1) {
                $candidate = $this->base($ownerType, $ownerId)->orderByDesc('id')->first();
                if ($candidate) {
                    $this->base($ownerType, $ownerId)->where('id', $candidate->id)->update([
                        'is_default' => 1, 'updated_at' => now()
                    ]);
                }
            }

            DB::commit();

            $this->audit($request, 'destroy', $id, null, $this->redact((array) $row), null);
            return response()->json(['status'=>'success','message'=>'Mailer setting deleted.']);
        } catch (Throwable $e) {
            DB::rollBack();
            $this->audit($request, 'destroy', $id, ['exception'], $this->redact((array) $row), [
                'error' => $e->getMessage(),
            ]);
            Log::error('Mailer delete failed', ['err'=>$e->getMessage()]);
            return response()->json(['status'=>'error','message'=>'Could not delete','error'=>$e->getMessage()], 500);
        }
    }
}
