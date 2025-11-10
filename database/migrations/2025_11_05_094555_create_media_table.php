<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('media', function (Blueprint $table) {
            $table->bigIncrements('id');

            // Identity
            $table->char('uuid', 36)->unique();

            // Basic info for listing/search
            $table->string('title', 255)->nullable();
            $table->text('description')->nullable();
            $table->string('alt_text', 255)->nullable();

            // File details (stored physically in public/AllMedia with a unique filename)
            $table->enum('category', ['image','video','audio','document','archive','other'])->default('other');
            $table->string('mime_type', 127)->nullable();
            $table->string('ext', 16)->nullable();
            $table->unsignedBigInteger('size_bytes')->nullable();

            // Media-specific (nullable when N/A)
            $table->unsignedInteger('width')->nullable();           // images/video
            $table->unsignedInteger('height')->nullable();          // images/video
            $table->decimal('duration_seconds', 10, 3)->nullable(); // video/audio

            // Access URL (absolute, includes APP_URL)
            $table->string('url', 1024)->unique();

            // Optional tagging to know where it is used from (quiz/question/course/etc.)
            $table->string('usage_tag', 50)->nullable()->index();   // e.g., 'quiz', 'question', 'course'

            // Extra room for anything caller-specific
            $table->json('metadata')->nullable();

            // Status & audit
            $table->enum('status', ['active','archived'])->default('active');
            $table->unsignedBigInteger('created_by')->nullable();
            $table->unsignedBigInteger('updated_by')->nullable();

            $table->timestamps();
            $table->softDeletes();

            // FKs (nullable on delete so media rows survive even if user is gone)
            $table->foreign('created_by')->references('id')->on('users')->nullOnDelete();
            $table->foreign('updated_by')->references('id')->on('users')->nullOnDelete();

            // Helpful indexes
            $table->index(['category','status']);
            $table->index(['deleted_at']);
        });

        // Optional: simple FULLTEXT for quick search in picker (MySQL 8+, InnoDB)
        try {
            DB::statement("ALTER TABLE media ADD FULLTEXT fulltext_media (title, description, alt_text)");
        } catch (\Throwable $e) {
            // ignore if engine/collation doesn't support it
        }
    }

    public function down(): void
    {
        try { DB::statement("ALTER TABLE media DROP INDEX fulltext_media"); } catch (\Throwable $e) {}
        Schema::dropIfExists('media');
    }
};
