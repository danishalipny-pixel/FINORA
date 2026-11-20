<?php
/**
 * FINORA - Dashboard Controller
 */

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Auth;
use App\Core\Request;
use App\Core\Response;
use App\Core\View;
use App\Services\FinancialAnalyticsService;

class DashboardController
{
    public function index(): void
    {
        $userId = Auth::id();
        $dashboardData = FinancialAnalyticsService::getDashboardData($userId);

        View::render('dashboard.index', array_merge($dashboardData, [
            'title' => 'Dashboard - FINORA Financial Intelligence',
        ]));
    }

    public function chartData(Request $request): void
    {
        $userId = Auth::id();
        $dashboardData = FinancialAnalyticsService::getDashboardData($userId);

        Response::json([
            'cashflow' => $dashboardData['chart_cashflow'],
            'categories' => $dashboardData['chart_category_expenses'],
        ]);
    }
}
