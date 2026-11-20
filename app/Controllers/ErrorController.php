<?php
/**
 * FINORA - Error Controller
 */

declare(strict_types=1);

namespace App\Controllers;

use App\Core\View;

class ErrorController
{
    public function notFound(): void
    {
        http_response_code(404);
        View::render('errors.404', ['title' => '404 Page Not Found - FINORA'], 'none');
    }

    public function serverError(): void
    {
        http_response_code(500);
        View::render('errors.500', ['title' => '500 Server Error - FINORA'], 'none');
    }
}
