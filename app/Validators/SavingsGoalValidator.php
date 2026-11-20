<?php
/**
 * FINORA - Savings Goal Validator
 *
 * Validates financial target definitions, target amounts, and deadlines.
 *
 * @package Finora\Validators
 * @author Danish Ali <https://github.com/danishalipny-pixel>
 */

declare(strict_types=1);

namespace App\Validators;

use App\Core\Validator;

class SavingsGoalValidator implements ValidatorInterface
{
    private Validator $validator;

    public function validate(array $data): bool
    {
        $rules = [
            'name'           => 'required|min:2|max:100',
            'target_amount'  => 'required|numeric|min:1',
            'current_amount' => 'numeric|min:0',
            'target_date'    => 'required|date',
            'color'          => 'max:20',
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
