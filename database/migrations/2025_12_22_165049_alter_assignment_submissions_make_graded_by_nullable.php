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
        // 1) Drop FK first (if it exists), then make graded_by nullable, then re-add FK.
        try {
            Schema::table('assignment_submissions', function (Blueprint $table) {
                $table->dropForeign(['graded_by']);
            });
        } catch (\Throwable $e) {
            // FK may not exist / may have a different name - ignore safely
        }

        // 2) Make graded_by nullable (MySQL/MariaDB-safe)
        if (Schema::getConnection()->getDriverName() === 'mysql') {
            DB::statement("ALTER TABLE `assignment_submissions` MODIFY `graded_by` BIGINT UNSIGNED NULL");
        } else {
            // Other DBs: may require doctrine/dbal for change()
            Schema::table('assignment_submissions', function (Blueprint $table) {
                $table->unsignedBigInteger('graded_by')->nullable()->change();
            });
        }

        // 3) Re-add FK with nullOnDelete (nullable FK is correct for ungraded submissions)
        try {
            Schema::table('assignment_submissions', function (Blueprint $table) {
                $table->foreign('graded_by')
                    ->references('id')
                    ->on('users')
                    ->nullOnDelete();
            });
        } catch (\Throwable $e) {
            // If FK already exists or can't be added, don't hard-fail migration
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Rollback: attempt to revert to NOT NULL safely by filling NULLs with a fallback user id.
        // If no users exist, we keep it nullable to avoid breaking rollback with invalid FK values.

        try {
            Schema::table('assignment_submissions', function (Blueprint $table) {
                $table->dropForeign(['graded_by']);
            });
        } catch (\Throwable $e) {}

        $fallbackUserId = (int) (DB::table('users')->min('id') ?? 0);

        if ($fallbackUserId > 0) {
            DB::table('assignment_submissions')
                ->whereNull('graded_by')
                ->update(['graded_by' => $fallbackUserId]);
        }

        if (Schema::getConnection()->getDriverName() === 'mysql') {
            if ($fallbackUserId > 0) {
                DB::statement("ALTER TABLE `assignment_submissions` MODIFY `graded_by` BIGINT UNSIGNED NOT NULL");
                // Re-add FK (default name will be assignment_submissions_graded_by_foreign)
                DB::statement("ALTER TABLE `assignment_submissions` 
                              ADD CONSTRAINT `assignment_submissions_graded_by_foreign`
                              FOREIGN KEY (`graded_by`) REFERENCES `users`(`id`)");
            } else {
                // No users => cannot enforce NOT NULL FK safely, keep it nullable
                DB::statement("ALTER TABLE `assignment_submissions` MODIFY `graded_by` BIGINT UNSIGNED NULL");
            }
        } else {
            if ($fallbackUserId > 0) {
                Schema::table('assignment_submissions', function (Blueprint $table) {
                    $table->unsignedBigInteger('graded_by')->nullable(false)->change();
                    $table->foreign('graded_by')->references('id')->on('users');
                });
            } else {
                Schema::table('assignment_submissions', function (Blueprint $table) {
                    $table->unsignedBigInteger('graded_by')->nullable()->change();
                });
            }
        }
    }
};
