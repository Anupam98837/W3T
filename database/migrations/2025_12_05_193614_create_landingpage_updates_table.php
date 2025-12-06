<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('landingpage_updates', function (Blueprint $table) {
            $table->id();

            // UUID, unique, auto-generated
            $table->uuid('uuid')->unique()->default(DB::raw('(UUID())'));

            // Fields
            $table->string('title');
            $table->text('description')->nullable();

            // Display order for sorting
            $table->unsignedInteger('display_order')->default(0);

            // created_by (who added the record)
            $table->unsignedBigInteger('created_by')->nullable();

            // Soft delete timestamp
            $table->softDeletes(); // deleted_at

            // Standard timestamps
            $table->timestamps();

            // Foreign key (after created_by column)
            $table->foreign('created_by')
                ->references('id')
                ->on('users')
                ->nullOnDelete(); // or ->onDelete('set null')
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('landingpage_updates');
    }
};
