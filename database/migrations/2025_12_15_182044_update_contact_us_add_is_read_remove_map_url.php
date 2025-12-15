<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up()
{
    Schema::table('contact_us', function (Blueprint $table) {
        $table->dropColumn('map_url');
        $table->boolean('is_read')->default(0)->after('message');
    });
}

public function down()
{
    Schema::table('contact_us', function (Blueprint $table) {
        $table->string('map_url')->nullable();
        $table->dropColumn('is_read');
    });
}

};
