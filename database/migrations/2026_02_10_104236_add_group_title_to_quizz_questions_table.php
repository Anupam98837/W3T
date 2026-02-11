<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('quizz_questions', function (Blueprint $table) {
            // Group title for grouping questions (optional)
            $table->string('group_title', 255)
                  ->nullable()
                  ->after('question_order')
                  ->comment('Optional group/section title for the question');
        });
    }

    public function down(): void
    {
        Schema::table('quizz_questions', function (Blueprint $table) {
            $table->dropColumn('group_title');
        });
    }
};
