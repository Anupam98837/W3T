// database/migrations/xxxx_create_password_reset_token_table.php

<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('password_reset_token', function (Blueprint $table) {
    $table->id();
    $table->string('email')->index();
    $table->string('token');                    // hashed — raw only in URL
    $table->tinyInteger('is_valid')->default(1);
    $table->timestamp('expires_at');            // created_at + 10 minutes
    $table->timestamp('used_at')->nullable();   // set on successful reset
    $table->timestamp('created_at');
});
    }

    public function down(): void
    {
        Schema::dropIfExists('password_reset_token');
    }
};