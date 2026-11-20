<?php
/**
 * FINORA - Auth Middleware
 */

declare(strict_types=1);

namespace App\Middleware;

use App\Core\Auth;
use App\Core\Request;
use App\Core\Response;
use App\Core\Session;

class AuthMiddleware
{
    public function handle(Request $request): void
    {
        if (!Auth::check()) {
            Session::set('_intended_url', $request->uri());
            Session::setFlash('warning', 'Please sign in to access your financial dashboard.');
            Response::redirect('/login');
        }
    }
}
