<?php
/**
 * FINORA - Savings Contribution Model
 */

declare(strict_types=1);

namespace App\Models;

use App\Core\Database;
use PDO;

class SavingsContribution
{
    public static function getByGoal(int $userId, int $goalId): array
    {
        $pdo = Database::getConnection();
        $stmt = $pdo->prepare("
            SELECT 
                c.*,
                a.name AS account_name
            FROM savings_contributions c
            LEFT JOIN accounts a ON c.account_id = a.id
            WHERE c.goal_id = ? AND c.user_id = ?
            ORDER BY c.contribution_date DESC, c.id DESC
        ");
        $stmt->execute([$goalId, $userId]);
        return $stmt->fetchAll();
    }

    public static function add(int $userId, int $goalId, array $data): int
    {
        $pdo = Database::getConnection();
        $pdo->beginTransaction();

        try {
            $stmt = $pdo->prepare("
                INSERT INTO savings_contributions (goal_id, user_id, account_id, amount, contribution_date, notes)
                VALUES (?, ?, ?, ?, ?, ?)
            ");

            $accId = !empty($data['account_id']) ? (int)$data['account_id'] : null;

            $stmt->execute([
                $goalId,
                $userId,
                $accId,
                (float)$data['amount'],
                $data['contribution_date'] ?? date('Y-m-d'),
                $data['notes'] ?? null
            ]);

            $contributionId = (int)$pdo->lastInsertId();

            $pdo->commit();

            // Recalculate goal status
            SavingsGoal::recalculateCurrentAmount($userId, $goalId);

            return $contributionId;
        } catch (\Throwable $e) {
            $pdo->rollBack();
            throw $e;
        }
    }

    public static function delete(int $userId, int $id): bool
    {
        $pdo = Database::getConnection();

        // Get goal ID first
        $stmt = $pdo->prepare("SELECT goal_id FROM savings_contributions WHERE id = ? AND user_id = ?");
        $stmt->execute([$id, $userId]);
        $goalId = $stmt->fetchColumn();

        if (!$goalId) {
            return false;
        }

        $stmtDel = $pdo->prepare("DELETE FROM savings_contributions WHERE id = ? AND user_id = ?");
        $deleted = $stmtDel->execute([$id, $userId]);

        if ($deleted) {
            SavingsGoal::recalculateCurrentAmount($userId, (int)$goalId);
        }

        return $deleted;
    }
}
