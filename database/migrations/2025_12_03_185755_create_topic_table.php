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
        Schema::create('topics', function (Blueprint $table) {
            $table->id();
             $table->uuid('uuid')->unique();
            $table->string('title', 200);
            $table->string('slug', 200)->unique();
            $table->text('description')->nullable();
            $table->string('image_path', 255)->nullable(); // e.g., public/oj/topics/xyz.jpg
            $table->string('status', 20)->default('active')->index(); // active|inactive|archived
            $table->unsignedInteger('sort_order')->default(0);
            $table->json('extras')->nullable(); // optional metadata
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('topics');
    }
};
