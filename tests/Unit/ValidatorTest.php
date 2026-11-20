<?php
/**
 * FINORA - Unit Test: Validation Engine
 *
 * @package Finora\Tests\Unit
 * @author Danish Ali <https://github.com/danishalipny-pixel>
 */

declare(strict_types=1);

namespace Tests\Unit;

use App\Core\Validator;
use App\Validators\TransactionValidator;
use App\Validators\AccountValidator;
use App\Validators\BudgetValidator;
use App\Validators\SavingsGoalValidator;
use App\Validators\AuthValidator;

class ValidatorTest
{
    public function run(): array
    {
        $results = [];

        // 1. Core Validator test
        $v1 = Validator::make([
            'email' => 'valid@finora.test',
            'amount' => '1500.50',
            'status' => 'active'
        ], [
            'email' => 'required|email',
            'amount' => 'required|numeric|min:1',
            'status' => 'required|in:active,inactive'
        ]);
        $results[] = [
            'name' => 'Core Validator accepts valid payload',
            'pass' => $v1->passes(),
        ];

        // 2. Core Validator failure test
        $v2 = Validator::make([
            'email' => 'not-an-email',
            'amount' => '-10',
            'status' => 'unknown'
        ], [
            'email' => 'required|email',
            'amount' => 'required|numeric|min:0.01',
            'status' => 'required|in:active,inactive'
        ]);
        $results[] = [
            'name' => 'Core Validator rejects invalid email, negative amount, and invalid enum',
            'pass' => $v2->fails() && count($v2->errors()) === 3,
        ];

        // 3. Domain TransactionValidator
        $txValidator = new TransactionValidator();
        $txPass = $txValidator->validate([
            'type' => 'income',
            'amount' => 50000,
            'account_id' => 1,
            'category_id' => 2,
            'transaction_date' => '2026-11-20',
            'payment_method' => 'bank_transfer',
            'description' => 'Freelance payment'
        ]);
        $results[] = [
            'name' => 'TransactionValidator passes for valid income payload',
            'pass' => $txPass,
        ];

        // 4. Domain TransactionValidator rejection for transfer with identical accounts
        $txTransferFail = $txValidator->validate([
            'type' => 'transfer',
            'amount' => 10000,
            'account_id' => 1,
            'to_account_id' => 1,
            'category_id' => 1,
            'transaction_date' => '2026-11-20'
        ]);
        $results[] = [
            'name' => 'TransactionValidator rejects transfer when source equals destination',
            'pass' => !$txTransferFail,
        ];

        // 5. Domain AccountValidator
        $accValidator = new AccountValidator();
        $accPass = $accValidator->validate([
            'name' => 'Standard Chartered Savings',
            'type' => 'savings',
            'initial_balance' => 250000.00
        ]);
        $results[] = [
            'name' => 'AccountValidator passes for valid savings account',
            'pass' => $accPass,
        ];

        // 6. Domain BudgetValidator
        $bValidator = new BudgetValidator();
        $bPass = $bValidator->validate([
            'category_id' => 1,
            'amount' => 50000.00,
            'period' => 'monthly',
            'start_date' => '2026-11-01',
            'end_date' => '2026-11-30',
            'alert_threshold' => 80
        ]);
        $results[] = [
            'name' => 'BudgetValidator passes for valid monthly budget',
            'pass' => $bPass,
        ];

        // 7. Domain AuthValidator
        $authValidator = new AuthValidator('register');
        $authPass = $authValidator->validate([
            'name' => 'Ali Raza',
            'email' => 'ali.raza@finora.test',
            'password' => 'SecurePass123!',
            'password_confirmation' => 'SecurePass123!'
        ]);
        $results[] = [
            'name' => 'AuthValidator validates registration with password confirmation',
            'pass' => $authPass,
        ];

        return $results;
    }
}
