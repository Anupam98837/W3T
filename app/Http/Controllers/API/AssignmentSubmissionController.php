<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;

class AssignmentSubmissionController extends Controller
{
    /**
     * Master whitelist of allowed file extensions for "file" submissions.
     * Keep lower-case, no leading dots.
     */
    private $MASTER_EXTENSIONS = [
        'pdf','doc','docx','txt','pptx','xlsx','zip','rar','7z',
        'jpg','png','gif','svg',
        'mp4',
        'js','py','java','html','css'
    ];

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

    private function getUuid(): string
    {
        return (string) Str::uuid();
    }

    /**
     * Helper: resolve submission by id or uuid
     * returns stdClass row or null
     */
    private function resolveSubmission($key)
    {
        $q = DB::table('assignment_submissions')->whereNull('deleted_at');
        if (ctype_digit((string)$key)) {
            $q->where('id', (int) $key);
        } elseif (Str::isUuid($key)) {
            $q->where('uuid', $key);
        } else {
            // try uuid/slug fallback: submissions do not typically have slug so treat non-digit non-uuid as id attempt
            $q->where('id', (int)$key);
        }
        return $q->first();
    }

    public function show(string $uuid)
    {
        $row = DB::table('assignment_submissions')
            ->where('uuid', $uuid)
            ->whereNull('deleted_at')
            ->first();

        if (!$row) {
            return response()->json(['error' => 'Submission not found'], 404);
        }

        return response()->json([
            'message' => 'Submission fetched successfully',
            'data' => $row,
        ], 200);
    }

    /**
     * Upload an assignment submission (no Eloquent models used).
     */
    public function upload(Request $request)
    {
        // build ext list for server-side info (not used in validator to avoid client-mime mismatches)
        $extList = implode(',', $this->MASTER_EXTENSIONS);

        // Validation rules
        $rules = [
            'assignment_id'      => 'required|integer|min:1',
            'course_id'          => 'required|integer|min:1',
            'course_module_id'   => 'required|integer|min:1',
            'batch_id'           => 'required|integer|min:1',
            'student_id'         => 'required|integer|min:1',
            'attempt_no'         => 'integer|min:1',
            'status'             => 'string|max:20',
            'content_text'       => 'nullable|string',
            'content_html'       => 'nullable|string',
            'link_url'           => 'nullable|url|max:1024',
            'repo_url'           => 'nullable|url|max:1024',
            'resubmission_of_id' => 'nullable|integer|min:1',
            'version_no'         => 'integer|min:1',
            'is_late'            => 'boolean',
            'late_minutes'       => 'integer|min:0',
            // attachments parent + per-file size (avoid mimes here)
            'attachments' => 'sometimes|array',
            'attachments.*'      => "nullable|file|max:51200",
        ];

        $validator = Validator::make($request->all(), $rules, [
            'attachments.*.max' => 'Each attachment must be <= 50 MB.',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Validation failed',
                'errors'  => $validator->errors(),
            ], 422);
        }

        // Prepare values (defaults)
        $uuid = $this->getUuid();
        $assignmentId = (int) $request->input('assignment_id');
        $courseId = (int) $request->input('course_id');
        $courseModuleId = (int) $request->input('course_module_id');
        $batchId = (int) $request->input('batch_id');
        $studentId = (int) $request->input('student_id');
        $attemptNo = (int) ($request->input('attempt_no', 1));
        $status = $request->input('status', 'submitted');
        $submittedAt = Carbon::now();
        $isLate = filter_var($request->input('is_late', false), FILTER_VALIDATE_BOOLEAN);
        $lateMinutes = (int) $request->input('late_minutes', 0);
        $contentText = $request->input('content_text');
        $contentHtml = $request->input('content_html');
        $linkUrl = $request->input('link_url');
        $repoUrl = $request->input('repo_url');
        $resubmissionOfId = $request->input('resubmission_of_id');
        $versionNo = (int) $request->input('version_no', 1);

        // Build attachments array by storing files if present
        $attachments = [];
        if ($request->hasFile('attachments')) {
            $files = is_array($request->file('attachments')) ? $request->file('attachments') : [$request->file('attachments')];
            // Ensure directory path: storage/app/public/assignment_submissions/{uuid}/
            $basePath = "assignment_submissions/{$uuid}";

            foreach ($files as $file) {
                if (! $file || ! $file->isValid()) {
                    // skip invalid file
                    continue;
                }

                // extra server-side extension check (prevents spoofed mime)
                $ext = strtolower($file->getClientOriginalExtension() ?: '');
                if ($ext === '' || ! in_array($ext, $this->MASTER_EXTENSIONS, true)) {
                    return response()->json([
                        'message' => 'Validation failed',
                        'errors' => ['attachments' => ["File type .{$ext} is not allowed for submissions."]],
                    ], 422);
                }

                // store publicly in the 'public' disk
                $storedPath = $file->storePublicly($basePath, 'public');
                // build URL (requires `php artisan storage:link` in app)
                $url = Storage::disk('public')->url($storedPath);

                $attachments[] = [
                    'name' => $file->getClientOriginalName(),
                    'mime' => $file->getClientMimeType(),
                    'size' => $file->getSize(),
                    'path' => $storedPath,
                    'url'  => $url,
                ];
            }
        }

        // capture IP (request->ip)
        $submittedIp = $request->ip();

        // flags_json / metadata placeholders
        $flagsJson = (array) $request->input('flags_json', []);
        $metadata  = (array) $request->input('metadata', []);

        // Build DB insert payload
        $payload = [
            'uuid' => $uuid,
            'assignment_id' => $assignmentId,
            'course_id' => $courseId,
            'course_module_id' => $courseModuleId,
            'batch_id' => $batchId,
            'student_id' => $studentId,
            'attempt_no' => $attemptNo,
            'status' => $status,
            'submitted_at' => $submittedAt->toDateTimeString(),
            'is_late' => $isLate ? 1 : 0,
            'late_minutes' => $lateMinutes,
            'submitted_ip' => $submittedIp,
            'content_text' => $contentText,
            'content_html' => $contentHtml,
            'link_url' => $linkUrl,
            'repo_url' => $repoUrl,
            'attachments_json' => $attachments ? json_encode($attachments) : null,
            'total_marks' => null,
            'grade_letter' => null,
            'graded_at' => null,
            'graded_by' => null,
            'grader_note' => null,
            'feedback_html' => null,
            'feedback_visible' => 1,
            'resubmission_of_id' => $resubmissionOfId,
            'version_no' => $versionNo,
            'flags_json' => $flagsJson ? json_encode($flagsJson) : null,
            'metadata' => $metadata ? json_encode($metadata) : null,
            'created_at' => $submittedAt->toDateTimeString(),
            'updated_at' => $submittedAt->toDateTimeString(),
        ];

