<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('landingpage_categories', function (Blueprint $table) {
            $table->id();

            // Unique UUID
            $table->uuid('uuid')->unique();

            // Created by foreign key (nullable, set null on delete)
            $table->foreignId('created_by')
                ->nullable()
                ->constrained('users')
                ->nullOnDelete();

            // Name/title of category
            $table->string('title')
                ->nullable()
                ->default(null);

            // Icon path / icon class
            $table->string('icon')
                ->nullable()
                ->default(null);

            // Optional description, HTML supported
            $table->text('description')
                ->nullable()
                ->default(null);

            // Soft deletes (for trash)
            $table->softDeletes();

            // Created at, Updated at
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('landingpage_categories');
    }
};
