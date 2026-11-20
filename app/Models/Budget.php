<?php
/**
 * FINORA - Budget Model
 */

declare(strict_types=1);

namespace App\Models;

use App\Core\Database;
use PDO;

class Budget
{
    public static function getBudgetsForMonth(int $userId, int $month, int $year): array
    {
        $pdo = Database::getConnection();

        $startDate = sprintf('%04d-%02d-01', $year, $month);
        $endDate = date('Y-m-t', strtotime($startDate));

        $stmt = $pdo->prepare("
            SELECT 
                b.id,
                b.user_id,
                b.category_id,
                b.amount AS budget_limit,
                b.month,
                b.year,
                c.name AS category_name,
                c.icon AS category_icon,
                c.color AS category_color,
                COALESCE(
                    (SELECT SUM(t.amount) 
                     FROM transactions t 
                     WHERE t.user_id = b.user_id 
                       AND t.category_id = b.category_id 
                       AND t.type = 'expense' 
                       AND t.transaction_date BETWEEN ? AND ?
                    ), 0
                ) AS spent_amount
            FROM budgets b
            JOIN categories c ON b.category_id = c.id
            WHERE b.user_id = ? AND b.month = ? AND b.year = ?
            ORDER BY b.amount DESC
        ");

        $stmt->execute([$startDate, $endDate, $userId, $month, $year]);
        $budgets = $stmt->fetchAll();

        foreach ($budgets as &$b) {
            $limit = (float)$b['budget_limit'];
            $spent = (float)$b['spent_amount'];
            $remaining = round($limit - $spent, 2);
            $percentage = $limit > 0 ? round(($spent / $limit) * 100, 1) : 0.0;

            if ($percentage > 100) {
                $status = 'exceeded';
            } elseif ($percentage >= 80) {
                $status = 'warning';
            } else {
                $status = 'normal';
            }

            $b['remaining'] = $remaining;
            $b['percentage'] = $percentage;
            $b['status'] = $status;
        }

        return $budgets;
    }

    public static function getTotalBudgetSummary(int $userId, int $month, int $year): array
    {
        $budgets = self::getBudgetsForMonth($userId, $month, $year);
        $totalLimit = 0.0;
        $totalSpent = 0.0;
        $warningCount = 0;
        $exceededCount = 0;

        foreach ($budgets as $b) {
            $totalLimit += (float)$b['budget_limit'];
            $totalSpent += (float)$b['spent_amount'];
            if ($b['status'] === 'warning') {
                $warningCount++;
            } elseif ($b['status'] === 'exceeded') {
                $exceededCount++;
            }
        }

        $remaining = round($totalLimit - $totalSpent, 2);
        $percentage = $totalLimit > 0 ? round(($totalSpent / $totalLimit) * 100, 1) : 0.0;

        return [
            'total_limit' => $totalLimit,
            'total_spent' => $totalSpent,
            'total_remaining' => $remaining,
            'percentage' => $percentage,
            'warning_count' => $warningCount,
            'exceeded_count' => $exceededCount,
            'items_count' => count($budgets),
        ];
    }

    public static function findById(int $userId, int $id): ?array
    {
        $pdo = Database::getConnection();
        $stmt = $pdo->prepare("
            SELECT b.*, c.name AS category_name
            FROM budgets b
            JOIN categories c ON b.category_id = c.id
            WHERE b.id = ? AND b.user_id = ?
            LIMIT 1
        ");
        $stmt->execute([$id, $userId]);
        $row = $stmt->fetch();
        return $row ?: null;
    }

    public static function createOrUpdate(int $userId, int $categoryId, float $amount, int $month, int $year): int
    {
        if ($amount < 0) {
            throw new \InvalidArgumentException("Budget amount cannot be negative.");
        }

        $pdo = Database::getConnection();

        // Check if exists
        $stmtCheck = $pdo->prepare("SELECT id FROM budgets WHERE user_id = ? AND category_id = ? AND month = ? AND year = ? LIMIT 1");
        $stmtCheck->execute([$userId, $categoryId, $month, $year]);
        $existing = $stmtCheck->fetch();

        if ($existing) {
            $stmtUpd = $pdo->prepare("UPDATE budgets SET amount = ? WHERE id = ?");
            $stmtUpd->execute([$amount, $existing['id']]);
            return (int)$existing['id'];
        }

        $stmtIns = $pdo->prepare("INSERT INTO budgets (user_id, category_id, amount, month, year) VALUES (?, ?, ?, ?, ?)");
        $stmtIns->execute([$userId, $categoryId, $amount, $month, $year]);
        return (int)$pdo->lastInsertId();
    }

    public static function delete(int $userId, int $id): bool
    {
        $pdo = Database::getConnection();
        $stmt = $pdo->prepare("DELETE FROM budgets WHERE id = ? AND user_id = ?");
        return $stmt->execute([$id, $userId]);
    }
}
