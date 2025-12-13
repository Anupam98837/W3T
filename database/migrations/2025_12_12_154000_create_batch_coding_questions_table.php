<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('batch_coding_questions', function (Blueprint $table) {
            $table->engine = 'InnoDB';

            $table->bigIncrements('id');
            $table->char('uuid', 36)->unique();

            $table->unsignedBigInteger('batch_id');
            $table->unsignedBigInteger('question_id');

            // Batch-level settings
            $table->enum('status', ['active','inactive','archived'])->default('active')->index();
            $table->unsignedInteger('display_order')->default(1);

            $table->timestamp('available_from')->nullable();
            $table->timestamp('available_until')->nullable();

            $table->boolean('publish_to_students')->default(false);
            $table->boolean('assign_status')->default(false);

            // Batch attempt cap (final allowed = min(coding_questions.total_attempts, attempt_allowed))
            $table->unsignedInteger('attempt_allowed')->nullable()->default(1);

            // Audit
            $table->unsignedBigInteger('created_by');
            $table->timestamp('created_at')->useCurrent();
            $table->string('created_at_ip', 45)->nullable();
            $table->timestamp('updated_at')->useCurrent()->useCurrentOnUpdate();
            $table->string('updated_at_ip', 45)->nullable();
            $table->timestamp('deleted_at')->nullable()->index();

            $table->longText('metadata')->collation('utf8mb4_bin')->nullable();

            // Constraints
            $table->unique(['batch_id','question_id'], 'uq_batch_coding_question');
            $table->index(['batch_id','status']);
            $table->index(['question_id','status']);

            // Foreign keys
            $table->foreign('batch_id')->references('id')->on('batches')->onDelete('cascade');
            $table->foreign('question_id')->references('id')->on('coding_questions')->onDelete('cascade');
            $table->foreign('created_by')->references('id')->on('users')->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::table('batch_coding_questions', function (Blueprint $table) {
            $table->dropForeign(['batch_id']);
            $table->dropForeign(['question_id']);
            $table->dropForeign(['created_by']);
        });

        Schema::dropIfExists('batch_coding_questions');
    }
};
