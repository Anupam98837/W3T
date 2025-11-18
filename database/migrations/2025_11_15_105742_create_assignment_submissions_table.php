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
        Schema::create('assignment_submissions', function (Blueprint $table) {
            // Primary
            $table->bigIncrements('id');

            // External UUID
            $table->char('uuid', 36)->unique();

            // Foreign keys (cascade on delete & update)
            $table->unsignedBigInteger('assignment_id');
            $table->unsignedBigInteger('course_id');
            $table->unsignedBigInteger('course_module_id');
            $table->unsignedBigInteger('batch_id');
            $table->unsignedBigInteger('student_id');

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
            $table->unsignedBigInteger('graded_by')->nullable();
            $table->text('grader_note')->nullable();
            $table->longText('feedback_html')->nullable();
            $table->boolean('feedback_visible')->default(true);

            // Resubmission / versioning
            $table->unsignedBigInteger('resubmission_of_id')->nullable()->index();
            $table->integer('version_no')->default(1);

            // JSON flags / metadata
            $table->json('flags_json')->nullable();
            $table->json('metadata')->nullable();

            // Timestamps / soft deletes
            $table->timestamps(); // created_at, updated_at
            $table->softDeletes(); // deleted_at

            // Additional indexes for fast filters/joins
            $table->index(['assignment_id', 'student_id', 'attempt_no'], 'idx_assignment_student_attempt');
            $table->index(['course_id', 'course_module_id', 'batch_id', 'student_id'], 'idx_course_module_batch_student');
        });

        // Add foreign key constraints (with cascade on delete & update)
        Schema::table('assignment_submissions', function (Blueprint $table) {
            $table->foreign('assignment_id')
                ->references('id')->on('assignments')
                ->onDelete('cascade')->onUpdate('cascade');

            $table->foreign('course_id')
                ->references('id')->on('courses')
                ->onDelete('cascade')->onUpdate('cascade');

            $table->foreign('course_module_id')
                ->references('id')->on('course_modules')
                ->onDelete('cascade')->onUpdate('cascade');

            $table->foreign('batch_id')
                ->references('id')->on('batches')
                ->onDelete('cascade')->onUpdate('cascade');

            $table->foreign('student_id')
                ->references('id')->on('users')
                ->onDelete('cascade')->onUpdate('cascade');

            $table->foreign('graded_by')
                ->references('id')->on('users')
                ->onDelete('cascade')->onUpdate('cascade');

            // Self-referential FK for resubmissions
            $table->foreign('resubmission_of_id')
                ->references('id')->on('assignment_submissions')
                ->onDelete('cascade')->onUpdate('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Drop FKs first (safer)
        Schema::table('assignment_submissions', function (Blueprint $table) {
            $fkNames = [
                'assignment_submissions_assignment_id_foreign',
                'assignment_submissions_course_id_foreign',
                'assignment_submissions_course_module_id_foreign',
                'assignment_submissions_batch_id_foreign',
                'assignment_submissions_student_id_foreign',
                'assignment_submissions_graded_by_foreign',
                'assignment_submissions_resubmission_of_id_foreign',
            ];

            foreach ($fkNames as $fk) {
                // suppress errors if FK doesn't exist
                try {
                    $table->dropForeign($fk);
                } catch (\Throwable $e) {
                    // ignore
                }
            }
        });

        Schema::dropIfExists('assignment_submissions');
    }
};
 