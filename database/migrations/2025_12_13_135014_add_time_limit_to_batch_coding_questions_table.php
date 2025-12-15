<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('batch_coding_questions', function (Blueprint $table) {
            if (!Schema::hasColumn('batch_coding_questions', 'time_limit_sec')) {
                $table->unsignedInteger('time_limit_sec')->nullable()->after('attempt_allowed'); 
                // e.g. 1800 = 30 minutes
            }
        });
    }

    public function down(): void
    {
        Schema::table('batch_coding_questions', function (Blueprint $table) {
            if (Schema::hasColumn('batch_coding_questions', 'time_limit_sec')) {
                $table->dropColumn('time_limit_sec');
            }
        });
    }
};
