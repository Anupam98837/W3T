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
        Schema::create('landingpage_hero_image', function (Blueprint $table) {
            $table->id();

            // UUID, unique, auto-generated (MySQL)
            $table->uuid('uuid')
                ->unique()
                ->default(DB::raw('(UUID())'));

            // Image title or label
            $table->string('img_title')->nullable();

            // URL or local path to image
            $table->string('image_url'); // non-nullable (you can change to nullable)

            // Display order for slider sorting
            $table->unsignedInteger('display_order')->default(0);

            // created_by (FK to users table)
            $table->unsignedBigInteger('created_by')->nullable();

            // Soft delete (deleted_at)
            $table->softDeletes();

            // created_at + updated_at
            $table->timestamps();

            // Foreign key: when user deleted -> set null
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
        Schema::dropIfExists('landingpage_hero_image');
    }
};
