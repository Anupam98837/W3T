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
        Schema::create('mailer_settings', function (Blueprint $table) {
            $table->id();

            /**
             * Polymorphic owner (use FQCN to match the rest of the app, e.g. 'App\\Models\\User')
             * This keeps it compatible with how tokens & roles use FQCNs in CheckRole.
             */
            $table->string('owner_type');              // e.g. App\Models\User
            $table->unsignedBigInteger('owner_id');    // user id (or other owner model id)

            // Optional short label to distinguish multiple configs for the same owner
            $table->string('label', 100)->nullable();

            // Core transport config (kept intentionally aligned with DynamicMail)
            $table->string('mailer', 32)->default('smtp');   // smtp|ses|mailgun|postmark|sendmail|log|array
            $table->string('host', 191)->nullable();
            $table->unsignedInteger('port')->nullable();
            $table->string('username', 191)->nullable();
            $table->text('password')->nullable();            // store encrypted via Crypt::encryptString
            $table->string('encryption', 16)->nullable();    // tls|ssl|null
            $table->unsignedInteger('timeout')->nullable();  // seconds (optional)

            // Sender identity
            $table->string('from_address', 191);
            $table->string('from_name', 191);
            $table->string('reply_to_address', 191)->nullable();
            $table->string('reply_to_name', 191)->nullable();

            // Flags / lifecycle
            $table->boolean('is_default')->default(false);   // app-level guarantee: max one default per owner
            $table->enum('status', ['active', 'inactive'])->default('active');


            // Auditing (keep lightweight; no FK to stay DB-agnostic)
            $table->unsignedBigInteger('created_by')->nullable();
            $table->unsignedBigInteger('updated_by')->nullable();

            $table->timestamps();
            $table->softDeletes();

            // Indexes
            $table->index(['owner_type', 'owner_id']);                 // common lookup
            $table->index(['owner_type', 'owner_id', 'is_default']);   // fast default selection
            $table->index(['status']);                                 // active-only lists
            $table->unique(['owner_type', 'owner_id', 'label']);       // prevent duplicate labels per owner
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('mailer_settings');
    }
};
