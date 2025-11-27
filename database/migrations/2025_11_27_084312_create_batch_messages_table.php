<?php
// database/migrations/2025_11_27_000000_create_batch_messages_table.php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('batch_messages', function (Blueprint $table) {
            $table->bigIncrements('id');

            // Public identifier (optional but useful for API/URLs)
            $table->char('uuid', 36)->unique();

            // Parent batch (one chat thread per batch)
            $table->foreignId('batch_id')
                  ->constrained('batches')
                  ->cascadeOnDelete();

            // Sender (from CheckRole token)
            $table->string('sender_type', 160)->nullable(); // e.g. App\Models\User
            $table->unsignedBigInteger('sender_id')->nullable();

            // Quick filter by role (W3T roles)
            $table->enum('sender_role', [
                'student',
                'instructor',
                'admin',
                'super_admin',
                'superadmin',
            ])->nullable();

            // Message content
            $table->longText('message_html')->nullable(); // sanitized HTML
            $table->text('message_text')->nullable();     // plain text for search/previews

            // Flags & extras
            $table->boolean('has_attachments')->default(false);
            $table->boolean('is_pinned')->default(false);
            $table->boolean('is_edited')->default(false);
            $table->json('metadata')->nullable();         // attachments, reactions, etc.

            // Timestamps + soft delete
            $table->timestampsTz();
            $table->softDeletesTz();

            // Indexes
            $table->index(['batch_id', 'created_at']);
            $table->index(['sender_type', 'sender_id']);
            $table->index('sender_role');
        });

        // Optional: link sender_id to users, but keep history if user is deleted
        Schema::table('batch_messages', function (Blueprint $table) {
            $table->foreign('sender_id')
                  ->references('id')
                  ->on('users')
                  ->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('batch_messages', function (Blueprint $table) {
            // Drop FKs defensively
            try { $table->dropForeign(['sender_id']); } catch (\Throwable $e) {}
            try { $table->dropForeign(['batch_id']); } catch (\Throwable $e) {}
        });

        Schema::dropIfExists('batch_messages');
    }
};
