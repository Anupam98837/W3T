<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('coding_results', function (Blueprint $table) {
            $table->engine = 'InnoDB';

            $table->bigIncrements('id');
            $table->char('uuid', 36)->unique();

            // One result per attempt
            $table->unsignedBigInteger('attempt_id')->unique();

            // Core references (no FK to preserve history)
            $table->unsignedBigInteger('question_id')->index();
            $table->unsignedBigInteger('user_id')->index();

            // Batch context
            $table->unsignedBigInteger('batch_id')->nullable()->index();
            $table->unsignedBigInteger('batch_coding_question_id')->nullable()->index();

            // Scoring/stats
            $table->unsignedInteger('marks_total')->default(0);
            $table->unsignedInteger('marks_obtained')->default(0);

            $table->unsignedInteger('total_tests')->default(0);
            $table->unsignedInteger('passed_tests')->default(0);
            $table->unsignedInteger('failed_tests')->default(0);

            $table->decimal('percentage', 6, 2)->nullable(); // e.g. 87.50
            $table->boolean('all_pass')->default(false)->index();

            $table->timestamp('evaluated_at')->nullable()->index();

            $table->timestamp('created_at')->useCurrent();
            $table->timestamp('updated_at')->useCurrent()->useCurrentOnUpdate();
            $table->timestamp('deleted_at')->nullable()->index();

            $table->longText('metadata')->collation('utf8mb4_bin')->nullable();

            // Useful indexes for dashboards
            $table->index(['batch_id','user_id']);
            $table->index(['question_id','batch_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('coding_results');
    }
};
