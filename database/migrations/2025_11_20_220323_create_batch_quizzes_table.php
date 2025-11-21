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
 
            $table->bigIncrements('id');

            $table->char('uuid', 36)->unique();
 
            $table->unsignedBigInteger('batch_id');

            $table->unsignedBigInteger('quiz_id');
 
            // quiz visibility status

            $table->enum('status', ['active', 'inactive', 'archived'])

                  ->default('active');
 
            // order of quiz inside a batch

            $table->unsignedInteger('display_order')->default(1);
 
            // availability schedule

            $table->timestamp('available_from')->nullable();

            $table->timestamp('available_until')->nullable();
 
            // publish permission

            $table->boolean('publish_to_students')->default(false);
 
            // audit fields

            $table->unsignedBigInteger('created_by');

            $table->timestamp('created_at')->useCurrent();

            $table->string('created_at_ip', 45)->nullable();

            $table->timestamp('updated_at')->useCurrent()->useCurrentOnUpdate();
 
            // soft delete

            $table->timestamp('deleted_at')->nullable();
 
            // additional settings

            $table->json('metadata')->nullable();
 
            // indexes

            $table->index('batch_id', 'idx_batch_quizzes_batch_id');

            $table->index('quiz_id', 'idx_batch_quizzes_quiz_id');

            $table->index('status', 'idx_batch_quizzes_status');

            $table->index('deleted_at', 'idx_batch_quizzes_deleted_at');
 
            // unique constraint

            $table->unique(['batch_id', 'quiz_id'], 'uniq_batch_quiz');
 
            // foreign keys

            $table->foreign('batch_id', 'fk_batch_quizzes_batch')

                  ->references('id')->on('batches')

                  ->onDelete('cascade');
 
            $table->foreign('quiz_id', 'fk_batch_quizzes_quiz')

                  ->references('id')->on('quizzes')

                  ->onDelete('cascade');
 
            $table->foreign('created_by', 'fk_batch_quizzes_creator')

                  ->references('id')->on('users');

        });

    }
 
    /**

     * Reverse the migrations.

     */

    public function down(): void

    {

        Schema::dropIfExists('batch_quizzes');

    }

};

 