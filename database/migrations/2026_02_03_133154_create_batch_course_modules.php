<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('batch_course_module', function (Blueprint $table) {
            $table->engine = 'InnoDB';

            // Keys
            $table->bigIncrements('id');
            $table->char('uuid', 36)->unique();

            // =========================
            // Relations
            // =========================
            $table->unsignedBigInteger('batch_id');          // FK -> batches.id
            $table->unsignedBigInteger('course_module_id');  // FK -> course_modules.id (reference to original)
            $table->unsignedBigInteger('course_id');         // FK -> courses.id

            // =========================
            // Progress / State
            // =========================
            $table->boolean('is_completed')->default(false); // 0/1 default 0

            // =========================
            // Module Columns (copied from course_modules)
            // =========================
            $table->string('title', 255);
            $table->text('short_description')->nullable();
            $table->longText('long_description')->nullable();

            $table->integer('order_no')->default(0);
            $table->string('status', 20)->default('draft'); // draft|published|archived

            $table->json('metadata')->nullable();

            // =========================
            // Audit
            // =========================
            $table->unsignedBigInteger('created_by')->nullable();
            $table->ipAddress('created_at_ip')->nullable();

            // Timestamps
            $table->timestamp('created_at')->useCurrent();
            $table->timestamp('updated_at')->useCurrent()->useCurrentOnUpdate();

            // Soft delete + indexes
            $table->softDeletes();
            $table->index('deleted_at');

            // Useful indexes
            $table->index(['batch_id', 'course_module_id']);
            $table->index(['course_id', 'order_no']);
            $table->index('status');
            $table->index('is_completed');

            // Prevent duplicate mapping (same module cannot be inserted twice for same batch)
            $table->unique(['batch_id', 'course_module_id'], 'batch_course_module_unique');

            // =========================
            // FKs
            // =========================
            $table->foreign('batch_id')
                ->references('id')->on('batches')
                ->cascadeOnDelete();

            $table->foreign('course_module_id')
                ->references('id')->on('course_modules')
                ->cascadeOnDelete();

            $table->foreign('course_id')
                ->references('id')->on('courses')
                ->cascadeOnDelete();

            $table->foreign('created_by')
                ->references('id')->on('users')
                ->nullOnDelete();
        });

        // MySQL 8.0.16+ supports CHECK (older versions ignore/throw, so guard it)
        try {
            DB::statement(
                "ALTER TABLE `batch_course_module`
                 ADD CONSTRAINT `batch_course_module_status_check`
                 CHECK (`status` IN ('draft','published','archived'))"
            );
        } catch (\Throwable $e) {
            // Safe no-op on MySQL versions that don't enforce CHECK constraints
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('batch_course_module');
    }
};
