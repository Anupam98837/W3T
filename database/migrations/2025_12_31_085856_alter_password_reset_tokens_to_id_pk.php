<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        $driver = Schema::getConnection()->getDriverName();

        // This migration is written for MySQL/MariaDB (XAMPP friendly).
        if (!in_array($driver, ['mysql', 'mariadb'], true)) {
            throw new RuntimeException("This migration supports MySQL/MariaDB only. Current driver: {$driver}");
        }

        // 1) Drop PRIMARY KEY on email (required before making email nullable / adding id PK)
        DB::statement("ALTER TABLE `password_reset_tokens` DROP PRIMARY KEY");

        // 2) Add id as AUTO_INCREMENT PRIMARY KEY (first column)
        DB::statement("
            ALTER TABLE `password_reset_tokens`
            ADD COLUMN `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY FIRST
        ");

        // 3) Make email nullable (still varchar 255)
        DB::statement("
            ALTER TABLE `password_reset_tokens`
            MODIFY `email` VARCHAR(255) NULL
        ");

        // 4) Add OTP + validity columns
        DB::statement("
            ALTER TABLE `password_reset_tokens`
            ADD COLUMN `otp` VARCHAR(255) NULL AFTER `created_at`,
            ADD COLUMN `otp_expires_at` TIMESTAMP NULL AFTER `otp`,
            ADD COLUMN `is_valid` TINYINT NOT NULL DEFAULT 1 COMMENT '1=valid, 0=used/invalid' AFTER `otp_expires_at`
        ");

        // 5) Helpful indexes
        DB::statement("CREATE INDEX `password_reset_tokens_email_index` ON `password_reset_tokens` (`email`)");
        DB::statement("CREATE INDEX `password_reset_tokens_is_valid_created_at_index` ON `password_reset_tokens` (`is_valid`, `created_at`)");

        // 6) Safety: if any existing token rows have emails not present in users table,
        // FK add will fail. Null them out so migration does not break.
        DB::statement("
            UPDATE `password_reset_tokens` prt
            LEFT JOIN `users` u ON prt.email = u.email
            SET prt.email = NULL
            WHERE prt.email IS NOT NULL AND u.email IS NULL
        ");

        // 7) Add FK to users.email (users.email must be UNIQUE/INDEXED)
        DB::statement("
            ALTER TABLE `password_reset_tokens`
            ADD CONSTRAINT `password_reset_tokens_email_foreign`
            FOREIGN KEY (`email`) REFERENCES `users` (`email`)
            ON DELETE SET NULL
            ON UPDATE CASCADE
        ");
    }

    public function down(): void
    {
        $driver = Schema::getConnection()->getDriverName();

        if (!in_array($driver, ['mysql', 'mariadb'], true)) {
            throw new RuntimeException("This migration supports MySQL/MariaDB only. Current driver: {$driver}");
        }

        // Drop FK first
        DB::statement("ALTER TABLE `password_reset_tokens` DROP FOREIGN KEY `password_reset_tokens_email_foreign`");

        // Drop indexes
        DB::statement("DROP INDEX `password_reset_tokens_email_index` ON `password_reset_tokens`");
        DB::statement("DROP INDEX `password_reset_tokens_is_valid_created_at_index` ON `password_reset_tokens`");

        // Drop added columns
        DB::statement("
            ALTER TABLE `password_reset_tokens`
            DROP COLUMN `otp`,
            DROP COLUMN `otp_expires_at`,
            DROP COLUMN `is_valid`
        ");

        // Email must be NOT NULL to become PRIMARY KEY again
        DB::statement("DELETE FROM `password_reset_tokens` WHERE `email` IS NULL");

        // Drop PK (id) and remove id column
        DB::statement("ALTER TABLE `password_reset_tokens` DROP PRIMARY KEY");
        DB::statement("ALTER TABLE `password_reset_tokens` DROP COLUMN `id`");

        // Restore old structure: email NOT NULL PRIMARY KEY
        DB::statement("ALTER TABLE `password_reset_tokens` MODIFY `email` VARCHAR(255) NOT NULL");
        DB::statement("ALTER TABLE `password_reset_tokens` ADD PRIMARY KEY (`email`)");
    }
};
