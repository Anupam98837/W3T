<?php

namespace App\Http\Controllers\VIEW;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\DB;

class CodingQuestionController extends Controller
{
    /**
     * Show Manage Questions view for a given topic/module
     */
    public function manage($topicId, $moduleId)
    {
        // Fetch topic + module details (safe fallback if missing)
        $topic  = DB::table('topics')->find($topicId);
        $module = DB::table('coding_modules')->where('id', $moduleId)
                                      ->where('topic_id', $topicId)
                                      ->first();

        if (! $topic || ! $module) {
            abort(404, 'Topic or Module not found.');
        }

        return view('pages.users.admin.pages.codingQuestions.manageCodingQuestions', [
            'topic_id'    => $topic->id,
            'topic_name'  => $topic->title ?? 'Topic',
            'module_id'   => $module->id,
            'module_name' => $module->title ?? 'Module',
        ]);
    }
}
