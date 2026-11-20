<?php
/**
 * FINORA - Feature Test: Financial Report & Export Engine
 *
 * @package Finora\Tests\Feature
 * @author Danish Ali <https://github.com/danishalipny-pixel>
 */

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\User;
use App\Services\FinancialAnalyticsService;

class ReportGenerationTest
{
    public function run(): array
    {
        $results = [];

        $user = User::findByEmail('demo@finora.test');
        $userId = (int)$user['id'];

        $monthlyReport = FinancialAnalyticsService::getReportData($userId, 'monthly');

        $results[] = [
            'name' => 'Generates monthly summary financial report object',
            'pass' => isset($monthlyReport['total_income'], $monthlyReport['total_expense'], $monthlyReport['net_savings']),
        ];

        $results[] = [
            'name' => 'Report net savings equals total income minus total expense',
            'pass' => round((float)$monthlyReport['total_income'] - (float)$monthlyReport['total_expense'], 2) === round((float)$monthlyReport['net_savings'], 2),
        ];

        $results[] = [
            'name' => 'Report period label is properly formatted',
            'pass' => !empty($monthlyReport['label']) && str_contains($monthlyReport['label'], 'Monthly Report'),
        ];

        return $results;
    }
}
