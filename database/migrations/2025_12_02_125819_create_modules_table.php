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
        Schema::create('modules', function (Blueprint $table) {
            // Primary Key
            $table->bigIncrements('id');

            // UUID (UNIQUE)
            $table->char('uuid', 36)->unique();

            // Name (UNIQUE)
            $table->string('name', 150)->unique();

            // Href (required, no default)
            $table->string('href', 255);

            // Optional description
            $table->text('description')->nullable();

            // Status with default 'Active'
            $table->string('status', 20)->default('Active');

            // Timestamps with proper defaults
            $table->timestamp('created_at')->useCurrent();
            $table->timestamp('updated_at')
                  ->useCurrent()
                  ->useCurrentOnUpdate();

            // Audit fields
            $table->unsignedBigInteger('created_by')->nullable();
            $table->string('created_at_ip', 45)->nullable();

            // Soft delete timestamp
            $table->timestamp('deleted_at')->nullable();

            // Index only where needed
            $table->index('created_by');

            // Foreign key: created_by -> users.id
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
        Schema::dropIfExists('modules');
    }
};
