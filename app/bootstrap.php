<?php
/**
 * FINORA - Application Bootstrap Forwarder
 *
 * Forwards execution to /bootstrap/app.php while maintaining backwards compatibility.
 */

declare(strict_types=1);

return require_once dirname(__DIR__) . '/bootstrap/app.php';
