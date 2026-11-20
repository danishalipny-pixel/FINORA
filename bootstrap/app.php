<?php
/**
 * FINORA - Framework Bootstrap
 *
 * Initializes autoloader, environment variables, error handling,
 * and sets default timezone and locale.
 *
 * @package Finora
 * @author Danish Ali <https://github.com/danishalipny-pixel>
 */

declare(strict_types=1);

// Define Base Paths
if (!defined('BASE_PATH')) {
    define('BASE_PATH', dirname(__DIR__));
}
if (!defined('APP_PATH')) {
    define('APP_PATH', BASE_PATH . '/app');
}
if (!defined('STORAGE_PATH')) {
    define('STORAGE_PATH', BASE_PATH . '/storage');
}

// 1. Register PSR-4 Autoloader
require_once APP_PATH . '/Core/Autoloader.php';
\App\Core\Autoloader::register();

// 2. Load Global Helpers (Preserves backwards-compatibility with all views)
require_once APP_PATH . '/helpers.php';

// 3. Load Environment Variables from .env if present
$envFile = BASE_PATH . '/.env';
if (file_exists($envFile)) {
    \App\Core\Env::load($envFile);
}

// 4. Load Configuration
$appConfig = require BASE_PATH . '/config/app.php';

// 5. Configure Timezone
date_default_timezone_set($appConfig['timezone'] ?? 'Asia/Karachi');

// 6. Configure Error Reporting and Logging
if (!empty($appConfig['debug'])) {
    error_reporting(E_ALL);
    ini_set('display_errors', '1');
    ini_set('display_startup_errors', '1');
} else {
    error_reporting(0);
    ini_set('display_errors', '0');
    ini_set('log_errors', '1');
    ini_set('error_log', STORAGE_PATH . '/logs/error.log');
}

return [
    'config' => $appConfig,
    'started_at' => microtime(true),
];
