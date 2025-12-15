<?php
// database/migrations/2025_10_17_000010_create_job_messages_table.php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('job_messages', function (Blueprint $table) {
            $table->id();

            $table->foreignId('job_id')
                  ->constrained('job_details')
                  ->cascadeOnDelete();

            // who sent (from CheckRole token)
            $table->string('sender_type', 160)->nullable();              // e.g. App\Models\Admin
            $table->unsignedBigInteger('sender_id')->nullable();
            $table->enum('sender_role', ['admin','assignee'])->nullable();   // quick filter

            // message body (rich text from editor)
            $table->longText('message_html')->nullable();                // sanitized HTML you save
            $table->text('message_text')->nullable();                    // optional: plain text extract for search

            $table->boolean('has_attachments')->default(false);
            $table->json('metadata')->nullable();                        // anything extra

            $table->timestampsTz();

            $table->index(['job_id', 'created_at']);
            $table->index(['sender_type', 'sender_id']);
            $table->index('sender_role');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('job_messages');
    }
};
