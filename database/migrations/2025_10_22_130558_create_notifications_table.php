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
        Schema::create('notifications', function (Blueprint $table) {
            $table->bigIncrements('id'); // Primary Key

            // Core fields
            $table->string('title', 255);
            $table->text('message');

            // JSON fields (MySQL 5.7+ supports JSON natively)
            $table->json('receivers')->nullable();  // [{ "id": <int>, "role": "<str>", "read": 0/1 }]
            $table->json('metadata')->nullable();   // { "job_id": 12, "project_id": 5 }

            // Type / category fields
            $table->string('type', 50)->default('general');   // system, job, project, alert, etc.
            $table->string('link_url', 255)->nullable();      // redirect URL or route

            // Priority and status enums
            $table->enum('priority', ['low', 'normal', 'high', 'urgent'])->default('normal');
            $table->enum('status', ['active', 'archived', 'deleted'])->default('active');

            // Timestamps
            $table->timestamp('created_at')->useCurrent();
            $table->timestamp('updated_at')->useCurrent()->useCurrentOnUpdate();

            // Indexes for performance
            $table->index(['type']);
            $table->index(['priority']);
            $table->index(['status']);
            $table->index(['created_at']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('notifications');
    }
};
