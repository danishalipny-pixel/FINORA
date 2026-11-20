<?php
/**
 * FINORA - Environment Variable Loader
 */

declare(strict_types=1);

namespace App\Core;

class Env
{
    private static array $variables = [];
    private static bool $loaded = false;

    public static function load(string $path): void
    {
        if (self::$loaded || !file_exists($path)) {
            return;
        }

        $lines = file($path, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
        if ($lines === false) {
            return;
        }

        foreach ($lines as $line) {
            $line = trim($line);
            if ($line === '' || str_starts_with($line, '#')) {
                continue;
            }

            if (str_contains($line, '=')) {
                [$name, $value] = explode('=', $line, 2);
                $name = trim($name);
                $value = trim($value);

                // Strip quotes if present
                if (
                    (str_starts_with($value, '"') && str_ends_with($value, '"')) ||
                    (str_starts_with($value, "'") && str_ends_with($value, "'"))
                ) {
                    $value = substr($value, 1, -1);
                }

                // Handle boolean / null strings
                $lower = strtolower($value);
                if ($lower === 'true') {
                    $parsed = true;
                } elseif ($lower === 'false') {
                    $parsed = false;
                } elseif ($lower === 'null') {
                    $parsed = null;
                } else {
                    $parsed = $value;
                }

                self::$variables[$name] = $parsed;
                putenv("{$name}={$value}");
                $_ENV[$name] = $parsed;
                $_SERVER[$name] = $parsed;
            }
        }

        self::$loaded = true;
    }

    public static function get(string $key, mixed $default = null): mixed
    {
        if (array_key_exists($key, self::$variables)) {
            return self::$variables[$key];
        }

        $envVal = getenv($key);
        if ($envVal !== false) {
            return $envVal;
        }

        return $default;
    }
}
