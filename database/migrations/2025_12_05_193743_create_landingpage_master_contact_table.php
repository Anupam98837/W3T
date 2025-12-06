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
        Schema::create('landingpage_master_contact', function (Blueprint $table) {
            $table->id();

            // UUID, unique, auto-generated (MySQL)
            $table->uuid('uuid')
                  ->unique()
                  ->default(DB::raw('(UUID())'));

            // Contact fields
            $table->string('contact_key');              // e.g. 'phone', 'whatsapp', 'email'
            $table->string('value');                    // e.g. '+91-98765-43210'
            $table->string('icon')->nullable();         // e.g. 'fa-solid fa-phone'
            $table->string('image_path')->nullable();   // e.g. '/assets/icons/whatsapp.svg'

            // Display order for sorting
            $table->unsignedInteger('display_order')->default(0);

            // created_by (who added the record)
            $table->unsignedBigInteger('created_by')->nullable();

            // Soft delete
            $table->softDeletes();  // deleted_at

            // Standard timestamps
            $table->timestamps();

            // Foreign key: when user deleted -> set created_by NULL
            $table->foreign('created_by')
                  ->references('id')
                  ->on('users')
                  ->nullOnDelete();  // or ->onDelete('set null')
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('landingpage_master_contact');
    }
};
