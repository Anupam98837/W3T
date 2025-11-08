<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('study_materials', function (Blueprint $table) {
            $table->bigIncrements('id');

            // External id & routing
            $table->char('uuid', 36)->unique();

            // Parents (required)
            $table->unsignedBigInteger('course_id')->index();
            $table->unsignedBigInteger('course_module_id')->index();
            $table->unsignedBigInteger('batch_id')->index();

            // Content
            $table->string('title', 255);
            $table->string('slug', 140)->unique();
            $table->longText('description')->nullable();

            $table->json('attachment')->nullable();
            $table->unsignedSmallInteger('attachment_count')->default(0);

            // Enforce “view only” at API layer; can switch later if needed
            $table->enum('view_policy', ['inline_only','downloadable'])->default('inline_only');

            // Audit
            $table->unsignedBigInteger('created_by')->index();
            $table->timestamps();
            $table->softDeletes();

            // Helpful compound index
            $table->index(['course_id', 'course_module_id', 'batch_id'], 'sm_course_module_batch_idx');

            // FKs (adjust onDelete to your preference)
            $table->foreign('course_id')->references('id')->on('courses')->onDelete('cascade');
            $table->foreign('course_module_id')->references('id')->on('course_modules')->onDelete('cascade');
            $table->foreign('batch_id')->references('id')->on('batches')->onDelete('cascade');
            $table->foreign('created_by')->references('id')->on('users')->onDelete('restrict');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('study_materials');
    }
};
