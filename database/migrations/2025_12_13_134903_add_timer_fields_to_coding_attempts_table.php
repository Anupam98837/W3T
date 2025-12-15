<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('coding_attempts', function (Blueprint $table) {
            if (!Schema::hasColumn('coding_attempts', 'time_limit_sec')) {
                $table->unsignedInteger('time_limit_sec')->nullable()->after('attempt_no');
            }

            if (!Schema::hasColumn('coding_attempts', 'expires_at')) {
                $table->timestamp('expires_at')->nullable()->after('server_started_at');
            }

            // Optional index (good for querying active/expired attempts fast)
            if (!Schema::hasColumn('coding_attempts', 'expires_at')) {
                // (index added above column, so this block won't run)
            } else {
                // safe guard: add index if not exists is not built-in, so just attempt
            }
        });

        // safer: add index in separate call (won't error if re-run)
        Schema::table('coding_attempts', function (Blueprint $table) {
            try { $table->index('expires_at', 'idx_ca_expires'); } catch (\Throwable $e) {}
        });
    }

    public function down(): void
    {
        Schema::table('coding_attempts', function (Blueprint $table) {
            try { $table->dropIndex('idx_ca_expires'); } catch (\Throwable $e) {}

            if (Schema::hasColumn('coding_attempts', 'expires_at')) {
                $table->dropColumn('expires_at');
            }
            if (Schema::hasColumn('coding_attempts', 'time_limit_sec')) {
                $table->dropColumn('time_limit_sec');
            }
        });
    }
};
