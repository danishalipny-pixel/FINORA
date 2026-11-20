<?php
/**
 * FINORA - Integration Test: Savings Goals & Contributions
 *
 * @package Finora\Tests\Integration
 * @author Danish Ali <https://github.com/danishalipny-pixel>
 */

declare(strict_types=1);

namespace Tests\Integration;

use App\Models\SavingsGoal;
use App\Models\SavingsContribution;
use App\Models\User;

class SavingsContributionTest
{
    public function run(): array
    {
        $results = [];

        $user = User::findByEmail('demo@finora.test');
        $userId = (int)$user['id'];

        $goals = SavingsGoal::getAll($userId);
        $results[] = [
            'name' => 'Retrieves user savings goals with target calculations',
            'pass' => count($goals) >= 4,
        ];

        if (!empty($goals)) {
            $firstGoal = $goals[0];
            $contributions = SavingsContribution::getByGoal($userId, (int)$firstGoal['id']);
            $results[] = [
                'name' => 'Retrieves associated savings contributions for goal',
                'pass' => is_array($contributions),
            ];

            $results[] = [
                'name' => 'Savings goal progress percentage is accurately bounded between 0 and 100',
                'pass' => $firstGoal['percentage'] >= 0 && $firstGoal['percentage'] <= 100,
            ];
        }

        return $results;
    }
}
