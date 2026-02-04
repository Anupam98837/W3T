<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('quizz_attempt_batch', function (Blueprint $table) {
            // ✅ add nullable column
            $table->unsignedBigInteger('course_module_id')
                  ->nullable()
                  ->after('batch_quiz_id');

            // ✅ index (optional but recommended)
            $table->index('course_module_id');

            // ✅ FK (optional: keep only if your DB/host supports FKs properly)
            $table->foreign('course_module_id')
                  ->references('id')->on('course_modules')
                  ->onDelete('set null');
        });
    }

    public function down(): void
    {
        Schema::table('quizz_attempt_batch', function (Blueprint $table) {
            // drop FK first
            $table->dropForeign(['course_module_id']);

            // drop index + column
            $table->dropIndex(['course_module_id']);
            $table->dropColumn('course_module_id');
        });
    }
};
