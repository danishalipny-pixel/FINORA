<?php
/**
 * FINORA - Date & Time Helper
 *
 * Provides date manipulation, formatting, and human-readable differences.
 *
 * @package Finora\Helpers
 * @author Danish Ali <https://github.com/danishalipny-pixel>
 */

declare(strict_types=1);

namespace App\Helpers;

use DateTimeImmutable;
use DateTimeZone;

class DateHelper
{
    /**
     * Format a date into custom string representation.
     */
    public static function format(mixed $date, string $format = 'd M Y', string $timezone = 'Asia/Karachi'): string
    {
        if (empty($date)) {
            return '-';
        }

        try {
            if ($date instanceof DateTimeImmutable) {
                return $date->setTimezone(new DateTimeZone($timezone))->format($format);
            }
            if (is_numeric($date)) {
                $dt = (new DateTimeImmutable())->setTimestamp((int)$date);
                return $dt->setTimezone(new DateTimeZone($timezone))->format($format);
            }
            $dt = new DateTimeImmutable((string)$date, new DateTimeZone($timezone));
            return $dt->format($format);
        } catch (\Throwable) {
            return '-';
        }
    }

    /**
     * Return human-friendly time elapsed (e.g. '5 minutes ago', 'yesterday').
     */
    public static function humanDiff(mixed $date): string
    {
        if (empty($date)) {
            return '-';
        }

        try {
            $timestamp = is_numeric($date) ? (int)$date : strtotime((string)$date);
            if (!$timestamp) {
                return '-';
            }

            $diff = time() - $timestamp;

            if ($diff < 0) {
                $absDiff = abs($diff);
                $days = (int)floor($absDiff / 86400);
                return $days === 0 ? 'Today' : "In {$days} days";
            }

            if ($diff < 60) {
                return 'Just now';
            }
            if ($diff < 3600) {
                $mins = (int)floor($diff / 60);
                return $mins === 1 ? '1 minute ago' : "{$mins} minutes ago";
            }
            if ($diff < 86400) {
                $hours = (int)floor($diff / 3600);
                return $hours === 1 ? '1 hour ago' : "{$hours} hours ago";
            }
            if ($diff < 172800) {
                return 'Yesterday';
            }
            if ($diff < 2592000) {
                $days = (int)floor($diff / 86400);
                return "{$days} days ago";
            }
            if ($diff < 31536000) {
                $months = (int)floor($diff / 2592000);
                return $months === 1 ? '1 month ago' : "{$months} months ago";
            }

            $years = (int)floor($diff / 31536000);
            return $years === 1 ? '1 year ago' : "{$years} years ago";
        } catch (\Throwable) {
            return '-';
        }
    }

    /**
     * Compute remaining calendar days between now and target date.
     */
    public static function daysRemaining(mixed $targetDate): int
    {
        if (empty($targetDate)) {
            return 0;
        }

        try {
            $target = new DateTimeImmutable((string)$targetDate);
            $now = new DateTimeImmutable('today');
            $diff = $now->diff($target);
            return $diff->invert ? -$diff->days : (int)$diff->days;
        } catch (\Throwable) {
            return 0;
        }
    }

    /**
     * Start of month in YYYY-MM-DD format.
     */
    public static function startOfMonth(?string $date = null): string
    {
        $dt = $date ? new DateTimeImmutable($date) : new DateTimeImmutable();
        return $dt->modify('first day of this month')->format('Y-m-d');
    }

    /**
     * End of month in YYYY-MM-DD format.
     */
    public static function endOfMonth(?string $date = null): string
    {
        $dt = $date ? new DateTimeImmutable($date) : new DateTimeImmutable();
        return $dt->modify('last day of this month')->format('Y-m-d');
    }
}
