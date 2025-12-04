<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        /* =========================
         * CODING QUESTIONS (core)
         * ========================= */
        Schema::create('coding_questions', function (Blueprint $table) {
            $table->engine    = 'InnoDB';
            $table->charset   = 'utf8mb4';
            $table->collation = 'utf8mb4_unicode_ci';

            $table->id();
            $table->uuid('uuid')->unique();

            // Required FKs (kept)
            $table->unsignedBigInteger('topic_id');
            $table->unsignedBigInteger('module_id');

            // Identity / basics
            $table->string('title', 200);
            $table->string('slug', 200);

            // Difficulty / status / ordering
            $table->enum('difficulty', ['easy','medium','hard'])->default('medium');
            $table->enum('status', ['active','draft','archived'])->default('active');
            $table->unsignedInteger('sort_order')->default(0);

            // Problem statement
            $table->longText('description')->nullable();

            /* -------------------------------------------------
             * Judge policy (tiny but essential, no JSON)
             * ------------------------------------------------- */
            $table->enum('compare_mode', ['exact','icase','float_abs','float_rel','token'])
                  ->default('exact');
            $table->boolean('trim_output')->default(true);
            $table->enum('whitespace_mode', ['none','trim','squash'])->default('trim');
            $table->double('float_abs_tol')->nullable();
            $table->double('float_rel_tol')->nullable();

            $table->timestamps();
            $table->softDeletes();

            // Indexes
            $table->index('topic_id',   'idx_cq_topic_id');
            $table->index('module_id',  'idx_cq_module_id');
            $table->index('difficulty', 'idx_cq_difficulty');
            $table->index('status',     'idx_cq_status');
            $table->index('sort_order', 'idx_cq_sort');

            // Unique slug within a module
            $table->unique(['module_id','slug'], 'uq_cq_module_slug');
        });

        // FKs for coding_questions (explicit names)
        Schema::table('coding_questions', function (Blueprint $table) {
            $table->foreign('topic_id',  'fk_cq_topic')
                  ->references('id')->on('topics')
                  ->cascadeOnDelete();

            $table->foreign('module_id', 'fk_cq_module')
                  ->references('id')->on('coding_modules')
                  ->cascadeOnDelete();
        });

        /* ============================================================
         * QUESTION LANGUAGES
         * ============================================================ */
        Schema::create('question_languages', function (Blueprint $table) {
            $table->engine    = 'InnoDB';
            $table->charset   = 'utf8mb4';
            $table->collation = 'utf8mb4_unicode_ci';

            $table->id();
            $table->uuid('uuid')->unique();
            $table->unsignedBigInteger('question_id'); // coding_questions.id

            // Language/runtime identifiers
            $table->string('language_key', 50);
            $table->string('runtime_key',  80)->nullable();

            // Portable command hints
            $table->string('source_filename', 120)->nullable();
            $table->string('compile_cmd', 255)->nullable();
            $table->string('run_cmd',     255)->nullable();

            // Resource limits
            $table->unsignedInteger('time_limit_ms')->nullable();
            $table->unsignedInteger('memory_limit_kb')->nullable();
            $table->unsignedInteger('stdout_kb_max')->nullable();
            $table->unsignedInteger('line_limit')->nullable();
            $table->unsignedInteger('byte_limit')->nullable();
            $table->unsignedInteger('max_inputs')->nullable();
            $table->unsignedInteger('max_stdin_tokens')->nullable();
            $table->unsignedInteger('max_args')->nullable();

            // Security / allowlist / denylist
            $table->string('allow_label', 50)->nullable();
            $table->text('allow')->nullable();
            $table->text('forbid_regex')->nullable();

            $table->boolean('is_enabled')->default(true);
            $table->unsignedInteger('sort_order')->default(0);

            $table->timestamps();

            // Indexes / uniqueness
            $table->index('question_id',  'idx_qlang_question_id');
            $table->index('language_key', 'idx_qlang_lang_key');
            $table->unique(['question_id','language_key'], 'uq_qlang_qid_lang');
        });

        Schema::table('question_languages', function (Blueprint $table) {
            $table->foreign('question_id', 'fk_qlang_question')
                  ->references('id')->on('coding_questions')
                  ->cascadeOnDelete();
        });

        /* ====================================================
         * QUESTION SNIPPETS
         * ==================================================== */
        Schema::create('question_snippets', function (Blueprint $table) {
            $table->engine    = 'InnoDB';
            $table->charset   = 'utf8mb4';
            $table->collation = 'utf8mb4_unicode_ci';

            $table->id();
            $table->uuid('uuid')->unique();
            $table->unsignedBigInteger('question_id'); // coding_questions.id
            $table->string('language_key', 50);
            $table->string('entry_hint', 200)->nullable();
            $table->longText('template');

            $table->boolean('is_default')->default(false);
            $table->unsignedInteger('sort_order')->default(0);

            $table->timestamps();

            $table->index('question_id', 'idx_qsnip_question_id');
            $table->index(['question_id','language_key'], 'idx_qsnip_qid_lang');
            $table->unique(['question_id','language_key'], 'uq_qsnip_qid_lang');
        });

        Schema::table('question_snippets', function (Blueprint $table) {
            $table->foreign('question_id', 'fk_qsnip_question')
                  ->references('id')->on('coding_questions')
                  ->cascadeOnDelete();
        });

        /* ==================================
         * QUESTION TESTS
         * ================================== */
        Schema::create('question_tests', function (Blueprint $table) {
            $table->engine    = 'InnoDB';
            $table->charset   = 'utf8mb4';
            $table->collation = 'utf8mb4_unicode_ci';

            $table->id();

            $table->unsignedBigInteger('question_id'); // coding_questions.id
            $table->enum('visibility', ['sample','hidden'])->default('hidden');
            $table->longText('input')->nullable();
            $table->longText('expected')->nullable();
            $table->integer('score')->default(1);
            $table->boolean('is_active')->default(true);
            $table->unsignedInteger('sort_order')->default(0);

            $table->unsignedInteger('time_limit_ms_override')->nullable();
            $table->unsignedInteger('memory_limit_kb_override')->nullable();

            $table->timestamps();

            $table->index('question_id', 'idx_qtests_question_id');
            $table->index(['question_id','visibility'], 'idx_qtests_qid_visibility');
            $table->index(['question_id','sort_order'], 'idx_qtests_qid_sort');
        });

        Schema::table('question_tests', function (Blueprint $table) {
            $table->foreign('question_id', 'fk_qtests_question')
                  ->references('id')->on('coding_questions')
                  ->cascadeOnDelete();
        });
    }

    public function down(): void
    {
        if (Schema::hasTable('question_tests')) {
            Schema::table('question_tests', function (Blueprint $table) {
                $table->dropForeign('fk_qtests_question');
            });
            Schema::dropIfExists('question_tests');
        }

        if (Schema::hasTable('question_snippets')) {
            Schema::table('question_snippets', function (Blueprint $table) {
                $table->dropForeign('fk_qsnip_question');
            });
            Schema::dropIfExists('question_snippets');
        }

        if (Schema::hasTable('question_languages')) {
            Schema::table('question_languages', function (Blueprint $table) {
                $table->dropForeign('fk_qlang_question');
            });
            Schema::dropIfExists('question_languages');
        }

        if (Schema::hasTable('coding_questions')) {
            Schema::table('coding_questions', function (Blueprint $table) {
                $table->dropForeign('fk_cq_topic');
                $table->dropForeign('fk_cq_module');
            });
            Schema::dropIfExists('coding_questions');
        }
    }
};
