<?php
/**
 * FINORA - Authentication & Authorization Manager
 */

declare(strict_types=1);

namespace App\Core;

use PDO;

class Auth
{
    private const SESSION_USER_ID = '_auth_user_id';
    private static ?array $cachedUser = null;
    private static ?array $cachedSettings = null;

    public static function check(): bool
    {
        return Session::has(self::SESSION_USER_ID) && self::user() !== null;
    }

    public static function id(): ?int
    {
        $id = Session::get(self::SESSION_USER_ID);
        return $id !== null ? (int)$id : null;
    }

    public static function user(): ?array
    {
        $id = self::id();
        if (!$id) {
            return null;
        }

        if (self::$cachedUser !== null) {
            return self::$cachedUser;
        }

        $pdo = Database::getConnection();
        $stmt = $pdo->prepare("SELECT id, name, email, avatar, role, status, created_at FROM users WHERE id = ? AND status = 'active' LIMIT 1");
        $stmt->execute([$id]);
        $user = $stmt->fetch();

        if (!$user) {
            self::logout();
            return null;
        }

        // Fetch settings
        $stmtSet = $pdo->prepare("SELECT currency, currency_symbol, date_format, theme, notify_budget_warning, notify_goal_reached, notify_monthly_summary FROM user_settings WHERE user_id = ? LIMIT 1");
        $stmtSet->execute([$id]);
        $settings = $stmtSet->fetch();

        if (!$settings) {
            $settings = [
                'currency' => 'PKR',
                'currency_symbol' => 'Rs.',
                'date_format' => 'd M Y',
                'theme' => 'light',
                'notify_budget_warning' => 1,
                'notify_goal_reached' => 1,
                'notify_monthly_summary' => 1,
            ];
        }

        $user['settings'] = $settings;
        self::$cachedUser = $user;
        self::$cachedSettings = $settings;

        return $user;
    }

    public static function settings(): array
    {
        if (self::$cachedSettings !== null) {
            return self::$cachedSettings;
        }
        $user = self::user();
        return $user['settings'] ?? [
            'currency' => 'PKR',
            'currency_symbol' => 'Rs.',
            'date_format' => 'd M Y',
            'theme' => 'light',
            'notify_budget_warning' => 1,
            'notify_goal_reached' => 1,
            'notify_monthly_summary' => 1,
        ];
    }

    public static function attempt(string $email, string $password): array
    {
        $pdo = Database::getConnection();
        $appConfig = require dirname(__DIR__, 2) . '/config/app.php';
        $maxAttempts = $appConfig['security']['max_login_attempts'] ?? 5;
        $lockoutSeconds = $appConfig['security']['lockout_duration_seconds'] ?? 900;

        $stmt = $pdo->prepare("SELECT id, name, email, password, role, status, login_attempts, lockout_until FROM users WHERE email = ? LIMIT 1");
        $stmt->execute([$email]);
        $user = $stmt->fetch();

        if (!$user) {
            return ['success' => false, 'message' => 'Invalid email or password.'];
        }

        // Check if locked out
        if ($user['lockout_until'] !== null) {
            $lockoutTime = strtotime($user['lockout_until']);
            if ($lockoutTime > time()) {
                $waitMinutes = ceil(($lockoutTime - time()) / 60);
                return [
                    'success' => false,
                    'message' => "Too many failed attempts. Account temporarily locked. Please try again in {$waitMinutes} minutes."
                ];
            } else {
                // Lockout expired, reset attempts
                $pdo->prepare("UPDATE users SET login_attempts = 0, lockout_until = NULL WHERE id = ?")->execute([$user['id']]);
                $user['login_attempts'] = 0;
            }
        }

        if ($user['status'] !== 'active') {
            return ['success' => false, 'message' => 'Your account is currently inactive or suspended. Please contact support.'];
        }

        if (!password_verify($password, $user['password'])) {
            $attempts = (int)$user['login_attempts'] + 1;
            $lockoutUntil = null;
            $msg = 'Invalid email or password.';

            if ($attempts >= $maxAttempts) {
                $lockoutUntil = date('Y-m-d H:i:s', time() + $lockoutSeconds);
                $msg = "Account locked due to {$maxAttempts} failed attempts. Please try again in 15 minutes.";
            } else {
                $remaining = $maxAttempts - $attempts;
                $msg = "Invalid email or password. ({$remaining} attempt" . ($remaining > 1 ? 's' : '') . " remaining)";
            }

            $upd = $pdo->prepare("UPDATE users SET login_attempts = ?, lockout_until = ? WHERE id = ?");
            $upd->execute([$attempts, $lockoutUntil, $user['id']]);

            return ['success' => false, 'message' => $msg];
        }

        // Reset login attempts upon success
        $reset = $pdo->prepare("UPDATE users SET login_attempts = 0, lockout_until = NULL WHERE id = ?");
        $reset->execute([$user['id']]);

        // Regenerate session ID to prevent session fixation attacks
        Session::regenerateId(true);
        Session::set(self::SESSION_USER_ID, (int)$user['id']);
        self::$cachedUser = null;
        self::$cachedSettings = null;

        return ['success' => true, 'user' => $user];
    }

    public static function login(int $userId): void
    {
        Session::regenerateId(true);
        Session::set(self::SESSION_USER_ID, $userId);
        self::$cachedUser = null;
        self::$cachedSettings = null;
    }

    public static function logout(): void
    {
        Session::remove(self::SESSION_USER_ID);
        Session::destroy();
        self::$cachedUser = null;
        self::$cachedSettings = null;
    }

    public static function refresh(): void
    {
        self::$cachedUser = null;
        self::$cachedSettings = null;
    }
}
