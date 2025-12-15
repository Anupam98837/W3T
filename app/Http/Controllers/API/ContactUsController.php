<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Symfony\Component\HttpFoundation\StreamedResponse;
use Carbon\Carbon;

class ContactUsController extends Controller
{
    /**
     * POST /api/contact-us
     * Public contact form submit
     */
    public function store(Request $request)
    {
        $v = Validator::make($request->all(), [
            'name'    => ['required', 'string', 'max:255'],
            'email'   => ['required', 'email', 'max:255'],
            'phone'   => ['nullable', 'string', 'max:20'],
            'message' => ['required', 'string'],
        ]);

        if ($v->fails()) {
            return response()->json([
                'success' => false,
                'errors'  => $v->errors()
            ], 422);
        }

        DB::table('contact_us')->insert([
            'name'       => $request->name,
            'email'      => $request->email,
            'phone'      => $request->phone,
            'message'    => $request->message,
            'is_read'    => 0, // default unread
            'created_at' => Carbon::now(),
            'updated_at' => Carbon::now(),
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Your message has been sent successfully.'
        ], 201);
    }

    /**
     * GET /api/contact-us
     * Admin: list all messages
     */
    public function index(Request $request)
    {
        $page     = max(1, (int) $request->query('page', 1));
        $perPage  = min(100, max(5, (int) $request->query('per_page', 20)));
        $q        = trim((string) $request->query('q', ''));
        $sortBy   = $request->query('sort_by', 'created_at');
        $sortDir  = strtolower($request->query('sort_dir', 'desc')) === 'asc' ? 'asc' : 'desc';

        $allowedSorts = ['id', 'name', 'email', 'phone', 'created_at'];
        if (!in_array($sortBy, $allowedSorts)) {
            $sortBy = 'created_at';
        }

        $query = DB::table('contact_us');

        if ($q !== '') {
            $query->where(function ($w) use ($q) {
                $like = '%' . $q . '%';
                $w->where('name', 'LIKE', $like)
                  ->orWhere('email', 'LIKE', $like)
                  ->orWhere('phone', 'LIKE', $like)
                  ->orWhere('message', 'LIKE', $like);
            });
        }

        $total = (clone $query)->count();

        $data = $query
            ->orderBy($sortBy, $sortDir)
            ->offset(($page - 1) * $perPage)
            ->limit($perPage)
            ->get();

        return response()->json([
            'success' => true,
            'data'    => $data,
            'meta'    => [
                'page'        => $page,
                'per_page'    => $perPage,
                'total'       => $total,
                'total_pages' => (int) ceil($total / $perPage),
                'sort_by'     => $sortBy,
                'sort_dir'    => $sortDir,
                'q'           => $q,
            ]
        ], 200);
    }

    /**
     * GET /api/contact-us/{id}
     * Admin: view single message
     */
    public function show($id)
    {
        $msg = DB::table('contact_us')->where('id', $id)->first();

        if (!$msg) {
            return response()->json([
                'success' => false,
                'message' => 'Message not found'
            ], 404);
        }

        if ((int) $msg->is_read === 0) {
            DB::table('contact_us')
                ->where('id', $id)
                ->update([
                    'is_read' => 1,
                    'updated_at' => Carbon::now(),
                ]);

            $msg->is_read = 1;
        }

        return response()->json([
            'success' => true,
            'message' => $msg
        ]);
    }

    /**
     * PATCH /api/contact-us/{id}/read
     * Admin: mark message as read
     */
    public function markAsRead($id)
    {
        $msg = DB::table('contact_us')->where('id', $id)->first();

        if (!$msg) {
            return response()->json([
                'success' => false,
                'message' => 'Message not found'
            ], 404);
        }

        if ((int) $msg->is_read === 1) {
            return response()->json([
                'success' => true,
                'message' => 'Message already marked as read'
            ]);
        }

        DB::table('contact_us')
            ->where('id', $id)
            ->update([
                'is_read' => 1,
                'updated_at' => Carbon::now(),
            ]);

        return response()->json([
            'success' => true,
            'message' => 'Message marked as read'
        ]);
    }

    /**
     * DELETE /api/contact-us/{id}
     * Admin: delete message
     */
    public function destroy($id)
    {
        $exists = DB::table('contact_us')->where('id', $id)->exists();

        if (!$exists) {
            return response()->json([
                'success' => false,
                'message' => 'Message not found'
            ], 404);
        }

        DB::table('contact_us')->where('id', $id)->delete();

        return response()->json([
            'success' => true,
            'message' => 'Message deleted successfully'
        ]);
    }

    public function exportCsv(Request $request): StreamedResponse
    {
        $q        = trim((string) $request->query('q', ''));
        $sortBy   = $request->query('sort_by', 'created_at');
        $sortDir  = strtolower($request->query('sort_dir', 'desc')) === 'asc' ? 'asc' : 'desc';

        $allowedSorts = ['id', 'name', 'email', 'phone', 'created_at'];
        if (!in_array($sortBy, $allowedSorts)) {
            $sortBy = 'created_at';
        }

        $query = DB::table('contact_us');

        if ($q !== '') {
            $query->where(function ($w) use ($q) {
                $like = '%' . $q . '%';
                $w->where('name', 'LIKE', $like)
                  ->orWhere('email', 'LIKE', $like)
                  ->orWhere('phone', 'LIKE', $like)
                  ->orWhere('message', 'LIKE', $like);
            });
        }

        $query->orderBy($sortBy, $sortDir);

        $fileName = 'enquiries_' . now()->format('Ymd_His') . '.csv';

        return response()->streamDownload(function () use ($query) {

            $handle = fopen('php://output', 'w');

            fputcsv($handle, [
                'ID',
                'Name',
                'Email',
                'Phone',
                'Message',
                'Created At'
            ]);

            $query->chunk(500, function ($rows) use ($handle) {
                foreach ($rows as $row) {
                    fputcsv($handle, [
                        $row->id,
                        $row->name,
                        $row->email,
                        $row->phone,
                        preg_replace("/\r|\n/", ' ', $row->message),
                        $row->created_at,
                    ]);
                }
            });

            fclose($handle);

        }, $fileName, [
            'Content-Type' => 'text/csv',
            'Cache-Control' => 'no-store, no-cache',
        ]);
    }
}
