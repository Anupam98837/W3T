<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('coding_questions', function (Blueprint $table) {
            if (!Schema::hasColumn('coding_questions', 'total_attempts')) {
                // Global attempt cap for this question (default 1)
                $table->unsignedInteger('total_attempts')->default(1)->after('sort_order');
            }
        });
    }

    public function down(): void
    {
        Schema::table('coding_questions', function (Blueprint $table) {
            if (Schema::hasColumn('coding_questions', 'total_attempts')) {
                $table->dropColumn('total_attempts');
            }
        });
    }
};
