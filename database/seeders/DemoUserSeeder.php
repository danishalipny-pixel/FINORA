<?php
/**
 * FINORA - Realistic Demo User Seeder
 *
 * Seeds a full portfolio profile for 'demo@finora.test' with realistic PKR transactions.
 *
 * @package Finora\Database\Seeders
 * @author Danish Ali <https://github.com/danishalipny-pixel>
 */

declare(strict_types=1);

namespace Database\Seeders;

use PDO;

class DemoUserSeeder implements SeederInterface
{
    public function run(PDO $pdo): void
    {
        echo " -> Seeding demo user profile (Zain Ahmed / demo@finora.test)... ";

        $demoEmail = 'demo@finora.test';
        $demoPassword = 'FinoraDemo123!';
        $hashedPassword = password_hash($demoPassword, PASSWORD_BCRYPT, ['cost' => 12]);

        // 1. Clean existing demo user for idempotency in proper FK order
        $stmt = $pdo->prepare("SELECT id FROM users WHERE email = ?");
        $stmt->execute([$demoEmail]);
        $existing = $stmt->fetch();
        if ($existing) {
            $uId = (int)$existing['id'];
            $pdo->prepare("DELETE FROM transactions WHERE user_id = ?")->execute([$uId]);
            $pdo->prepare("DELETE FROM savings_contributions WHERE user_id = ?")->execute([$uId]);
            $pdo->prepare("DELETE FROM savings_goals WHERE user_id = ?")->execute([$uId]);
            $pdo->prepare("DELETE FROM budgets WHERE user_id = ?")->execute([$uId]);
            $pdo->prepare("DELETE FROM accounts WHERE user_id = ?")->execute([$uId]);
            $pdo->prepare("DELETE FROM notifications WHERE user_id = ?")->execute([$uId]);
            $pdo->prepare("DELETE FROM user_settings WHERE user_id = ?")->execute([$uId]);
            $pdo->prepare("DELETE FROM users WHERE id = ?")->execute([$uId]);
        }

        // 2. Insert User
        $stmt = $pdo->prepare("INSERT INTO users (name, email, password, role, status) VALUES (?, ?, ?, 'user', 'active')");
        $stmt->execute(['Zain Ahmed', $demoEmail, $hashedPassword]);
        $userId = (int)$pdo->lastInsertId();

        // 3. User Settings
        $stmt = $pdo->prepare("INSERT INTO user_settings (user_id, currency, currency_symbol, date_format, theme, notify_budget_warning, notify_goal_reached, notify_monthly_summary) VALUES (?, 'PKR', 'Rs.', 'd M Y', 'light', 1, 1, 1)");
        $stmt->execute([$userId]);

        // 4. Accounts
        $accountsData = [
            ['name' => 'Meezan Bank - Main Checking', 'type' => 'bank', 'account_number' => '0101-0104882910', 'opening_balance' => 125000.00, 'color' => '#10B981'],
            ['name' => 'HBL - Roshan Digital Savings', 'type' => 'savings', 'account_number' => '5012-9920148821', 'opening_balance' => 380000.00, 'color' => '#3B82F6'],
            ['name' => 'Physical Cash Wallet', 'type' => 'cash', 'account_number' => null, 'opening_balance' => 25000.00, 'color' => '#F59E0B'],
            ['name' => 'Standard Chartered Reserve', 'type' => 'bank', 'account_number' => '03-9128340-01', 'opening_balance' => 95000.00, 'color' => '#8B5CF6'],
        ];

        $accountIds = [];
        $stmtAcc = $pdo->prepare("INSERT INTO accounts (user_id, name, type, account_number, opening_balance, color) VALUES (?, ?, ?, ?, ?, ?)");
        foreach ($accountsData as $acc) {
            $stmtAcc->execute([$userId, $acc['name'], $acc['type'], $acc['account_number'], $acc['opening_balance'], $acc['color']]);
            $accountIds[$acc['name']] = (int)$pdo->lastInsertId();
        }

        // 5. Fetch Categories
        $stmtCat = $pdo->prepare("SELECT id, name FROM categories WHERE user_id IS NULL OR user_id = ?");
        $stmtCat->execute([$userId]);
        $categories = [];
        foreach ($stmtCat->fetchAll() as $cat) {
            $categories[$cat['name']] = (int)$cat['id'];
        }

        // 6. Transactions
        $transactions = [
            // Current Month (November 2026)
            ['date' => '2026-11-01', 'acc' => 'Meezan Bank - Main Checking', 'cat' => 'Salary & Wages', 'type' => 'income', 'amount' => 260000.00, 'method' => 'Bank Transfer', 'desc' => 'Monthly Professional Salary - Systems Ltd'],
            ['date' => '2026-11-02', 'acc' => 'Meezan Bank - Main Checking', 'cat' => 'Housing & Rent', 'type' => 'expense', 'amount' => 65000.00, 'method' => 'Bank Transfer', 'desc' => 'Apartment Monthly Rent - DHA Phase 6'],
            ['date' => '2026-11-03', 'acc' => 'Meezan Bank - Main Checking', 'cat' => 'Utilities & Bills', 'type' => 'expense', 'amount' => 18450.00, 'method' => 'Online', 'desc' => 'K-Electric Bill Payment'],
            ['date' => '2026-11-04', 'acc' => 'Physical Cash Wallet', 'cat' => 'Groceries & Food', 'type' => 'expense', 'amount' => 8400.00, 'method' => 'Cash', 'desc' => 'Fresh Mart Weekly Groceries'],
            ['date' => '2026-11-06', 'acc' => 'Meezan Bank - Main Checking', 'cat' => 'Transportation & Fuel', 'type' => 'expense', 'amount' => 6500.00, 'method' => 'Debit Card', 'desc' => 'Total PARCO Fuel Refill'],
            ['date' => '2026-11-08', 'acc' => 'Standard Chartered Reserve', 'cat' => 'Freelance & Consulting', 'type' => 'income', 'amount' => 78000.00, 'method' => 'Bank Transfer', 'desc' => 'Fintech UI Consultation - Client UAE'],
            ['date' => '2026-11-10', 'acc' => 'Meezan Bank - Main Checking', 'cat' => 'Dining & Entertainment', 'type' => 'expense', 'amount' => 6200.00, 'method' => 'Debit Card', 'desc' => 'Family Dinner at Kolachi Restaurant'],
            ['date' => '2026-11-12', 'acc' => 'Physical Cash Wallet', 'cat' => 'Groceries & Food', 'type' => 'expense', 'amount' => 9100.00, 'method' => 'Cash', 'desc' => 'Imtiaz Super Market Household Supplies'],
            ['date' => '2026-11-14', 'acc' => 'Meezan Bank - Main Checking', 'cat' => 'Healthcare & Medical', 'type' => 'expense', 'amount' => 4500.00, 'method' => 'Debit Card', 'desc' => 'Aga Khan Hospital Checkup & Prescriptions'],
            ['date' => '2026-11-15', 'acc' => 'Meezan Bank - Main Checking', 'cat' => 'Utilities & Bills', 'type' => 'expense', 'amount' => 4200.00, 'method' => 'Online', 'desc' => 'StormFiber Gigabit Internet Bill'],
            ['date' => '2026-11-16', 'acc' => 'Meezan Bank - Main Checking', 'cat' => 'Zakat & Charity', 'type' => 'expense', 'amount' => 10000.00, 'method' => 'Bank Transfer', 'desc' => 'Monthly Donation to Indus Hospital'],
            ['date' => '2026-11-17', 'acc' => 'Physical Cash Wallet', 'cat' => 'Transportation & Fuel', 'type' => 'expense', 'amount' => 3800.00, 'method' => 'Cash', 'desc' => 'Careem / Uber Ride Credits'],

            // October 2026
            ['date' => '2026-10-01', 'acc' => 'Meezan Bank - Main Checking', 'cat' => 'Salary & Wages', 'type' => 'income', 'amount' => 260000.00, 'method' => 'Bank Transfer', 'desc' => 'Monthly Professional Salary - Systems Ltd'],
            ['date' => '2026-10-02', 'acc' => 'Meezan Bank - Main Checking', 'cat' => 'Housing & Rent', 'type' => 'expense', 'amount' => 65000.00, 'method' => 'Bank Transfer', 'desc' => 'Apartment Monthly Rent - DHA Phase 6'],
            ['date' => '2026-10-05', 'acc' => 'Meezan Bank - Main Checking', 'cat' => 'Utilities & Bills', 'type' => 'expense', 'amount' => 21800.00, 'method' => 'Online', 'desc' => 'K-Electric + SSGC Bills'],
            ['date' => '2026-10-08', 'acc' => 'Meezan Bank - Main Checking', 'cat' => 'Groceries & Food', 'type' => 'expense', 'amount' => 24500.00, 'method' => 'Debit Card', 'desc' => 'Carrefour Monthly Grocery Bulk'],
            ['date' => '2026-10-12', 'acc' => 'Standard Chartered Reserve', 'cat' => 'Freelance & Consulting', 'type' => 'income', 'amount' => 65000.00, 'method' => 'Bank Transfer', 'desc' => 'Web App Code Audit - UK Client'],
            ['date' => '2026-10-15', 'acc' => 'Meezan Bank - Main Checking', 'cat' => 'Transportation & Fuel', 'type' => 'expense', 'amount' => 14500.00, 'method' => 'Debit Card', 'desc' => 'Monthly Car Maintenance & Fuel'],
            ['date' => '2026-10-20', 'acc' => 'Meezan Bank - Main Checking', 'cat' => 'Shopping & Personal', 'type' => 'expense', 'amount' => 11200.00, 'method' => 'Debit Card', 'desc' => 'Outfitter Winter Wardrobe'],
            ['date' => '2026-10-25', 'acc' => 'Meezan Bank - Main Checking', 'cat' => 'Dining & Entertainment', 'type' => 'expense', 'amount' => 7800.00, 'method' => 'Debit Card', 'desc' => 'Weekend Dinner & Cinema'],

            // September 2026
            ['date' => '2026-09-01', 'acc' => 'Meezan Bank - Main Checking', 'cat' => 'Salary & Wages', 'type' => 'income', 'amount' => 260000.00, 'method' => 'Bank Transfer', 'desc' => 'Monthly Professional Salary - Systems Ltd'],
            ['date' => '2026-09-02', 'acc' => 'Meezan Bank - Main Checking', 'cat' => 'Housing & Rent', 'type' => 'expense', 'amount' => 65000.00, 'method' => 'Bank Transfer', 'desc' => 'Apartment Monthly Rent - DHA Phase 6'],
            ['date' => '2026-09-05', 'acc' => 'Meezan Bank - Main Checking', 'cat' => 'Utilities & Bills', 'type' => 'expense', 'amount' => 24600.00, 'method' => 'Online', 'desc' => 'Summer Peak Electric Bill'],
            ['date' => '2026-09-10', 'acc' => 'Meezan Bank - Main Checking', 'cat' => 'Groceries & Food', 'type' => 'expense', 'amount' => 22800.00, 'method' => 'Debit Card', 'desc' => 'Naheed Supermarket Essentials'],
            ['date' => '2026-09-18', 'acc' => 'Standard Chartered Reserve', 'cat' => 'Business & Investments', 'type' => 'income', 'amount' => 45000.00, 'method' => 'Bank Transfer', 'desc' => 'Mutual Fund Dividend Payout - Al-Meezan'],
            ['date' => '2026-09-22', 'acc' => 'Meezan Bank - Main Checking', 'cat' => 'Education & Books', 'type' => 'expense', 'amount' => 18000.00, 'method' => 'Online', 'desc' => 'Cloud Architecture & Kubernetes Certification'],

            // August 2026
            ['date' => '2026-08-01', 'acc' => 'Meezan Bank - Main Checking', 'cat' => 'Salary & Wages', 'type' => 'income', 'amount' => 260000.00, 'method' => 'Bank Transfer', 'desc' => 'Monthly Professional Salary - Systems Ltd'],
            ['date' => '2026-08-03', 'acc' => 'Meezan Bank - Main Checking', 'cat' => 'Housing & Rent', 'type' => 'expense', 'amount' => 65000.00, 'method' => 'Bank Transfer', 'desc' => 'Apartment Monthly Rent - DHA Phase 6'],
            ['date' => '2026-08-12', 'acc' => 'Standard Chartered Reserve', 'cat' => 'Freelance & Consulting', 'type' => 'income', 'amount' => 82000.00, 'method' => 'Bank Transfer', 'desc' => 'Enterprise API Architecture Engagement'],
            ['date' => '2026-08-15', 'acc' => 'Meezan Bank - Main Checking', 'cat' => 'Shopping & Personal', 'type' => 'expense', 'amount' => 16500.00, 'method' => 'Debit Card', 'desc' => 'Electronics & Gadgets Accessories'],

            // July 2026
            ['date' => '2026-07-01', 'acc' => 'Meezan Bank - Main Checking', 'cat' => 'Salary & Wages', 'type' => 'income', 'amount' => 260000.00, 'method' => 'Bank Transfer', 'desc' => 'Monthly Professional Salary - Systems Ltd'],
            ['date' => '2026-07-02', 'acc' => 'Meezan Bank - Main Checking', 'cat' => 'Housing & Rent', 'type' => 'expense', 'amount' => 65000.00, 'method' => 'Bank Transfer', 'desc' => 'Apartment Monthly Rent - DHA Phase 6'],
            ['date' => '2026-07-07', 'acc' => 'Meezan Bank - Main Checking', 'cat' => 'Zakat & Charity', 'type' => 'expense', 'amount' => 45000.00, 'method' => 'Bank Transfer', 'desc' => 'Annual Wealth Zakat Allocation'],
            ['date' => '2026-07-15', 'acc' => 'Standard Chartered Reserve', 'cat' => 'Freelance & Consulting', 'type' => 'income', 'amount' => 95000.00, 'method' => 'Bank Transfer', 'desc' => 'Payment Gateway Integration Project'],
            ['date' => '2026-07-28', 'acc' => 'Meezan Bank - Main Checking', 'cat' => 'Dining & Entertainment', 'type' => 'expense', 'amount' => 12500.00, 'method' => 'Debit Card', 'desc' => 'Team Lunch & Celebrations'],
        ];

        $stmtTx = $pdo->prepare("INSERT INTO transactions (user_id, account_id, category_id, type, amount, transaction_date, payment_method, description) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
        foreach ($transactions as $tx) {
            $accId = $accountIds[$tx['acc']] ?? reset($accountIds);
            $catId = $categories[$tx['cat']] ?? null;
            $stmtTx->execute([$userId, $accId, $catId, $tx['type'], $tx['amount'], $tx['date'], $tx['method'], $tx['desc']]);
        }

        // 7. Budgets for Current Month (November 2026)
        $budgets = [
            ['cat' => 'Housing & Rent', 'amount' => 70000.00],
            ['cat' => 'Groceries & Food', 'amount' => 30000.00],
            ['cat' => 'Utilities & Bills', 'amount' => 28000.00],
            ['cat' => 'Transportation & Fuel', 'amount' => 18000.00],
            ['cat' => 'Dining & Entertainment', 'amount' => 12000.00],
            ['cat' => 'Healthcare & Medical', 'amount' => 15000.00],
            ['cat' => 'Zakat & Charity', 'amount' => 15000.00],
        ];

        $stmtBg = $pdo->prepare("INSERT INTO budgets (user_id, category_id, amount, month, year) VALUES (?, ?, ?, 11, 2026)");
        foreach ($budgets as $b) {
            if (isset($categories[$b['cat']])) {
                $stmtBg->execute([$userId, $categories[$b['cat']], $b['amount']]);
            }
        }

        // 8. Savings Goals
        $savingsGoals = [
            ['name' => 'Emergency Fund (6 Months Cushion)', 'target' => 500000.00, 'current' => 450000.00, 'date' => '2026-12-31', 'color' => '#10B981', 'acc' => 'HBL - Roshan Digital Savings'],
            ['name' => 'Umrah Sacred Journey', 'target' => 600000.00, 'current' => 380000.00, 'date' => '2027-04-15', 'color' => '#3B82F6', 'acc' => 'HBL - Roshan Digital Savings'],
            ['name' => 'Sedan Vehicle Down Payment', 'target' => 1500000.00, 'current' => 380000.00, 'date' => '2027-09-30', 'color' => '#F59E0B', 'acc' => 'Standard Chartered Reserve'],
            ['name' => 'Apple MacBook M3 Max & 4K Monitor', 'target' => 420000.00, 'current' => 420000.00, 'date' => '2026-10-30', 'color' => '#8B5CF6', 'acc' => 'Meezan Bank - Main Checking'],
        ];

        $stmtGoal = $pdo->prepare("INSERT INTO savings_goals (user_id, account_id, name, target_amount, current_amount, target_date, color, status) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
        $goalIds = [];
        foreach ($savingsGoals as $g) {
            $status = ($g['current'] >= $g['target']) ? 'completed' : 'in_progress';
            $accId = $accountIds[$g['acc']] ?? null;
            $stmtGoal->execute([$userId, $accId, $g['name'], $g['target'], $g['current'], $g['date'], $g['color'], $status]);
            $goalIds[$g['name']] = (int)$pdo->lastInsertId();
        }

        // 9. Savings Contributions
        $stmtContrib = $pdo->prepare("INSERT INTO savings_contributions (goal_id, user_id, account_id, amount, contribution_date, notes) VALUES (?, ?, ?, ?, ?, ?)");
        if (isset($goalIds['Emergency Fund (6 Months Cushion)'])) {
            $gId = $goalIds['Emergency Fund (6 Months Cushion)'];
            $stmtContrib->execute([$gId, $userId, $accountIds['HBL - Roshan Digital Savings'], 50000.00, '2026-08-05', 'Monthly emergency reserve allocation']);
            $stmtContrib->execute([$gId, $userId, $accountIds['HBL - Roshan Digital Savings'], 50000.00, '2026-09-05', 'Monthly emergency reserve allocation']);
            $stmtContrib->execute([$gId, $userId, $accountIds['HBL - Roshan Digital Savings'], 50000.00, '2026-10-05', 'Monthly emergency reserve allocation']);
            $stmtContrib->execute([$gId, $userId, $accountIds['HBL - Roshan Digital Savings'], 50000.00, '2026-11-05', 'Monthly emergency reserve allocation']);
        }
        if (isset($goalIds['Umrah Sacred Journey'])) {
            $gId = $goalIds['Umrah Sacred Journey'];
            $stmtContrib->execute([$gId, $userId, $accountIds['HBL - Roshan Digital Savings'], 70000.00, '2026-09-15', 'Umrah savings transfer']);
            $stmtContrib->execute([$gId, $userId, $accountIds['HBL - Roshan Digital Savings'], 60000.00, '2026-10-15', 'Umrah savings transfer']);
            $stmtContrib->execute([$gId, $userId, $accountIds['HBL - Roshan Digital Savings'], 50000.00, '2026-11-10', 'Umrah savings transfer']);
        }

        // 10. Notifications
        $notifs = [
            ['type' => 'budget_warning', 'title' => 'Budget Alert: Utilities & Bills', 'msg' => 'Utilities & Bills has reached 81% of your PKR 28,000 monthly budget.', 'link' => '/budgets', 'read' => 0],
            ['type' => 'goal_reached', 'title' => 'Goal Accomplished!', 'msg' => 'Congratulations! You reached 100% of your Apple MacBook M3 Max savings target.', 'link' => '/savings', 'read' => 0],
            ['type' => 'monthly_report', 'title' => 'October Financial Report Ready', 'msg' => 'Your financial summary for October 2026 is ready for review.', 'link' => '/reports?month=10&year=2026', 'read' => 1],
            ['type' => 'security', 'title' => 'New Login Detected', 'msg' => 'Successful sign-in from Chrome on Windows at Karachi, PK.', 'link' => '/profile', 'read' => 1],
        ];

        $stmtNotif = $pdo->prepare("INSERT INTO notifications (user_id, type, title, message, link, is_read) VALUES (?, ?, ?, ?, ?, ?)");
        foreach ($notifs as $n) {
            $stmtNotif->execute([$userId, $n['type'], $n['title'], $n['msg'], $n['link'], $n['read']]);
        }

        echo "[DONE]\n";
    }
}
