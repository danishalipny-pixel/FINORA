<?php
/**
 * FINORA - Unit Test: CSRF Protection
 *
 * @package Finora\Tests\Unit
 * @author Danish Ali <https://github.com/danishalipny-pixel>
 */

declare(strict_types=1);

namespace Tests\Unit;

use App\Core\Csrf;

class CsrfTest
{
    public function run(): array
    {
        $results = [];

        $token = Csrf::getToken();
        $results[] = [
            'name' => 'Generates valid 64-character hexadecimal CSRF token',
            'pass' => is_string($token) && strlen($token) === 64 && ctype_xdigit($token),
        ];

        $field = Csrf::field();
        $results[] = [
            'name' => 'Produces valid hidden HTML CSRF input element',
            'pass' => str_contains($field, '<input type="hidden" name="_csrf_token"') && str_contains($field, $token),
        ];

        $results[] = [
            'name' => 'Valid token passes validation check',
            'pass' => Csrf::validate($token),
        ];

        $results[] = [
            'name' => 'Forged token is securely rejected',
            'pass' => !Csrf::validate('fake_invalid_token_12345'),
        ];

        return $results;
    }
}
