<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        /* =========================
         * Table: quizz_questions
         * ========================= */
        Schema::create('quizz_questions', function (Blueprint $table) {
            $table->id(); // BIGINT PK

            // ---- Identity (uuid) ----
            $table->uuid('uuid')->unique()->comment('Public identifier for the question');

            // ---- Parent quiz: allow lookup by id or uuid ----
            $table->unsignedBigInteger('quiz_id');                      // FK → quizz.id
            $table->char('quiz_uuid', 36);                              // FK → quizz.uuid
            $table->index('quiz_id', 'qq_quiz_id_idx');
            $table->index('quiz_uuid', 'qq_quiz_uuid_idx');

            // ---- Author / audit (lightweight) ----
            $table->unsignedBigInteger('created_by')->nullable()->index();
            $table->unsignedBigInteger('updated_by')->nullable()->index();
            $table->string('created_at_ip', 45)->nullable();

            // ---- Display & content ----
            $table->unsignedInteger('question_order')->default(1);      // display order
            $table->longText('question_title');                         // rich HTML supported
            $table->longText('question_description')->nullable();       // optional details
            $table->longText('answer_explanation')->nullable();         // why the answer is correct

            // ---- Type & scoring ----
            $table->enum('question_type', ['mcq','true_false','fill_in_the_blank']);
            $table->integer('question_mark')->default(1);

            // ---- Extra settings (shuffle, etc.) ----
            $table->json('question_settings')->nullable();

            // ---- Timestamps ----
            $table->timestamp('created_at')->useCurrent();
            $table->timestamp('updated_at')->useCurrent()->useCurrentOnUpdate();

            // ---- FKs ----
            // 1) by numeric id
            $table->foreign('quiz_id', 'fk_qq_quiz_id')
                  ->references('id')->on('quizz')
                  ->cascadeOnDelete();

            // 2) by uuid (requires quizz.uuid UNIQUE, which you already have)
            $table->foreign('quiz_uuid', 'fk_qq_quiz_uuid')
                  ->references('uuid')->on('quizz')
                  ->cascadeOnDelete();
        });

        /* ======================================
         * Table: quizz_question_answers
         * ====================================== */
        Schema::create('quizz_question_answers', function (Blueprint $table) {
            $table->id(); // BIGINT PK

            // ---- Identity (uuid) ----
            $table->uuid('uuid')->unique()->comment('Public identifier for the answer option');

            // ---- Parent question: allow lookup by id or uuid ----
            $table->unsignedBigInteger('belongs_question_id');          // FK → quizz_questions.id
            $table->char('belongs_question_uuid', 36);                  // FK → quizz_questions.uuid
            $table->index('belongs_question_id', 'qqa_belongs_q_id_idx');
            $table->index('belongs_question_uuid', 'qqa_belongs_q_uuid_idx');

            // ---- Author / audit (lightweight) ----
            $table->unsignedBigInteger('created_by')->nullable()->index();
            $table->unsignedBigInteger('updated_by')->nullable()->index();
            $table->string('created_at_ip', 45)->nullable();

            // ---- Optional metadata about the question type context ----
            $table->string('belongs_question_type', 255)->nullable();

            // ---- The option/answer itself (HTML allowed) ----
            $table->longText('answer_title')->nullable();

            // ---- Correct flag ----
            $table->boolean('is_correct')->default(false);

            // ---- Optional media linkage ----
            $table->unsignedBigInteger('image_id')->nullable();

            // ---- Gap-fill helper field(s) ----
            $table->string('answer_two_gap_match', 255)->nullable();

            // ---- UI formatting hint ----
            $table->string('answer_view_format', 255)->nullable();

            // ---- Extra settings ----
            $table->json('answer_settings')->nullable();

            // ---- Ordering among options ----
            $table->integer('answer_order')->default(0);

            // ---- Timestamps ----
            $table->timestamp('created_at')->useCurrent();
            $table->timestamp('updated_at')->useCurrent()->useCurrentOnUpdate();

            // ---- FKs ----
            // 1) by numeric id
            $table->foreign('belongs_question_id', 'fk_qqa_question_id')
                  ->references('id')->on('quizz_questions')
                  ->cascadeOnDelete();

            // 2) by uuid
            $table->foreign('belongs_question_uuid', 'fk_qqa_question_uuid')
                  ->references('uuid')->on('quizz_questions')
                  ->cascadeOnDelete();
        });
    }

    public function down(): void
    {
        // Drop child first, then parent, respecting FK order
        Schema::dropIfExists('quizz_question_answers');
        Schema::dropIfExists('quizz_questions');
    }
};
