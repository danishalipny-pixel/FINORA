<?php
/**
 * FINORA - Budget Validator
 *
 * Validates budget creation and threshold definitions.
 *
 * @package Finora\Validators
 * @author Danish Ali <https://github.com/danishalipny-pixel>
 */

declare(strict_types=1);

namespace App\Validators;

use App\Core\Validator;

class BudgetValidator implements ValidatorInterface
{
    private Validator $validator;

    public function validate(array $data): bool
    {
        $rules = [
            'category_id'      => 'required|integer',
            'amount'           => 'required|numeric|min:1',
            'period'           => 'required|in:monthly,weekly,yearly',
            'start_date'       => 'required|date',
            'end_date'         => 'date',
            'alert_threshold'  => 'numeric|min:1|max:100',
        ];

        $this->validator = Validator::make($data, $rules);
        $passed = $this->validator->validate();

        if ($passed && !empty($data['start_date']) && !empty($data['end_date'])) {
            if (strtotime((string)$data['end_date']) < strtotime((string)$data['start_date'])) {
                $this->validator->errors()['end_date'] = 'End date cannot be prior to start date.';
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
