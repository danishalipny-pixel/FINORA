<?php
/**
 * FINORA - Authentication Controller
 */

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Auth;
use App\Core\Request;
use App\Core\Response;
use App\Core\Session;
use App\Core\Validator;
use App\Core\View;
use App\Models\User;

class AuthController
{
    public function showLogin(): void
    {
        View::render('auth.login', ['title' => 'Sign In - FINORA'], 'auth');
    }

    public function login(Request $request): void
    {
        $data = $request->all();
        Session::setOldInput($data);

        $validator = Validator::make($data, [
            'email' => 'required|email',
            'password' => 'required|min:6',
        ]);

        if ($validator->fails()) {
            Session::setErrors($validator->errors());
            Response::redirect('/login');
        }

        $result = Auth::attempt(trim($data['email']), (string)$data['password']);

        if (!$result['success']) {
            Session::setFlash('error', $result['message']);
            Response::redirect('/login');
        }

        Session::clearOldInput();
        Session::setFlash('success', 'Welcome back to FINORA, ' . htmlspecialchars($result['user']['name']) . '!');

        $intended = Session::get('_intended_url', '/dashboard');
        Session::remove('_intended_url');
        Response::redirect($intended);
    }

    public function showRegister(): void
    {
        View::render('auth.register', ['title' => 'Create Account - FINORA'], 'auth');
    }

    public function register(Request $request): void
    {
        $data = $request->all();
        Session::setOldInput($data);

        $validator = Validator::make($data, [
            'name' => 'required|min:2|max:100',
            'email' => 'required|email|max:191|unique:users,email',
            'password' => 'required|min:8|max:72',
            'password_confirmation' => 'required|matches:password',
        ], [
            'password_confirmation' => 'Passwords do not match.',
            'email' => 'This email is already registered.'
        ]);

        if ($validator->fails()) {
            Session::setErrors($validator->errors());
            Response::redirect('/register');
        }

        try {
            $userId = User::create([
                'name' => trim($data['name']),
                'email' => strtolower(trim($data['email'])),
                'password' => $data['password'],
            ]);

            Auth::login($userId);
            Session::clearOldInput();
            Session::setFlash('success', 'Your FINORA account has been successfully created! Let’s start managing your wealth.');
            Response::redirect('/dashboard');
        } catch (\Throwable $e) {
            Session::setFlash('error', 'Registration failed. Please try again.');
            Response::redirect('/register');
        }
    }

    public function showForgotPassword(): void
    {
        View::render('auth.forgot-password', ['title' => 'Reset Password - FINORA'], 'auth');
    }

    public function forgotPassword(Request $request): void
    {
        $data = $request->all();
        $validator = Validator::make($data, [
            'email' => 'required|email',
        ]);

        if ($validator->fails()) {
            Session::setErrors($validator->errors());
            Response::redirect('/forgot-password');
        }

        $email = strtolower(trim($data['email']));
        $user = User::findByEmail($email);

        if ($user) {
            $token = bin2hex(random_bytes(32));
            $expiresAt = date('Y-m-d H:i:s', time() + 3600); // 1 hour
            User::createPasswordReset($email, $token, $expiresAt);

            // In production without external mail API, log simulated reset or show security instructions
            Session::setFlash('success', 'If an account exists with this email, instructions have been generated. (For testing, demo account credentials are available in README.md).');
        } else {
            Session::setFlash('success', 'If an account exists with this email, instructions have been generated.');
        }

        Response::redirect('/login');
    }

    public function logout(): void
    {
        Auth::logout();
        Session::setFlash('info', 'You have been safely signed out. See you next time!');
        Response::redirect('/login');
    }

    public function demoLogin(): void
    {
        $user = User::findByEmail('demo@finora.test');
        if ($user) {
            Auth::login((int)$user['id']);
            Session::setFlash('success', 'Welcome to FINORA Portfolio Showcase, ' . htmlspecialchars($user['name']) . '!');
        }
        Response::redirect('/dashboard');
    }
}
