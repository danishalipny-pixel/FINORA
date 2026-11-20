<?php
/**
 * FINORA - Default Categories Seeder
 *
 * Seeds baseline system-level income and expense categories.
 *
 * @package Finora\Database\Seeders
 * @author Danish Ali <https://github.com/danishalipny-pixel>
 */

declare(strict_types=1);

namespace Database\Seeders;

use PDO;

class CategorySeeder implements SeederInterface
{
    public function run(PDO $pdo): void
    {
        echo " -> Seeding default categories... ";

        $defaults = [
            // Income Categories
            ['name' => 'Salary & Wages', 'type' => 'income', 'icon' => 'bi-cash-stack', 'color' => '#10B981'],
            ['name' => 'Freelance & Consulting', 'type' => 'income', 'icon' => 'bi-laptop', 'color' => '#3B82F6'],
            ['name' => 'Business & Investments', 'type' => 'income', 'icon' => 'bi-graph-up-arrow', 'color' => '#6366F1'],
            ['name' => 'Rental Income', 'type' => 'income', 'icon' => 'bi-house-door', 'color' => '#8B5CF6'],
            ['name' => 'Gifts & Grants', 'type' => 'income', 'icon' => 'bi-gift', 'color' => '#EC4899'],
            ['name' => 'Other Income', 'type' => 'income', 'icon' => 'bi-wallet2', 'color' => '#64748B'],

            // Expense Categories
            ['name' => 'Housing & Rent', 'type' => 'expense', 'icon' => 'bi-house', 'color' => '#EF4444'],
            ['name' => 'Groceries & Food', 'type' => 'expense', 'icon' => 'bi-cart', 'color' => '#F59E0B'],
            ['name' => 'Utilities & Bills', 'type' => 'expense', 'icon' => 'bi-lightning-charge', 'color' => '#F97316'],
            ['name' => 'Transportation & Fuel', 'type' => 'expense', 'icon' => 'bi-fuel-pump', 'color' => '#06B6D4'],
            ['name' => 'Healthcare & Medical', 'type' => 'expense', 'icon' => 'bi-heart-pulse', 'color' => '#E11D48'],
            ['name' => 'Education & Books', 'type' => 'expense', 'icon' => 'bi-book', 'color' => '#8B5CF6'],
            ['name' => 'Dining & Entertainment', 'type' => 'expense', 'icon' => 'bi-cup-straw', 'color' => '#EC4899'],
            ['name' => 'Shopping & Personal', 'type' => 'expense', 'icon' => 'bi-bag', 'color' => '#14B8A6'],
            ['name' => 'Family & Children', 'type' => 'expense', 'icon' => 'bi-people', 'color' => '#0EA5E9'],
            ['name' => 'Zakat & Charity', 'type' => 'expense', 'icon' => 'bi-suit-heart', 'color' => '#10B981'],
            ['name' => 'Insurance & Taxes', 'type' => 'expense', 'icon' => 'bi-shield-check', 'color' => '#475569'],
            ['name' => 'Other Expenses', 'type' => 'expense', 'icon' => 'bi-three-dots', 'color' => '#64748B'],
        ];

        $checkStmt = $pdo->prepare("SELECT id FROM categories WHERE user_id IS NULL AND name = ?");
        $insertStmt = $pdo->prepare(
            "INSERT INTO categories (user_id, name, type, icon, color, is_default) VALUES (NULL, ?, ?, ?, ?, 1)"
        );

        $inserted = 0;
        foreach ($defaults as $cat) {
            $checkStmt->execute([$cat['name']]);
            if (!$checkStmt->fetch()) {
                $insertStmt->execute([$cat['name'], $cat['type'], $cat['icon'], $cat['color']]);
                $inserted++;
            }
        }

        echo "[DONE] ({$inserted} added, total " . count($defaults) . " active)\n";
    }
}
