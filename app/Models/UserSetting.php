<?php
/**
 * FINORA - User Settings Model
 */

declare(strict_types=1);

namespace App\Models;

use App\Core\Database;

class UserSetting
{
    public static function getByUserId(int $userId): array
    {
        $pdo = Database::getConnection();
        $stmt = $pdo->prepare("SELECT * FROM user_settings WHERE user_id = ? LIMIT 1");
        $stmt->execute([$userId]);
        $row = $stmt->fetch();

        if ($row) {
            return $row;
        }

        return [
            'currency' => 'PKR',
            'currency_symbol' => 'Rs.',
            'date_format' => 'd M Y',
            'theme' => 'light',
            'notify_budget_warning' => 1,
            'notify_goal_reached' => 1,
            'notify_monthly_summary' => 1,
        ];
    }

    public static function update(int $userId, array $data): bool
    {
        $pdo = Database::getConnection();
        $stmt = $pdo->prepare("
            UPDATE user_settings 
            SET currency = ?,
                currency_symbol = ?,
                date_format = ?,
                theme = ?,
                notify_budget_warning = ?,
                notify_goal_reached = ?,
                notify_monthly_summary = ?
            WHERE user_id = ?
        ");

        return $stmt->execute([
            $data['currency'] ?? 'PKR',
            $data['currency_symbol'] ?? 'Rs.',
            $data['date_format'] ?? 'd M Y',
            $data['theme'] ?? 'light',
            (int)($data['notify_budget_warning'] ?? 1),
            (int)($data['notify_goal_reached'] ?? 1),
            (int)($data['notify_monthly_summary'] ?? 1),
            $userId
        ]);
    }
}
