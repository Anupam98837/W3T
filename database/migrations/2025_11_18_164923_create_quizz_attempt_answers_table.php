<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('quizz_attempt_answers', function (Blueprint $t) {
            $t->bigIncrements('id');

            $t->unsignedBigInteger('attempt_id')->index();     // quizz_attempts.id
            $t->unsignedBigInteger('question_id')->index();    // quizz_questions.id
            $t->char('question_uuid', 36)->nullable();
            $t->string('question_type', 32);                   // mcq|true_false|fill_in_the_blank

            // What the student selected/typed
            $t->json('selected_answer_ids')->nullable();       // [answer_id,...] for single/multi/true_false
            $t->text('selected_text')->nullable();             // for fill_in_the_blank (raw)

            // Scoring
            $t->boolean('is_correct')->nullable();             // null until scored
            $t->integer('awarded_mark')->default(0);
            $t->smallInteger('time_spent_sec')->nullable();

            $t->timestamp('answered_at')->nullable();

            $t->timestamps();

            $t->unique(['attempt_id','question_id']);          // 1 row per question per attempt
            $t->index(['attempt_id', 'answered_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('quizz_attempt_answers');
    }
};
