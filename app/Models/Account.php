<?php
/**
 * FINORA - Account Model
 */

declare(strict_types=1);

namespace App\Models;

use App\Core\Database;
use PDO;

class Account
{
    public static function allForUser(int $userId, bool $includeArchived = false): array
    {
        return self::getAll($userId, $includeArchived);
    }

    public static function allForUserWithBalance(int $userId, bool $includeArchived = false): array
    {
        return self::getAll($userId, $includeArchived);
    }

    public static function getAll(int $userId, bool $includeArchived = false): array
    {
        $pdo = Database::getConnection();
        $sql = "SELECT * FROM accounts WHERE user_id = ?";
        if (!$includeArchived) {
            $sql .= " AND is_archived = 0";
        }
        $sql .= " ORDER BY is_archived ASC, name ASC";

        $stmt = $pdo->prepare($sql);
        $stmt->execute([$userId]);
        $accounts = $stmt->fetchAll();

        // Calculate dynamic real-time balance for each account
        foreach ($accounts as &$account) {
            $account['current_balance'] = self::calculateBalance($userId, (int)$account['id'], (float)$account['opening_balance']);
            $account['transaction_count'] = self::getTransactionCount($userId, (int)$account['id']);
        }

        return $accounts;
    }

    public static function findById(int $userId, int $id): ?array
    {
        $pdo = Database::getConnection();
        $stmt = $pdo->prepare("SELECT * FROM accounts WHERE id = ? AND user_id = ? LIMIT 1");
        $stmt->execute([$id, $userId]);
        $account = $stmt->fetch();

        if ($account) {
            $account['current_balance'] = self::calculateBalance($userId, (int)$account['id'], (float)$account['opening_balance']);
            $account['transaction_count'] = self::getTransactionCount($userId, (int)$account['id']);
            return $account;
        }

        return null;
    }

    public static function calculateBalance(int $userId, int $accountId, float $openingBalance): float
    {
        $pdo = Database::getConnection();

        // 1. Regular Income
        $stmtInc = $pdo->prepare("SELECT COALESCE(SUM(amount), 0) FROM transactions WHERE user_id = ? AND account_id = ? AND type = 'income'");
        $stmtInc->execute([$userId, $accountId]);
        $income = (float)$stmtInc->fetchColumn();

        // 2. Regular Expenses
        $stmtExp = $pdo->prepare("SELECT COALESCE(SUM(amount), 0) FROM transactions WHERE user_id = ? AND account_id = ? AND type = 'expense'");
        $stmtExp->execute([$userId, $accountId]);
        $expense = (float)$stmtExp->fetchColumn();

        // 3. Outgoing Transfers (Money transferred OUT of this account)
        $stmtTransOut = $pdo->prepare("SELECT COALESCE(SUM(amount), 0) FROM transactions WHERE user_id = ? AND account_id = ? AND type = 'transfer'");
        $stmtTransOut->execute([$userId, $accountId]);
        $transOut = (float)$stmtTransOut->fetchColumn();

        // 4. Incoming Transfers (Money transferred INTO this account)
        $stmtTransIn = $pdo->prepare("SELECT COALESCE(SUM(amount), 0) FROM transactions WHERE user_id = ? AND destination_account_id = ? AND type = 'transfer'");
        $stmtTransIn->execute([$userId, $accountId]);
        $transIn = (float)$stmtTransIn->fetchColumn();

        return round($openingBalance + $income - $expense - $transOut + $transIn, 2);
    }

    public static function getTotalBalance(int $userId): float
    {
        $accounts = self::getAll($userId, false);
        $total = 0.0;
        foreach ($accounts as $acc) {
            $total += $acc['current_balance'];
        }
        return round($total, 2);
    }

    public static function getTransactionCount(int $userId, int $accountId): int
    {
        $pdo = Database::getConnection();
        $stmt = $pdo->prepare("
            SELECT COUNT(*) 
            FROM transactions 
            WHERE user_id = ? AND (account_id = ? OR destination_account_id = ?)
        ");
        $stmt->execute([$userId, $accountId, $accountId]);
        return (int)$stmt->fetchColumn();
    }

    public static function create(int $userId, array $data): int
    {
        $pdo = Database::getConnection();
        $stmt = $pdo->prepare("
            INSERT INTO accounts (user_id, name, type, account_number, opening_balance, color)
            VALUES (?, ?, ?, ?, ?, ?)
        ");
        $stmt->execute([
            $userId,
            $data['name'],
            $data['type'],
            $data['account_number'] ?? null,
            (float)($data['opening_balance'] ?? 0.00),
            $data['color'] ?? '#10B981'
        ]);
        return (int)$pdo->lastInsertId();
    }

    public static function update(int $userId, int $id, array $data): bool
    {
        $pdo = Database::getConnection();
        $stmt = $pdo->prepare("
            UPDATE accounts 
            SET name = ?,
                type = ?,
                account_number = ?,
                opening_balance = ?,
                color = ?
            WHERE id = ? AND user_id = ?
        ");
        return $stmt->execute([
            $data['name'],
            $data['type'],
            $data['account_number'] ?? null,
            (float)($data['opening_balance'] ?? 0.00),
            $data['color'] ?? '#10B981',
            $id,
            $userId
        ]);
    }

    public static function archive(int $userId, int $id, bool $archive = true): bool
    {
        $pdo = Database::getConnection();
        $stmt = $pdo->prepare("UPDATE accounts SET is_archived = ? WHERE id = ? AND user_id = ?");
        return $stmt->execute([$archive ? 1 : 0, $id, $userId]);
    }

    public static function delete(int $userId, int $id): array
    {
        // Check if transactions exist
        $txCount = self::getTransactionCount($userId, $id);
        if ($txCount > 0) {
            return [
                'success' => false,
                'message' => "Cannot delete account because it contains {$txCount} transaction(s). You can archive this account instead to preserve financial history."
            ];
        }

        $pdo = Database::getConnection();
        $stmt = $pdo->prepare("DELETE FROM accounts WHERE id = ? AND user_id = ?");
        $deleted = $stmt->execute([$id, $userId]);

        return [
            'success' => $deleted,
            'message' => $deleted ? 'Account deleted successfully.' : 'Failed to delete account.'
        ];
    }
}
