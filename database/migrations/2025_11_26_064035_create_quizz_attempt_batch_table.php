<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('quizz_attempt_batch', function (Blueprint $table) {
            $table->bigIncrements('id');

            // External reference
            $table->char('uuid', 36)->unique();

            // Core links
            $table->unsignedBigInteger('quiz_id');            // quizz.id
            $table->unsignedBigInteger('batch_id')->nullable();       // batches.id
            $table->unsignedBigInteger('batch_quiz_id')->nullable();  // batch_quizzes.id
            $table->unsignedBigInteger('attempt_id');         // quizz_attempts.id
            $table->unsignedBigInteger('user_id');            // users.id

            // Extra info
            $table->string('came_from', 64)->nullable()->index(); // 'batch', 'standalone', etc.
            $table->longText('metadata')->nullable();             // JSON / text

            // Timestamps + soft delete
            $table->timestamps();
            $table->softDeletes();

            // Indexes
            $table->index(['quiz_id', 'user_id']);
            $table->index('batch_id');
            $table->index('batch_quiz_id');
            $table->unique('attempt_id'); // one mapping row per attempt

            // OPTIONAL: enable these only if your DB/Host supports FKs properly
            $table->foreign('quiz_id')
                  ->references('id')->on('quizz')
                  ->onDelete('cascade');

            $table->foreign('batch_id')
                  ->references('id')->on('batches')
                  ->onDelete('set null');

            $table->foreign('batch_quiz_id')
                  ->references('id')->on('batch_quizzes')
                  ->onDelete('set null');

            $table->foreign('attempt_id')
                  ->references('id')->on('quizz_attempts')
                  ->onDelete('cascade');

            $table->foreign('user_id')
                  ->references('id')->on('users')
                  ->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::table('quizz_attempt_batch', function (Blueprint $table) {
            // drop FKs first (if you enabled them)
            $table->dropForeign(['quiz_id']);
            $table->dropForeign(['batch_id']);
            $table->dropForeign(['batch_quiz_id']);
            $table->dropForeign(['attempt_id']);
            $table->dropForeign(['user_id']);
        });

        Schema::dropIfExists('quizz_attempt_batch');
    }
};