        // Insert using transaction
        try {
            $id = DB::transaction(function () use ($payload) {
                return DB::table('assignment_submissions')->insertGetId($payload);
            });

            // successful
            return response()->json([
                'message' => 'Submission uploaded successfully',
                'data' => [
                    'id' => $id,
                    'uuid' => $uuid,
                    'attachments' => $attachments,
                ],
            ], 201);
        } catch (\Throwable $e) {
            Log::error('assignment submission upload error: '.$e->getMessage(), [
                'exception' => $e,
                'payload' => $payload,
            ]);

            // attempt to cleanup stored files on disk if something went wrong
            if (! empty($attachments) && isset($basePath)) {
                try {
                    Storage::disk('public')->deleteDirectory($basePath);
                } catch (\Throwable $ex) {
                    Log::warning('Failed to cleanup attachments after failed DB insert: '.$ex->getMessage());
                }
            }

            return response()->json([
                'message' => 'Failed to upload submission',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Upload by assignment key (id|uuid|slug) with allowed_submission_types enforcement.
     */
    public function uploadByAssignment(Request $r, string $assignmentKey)
    {
        // allowed roles: student (typical), plus admins/instructors may submit on behalf
        if ($res = $this->requireRole($r, ['student','admin','superadmin','instructor'])) return $res;
        $actor = $this->actor($r);
        $actorRole = $actor['role'];
        $actorId = $actor['id'];

        // Resolve assignment (id | uuid | slug)
        $aq = DB::table('assignments')->whereNull('deleted_at');
        if (ctype_digit($assignmentKey)) {
            $aq->where('id', (int)$assignmentKey);
        } elseif (\Illuminate\Support\Str::isUuid($assignmentKey)) {
            $aq->where('uuid', $assignmentKey);
        } elseif (\Illuminate\Support\Facades\Schema::hasColumn('assignments', 'slug')) {
            $aq->where('slug', $assignmentKey);
        } else {
            return response()->json(['error' => 'Assignment not found'], 404);
        }
        $assignment = $aq->first();
        if (!$assignment) return response()->json(['error' => 'Assignment not found'], 404);

        // Extract allowed_submission_types (accept JSON array, comma-separated or single token)
        $allowedRaw = $assignment->allowed_submission_types ?? $r->input('allowed_submission_types'); // fallback if provided by client
        $allowed = [];

        if ($allowedRaw !== null) {
            if (is_array($allowedRaw)) {
                $allowed = $allowedRaw;
            } elseif (is_string($allowedRaw)) {
                $decoded = json_decode($allowedRaw, true);
                if (json_last_error() === JSON_ERROR_NONE && is_array($decoded)) {
                    $allowed = $decoded;
                } else {
                    // comma-separated fallback
                    $allowed = array_filter(array_map('trim', explode(',', $allowedRaw)));
                }
            }
        }

        // normalize to lowercase tokens
        $allowed = array_map('strtolower', $allowed);
        $allowed = array_values(array_unique($allowed));

        // If no allowed types defined, require explicit configuration
        if (empty($allowed)) {
            return response()->json(['error' => 'Assignment has no allowed_submission_types configured'], 422);
        }

        // Determine which student this submission is for
        $studentId = null;
        if ($actorRole === 'student') {
            $studentId = $actorId;
        } else {
            // admin/instructor must pass student_id if submitting on behalf
            if (! $r->filled('student_id')) {
                return response()->json(['error' => 'student_id is required when submitting on behalf'], 422);
            }
            $studentId = (int) $r->input('student_id');
        }

        // ----------------- ATTEMPT NO. HANDLING -----------------
        // determine provided attempt_no (may be missing)
        $providedAttempt = $r->filled('attempt_no') ? (int) $r->input('attempt_no') : null;

        // try to detect assignment's attempts allowed (column name may vary)
        $attemptsAllowed = null;
        if (isset($assignment->attempts_allowed)) {
            $attemptsAllowed = (int) $assignment->attempts_allowed;
        } elseif (isset($assignment->max_attempts)) {
            $attemptsAllowed = (int) $assignment->max_attempts;
        }

        // compute next attempt if none provided
        if ($providedAttempt === null) {
            $last = DB::table('assignment_submissions')
                ->where('assignment_id', $assignment->id)
                ->where('student_id', $studentId)
                ->whereNull('deleted_at')
                ->selectRaw('COALESCE(MAX(attempt_no), 0) as last_attempt')
                ->first();
            $nextAttempt = (int) ($last->last_attempt ?? 0) + 1;
            $attemptNo = $nextAttempt;
        } else {
            $attemptNo = $providedAttempt;
        }

        // validate attempt range if assignment restricts attempts
        if ($attemptsAllowed !== null && $attemptsAllowed > 0 && $attemptNo > $attemptsAllowed) {
            return response()->json([
                'error' => 'Attempt number exceeds allowed attempts for this assignment',
                'allowed' => $attemptsAllowed,
                'attempt_no' => $attemptNo
            ], 422);
        }

        // prevent duplicate submission row for same assignment+student+attempt_no
        $exists = DB::table('assignment_submissions')
            ->where('assignment_id', $assignment->id)
            ->where('student_id', $studentId)
            ->where('attempt_no', $attemptNo)
            ->whereNull('deleted_at')
            ->exists();

        if ($exists) {
            return response()->json([
                'error' => 'Submission for this attempt already exists'
            ], 409);
        }

        // Validate inputs dynamically according to allowed types
        $rules = [
            // core
            'attempt_no' => 'sometimes|integer|min:1',
            'status' => 'sometimes|string|max:20',
            'is_late' => 'sometimes|boolean',
            'late_minutes' => 'sometimes|integer|min:0',
            'version_no' => 'sometimes|integer|min:1',
        ];

        // Determine if allowed contains extension list (pdf,pptx etc.)
        $allowedExtensions = [];
        foreach ($allowed as $token) {
            if (!in_array($token, ['file','link','repo','text','html'], true)) {
                // treat short alphanumeric tokens as extensions
                if (preg_match('/^[a-z0-9]{1,5}$/i', $token)) {
                    $allowedExtensions[] = strtolower($token);
                }
            }
        }
        $hasFileToken = in_array('file', $allowed, true) || !empty($allowedExtensions);

        // If 'file' allowed, permit attachments and enforce whitelist
        if ($hasFileToken) {
            $extList = !empty($allowedExtensions) ? implode(',', $allowedExtensions) : implode(',', $this->MASTER_EXTENSIONS);
            // note: keep per-file size limit; do not rely solely on client mime
            $rules['attachments'] = 'sometimes|array';
            $rules['attachments.*'] = "nullable|file|max:51200";
        } else {
            // validate that client didn't send attachments if not allowed
            if ($r->hasFile('attachments')) {
                return response()->json([
                    'error' => 'File attachments are not allowed for this assignment',
                ], 422);
            }
        }

        // If 'link' allowed, allow link_url; otherwise reject provided link_url
        if (in_array('link', $allowed, true)) {
            $rules['link_url'] = 'nullable|url|max:1024';
        } else {
            if ($r->filled('link_url')) {
                return response()->json(['error' => 'Link submission is not allowed for this assignment'], 422);
            }
        }

        // If 'repo' allowed, allow repo_url; otherwise reject
        if (in_array('repo', $allowed, true)) {
            $rules['repo_url'] = 'nullable|url|max:1024';
        } else {
            if ($r->filled('repo_url')) {
                return response()->json(['error' => 'Repository URL submission is not allowed for this assignment'], 422);
            }
        }

        // If 'text' allowed, allow content_text; otherwise reject
        if (in_array('text', $allowed, true)) {
            $rules['content_text'] = 'nullable|string';
        } else {
            if ($r->filled('content_text')) {
                return response()->json(['error' => 'Plain text submission is not allowed for this assignment'], 422);
            }
        }

        // If 'html' allowed, allow content_html; otherwise reject
        if (in_array('html', $allowed, true)) {
            $rules['content_html'] = 'nullable|string';
        } else {
            if ($r->filled('content_html')) {
                return response()->json(['error' => 'HTML content submission is not allowed for this assignment'], 422);
            }
        }

        // run validator
        $validator = Validator::make($r->all(), $rules, [
            'attachments.*.max' => 'Each attachment must be <= 50 MB.',
        ]);
        if ($validator->fails()) {
            return response()->json(['message' => 'Validation failed', 'errors' => $validator->errors()], 422);
        }

        // Ensure at least one allowed submission field is present
        $present = false;
        foreach ($allowed as $t) {
            switch ($t) {
                case 'file':
                    if ($r->hasFile('attachments')) $present = true;
                    break;
                case 'link':
                    if ($r->filled('link_url')) $present = true;
                    break;
                case 'repo':
                    if ($r->filled('repo_url')) $present = true;
                    break;
                case 'text':
                    if ($r->filled('content_text')) $present = true;
                    break;
                case 'html':
                    if ($r->filled('content_html')) $present = true;
                    break;
                default:
                    // if token looks like extension and files present, count as present
                    if (in_array($t, $allowedExtensions, true) && $r->hasFile('attachments')) {
                        $present = true;
                    }
                    break;
            }
            if ($present) break;
        }
        if (! $present) {
            return response()->json(['error' => 'No allowed submission content provided. Allowed types: '.implode(',', $allowed)], 422);
        }

        // Build payload fields
        $uuid = $this->getUuid();
        $assignmentId = (int) $assignment->id;
        $courseId = (int) ($assignment->course_id ?? $r->input('course_id', 0));
        $courseModuleId = (int) ($assignment->course_module_id ?? $r->input('course_module_id', 0));
        $batchId = (int) ($assignment->batch_id ?? $r->input('batch_id', 0));

        // use computed attemptNo from earlier
        $status = $r->input('status', 'submitted');
        $submittedAt = Carbon::now();
        $isLate = filter_var($r->input('is_late', false), FILTER_VALIDATE_BOOLEAN);
        $lateMinutes = (int) $r->input('late_minutes', 0);
        $versionNo = (int) $r->input('version_no', 1);

        $contentText = $r->input('content_text');
        $contentHtml = $r->input('content_html');
        $linkUrl = $r->input('link_url');
        $repoUrl = $r->input('repo_url');

        // Handle file attachments if allowed
        $attachments = [];
        $basePath = "assignment_submissions/{$uuid}";
        if ($hasFileToken && $r->hasFile('attachments')) {
            $files = is_array($r->file('attachments')) ? $r->file('attachments') : [$r->file('attachments')];
            // determine allowed check list
            $allowedCheckList = !empty($allowedExtensions) ? $allowedExtensions : $this->MASTER_EXTENSIONS;

            foreach ($files as $file) {
                if (! $file || ! $file->isValid()) continue;

                // double-check extension server-side
                $ext = strtolower($file->getClientOriginalExtension() ?: '');
                if ($ext === '' || ! in_array($ext, $allowedCheckList, true)) {
                    return response()->json([
                        'message' => 'Validation failed',
                        'errors' => ['attachments' => ["File type .{$ext} is not allowed for submissions."]],
                    ], 422);
                }

                $storedPath = $file->storePublicly($basePath, 'public');
                $url = Storage::disk('public')->url($storedPath);
                $attachments[] = [
                    'name' => $file->getClientOriginalName(),
                    'mime' => $file->getClientMimeType(),
                    'size' => $file->getSize(),
                    'path' => $storedPath,
                    'url'  => $url,
                ];
            }
        }

        // Build DB payload
        $payload = [
            'uuid' => $uuid,
            'assignment_id' => $assignmentId,
            'course_id' => $courseId,
            'course_module_id' => $courseModuleId ?: null,
            'batch_id' => $batchId ?: null,
            'student_id' => $studentId,
            'attempt_no' => $attemptNo,
            'status' => $status,
            'submitted_at' => $submittedAt->toDateTimeString(),
            'is_late' => $isLate ? 1 : 0,
            'late_minutes' => $lateMinutes,
            'submitted_ip' => $r->ip(),
            'content_text' => $contentText,
            'content_html' => $contentHtml,
            'link_url' => $linkUrl,
            'repo_url' => $repoUrl,
            'attachments_json' => $attachments ? json_encode($attachments) : null,
            'total_marks' => null,
            'grade_letter' => null,
            'graded_at' => null,
            'graded_by' => null,
            'grader_note' => null,
            'feedback_html' => null,
            'feedback_visible' => 1,
            'resubmission_of_id' => $r->input('resubmission_of_id'),
            'version_no' => $versionNo,
            'flags_json' => json_encode((array)$r->input('flags_json', [])),
            'metadata' => json_encode((array)$r->input('metadata', [])),
            'created_at' => $submittedAt->toDateTimeString(),
            'updated_at' => $submittedAt->toDateTimeString(),
        ];

        // Insert using transaction and handle duplicate attempts
        try {
            $id = DB::transaction(function () use ($payload) {
                return DB::table('assignment_submissions')->insertGetId($payload);
            });

            return response()->json([
                'message' => 'Submission uploaded successfully',
                'data' => [
                    'id' => $id,
                    'uuid' => $uuid,
                    'attachments' => $attachments,
                ],
            ], 201);
        } catch (\Illuminate\Database\QueryException $qe) {
            // Unique constraint violation (duplicate attempt) -> 409
            if ($qe->getCode() === '23000') {
                return response()->json(['error' => 'Submission for this attempt already exists (constraint)'], 409);
            }
            Log::error('assignment submission uploadByAssignment DB error: '.$qe->getMessage(), ['exception'=>$qe,'payload'=>$payload]);
            if (! empty($attachments)) {
                try { Storage::disk('public')->deleteDirectory($basePath); } catch (\Throwable $ex) {}
            }
            return response()->json(['message' => 'Failed to upload submission', 'error' => $qe->getMessage()], 500);
        } catch (\Throwable $e) {
            Log::error('assignment submission uploadByAssignment error: '.$e->getMessage(), ['exception'=>$e,'payload'=>$payload]);
            if (! empty($attachments)) {
                try { Storage::disk('public')->deleteDirectory($basePath); } catch (\Throwable $ex) {}
            }
            return response()->json(['message' => 'Failed to upload submission', 'error' => $e->getMessage()], 500);
        }
    }

    /* -----------------------------------------------------
     * Soft delete / restore / force delete helpers
     * ----------------------------------------------------- */

    /**
     * Soft-delete a submission by id or uuid.
     * - Students can soft-delete their own submission.
     * - Admins/Instructors can soft-delete any submission.
     */
    public function softDeleteSubmission(Request $r, $submissionKey)
{
    try {
        // Find by id or uuid
        $sub = \DB::table('assignment_submissions')
            ->where('id', $submissionKey)
            ->orWhere('uuid', $submissionKey)
            ->first();

        if (! $sub) {
            return response()->json(['error' => 'Submission not found'], 404);
        }

        // --- Authorization (student can delete only own submission) ---
        $actor = $this->actor($r); // your existing method
        $role  = $actor['role'] ?? null;
        $actorId = (int)($actor['id'] ?? 0);

        // student can delete only his own submission
        if ($role === 'student' && $actorId !== (int)$sub->student_id) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        // instructors/admins can delete any
        // (super_admin, admin, instructor implicitly allowed)

        // --- Soft delete + update status ---
        \DB::table('assignment_submissions')
            ->where('id', $sub->id)
            ->update([
                'deleted_at' => now(),
                'status'     => 'unsubmitted',
                'updated_at' => now(),
            ]);

        return response()->json(['message' => 'Deleted'], 200);

    } catch (\Throwable $e) {
        \Log::error("softDeleteSubmission error: ".$e->getMessage(), [
            'trace' => $e->getTraceAsString()
        ]);
        return response()->json(['error' => 'Server error'], 500);
    }
}


    /**
     * Restore a soft-deleted submission.
     * Only admins/instructors are permitted to restore.
     */
    public function restoreSubmission(Request $r, string $submissionKey)
    {
        if ($res = $this->requireRole($r, ['admin','superadmin','instructor'])) return $res;

        // resolve including deleted
        $q = DB::table('assignment_submissions');
        if (ctype_digit($submissionKey)) {
            $q->where('id', (int)$submissionKey);
        } elseif (Str::isUuid($submissionKey)) {
            $q->where('uuid', $submissionKey);
        } else {
            $q->where('id', (int)$submissionKey);
        }
        $row = $q->first();
        if (!$row) return response()->json(['error'=>'Submission not found'], 404);
        if (empty($row->deleted_at)) return response()->json(['error'=>'Submission is not deleted'], 422);

        try {
            DB::table('assignment_submissions')->where('id', $row->id)->update([
                'deleted_at' => null,
                'deleted_by' => null,
                'deleted_by_role' => null,
                'updated_at' => Carbon::now()->toDateTimeString(),
            ]);
            return response()->json(['message'=>'Submission restored','id'=>$row->id,'uuid'=>$row->uuid], 200);
        } catch (\Throwable $e) {
            Log::error('restore submission error: '.$e->getMessage(), ['submission'=>$row]);
            return response()->json(['error'=>'Failed to restore submission'], 500);
        }
    }

    /**
     * Force-delete a submission (permanent). Only admins allowed.
     * Removes DB row and attempts to delete stored files (if any).
     */
    public function forceDeleteSubmission(Request $r, string $submissionKey)
    {
        if ($res = $this->requireRole($r, ['admin','superadmin'])) return $res;

        // resolve including deleted
        $q = DB::table('assignment_submissions');
        if (ctype_digit($submissionKey)) {
            $q->where('id', (int)$submissionKey);
        } elseif (Str::isUuid($submissionKey)) {
            $q->where('uuid', $submissionKey);
        } else {
            $q->where('id', (int)$submissionKey);
        }
        $row = $q->first();
        if (!$row) return response()->json(['error'=>'Submission not found'], 404);

        // attempt to parse attachments_json for cleanup
        $attachments = [];
        if (!empty($row->attachments_json)) {
            try { $attachments = json_decode($row->attachments_json, true) ?: []; } catch (\Throwable $e) { $attachments = []; }
        }

        try {
            DB::transaction(function() use ($row, $attachments) {
                DB::table('assignment_submissions')->where('id', $row->id)->delete(); // permanent
                // cleanup files
                if (!empty($attachments) && is_array($attachments)) {
                    // attempt to delete individual paths first
                    foreach ($attachments as $att) {
                        if (!empty($att['path'])) {
                            try { Storage::disk('public')->delete($att['path']); } catch (\Throwable $ex) {}
                        }
                    }
                    // also try to remove directory if stored using uuid-based path
                    if (!empty($row->uuid)) {
                        try { Storage::disk('public')->deleteDirectory("assignment_submissions/{$row->uuid}"); } catch (\Throwable $ex) {}
                    }
                }
            });

            return response()->json(['message'=>'Submission permanently deleted'], 200);
        } catch (\Throwable $e) {
            Log::error('force delete submission error: '.$e->getMessage(), ['submission'=>$row]);
            return response()->json(['error'=>'Failed to permanently delete submission'], 500);
        }
    }
   public function mySubmissions(Request $r, $assignmentKey = null)
{
    $actor = $this->actor($r);
    if (empty($actor['role']) || $actor['role'] !== 'student') {
        return response()->json(['error' => 'Unauthorized'], 403);
    }
    $studentId = (int) $actor['id'];

    $page = max(1, (int) $r->query('page', 1));
    $perPage = min(100, max(1, (int) $r->query('per_page', 20)));
    $offset = ($page - 1) * $perPage;
    $includeDeleted = (bool) $r->query('include_deleted', false);

    $q = DB::table('assignment_submissions')->where('student_id', $studentId);
    if (! $includeDeleted) {
        $q->whereNull('deleted_at');
    }

    // Resolve assignmentKey -> assignment_id when provided (accepts numeric id, UUID, or slug)
    if ($assignmentKey) {
        $assignmentId = null;

        // numeric id
        if (ctype_digit((string)$assignmentKey)) {
            $assignmentId = (int) $assignmentKey;
            $ass = DB::table('assignments')->where('id', $assignmentId)->whereNull('deleted_at')->first();
            if (! $ass) return response()->json(['error' => 'Assignment not found'], 404);
            $assignmentId = $ass->id;
        }
        // UUID
        elseif (Str::isUuid($assignmentKey)) {
            $ass = DB::table('assignments')->where('uuid', $assignmentKey)->whereNull('deleted_at')->first();
            if (! $ass) return response()->json(['error' => 'Assignment not found'], 404);
            $assignmentId = $ass->id;
        }
        // slug fallback (if table has slug column)
        else {
            if (\Illuminate\Support\Facades\Schema::hasColumn('assignments', 'slug')) {
                $ass = DB::table('assignments')->where('slug', $assignmentKey)->whereNull('deleted_at')->first();
                if (! $ass) return response()->json(['error' => 'Assignment not found'], 404);
                $assignmentId = $ass->id;
            } else {
                return response()->json(['error' => 'Assignment not found'], 404);
            }
        }

        // Apply filter
        if ($assignmentId) $q->where('assignment_id', $assignmentId);
    }

    $total = (int) $q->count();
    $rows = $q->orderBy('created_at', 'desc')
              ->offset($offset)
              ->limit($perPage)
              ->get();

    $items = $rows->map(function($row){
        $row = (array) $row;
        $row['attachments'] = [];
        if (!empty($row['attachments_json'])) {
            $decoded = json_decode($row['attachments_json'], true);
            if (json_last_error() === JSON_ERROR_NONE && is_array($decoded)) $row['attachments'] = $decoded;
        }
        $row['flags'] = [];
        if (!empty($row['flags_json'])) {
            $decoded = json_decode($row['flags_json'], true);
            if (json_last_error() === JSON_ERROR_NONE && is_array($decoded)) $row['flags'] = $decoded;
        }
        $row['metadata'] = [];
        if (!empty($row['metadata'])) {
            $decoded = json_decode($row['metadata'], true);
            if (json_last_error() === JSON_ERROR_NONE && is_array($decoded)) $row['metadata'] = $decoded;
        }
        return $row;
    });

    return response()->json([
        'message' => 'Submissions fetched',
        'data' => [
            'items' => $items,
            'pagination' => [
                'total' => $total,
                'page' => $page,
                'per_page' => $perPage,
                'pages' => (int) ceil($total / max(1, $perPage)),
            ],
        ],
    ], 200);
}

    /**
     * Return one submission owned by the authenticated student (by id or uuid).
     */
    public function mySubmissionDetail(Request $r, string $submissionKey)
    {
        $actor = $this->actor($r);
        if (empty($actor['role']) || $actor['role'] !== 'student') {
            return response()->json(['error' => 'Unauthorized'], 403);
        }
        $studentId = (int) $actor['id'];

        // resolve submission including soft-deleted? default exclude deleted
        $q = DB::table('assignment_submissions');
        if (ctype_digit((string)$submissionKey)) {
            $q->where('id', (int)$submissionKey);
        } elseif (Str::isUuid($submissionKey)) {
            $q->where('uuid', $submissionKey);
        } else {
            $q->where('id', (int)$submissionKey);
        }
        $row = $q->first();
        if (! $row) return response()->json(['error' => 'Submission not found'], 404);

        if ((int)$row->student_id !== $studentId) {
            return response()->json(['error' => 'Forbidden'], 403);
        }

        $row = (array) $row;
        $row['attachments'] = [];
        if (!empty($row['attachments_json'])) {
            $decoded = json_decode($row['attachments_json'], true);
            if (json_last_error() === JSON_ERROR_NONE && is_array($decoded)) $row['attachments'] = $decoded;
        }
        $row['flags'] = [];
        if (!empty($row['flags_json'])) {
            $decoded = json_decode($row['flags_json'], true);
            if (json_last_error() === JSON_ERROR_NONE && is_array($decoded)) $row['flags'] = $decoded;
        }
        $row['metadata'] = [];
        if (!empty($row['metadata'])) {
            $decoded = json_decode($row['metadata'], true);
            if (json_last_error() === JSON_ERROR_NONE && is_array($decoded)) $row['metadata'] = $decoded;
        }

        return response()->json(['message'=>'Submission fetched','data'=>$row], 200);
    }
    
        /**
     * Return submission-related info for the modal UI:
     * - allowed_submission_types (array)
     * - allowed_extensions (array)
     * - file_allowed (bool)
     * - attempts_allowed (int|null)
     * - attempts_taken (int)
     * - attempts_left (int|null)
     * - allowed_display (human-friendly string like "PDF, DOC, Images")
     */
    public function assignmentInfo(Request $r, string $assignmentKey)
    {
        // resolve assignment by id|uuid|slug
        $aq = DB::table('assignments')->whereNull('deleted_at');
        if (ctype_digit((string)$assignmentKey)) {
            $aq->where('id', (int)$assignmentKey);
        } elseif (Str::isUuid($assignmentKey)) {
            $aq->where('uuid', $assignmentKey);
        } elseif (\Illuminate\Support\Facades\Schema::hasColumn('assignments', 'slug')) {
            $aq->where('slug', $assignmentKey);
        } else {
            return response()->json(['error' => 'Assignment not found'], 404);
        }

        $assignment = $aq->first();
        if (!$assignment) return response()->json(['error' => 'Assignment not found'], 404);

        // parse allowed_submission_types (same logic you use elsewhere)
        $allowedRaw = $assignment->allowed_submission_types ?? $r->input('allowed_submission_types');
        $allowed = [];
        if ($allowedRaw !== null) {
            if (is_array($allowedRaw)) {
                $allowed = $allowedRaw;
            } elseif (is_string($allowedRaw)) {
                $decoded = json_decode($allowedRaw, true);
                if (json_last_error() === JSON_ERROR_NONE && is_array($decoded)) {
                    $allowed = $decoded;
                } else {
                    $allowed = array_filter(array_map('trim', explode(',', $allowedRaw)));
                }
            }
        }
        $allowed = array_map('strtolower', $allowed);
        $allowed = array_values(array_unique($allowed));

        // derive allowed extensions and whether files are allowed
        $allowedExtensions = [];
        foreach ($allowed as $token) {
            if (!in_array($token, ['file','link','repo','text','html'], true)) {
                if (preg_match('/^[a-z0-9]{1,5}$/i', $token)) $allowedExtensions[] = strtolower($token);
            }
        }
        $fileAllowed = in_array('file', $allowed, true) || !empty($allowedExtensions);

        // compute attempts info (use columns attempts_allowed or max_attempts if present)
        $attemptsAllowed = null;
        if (isset($assignment->attempts_allowed)) $attemptsAllowed = (int)$assignment->attempts_allowed;
        elseif (isset($assignment->max_attempts)) $attemptsAllowed = (int)$assignment->max_attempts;

        // current actor (student) attempts taken for this assignment
        $actor = $this->actor($r);
        $studentId = (int) ($actor['id'] ?? 0);
        $attemptsTaken = 0;
        if ($studentId > 0) {
            $cnt = DB::table('assignment_submissions')
                ->where('assignment_id', $assignment->id)
                ->where('student_id', $studentId)
                ->whereNull('deleted_at')
                ->selectRaw('COUNT(1) as cnt')
                ->first();
            $attemptsTaken = (int) ($cnt->cnt ?? 0);
        }

        $attemptsLeft = null;
        if ($attemptsAllowed !== null && $attemptsAllowed > 0) {
            $attemptsLeft = max(0, $attemptsAllowed - $attemptsTaken);
        }

        // build a human friendly allowed_display (e.g. "PDF, DOC, Images")
        $displayParts = [];
        if ($fileAllowed) {
            if (!empty($allowedExtensions)) {
                $displayParts[] = implode(', ', array_map('strtoupper', $allowedExtensions));
            } else {
                // fallback to master list
                $displayParts[] = 'Files (pdf, doc, images, etc)';
            }
        }
        if (in_array('link', $allowed, true)) $displayParts[] = 'Link';
        if (in_array('repo', $allowed, true)) $displayParts[] = 'Repository URL';
        if (in_array('text', $allowed, true)) $displayParts[] = 'Plain text';
        if (in_array('html', $allowed, true)) $displayParts[] = 'HTML';
        $allowedDisplay = $displayParts ? implode(' • ', $displayParts) : 'None';

        return response()->json([
            'message' => 'Assignment submit info',
            'data' => [
                'assignment_id' => $assignment->id,
                'assignment_uuid' => $assignment->uuid ?? null,
                'title' => $assignment->title ?? null,
                'allowed_submission_types' => $allowed,
                'allowed_extensions' => $allowedExtensions,
                'file_allowed' => (bool) $fileAllowed,
                'attempts_allowed' => ($attemptsAllowed !== null ? $attemptsAllowed : null),
                'attempts_taken' => $attemptsTaken,
                'attempts_left' => $attemptsLeft,
                'allowed_display' => $allowedDisplay,
            ],
        ], 200);
    }
    /**
 * Get all submissions for a specific assignment (for instructors)
 * Query params: page, per_page, status, student_name, attempt_no, include_deleted
 */
public function assignmentSubmissions(Request $r, string $assignmentKey)
{
    if ($res = $this->requireRole($r, ['admin','super_admin','instructor'])) return $res;

    // Resolve assignment
    $assignment = $this->resolveAssignment($assignmentKey);
    if (!$assignment) {
        return response()->json(['error' => 'Assignment not found'], 404);
    }

    $page = max(1, (int) $r->query('page', 1));
    $perPage = min(100, max(1, (int) $r->query('per_page', 20)));
    $offset = ($page - 1) * $perPage;
    $includeDeleted = (bool) $r->query('include_deleted', false);

    // Base query
    $q = DB::table('assignment_submissions')
        ->where('assignment_id', $assignment->id)
        ->join('users', 'assignment_submissions.student_id', '=', 'users.id')
        ->select(
            'assignment_submissions.*',
            'users.name as student_name',
            'users.email as student_email',
            'users.uuid as student_uuid'
        );

    if (! $includeDeleted) {
        $q->whereNull('assignment_submissions.deleted_at');
    }

    // Filters
    if ($r->filled('status')) {
        $q->where('assignment_submissions.status', $r->query('status'));
    }

    if ($r->filled('attempt_no')) {
        $q->where('assignment_submissions.attempt_no', (int)$r->query('attempt_no'));
    }

    if ($r->filled('student_name')) {
        $q->where('users.name', 'like', '%' . $r->query('student_name') . '%');
    }

    if ($r->filled('student_email')) {
        $q->where('users.email', 'like', '%' . $r->query('student_email') . '%');
    }

    if ($r->filled('is_late')) {
        $q->where('assignment_submissions.is_late', (bool)$r->query('is_late'));
    }

    // Sorting
    $sortBy = $r->query('sort_by', 'submitted_at');
    $sortOrder = $r->query('sort_order', 'desc');
    $q->orderBy($sortBy, $sortOrder);

    $total = (int) $q->count();
    $rows = $q->offset($offset)
              ->limit($perPage)
              ->get();

    // Process submissions
    $items = $rows->map(function($row) {
        $row = (array) $row;
        
        // Decode attachments
        $row['attachments'] = [];
        if (!empty($row['attachments_json'])) {
            try {
                $decoded = json_decode($row['attachments_json'], true);
                if (is_array($decoded)) $row['attachments'] = $decoded;
            } catch (\Throwable $e) {
                // Keep empty array
            }
        }

        // Decode flags and metadata
        $row['flags'] = $this->decodeJsonField($row['flags_json'] ?? null);
        $row['metadata'] = $this->decodeJsonField($row['metadata'] ?? null);

        // Format dates for frontend
        if (!empty($row['submitted_at'])) {
            $row['submitted_at_formatted'] = \Carbon\Carbon::parse($row['submitted_at'])->format('M j, Y g:i A');
        }
        if (!empty($row['graded_at'])) {
            $row['graded_at_formatted'] = \Carbon\Carbon::parse($row['graded_at'])->format('M j, Y g:i A');
        }

        return $row;
    });

    return response()->json([
        'message' => 'Submissions fetched successfully',
        'data' => [
            'assignment' => [
                'id' => $assignment->id,
                'title' => $assignment->title,
                'due_at' => $assignment->due_at,
                'attempts_allowed' => $assignment->attempts_allowed,
            ],
            'submissions' => $items,
            'pagination' => [
                'total' => $total,
                'page' => $page,
                'per_page' => $perPage,
                'pages' => (int) ceil($total / max(1, $perPage)),
            ],
        ],
    ], 200);
}

/**
 * Get student submission status for an assignment (who submitted and who didn't)
 */
public function studentSubmissionStatus(Request $r, string $assignmentKey)
{
    if ($res = $this->requireRole($r, ['admin','super_admin','instructor'])) return $res;

    // Resolve assignment
    $assignment = $this->resolveAssignment($assignmentKey);
    if (!$assignment) {
        return response()->json(['error' => 'Assignment not found'], 404);
    }

    $batchId = $assignment->batch_id;
    if (!$batchId) {
        return response()->json(['error' => 'Assignment not linked to a batch'], 422);
    }

    // Get assigned students from users table joined with batch_students
    $batchStudents = DB::table('users')
        ->where('users.role', 'student')
        ->whereNull('users.deleted_at')
        ->join('batch_students', function($join) use ($batchId) {
            $join->on('users.id', '=', 'batch_students.user_id')
                 ->where('batch_students.batch_id', '=', $batchId);
        })
        ->whereNull('batch_students.deleted_at')
        ->select(
            'users.id as student_id',
            'users.name as student_name',
            'users.email as student_email',
            'users.uuid as student_uuid',
            'batch_students.created_at as enrolled_at'
        )
        ->get();

    if (!$batchStudents || $batchStudents->isEmpty()) {
        return response()->json([
            'message' => 'No assigned students found for this assignment batch',
            'data' => [
                'assignment' => [
                    'id' => $assignment->id,
                    'title' => $assignment->title,
                ],
                'statistics' => [
                    'submitted' => 0,
                    'not_submitted' => 0,
                    'late_submissions' => 0,
                    'submission_rate' => 0,
                ],
                'students' => [],
                'submitted' => [],
                'not_submitted' => [],
                'pagination' => [
                    'total' => 0,
                    'page' => 1,
                    'per_page' => 50,
                    'pages' => 0,
                ],
            ],
        ], 200);
    }

    // Get submissions for this assignment (ordered so first() is latest attempt)
    $submissions = DB::table('assignment_submissions')
        ->where('assignment_id', $assignment->id)
        ->whereNull('deleted_at')
        ->select('student_id', 'attempt_no', 'submitted_at', 'status', 'is_late')
        ->orderBy('attempt_no', 'desc')
        ->get()
        ->groupBy('student_id');

    // Combine data
    $studentStatus = $batchStudents->map(function($student) use ($submissions, $assignment) {
        $studentId = $student->student_id;
        // get group (may be null) - default to empty collection
        $studentSubmissions = $submissions->get($studentId, collect());

        // ensure it's a collection
        if (!($studentSubmissions instanceof \Illuminate\Support\Collection)) {
            $studentSubmissions = collect($studentSubmissions);
        }

        $latestSubmission = $studentSubmissions->first();
        $attemptsCount = $studentSubmissions->count();

        // convert attempts to arrays for predictable JSON
        $allAttemptsArray = $studentSubmissions->values()->map(function($a) {
            return (array) $a;
        })->all();

        return [
            'student_id' => $studentId,
            'student_name' => $student->student_name,
            'student_email' => $student->student_email,
            'student_uuid' => $student->student_uuid,
            'enrolled_at' => $student->enrolled_at,
            'has_submitted' => $studentSubmissions->isNotEmpty(),
            'submission_count' => $attemptsCount,
            'latest_attempt' => $latestSubmission ? $latestSubmission->attempt_no : null,
            'latest_submission_date' => $latestSubmission ? $latestSubmission->submitted_at : null,
            'status' => $latestSubmission ? $latestSubmission->status : 'not_submitted',
            'is_late' => $latestSubmission ? (bool)$latestSubmission->is_late : false,
            'attempts_remaining' => $assignment->attempts_allowed ?
                max(0, $assignment->attempts_allowed - $attemptsCount) : null,
            'all_attempts' => $allAttemptsArray,
        ];
    });

    // Sorting
    $sortBy = $r->query('sort_by', 'student_name');
    $sortOrder = $r->query('sort_order', 'asc');

    $studentStatus = $studentStatus->sortBy(function($item) use ($sortBy) {
        return $item[$sortBy] ?? '';
    }, SORT_REGULAR, $sortOrder === 'desc');

    // Build explicit submitted / not submitted lists (un-paginated) for easy frontend consumption
    $submittedList = $studentStatus
        ->filter(function($s) { return !empty($s['has_submitted']); })
        ->values()
        ->map(function($s) {
            // keep only fields frontend usually needs (you can expand if required)
            return [
                'student_id' => $s['student_id'],
                'student_name' => $s['student_name'],
                'student_email' => $s['student_email'],
                'student_uuid' => $s['student_uuid'],
                'submission_count' => $s['submission_count'],
                'latest_attempt' => $s['latest_attempt'],
                'latest_submission_date' => $s['latest_submission_date'],
                'status' => $s['status'],
                'is_late' => $s['is_late'],
                'all_attempts' => $s['all_attempts'],
            ];
        })->all();

    $notSubmittedList = $studentStatus
        ->filter(function($s) { return empty($s['has_submitted']); })
        ->values()
        ->map(function($s) {
            return [
                'student_id' => $s['student_id'],
                'student_name' => $s['student_name'],
                'student_email' => $s['student_email'],
                'student_uuid' => $s['student_uuid'],
                'enrolled_at' => $s['enrolled_at'],
            ];
        })->all();

    // Pagination
    $page = max(1, (int) $r->query('page', 1));
    $perPage = min(100, max(1, (int) $r->query('per_page', 50)));
    $total = $studentStatus->count();

    $paginatedData = $studentStatus->slice(($page - 1) * $perPage, $perPage)->values()->all();

    // Statistics
    $submittedCount = count($submittedList);
    $notSubmittedCount = $total - $submittedCount;
    $lateCount = $studentStatus->where('is_late', true)->count();

    return response()->json([
        'message' => 'Student submission status fetched',
        'data' => [
            'assignment' => [
                'id' => $assignment->id,
                'title' => $assignment->title,
                'due_at' => $assignment->due_at,
                'total_students' => $total,
            ],
            'statistics' => [
                'submitted' => $submittedCount,
                'not_submitted' => $notSubmittedCount,
                'late_submissions' => $lateCount,
                'submission_rate' => $total > 0 ? round(($submittedCount / $total) * 100, 2) : 0,
            ],
            // paginated view for existing UI
            'students' => $paginatedData,
            // NEW: convenience arrays for frontend tabs (full lists, not paginated)
            'submitted' => $submittedList,
            'not_submitted' => $notSubmittedList,
            'pagination' => [
                'total' => $total,
                'page' => $page,
                'per_page' => $perPage,
                'pages' => (int) ceil($total / max(1, $perPage)),
            ],
        ],
    ], 200);
}

/**
 * Helper method to resolve assignment by key (id, uuid, or slug)
 */
private function resolveAssignment($key)
{
    $q = DB::table('assignments')->whereNull('deleted_at');
    
    if (ctype_digit((string)$key)) {
        $q->where('id', (int)$key);
    } elseif (Str::isUuid($key)) {
        $q->where('uuid', $key);
    } elseif (\Illuminate\Support\Facades\Schema::hasColumn('assignments', 'slug')) {
        $q->where('slug', $key);
    } else {
        return null;
    }
    
    return $q->first();
}

/**
 * Helper method to decode JSON fields
 */
private function decodeJsonField($jsonString)
{
    if (empty($jsonString)) {
        return [];
    }
    
    try {
        $decoded = json_decode($jsonString, true);
        return is_array($decoded) ? $decoded : [];
    } catch (\Throwable $e) {
        return [];
    }
}
/**
 * Export submitted students list as CSV
 */
public function exportSubmittedStudentsCSV(Request $r, string $assignmentKey)
{
    if ($res = $this->requireRole($r, ['admin','super_admin','instructor'])) return $res;

    // Resolve assignment
    $assignment = $this->resolveAssignment($assignmentKey);
    if (!$assignment) {
        return response()->json(['error' => 'Assignment not found'], 404);
    }

    $batchId = $assignment->batch_id;
    if (!$batchId) {
        return response()->json(['error' => 'Assignment not linked to a batch'], 422);
    }

    // Get assigned students
    $batchStudents = DB::table('users')
        ->where('users.role', 'student')
        ->whereNull('users.deleted_at')
        ->join('batch_students', function($join) use ($batchId) {
            $join->on('users.id', '=', 'batch_students.user_id')
                 ->where('batch_students.batch_id', '=', $batchId);
        })
        ->whereNull('batch_students.deleted_at')
        ->select(
            'users.id as student_id',
            'users.name as student_name',
            'users.email as student_email',
            'users.uuid as student_uuid',
            'batch_students.created_at as enrolled_at'
        )
        ->get();

    if (!$batchStudents || $batchStudents->isEmpty()) {
        return response()->json(['error' => 'No assigned students found'], 404);
    }

    // Get submissions for this assignment
    $submissions = DB::table('assignment_submissions')
        ->where('assignment_id', $assignment->id)
        ->whereNull('deleted_at')
        ->select('student_id', 'attempt_no', 'submitted_at', 'status', 'is_late')
        ->orderBy('attempt_no', 'desc')
        ->get()
        ->groupBy('student_id');

    // Filter only submitted students
    $submittedStudents = $batchStudents->filter(function($student) use ($submissions) {
        $studentId = $student->student_id;
        $studentSubmissions = $submissions->get($studentId, collect());
        return $studentSubmissions->isNotEmpty();
    });

    if ($submittedStudents->isEmpty()) {
        return response()->json(['error' => 'No submitted students found'], 404);
    }

    // Prepare CSV data
    $csvData = [];
    $csvData[] = ['Student ID', 'Name', 'Email', 'Submission Date', 'Attempt No', 'Status', 'Is Late', 'Total Submissions'];

    foreach ($submittedStudents as $student) {
        $studentId = $student->student_id;
        $studentSubmissions = $submissions->get($studentId, collect());
        $latestSubmission = $studentSubmissions->first();
        $totalSubmissions = $studentSubmissions->count();

        $csvData[] = [
            $student->student_id,
            $student->student_name,
            $student->student_email,
            $latestSubmission ? $latestSubmission->submitted_at : 'N/A',
            $latestSubmission ? $latestSubmission->attempt_no : 'N/A',
            $latestSubmission ? $latestSubmission->status : 'N/A',
            $latestSubmission ? ($latestSubmission->is_late ? 'Yes' : 'No') : 'N/A',
            $totalSubmissions
        ];
    }

    // Generate CSV file
    $fileName = "submitted_students_assignment_{$assignment->id}_" . date('Y-m-d_H-i-s') . '.csv';
    
    return $this->generateCSVResponse($csvData, $fileName);
}

/**
 * Export unsubmitted students list as CSV
 */
public function exportUnsubmittedStudentsCSV(Request $r, string $assignmentKey)
{
    if ($res = $this->requireRole($r, ['admin','super_admin','instructor'])) return $res;

    // Resolve assignment
    $assignment = $this->resolveAssignment($assignmentKey);
    if (!$assignment) {
        return response()->json(['error' => 'Assignment not found'], 404);
    }

    $batchId = $assignment->batch_id;
    if (!$batchId) {
        return response()->json(['error' => 'Assignment not linked to a batch'], 422);
    }

    // Get assigned students
    $batchStudents = DB::table('users')
        ->where('users.role', 'student')
        ->whereNull('users.deleted_at')
        ->join('batch_students', function($join) use ($batchId) {
            $join->on('users.id', '=', 'batch_students.user_id')
                 ->where('batch_students.batch_id', '=', $batchId);
        })
        ->whereNull('batch_students.deleted_at')
        ->select(
            'users.id as student_id',
            'users.name as student_name',
            'users.email as student_email',
            'users.uuid as student_uuid',
            'batch_students.created_at as enrolled_at'
        )
        ->get();

    if (!$batchStudents || $batchStudents->isEmpty()) {
        return response()->json(['error' => 'No assigned students found'], 404);
    }

    // Get submissions for this assignment
    $submissions = DB::table('assignment_submissions')
        ->where('assignment_id', $assignment->id)
        ->whereNull('deleted_at')
        ->select('student_id')
        ->get()
        ->pluck('student_id')
        ->toArray();

    // Filter only unsubmitted students
    $unsubmittedStudents = $batchStudents->filter(function($student) use ($submissions) {
        return !in_array($student->student_id, $submissions);
    });

    if ($unsubmittedStudents->isEmpty()) {
        return response()->json(['error' => 'No unsubmitted students found'], 404);
    }

    // Prepare CSV data
    $csvData = [];
    $csvData[] = ['Student ID', 'Name', 'Email', 'Enrollment Date', 'Status'];

    foreach ($unsubmittedStudents as $student) {
        $csvData[] = [
            $student->student_id,
            $student->student_name,
            $student->student_email,
            $student->enrolled_at,
            'Not Submitted'
        ];
    }

    // Generate CSV file
    $fileName = "unsubmitted_students_assignment_{$assignment->id}_" . date('Y-m-d_H-i-s') . '.csv';
    
    return $this->generateCSVResponse($csvData, $fileName);
}

/**
 * Helper method to generate CSV response
 */
private function generateCSVResponse(array $data, string $fileName)
{
    $headers = [
        'Content-Type' => 'text/csv',
        'Content-Disposition' => 'attachment; filename="' . $fileName . '"',
    ];

    $callback = function() use ($data) {
        $file = fopen('php://output', 'w');
        
        // Add BOM for UTF-8 to help with Excel compatibility
        fwrite($file, "\xEF\xBB\xBF");
        
        foreach ($data as $row) {
            fputcsv($file, $row);
        }
        fclose($file);
    };

    return response()->stream($callback, 200, $headers);
}

/**
 * Combined export - both submitted and unsubmitted in separate sheets (single CSV with sections)
 */
public function exportAllStudentsStatusCSV(Request $r, string $assignmentKey)
{
    if ($res = $this->requireRole($r, ['admin','super_admin','instructor'])) return $res;

    // Resolve assignment
    $assignment = $this->resolveAssignment($assignmentKey);
    if (!$assignment) {
        return response()->json(['error' => 'Assignment not found'], 404);
    }

    $batchId = $assignment->batch_id;
    if (!$batchId) {
        return response()->json(['error' => 'Assignment not linked to a batch'], 422);
    }

    // Get assigned students
    $batchStudents = DB::table('users')
        ->where('users.role', 'student')
        ->whereNull('users.deleted_at')
        ->join('batch_students', function($join) use ($batchId) {
            $join->on('users.id', '=', 'batch_students.user_id')
                 ->where('batch_students.batch_id', '=', $batchId);
        })
        ->whereNull('batch_students.deleted_at')
        ->select(
            'users.id as student_id',
            'users.name as student_name',
            'users.email as student_email',
            'users.uuid as student_uuid',
            'batch_students.created_at as enrolled_at'
        )
        ->get();

    if (!$batchStudents || $batchStudents->isEmpty()) {
        return response()->json(['error' => 'No assigned students found'], 404);
    }

    // Get submissions for this assignment
    $submissions = DB::table('assignment_submissions')
        ->where('assignment_id', $assignment->id)
        ->whereNull('deleted_at')
        ->select('student_id', 'attempt_no', 'submitted_at', 'status', 'is_late')
        ->orderBy('attempt_no', 'desc')
        ->get()
        ->groupBy('student_id');

    // Prepare combined CSV data
    $csvData = [];

    // Assignment info section
    $csvData[] = ['Assignment Submission Report'];
    $csvData[] = ['Assignment:', $assignment->title];
    $csvData[] = ['Assignment ID:', $assignment->id];
    $csvData[] = ['Due Date:', $assignment->due_at];
    $csvData[] = ['Report Generated:', now()->toDateTimeString()];
    $csvData[] = []; // Empty row

    // Submitted students section
    $csvData[] = ['SUBMITTED STUDENTS'];
    $csvData[] = ['Student ID', 'Name', 'Email', 'Submission Date', 'Latest Attempt', 'Status', 'Is Late', 'Total Submissions'];

    $submittedCount = 0;
    foreach ($batchStudents as $student) {
        $studentId = $student->student_id;
        $studentSubmissions = $submissions->get($studentId, collect());
        
        if ($studentSubmissions->isNotEmpty()) {
            $latestSubmission = $studentSubmissions->first();
            $totalSubmissions = $studentSubmissions->count();
            $submittedCount++;

            $csvData[] = [
                $student->student_id,
                $student->student_name,
                $student->student_email,
                $latestSubmission->submitted_at,
                $latestSubmission->attempt_no,
                $latestSubmission->status,
                $latestSubmission->is_late ? 'Yes' : 'No',
                $totalSubmissions
            ];
        }
    }

    $csvData[] = []; // Empty row
    $csvData[] = ['Total Submitted Students:', $submittedCount];
    $csvData[] = []; // Empty row

    // Unsubmitted students section
    $csvData[] = ['UNSUBMITTED STUDENTS'];
    $csvData[] = ['Student ID', 'Name', 'Email', 'Enrollment Date'];

    $unsubmittedCount = 0;
    foreach ($batchStudents as $student) {
        $studentId = $student->student_id;
        $studentSubmissions = $submissions->get($studentId, collect());
        
        if ($studentSubmissions->isEmpty()) {
            $unsubmittedCount++;
            $csvData[] = [
                $student->student_id,
                $student->student_name,
                $student->student_email,
                $student->enrolled_at
            ];
        }
    }

    $csvData[] = []; // Empty row
    $csvData[] = ['Total Unsubmitted Students:', $unsubmittedCount];
    $csvData[] = ['Total Assigned Students:', $batchStudents->count()];
    $csvData[] = ['Submission Rate:', round(($submittedCount / $batchStudents->count()) * 100, 2) . '%'];

    // Generate CSV file
    $fileName = "assignment_{$assignment->id}_submission_report_" . date('Y-m-d_H-i-s') . '.csv';
    
    return $this->generateCSVResponse($csvData, $fileName);
}
/**
 * Get student-wise submitted documents for an assignment
 * Returns all submissions with their attachments organized by student
 */
public function getAssignmentSubmissionsWithDocuments(Request $r, string $assignmentKey)
{
    if ($res = $this->requireRole($r, ['admin','superadmin','instructor'])) return $res;

    // Resolve assignment
    $assignment = $this->resolveAssignment($assignmentKey);
    if (!$assignment) {
        return response()->json(['error' => 'Assignment not found'], 404);
    }

    $batchId = $assignment->batch_id;
    if (!$batchId) {
        return response()->json(['error' => 'Assignment not linked to a batch'], 422);
    }

    $page = max(1, (int) $r->query('page', 1));
    $perPage = min(100, max(1, (int) $r->query('per_page', 50)));
    $offset = ($page - 1) * $perPage;

    // Get all students in the batch
    $studentsQuery = DB::table('users')
        ->where('users.role', 'student')
        ->whereNull('users.deleted_at')
        ->join('batch_students', function($join) use ($batchId) {
            $join->on('users.id', '=', 'batch_students.user_id')
                 ->where('batch_students.batch_id', '=', $batchId);
        })
        ->whereNull('batch_students.deleted_at')
        ->select(
            'users.id as student_id',
            'users.name as student_name',
            'users.email as student_email',
            'users.uuid as student_uuid'
        );

    // Apply student name filter if provided
    if ($r->filled('student_name')) {
        $studentsQuery->where('users.name', 'like', '%' . $r->query('student_name') . '%');
    }

    if ($r->filled('student_email')) {
        $studentsQuery->where('users.email', 'like', '%' . $r->query('student_email') . '%');
    }

    $totalStudents = $studentsQuery->count();
    $students = $studentsQuery->offset($offset)
                             ->limit($perPage)
                             ->get();

    // Get all submissions for this assignment with attachments
    $submissions = DB::table('assignment_submissions')
        ->where('assignment_id', $assignment->id)
        ->whereNull('deleted_at')
        ->select('*')
        ->get()
        ->groupBy('student_id');

    // Prepare student-wise submission data
    $studentSubmissions = $students->map(function($student) use ($submissions, $assignment) {
        $studentId = $student->student_id;
        $studentSubmissions = $submissions->get($studentId, collect());
        
        $submissionData = $studentSubmissions->map(function($submission) {
            // Decode attachments
            $attachments = [];
            if (!empty($submission->attachments_json)) {
                try {
                    $decoded = json_decode($submission->attachments_json, true);
                    if (is_array($decoded)) {
                        $attachments = $decoded;
                    }
                } catch (\Throwable $e) {
                    $attachments = [];
                }
            }

            // Decode metadata for grading details
            $gradingDetails = [];
            if (!empty($submission->metadata)) {
                try {
                    $metadata = json_decode($submission->metadata, true);
                    $gradingDetails = $metadata['grading_details'] ?? [];
                } catch (\Throwable $e) {
                    $gradingDetails = [];
                }
            }

            return [
                'submission_id' => $submission->id,
                'submission_uuid' => $submission->uuid,
                'attempt_no' => $submission->attempt_no,
                'status' => $submission->status,
                'submitted_at' => $submission->submitted_at,
                'submitted_at_formatted' => $submission->submitted_at ? 
                    Carbon::parse($submission->submitted_at)->format('M j, Y g:i A') : null,
                'is_late' => (bool)$submission->is_late,
                'late_minutes' => $submission->late_minutes,
                'content_text' => $submission->content_text,
                'content_html' => $submission->content_html,
                'link_url' => $submission->link_url,
                'repo_url' => $submission->repo_url,
                'attachments' => $attachments,
                'total_attachments' => count($attachments),
                'total_marks' => $submission->total_marks,
                'grade_letter' => $submission->grade_letter,
                'graded_at' => $submission->graded_at,
                'graded_at_formatted' => $submission->graded_at ? 
                    Carbon::parse($submission->graded_at)->format('M j, Y g:i A') : null,
                'grader_note' => $submission->grader_note,
                'feedback_html' => $submission->feedback_html,
                'grading_details' => $gradingDetails,
            ];
        })->sortByDesc('attempt_no')->values();

        return [
            'student_id' => $student->student_id,
            'student_name' => $student->student_name,
            'student_email' => $student->student_email,
            'student_uuid' => $student->student_uuid,
            'has_submitted' => $studentSubmissions->isNotEmpty(),
            'total_submissions' => $studentSubmissions->count(),
            'latest_submission' => $studentSubmissions->first(),
            'all_submissions' => $studentSubmissions,
            'total_documents' => $studentSubmissions->sum('total_attachments'),
        ];
    });

    // Apply sorting
    $sortBy = $r->query('sort_by', 'student_name');
    $sortOrder = $r->query('sort_order', 'asc');
    
    $studentSubmissions = $studentSubmissions->sortBy(function($item) use ($sortBy) {
        // Handle nested sorting
        if ($sortBy === 'latest_submission.submitted_at') {
            return $item['latest_submission']['submitted_at'] ?? '';
        } elseif ($sortBy === 'total_submissions') {
            return $item['total_submissions'];
        } elseif ($sortBy === 'total_documents') {
            return $item['total_documents'];
        } else {
            return $item[$sortBy] ?? '';
        }
    }, SORT_REGULAR, $sortOrder === 'desc');

    // Statistics
    $submittedCount = $studentSubmissions->where('has_submitted', true)->count();
    $totalDocuments = $studentSubmissions->sum('total_documents');
    $totalSubmissions = $studentSubmissions->sum('total_submissions');
    $gradedCount = $studentSubmissions->where('latest_submission.total_marks', '!==', null)->count();

    return response()->json([
        'message' => 'Student submissions with documents fetched successfully',
        'data' => [
            'assignment' => [
                'id' => $assignment->id,
                'title' => $assignment->title,
                'due_at' => $assignment->due_at,
                'max_marks' => $assignment->max_marks,
                'total_students' => $totalStudents,
            ],
            'statistics' => [
                'total_students' => $studentSubmissions->count(),
                'submitted_count' => $submittedCount,
                'not_submitted_count' => $studentSubmissions->count() - $submittedCount,
                'graded_count' => $gradedCount,
                'total_documents' => $totalDocuments,
                'total_submissions' => $totalSubmissions,
                'submission_rate' => $studentSubmissions->count() > 0 ? 
                    round(($submittedCount / $studentSubmissions->count()) * 100, 2) : 0,
            ],
            'student_submissions' => $studentSubmissions->values(),
            'pagination' => [
                'total' => $totalStudents,
                'page' => $page,
                'per_page' => $perPage,
                'pages' => (int) ceil($totalStudents / max(1, $perPage)),
            ],
        ],
    ], 200);
}

/**
 * Get detailed submission documents for a specific student in an assignment
 */
public function getStudentAssignmentDocuments(Request $r, string $assignmentKey, string $studentKey)
{
    if ($res = $this->requireRole($r, ['admin','superadmin','instructor'])) return $res;

    // Resolve assignment
    $assignment = $this->resolveAssignment($assignmentKey);
    if (!$assignment) {
        return response()->json(['error' => 'Assignment not found'], 404);
    }

    // Resolve student (by id, uuid, or email)
    $studentQuery = DB::table('users')
        ->where('users.role', 'student')
        ->whereNull('users.deleted_at');

    if (ctype_digit($studentKey)) {
        $studentQuery->where('users.id', (int)$studentKey);
    } elseif (Str::isUuid($studentKey)) {
        $studentQuery->where('users.uuid', $studentKey);
    } else {
        $studentQuery->where('users.email', $studentKey);
    }

    $student = $studentQuery->first();
    if (!$student) {
        return response()->json(['error' => 'Student not found'], 404);
    }

    // Check if student is in the assignment's batch
    $batchStudent = DB::table('batch_students')
        ->where('user_id', $student->id)
        ->where('batch_id', $assignment->batch_id)
        ->whereNull('deleted_at')
        ->first();

    if (!$batchStudent) {
        return response()->json(['error' => 'Student is not enrolled in this assignment batch'], 403);
    }

    // Get all submissions for this student and assignment
    $submissions = DB::table('assignment_submissions')
        ->where('assignment_id', $assignment->id)
        ->where('student_id', $student->id)
        ->whereNull('deleted_at')
        ->orderBy('attempt_no', 'desc')
        ->get();

    // Process submissions with detailed attachment info
    $submissionDetails = $submissions->map(function($submission) {
        // Decode attachments
        $attachments = [];
        if (!empty($submission->attachments_json)) {
            try {
                $decoded = json_decode($submission->attachments_json, true);
                if (is_array($decoded)) {
                    $attachments = $decoded;
                }
            } catch (\Throwable $e) {
                $attachments = [];
            }
        }

        // Decode metadata for grading details
        $gradingDetails = [];
        $flags = [];
        $metadata = [];

        if (!empty($submission->metadata)) {
            try {
                $metadata = json_decode($submission->metadata, true);
                $gradingDetails = $metadata['grading_details'] ?? [];
            } catch (\Throwable $e) {
                $metadata = [];
            }
        }

        if (!empty($submission->flags_json)) {
            try {
                $flags = json_decode($submission->flags_json, true);
            } catch (\Throwable $e) {
                $flags = [];
            }
        }

        // Categorize attachments by type
        $documentAttachments = [];
        $imageAttachments = [];
        $codeAttachments = [];
        $archiveAttachments = [];
        $videoAttachments = [];

        foreach ($attachments as $attachment) {
            $ext = pathinfo($attachment['name'], PATHINFO_EXTENSION);
            $mime = $attachment['mime'] ?? '';
            
            if (in_array(strtolower($ext), ['pdf', 'doc', 'docx', 'txt', 'pptx', 'xlsx'])) {
                $documentAttachments[] = $attachment;
            } elseif (in_array(strtolower($ext), ['jpg', 'jpeg', 'png', 'gif', 'svg', 'bmp'])) {
                $imageAttachments[] = $attachment;
            } elseif (in_array(strtolower($ext), ['js', 'py', 'java', 'html', 'css', 'cpp', 'c', 'php', 'rb'])) {
                $codeAttachments[] = $attachment;
            } elseif (in_array(strtolower($ext), ['zip', 'rar', '7z', 'tar', 'gz'])) {
                $archiveAttachments[] = $attachment;
            } elseif (in_array(strtolower($ext), ['mp4', 'avi', 'mov', 'wmv', 'flv'])) {
                $videoAttachments[] = $attachment;
            } else {
                $documentAttachments[] = $attachment; // default to documents
            }
        }

        return [
            'submission_id' => $submission->id,
            'submission_uuid' => $submission->uuid,
            'attempt_no' => $submission->attempt_no,
            'status' => $submission->status,
            'submitted_at' => $submission->submitted_at,
            'submitted_at_formatted' => $submission->submitted_at ? 
                Carbon::parse($submission->submitted_at)->format('M j, Y g:i A') : null,
            'is_late' => (bool)$submission->is_late,
            'late_minutes' => $submission->late_minutes,
            'late_days' => $submission->late_minutes ? ceil($submission->late_minutes / (24 * 60)) : 0,
            'content_text' => $submission->content_text,
            'content_html' => $submission->content_html,
            'link_url' => $submission->link_url,
            'repo_url' => $submission->repo_url,
            
            // Attachments organized by type
            'all_attachments' => $attachments,
            'document_attachments' => $documentAttachments,
            'image_attachments' => $imageAttachments,
            'code_attachments' => $codeAttachments,
            'archive_attachments' => $archiveAttachments,
            'video_attachments' => $videoAttachments,
            
            // Attachment counts
            'total_attachments' => count($attachments),
            'document_count' => count($documentAttachments),
            'image_count' => count($imageAttachments),
            'code_count' => count($codeAttachments),
            'archive_count' => count($archiveAttachments),
            'video_count' => count($videoAttachments),
            
            // Grading information
            'total_marks' => $submission->total_marks,
            'grade_letter' => $submission->grade_letter,
            'graded_at' => $submission->graded_at,
            'graded_at_formatted' => $submission->graded_at ? 
                Carbon::parse($submission->graded_at)->format('M j, Y g:i A') : null,
            'graded_by' => $submission->graded_by,
            'grader_note' => $submission->grader_note,
            'feedback_html' => $submission->feedback_html,
            'feedback_visible' => (bool)$submission->feedback_visible,
            
            // Additional details
            'grading_details' => $gradingDetails,
            'flags' => $flags,
            'metadata' => $metadata,
            'submitted_ip' => $submission->submitted_ip,
            'version_no' => $submission->version_no,
        ];
    });

    // make sure submissionDetails is a collection (it should be)
    if (!($submissionDetails instanceof \Illuminate\Support\Collection)) {
        $submissionDetails = collect($submissionDetails);
    }

    // compute statistics safely
    $total_submissions = $submissionDetails->count();
    $total_attachments = $submissionDetails->sum('total_attachments');
    $total_documents = $submissionDetails->sum('document_count');
    $total_images = $submissionDetails->sum('image_count');
    $total_code_files = $submissionDetails->sum('code_count');
    $total_archives = $submissionDetails->sum('archive_count');
    $total_videos = $submissionDetails->sum('video_count');
    $latest_attempt = $submissionDetails->max('attempt_no');
    $graded_submissions = $submissionDetails->filter(function($s){
        return isset($s['total_marks']) && $s['total_marks'] !== null;
    })->count();

    return response()->json([
        'message' => 'Student assignment documents fetched successfully',
        'data' => [
            // convenience top-level uuids for frontend redirecting
            'assignment_uuid' => $assignment->uuid ?? null,
            'student_uuid' => $student->uuid ?? null,

            'assignment' => [
                'id' => $assignment->id,
                'uuid' => $assignment->uuid ?? null,
                'title' => $assignment->title ?? 'Unknown Title',
                'due_at' => $assignment->due_at ?? null,
                'total_marks' => $assignment->total_marks ?? null, // Changed from max_marks to total_marks
            ],
            'student' => [
                'id' => $student->id,
                'uuid' => $student->uuid ?? null,
                'name' => $student->name ?? 'Unknown Student',
                'email' => $student->email ?? null,
            ],
            'submissions' => $submissionDetails,
            'statistics' => [
                'total_submissions' => $total_submissions,
                'total_attachments' => $total_attachments,
                'total_documents' => $total_documents,
                'total_images' => $total_images,
                'total_code_files' => $total_code_files,
                'total_archives' => $total_archives,
                'total_videos' => $total_videos,
                'latest_attempt' => $latest_attempt,
                'graded_submissions' => $graded_submissions,
            ],
        ],
    ], 200);
}

/**
 * Download all documents for a specific submission as ZIP
 */
public function downloadSubmissionDocuments(Request $r, string $submissionKey)
{
    if ($res = $this->requireRole($r, ['admin','superadmin','instructor'])) return $res;

    $submission = $this->resolveSubmission($submissionKey);
    if (!$submission) {
        return response()->json(['error' => 'Submission not found'], 404);
    }

    // Get student and assignment info
    $student = DB::table('users')
        ->where('id', $submission->student_id)
        ->select('name', 'email')
        ->first();

    $assignment = DB::table('assignments')
        ->where('id', $submission->assignment_id)
        ->select('title')
        ->first();

    if (!$student || !$assignment) {
        return response()->json(['error' => 'Student or assignment not found'], 404);
    }

    // Decode attachments
    $attachments = [];
    if (!empty($submission->attachments_json)) {
        try {
            $decoded = json_decode($submission->attachments_json, true);
            if (is_array($decoded)) {
                $attachments = $decoded;
            }
        } catch (\Throwable $e) {
            $attachments = [];
        }
    }

    if (empty($attachments)) {
        return response()->json(['error' => 'No documents found for this submission'], 404);
    }

    try {
        // Create ZIP file
        $zipFileName = "submission_{$submission->id}_documents.zip";
        $zipPath = storage_path("app/temp/{$zipFileName}");
        
        // Ensure temp directory exists
        if (!file_exists(dirname($zipPath))) {
            mkdir(dirname($zipPath), 0755, true);
        }

        $zip = new \ZipArchive();
        if ($zip->open($zipPath, \ZipArchive::CREATE | \ZipArchive::OVERWRITE) === TRUE) {
            foreach ($attachments as $attachment) {
                if (!empty($attachment['path']) && Storage::disk('public')->exists($attachment['path'])) {
                    $fileContent = Storage::disk('public')->get($attachment['path']);
                    $safeFileName = $this->sanitizeFileName($attachment['name']);
                    $zip->addFromString($safeFileName, $fileContent);
                }
            }
            $zip->close();
        }

        // Add info file
        $infoContent = "Submission Documents\n";
        $infoContent .= "===================\n";
        $infoContent .= "Student: {$student->name} ({$student->email})\n";
        $infoContent .= "Assignment: {$assignment->title}\n";
        $infoContent .= "Submission ID: {$submission->id}\n";
        $infoContent .= "Attempt: {$submission->attempt_no}\n";
        $infoContent .= "Submitted: {$submission->submitted_at}\n";
        $infoContent .= "Total Documents: " . count($attachments) . "\n";
        $infoContent .= "\nDocuments included:\n";
        foreach ($attachments as $index => $attachment) {
            $infoContent .= ($index + 1) . ". {$attachment['name']} ({$attachment['mime']}) - " . 
                           $this->formatBytes($attachment['size']) . "\n";
        }

        // Add info file to ZIP
        $zip->open($zipPath);
        $zip->addFromString('README.txt', $infoContent);
        $zip->close();

        // Return download response
        return response()->download($zipPath, $zipFileName)->deleteFileAfterSend(true);

    } catch (\Throwable $e) {
        Log::error('Download submission documents error: ' . $e->getMessage());
        return response()->json(['error' => 'Failed to create download package'], 500);
    }
}

/**
 * Helper method to sanitize file names
 */
private function sanitizeFileName($filename)
{
    // Remove any path information
    $filename = basename($filename);
    // Replace spaces with underscores
    $filename = str_replace(' ', '_', $filename);
    // Remove any non-alphanumeric characters except dots, underscores, and hyphens
    $filename = preg_replace('/[^a-zA-Z0-9._-]/', '', $filename);
    return $filename;
}

/**
 * Helper method to format bytes to human readable
 */
private function formatBytes($bytes, $precision = 2)
{
    $units = ['B', 'KB', 'MB', 'GB', 'TB'];
    $bytes = max($bytes, 0);
    $pow = floor(($bytes ? log($bytes) : 0) / log(1024));
    $pow = min($pow, count($units) - 1);
    $bytes /= pow(1024, $pow);
    return round($bytes, $precision) . ' ' . $units[$pow];
}
/**
 * Grade a submission with automatic late penalty calculation
 */
public function gradeSubmission(Request $r, string $submissionKey)
{
    if ($res = $this->requireRole($r, ['admin','superadmin','instructor'])) return $res;

    $validator = Validator::make($r->all(), [
        'marks' => 'required|numeric|min:0',
        'grade_letter' => 'nullable|string|max:10',
        'grader_note' => 'nullable|string',
        'feedback_html' => 'nullable|string',
        'feedback_visible' => 'boolean',
        'apply_late_penalty' => 'boolean', // Whether to auto-apply late penalty
    ]);

    if ($validator->fails()) {
        return response()->json(['message' => 'Validation failed', 'errors' => $validator->errors()], 422);
    }

    // Resolve submission
    $submission = $this->resolveSubmission($submissionKey);
    if (!$submission) {
        return response()->json(['error' => 'Submission not found'], 404);
    }

    // Get assignment details
    $assignment = DB::table('assignments')
        ->where('id', $submission->assignment_id)
        ->whereNull('deleted_at')
        ->first();

    if (!$assignment) {
        return response()->json(['error' => 'Assignment not found'], 404);
    }

    try {
        $actor = $this->actor($r);
        $givenMarks = (float) $r->input('marks');
        $applyLatePenalty = $r->input('apply_late_penalty', true);
        
        // Calculate late penalty if applicable
        $penaltyDetails = $this->calculateLatePenalty($assignment, $submission, $givenMarks, $applyLatePenalty);
        
        $updateData = [
            'total_marks' => $penaltyDetails['final_marks'],
            'grade_letter' => $r->input('grade_letter'),
            'graded_at' => Carbon::now()->toDateTimeString(),
            'graded_by' => $actor['id'],
            'grader_note' => $r->input('grader_note'),
            'feedback_html' => $r->input('feedback_html'),
            'feedback_visible' => $r->input('feedback_visible', true),
            'updated_at' => Carbon::now()->toDateTimeString(),
        ];

        // Store penalty details in metadata
        $metadata = [];
        if (!empty($submission->metadata)) {
            try {
                $metadata = json_decode($submission->metadata, true) ?: [];
            } catch (\Throwable $e) {
                $metadata = [];
            }
        }
        
        $metadata['grading_details'] = [
            'given_marks' => $givenMarks,
            'late_penalty_applied' => $penaltyDetails['penalty_applied'],
            'late_penalty_amount' => $penaltyDetails['penalty_amount'],
            'late_penalty_percentage' => $penaltyDetails['penalty_percentage'],
            'final_marks_after_penalty' => $penaltyDetails['final_marks'],
            'graded_by' => $actor['id'],
            'graded_by_role' => $actor['role'],
            'graded_at' => Carbon::now()->toDateTimeString(),
        ];

        $updateData['metadata'] = json_encode($metadata);

        DB::table('assignment_submissions')
            ->where('id', $submission->id)
            ->update($updateData);

        // Return the updated submission with penalty details
        $updatedSubmission = $this->resolveSubmission($submissionKey);
        $updatedSubmission->penalty_details = $penaltyDetails;

        return response()->json([
            'message' => 'Submission graded successfully',
            'data' => $updatedSubmission,
        ], 200);

    } catch (\Throwable $e) {
        Log::error('grade submission error: '.$e->getMessage(), ['submission' => $submission]);
        return response()->json(['error' => 'Failed to grade submission'], 500);
    }
}

/**
 * Calculate late penalty for a submission
 */
private function calculateLatePenalty($assignment, $submission, $givenMarks, $applyLatePenalty)
{
    $penaltyApplied = false;
    $penaltyAmount = 0;
    $penaltyPercentage = 0;
    $finalMarks = $givenMarks;

    // Check if submission is late and penalty should be applied
    if ($applyLatePenalty && $submission->is_late && $submission->late_minutes > 0) {
        
        // Get assignment penalty settings
        $penaltySettings = $this->getAssignmentPenaltySettings($assignment);
        
        if ($penaltySettings['late_penalty_enabled']) {
            $penaltyApplied = true;
            
            // Calculate penalty based on type
            switch ($penaltySettings['penalty_type']) {
                case 'percentage_per_day':
                    $penaltyPercentage = $this->calculatePercentagePenalty($submission, $penaltySettings);
                    $penaltyAmount = ($givenMarks * $penaltyPercentage) / 100;
                    break;
                    
                case 'fixed_per_day':
                    $penaltyAmount = $this->calculateFixedPenalty($submission, $penaltySettings);
                    break;
                    
                case 'percentage_total':
                    $penaltyPercentage = $penaltySettings['penalty_value'];
                    $penaltyAmount = ($givenMarks * $penaltyPercentage) / 100;
                    break;
                    
                case 'fixed_total':
                    $penaltyAmount = $penaltySettings['penalty_value'];
                    break;
            }
            
            // Ensure penalty doesn't make marks negative
            $finalMarks = max(0, $givenMarks - $penaltyAmount);
        }
    }

    return [
        'penalty_applied' => $penaltyApplied,
        'penalty_amount' => round($penaltyAmount, 2),
        'penalty_percentage' => round($penaltyPercentage, 2),
        'final_marks' => round($finalMarks, 2),
        'given_marks' => round($givenMarks, 2),
    ];
}

/**
 * Get penalty settings for an assignment
 */
private function getAssignmentPenaltySettings($assignment)
{
    // Default settings
    $defaultSettings = [
        'late_penalty_enabled' => false,
        'penalty_type' => 'percentage_per_day', // percentage_per_day, fixed_per_day, percentage_total, fixed_total
        'penalty_value' => 0,
        'max_penalty' => null,
        'grace_period_minutes' => 0,
    ];

    // Try to get settings from assignment metadata/flags
    $settings = $defaultSettings;
    
    if (!empty($assignment->metadata)) {
        try {
            $metadata = json_decode($assignment->metadata, true);
            if (isset($metadata['late_penalty_settings'])) {
                $settings = array_merge($settings, $metadata['late_penalty_settings']);
            }
        } catch (\Throwable $e) {
            // Use default settings
        }
    }

    // Also check flags_json
    if (!empty($assignment->flags_json)) {
        try {
            $flags = json_decode($assignment->flags_json, true);
            if (isset($flags['late_penalty_enabled'])) {
                $settings['late_penalty_enabled'] = (bool)$flags['late_penalty_enabled'];
            }
            if (isset($flags['penalty_type'])) {
                $settings['penalty_type'] = $flags['penalty_type'];
            }
            if (isset($flags['penalty_value'])) {
                $settings['penalty_value'] = (float)$flags['penalty_value'];
            }
        } catch (\Throwable $e) {
            // Use existing settings
        }
    }

    return $settings;
}

/**
 * Calculate percentage-based penalty
 */
private function calculatePercentagePenalty($submission, $settings)
{
    $lateDays = ceil($submission->late_minutes / (24 * 60)); // Convert minutes to days (rounded up)
    $penaltyPercentage = $lateDays * $settings['penalty_value'];
    
    // Apply max penalty limit if set
    if (!empty($settings['max_penalty'])) {
        $penaltyPercentage = min($penaltyPercentage, $settings['max_penalty']);
    }
    
    return $penaltyPercentage;
}

/**
 * Calculate fixed amount penalty
 */
private function calculateFixedPenalty($submission, $settings)
{
    $lateDays = ceil($submission->late_minutes / (24 * 60)); // Convert minutes to days (rounded up)
    $penaltyAmount = $lateDays * $settings['penalty_value'];
    
    // Apply max penalty limit if set
    if (!empty($settings['max_penalty'])) {
        $penaltyAmount = min($penaltyAmount, $settings['max_penalty']);
    }
    
    return $penaltyAmount;
}
/**
 * Get all students' marks for an assignment with penalty details
 */
/**
 * Get all students' marks for an assignment with penalty details
 */
public function getAssignmentMarks(Request $r, string $assignmentKey)
{
    if ($res = $this->requireRole($r, ['admin','superadmin','instructor'])) return $res;

    $assignment = $this->resolveAssignment($assignmentKey);
    if (!$assignment) {
        return response()->json(['error' => 'Assignment not found'], 404);
    }

    $batchId = $assignment->batch_id;
    if (!$batchId) {
        return response()->json(['error' => 'Assignment not linked to a batch'], 422);
    }

    // Get penalty settings
    $penaltySettings = $this->getAssignmentPenaltySettings($assignment);

    // Get all students in the batch
    $students = DB::table('users')
        ->where('users.role', 'student')
        ->whereNull('users.deleted_at')
        ->join('batch_students', function($join) use ($batchId) {
            $join->on('users.id', '=', 'batch_students.user_id')
                 ->where('batch_students.batch_id', '=', $batchId);
        })
        ->whereNull('batch_students.deleted_at')
        ->select(
            'users.id as student_id',
            'users.name as student_name',
            'users.email as student_email',
            'users.uuid as student_uuid'
        )
        ->get();

    // Get all submissions for this assignment
    $submissions = DB::table('assignment_submissions')
        ->where('assignment_id', $assignment->id)
        ->whereNull('deleted_at')
        ->select('*')
        ->get()
        ->groupBy('student_id');

    // Prepare marks data
    $marksData = $students->map(function($student) use ($submissions, $assignment, $penaltySettings) {
        $studentId = $student->student_id;
        $studentSubmissions = $submissions->get($studentId, collect());
        
        $latestSubmission = $studentSubmissions->sortByDesc('attempt_no')->first();
        
        $marksInfo = [
            'student_id' => $student->student_id,
            'student_name' => $student->student_name,
            'student_email' => $student->student_email,
            'student_uuid' => $student->student_uuid,
            'has_submitted' => !is_null($latestSubmission),
            'submission_count' => $studentSubmissions->count(),
        ];

        if ($latestSubmission) {
            // Extract grading details from metadata
            $gradingDetails = [];
            if (!empty($latestSubmission->metadata)) {
                try {
                    $metadata = json_decode($latestSubmission->metadata, true);
                    $gradingDetails = $metadata['grading_details'] ?? [];
                } catch (\Throwable $e) {
                    $gradingDetails = [];
                }
            }

            $marksInfo['submission'] = [
                'submission_id' => $latestSubmission->id,
                'submission_uuid' => $latestSubmission->uuid,
                'attempt_no' => $latestSubmission->attempt_no,
                'submitted_at' => $latestSubmission->submitted_at,
                'is_late' => (bool)$latestSubmission->is_late,
                'late_minutes' => $latestSubmission->late_minutes,
                'late_days' => ceil($latestSubmission->late_minutes / (24 * 60)),
                'total_marks' => $latestSubmission->total_marks,
                'grade_letter' => $latestSubmission->grade_letter,
                'graded_at' => $latestSubmission->graded_at,
                'status' => $latestSubmission->status,
            ];

            $marksInfo['penalty_info'] = [
                'penalty_applied' => !empty($gradingDetails['late_penalty_applied']) && $gradingDetails['late_penalty_applied'],
                'penalty_amount' => $gradingDetails['late_penalty_amount'] ?? 0,
                'penalty_percentage' => $gradingDetails['late_penalty_percentage'] ?? 0,
                'given_marks' => $gradingDetails['given_marks'] ?? $latestSubmission->total_marks,
                'final_marks_after_penalty' => $gradingDetails['final_marks_after_penalty'] ?? $latestSubmission->total_marks,
            ];
        } else {
            $marksInfo['submission'] = null;
            $marksInfo['penalty_info'] = null;
        }

        return $marksInfo;
    });

    // Apply sorting
    $sortBy = $r->query('sort_by', 'student_name');
    $sortOrder = $r->query('sort_order', 'asc');
    
    $marksData = $marksData->sortBy(function($item) use ($sortBy) {
        return $item[$sortBy] ?? '';
    }, SORT_REGULAR, $sortOrder === 'desc');

    // Pagination
    $page = max(1, (int) $r->query('page', 1));
    $perPage = min(100, max(1, (int) $r->query('per_page', 50)));
    $total = $marksData->count();

    $paginatedData = $marksData->slice(($page - 1) * $perPage, $perPage)->values();

    // Statistics
    $submittedCount = $marksData->where('has_submitted', true)->count();
    $gradedCount = $marksData->where('submission.total_marks', '!==', null)->count();
    $lateCount = $marksData->where('submission.is_late', true)->count();
    $penaltyAppliedCount = $marksData->where('penalty_info.penalty_applied', true)->count();

    $averageMarks = $marksData->where('submission.total_marks', '!==', null)
        ->avg('submission.total_marks');

    return response()->json([
        'message' => 'Assignment marks fetched successfully',
        'data' => [
            'assignment' => [
                'id' => $assignment->id,
                'title' => $assignment->title ?? 'Unknown Title',
                'max_marks' => $assignment->max_marks ?? $assignment->total_marks ?? null,
                'due_at' => $assignment->due_at ?? null,
                'penalty_settings' => $penaltySettings,
            ],
            'statistics' => [
                'total_students' => $total,
                'submitted_count' => $submittedCount,
                'graded_count' => $gradedCount,
                'late_count' => $lateCount,
                'penalty_applied_count' => $penaltyAppliedCount,
                'submission_rate' => $total > 0 ? round(($submittedCount / $total) * 100, 2) : 0,
                'grading_rate' => $submittedCount > 0 ? round(($gradedCount / $submittedCount) * 100, 2) : 0,
                'average_marks' => round($averageMarks, 2),
            ],
            'marks' => $paginatedData,
            'pagination' => [
                'total' => $total,
                'page' => $page,
                'per_page' => $perPage,
                'pages' => (int) ceil($total / max(1, $perPage)),
            ],
        ],
    ], 200);
}
/**
 * Get marks and penalty details for a specific submission
 */
public function getSubmissionMarks(string $submissionKey)
{
    $submission = $this->resolveSubmission($submissionKey);
    if (!$submission) {
        return response()->json(['error' => 'Submission not found'], 404);
    }

    // Get assignment details
    $assignment = DB::table('assignments')
        ->where('id', $submission->assignment_id)
        ->whereNull('deleted_at')
        ->first();

    if (!$assignment) {
        return response()->json(['error' => 'Assignment not found'], 404);
    }

    // Get penalty settings
    $penaltySettings = $this->getAssignmentPenaltySettings($assignment);
    
    // Calculate potential penalty (for ungraded submissions)
    $potentialPenalty = [];
    if ($submission->is_late && $submission->late_minutes > 0 && $penaltySettings['late_penalty_enabled']) {
        // Use a safe default for max_marks if the field doesn't exist
        $maxMarks = isset($assignment->max_marks) ? $assignment->max_marks : 
                   (isset($assignment->total_marks) ? $assignment->total_marks : 100);
        
        $potentialPenalty = $this->calculateLatePenalty(
            $assignment, 
            $submission, 
            $maxMarks,
            true
        );
    }

    // Extract grading details from metadata
    $gradingDetails = [];
    if (!empty($submission->metadata)) {
        try {
            $metadata = json_decode($submission->metadata, true);
            $gradingDetails = $metadata['grading_details'] ?? [];
        } catch (\Throwable $e) {
            $gradingDetails = [];
        }
    }

    // Build response with safe property access
    $response = [
        'submission' => [
            'id' => $submission->id,
            'uuid' => $submission->uuid,
            'student_id' => $submission->student_id,
            'attempt_no' => $submission->attempt_no,
            'is_late' => (bool)$submission->is_late,
            'late_minutes' => $submission->late_minutes,
            'late_days' => ceil($submission->late_minutes / (24 * 60)),
            'submitted_at' => $submission->submitted_at,
        ],
        'assignment' => [
            'id' => $assignment->id,
            'title' => $assignment->title ?? 'Unknown Title',
            'max_marks' => $assignment->max_marks ?? $assignment->total_marks ?? null,
            'due_at' => $assignment->due_at ?? null,
        ],
        'penalty_settings' => $penaltySettings,
        'current_marks' => [
            'total_marks' => $submission->total_marks,
            'grade_letter' => $submission->grade_letter,
            'graded_at' => $submission->graded_at,
            'graded_by' => $submission->graded_by,
        ],
        'grading_details' => $gradingDetails,
        'potential_penalty' => $potentialPenalty,
    ];

    return response()->json([
        'message' => 'Marks details fetched successfully',
        'data' => $response,
    ], 200);
}
/**
 * Bulk grade multiple submissions
 */
public function bulkGradeSubmissions(Request $r)
{
    if ($res = $this->requireRole($r, ['admin','superadmin','instructor'])) return $res;

    $validator = Validator::make($r->all(), [
        'submissions' => 'required|array',
        'submissions.*.submission_id' => 'required',
        'submissions.*.marks' => 'required|numeric|min:0',
        'submissions.*.grade_letter' => 'nullable|string|max:10',
        'submissions.*.grader_note' => 'nullable|string',
        'apply_late_penalty' => 'boolean',
    ]);

    if ($validator->fails()) {
        return response()->json(['message' => 'Validation failed', 'errors' => $validator->errors()], 422);
    }

    $actor = $this->actor($r);
    $applyLatePenalty = $r->input('apply_late_penalty', true);
    $results = [
        'successful' => [],
        'failed' => [],
    ];

    DB::beginTransaction();
    try {
        foreach ($r->input('submissions') as $index => $gradingData) {
            try {
                $submission = $this->resolveSubmission($gradingData['submission_id']);
                if (!$submission) {
                    $results['failed'][] = [
                        'index' => $index,
                        'submission_id' => $gradingData['submission_id'],
                        'error' => 'Submission not found'
                    ];
                    continue;
                }

                $assignment = DB::table('assignments')
                    ->where('id', $submission->assignment_id)
                    ->whereNull('deleted_at')
                    ->first();

                if (!$assignment) {
                    $results['failed'][] = [
                        'index' => $index,
                        'submission_id' => $gradingData['submission_id'],
                        'error' => 'Assignment not found'
                    ];
                    continue;
                }

                // Calculate penalty
                $penaltyDetails = $this->calculateLatePenalty(
                    $assignment, 
                    $submission, 
                    (float) $gradingData['marks'],
                    $applyLatePenalty
                );

                // Prepare update data
                $updateData = [
                    'total_marks' => $penaltyDetails['final_marks'],
                    'grade_letter' => $gradingData['grade_letter'] ?? null,
                    'graded_at' => Carbon::now()->toDateTimeString(),
                    'graded_by' => $actor['id'],
                    'grader_note' => $gradingData['grader_note'] ?? null,
                    'updated_at' => Carbon::now()->toDateTimeString(),
                ];

                // Update metadata with grading details
                $metadata = [];
                if (!empty($submission->metadata)) {
                    try {
                        $metadata = json_decode($submission->metadata, true) ?: [];
                    } catch (\Throwable $e) {
                        $metadata = [];
                    }
                }

                $metadata['grading_details'] = [
                    'given_marks' => (float) $gradingData['marks'],
                    'late_penalty_applied' => $penaltyDetails['penalty_applied'],
                    'late_penalty_amount' => $penaltyDetails['penalty_amount'],
                    'late_penalty_percentage' => $penaltyDetails['penalty_percentage'],
                    'final_marks_after_penalty' => $penaltyDetails['final_marks'],
                    'graded_by' => $actor['id'],
                    'graded_by_role' => $actor['role'],
                    'graded_at' => Carbon::now()->toDateTimeString(),
                    'bulk_graded' => true,
                ];

                $updateData['metadata'] = json_encode($metadata);

                DB::table('assignment_submissions')
                    ->where('id', $submission->id)
                    ->update($updateData);

                $results['successful'][] = [
                    'submission_id' => $submission->id,
                    'student_id' => $submission->student_id,
                    'given_marks' => (float) $gradingData['marks'],
                    'final_marks' => $penaltyDetails['final_marks'],
                    'penalty_applied' => $penaltyDetails['penalty_applied'],
                    'penalty_amount' => $penaltyDetails['penalty_amount'],
                ];

            } catch (\Throwable $e) {
                $results['failed'][] = [
                    'index' => $index,
                    'submission_id' => $gradingData['submission_id'],
                    'error' => $e->getMessage()
                ];
            }
        }

        DB::commit();

        return response()->json([
            'message' => 'Bulk grading completed',
            'data' => $results,
        ], 200);

    } catch (\Throwable $e) {
        DB::rollBack();
        Log::error('bulk grade submissions error: '.$e->getMessage());
        return response()->json(['error' => 'Failed to process bulk grading'], 500);
    }
}
/**
 * Get student's marks for all attempts of a specific assignment
 */
public function getMyAssignmentMarks(Request $r, string $assignmentKey)
{
    $actor = $this->actor($r);
    if (empty($actor['role']) || $actor['role'] !== 'student') {
        return response()->json(['error' => 'Unauthorized'], 403);
    }
    $studentId = (int) $actor['id'];

    // Resolve assignment by key (id, uuid, or slug)
    $assignment = $this->resolveAssignment($assignmentKey);
    if (!$assignment) {
        return response()->json(['error' => 'Assignment not found'], 404);
    }

    // Get all submissions for this student and assignment, ordered by attempt number
    $submissions = DB::table('assignment_submissions')
        ->where('assignment_id', $assignment->id)
        ->where('student_id', $studentId)
        ->whereNull('deleted_at')
        ->orderBy('attempt_no', 'asc')
        ->get();

    // Process submissions to include marks and penalty details
    $attempts = $submissions->map(function($submission) {
        // Decode metadata for grading details
        $gradingDetails = [];
        $flags = [];
        
        if (!empty($submission->metadata)) {
            try {
                $metadata = json_decode($submission->metadata, true);
                $gradingDetails = $metadata['grading_details'] ?? [];
            } catch (\Throwable $e) {
                $gradingDetails = [];
            }
        }

        if (!empty($submission->flags_json)) {
            try {
                $flags = json_decode($submission->flags_json, true);
            } catch (\Throwable $e) {
                $flags = [];
            }
        }

        // Calculate late days if applicable
        $lateDays = 0;
        if ($submission->late_minutes > 0) {
            $lateDays = ceil($submission->late_minutes / (24 * 60)); // Convert minutes to days (rounded up)
        }

        return [
            'attempt_no' => $submission->attempt_no,
            'submission_id' => $submission->id,
            'submission_uuid' => $submission->uuid,
            'status' => $submission->status,
            'submitted_at' => $submission->submitted_at,
            'submitted_at_formatted' => $submission->submitted_at ? 
                Carbon::parse($submission->submitted_at)->format('M j, Y g:i A') : null,
            
            // Marks information
            'total_marks' => $submission->total_marks,
            'grade_letter' => $submission->grade_letter,
            'graded_at' => $submission->graded_at,
            'graded_at_formatted' => $submission->graded_at ? 
                Carbon::parse($submission->graded_at)->format('M j, Y g:i A') : null,
            'grader_note' => $submission->grader_note,
            'feedback_html' => $submission->feedback_html,
            'feedback_visible' => (bool)$submission->feedback_visible,
            
            // Late submission details
            'is_late' => (bool)$submission->is_late,
            'late_minutes' => $submission->late_minutes,
            'late_days' => $lateDays,
            
            // Penalty information
            'penalty_details' => [
                'penalty_applied' => !empty($gradingDetails['late_penalty_applied']) && $gradingDetails['late_penalty_applied'],
                'penalty_amount' => $gradingDetails['late_penalty_amount'] ?? 0,
                'penalty_percentage' => $gradingDetails['late_penalty_percentage'] ?? 0,
                'given_marks' => $gradingDetails['given_marks'] ?? $submission->total_marks,
                'final_marks_after_penalty' => $gradingDetails['final_marks_after_penalty'] ?? $submission->total_marks,
            ],
            
            // Submission content (optional - you might want to exclude large fields)
            'has_content_text' => !empty($submission->content_text),
            'has_content_html' => !empty($submission->content_html),
            'link_url' => $submission->link_url,
            'repo_url' => $submission->repo_url,
            
            // Attachments info (without full details to keep response small)
            'attachments_count' => 0, // We'll calculate this below
        ];
    });

    // Count attachments for each submission
    foreach ($submissions as $index => $submission) {
        if (!empty($submission->attachments_json)) {
            try {
                $attachments = json_decode($submission->attachments_json, true);
                if (is_array($attachments)) {
                    $attempts[$index]['attachments_count'] = count($attachments);
                }
            } catch (\Throwable $e) {
                // Keep count as 0
            }
        }
    }

    // Get assignment details
    $assignmentDetails = [
        'id' => $assignment->id,
        'uuid' => $assignment->uuid ?? null,
        'title' => $assignment->title ?? 'Unknown Assignment',
        'description' => $assignment->description ?? null,
        'due_at' => $assignment->due_at,
        'due_at_formatted' => $assignment->due_at ? 
            Carbon::parse($assignment->due_at)->format('M j, Y g:i A') : null,
        'max_marks' => $assignment->max_marks ?? $assignment->total_marks ?? null,
        'attempts_allowed' => $assignment->attempts_allowed ?? $assignment->max_attempts ?? null,
    ];

    // Calculate statistics
    $totalAttempts = $attempts->count();
    $gradedAttempts = $attempts->where('total_marks', '!==', null)->count();
    $latestAttempt = $attempts->last();
    $bestAttempt = $attempts->where('total_marks', '!==', null)
        ->sortByDesc('total_marks')
        ->first();

    $statistics = [
        'total_attempts' => $totalAttempts,
        'graded_attempts' => $gradedAttempts,
        'latest_attempt_no' => $latestAttempt['attempt_no'] ?? null,
        'best_marks' => $bestAttempt['total_marks'] ?? null,
        'best_attempt_no' => $bestAttempt['attempt_no'] ?? null,
        'has_late_submissions' => $attempts->where('is_late', true)->isNotEmpty(),
    ];

    return response()->json([
        'message' => 'Assignment marks fetched successfully',
        'data' => [
            'assignment' => $assignmentDetails,
            'statistics' => $statistics,
            'attempts' => $attempts->values(), // Ensure sequential indexing
        ],
    ], 200);
}
}
