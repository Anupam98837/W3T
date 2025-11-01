<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class SuperAdminSeeder extends Seeder
{
    public function run(): void
    {
        // Use env overrides if you want to customize without touching code
        $email    = env('W3T_SUPERADMIN_EMAIL', 'superadmin@hallienz.com');
        $password = env('W3T_SUPERADMIN_PASSWORD', 'SuperAdmin@123');
        $name     = env('W3T_SUPERADMIN_NAME', 'Super Administrator');

        $now   = now();

        // Ensure unique UUID
        do {
            $uuid = (string) Str::uuid();
        } while (DB::table('users')->where('uuid', $uuid)->exists());

        // Ensure unique slug
        $base = Str::slug($name);
        do {
            $slug = $base . '-' . Str::lower(Str::random(24));
        } while (DB::table('users')->where('slug', $slug)->exists());

        // If user exists (by email), this will update it; otherwise insert.
        DB::table('users')->updateOrInsert(
            ['email' => $email],
            [
                'uuid'            => $uuid,
                'name'            => $name,
                'email'           => $email,
                'phone_number'    => null, // optional
                'password'        => Hash::make($password),
                'image'           => null,
                'address'         => null,

                // w3t roles
                'role'            => 'super_admin',
                'role_short_form' => 'SA',

                'slug'            => $slug,
                'status'          => 'active',
                'last_login_at'   => null,
                'last_login_ip'   => null,
                'remember_token'  => Str::random(60),

                'created_by'      => null,
                'created_at'      => $now,
                'updated_at'      => $now,
                'created_at_ip'   => '127.0.0.1',
                'deleted_at'      => null, // revive if soft-deleted

                'metadata'        => json_encode([
                    'timezone' => 'Asia/Kolkata',
                    'seeded'   => true,
                    'source'   => 'SuperAdminSeeder',
                ], JSON_UNESCAPED_UNICODE),
            ]
        );
    }
}
