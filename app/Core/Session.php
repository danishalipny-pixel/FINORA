<?php
/**
 * FINORA - Secure Session & Flash Manager
 */

declare(strict_types=1);

namespace App\Core;

class Session
{
    private static bool $started = false;

    public static function start(): void
    {
        if (self::$started || session_status() === PHP_SESSION_ACTIVE) {
            self::$started = true;
            return;
        }

        $appConfig = require dirname(__DIR__, 2) . '/config/app.php';
        $sessionCfg = $appConfig['session'];

        $isHttps = (
            (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ||
            (isset($_SERVER['SERVER_PORT']) && $_SERVER['SERVER_PORT'] == 443) ||
            (!empty($_SERVER['HTTP_X_FORWARDED_PROTO']) && $_SERVER['HTTP_X_FORWARDED_PROTO'] === 'https')
        );

        if (!headers_sent()) {
            session_set_cookie_params([
                'lifetime' => $sessionCfg['lifetime'] ?? 7200,
                'path' => '/',
                'domain' => '',
                'secure' => $sessionCfg['cookie_secure'] && $isHttps,
                'httponly' => true,
                'samesite' => $sessionCfg['cookie_samesite'] ?? 'Lax',
            ]);

            session_name($sessionCfg['name'] ?? 'FINORA_SESSION');
        }

        if (session_status() !== PHP_SESSION_ACTIVE) {
            @session_start();
        }

        self::$started = true;

        // Session Expiration / Inactivity check
        $now = time();
        if (isset($_SESSION['_last_activity']) && ($now - $_SESSION['_last_activity'] > ($sessionCfg['lifetime'] ?? 7200))) {
            self::destroy();
            session_start();
            self::setFlash('warning', 'Your session expired due to inactivity. Please log in again.');
        }
        $_SESSION['_last_activity'] = $now;

        // Rotate flash data
        if (isset($_SESSION['_flash_next'])) {
            $_SESSION['_flash_current'] = $_SESSION['_flash_next'];
            unset($_SESSION['_flash_next']);
        } else {
            $_SESSION['_flash_current'] = [];
        }
    }

    public static function set(string $key, mixed $value): void
    {
        self::start();
        $_SESSION[$key] = $value;
    }

    public static function get(string $key, mixed $default = null): mixed
    {
        self::start();
        return $_SESSION[$key] ?? $default;
    }

    public static function has(string $key): bool
    {
        self::start();
        return isset($_SESSION[$key]);
    }

    public static function remove(string $key): void
    {
        self::start();
        unset($_SESSION[$key]);
    }

    public static function destroy(): void
    {
        if (session_status() === PHP_SESSION_ACTIVE) {
            $_SESSION = [];
            if (ini_get("session.use_cookies")) {
                $params = session_get_cookie_params();
                setcookie(
                    session_name(),
                    '',
                    time() - 42000,
                    $params["path"],
                    $params["domain"],
                    $params["secure"],
                    $params["httponly"]
                );
            }
            session_destroy();
            self::$started = false;
        }
    }

    public static function regenerateId(bool $deleteOld = true): void
    {
        self::start();
        if (session_status() === PHP_SESSION_ACTIVE) {
            @session_regenerate_id($deleteOld);
        }
    }

    public static function setFlash(string $key, mixed $value): void
    {
        self::start();
        $_SESSION['_flash_next'][$key] = $value;
    }

    public static function getFlash(string $key, mixed $default = null): mixed
    {
        self::start();
        return $_SESSION['_flash_current'][$key] ?? $default;
    }

    public static function hasFlash(string $key): bool
    {
        self::start();
        return isset($_SESSION['_flash_current'][$key]);
    }

    public static function setOldInput(array $data): void
    {
        // Exclude passwords
        unset($data['password'], $data['password_confirmation'], $data['current_password'], $data['new_password']);
        self::set('_old_input', $data);
    }

    public static function getOldInput(?string $key = null, mixed $default = ''): mixed
    {
        $old = self::get('_old_input', []);
        if ($key === null) {
            return $old;
        }
        return $old[$key] ?? $default;
    }

    public static function clearOldInput(): void
    {
        self::remove('_old_input');
    }

    public static function setErrors(array $errors): void
    {
        self::set('_validation_errors', $errors);
    }

    public static function getErrors(): array
    {
        $errors = self::get('_validation_errors', []);
        self::remove('_validation_errors');
        return $errors;
    }

    public static function getError(string $field): ?string
    {
        $errors = self::get('_validation_errors', []);
        return $errors[$field] ?? null;
    }
}
