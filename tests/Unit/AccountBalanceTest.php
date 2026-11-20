<?php
/**
 * FINORA - Unit Test: Account Balance Computation
 *
 * @package Finora\Tests\Unit
 * @author Danish Ali <https://github.com/danishalipny-pixel>
 */

declare(strict_types=1);

namespace Tests\Unit;

use App\Models\Account;
use App\Models\User;

class AccountBalanceTest
{
    public function run(): array
    {
        $results = [];

        $user = User::findByEmail('demo@finora.test');
        if (!$user) {
            $results[] = ['name' => 'Demo user exists for balance test', 'pass' => false];
            return $results;
        }

        $userId = (int)$user['id'];
        $accounts = Account::allForUserWithBalance($userId);

        $results[] = [
            'name' => 'Retrieved active accounts with reconciled balances',
            'pass' => count($accounts) >= 4,
        ];

        foreach ($accounts as $acc) {
            $calcBalance = Account::calculateBalance($userId, (int)$acc['id'], (float)$acc['opening_balance']);
            $results[] = [
                'name' => "Account '{$acc['name']}' reconciled balance is non-negative and valid numeric",
                'pass' => is_numeric($calcBalance) && $calcBalance >= 0,
            ];
        }

        return $results;
    }
}
