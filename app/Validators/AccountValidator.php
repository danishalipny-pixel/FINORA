<?php
/**
 * FINORA - Account Validator
 *
 * Validates financial account creation and update payloads.
 *
 * @package Finora\Validators
 * @author Danish Ali <https://github.com/danishalipny-pixel>
 */

declare(strict_types=1);

namespace App\Validators;

use App\Core\Validator;

class AccountValidator implements ValidatorInterface
{
    private Validator $validator;

    public function validate(array $data): bool
    {
        $rules = [
            'name'            => 'required|min:2|max:100',
            'type'            => 'required|in:checking,savings,credit_card,cash,investment,loan,other',
            'initial_balance' => 'numeric',
            'currency'        => 'max:10',
            'account_number'  => 'max:50',
            'bank_name'       => 'max:100',
            'color'           => 'max:20',
        ];

        $this->validator = Validator::make($data, $rules);
        return $this->validator->validate();
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
