<?php
/**
 * FINORA - System & User Settings Controller
 */

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Auth;
use App\Core\Database;
use App\Core\Request;
use App\Core\Response;
use App\Core\Session;
use App\Core\Validator;
use App\Core\View;
use App\Models\User;
use App\Models\UserSetting;

class SettingsController
{
    public function index(): void
    {
        $userId = Auth::id();
        $settings = UserSetting::getByUserId($userId);

        View::render('settings.index', [
            'title' => 'Preferences & Settings - FINORA',
            'settings' => $settings,
        ]);
    }

    public function updatePreferences(Request $request): void
    {
        $userId = Auth::id();
        $data = $request->all();

        $currency = $data['currency'] ?? 'PKR';
        $symbols = [
            'PKR' => 'Rs.',
            'USD' => '$',
            'EUR' => '€',
            'GBP' => '£',
            'AED' => 'AED',
            'SAR' => 'SAR',
            'INR' => '₹',
            'CAD' => 'CA$',
        ];
        $currencySymbol = $symbols[$currency] ?? 'Rs.';

        $updateData = [
            'currency' => $currency,
            'currency_symbol' => $currencySymbol,
            'date_format' => $data['date_format'] ?? 'd M Y',
            'theme' => in_array($data['theme'] ?? 'light', ['light', 'dark'], true) ? $data['theme'] : 'light',
            'notify_budget_warning' => isset($data['notify_budget_warning']) ? 1 : 0,
            'notify_goal_reached' => isset($data['notify_goal_reached']) ? 1 : 0,
            'notify_monthly_summary' => isset($data['notify_monthly_summary']) ? 1 : 0,
        ];

        try {
            UserSetting::update($userId, $updateData);
            Auth::refresh();
            Session::setFlash('success', 'Preferences updated successfully.');
            Response::redirect('/settings');
        } catch (\Throwable $e) {
            Session::setFlash('error', 'Failed to update preferences: ' . $e->getMessage());
            Response::redirect('/settings');
        }
    }

    public function updatePassword(Request $request): void
    {
        $userId = Auth::id();
        $data = $request->all();

        $validator = Validator::make($data, [
            'current_password' => 'required',
            'new_password' => 'required|min:8|max:72',
            'new_password_confirmation' => 'required|matches:new_password',
        ], [
            'new_password_confirmation' => 'New password confirmation does not match.',
        ]);

        if ($validator->fails()) {
            Session::setErrors($validator->errors());
            Response::redirect('/settings');
        }

        // Verify current password
        $pdo = Database::getConnection();
        $stmt = $pdo->prepare("SELECT password FROM users WHERE id = ? LIMIT 1");
        $stmt->execute([$userId]);
        $currentHash = $stmt->fetchColumn();

        if (!password_verify($data['current_password'], (string)$currentHash)) {
            Session::setErrors(['current_password' => 'Incorrect current password.']);
            Response::redirect('/settings');
        }

        try {
            User::updatePassword($userId, $data['new_password']);
            Session::setFlash('success', 'Password successfully changed! Use your new password on future logins.');
            Response::redirect('/settings');
        } catch (\Throwable $e) {
            Session::setFlash('error', 'Failed to update password: ' . $e->getMessage());
            Response::redirect('/settings');
        }
    }
}
