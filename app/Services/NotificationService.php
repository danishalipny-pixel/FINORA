<?php
/**
 * FINORA - Notification Service
 */

declare(strict_types=1);

namespace App\Services;

use App\Core\Database;
use App\Models\Budget;
use App\Models\Notification;

class NotificationService
{
    public static function checkBudgetAlerts(int $userId, int $categoryId, string $transactionDate): void
    {
        $timestamp = strtotime($transactionDate);
        $month = (int)date('m', $timestamp);
        $year = (int)date('Y', $timestamp);

        $pdo = Database::getConnection();

        // Check user preference
        $stmtPref = $pdo->prepare("SELECT notify_budget_warning FROM user_settings WHERE user_id = ? LIMIT 1");
        $stmtPref->execute([$userId]);
        $pref = (int)$stmtPref->fetchColumn();
        if ($pref === 0) {
            return;
        }

        // Find budget for this category & month
        $stmtBgt = $pdo->prepare("
            SELECT b.*, c.name AS category_name 
            FROM budgets b 
            JOIN categories c ON b.category_id = c.id 
            WHERE b.user_id = ? AND b.category_id = ? AND b.month = ? AND b.year = ? 
            LIMIT 1
        ");
        $stmtBgt->execute([$userId, $categoryId, $month, $year]);
        $budget = $stmtBgt->fetch();

        if (!$budget) {
            return;
        }

        $limit = (float)$budget['amount'];
        if ($limit <= 0) {
            return;
        }

        // Calculate current spent
        $startDate = sprintf('%04d-%02d-01', $year, $month);
        $endDate = date('Y-m-t', strtotime($startDate));

        $stmtSpent = $pdo->prepare("
            SELECT COALESCE(SUM(amount), 0) 
            FROM transactions 
            WHERE user_id = ? AND category_id = ? AND type = 'expense' AND transaction_date BETWEEN ? AND ?
        ");
        $stmtSpent->execute([$userId, $categoryId, $startDate, $endDate]);
        $spent = (float)$stmtSpent->fetchColumn();

        $percentage = ($spent / $limit) * 100;

        // Check if an alert was already logged today for this category
        $stmtCheckNotif = $pdo->prepare("
            SELECT COUNT(*) 
            FROM notifications 
            WHERE user_id = ? AND type = 'budget_warning' 
              AND message LIKE ? AND DATE(created_at) = CURRENT_DATE()
        ");
        $catSearch = "%" . $budget['category_name'] . "%";
        $stmtCheckNotif->execute([$userId, $catSearch]);
        if ((int)$stmtCheckNotif->fetchColumn() > 0) {
            return;
        }

        if ($percentage >= 100) {
            Notification::create(
                $userId,
                'budget_warning',
                "Budget Exceeded: {$budget['category_name']}",
                "You have exceeded your monthly limit for {$budget['category_name']}. Spent: " . currency($spent) . " / Limit: " . currency($limit) . " (" . round($percentage, 1) . "%).",
                '/budgets'
            );
        } elseif ($percentage >= 80) {
            Notification::create(
                $userId,
                'budget_warning',
                "Budget Warning: {$budget['category_name']}",
                "You have utilized " . round($percentage, 1) . "% of your {$budget['category_name']} budget (" . currency($spent) . " of " . currency($limit) . ").",
                '/budgets'
            );
        }
    }
}
