<?php
/**
 * FINORA - Security Helper
 *
 * Provides sanitization, XSS mitigation, secure token generation, and input cleansing.
 *
 * @package Finora\Helpers
 * @author Danish Ali <https://github.com/danishalipny-pixel>
 */

declare(strict_types=1);

namespace App\Helpers;

class SecurityHelper
{
    /**
     * Escape HTML output against XSS attacks.
     */
    public static function escape(mixed $value): string
    {
        if ($value === null) {
            return '';
        }
        return htmlspecialchars((string)$value, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
    }

    /**
     * Strip unsafe tags and trim whitespace.
     */
    public static function cleanString(mixed $value): string
    {
        if (!is_string($value)) {
            return '';
        }
        return trim(strip_tags($value));
    }

    /**
     * Sanitize and validate email address.
     */
    public static function sanitizeEmail(string $email): string
    {
        $filtered = filter_var(trim($email), FILTER_SANITIZE_EMAIL);
        return is_string($filtered) ? strtolower($filtered) : '';
    }

    /**
     * Parse and sanitize monetary numeric input.
     */
    public static function sanitizeAmount(mixed $amount): float
    {
        if (is_numeric($amount)) {
            return round((float)$amount, 2);
        }
        if (!is_string($amount)) {
            return 0.0;
        }

        // Remove commas and currency prefixes
        $clean = preg_replace('/[^0-9.-]/', '', $amount);
        return round((float)$clean, 2);
    }

    /**
     * Generate cryptographically secure random token in hexadecimal.
     */
    public static function randomToken(int $bytes = 32): string
    {
        return bin2hex(random_bytes(max(16, $bytes)));
    }
}
