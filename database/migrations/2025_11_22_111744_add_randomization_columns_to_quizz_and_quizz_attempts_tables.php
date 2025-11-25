<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // 1) Flags on quizz table
        Schema::table('quizz', function (Blueprint $table) {
            $table->enum('is_question_random', ['yes', 'no'])
                ->default('no')
                ->after('total_questions');

            $table->enum('is_option_random', ['yes', 'no'])
                ->default('no')
                ->after('is_question_random');
        });

        // 2) Per-attempt layout on quizz_attempts table
        Schema::table('quizz_attempts', function (Blueprint $table) {
            // stores [qId1, qId2, ...] in the order this student will see
            $table->json('questions_order')
                ->nullable()
                ->after('total_time_sec');

            // stores { "question_id": [answerId1, answerId2, ...], ... }
            $table->json('options_order')
                ->nullable()
                ->after('questions_order');
        });
    }

    public function down(): void
    {
        Schema::table('quizz_attempts', function (Blueprint $table) {
            $table->dropColumn(['questions_order', 'options_order']);
        });

        Schema::table('quizz', function (Blueprint $table) {
            $table->dropColumn(['is_question_random', 'is_option_random']);
        });
    }
};
