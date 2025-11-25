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
        Schema::create('batch_quizzes', function (Blueprint $table) {
            $table->engine = 'InnoDB';

            // Primary Key
            $table->bigIncrements('id');

            // UUID identifier
            $table->char('uuid', 36);

            // Foreign keys
            $table->foreignId('batch_id')
                ->constrained('batches')
                ->cascadeOnDelete();

            // IMPORTANT: your actual table name is "quizz"
            $table->foreignId('quiz_id')
                ->constrained('quizz')
                ->cascadeOnDelete();

            // Status fields
            $table->enum('status', ['active', 'inactive', 'archived'])
                ->default('active')
                ->index();

            $table->unsignedInteger('display_order')->default(1);

            // Availability
            $table->timestamp('available_from')->nullable();
            $table->timestamp('available_until')->nullable();

            // Assignment timestamps
            $table->timestamp('assigned_at')->useCurrent()->nullable();
            $table->timestamp('unassigned_at')->nullable();

            // Flags
            $table->boolean('publish_to_students')->default(false);
            $table->boolean('assign_status')->default(false);

            // Audit
            $table->foreignId('created_by')
                ->constrained('users')
                ->cascadeOnDelete();

            $table->timestamp('created_at')->useCurrent();
            $table->string('created_at_ip', 45)->nullable();
            $table->timestamp('updated_at')->useCurrent()->useCurrentOnUpdate();

            // Soft delete
            $table->timestamp('deleted_at')->nullable()->index();

            // Metadata
            $table->longText('metadata')
                ->collation('utf8mb4_bin')
                ->nullable();

            // Attempt count
            $table->integer('attempt_allowed')->nullable()->default(1);

            // Extra index
            $table->index('uuid');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('batch_quizzes', function (Blueprint $table) {
            // Drop foreign keys before dropping the table
            $table->dropForeign(['batch_id']);
            $table->dropForeign(['quiz_id']);
            $table->dropForeign(['created_by']);
        });

        Schema::dropIfExists('batch_quizzes');
    }
};
