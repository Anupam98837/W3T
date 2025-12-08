<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /* ====================== Helpers ====================== */

    private function isMysql(): bool
    {
        return Schema::getConnection()->getDriverName() === 'mysql';
    }

    /**
     * Detect whether the PK `id` of the referenced table is BIGINT (true) or INT (false).
     * Falls back to BIGINT on errors (modern Laravel default).
     */
    private function refPkIsBigInt(string $table): bool
    {
        try {
            if ($this->isMysql()) {
                $db = DB::getDatabaseName();
                $row = DB::selectOne(
                    "SELECT DATA_TYPE FROM INFORMATION_SCHEMA.COLUMNS
                     WHERE TABLE_SCHEMA = ? AND TABLE_NAME = ? AND COLUMN_NAME = 'id' LIMIT 1",
                    [$db, $table]
                );
                if ($row && isset($row->DATA_TYPE)) {
                    return strtolower($row->DATA_TYPE) === 'bigint';
                }
            } else {
                // For Postgres/others, assume bigint (Laravel default for bigIncrements)
                return true;
            }
        } catch (\Throwable $e) {
            // ignore and fall through
        }
        return true;
    }

    /** Add an unsigned FK column with size matching the parent PK. */
    private function addFkColumn(Blueprint $table, string $column, string $refTable): void
    {
        if ($this->refPkIsBigInt($refTable)) {
            $table->unsignedBigInteger($column);
        } else {
            $table->unsignedInteger($column);
        }
    }

    /** Safely try to add a foreign key; ignore errors (mismatch/table missing). */
    private function tryAddFk(string $table, string $column, string $refTable, string $fkName, $onDelete = 'cascade', $onUpdate = 'cascade'): void
    {
        if (!Schema::hasTable($table) || !Schema::hasTable($refTable) || !Schema::hasColumn($table, $column)) {
            return;
        }
        try {
            Schema::table($table, function (Blueprint $tb) use ($column, $refTable, $fkName, $onDelete, $onUpdate) {
                $fb = $tb->foreign($column, $fkName)->references('id')->on($refTable);
                if ($onDelete === 'set null') $fb->nullOnDelete(); else $fb->onDelete($onDelete);
                $fb->onUpdate($onUpdate);
            });
        } catch (\Throwable $e) {
            // Ignore FK errors to keep migration non-breaking
        }
    }

    public function up(): void
    {
        // 1) Create table if missing
        if (!Schema::hasTable('assignment_submissions')) {
            Schema::create('assignment_submissions', function (Blueprint $table) {
                if ($this->isMysql()) {
                    $table->engine = 'InnoDB';
                }

                // Primary
                $table->bigIncrements('id');

                // External UUID
                $table->char('uuid', 36)->unique();

                // FKs (size matched to parents)
                $this->addFkColumn($table, 'assignment_id',     'assignments');
                $this->addFkColumn($table, 'course_id',         'courses');
                $this->addFkColumn($table, 'course_module_id',  'course_modules');
                $this->addFkColumn($table, 'batch_id',          'batches');
                $this->addFkColumn($table, 'student_id',        'users');

                // Attempt / status
                $table->integer('attempt_no')->default(1);
                $table->string('status', 20)->default('submitted')->index();

                // Submission timestamps / lateness
                $table->timestamp('submitted_at')->useCurrent()->index();
                $table->boolean('is_late')->default(false);
                $table->integer('late_minutes')->default(0);

                // Submission metadata
                $table->string('submitted_ip', 45)->nullable();
                $table->longText('content_text')->nullable();
                $table->longText('content_html')->nullable();
                $table->string('link_url', 1024)->nullable();
                $table->string('repo_url', 1024)->nullable();
                $table->json('attachments_json')->nullable();

                // Grading
                $table->decimal('total_marks', 6, 2)->nullable();
                $table->string('grade_letter', 8)->nullable();
                $table->timestamp('graded_at')->nullable()->index();
                $this->addFkColumn($table, 'graded_by', 'users');
                $table->text('grader_note')->nullable();
                $table->longText('feedback_html')->nullable();
                $table->boolean('feedback_visible')->default(true);

                // Resubmission / versioning
                $table->unsignedBigInteger('resubmission_of_id')->nullable()->index(); // self FK uses this table's PK size (bigint)
                $table->integer('version_no')->default(1);

                // JSON flags / metadata
                $table->json('flags_json')->nullable();
                $table->json('metadata')->nullable();

                // Timestamps / soft deletes
                $table->timestamps();
                $table->softDeletes();

                // Additional indexes for fast filters/joins
                $table->index(['assignment_id', 'student_id', 'attempt_no'], 'idx_assignment_student_attempt');
                $table->index(['course_id', 'course_module_id', 'batch_id', 'student_id'], 'idx_course_module_batch_student');
            });
        } else {
            // 2) If table exists, ensure required columns exist (non-breaking)
            Schema::table('assignment_submissions', function (Blueprint $table) {
                if (!Schema::hasColumn('assignment_submissions', 'uuid')) {
                    $table->char('uuid', 36)->nullable(); // make nullable for safe backfill
                }
                foreach ([
                    ['assignment_id', 'assignments'],
                    ['course_id', 'courses'],
                    ['course_module_id', 'course_modules'],
                    ['batch_id', 'batches'],
                    ['student_id', 'users'],
                ] as [$col, $ref]) {
                    if (!Schema::hasColumn('assignment_submissions', $col)) {
                        $this->addFkColumn($table, $col, $ref);
                    }
                }
                if (!Schema::hasColumn('assignment_submissions', 'attempt_no')) {
                    $table->integer('attempt_no')->default(1);
                }
                if (!Schema::hasColumn('assignment_submissions', 'status')) {
                    $table->string('status', 20)->default('submitted')->index();
                }
                if (!Schema::hasColumn('assignment_submissions', 'submitted_at')) {
                    $table->timestamp('submitted_at')->useCurrent()->index();
                }
                if (!Schema::hasColumn('assignment_submissions', 'is_late')) {
                    $table->boolean('is_late')->default(false);
                }
                if (!Schema::hasColumn('assignment_submissions', 'late_minutes')) {
                    $table->integer('late_minutes')->default(0);
                }
                if (!Schema::hasColumn('assignment_submissions', 'submitted_ip')) {
                    $table->string('submitted_ip', 45)->nullable();
                }
                if (!Schema::hasColumn('assignment_submissions', 'content_text')) {
                    $table->longText('content_text')->nullable();
                }
                if (!Schema::hasColumn('assignment_submissions', 'content_html')) {
                    $table->longText('content_html')->nullable();
                }
                if (!Schema::hasColumn('assignment_submissions', 'link_url')) {
                    $table->string('link_url', 1024)->nullable();
                }
                if (!Schema::hasColumn('assignment_submissions', 'repo_url')) {
                    $table->string('repo_url', 1024)->nullable();
                }
                if (!Schema::hasColumn('assignment_submissions', 'attachments_json')) {
                    $table->json('attachments_json')->nullable();
                }
                if (!Schema::hasColumn('assignment_submissions', 'total_marks')) {
                    $table->decimal('total_marks', 6, 2)->nullable();
                }
                if (!Schema::hasColumn('assignment_submissions', 'grade_letter')) {
                    $table->string('grade_letter', 8)->nullable();
                }
                if (!Schema::hasColumn('assignment_submissions', 'graded_at')) {
                    $table->timestamp('graded_at')->nullable()->index();
                }
                if (!Schema::hasColumn('assignment_submissions', 'graded_by')) {
                    $this->addFkColumn($table, 'graded_by', 'users');
                }
                if (!Schema::hasColumn('assignment_submissions', 'grader_note')) {
                    $table->text('grader_note')->nullable();
                }
                if (!Schema::hasColumn('assignment_submissions', 'feedback_html')) {
                    $table->longText('feedback_html')->nullable();
                }
                if (!Schema::hasColumn('assignment_submissions', 'feedback_visible')) {
                    $table->boolean('feedback_visible')->default(true);
                }
                if (!Schema::hasColumn('assignment_submissions', 'resubmission_of_id')) {
                    $table->unsignedBigInteger('resubmission_of_id')->nullable()->index();
                }
                if (!Schema::hasColumn('assignment_submissions', 'version_no')) {
                    $table->integer('version_no')->default(1);
                }
                if (!Schema::hasColumn('assignment_submissions', 'flags_json')) {
                    $table->json('flags_json')->nullable();
                }
                if (!Schema::hasColumn('assignment_submissions', 'metadata')) {
                    $table->json('metadata')->nullable();
                }
                if (!Schema::hasColumn('assignment_submissions', 'created_at')) {
                    $table->timestamps();
                }
                if (!Schema::hasColumn('assignment_submissions', 'deleted_at')) {
                    $table->softDeletes();
                }
            });

            // Backfill & enforce uuid if we just added it nullable
            if (Schema::hasColumn('assignment_submissions', 'uuid')) {
                try {
                    if ($this->isMysql()) {
                        DB::statement("UPDATE `assignment_submissions` SET `uuid` = (SELECT UUID()) WHERE `uuid` IS NULL");
                        DB::statement("ALTER TABLE `assignment_submissions` MODIFY `uuid` CHAR(36) NOT NULL");
                    } else {
                        DB::statement("UPDATE assignment_submissions SET uuid = gen_random_uuid()::text WHERE uuid IS NULL");
                        DB::statement("ALTER TABLE assignment_submissions ALTER COLUMN uuid SET NOT NULL");
                    }
                    // Unique index (safe attempt)
                    try {
                        Schema::table('assignment_submissions', function (Blueprint $t) {
                            $t->unique('uuid', 'assignment_submissions_uuid_unique');
                        });
                    } catch (\Throwable $e) { /* ignore */ }
                } catch (\Throwable $e) { /* ignore */ }
            }
        }

        // 3) Try to add foreign keys (non-breaking, one by one)
        $this->tryAddFk('assignment_submissions', 'assignment_id',    'assignments',     'fk_asnsub_assignment');
        $this->tryAddFk('assignment_submissions', 'course_id',        'courses',         'fk_asnsub_course');
        $this->tryAddFk('assignment_submissions', 'course_module_id', 'course_modules',  'fk_asnsub_course_module');
        $this->tryAddFk('assignment_submissions', 'batch_id',         'batches',         'fk_asnsub_batch');
        $this->tryAddFk('assignment_submissions', 'student_id',       'users',           'fk_asnsub_student');
        // choose cascade or set null for graded_by; keeping cascade to mirror your original:
        $this->tryAddFk('assignment_submissions', 'graded_by',        'users',           'fk_asnsub_graded_by');

        // Self-referential FK
        if (Schema::hasTable('assignment_submissions') && Schema::hasColumn('assignment_submissions', 'resubmission_of_id')) {
            try {
                Schema::table('assignment_submissions', function (Blueprint $tb) {
                    $tb->foreign('resubmission_of_id', 'fk_asnsub_self')
                       ->references('id')->on('assignment_submissions')
                       ->onDelete('cascade')->onUpdate('cascade');
                });
            } catch (\Throwable $e) { /* ignore */ }
        }
    }

    public function down(): void
    {
        if (!Schema::hasTable('assignment_submissions')) {
            return;
        }

        // Drop FKs first, but don't fail if they don't exist
        foreach ([
            'fk_asnsub_assignment',
            'fk_asnsub_course',
            'fk_asnsub_course_module',
            'fk_asnsub_batch',
            'fk_asnsub_student',
            'fk_asnsub_graded_by',
            'fk_asnsub_self',
        ] as $fk) {
            try {
                Schema::table('assignment_submissions', function (Blueprint $tb) use ($fk) {
                    $tb->dropForeign($fk);
                });
            } catch (\Throwable $e) { /* ignore */ }
        }

        Schema::dropIfExists('assignment_submissions');
    }
};