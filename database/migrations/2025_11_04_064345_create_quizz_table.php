<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('quizz', function (Blueprint $table) {
            $table->id();

            // creator (no FK per requirement)
            $table->unsignedBigInteger('created_by')->index(); // user id (no foreign key)

            // identity
            $table->uuid('uuid')->unique();

            /* ────────── core ────────── */
            $table->string('quiz_name', 255);                 // title
            $table->text('quiz_description')->nullable();     // rich HTML allowed
            $table->string('quiz_img', 255)->nullable();      // stored path or external URL
            $table->text('instructions')->nullable();         // rules/marking shown before start
            $table->text('note')->nullable();                 // internal/admin note

            /* ────────── visibility ────────── */
            $table->enum('is_public', ['yes', 'no'])->default('no');

            /* ────────── result scheduling ────────── */
            $table->enum('result_set_up_type', ['Immediately', 'Now', 'Schedule'])->default('Immediately');
            $table->timestamp('result_release_date')->nullable();

            /* ────────── quiz-level info ────────── */
            $table->unsignedInteger('total_time')->nullable();       // minutes
            $table->unsignedInteger('total_attempts')->default(1);   // attempts allowed
            $table->unsignedInteger('total_questions')->nullable();

            /* ────────── lifecycle ────────── */
            $table->enum('status', ['active', 'archived'])->default('active')->index();

            /* ────────── metadata & audit ────────── */
            // MySQL 8+: JSON default object; for Postgres use -> DB::raw("'{}'::jsonb")
            $table->json('metadata')->default(DB::raw('(JSON_OBJECT())'));
            $table->string('created_at_ip', 45)->nullable();

            $table->timestamps();
            $table->softDeletes(); // deleted_at + index

            // Intentionally NO foreign keys (standalone table)
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('quizz');
    }
};
