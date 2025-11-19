<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('notices', function (Blueprint $table) {

            // Primary
            $table->bigIncrements('id');

            // External UUID
            $table->char('uuid', 36)->unique();

            // Parent references
            $table->foreignId('course_id')
                ->constrained('courses')
                ->cascadeOnDelete()
                ->cascadeOnUpdate();

            $table->foreignId('course_module_id')
                ->nullable()
                ->constrained('course_modules')
                ->nullOnDelete()
                ->cascadeOnUpdate();

            $table->foreignId('batch_id')
                ->constrained('batches')
                ->cascadeOnDelete()
                ->cascadeOnUpdate();

            // Visibility / metadata
            $table->string('visibility_scope', 20)
                ->default('batch')
                ->comment('course / batch / module');

            // Heading / slug
            $table->string('title', 255);
            $table->string('slug', 140)->unique();

            // Content
            $table->longText('message_html')->nullable();
            $table->json('attachments_json')->nullable();

            // Priority / status
            $table->string('priority', 20)->default('normal'); // low | normal | high
            $table->string('status', 20)->default('draft');   // draft | published | archived etc.

            // Creator
            $table->foreignId('created_by')
                ->nullable()
                ->constrained('users')
                ->nullOnDelete()
                ->cascadeOnUpdate();

            $table->string('created_at_ip', 45)->nullable();

            // Standard timestamps
            $table->timestamp('created_at')->useCurrent();
            $table->timestamp('updated_at')->useCurrent()->useCurrentOnUpdate();

            // Soft deletes
            $table->softDeletes(); // deleted_at

            // Extra metadata
            $table->json('metadata')->nullable();

            // Indexes
            $table->index('visibility_scope');
            $table->index('priority');
            $table->index('status');
            $table->index('deleted_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('notices');
    }
};
