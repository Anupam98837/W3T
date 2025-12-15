<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('coding_questions', function (Blueprint $table) {

            if (!Schema::hasColumn('coding_questions', 'total_time_min')) {
                // Total allowed time for this question (in minutes) - nullable
                if (Schema::hasColumn('coding_questions', 'total_attempts')) {
                    $table->unsignedInteger('total_time_min')->nullable()->after('total_attempts');
                } else {
                    $table->unsignedInteger('total_time_min')->nullable()->after('sort_order');
                }
            }
        });
    }

    public function down(): void
    {
        Schema::table('coding_questions', function (Blueprint $table) {
            if (Schema::hasColumn('coding_questions', 'total_time_min')) {
                $table->dropColumn('total_time_min');
            }
        });
    }
};
