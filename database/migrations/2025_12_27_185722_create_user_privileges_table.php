<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('user_privileges', function (Blueprint $table) {

            $table->id();

            // UUID
            $table->uuid('uuid')->unique();

            // One row per user
            $table->unsignedBigInteger('user_id');

            /**
             * ✅ TREE JSON STRUCTURE
             * [
             *   {
             *     "id": 1,                // header (dashboard_menu.is_dropdown_head = 1)
             *     "children": [
             *       {
             *         "id": 2,            // page (dashboard_menu.id)
             *         "privileges": [
             *           { "id": 1, "action": "add" },
             *           { "id": 2, "action": "edit" }
             *         ]
             *       }
             *     ]
             *   }
             * ]
             */
            $table->json('privileges')->nullable();

            // Audit
            $table->unsignedBigInteger('assigned_by')->nullable();
            $table->string('created_at_ip', 45)->nullable();

            $table->timestamps();
            $table->softDeletes();

            // One row per user
            $table->unique('user_id', 'unique_user_privileges_user');

            // FKs
            $table->foreign('user_id')
                ->references('id')->on('users')
                ->cascadeOnDelete();

            $table->foreign('assigned_by')
                ->references('id')->on('users')
                ->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('user_privileges');
    }
};
