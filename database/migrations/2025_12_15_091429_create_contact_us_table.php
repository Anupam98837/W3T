<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateContactUsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('contact_us', function (Blueprint $table) {
            $table->id(); // auto-increment ID

            $table->string('name'); // Sender name
            $table->string('email'); // Sender email
            $table->string('phone', 20)->nullable(); // Phone number
            $table->longText('message'); // User message
            $table->string('map_url')->nullable(); // Google Map embed or URL

            $table->timestamps(); // created_at & updated_at
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('contact_us');
    }
}
