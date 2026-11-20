<?php
/**
 * FINORA - CSRF Verification Middleware
 */

declare(strict_types=1);

namespace App\Middleware;

use App\Core\Csrf;
use App\Core\Request;
use App\Core\Response;
use App\Core\Session;

class CsrfMiddleware
{
    public function handle(Request $request): void
    {
        if ($request->isPost()) {
            $token = $request->csrfToken();
            if (!Csrf::validate($token)) {
                if ($request->isAjax()) {
                    Response::json(['success' => false, 'message' => 'CSRF token mismatch. Please reload the page.'], 419);
                }
                Session::setFlash('error', 'Your session or security token expired. Please try again.');
                Response::back();
            }
        }
    }
}
