<?php
/**
 * FINORA - User Model
 */

declare(strict_types=1);

namespace App\Models;

use App\Core\Database;
use PDO;

class User
{
    public static function create(array $data): int
    {
        $pdo = Database::getConnection();
        $pdo->beginTransaction();

        try {
            $stmt = $pdo->prepare("
                INSERT INTO users (name, email, password, role, status)
                VALUES (?, ?, ?, 'user', 'active')
            ");
            $hashedPassword = password_hash($data['password'], PASSWORD_BCRYPT, ['cost' => 12]);
            $stmt->execute([$data['name'], $data['email'], $hashedPassword]);
            $userId = (int)$pdo->lastInsertId();

            // Initialize default user settings
            $stmtSettings = $pdo->prepare("
                INSERT INTO user_settings (user_id, currency, currency_symbol, date_format, theme, notify_budget_warning, notify_goal_reached, notify_monthly_summary)
                VALUES (?, 'PKR', 'Rs.', 'd M Y', 'light', 1, 1, 1)
            ");
            $stmtSettings->execute([$userId]);

            // Create default starter accounts
            $stmtAccount = $pdo->prepare("
                INSERT INTO accounts (user_id, name, type, opening_balance, color)
                VALUES (?, ?, ?, ?, ?)
            ");
            $stmtAccount->execute([$userId, 'Cash Wallet', 'cash', 0.00, '#F59E0B']);
            $stmtAccount->execute([$userId, 'Main Bank Account', 'bank', 0.00, '#10B981']);

            // Create welcome notification
            $stmtNotif = $pdo->prepare("
                INSERT INTO notifications (user_id, type, title, message, link)
                VALUES (?, 'security', 'Welcome to FINORA!', 'Your personal finance account has been successfully set up. Start by recording your income, expenses, and savings goals.', '/dashboard')
            ");
            $stmtNotif->execute([$userId]);

            $pdo->commit();
            return $userId;
        } catch (\Throwable $e) {
            $pdo->rollBack();
            throw $e;
        }
    }

    public static function findById(int $id): ?array
    {
        $pdo = Database::getConnection();
        $stmt = $pdo->prepare("SELECT id, name, email, avatar, role, status, created_at FROM users WHERE id = ? LIMIT 1");
        $stmt->execute([$id]);
        $user = $stmt->fetch();
        return $user ?: null;
    }

    public static function findByEmail(string $email): ?array
    {
        $pdo = Database::getConnection();
        $stmt = $pdo->prepare("SELECT * FROM users WHERE email = ? LIMIT 1");
        $stmt->execute([$email]);
        $user = $stmt->fetch();
        return $user ?: null;
    }

    public static function updateProfile(int $id, string $name, string $email, ?string $avatar = null): bool
    {
        $pdo = Database::getConnection();
        if ($avatar !== null) {
            $stmt = $pdo->prepare("UPDATE users SET name = ?, email = ?, avatar = ? WHERE id = ?");
            return $stmt->execute([$name, $email, $avatar, $id]);
        }
        $stmt = $pdo->prepare("UPDATE users SET name = ?, email = ? WHERE id = ?");
        return $stmt->execute([$name, $email, $id]);
    }

    public static function updatePassword(int $id, string $newPassword): bool
    {
        $pdo = Database::getConnection();
        $hash = password_hash($newPassword, PASSWORD_BCRYPT, ['cost' => 12]);
        $stmt = $pdo->prepare("UPDATE users SET password = ? WHERE id = ?");
        return $stmt->execute([$hash, $id]);
    }

    public static function createPasswordReset(string $email, string $token, string $expiresAt): bool
    {
        $pdo = Database::getConnection();
        // Remove existing tokens
        $pdo->prepare("DELETE FROM password_resets WHERE email = ?")->execute([$email]);
        $stmt = $pdo->prepare("INSERT INTO password_resets (email, token, expires_at) VALUES (?, ?, ?)");
        return $stmt->execute([$email, $token, $expiresAt]);
    }

    public static function findPasswordReset(string $email, string $token): ?array
    {
        $pdo = Database::getConnection();
        $stmt = $pdo->prepare("SELECT * FROM password_resets WHERE email = ? AND token = ? AND expires_at > NOW() LIMIT 1");
        $stmt->execute([$email, $token]);
        $row = $stmt->fetch();
        return $row ?: null;
    }

    public static function deletePasswordReset(string $email): bool
    {
        $pdo = Database::getConnection();
        $stmt = $pdo->prepare("DELETE FROM password_resets WHERE email = ?");
        return $stmt->execute([$email]);
    }
}
