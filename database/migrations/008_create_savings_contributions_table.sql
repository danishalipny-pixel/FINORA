-- Migration: 008_create_savings_contributions_table
-- Author: Danish Ali <https://github.com/danishalipny-pixel>

CREATE TABLE IF NOT EXISTS `savings_contributions` (
    `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `goal_id` INT UNSIGNED NOT NULL,
    `user_id` INT UNSIGNED NOT NULL,
    `account_id` INT UNSIGNED NULL DEFAULT NULL,
    `amount` DECIMAL(15,2) NOT NULL,
    `contribution_date` DATE NOT NULL,
    `notes` VARCHAR(255) NULL DEFAULT NULL,
    `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT `fk_savings_contributions_goal` FOREIGN KEY (`goal_id`) REFERENCES `savings_goals` (`id`) ON DELETE CASCADE,
    CONSTRAINT `fk_savings_contributions_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
    CONSTRAINT `fk_savings_contributions_account` FOREIGN KEY (`account_id`) REFERENCES `accounts` (`id`) ON DELETE SET NULL,
    INDEX `idx_savings_contrib_goal_date` (`goal_id`, `contribution_date`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
