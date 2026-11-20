<?php
/**
 * FINORA - Application Configuration
 */

declare(strict_types=1);

use App\Core\Env;

return [
    'name' => Env::get('APP_NAME', 'FINORA – Personal Finance Intelligence'),
    'tagline' => 'Track Smarter. Spend Better. Grow Wealth.',
    'env' => Env::get('APP_ENV', 'development'), // 'development' or 'production'
    'debug' => (bool)Env::get('APP_DEBUG', true),
    'url' => Env::get('APP_URL', 'http://localhost:8000'),
    'timezone' => Env::get('APP_TIMEZONE', 'Asia/Karachi'),
    'locale' => 'en',
    
    // Financial defaults
    'currency' => Env::get('APP_CURRENCY', 'PKR'),
    'currency_symbol' => Env::get('APP_CURRENCY_SYMBOL', 'Rs.'),
    'date_format' => Env::get('APP_DATE_FORMAT', 'd M Y'),

    // Session settings
    'session' => [
        'lifetime' => (int)Env::get('SESSION_LIFETIME', 7200), // 2 hours
        'cookie_secure' => (bool)Env::get('SESSION_SECURE', false), // true in HTTPS production
        'cookie_httponly' => true,
        'cookie_samesite' => 'Lax',
        'name' => 'FINORA_SESSION',
    ],

    // Security & Rate Limiting
    'security' => [
        'max_login_attempts' => (int)Env::get('MAX_LOGIN_ATTEMPTS', 5),
        'lockout_duration_seconds' => (int)Env::get('LOCKOUT_DURATION_SECONDS', 900), // 15 minutes
    ],
];
