<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('coding_attempts', function (Blueprint $table) {
            $table->engine = 'InnoDB';

            $table->bigIncrements('id');
            $table->char('uuid', 36)->unique();

            $table->unsignedBigInteger('question_id');
            $table->unsignedBigInteger('user_id');

            $table->unsignedBigInteger('batch_id')->nullable();
            $table->unsignedBigInteger('batch_coding_question_id')->nullable();

            $table->unsignedInteger('attempt_no')->default(1);

            $table->string('language_key', 50)->nullable();
            $table->unsignedInteger('judge_language_id')->nullable();
            $table->longText('source_code')->nullable();

            $table->string('judge_vendor', 40)->nullable();
            $table->string('judge_request_id', 120)->nullable();

            $table->json('test_results_json')->nullable();

            $table->unsignedInteger('total_tests')->default(0);
            $table->unsignedInteger('passed_tests')->default(0);
            $table->unsignedInteger('failed_tests')->default(0);

            $table->unsignedInteger('total_runtime_ms')->nullable();
            $table->unsignedInteger('max_memory_kb')->nullable();

            $table->enum('status', ['in_progress','submitted','evaluated','error'])->default('submitted');

            $table->timestamp('server_started_at')->nullable();
            $table->timestamp('submitted_at')->nullable();
            $table->timestamp('evaluated_at')->nullable();

            $table->string('ip', 64)->nullable();
            $table->string('user_agent', 512)->nullable();
            $table->json('auth_snapshot')->nullable();

            $table->timestamp('created_at')->useCurrent();
            $table->timestamp('updated_at')->useCurrent()->useCurrentOnUpdate();
            $table->timestamp('deleted_at')->nullable();

            // ✅ Short, MySQL-safe index names
            $table->index('question_id', 'idx_ca_q');
            $table->index('user_id', 'idx_ca_u');
            $table->index('batch_id', 'idx_ca_b');
            $table->index('batch_coding_question_id', 'idx_ca_bcq');
            $table->index(['question_id','user_id'], 'idx_ca_q_u');
            $table->index(['batch_id','question_id','user_id'], 'idx_ca_b_q_u');
            $table->index(['batch_coding_question_id','question_id','user_id'], 'idx_ca_bcq_q_u');

            $table->index('attempt_no', 'idx_ca_attempt');
            $table->index('status', 'idx_ca_status');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('coding_attempts');
    }
};
