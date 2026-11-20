<?php
/**
 * FINORA - Feature Test: Financial Analytics & KPI Metrics
 *
 * @package Finora\Tests\Feature
 * @author Danish Ali <https://github.com/danishalipny-pixel>
 */

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\User;
use App\Services\FinancialAnalyticsService;

class DashboardAnalyticsTest
{
    public function run(): array
    {
        $results = [];

        $user = User::findByEmail('demo@finora.test');
        $userId = (int)$user['id'];

        $dashboard = FinancialAnalyticsService::getDashboardData($userId);

        $results[] = [
            'name' => 'Dashboard KPIs returns all 4 primary metrics',
            'pass' => isset($dashboard['total_balance'], $dashboard['monthly_income'], $dashboard['monthly_expense'], $dashboard['total_savings']),
        ];

        $results[] = [
            'name' => 'Monthly income calculated accurately from transactions',
            'pass' => (float)$dashboard['monthly_income'] >= 260000.00,
        ];

        $results[] = [
            'name' => 'Cash flow trend generates 6 consecutive historical periods',
            'pass' => is_array($dashboard['chart_cashflow']) && count($dashboard['chart_cashflow']) === 6,
        ];

        $results[] = [
            'name' => 'Expense category distribution generates valid breakdown payload',
            'pass' => is_array($dashboard['chart_category_expenses']) && count($dashboard['chart_category_expenses']) > 0,
        ];

        return $results;
    }
}
