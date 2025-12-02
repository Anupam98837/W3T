<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('user_privileges', function (Blueprint $table) {

            $table->id(); // BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY

            // UUID
            $table->uuid('uuid')->unique();

            // Foreign keys
            $table->unsignedBigInteger('user_id');
            $table->unsignedBigInteger('privilege_id');

            // Audit information
            $table->unsignedBigInteger('created_by')->nullable();
            $table->string('created_at_ip', 45)->nullable();

            // Timestamps (Laravel default: useCurrent + useCurrentOnUpdate)
            $table->timestamps();

            // Soft deletes
            $table->softDeletes();

            // Foreign key constraints
            $table->foreign('user_id')
                  ->references('id')->on('users')
                  ->onDelete('cascade');

            $table->foreign('privilege_id')
                  ->references('id')->on('privileges')
                  ->onDelete('cascade');

            $table->foreign('created_by')
                  ->references('id')->on('users')
                  ->nullOnDelete();

            // Unique: same privilege cannot be assigned twice to same user
            $table->unique(['user_id', 'privilege_id'], 'unique_user_privilege');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('user_privileges');
    }
};
