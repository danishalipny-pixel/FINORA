<?php
/**
 * FINORA - Transaction Model
 */

declare(strict_types=1);

namespace App\Models;

use App\Core\Database;
use PDO;

class Transaction
{
    public static function getFiltered(int $userId, array $filters = [], int $page = 1, int $perPage = 15): array
    {
        $pdo = Database::getConnection();

        $where = ["t.user_id = ?"];
        $params = [$userId];

        // Search text
        if (!empty($filters['q'])) {
            $where[] = "(t.description LIKE ? OR t.notes LIKE ? OR t.reference_id LIKE ?)";
            $searchTerm = '%' . trim($filters['q']) . '%';
            $params[] = $searchTerm;
            $params[] = $searchTerm;
            $params[] = $searchTerm;
        }

        // Type filter
        if (!empty($filters['type'])) {
            $where[] = "t.type = ?";
            $params[] = $filters['type'];
        }

        // Category filter
        if (!empty($filters['category_id'])) {
            $where[] = "t.category_id = ?";
            $params[] = (int)$filters['category_id'];
        }

        // Account filter
        if (!empty($filters['account_id'])) {
            $where[] = "(t.account_id = ? OR t.destination_account_id = ?)";
            $params[] = (int)$filters['account_id'];
            $params[] = (int)$filters['account_id'];
        }

        // Date range
        if (!empty($filters['date_from'])) {
            $where[] = "t.transaction_date >= ?";
            $params[] = $filters['date_from'];
        }
        if (!empty($filters['date_to'])) {
            $where[] = "t.transaction_date <= ?";
            $params[] = $filters['date_to'];
        }

        $whereClause = implode(" AND ", $where);

        // Count total matching
        $countSql = "SELECT COUNT(*) FROM transactions t WHERE {$whereClause}";
        $stmtCount = $pdo->prepare($countSql);
        $stmtCount->execute($params);
        $total = (int)$stmtCount->fetchColumn();

        // Sorting
        $sort = $filters['sort'] ?? 'date_desc';
        $orderBy = match ($sort) {
            'date_asc' => "t.transaction_date ASC, t.id ASC",
            'amount_desc' => "t.amount DESC",
            'amount_asc' => "t.amount ASC",
            default => "t.transaction_date DESC, t.id DESC",
        };

        // Pagination
        $offset = max(0, ($page - 1) * $perPage);
        $totalPages = (int)ceil($total / $perPage);

        $sql = "
            SELECT 
                t.*,
                a.name AS account_name,
                a.type AS account_type,
                a.color AS account_color,
                da.name AS destination_account_name,
                c.name AS category_name,
                c.icon AS category_icon,
                c.color AS category_color
            FROM transactions t
            LEFT JOIN accounts a ON t.account_id = a.id
            LEFT JOIN accounts da ON t.destination_account_id = da.id
            LEFT JOIN categories c ON t.category_id = c.id
            WHERE {$whereClause}
            ORDER BY {$orderBy}
            LIMIT {$perPage} OFFSET {$offset}
        ";

        $stmt = $pdo->prepare($sql);
        $stmt->execute($params);
        $items = $stmt->fetchAll();

        return [
            'data' => $items,
            'total' => $total,
            'per_page' => $perPage,
            'current_page' => $page,
            'last_page' => max(1, $totalPages),
        ];
    }

