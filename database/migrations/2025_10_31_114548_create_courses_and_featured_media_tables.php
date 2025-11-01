<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration {
    public function up(): void
    {
        $driver = Schema::getConnection()->getDriverName();

        // ================= COURSES =================
        Schema::create('courses', function (Blueprint $table) use ($driver) {
            $table->bigIncrements('id');
            $table->char('uuid', 36)->unique();
            $table->string('title', 255);
            $table->string('slug', 140)->unique();

            // ⬇ changed from text() → longText()
            $table->longText('short_description')->nullable();  // can hold large log-like text
            $table->longText('full_description')->nullable();   // detailed or HTML content

            $table->string('status', 20)->default('draft');
            $table->string('course_type', 16)->default('premium');
            $table->decimal('price_amount', 10, 2)->default(0);
            $table->char('price_currency', 3)->default('INR');
            $table->decimal('discount_amount', 10, 2)->nullable();
            $table->decimal('discount_percent', 5, 2)->nullable();
            $table->timestamp('discount_expires_at')->nullable();
            $table->boolean('is_featured')->default(false);
            $table->integer('featured_rank')->default(0);
            $table->integer('order_no')->default(0);
            $table->string('level', 20)->nullable();
            $table->string('language', 10)->nullable();
            $table->timestamp('publish_at')->nullable();
            $table->timestamp('unpublish_at')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('created_at')->useCurrent();
            $table->string('created_at_ip', 45)->nullable();
            $table->timestamp('updated_at')->useCurrent()->useCurrentOnUpdate();
            $table->timestamp('deleted_at')->nullable()->index();

            if ($driver === 'pgsql') {
                $table->json('metadata')->default(DB::raw("'{}'::json"));
            } else {
                $table->json('metadata')->nullable();
            }
        });

        // generated column logic same
        if ($driver === 'pgsql') {
            DB::statement("
                ALTER TABLE courses
                ADD COLUMN final_price NUMERIC(10,2) GENERATED ALWAYS AS (
                    GREATEST(
                        0,
                        price_amount
                        - COALESCE(discount_amount, 0)
                        - (price_amount * COALESCE(discount_percent, 0) / 100)
                    )
                ) STORED
            ");
        } else {
            DB::statement("
                ALTER TABLE `courses`
                ADD COLUMN `final_price` DECIMAL(10,2)
                AS (
                    GREATEST(
                        0,
                        price_amount
                        - IFNULL(discount_amount, 0)
                        - (price_amount * IFNULL(discount_percent, 0) / 100)
                    )
                ) STORED
            ");
        }

        // ================= COURSE FEATURED MEDIA =================
        Schema::create('course_featured_media', function (Blueprint $table) use ($driver) {
            $table->bigIncrements('id');
            $table->char('uuid', 36)->unique();
            $table->foreignId('course_id')->constrained('courses')->cascadeOnDelete()->index();
            $table->string('featured_type', 20);
            $table->text('featured_url');
            $table->integer('order_no')->default(0);
            $table->string('status', 20)->default('active');
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('created_at')->useCurrent();
            $table->timestamp('updated_at')->useCurrent()->useCurrentOnUpdate();
            $table->timestamp('deleted_at')->nullable()->index();

            if ($driver === 'pgsql') {
                $table->json('metadata')->default(DB::raw("'{}'::json"));
            } else {
                $table->json('metadata')->nullable();
            }
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('course_featured_media');
        Schema::dropIfExists('courses');
    }
};
