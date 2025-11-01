<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            // Identifiers
            if (!Schema::hasColumn('users', 'uuid')) {
                $table->uuid('uuid')->unique()->after('id'); // CHAR(36)
            }
            if (!Schema::hasColumn('users', 'slug')) {
                $table->string('slug', 140)->unique()->after('name'); // human-friendly unique id
            }

            // Contacts
            if (!Schema::hasColumn('users', 'phone_number')) {
                // nullable for safe migration; app will enforce presence on create
                $table->string('phone_number', 32)->nullable()->unique()->after('email');
            }
            if (!Schema::hasColumn('users', 'alternative_email')) {
                $table->string('alternative_email', 255)->nullable()->after('phone_number');
            }
            if (!Schema::hasColumn('users', 'alternative_phone_number')) {
                $table->string('alternative_phone_number', 32)->nullable()->after('alternative_email');
            }
            if (!Schema::hasColumn('users', 'whatsapp_number')) {
                $table->string('whatsapp_number', 32)->nullable()->after('alternative_phone_number');
            }

            // Profile
            if (!Schema::hasColumn('users', 'image')) {
                // store path/URL (app writes to /Public/UserProfileImage/{unique}.{ext})
                $table->string('image', 255)->nullable()->after('password');
            }
            if (!Schema::hasColumn('users', 'address')) {
                $table->text('address')->nullable()->after('image');
            }

            // Roles (w3t roles: super admin, admin, instructor, students, author)
            if (!Schema::hasColumn('users', 'role')) {
                $table->string('role', 50)->default('student')->after('address');
            }
            if (!Schema::hasColumn('users', 'role_short_form')) {
                $table->string('role_short_form', 10)->default('STD')->after('role');
            }

            // Auth / Tracking
            if (!Schema::hasColumn('users', 'status')) {
                $table->string('status', 20)->default('active')->after('role_short_form');
            }
            if (!Schema::hasColumn('users', 'last_login_at')) {
                $table->timestamp('last_login_at')->nullable()->after('status');
            }
            if (!Schema::hasColumn('users', 'last_login_ip')) {
                $table->string('last_login_ip', 45)->nullable()->after('last_login_at');
            }

            // Audit
            if (!Schema::hasColumn('users', 'created_by')) {
                $table->foreignId('created_by')->nullable()->after('remember_token')
                      ->constrained('users')->nullOnDelete();
            }
            if (!Schema::hasColumn('users', 'created_at_ip')) {
                $table->ipAddress('created_at_ip')->nullable()->after('created_at');
            }

            // Soft delete + metadata
            if (!Schema::hasColumn('users', 'deleted_at')) {
                $table->softDeletes();              // adds deleted_at
                $table->index('deleted_at');
            }
            if (!Schema::hasColumn('users', 'metadata')) {
                $table->json('metadata')->nullable()->after('deleted_at'); // optional extras (timezone, device, etc.)
            }
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            // Drop FK first (if exists)
            if (Schema::hasColumn('users', 'created_by')) {
                try { $table->dropForeign(['created_by']); } catch (\Throwable $e) {}
            }

            // Drop columns safely without doctrine/dbal
            foreach ([
                'metadata',
                'deleted_at',
                'created_at_ip',
                'created_by',
                'last_login_ip',
                'last_login_at',
                'status',
                'role_short_form',
                'role',
                'address',
                'image',
                'whatsapp_number',
                'alternative_phone_number',
                'alternative_email',
                'phone_number',
                'slug',
                'uuid',
            ] as $col) {
                if (Schema::hasColumn('users', $col)) {
                    $table->dropColumn($col);
                }
            }
        });
    }
};
