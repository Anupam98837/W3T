<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        $driver = Schema::getConnection()->getDriverName();

        Schema::table('assignments', function (Blueprint $table) use ($driver) {
            // add uuid if missing
            if (!Schema::hasColumn('assignments', 'uuid')) {
                $table->char('uuid', 36)->unique()->after('id');
            }

            // add allowed_submission_types JSON column
            if (!Schema::hasColumn('assignments', 'allowed_submission_types')) {
                if ($driver === 'pgsql') {
                    $table->json('allowed_submission_types')->default(DB::raw("'[]'::json"))->after('submission_type');
                } else {
                    $table->json('allowed_submission_types')->nullable()->after('submission_type');
                }
            }

            // ensure attachments_json exists
            if (!Schema::hasColumn('assignments', 'attachments_json')) {
                if ($driver === 'pgsql') {
                    $table->json('attachments_json')->nullable()->default(DB::raw("'[]'::json"))->after('late_penalty_percent');
                } else {
                    $table->json('attachments_json')->nullable()->after('late_penalty_percent');
                }
            }

            // add metadata if missing
            if (!Schema::hasColumn('assignments', 'metadata')) {
                if ($driver === 'pgsql') {
                    $table->json('metadata')->nullable()->default(DB::raw("'{}'::json"))->after('deleted_at');
                } else {
                    $table->json('metadata')->nullable()->after('deleted_at');
                }
            }

            // add created_by if missing
            if (!Schema::hasColumn('assignments', 'created_by')) {
                $table->unsignedBigInteger('created_by')->nullable()->after('attachments_json');
            }

            // add standard indexes if missing
            if (!Schema::hasColumn('assignments', 'course_id') === false && !Schema::hasColumn('assignments', 'course_id')) {
                // nothing: course_id must exist earlier; we don't auto-create complex columns here
            }
            // add helpful indexes (if not already)
            if (!Schema::hasColumn('assignments', 'status')) {
                $table->string('status', 20)->default('draft')->after('instruction');
            }
        });

        // attempt to add FK constraints -- wrapped in try/catch because they might already exist
        try {
            Schema::table('assignments', function (Blueprint $table) {
                // Add foreign key constraints only if columns exist and no FK present
                if (Schema::hasColumn('assignments', 'course_id')) {
                    $sm = Schema::getConnection()->getDoctrineSchemaManager();
                    $diff = true; // we just try adding; DB will throw if exists
                    $table->foreign('course_id')->references('id')->on('courses')->cascadeOnDelete();
                }
                if (Schema::hasColumn('assignments', 'course_module_id')) {
                    $table->foreign('course_module_id')->references('id')->on('course_modules')->cascadeOnDelete();
                }
                if (Schema::hasColumn('assignments', 'batch_id')) {
                    $table->foreign('batch_id')->references('id')->on('batches')->cascadeOnDelete();
                }
                if (Schema::hasColumn('assignments', 'created_by')) {
                    $table->foreign('created_by')->references('id')->on('users')->nullOnDelete();
                }
            });
        } catch (\Throwable $e) {
            // ignore FK add errors (likely constraints already exist)
        }
    }

    public function down(): void
    {
        Schema::table('assignments', function (Blueprint $table) {
            if (Schema::hasColumn('assignments', 'allowed_submission_types')) {
                $table->dropColumn('allowed_submission_types');
            }
            if (Schema::hasColumn('assignments', 'attachments_json')) {
                // keep attachments_json if you want historic data; otherwise uncomment to drop
                // $table->dropColumn('attachments_json');
            }
            // do NOT drop uuid/created_by/metadata by default to avoid data loss
        });
    }
};
