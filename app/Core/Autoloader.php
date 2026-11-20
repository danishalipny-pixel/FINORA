<?php
/**
 * FINORA - PSR-4 Class Autoloader
 *
 * Supports App\, Tests\, and Database\ namespaces across both
 * case-insensitive (Windows/macOS) and case-sensitive (Linux) filesystems.
 *
 * @package Finora\Core
 * @author Danish Ali <https://github.com/danishalipny-pixel>
 */

declare(strict_types=1);

namespace App\Core;

class Autoloader
{
    /**
     * Map of namespace prefixes to base directories.
     *
     * @var array<string, string>
     */
    private static array $prefixes = [];

    public static function register(): void
    {
        $root = dirname(__DIR__, 2);

        self::$prefixes = [
            'App\\'      => $root . DIRECTORY_SEPARATOR . 'app' . DIRECTORY_SEPARATOR,
            'Tests\\'    => $root . DIRECTORY_SEPARATOR . 'tests' . DIRECTORY_SEPARATOR,
            'Database\\' => $root . DIRECTORY_SEPARATOR . 'database' . DIRECTORY_SEPARATOR,
        ];

        spl_autoload_register(function (string $class) {
            foreach (self::$prefixes as $prefix => $baseDir) {
                $len = strlen($prefix);
                if (strncmp($prefix, $class, $len) !== 0) {
                    continue;
                }

                $relativeClass = substr($class, $len);
                $file = $baseDir . str_replace('\\', DIRECTORY_SEPARATOR, $relativeClass) . '.php';

                if (file_exists($file)) {
                    require $file;
                    return;
                }

                // Case-sensitivity resolution on Linux: check lowercase subdirectory variants
                $parts = explode('\\', $relativeClass);
                $className = array_pop($parts);
                $lowerPath = array_map('strtolower', $parts);
                $lowerPath[] = $className;
                $fallbackFile = $baseDir . implode(DIRECTORY_SEPARATOR, $lowerPath) . '.php';
                if (file_exists($fallbackFile)) {
                    require $fallbackFile;
                    return;
                }
            }
        });
    }
}
