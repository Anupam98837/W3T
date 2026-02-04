<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('batch_course_module_settings', function (Blueprint $table) {
            $table->engine = 'InnoDB';

            // Keys
            $table->bigIncrements('id');

            // FKs
            $table->unsignedBigInteger('batch_id');   // -> batches.id
            $table->unsignedBigInteger('course_id');  // -> courses.id

            /**
             * settings_json structure (example):
             * {
             *   "previous_module_completed": 0/1,
             *   "assignment_submitted": 0/1,
             *   "exam_submitted": 0/1,
             *   "coding_test_submitted": 0/1
             * }
             */
            $table->json('settings_json')->nullable();

            // Audit
            $table->unsignedBigInteger('created_by')->nullable();
            $table->unsignedBigInteger('updated_by')->nullable();

            // Timestamps
            $table->timestamp('created_at')->useCurrent();
            $table->timestamp('updated_at')->useCurrent()->useCurrentOnUpdate();

            // Indexes
            // ✅ enforce one settings row per (batch_id, course_id)
            $table->unique(['batch_id', 'course_id'], 'uq_batch_course_module_settings');

            // Optional extra index (usually redundant if unique exists, but fine if you want)
            // $table->index(['batch_id', 'course_id']);

            // Foreign keys
            $table->foreign('batch_id')
                ->references('id')->on('batches')
                ->cascadeOnDelete();

            $table->foreign('course_id')
                ->references('id')->on('courses')
                ->cascadeOnDelete();

            $table->foreign('created_by')
                ->references('id')->on('users')
                ->nullOnDelete();

            $table->foreign('updated_by')
                ->references('id')->on('users')
                ->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('batch_course_module_settings');
    }
};
