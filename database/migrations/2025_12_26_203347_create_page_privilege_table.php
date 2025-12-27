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
        Schema::create('page_privilege', function (Blueprint $table) {
            /* =========================
             * Primary / Identifiers
             * ========================= */
            $table->bigIncrements('id');                 // PK
            $table->char('uuid', 36)->unique();          // external UUID

            // Global privilege code (for backend checks)
            $table->string('key', 120)->unique();

            /* =========================
             *  Relationships
             * ========================= */
            $table->unsignedBigInteger('dashboard_menu_id'); // FK -> dashboard_menu(id)

            /* =========================
             *  Core Privilege Fields
             * ========================= */
            $table->string('action', 80);               // action name
            $table->text('description')->nullable();    // optional UI description
            $table->unsignedInteger('order_no')->nullable();
            $table->string('status', 20)->default('Active');

            /* =========================
             *  Assigned APIs
             * ========================= */
            $table->json('assigned_apis')->nullable();
            $table->json('meta')->nullable();

            /* =========================
             *  Audit / Timestamps
             * ========================= */
            $table->timestamp('created_at')->useCurrent();
            $table->timestamp('updated_at')
                  ->useCurrent()
                  ->useCurrentOnUpdate();

            $table->unsignedBigInteger('created_by')->nullable();
            $table->string('created_at_ip', 45)->nullable();

            // Soft delete
            $table->timestamp('deleted_at')->nullable();

            /* =========================
             *  Indexes & Constraints
             * ========================= */
            $table->index('dashboard_menu_id');
            $table->index('action');
            $table->index('status');
            $table->index('created_by');

            // Prevent duplicate actions inside the same dashboard menu
            $table->unique(['dashboard_menu_id', 'action'], 'page_privilege_menu_action_unique');

            // FKs
            $table->foreign('dashboard_menu_id')
                  ->references('id')
                  ->on('dashboard_menu')
                  ->cascadeOnDelete();

            $table->foreign('created_by')
                  ->references('id')
                  ->on('users')
                  ->nullOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('page_privilege');
    }
};
