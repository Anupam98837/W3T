<?php

// database/migrations/2025_11_19_100000_align_exam_controller_columns.php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        // ---- quizz_attempts ----
        Schema::table('quizz_attempts', function (Blueprint $t) {
            if (!Schema::hasColumn('quizz_attempts', 'total_time_sec')) {
                $t->integer('total_time_sec')->nullable()->after('user_id');
            }
            if (!Schema::hasColumn('quizz_attempts', 'started_at')) {
                $t->timestamp('started_at')->nullable()->after('total_time_sec');
            }
            if (!Schema::hasColumn('quizz_attempts', 'current_question_id')) {
                $t->unsignedBigInteger('current_question_id')->nullable()->after('server_deadline_at')->index();
            }
            if (!Schema::hasColumn('quizz_attempts', 'current_q_started_at')) {
                $t->timestamp('current_q_started_at')->nullable()->after('current_question_id');
            }
            if (!Schema::hasColumn('quizz_attempts', 'last_activity_at')) {
                $t->timestamp('last_activity_at')->nullable()->after('current_q_started_at')->index();
            }
            if (!Schema::hasColumn('quizz_attempts', 'finished_at')) {
                $t->timestamp('finished_at')->nullable()->after('last_activity_at');
            }
            if (!Schema::hasColumn('quizz_attempts', 'result_id')) {
                $t->unsignedBigInteger('result_id')->nullable()->after('finished_at')->index();
            }
        });

        // ---- quizz_attempt_answers ----
        Schema::table('quizz_attempt_answers', function (Blueprint $t) {
            if (!Schema::hasColumn('quizz_attempt_answers', 'selected_raw')) {
                $t->longText('selected_raw')->nullable()->after('selected_text');
            }
        });

        // ---- quizz_results ----
        Schema::table('quizz_results', function (Blueprint $t) {
            if (!Schema::hasColumn('quizz_results', 'total_marks')) {
                $t->integer('total_marks')->default(0)->after('marks_obtained');
            }
            if (!Schema::hasColumn('quizz_results', 'attempt_number')) {
                $t->integer('attempt_number')->default(0)->after('total_marks');
            }
        });
    }

    public function down(): void
    {
        // Keep columns; they’re used by your controller.
    }
};
