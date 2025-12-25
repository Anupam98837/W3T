<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('blogs', function (Blueprint $table) {
            // Primary key
            $table->bigIncrements('id');

            // UUID (used by controller resolveBlog + file naming)
            $table->uuid('uuid')->unique()->index();

            // Slug & shortcode
            $table->string('slug', 200)->unique();
            $table->string('shortcode', 20)->unique()->index();

            // Core blog content
            $table->string('title', 255)->index();

            // ✅ match controller field name
            $table->string('short_description', 500)->nullable();

            // ✅ match controller field name (stores public URL)
            $table->string('featured_image_url', 500)->nullable();

            $table->longText('content_html')->nullable();

            // Blog display date
            $table->date('blog_date')->nullable()->index();

            // Status lifecycle
            $table->enum('status', [
                'draft',
                'pending_approval',
                'approved',
                'active',
                'inactive'
            ])->default('draft')->index();

            // Publish toggle (controller stores int 0/1, so use tinyint)
            $table->tinyInteger('is_published')->unsigned()->default(0)->index();

            // Audit users
            $table->unsignedBigInteger('created_by_user_id')->nullable()->index();
            $table->unsignedBigInteger('updated_by_user_id')->nullable()->index();
            $table->unsignedBigInteger('approved_by_user_id')->nullable()->index();

            // Approval timestamp
            $table->timestamp('approved_at')->nullable()->index();

            // Optional audit IP (controller writes created_at_ip)
            $table->string('created_at_ip', 45)->nullable();

            // Soft delete
            $table->timestamp('deleted_at')->nullable()->index();

            // Timestamps
            $table->timestamp('created_at')->useCurrent();
            $table->timestamp('updated_at')->useCurrent()->useCurrentOnUpdate();

            // Foreign keys
            $table->foreign('created_by_user_id')
                ->references('id')
                ->on('users')
                ->nullOnDelete();

            $table->foreign('updated_by_user_id')
                ->references('id')
                ->on('users')
                ->nullOnDelete();

            $table->foreign('approved_by_user_id')
                ->references('id')
                ->on('users')
                ->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('blogs');
    }
};
