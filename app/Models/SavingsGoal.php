<?php
/**
 * FINORA - Savings Goal Model
 */

declare(strict_types=1);

namespace App\Models;

use App\Core\Database;
use PDO;

class SavingsGoal
{
    public static function getAll(int $userId): array
    {
        $pdo = Database::getConnection();
        $stmt = $pdo->prepare("
            SELECT 
                g.*,
                a.name AS account_name,
                (SELECT COUNT(*) FROM savings_contributions c WHERE c.goal_id = g.id) AS contributions_count
            FROM savings_goals g
            LEFT JOIN accounts a ON g.account_id = a.id
            WHERE g.user_id = ?
            ORDER BY 
                CASE WHEN g.status = 'in_progress' THEN 1 WHEN g.status = 'paused' THEN 2 ELSE 3 END,
                g.target_date ASC
        ");
        $stmt->execute([$userId]);
        $goals = $stmt->fetchAll();

        $today = new \DateTime('today');

        foreach ($goals as &$g) {
            $target = (float)$g['target_amount'];
            $current = (float)$g['current_amount'];
            $pct = $target > 0 ? min(100.0, round(($current / $target) * 100, 1)) : 0.0;
            $g['percentage'] = $pct;

            $targetDate = new \DateTime($g['target_date']);
            $diff = $today->diff($targetDate);
            $g['days_remaining'] = $targetDate >= $today ? (int)$diff->format('%a') : -(int)$diff->format('%a');
        }

        return $goals;
    }

    public static function findById(int $userId, int $id): ?array
    {
        $pdo = Database::getConnection();
        $stmt = $pdo->prepare("
            SELECT 
                g.*,
                a.name AS account_name
            FROM savings_goals g
            LEFT JOIN accounts a ON g.account_id = a.id
            WHERE g.id = ? AND g.user_id = ?
            LIMIT 1
        ");
        $stmt->execute([$id, $userId]);
        $goal = $stmt->fetch();

        if ($goal) {
            $target = (float)$goal['target_amount'];
            $current = (float)$goal['current_amount'];
            $goal['percentage'] = $target > 0 ? min(100.0, round(($current / $target) * 100, 1)) : 0.0;

            $today = new \DateTime('today');
            $targetDate = new \DateTime($goal['target_date']);
            $diff = $today->diff($targetDate);
            $goal['days_remaining'] = $targetDate >= $today ? (int)$diff->format('%a') : -(int)$diff->format('%a');

            return $goal;
        }

        return null;
    }

    public static function create(int $userId, array $data): int
    {
        $pdo = Database::getConnection();
        $stmt = $pdo->prepare("
            INSERT INTO savings_goals (
                user_id, account_id, name, target_amount, current_amount,
                target_date, color, status, notes
            ) VALUES (?, ?, ?, ?, 0.00, ?, ?, 'in_progress', ?)
        ");

        $accId = !empty($data['account_id']) ? (int)$data['account_id'] : null;

        $stmt->execute([
            $userId,
            $accId,
            $data['name'],
            (float)$data['target_amount'],
            $data['target_date'],
            $data['color'] ?? '#10B981',
            $data['notes'] ?? null
        ]);

        return (int)$pdo->lastInsertId();
    }

    public static function update(int $userId, int $id, array $data): bool
    {
        $pdo = Database::getConnection();
        $accId = !empty($data['account_id']) ? (int)$data['account_id'] : null;

        $stmt = $pdo->prepare("
            UPDATE savings_goals 
            SET account_id = ?,
                name = ?,
                target_amount = ?,
                target_date = ?,
                color = ?,
                status = ?,
                notes = ?
            WHERE id = ? AND user_id = ?
        ");

        return $stmt->execute([
            $accId,
            $data['name'],
            (float)$data['target_amount'],
            $data['target_date'],
            $data['color'] ?? '#10B981',
            $data['status'] ?? 'in_progress',
            $data['notes'] ?? null,
            $id,
            $userId
        ]);
    }

    public static function delete(int $userId, int $id): bool
    {
        $pdo = Database::getConnection();
        $stmt = $pdo->prepare("DELETE FROM savings_goals WHERE id = ? AND user_id = ?");
        return $stmt->execute([$id, $userId]);
    }

    public static function recalculateCurrentAmount(int $userId, int $goalId): void
    {
        $pdo = Database::getConnection();

        // Sum contributions
        $stmtSum = $pdo->prepare("SELECT COALESCE(SUM(amount), 0) FROM savings_contributions WHERE goal_id = ? AND user_id = ?");
        $stmtSum->execute([$goalId, $userId]);
        $totalContributions = (float)$stmtSum->fetchColumn();

        // Get goal details
        $stmtGoal = $pdo->prepare("SELECT target_amount, status, name FROM savings_goals WHERE id = ? AND user_id = ?");
        $stmtGoal->execute([$goalId, $userId]);
        $goal = $stmtGoal->fetch();

        if ($goal) {
            $target = (float)$goal['target_amount'];
            $newStatus = $goal['status'];

            // Auto-mark completed if target reached
            if ($totalContributions >= $target && $goal['status'] === 'in_progress') {
                $newStatus = 'completed';
                // Trigger notification
                Notification::create(
                    $userId,
                    'goal_milestone',
                    'Savings Goal Completed!',
                    "Awesome achievement! You have fully funded your goal '{$goal['name']}' with " . currency($totalContributions) . ".",
                    '/savings'
                );
            } elseif ($totalContributions < $target && $goal['status'] === 'completed') {
                $newStatus = 'in_progress';
            }

            $stmtUpd = $pdo->prepare("UPDATE savings_goals SET current_amount = ?, status = ? WHERE id = ? AND user_id = ?");
            $stmtUpd->execute([$totalContributions, $newStatus, $goalId, $userId]);
        }
    }

    public static function getTotalSaved(int $userId): float
    {
        $pdo = Database::getConnection();
        $stmt = $pdo->prepare("SELECT COALESCE(SUM(current_amount), 0) FROM savings_goals WHERE user_id = ?");
        $stmt->execute([$userId]);
        return (float)$stmt->fetchColumn();
    }
}
