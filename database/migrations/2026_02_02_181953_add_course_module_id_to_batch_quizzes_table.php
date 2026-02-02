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
        Schema::table('batch_quizzes', function (Blueprint $table) {
            // Add nullable FK column
            $table->foreignId('course_module_id')
                ->nullable()
                ->after('quiz_id') // change position if you want
                ->constrained('course_modules')
                ->nullOnDelete();  // if course_module deleted => set null
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('batch_quizzes', function (Blueprint $table) {
            // Drop FK first, then column
            $table->dropForeign(['course_module_id']);
            $table->dropColumn('course_module_id');
        });
    }
};
