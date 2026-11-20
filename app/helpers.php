<?php
/**
 * FINORA - Global Template & Utility Helpers
 *
 * Backwards-compatible procedural functions delegating to App\Helpers and Core classes.
 *
 * @package Finora
 * @author Danish Ali <https://github.com/danishalipny-pixel>
 */

declare(strict_types=1);

use App\Core\Auth;
use App\Core\Csrf;
use App\Core\Request;
use App\Core\Session;
use App\Helpers\DateHelper;
use App\Helpers\FormatHelper;
use App\Helpers\SecurityHelper;

if (!function_exists('e')) {
    function e(mixed $value): string
    {
        return SecurityHelper::escape($value);
    }
}

if (!function_exists('currency')) {
    function currency(mixed $amount, ?string $symbol = null): string
    {
        $settings = Auth::settings();
        $sym = $symbol ?? $settings['currency_symbol'] ?? 'Rs.';
        return FormatHelper::currency($amount, $sym);
    }
}

if (!function_exists('date_fmt')) {
    function date_fmt(mixed $date, ?string $format = null): string
    {
        $settings = Auth::settings();
        $fmt = $format ?? $settings['date_format'] ?? 'd M Y';
        $timezone = $settings['timezone'] ?? 'Asia/Karachi';
        return DateHelper::format($date, $fmt, $timezone);
    }
}

if (!function_exists('csrf_field')) {
    function csrf_field(): string
    {
        return Csrf::field();
    }
}

if (!function_exists('csrf_token')) {
    function csrf_token(): string
    {
        return Csrf::getToken();
    }
}

if (!function_exists('old')) {
    function old(string $key, mixed $default = ''): mixed
    {
        return Session::getOldInput($key, $default);
    }
}

if (!function_exists('has_error')) {
    function has_error(string $field): bool
    {
        return Session::getError($field) !== null;
    }
}

if (!function_exists('error')) {
    function error(string $field): ?string
    {
        return Session::getError($field);
    }
}

if (!function_exists('asset')) {
    function asset(string $path): string
    {
        $clean = ltrim($path, '/');
        return '/' . $clean;
    }
}

if (!function_exists('url')) {
    function url(string $path): string
    {
        $clean = ltrim($path, '/');
        return '/' . $clean;
    }
}

if (!function_exists('is_active')) {
    function is_active(string $path, string $activeClass = 'active'): string
    {
        $currentUri = (new Request())->uri();
        if ($path === '/' && $currentUri === '/') {
            return $activeClass;
        }
        if ($path !== '/' && str_starts_with($currentUri, $path)) {
            return $activeClass;
        }
        return '';
    }
}
