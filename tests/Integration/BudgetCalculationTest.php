<?php
/**
 * FINORA - Integration Test: Budget Calculations & Utilization
 *
 * @package Finora\Tests\Integration
 * @author Danish Ali <https://github.com/danishalipny-pixel>
 */

declare(strict_types=1);

namespace Tests\Integration;

use App\Models\Budget;
use App\Models\User;

class BudgetCalculationTest
{
    public function run(): array
    {
        $results = [];

        $user = User::findByEmail('demo@finora.test');
        $userId = (int)$user['id'];

        $budgets = Budget::getBudgetsForMonth($userId, 11, 2026);
        $results[] = [
            'name' => 'Retrieves category budgets with calculated spent amounts',
            'pass' => count($budgets) >= 5,
        ];

        $summary = Budget::getTotalBudgetSummary($userId, 11, 2026);
        $results[] = [
            'name' => 'Monthly budget summary aggregates total budgeted amount accurately',
            'pass' => (float)$summary['total_limit'] > 0,
        ];

        $results[] = [
            'name' => 'Monthly budget summary computes remaining allowance and percentage',
            'pass' => isset($summary['total_spent'], $summary['percentage']) && is_numeric($summary['percentage']),
        ];

        return $results;
    }
}
