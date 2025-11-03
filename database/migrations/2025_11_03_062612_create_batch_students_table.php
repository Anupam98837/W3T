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
        Schema::create('batch_students', function (Blueprint $table) {
            $table->id();                                      // BIGINT PK
            $table->char('uuid', 36)->unique();

            $table->unsignedBigInteger('batch_id');            // FK -> batches(id)
            $table->unsignedBigInteger('user_id');             // FK -> users(id) (student)
            $table->string('enrollment_status', 20)->default('enrolled'); // enrolled|waitlisted|dropped|completed

            $table->timestamp('enrolled_at')->nullable();
            $table->timestamp('completed_at')->nullable();

            // Audit
            $table->unsignedBigInteger('created_by');          // who enrolled
            $table->timestamp('created_at')->useCurrent();
            $table->string('created_at_ip', 45)->nullable();
            $table->timestamp('updated_at')->useCurrent()->useCurrentOnUpdate();
            $table->softDeletes();                             // deleted_at
            $table->json('metadata')->nullable();              // e.g. {"feesPaid":true,"remarks":"Scholarship"}

            // Indexes
            $table->index(['batch_id', 'user_id']);
            $table->index('enrollment_status');

            // Enforce one active enrollment per student per batch
            $table->unique(['batch_id', 'user_id', 'deleted_at'], 'uniq_batch_user_alive');

            // FKs
            $table->foreign('batch_id')->references('id')->on('batches')->onDelete('cascade');
            // Keep student records safe by default; change to cascade if you prefer
            $table->foreign('user_id')->references('id')->on('users')->onDelete('restrict');
            $table->foreign('created_by')->references('id')->on('users')->onDelete('restrict');
        });

        // CHECK constraint for enrollment_status
        try {
            DB::statement("ALTER TABLE batch_students
                ADD CONSTRAINT chk_batch_students_status
                CHECK (enrollment_status IN ('enrolled','waitlisted','dropped','completed'))");
        } catch (\Throwable $e) {}
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('batch_students');
    }
};
