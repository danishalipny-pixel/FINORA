<?php
/**
 * FINORA - Feature Test: Authentication & User Lifecycle
 *
 * @package Finora\Tests\Feature
 * @author Danish Ali <https://github.com/danishalipny-pixel>
 */

declare(strict_types=1);

namespace Tests\Feature;

use App\Core\Auth;
use App\Models\User;

class AuthenticationFlowTest
{
    public function run(): array
    {
        $results = [];

        // 1. Attempt invalid login
        $failedAttempt = Auth::attempt('nonexistent@finora.test', 'RandomPassword123!');
        $results[] = [
            'name' => 'Rejects authentication for nonexistent user email',
            'pass' => isset($failedAttempt['success']) && $failedAttempt['success'] === false,
        ];

        // 2. Attempt valid login
        $successAttempt = Auth::attempt('demo@finora.test', 'FinoraDemo123!');
        $results[] = [
            'name' => 'Authenticates demo user credentials successfully',
            'pass' => isset($successAttempt['success']) && $successAttempt['success'] === true && Auth::check(),
        ];

        // 3. User session data
        $currentUser = Auth::user();
        $results[] = [
            'name' => 'Session holds active user payload without plaintext password',
            'pass' => is_array($currentUser) && $currentUser['email'] === 'demo@finora.test' && !isset($currentUser['password']),
        ];

        // 4. Logout
        Auth::logout();
        $results[] = [
            'name' => 'Logout flushes authenticated session state',
            'pass' => !Auth::check(),
        ];

        return $results;
    }
}
