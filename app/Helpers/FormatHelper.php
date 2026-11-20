<?php
/**
 * FINORA - Format Helper
 *
 * Provides financial and numeric formatting utilities.
 *
 * @package Finora\Helpers
 * @author Danish Ali <https://github.com/danishalipny-pixel>
 */

declare(strict_types=1);

namespace App\Helpers;

class FormatHelper
{
    /**
     * Format a numeric amount with currency symbol.
     */
    public static function currency(mixed $amount, string $symbol = 'Rs.', int $decimals = 2): string
    {
        $num = (float)($amount ?? 0);
        return $symbol . ' ' . number_format($num, $decimals, '.', ',');
    }

    /**
     * Format a standard decimal representation.
     */
    public static function decimal(mixed $amount, int $decimals = 2): string
    {
        $num = (float)($amount ?? 0);
        return number_format($num, $decimals, '.', '');
    }

    /**
     * Format a percentage value.
     */
    public static function percentage(mixed $value, int $decimals = 1): string
    {
        $num = (float)($value ?? 0);
        return number_format($num, $decimals, '.', '') . '%';
    }

    /**
     * Format raw byte size into human readable string.
     */
    public static function fileSize(int $bytes): string
    {
        $units = ['B', 'KB', 'MB', 'GB', 'TB'];
        $power = $bytes > 0 ? floor(log($bytes, 1024)) : 0;
        $power = min((int)$power, count($units) - 1);
        $size = $bytes / (1024 ** $power);
        return round($size, 2) . ' ' . $units[$power];
    }

    /**
     * Mask sensitive bank account or card numbers.
     */
    public static function maskAccountNumber(string $accountNumber): string
    {
        $clean = preg_replace('/\s+/', '', $accountNumber);
        $len = strlen($clean);
        if ($len <= 4) {
            return $clean;
        }
        return str_repeat('•', max(0, $len - 4)) . ' ' . substr($clean, -4);
    }
}
