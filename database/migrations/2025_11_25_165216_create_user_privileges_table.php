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
        Schema::create('user_privileges', function (Blueprint $table) {

            $table->id(); // BIGSERIAL PRIMARY KEY

            // UUID (unique)
            $table->uuid('uuid')->unique();

            // Foreign Keys
            $table->unsignedBigInteger('user_id');
            $table->unsignedBigInteger('privilege_id');

            $table->foreign('user_id')
                ->references('id')->on('users')
                ->onDelete('cascade');

            $table->foreign('privilege_id')
                ->references('id')->on('privileges')
                ->onDelete('cascade');

            // Audit time columns
            $table->timestamp('created_at')->useCurrent();
            $table->timestamp('updated_at')->useCurrent()->useCurrentOnUpdate();

            // Audit information
            $table->unsignedBigInteger('created_by')->nullable();
            $table->foreign('created_by')
                ->references('id')->on('users')
                ->nullOnDelete();

            $table->string('created_at_ip', 45)->nullable();

            // Soft delete
            $table->timestamp('deleted_at')->nullable();

            // Unique constraint: A user cannot have the same privilege more than once
            $table->unique(['user_id', 'privilege_id'], 'unique_user_privilege');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('user_privileges');
    }
};
