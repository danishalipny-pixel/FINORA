<?php
/**
 * FINORA - Built-in PHP Server Router
 */

declare(strict_types=1);

$uri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
$publicFile = __DIR__ . '/public' . $uri;

// Serve static assets directly if they exist
if ($uri !== '/' && file_exists($publicFile) && !is_dir($publicFile)) {
    return false;
}

// Explicitly set 200 OK status header for PHP built-in web server
header("HTTP/1.1 200 OK");

// Route all dynamic traffic to public/index.php
require_once __DIR__ . '/public/index.php';
