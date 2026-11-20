<?php
/**
 * FINORA - Transaction Validator
 *
 * Validates income, expense, and transfer transaction payloads.
 *
 * @package Finora\Validators
 * @author Danish Ali <https://github.com/danishalipny-pixel>
 */

declare(strict_types=1);

namespace App\Validators;

use App\Core\Validator;

class TransactionValidator implements ValidatorInterface
{
    private Validator $validator;

    public function validate(array $data): bool
    {
        $rules = [
            'type'             => 'required|in:income,expense,transfer',
            'amount'           => 'required|numeric|min:0.01',
            'account_id'       => 'required|integer',
            'category_id'      => 'required|integer',
            'transaction_date' => 'required|date',
            'payment_method'   => 'in:cash,bank_transfer,credit_card,debit_card,cheque,other',
            'description'      => 'max:255',
        ];

        if (($data['type'] ?? '') === 'transfer') {
            $rules['to_account_id'] = 'required|integer';
        }

        $this->validator = Validator::make($data, $rules);
        $passed = $this->validator->validate();

        // Additional domain checks
        if ($passed && ($data['type'] ?? '') === 'transfer') {
            if ((int)($data['account_id'] ?? 0) === (int)($data['to_account_id'] ?? 0)) {
                $this->validator->errors()['to_account_id'] = 'Destination account must be different from source account.';
                return false;
            }
        }

        return $passed;
    }

    public function errors(): array
    {
        return $this->validator->errors();
    }

    public function firstError(): ?string
    {
        return $this->validator->firstError();
    }
}
