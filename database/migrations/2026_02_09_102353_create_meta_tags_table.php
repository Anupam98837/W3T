<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('meta_tags', function (Blueprint $table) {
            // id: BIGINT UNSIGNED, PK, AUTO_INCREMENT, NOT NULL
            $table->bigIncrements('id');

            // uuid: CHAR(36), UNIQUE, NOT NULL
            $table->uuid('uuid')->unique();

            // tag_type: VARCHAR(255), NOT NULL
            $table->string('tag_type', 255);

            // tag_attribute: VARCHAR(255), NULL
            $table->string('tag_attribute', 255)->nullable();

            // tag_attribute_value: VARCHAR(255), NOT NULL
            $table->string('tag_attribute_value', 255);

            // page_link: VARCHAR(255), NOT NULL
            $table->string('page_link', 255);

            // created_by: BIGINT UNSIGNED, FK -> users.id, NULL
            $table->foreignId('created_by')
                ->nullable()
                ->constrained('users')
                ->nullOnDelete();

            // created_at_ip: VARCHAR(45), NULL
            $table->string('created_at_ip', 45)->nullable();

            // updated_at_ip: VARCHAR(45), NULL
            $table->string('updated_at_ip', 45)->nullable();

            // created_at / updated_at: TIMESTAMP, NULL (Laravel timestamp)
            $table->timestamp('created_at')->nullable();
            $table->timestamp('updated_at')->nullable();

            // deleted_at: TIMESTAMP, NULL, INDEX (soft deletes)
            $table->timestamp('deleted_at')->nullable()->index();

            // metadata: JSON, NULL
            $table->json('metadata')->nullable();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('meta_tags');
    }
};
