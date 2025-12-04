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
        Schema::create('coding_modules', function (Blueprint $table) {
            $table->id();
            
            $table->char('uuid', 36)->unique();

            // Relationship to topic
            $table->foreignId('topic_id')
                ->constrained('topics')
                ->cascadeOnDelete();

            $table->string('title', 200);

            // indexed for speed + unique per topic
            $table->string('slug', 200);

            $table->text('description')->nullable();

            $table->string('status', 20)->default('active')->index(); // active|inactive|archived

            $table->unsignedInteger('sort_order')->default(0)->index();

            $table->json('extras')->nullable(); // optional metadata (difficulty, tags, etc.)

            $table->timestamps();
            $table->softDeletes();

            // Ensure slug unique per topic (Python can have "loops", Algo can also have "loops")
            $table->unique(['topic_id', 'slug']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('coding_modules');
    }
};
