<?php
/**
 * FINORA - Unit Test: Password Security & Verification
 *
 * @package Finora\Tests\Unit
 * @author Danish Ali <https://github.com/danishalipny-pixel>
 */

declare(strict_types=1);

namespace Tests\Unit;

use App\Models\User;

class PasswordSecurityTest
{
    public function run(): array
    {
        $results = [];

        $plain = 'FinoraSecurePassword2026!';
        $hash = password_hash($plain, PASSWORD_BCRYPT, ['cost' => 12]);

        $results[] = [
            'name' => 'Password hashing produces valid bcrypt string ($2y$)',
            'pass' => str_starts_with($hash, '$2y$'),
        ];

        $results[] = [
            'name' => 'password_verify correctly matches legitimate secret',
            'pass' => password_verify($plain, $hash),
        ];

        $results[] = [
            'name' => 'password_verify rejects incorrect secret',
            'pass' => !password_verify('WrongPassword999', $hash),
        ];

        $user = User::findByEmail('demo@finora.test');
        $results[] = [
            'name' => 'Demo user credentials verify against stored hash',
            'pass' => $user !== null && password_verify('FinoraDemo123!', $user['password']),
        ];

        return $results;
    }
}
