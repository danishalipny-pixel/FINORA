-- Migration: 007_create_savings_goals_table
-- Author: Danish Ali <https://github.com/danishalipny-pixel>

CREATE TABLE IF NOT EXISTS `savings_goals` (
    `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `user_id` INT UNSIGNED NOT NULL,
    `account_id` INT UNSIGNED NULL DEFAULT NULL,
    `name` VARCHAR(150) NOT NULL,
    `target_amount` DECIMAL(15,2) NOT NULL,
    `current_amount` DECIMAL(15,2) NOT NULL DEFAULT 0.00,
    `target_date` DATE NOT NULL,
    `color` VARCHAR(20) NOT NULL DEFAULT '#10B981',
    `status` ENUM('in_progress', 'completed', 'paused') NOT NULL DEFAULT 'in_progress',
    `notes` TEXT NULL DEFAULT NULL,
    `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    CONSTRAINT `fk_savings_goals_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
    CONSTRAINT `fk_savings_goals_account` FOREIGN KEY (`account_id`) REFERENCES `accounts` (`id`) ON DELETE SET NULL,
    INDEX `idx_savings_goals_user_status` (`user_id`, `status`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
