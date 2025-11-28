<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('batch_message_reads', function (Blueprint $table) {
            $table->bigIncrements('id');

            // FK to batch_messages.id
            $table->unsignedBigInteger('message_id');

            // FK to users.id (student / instructor / admin / superadmin)
            $table->unsignedBigInteger('user_id');

            // Canonical role from CheckRole (student, instructor, admin, superadmin, etc.)
            $table->string('user_role', 64)->nullable();

            // When the message was first seen/read by this user
            $table->timestampTz('read_at')->nullable();

            // Optional: which client / channel (web, mobile, api, etc.)
            $table->string('read_from', 32)->nullable();

            // Extra info (ip, user agent, etc.) if you ever want it
            $table->json('meta')->nullable();

            // For debugging / audits
            $table->timestampsTz();

            // Indexes
            $table->index(['message_id', 'user_id']);
            $table->index('user_id');
            $table->index('read_at');

            // One row per (message, user)
            $table->unique(['message_id', 'user_id'], 'msg_user_unique');
        });

        // Add FKs separately so down() can drop them safely
        Schema::table('batch_message_reads', function (Blueprint $table) {
            $table->foreign('message_id')
                ->references('id')
                ->on('batch_messages')
                ->cascadeOnDelete();

            $table->foreign('user_id')
                ->references('id')
                ->on('users')
                ->cascadeOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('batch_message_reads', function (Blueprint $table) {
            try { $table->dropForeign(['message_id']); } catch (\Throwable $e) {}
            try { $table->dropForeign(['user_id']); } catch (\Throwable $e) {}
        });

        Schema::dropIfExists('batch_message_reads');
    }
};
