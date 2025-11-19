<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('quizz_results', function (Blueprint $t) {
            $t->bigIncrements('id');

            $t->char('uuid', 36)->unique();

            // Link back
            $t->unsignedBigInteger('attempt_id')->unique(); // 1 result per attempt
            $t->unsignedBigInteger('quiz_id')->index();
            $t->char('quiz_uuid', 36)->nullable()->index();
            $t->unsignedBigInteger('user_id')->index();     // users.id

            // Denormalized scoring summary
            $t->integer('marks_obtained')->default(0);
            $t->integer('marks_total')->default(0);
            $t->integer('total_questions')->default(0);
            $t->integer('total_correct')->default(0);
            $t->integer('total_incorrect')->default(0);
            $t->integer('total_skipped')->default(0);
            $t->decimal('percentage', 5, 2)->default(0.00);
            $t->string('grade', 16)->nullable();            // PASS|FAIL or A/B/C...

            // Release rules snapshot (copied from quizz at finish)
            $t->enum('result_set_up_type', ['Immediately','Now','Schedule'])->default('Immediately');
            $t->timestamp('result_release_date')->nullable();
            $t->boolean('publish_to_student')->default(true);
            $t->timestamp('released_at')->nullable();

            // Cached full answer sheet for quick retrieval/email/pdf
            $t->json('students_answer')->nullable(); // compact [{question_id, selected:[...]/text},...]

            // Optional evaluator (if manual grading ever needed)
            $t->unsignedBigInteger('evaluator_id')->nullable();

            $t->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('quizz_results');
    }
};
