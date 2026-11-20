-- Migration: 005_create_transactions_table
-- Author: Danish Ali <https://github.com/danishalipny-pixel>

CREATE TABLE IF NOT EXISTS `transactions` (
    `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `user_id` INT UNSIGNED NOT NULL,
    `account_id` INT UNSIGNED NOT NULL,
    `category_id` INT UNSIGNED NULL DEFAULT NULL,
    `type` ENUM('income', 'expense', 'transfer') NOT NULL,
    `amount` DECIMAL(15,2) NOT NULL,
    `transaction_date` DATE NOT NULL,
    `payment_method` VARCHAR(50) NOT NULL DEFAULT 'Cash',
    `description` VARCHAR(255) NOT NULL,
    `notes` TEXT NULL DEFAULT NULL,
    `reference_id` VARCHAR(100) NULL DEFAULT NULL,
    `destination_account_id` INT UNSIGNED NULL DEFAULT NULL,
    `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    CONSTRAINT `fk_transactions_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
    CONSTRAINT `fk_transactions_account` FOREIGN KEY (`account_id`) REFERENCES `accounts` (`id`) ON DELETE RESTRICT,
    CONSTRAINT `fk_transactions_category` FOREIGN KEY (`category_id`) REFERENCES `categories` (`id`) ON DELETE SET NULL,
    CONSTRAINT `fk_transactions_dest_account` FOREIGN KEY (`destination_account_id`) REFERENCES `accounts` (`id`) ON DELETE SET NULL,
    INDEX `idx_transactions_user_date` (`user_id`, `transaction_date`),
    INDEX `idx_transactions_user_type` (`user_id`, `type`),
    INDEX `idx_transactions_user_category` (`user_id`, `category_id`),
    INDEX `idx_transactions_user_account` (`user_id`, `account_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
