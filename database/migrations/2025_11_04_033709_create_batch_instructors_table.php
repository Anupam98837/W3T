<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('batch_instructors', function (Blueprint $table) {
            $table->bigIncrements('id');

            $table->char('uuid', 36)->unique(); // populate in app code

            $table->unsignedBigInteger('batch_id');
            $table->unsignedBigInteger('user_id');

            // Role & status (enum keeps data clean; aligns with your sheet)
            $table->enum('role_in_batch', ['instructor','tutor','TA','mentor'])
                  ->default('instructor')
                  ->comment('instructor / tutor / TA / mentor');

            $table->enum('assign_status', ['active','standby','replaced','removed'])
                  ->default('active')
                  ->comment('active / standby / replaced / removed');

            $table->timestamp('assigned_at')->nullable();
            $table->timestamp('unassigned_at')->nullable();

            $table->unsignedBigInteger('created_by')->nullable();
            $table->string('created_at_ip', 45)->nullable();

            // Laravel timestamps with NOW() defaults
            $table->timestamp('created_at')->useCurrent();
            $table->timestamp('updated_at')->useCurrent()->useCurrentOnUpdate();

            // Soft delete + index
            $table->softDeletes();
            $table->index('deleted_at');

            // Helpful indexes
            $table->index('batch_id');
            $table->index('user_id');
        });

        // FKs
        Schema::table('batch_instructors', function (Blueprint $table) {
            $table->foreign('batch_id')->references('id')->on('batches')->onDelete('cascade');
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
            $table->foreign('created_by')->references('id')->on('users')->nullOnDelete();
        });

        // Engine-specific JSON default and unique constraint that ignores soft-deleted rows
        $driver = DB::getDriverName();

        if ($driver === 'mysql') {
            // JSON default '{}' and unique (batch_id, user_id, deleted_at)
            DB::statement("ALTER TABLE batch_instructors ADD COLUMN metadata JSON NOT NULL DEFAULT (JSON_OBJECT())");
            DB::statement("ALTER TABLE batch_instructors ADD UNIQUE KEY ux_batch_user_deleted (batch_id, user_id, deleted_at)");
        } elseif ($driver === 'pgsql') {
            DB::statement("ALTER TABLE batch_instructors ADD COLUMN metadata JSONB NOT NULL DEFAULT '{}'::jsonb");
            DB::statement("CREATE UNIQUE INDEX ux_batch_user_deleted ON batch_instructors (batch_id, user_id, deleted_at)");
        } else {
            // Fallback (rare): allow null JSON and still add a composite unique
            Schema::table('batch_instructors', function (Blueprint $table) {
                $table->json('metadata')->nullable();
                $table->unique(['batch_id', 'user_id', 'deleted_at'], 'ux_batch_user_deleted');
            });
        }
    }

    public function down(): void
    {
        // Drop FKs first (avoid errors on some engines)
        Schema::table('batch_instructors', function (Blueprint $table) {
            $table->dropForeign(['batch_id']);
            $table->dropForeign(['user_id']);
            $table->dropForeign(['created_by']);
        });

        Schema::dropIfExists('batch_instructors');
    }
};
