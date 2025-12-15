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
        // Drop old CHECK constraint if exists
        try {
            DB::statement("ALTER TABLE batch_students DROP CONSTRAINT chk_batch_students_status");
        } catch (\Throwable $e) {
            // ignore if not exists
        }

        // Add new CHECK constraint including verified & not_verified
        DB::statement("
            ALTER TABLE batch_students
            ADD CONSTRAINT chk_batch_students_status
            CHECK (
                enrollment_status IN (
                    'enrolled',
                    'waitlisted',
                    'dropped',
                    'completed',
                    'verified',
                    'not_verified'
                )
            )
        ");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Drop new constraint
        try {
            DB::statement("ALTER TABLE batch_students DROP CONSTRAINT chk_batch_students_status");
        } catch (\Throwable $e) {}

        // Restore original CHECK constraint
        DB::statement("
            ALTER TABLE batch_students
            ADD CONSTRAINT chk_batch_students_status
            CHECK (
                enrollment_status IN (
                    'enrolled',
                    'waitlisted',
                    'dropped',
                    'completed'
                )
            )
        ");
    }
};
