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
        Schema::create('assignments', function (Blueprint $table) {
            // Engine to match: ENGINE=InnoDB
            if (Schema::getConnection()->getDriverName() === 'mysql') {
                $table->engine = 'InnoDB';
            }

            // Columns (1:1 with your SQL)

            // `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
            $table->bigIncrements('id');

            // `uuid` char(36) NOT NULL,
            $table->char('uuid', 36);

            // `course_id` bigint(20) UNSIGNED NOT NULL,
            $table->unsignedBigInteger('course_id');

            // `course_module_id` bigint(20) UNSIGNED NOT NULL,
            $table->unsignedBigInteger('course_module_id');

            // `batch_id` bigint(20) UNSIGNED NOT NULL,
            $table->unsignedBigInteger('batch_id');

            // `title` varchar(255) NOT NULL,
            $table->string('title', 255);

            // `slug` varchar(140) NOT NULL,
            $table->string('slug', 140);

            // `instruction` longtext DEFAULT NULL,
            $table->longText('instruction')->nullable();

            // `status` varchar(20) NOT NULL DEFAULT 'draft',
            $table->string('status', 20)->default('draft');

            // `submission_type` varchar(20) NOT NULL DEFAULT 'file',
            $table->string('submission_type', 20)->default('file');

            // `allowed_submission_types` longtext ... CHECK (json_valid(...)),
            // (MySQL json columns show as longtext + CHECK in dumps)
            $table->json('allowed_submission_types')->nullable();

            // `attempts_allowed` int(11) NOT NULL DEFAULT 1,
            $table->integer('attempts_allowed')->default(1);

            // `total_marks` decimal(6,2) NOT NULL DEFAULT 100.00,
            $table->decimal('total_marks', 6, 2)->default(100.00);

            // `pass_marks` decimal(6,2) DEFAULT NULL,
            $table->decimal('pass_marks', 6, 2)->nullable();

            // `release_at` timestamp NULL DEFAULT NULL,
            $table->timestamp('release_at')->nullable();

            // `due_at` timestamp NULL DEFAULT NULL,
            $table->timestamp('due_at')->nullable();

            // `end_at` timestamp NULL DEFAULT NULL,
            $table->timestamp('end_at')->nullable();

            // `allow_late` tinyint(1) NOT NULL DEFAULT 0,
            $table->boolean('allow_late')->default(0);

            // `late_penalty_percent` decimal(5,2) DEFAULT NULL,
            $table->decimal('late_penalty_percent', 5, 2)->nullable();

            // `attachments_json` longtext ... CHECK (json_valid(...)),
            $table->json('attachments_json')->nullable();

            // `created_by` bigint(20) UNSIGNED DEFAULT NULL,
            $table->unsignedBigInteger('created_by')->nullable();

            // `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
            $table->timestamp('created_at')->useCurrent();

            // `created_at_ip` varchar(45) DEFAULT NULL,
            $table->string('created_at_ip', 45)->nullable();

            // `updated_at` timestamp NOT NULL DEFAULT current_timestamp(),
            $table->timestamp('updated_at')->useCurrent();

            // `deleted_at` timestamp NULL DEFAULT NULL,
            $table->timestamp('deleted_at')->nullable();

            // `metadata` longtext ... DEFAULT '{}' CHECK (json_valid(`metadata`))
            // (again, Laravel json type will dump similarly)
            $table->json('metadata')->default(DB::raw("'{}'"));

            // Indexes (exactly as in dump)
            // ADD PRIMARY KEY (`id`) — already added by bigIncrements
            $table->index('course_id', 'assignments_course_id_index');
            $table->index('course_module_id', 'assignments_course_module_id_index');
            $table->index('batch_id', 'assignments_batch_id_index');
        });

        // Foreign key (exactly as in dump)
        Schema::table('assignments', function (Blueprint $table) {
            $table->foreign('course_id', 'assignments_course_id_foreign')
                  ->references('id')->on('courses')
                  ->onDelete('cascade');
            // Note: your dump only had FK on course_id.
            // No FKs on course_module_id, batch_id, created_by in the SQL you shared,
            // so we don't add them here to stay "exact".
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Drop FK then table
        Schema::table('assignments', function (Blueprint $table) {
            try {
                $table->dropForeign('assignments_course_id_foreign');
            } catch (\Throwable $e) {
                // ignore if it doesn't exist
            }
        });

        Schema::dropIfExists('assignments');
    }
};
