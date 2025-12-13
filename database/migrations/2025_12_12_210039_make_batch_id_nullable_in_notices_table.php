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
        Schema::table('notices', function (Blueprint $table) {

            // Drop existing foreign key
            $table->dropForeign(['batch_id']);

            // Make column nullable
            $table->foreignId('batch_id')
                  ->nullable()
                  ->change();

            // Re-add foreign key with nullOnDelete()
            $table->foreign('batch_id')
                  ->references('id')
                  ->on('batches')
                  ->nullOnDelete()
                  ->cascadeOnUpdate();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('notices', function (Blueprint $table) {

            // Reverse: drop new FK
            $table->dropForeign(['batch_id']);

            // Make column NOT NULL again
            $table->foreignId('batch_id')
                  ->nullable(false)
                  ->change();

            // Re-add original cascading FK
            $table->foreign('batch_id')
                  ->references('id')
                  ->on('batches')
                  ->cascadeOnDelete()
                  ->cascadeOnUpdate();
        });
    }
};
