<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('batches', function (Blueprint $table) {
            $table->id();                                      // BIGINT PK
            $table->char('uuid', 36)->unique();                // External UUID

            // Link to Course
            $table->unsignedBigInteger('course_id');           // FK -> courses(id)

            // Badge / Batch meta
            $table->string('badge_title', 255);                // e.g., "Python Mastery"
            $table->text('badge_description')->nullable();
            $table->string('tagline', 255)->nullable();
            $table->string('featured_image', 512)->nullable(); // absolute/relative URL

            // Delivery mode (enum-like via CHECK), default online
            $table->string('mode', 16)->default('online');     // online|offline|hybrid

            // Social / groups, contact
            $table->json('group_links')->nullable();           // {"whatsapp":"...","telegram":"..."}
            $table->string('contact_number', 32)->nullable();

            // Notes
            $table->text('badge_note')->nullable();

            // Lifecycle/status (archive supported)
            $table->string('status', 20)->default('active');   // active|inactive|archived

            // Schedule
            $table->timestamp('starts_at')->nullable();        // batch start (for UI filters)
            $table->timestamp('ends_at')->nullable();          // batch end

            // Audit
            $table->unsignedBigInteger('created_by');          // FK -> users(id)
            $table->timestamp('created_at')->useCurrent();
            $table->string('created_at_ip', 45)->nullable();   // IPv4/IPv6
            $table->timestamp('updated_at')->useCurrent()->useCurrentOnUpdate();
            $table->softDeletes();                             // deleted_at
            $table->json('metadata')->nullable();              // free-form meta

            // Indexes
            $table->index('course_id');
            $table->index('status');
            $table->index('mode');

            // FKs
            $table->foreign('course_id')->references('id')->on('courses')->onDelete('cascade');
            $table->foreign('created_by')->references('id')->on('users')->onDelete('restrict');
        });

        // Portable CHECK constraints (ignored on older MySQL; safe-wrapped)
        try {
            DB::statement("ALTER TABLE batches
                ADD CONSTRAINT chk_batches_mode
                CHECK (mode IN ('online','offline','hybrid'))");
        } catch (\Throwable $e) {}

        try {
            DB::statement("ALTER TABLE batches
                ADD CONSTRAINT chk_batches_status
                CHECK (status IN ('active','inactive','archived'))");
        } catch (\Throwable $e) {}
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('batches');
    }
};
