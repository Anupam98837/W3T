<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('course_modules', function (Blueprint $table) {
            $table->engine = 'InnoDB';

            // Keys
            $table->bigIncrements('id');
            $table->char('uuid', 36)->unique();

            // Relations
            $table->unsignedBigInteger('course_id');
            $table->unsignedBigInteger('created_by')->nullable();

            // Content
            $table->string('title', 255);
            $table->text('short_description')->nullable();
            $table->longText('long_description')->nullable();

            // Ordering & state
            $table->integer('order_no')->default(0);
            $table->string('status', 20)->default('draft'); // draft|published|archived

            // JSON meta (MySQL: nullable is safer than trying to default to {})
            $table->json('metadata')->nullable();

            // Audit
            $table->ipAddress('created_at_ip')->nullable();

            // Timestamps
            $table->timestamp('created_at')->useCurrent();
            $table->timestamp('updated_at')->useCurrent()->useCurrentOnUpdate();

            // Soft delete + indexes
            $table->softDeletes();
            $table->index('deleted_at');
            $table->index(['course_id', 'order_no']);
            $table->index('status');

            // FKs
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
                "ALTER TABLE `course_modules`
                 ADD CONSTRAINT `course_modules_status_check`
                 CHECK (`status` IN ('draft','published','archived'))"
            );
        } catch (\Throwable $e) {
            // Safe no-op on MySQL versions that don't enforce CHECK constraints
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('course_modules');
    }
};
