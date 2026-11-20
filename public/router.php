<?php
/**
 * FINORA - Built-in Server Router for public document root
 */

declare(strict_types=1);

$uri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
$file = __DIR__ . $uri;

// Serve static assets directly
if ($uri !== '/' && file_exists($file) && !is_dir($file)) {
    return false;
}

// Route dynamic traffic to index.php
require_once __DIR__ . '/index.php';
