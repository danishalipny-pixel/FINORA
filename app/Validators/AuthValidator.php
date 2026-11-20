<?php
/**
 * FINORA - Auth Validator
 *
 * Validates registration, login, and password reset requests.
 *
 * @package Finora\Validators
 * @author Danish Ali <https://github.com/danishalipny-pixel>
 */

declare(strict_types=1);

namespace App\Validators;

use App\Core\Validator;

class AuthValidator implements ValidatorInterface
{
    private Validator $validator;
    private string $scenario;

    public function __construct(string $scenario = 'register')
    {
        $this->scenario = $scenario;
    }

    public function validate(array $data): bool
    {
        $rules = match ($this->scenario) {
            'login' => [
                'email'    => 'required|email',
                'password' => 'required',
            ],
            'forgot_password' => [
                'email' => 'required|email',
            ],
            'reset_password' => [
                'password'              => 'required|min:8',
                'password_confirmation' => 'required|matches:password',
            ],
            default => [
                'name'                  => 'required|min:2|max:100',
                'email'                 => 'required|email',
                'password'              => 'required|min:8',
                'password_confirmation' => 'required|matches:password',
            ],
        };

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
