<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('coding_attempts', function (Blueprint $table) {
            // ✅ add nullable column
            $table->unsignedBigInteger('course_module_id')
                  ->nullable()
                  ->after('batch_coding_question_id');

            // ✅ index (short name)
            $table->index('course_module_id', 'idx_ca_cm');

            // ✅ FK (keep only if your DB supports FKs properly)
            $table->foreign('course_module_id', 'fk_ca_cm')
                  ->references('id')->on('course_modules')
                  ->onDelete('set null');
        });
    }

    public function down(): void
    {
        Schema::table('coding_attempts', function (Blueprint $table) {
            $table->dropForeign('fk_ca_cm');
            $table->dropIndex('idx_ca_cm');
            $table->dropColumn('course_module_id');
        });
    }
};
