<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
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
    public function index()
    {
        $messages = DB::table('contact_us')
            ->orderBy('created_at', 'desc')
            ->get();

        return response()->json([
            'success'  => true,
            'messages' => $messages
        ]);
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

        return response()->json([
            'success' => true,
            'message' => $msg
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
}
