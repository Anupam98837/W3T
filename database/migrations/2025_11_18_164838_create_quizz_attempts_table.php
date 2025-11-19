<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('quizz_attempts', function (Blueprint $t) {
            $t->bigIncrements('id');

            // Identity & linking
            $t->char('uuid', 36)->unique();
            $t->unsignedBigInteger('quiz_id');
            $t->char('quiz_uuid', 36)->nullable()->index();
            $t->unsignedBigInteger('user_id')->index(); // users.id (role=student)

            // Authoritative timing (server-side)
            $t->timestamp('server_started_at')->nullable()->index();
            $t->integer('server_time_limit_sec')->nullable(); // snapshot from quizz.total_time * 60
            $t->timestamp('server_deadline_at')->nullable()->index();

            // Client telemetry (for drift/tamper analysis; optional but useful)
            $t->timestamp('client_started_at')->nullable();
            $t->timestamp('last_heartbeat_at')->nullable()->index();
            $t->integer('client_reported_left_sec')->nullable();
            $t->integer('time_drift_sec')->nullable(); // server computed (client_left - real_left)

            // Lifecycle
            $t->enum('status', ['in_progress','submitted','auto_submitted','expired','canceled'])
              ->default('in_progress')->index();
            $t->string('submit_reason', 32)->nullable(); // manual|timeout|force|system
            $t->timestamp('submitted_at')->nullable();
            $t->unsignedBigInteger('submitted_by')->nullable(); // usually same as user_id

            // Forensics / audit
            $t->string('ip', 64)->nullable();
            $t->string('user_agent', 512)->nullable();
            $t->json('auth_snapshot')->nullable();
            $t->string('created_at_ip', 64)->nullable();

            $t->timestamps();
            $t->softDeletes();

            // Simple FKs (no cascade to keep history even if quiz/user removed)
            $t->index(['quiz_id', 'user_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('quizz_attempts');
    }
};
