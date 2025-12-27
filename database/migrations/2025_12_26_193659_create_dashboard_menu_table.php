<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('dashboard_menu', function (Blueprint $table) {

            // Primary Key
            $table->bigIncrements('id');

            /**
             * Self FK: parent_id
             * - NULL  => root menu
             * - value => child of another menu (dashboard_menu.id)
             */
            $table->unsignedBigInteger('parent_id')->nullable();

            // UUID (UNIQUE)
            $table->char('uuid', 36)->unique();

            // order within siblings
            $table->unsignedInteger('position')->default(0);

            // Name (UNIQUE globally)
            $table->string('name', 150)->unique();

            // icon_class nullable
            $table->string('icon_class', 120)->nullable();

            // href nullable
            $table->string('href', 255)->nullable();

            // Optional description
            $table->text('description')->nullable();

            // Dropdown head flag
            $table->unsignedTinyInteger('is_dropdown_head')->default(0);

            // Status with default 'Active'
            $table->string('status', 20)->default('Active');

            // Timestamps with proper defaults
            $table->timestamp('created_at')->useCurrent();
            $table->timestamp('updated_at')->useCurrent()->useCurrentOnUpdate();

            // Soft delete timestamp (your style)
            $table->timestamp('deleted_at')->nullable();

            // Audit fields
            $table->unsignedBigInteger('created_by')->nullable();
            $table->unsignedBigInteger('updated_by')->nullable();        // ✅ added
            $table->string('created_at_ip', 45)->nullable();
            $table->string('updated_at_ip', 45)->nullable();             // ✅ added

            // Indexes
            $table->index('created_by');
            $table->index('updated_by');                                // ✅ added
            $table->index('parent_id');
            $table->index(['parent_id', 'position']);                    // ✅ ordering within siblings

            // FK: created_by -> users.id
            $table->foreign('created_by')
                ->references('id')
                ->on('users')
                ->nullOnDelete();

            // FK: updated_by -> users.id
            $table->foreign('updated_by')
                ->references('id')
                ->on('users')
                ->nullOnDelete();

            // Self FK: parent_id -> dashboard_menu.id
            $table->foreign('parent_id')
                ->references('id')
                ->on('dashboard_menu')
                ->nullOnDelete();
        });
    }

    public function down(): void
    {
        if (Schema::hasTable('dashboard_menu')) {
            Schema::table('dashboard_menu', function (Blueprint $table) {
                try { $table->dropForeign(['parent_id']); } catch (\Throwable $e) {}
                try { $table->dropForeign(['created_by']); } catch (\Throwable $e) {}
                try { $table->dropForeign(['updated_by']); } catch (\Throwable $e) {}
            });
        }

        Schema::dropIfExists('dashboard_menu');
    }
};
