<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('password_reset_tokens', function (Blueprint $table) {

            $table->bigIncrements('id'); // ✅ PK because email is nullable

            // email nullable + FK to users.email
            $table->string('email', 255)->nullable();

            // token required
            $table->string('token', 255);

            // created_at nullable (as per your schema)
            $table->timestamp('created_at')->nullable();

            // OTP support
            $table->string('otp', 255)->nullable();
            $table->timestamp('otp_expires_at')->nullable();

            // is_valid 1/0
            $table->tinyInteger('is_valid')
                  ->default(1)
                  ->comment('1=valid, 0=used/invalid');

            // helpful indexes
            $table->index('email');
            $table->index(['is_valid', 'created_at']);

            // ✅ FK: only applies when email is NOT NULL
            // Requires users.email to be UNIQUE/INDEXED.
            $table->foreign('email')
                  ->references('email')
                  ->on('users')
                  ->nullOnDelete(); // if user deleted -> set email NULL
        });
    }

    public function down(): void
    {
        Schema::table('password_reset_tokens', function (Blueprint $table) {
            $table->dropForeign(['email']);
        });

        Schema::dropIfExists('password_reset_tokens');
    }
};
