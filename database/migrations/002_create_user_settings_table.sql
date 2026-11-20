-- Migration: 002_create_user_settings_table
-- Author: Danish Ali <https://github.com/danishalipny-pixel>

CREATE TABLE IF NOT EXISTS `user_settings` (
    `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `user_id` INT UNSIGNED NOT NULL UNIQUE,
    `currency` VARCHAR(10) NOT NULL DEFAULT 'PKR',
    `currency_symbol` VARCHAR(10) NOT NULL DEFAULT 'Rs.',
    `date_format` VARCHAR(20) NOT NULL DEFAULT 'Y-m-d',
    `theme` ENUM('light', 'dark') NOT NULL DEFAULT 'light',
    `notify_budget_warning` TINYINT(1) NOT NULL DEFAULT 1,
    `notify_goal_reached` TINYINT(1) NOT NULL DEFAULT 1,
    `notify_monthly_summary` TINYINT(1) NOT NULL DEFAULT 1,
    `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    CONSTRAINT `fk_user_settings_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
