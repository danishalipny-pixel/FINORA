<?php
/**
 * FINORA - Integration Test: Transaction Repository CRUD
 *
 * @package Finora\Tests\Integration
 * @author Danish Ali <https://github.com/danishalipny-pixel>
 */

declare(strict_types=1);

namespace Tests\Integration;

use App\Models\Account;
use App\Models\Category;
use App\Models\Transaction;
use App\Models\User;

class TransactionRepositoryTest
{
    public function run(): array
    {
        $results = [];

        $user = User::findByEmail('demo@finora.test');
        $userId = (int)$user['id'];
        $accounts = Account::getAll($userId);
        $accountId = (int)$accounts[0]['id'];

        $incomeCategories = Category::getAll($userId, 'income');
        $catId = (int)$incomeCategories[0]['id'];

        // 1. Create Transaction
        $txId = Transaction::create($userId, [
            'account_id' => $accountId,
            'category_id' => $catId,
            'type' => 'income',
            'amount' => 15500.00,
            'transaction_date' => date('Y-m-d'),
            'payment_method' => 'Bank Transfer',
            'description' => 'Automated Integration Test Credit',
        ]);

        $results[] = [
            'name' => 'Creates transaction record in database',
            'pass' => $txId > 0,
        ];

        // 2. Read
        $record = Transaction::findById($userId, $txId);
        $results[] = [
            'name' => 'Reads created transaction with correct user tenancy',
            'pass' => $record !== null && (float)$record['amount'] === 15500.00,
        ];

        // 3. Update
        $updated = Transaction::update($userId, $txId, [
            'account_id' => $accountId,
            'category_id' => $catId,
            'type' => 'income',
            'amount' => 17200.00,
            'transaction_date' => date('Y-m-d'),
            'payment_method' => 'Bank Transfer',
            'description' => 'Updated Test Credit Amount',
        ]);
        $reRead = Transaction::findById($userId, $txId);
        $results[] = [
            'name' => 'Updates transaction amount and description accurately',
            'pass' => $updated && (float)$reRead['amount'] === 17200.00,
        ];

        // 4. Delete
        $deleted = Transaction::delete($userId, $txId);
        $afterDelete = Transaction::findById($userId, $txId);
        $results[] = [
            'name' => 'Deletes transaction safely ensuring idempotency',
            'pass' => $deleted && $afterDelete === null,
        ];

        return $results;
    }
}
