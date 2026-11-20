-- ==============================================================================
-- FINORA - Personal Finance Intelligence
-- Production Database Schema
-- MySQL 8.0+ / MariaDB 10.4+
-- ==============================================================================

SET FOREIGN_KEY_CHECKS = 0;

DROP TABLE IF EXISTS `password_resets`;
DROP TABLE IF EXISTS `notifications`;
DROP TABLE IF EXISTS `savings_contributions`;
DROP TABLE IF EXISTS `savings_goals`;
DROP TABLE IF EXISTS `budgets`;
DROP TABLE IF EXISTS `transactions`;
DROP TABLE IF EXISTS `categories`;
DROP TABLE IF EXISTS `accounts`;
DROP TABLE IF EXISTS `user_settings`;
DROP TABLE IF EXISTS `users`;

SET FOREIGN_KEY_CHECKS = 1;

-- ------------------------------------------------------------------------------
-- Table: users
-- ------------------------------------------------------------------------------
CREATE TABLE `users` (
    `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `name` VARCHAR(100) NOT NULL,
    `email` VARCHAR(191) NOT NULL UNIQUE,
    `password` VARCHAR(255) NOT NULL,
    `avatar` VARCHAR(255) NULL DEFAULT NULL,
    `role` ENUM('user', 'admin') NOT NULL DEFAULT 'user',
    `status` ENUM('active', 'inactive', 'suspended') NOT NULL DEFAULT 'active',
    `login_attempts` INT NOT NULL DEFAULT 0,
    `lockout_until` DATETIME NULL DEFAULT NULL,
    `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ------------------------------------------------------------------------------
-- Table: user_settings
-- ------------------------------------------------------------------------------
CREATE TABLE `user_settings` (
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

-- ------------------------------------------------------------------------------
-- Table: accounts
-- ------------------------------------------------------------------------------
CREATE TABLE `accounts` (
    `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `user_id` INT UNSIGNED NOT NULL,
    `name` VARCHAR(100) NOT NULL,
    `type` ENUM('cash', 'bank', 'savings', 'other') NOT NULL DEFAULT 'bank',
    `account_number` VARCHAR(50) NULL DEFAULT NULL,
    `opening_balance` DECIMAL(15,2) NOT NULL DEFAULT 0.00,
    `color` VARCHAR(20) NOT NULL DEFAULT '#10B981',
    `is_archived` TINYINT(1) NOT NULL DEFAULT 0,
    `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    CONSTRAINT `fk_accounts_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
    INDEX `idx_accounts_user_archived` (`user_id`, `is_archived`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ------------------------------------------------------------------------------
-- Table: categories
-- ------------------------------------------------------------------------------
CREATE TABLE `categories` (
    `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `user_id` INT UNSIGNED NULL DEFAULT NULL,
    `name` VARCHAR(100) NOT NULL,
    `type` ENUM('income', 'expense') NOT NULL,
    `icon` VARCHAR(50) NOT NULL DEFAULT 'bi-tag',
    `color` VARCHAR(20) NOT NULL DEFAULT '#3B82F6',
    `is_default` TINYINT(1) NOT NULL DEFAULT 0,
    `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    CONSTRAINT `fk_categories_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
    INDEX `idx_categories_user_type` (`user_id`, `type`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ------------------------------------------------------------------------------
-- Table: transactions
-- ------------------------------------------------------------------------------
CREATE TABLE `transactions` (
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

-- ------------------------------------------------------------------------------
-- Table: budgets
-- ------------------------------------------------------------------------------
CREATE TABLE `budgets` (
    `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `user_id` INT UNSIGNED NOT NULL,
    `category_id` INT UNSIGNED NOT NULL,
    `amount` DECIMAL(15,2) NOT NULL,
    `month` TINYINT UNSIGNED NOT NULL,
    `year` SMALLINT UNSIGNED NOT NULL,
    `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    CONSTRAINT `fk_budgets_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
    CONSTRAINT `fk_budgets_category` FOREIGN KEY (`category_id`) REFERENCES `categories` (`id`) ON DELETE CASCADE,
    UNIQUE KEY `uk_user_category_month_year` (`user_id`, `category_id`, `month`, `year`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ------------------------------------------------------------------------------
-- Table: savings_goals
-- ------------------------------------------------------------------------------
CREATE TABLE `savings_goals` (
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

-- ------------------------------------------------------------------------------
-- Table: savings_contributions
-- ------------------------------------------------------------------------------
CREATE TABLE `savings_contributions` (
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

-- ------------------------------------------------------------------------------
-- Table: notifications
-- ------------------------------------------------------------------------------
CREATE TABLE `notifications` (
    `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `user_id` INT UNSIGNED NOT NULL,
    `type` VARCHAR(50) NOT NULL,
    `title` VARCHAR(150) NOT NULL,
    `message` TEXT NOT NULL,
    `link` VARCHAR(255) NULL DEFAULT NULL,
    `is_read` TINYINT(1) NOT NULL DEFAULT 0,
    `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT `fk_notifications_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
    INDEX `idx_notifications_user_read` (`user_id`, `is_read`),
    INDEX `idx_notifications_user_created` (`user_id`, `created_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ------------------------------------------------------------------------------
-- Table: password_resets
-- ------------------------------------------------------------------------------
CREATE TABLE `password_resets` (
    `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `email` VARCHAR(191) NOT NULL,
    `token` VARCHAR(100) NOT NULL,
    `expires_at` DATETIME NOT NULL,
    `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    INDEX `idx_password_resets_email` (`email`),
    INDEX `idx_password_resets_token` (`token`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ------------------------------------------------------------------------------
-- Default Categories (System Level: user_id IS NULL)
-- ------------------------------------------------------------------------------
INSERT INTO `categories` (`user_id`, `name`, `type`, `icon`, `color`, `is_default`) VALUES
-- Income Categories
(NULL, 'Salary & Wages', 'income', 'bi-cash-stack', '#10B981', 1),
(NULL, 'Freelance & Consulting', 'income', 'bi-laptop', '#3B82F6', 1),
(NULL, 'Business & Investments', 'income', 'bi-graph-up-arrow', '#6366F1', 1),
(NULL, 'Rental Income', 'income', 'bi-house-door', '#8B5CF6', 1),
(NULL, 'Gifts & Grants', 'income', 'bi-gift', '#EC4899', 1),
(NULL, 'Other Income', 'income', 'bi-wallet2', '#64748B', 1),

-- Expense Categories
(NULL, 'Housing & Rent', 'expense', 'bi-house', '#EF4444', 1),
(NULL, 'Groceries & Food', 'expense', 'bi-cart', '#F59E0B', 1),
(NULL, 'Utilities & Bills', 'expense', 'bi-lightning-charge', '#F97316', 1),
(NULL, 'Transportation & Fuel', 'expense', 'bi-fuel-pump', '#06B6D4', 1),
(NULL, 'Healthcare & Medical', 'expense', 'bi-heart-pulse', '#E11D48', 1),
(NULL, 'Education & Books', 'expense', 'bi-book', '#8B5CF6', 1),
(NULL, 'Dining & Entertainment', 'expense', 'bi-cup-straw', '#EC4899', 1),
(NULL, 'Shopping & Personal', 'expense', 'bi-bag', '#14B8A6', 1),
(NULL, 'Family & Children', 'expense', 'bi-people', '#0EA5E9', 1),
(NULL, 'Zakat & Charity', 'expense', 'bi-suit-heart', '#10B981', 1),
(NULL, 'Insurance & Taxes', 'expense', 'bi-shield-check', '#475569', 1),
(NULL, 'Other Expenses', 'expense', 'bi-three-dots', '#64748B', 1);
