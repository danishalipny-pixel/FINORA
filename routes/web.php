<?php
/**
 * FINORA - Web Route Definitions
 */

declare(strict_types=1);

use App\Controllers\AccountController;
use App\Controllers\AuthController;
use App\Controllers\BudgetController;
use App\Controllers\CategoryController;
use App\Controllers\DashboardController;
use App\Controllers\ErrorController;
use App\Controllers\NotificationController;
use App\Controllers\ProfileController;
use App\Controllers\ReportController;
use App\Controllers\SavingsGoalController;
use App\Controllers\SettingsController;
use App\Controllers\TransactionController;
use App\Middleware\AuthMiddleware;
use App\Middleware\CsrfMiddleware;
use App\Middleware\GuestMiddleware;

/** @var \App\Core\Router $router */

// --- Public / Guest Routes ---
$router->get('/demo', [AuthController::class, 'demoLogin']);
$router->get('/login', [AuthController::class, 'showLogin'], [GuestMiddleware::class]);
$router->post('/login', [AuthController::class, 'login'], [GuestMiddleware::class, CsrfMiddleware::class]);

$router->get('/register', [AuthController::class, 'showRegister'], [GuestMiddleware::class]);
$router->post('/register', [AuthController::class, 'register'], [GuestMiddleware::class, CsrfMiddleware::class]);

$router->get('/forgot-password', [AuthController::class, 'showForgotPassword'], [GuestMiddleware::class]);
$router->post('/forgot-password', [AuthController::class, 'forgotPassword'], [GuestMiddleware::class, CsrfMiddleware::class]);

$router->get('/logout', [AuthController::class, 'logout']);
$router->post('/logout', [AuthController::class, 'logout'], [CsrfMiddleware::class]);

// --- Protected Routes (Require Authentication) ---

// 1. Dashboard
$router->get('/', [DashboardController::class, 'index'], [AuthMiddleware::class]);
$router->get('/dashboard', [DashboardController::class, 'index'], [AuthMiddleware::class]);
$router->get('/api/chart-data', [DashboardController::class, 'chartData'], [AuthMiddleware::class]);

// 2. Transactions
$router->get('/transactions', [TransactionController::class, 'index'], [AuthMiddleware::class]);
$router->get('/transactions/create', [TransactionController::class, 'create'], [AuthMiddleware::class]);
$router->post('/transactions', [TransactionController::class, 'store'], [AuthMiddleware::class, CsrfMiddleware::class]);
$router->get('/transactions/{id}/edit', [TransactionController::class, 'edit'], [AuthMiddleware::class]);
$router->post('/transactions/{id}/edit', [TransactionController::class, 'update'], [AuthMiddleware::class, CsrfMiddleware::class]);
$router->post('/transactions/{id}/delete', [TransactionController::class, 'delete'], [AuthMiddleware::class, CsrfMiddleware::class]);
$router->get('/transactions/export/csv', [TransactionController::class, 'exportCsv'], [AuthMiddleware::class]);
$router->get('/transactions/import', [TransactionController::class, 'importForm'], [AuthMiddleware::class]);
$router->post('/transactions/import', [TransactionController::class, 'importCsv'], [AuthMiddleware::class, CsrfMiddleware::class]);

// 3. Categories
$router->get('/categories', [CategoryController::class, 'index'], [AuthMiddleware::class]);
$router->post('/categories', [CategoryController::class, 'store'], [AuthMiddleware::class, CsrfMiddleware::class]);
$router->post('/categories/{id}/edit', [CategoryController::class, 'update'], [AuthMiddleware::class, CsrfMiddleware::class]);
$router->post('/categories/{id}/delete', [CategoryController::class, 'delete'], [AuthMiddleware::class, CsrfMiddleware::class]);

// 4. Budgets
$router->get('/budgets', [BudgetController::class, 'index'], [AuthMiddleware::class]);
$router->post('/budgets', [BudgetController::class, 'store'], [AuthMiddleware::class, CsrfMiddleware::class]);
$router->post('/budgets/{id}/delete', [BudgetController::class, 'delete'], [AuthMiddleware::class, CsrfMiddleware::class]);

// 5. Savings Goals
$router->get('/savings', [SavingsGoalController::class, 'index'], [AuthMiddleware::class]);
$router->post('/savings', [SavingsGoalController::class, 'store'], [AuthMiddleware::class, CsrfMiddleware::class]);
$router->post('/savings/{id}/edit', [SavingsGoalController::class, 'update'], [AuthMiddleware::class, CsrfMiddleware::class]);
$router->post('/savings/{id}/delete', [SavingsGoalController::class, 'delete'], [AuthMiddleware::class, CsrfMiddleware::class]);
$router->get('/savings/{id}/contributions', [SavingsGoalController::class, 'contributions'], [AuthMiddleware::class]);
$router->post('/savings/{id}/contributions', [SavingsGoalController::class, 'addContribution'], [AuthMiddleware::class, CsrfMiddleware::class]);
$router->post('/savings/{id}/contributions/{contributionId}/delete', [SavingsGoalController::class, 'deleteContribution'], [AuthMiddleware::class, CsrfMiddleware::class]);

// 6. Accounts
$router->get('/accounts', [AccountController::class, 'index'], [AuthMiddleware::class]);
$router->get('/accounts/{id}', [AccountController::class, 'show'], [AuthMiddleware::class]);
$router->post('/accounts', [AccountController::class, 'store'], [AuthMiddleware::class, CsrfMiddleware::class]);
$router->post('/accounts/{id}/edit', [AccountController::class, 'update'], [AuthMiddleware::class, CsrfMiddleware::class]);
$router->post('/accounts/{id}/archive', [AccountController::class, 'archive'], [AuthMiddleware::class, CsrfMiddleware::class]);
$router->post('/accounts/{id}/delete', [AccountController::class, 'delete'], [AuthMiddleware::class, CsrfMiddleware::class]);

// 7. Reports & Analytics
$router->get('/reports', [ReportController::class, 'index'], [AuthMiddleware::class]);
$router->get('/reports/export/csv', [ReportController::class, 'exportCsv'], [AuthMiddleware::class]);
$router->get('/reports/print', [ReportController::class, 'printReport'], [AuthMiddleware::class]);
$router->get('/reports/download/pdf', [ReportController::class, 'downloadPdf'], [AuthMiddleware::class]);

// 8. Notifications
$router->get('/notifications', [NotificationController::class, 'index'], [AuthMiddleware::class]);
$router->post('/notifications/{id}/read', [NotificationController::class, 'markAsRead'], [AuthMiddleware::class, CsrfMiddleware::class]);
$router->post('/notifications/mark-all-read', [NotificationController::class, 'markAllRead'], [AuthMiddleware::class, CsrfMiddleware::class]);

// 9. Profile & Settings
$router->get('/profile', [ProfileController::class, 'index'], [AuthMiddleware::class]);
$router->post('/profile', [ProfileController::class, 'update'], [AuthMiddleware::class, CsrfMiddleware::class]);
$router->get('/settings', [SettingsController::class, 'index'], [AuthMiddleware::class]);
$router->post('/settings/preferences', [SettingsController::class, 'updatePreferences'], [AuthMiddleware::class, CsrfMiddleware::class]);
$router->post('/settings/password', [SettingsController::class, 'updatePassword'], [AuthMiddleware::class, CsrfMiddleware::class]);

// --- Explicit Error Testing Pages ---
$router->get('/404', [ErrorController::class, 'notFound']);
$router->get('/500', [ErrorController::class, 'serverError']);
