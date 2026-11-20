<?php
/**
 * FINORA - Category Model
 */

declare(strict_types=1);

namespace App\Models;

use App\Core\Database;
use PDO;

class Category
{
    public static function getAll(int $userId, ?string $type = null): array
    {
        $pdo = Database::getConnection();
        $sql = "SELECT * FROM categories WHERE (user_id IS NULL OR user_id = ?)";
        $params = [$userId];

        if ($type !== null) {
            $sql .= " AND type = ?";
            $params[] = $type;
        }

        $sql .= " ORDER BY is_default DESC, name ASC";

        $stmt = $pdo->prepare($sql);
        $stmt->execute($params);
        $categories = $stmt->fetchAll();

        foreach ($categories as &$cat) {
            $cat['transaction_count'] = self::getTransactionCount($userId, (int)$cat['id']);
        }

        return $categories;
    }

    public static function findById(int $userId, int $id): ?array
    {
        $pdo = Database::getConnection();
        $stmt = $pdo->prepare("SELECT * FROM categories WHERE id = ? AND (user_id IS NULL OR user_id = ?) LIMIT 1");
        $stmt->execute([$id, $userId]);
        $cat = $stmt->fetch();
        return $cat ?: null;
    }

    public static function getTransactionCount(int $userId, int $categoryId): int
    {
        $pdo = Database::getConnection();
        $stmt = $pdo->prepare("SELECT COUNT(*) FROM transactions WHERE user_id = ? AND category_id = ?");
        $stmt->execute([$userId, $categoryId]);
        return (int)$stmt->fetchColumn();
    }

    public static function create(int $userId, array $data): int
    {
        $pdo = Database::getConnection();
        $stmt = $pdo->prepare("
            INSERT INTO categories (user_id, name, type, icon, color, is_default)
            VALUES (?, ?, ?, ?, ?, 0)
        ");
        $stmt->execute([
            $userId,
            $data['name'],
            $data['type'],
            $data['icon'] ?? 'bi-tag',
            $data['color'] ?? '#3B82F6'
        ]);
        return (int)$pdo->lastInsertId();
    }

    public static function update(int $userId, int $id, array $data): array
    {
        $pdo = Database::getConnection();
        // Check if user owns this category and it is not a system default
        $category = self::findById($userId, $id);
        if (!$category) {
            return ['success' => false, 'message' => 'Category not found.'];
        }

        if ((int)$category['is_default'] === 1 || $category['user_id'] === null) {
            return ['success' => false, 'message' => 'Default system categories cannot be modified. You may create a custom category instead.'];
        }

        $stmt = $pdo->prepare("
            UPDATE categories 
            SET name = ?,
                type = ?,
                icon = ?,
                color = ?
            WHERE id = ? AND user_id = ?
        ");
        $stmt->execute([
            $data['name'],
            $data['type'],
            $data['icon'] ?? 'bi-tag',
            $data['color'] ?? '#3B82F6',
            $id,
            $userId
        ]);

        return ['success' => true, 'message' => 'Category updated successfully.'];
    }

    public static function delete(int $userId, int $id): array
    {
        $category = self::findById($userId, $id);
        if (!$category) {
            return ['success' => false, 'message' => 'Category not found.'];
        }

        if ((int)$category['is_default'] === 1 || $category['user_id'] === null) {
            return ['success' => false, 'message' => 'System default categories cannot be deleted.'];
        }

        $pdo = Database::getConnection();
        $stmt = $pdo->prepare("DELETE FROM categories WHERE id = ? AND user_id = ?");
        $deleted = $stmt->execute([$id, $userId]);

        return [
            'success' => $deleted,
            'message' => $deleted ? 'Category deleted successfully.' : 'Failed to delete category.'
        ];
    }
}