    public static function findById(int $userId, int $id): ?array
    {
        $pdo = Database::getConnection();
        $stmt = $pdo->prepare("
            SELECT 
                t.*,
                a.name AS account_name,
                da.name AS destination_account_name,
                c.name AS category_name
            FROM transactions t
            LEFT JOIN accounts a ON t.account_id = a.id
            LEFT JOIN accounts da ON t.destination_account_id = da.id
            LEFT JOIN categories c ON t.category_id = c.id
            WHERE t.id = ? AND t.user_id = ?
            LIMIT 1
        ");
        $stmt->execute([$id, $userId]);
        $row = $stmt->fetch();
        return $row ?: null;
    }

    public static function create(int $userId, array $data): int
    {
        $pdo = Database::getConnection();
        $pdo->beginTransaction();

        try {
            $stmt = $pdo->prepare("
                INSERT INTO transactions (
                    user_id, account_id, category_id, type, amount,
                    transaction_date, payment_method, description, notes,
                    reference_id, destination_account_id
                ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
            ");

            $catId = !empty($data['category_id']) ? (int)$data['category_id'] : null;
            $destAccId = (!empty($data['destination_account_id']) && $data['type'] === 'transfer')
                ? (int)$data['destination_account_id']
                : null;

            $stmt->execute([
                $userId,
                (int)$data['account_id'],
                $catId,
                $data['type'],
                (float)$data['amount'],
                $data['transaction_date'],
                $data['payment_method'] ?? 'Cash',
                $data['description'],
                $data['notes'] ?? null,
                $data['reference_id'] ?? null,
                $destAccId,
            ]);

            $insertId = (int)$pdo->lastInsertId();
            $pdo->commit();

            return $insertId;
        } catch (\Throwable $e) {
            $pdo->rollBack();
            throw $e;
        }
    }

    public static function update(int $userId, int $id, array $data): bool
    {
        $pdo = Database::getConnection();
        $pdo->beginTransaction();

        try {
            $catId = !empty($data['category_id']) ? (int)$data['category_id'] : null;
            $destAccId = (!empty($data['destination_account_id']) && $data['type'] === 'transfer')
                ? (int)$data['destination_account_id']
                : null;

            $stmt = $pdo->prepare("
                UPDATE transactions 
                SET account_id = ?,
                    category_id = ?,
                    type = ?,
                    amount = ?,
                    transaction_date = ?,
                    payment_method = ?,
                    description = ?,
                    notes = ?,
                    reference_id = ?,
                    destination_account_id = ?
                WHERE id = ? AND user_id = ?
            ");

            $updated = $stmt->execute([
                (int)$data['account_id'],
                $catId,
                $data['type'],
                (float)$data['amount'],
                $data['transaction_date'],
                $data['payment_method'] ?? 'Cash',
                $data['description'],
                $data['notes'] ?? null,
                $data['reference_id'] ?? null,
                $destAccId,
                $id,
                $userId
            ]);

            $pdo->commit();
            return $updated;
        } catch (\Throwable $e) {
            $pdo->rollBack();
            throw $e;
        }
    }

    public static function delete(int $userId, int $id): bool
    {
        $pdo = Database::getConnection();
        $stmt = $pdo->prepare("DELETE FROM transactions WHERE id = ? AND user_id = ?");
        return $stmt->execute([$id, $userId]);
    }

    public static function getRecent(int $userId, int $limit = 5): array
    {
        $pdo = Database::getConnection();
        $stmt = $pdo->prepare("
            SELECT 
                t.*,
                a.name AS account_name,
                a.color AS account_color,
                c.name AS category_name,
                c.icon AS category_icon,
                c.color AS category_color
            FROM transactions t
            LEFT JOIN accounts a ON t.account_id = a.id
            LEFT JOIN categories c ON t.category_id = c.id
            WHERE t.user_id = ?
            ORDER BY t.transaction_date DESC, t.id DESC
            LIMIT ?
        ");
        $stmt->bindValue(1, $userId, PDO::PARAM_INT);
        $stmt->bindValue(2, $limit, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll();
    }

    public static function getMonthlySummary(int $userId, int $year, int $month): array
    {
        $pdo = Database::getConnection();

        $startDate = sprintf('%04d-%02d-01', $year, $month);
        $endDate = date('Y-m-t', strtotime($startDate));

        $stmt = $pdo->prepare("
            SELECT 
                COALESCE(SUM(CASE WHEN type = 'income' THEN amount ELSE 0 END), 0) AS total_income,
                COALESCE(SUM(CASE WHEN type = 'expense' THEN amount ELSE 0 END), 0) AS total_expense
            FROM transactions
            WHERE user_id = ? AND transaction_date BETWEEN ? AND ?
        ");
        $stmt->execute([$userId, $startDate, $endDate]);
        $res = $stmt->fetch();

        $income = (float)$res['total_income'];
        $expense = (float)$res['total_expense'];
        $savings = $income - $expense;
        $savingsRate = $income > 0 ? round(($savings / $income) * 100, 1) : 0.0;

        return [
            'income' => $income,
            'expense' => $expense,
            'savings' => $savings,
            'savings_rate' => max(0, $savingsRate),
        ];
    }

    public static function getCategoryExpenses(int $userId, int $year, int $month): array
    {
        $pdo = Database::getConnection();
        $startDate = sprintf('%04d-%02d-01', $year, $month);
        $endDate = date('Y-m-t', strtotime($startDate));

        $stmt = $pdo->prepare("
            SELECT 
                COALESCE(c.name, 'Uncategorized') AS category_name,
                COALESCE(c.color, '#64748B') AS category_color,
                COALESCE(c.icon, 'bi-tag') AS category_icon,
                SUM(t.amount) AS total_amount,
                COUNT(t.id) AS transaction_count
            FROM transactions t
            LEFT JOIN categories c ON t.category_id = c.id
            WHERE t.user_id = ? AND t.type = 'expense' AND t.transaction_date BETWEEN ? AND ?
            GROUP BY t.category_id, c.name, c.color, c.icon
            ORDER BY total_amount DESC
        ");
        $stmt->execute([$userId, $startDate, $endDate]);
        return $stmt->fetchAll();
    }

    public static function getMonthlyCashflow(int $userId, int $months = 6): array
    {
        $pdo = Database::getConnection();
        $results = [];

        for ($i = $months - 1; $i >= 0; $i--) {
            $monthTimestamp = strtotime("-{$i} months");
            $year = (int)date('Y', $monthTimestamp);
            $month = (int)date('m', $monthTimestamp);
            $monthLabel = date('M Y', $monthTimestamp);

            $summary = self::getMonthlySummary($userId, $year, $month);

            $results[] = [
                'year' => $year,
                'month' => $month,
                'label' => $monthLabel,
                'income' => $summary['income'],
                'expense' => $summary['expense'],
                'savings' => $summary['savings'],
            ];
        }

        return $results;
    }

    public static function getAllForExport(int $userId, array $filters = []): array
    {
        // Re-use filter logic with large limit for CSV or PDF
        $res = self::getFiltered($userId, $filters, 1, 10000);
        return $res['data'];
    }
}
