<?php
/**
 * FINORA - Personal Finance Intelligence
 * Main Application Front Controller
 */

declare(strict_types=1);

require_once dirname(__DIR__) . '/app/bootstrap.php';

use App\Core\Request;
use App\Core\Response;
use App\Core\Router;
use App\Core\Session;
use App\Core\View;

try {
    // Start Secure Session
    Session::start();

    // Create Request & Router
    $request = new Request();
    $router = new Router();

    // Load Routes
    require_once dirname(__DIR__) . '/routes/web.php';

    // Dispatch
    $router->dispatch($request);
} catch (\Throwable $e) {
    $appConfig = require dirname(__DIR__) . '/config/app.php';

    // Log the error
    error_log(sprintf(
        "[%s] Uncaught %s: %s in %s:%d\nStack Trace:\n%s",
        date('Y-m-d H:i:s'),
        get_class($e),
        $e->getMessage(),
        $e->getFile(),
        $e->getLine(),
        $e->getTraceAsString()
    ));

    http_response_code(500);

    if ($appConfig['debug']) {
        echo "<div style='font-family:sans-serif; background:#FFF5F5; color:#9B1C1C; padding:24px; border:1px solid #F87171; border-radius:12px; margin:24px;'>";
        echo "<h2 style='margin-top:0;'>Application Exception: " . htmlspecialchars($e->getMessage()) . "</h2>";
        echo "<p><strong>File:</strong> " . htmlspecialchars($e->getFile()) . ":" . $e->getLine() . "</p>";
        echo "<pre style='background:#1F2937; color:#F9FAFB; padding:16px; border-radius:8px; overflow-x:auto;'>" . htmlspecialchars($e->getTraceAsString()) . "</pre>";
        echo "</div>";
    } else {
        View::render('errors.500', ['title' => 'Server Error - FINORA'], 'none');
    }
}
