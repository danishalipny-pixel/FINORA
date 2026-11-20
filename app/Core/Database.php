<?php
/**
 * FINORA - Database Manager (PDO Singleton)
 */

declare(strict_types=1);

namespace App\Core;

use PDO;
use PDOException;
use RuntimeException;

class Database
{
    private static ?PDO $instance = null;

    private function __construct() {}
    private function __clone() {}

    public static function connection(): PDO
    {
        return self::getConnection();
    }

    public static function getConnection(): PDO
    {
        if (self::$instance === null) {
            $config = require dirname(__DIR__, 2) . '/config/database.php';

            $dsn = sprintf(
                'mysql:host=%s;port=%d;dbname=%s;charset=%s',
                $config['host'],
                $config['port'],
                $config['database'],
                $config['charset']
            );

            try {
                self::$instance = new PDO(
                    $dsn,
                    $config['username'],
                    $config['password'],
                    $config['options']
                );
            } catch (PDOException $e) {
                // Log and show friendly error or rethrow depending on debug mode
                $appConfig = require dirname(__DIR__, 2) . '/config/app.php';
                if ($appConfig['debug']) {
                    throw new RuntimeException("Database Connection Failed: " . $e->getMessage(), (int)$e->getCode(), $e);
                } else {
                    error_log("Database Connection Error: " . $e->getMessage());
                    http_response_code(500);
                    include dirname(__DIR__, 2) . '/resources/views/errors/500.php';
                    exit;
                }
            }
        }

        return self::$instance;
    }

    public static function beginTransaction(): bool
    {
        return self::getConnection()->beginTransaction();
    }

    public static function commit(): bool
    {
        return self::getConnection()->commit();
    }

    public static function rollBack(): bool
    {
        if (self::getConnection()->inTransaction()) {
            return self::getConnection()->rollBack();
        }
        return false;
    }
}
