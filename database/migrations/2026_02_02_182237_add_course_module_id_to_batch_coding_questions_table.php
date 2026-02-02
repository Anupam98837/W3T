<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('batch_coding_questions', function (Blueprint $table) {
            $table->foreignId('course_module_id')
                ->nullable()
                ->after('question_id')          // position (change if you want)
                ->constrained('course_modules') // references id on course_modules
                ->nullOnDelete();               // if module deleted => set null
        });
    }

    public function down(): void
    {
        Schema::table('batch_coding_questions', function (Blueprint $table) {
            $table->dropForeign(['course_module_id']);
            $table->dropColumn('course_module_id');
        });
    }
};
