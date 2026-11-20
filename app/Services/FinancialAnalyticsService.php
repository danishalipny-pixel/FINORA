<?php
/**
 * FINORA - Financial Analytics & Aggregation Engine
 */

declare(strict_types=1);

namespace App\Services;

use App\Core\Database;
use App\Models\Account;
use App\Models\Budget;
use App\Models\SavingsGoal;
use App\Models\Transaction;
use PDO;

class FinancialAnalyticsService
{
    public static function getDashboardData(int $userId): array
    {
        $currentYear = (int)date('Y');
        $currentMonth = (int)date('m');

        // 1. Account Balances & Total Balance
        $accounts = Account::getAll($userId, false);
        $totalBalance = 0.0;
        foreach ($accounts as $acc) {
            $totalBalance += $acc['current_balance'];
        }

        // 2. Monthly Summary
        $monthly = Transaction::getMonthlySummary($userId, $currentYear, $currentMonth);

        // Previous month comparison for growth %
        $prevMonthTimestamp = strtotime("-1 month");
        $prevYear = (int)date('Y', $prevMonthTimestamp);
        $prevMonth = (int)date('m', $prevMonthTimestamp);
        $prevMonthly = Transaction::getMonthlySummary($userId, $prevYear, $prevMonth);

        $incomeGrowth = $prevMonthly['income'] > 0
            ? round((($monthly['income'] - $prevMonthly['income']) / $prevMonthly['income']) * 100, 1)
            : 0.0;

        $expenseGrowth = $prevMonthly['expense'] > 0
            ? round((($monthly['expense'] - $prevMonthly['expense']) / $prevMonthly['expense']) * 100, 1)
            : 0.0;

        // 3. Total Savings
        $totalSavings = SavingsGoal::getTotalSaved($userId);

        // 4. Recent Transactions
        $recentTransactions = Transaction::getRecent($userId, 6);

        // 5. Monthly Budget Progress
        $budgetSummary = Budget::getTotalBudgetSummary($userId, $currentMonth, $currentYear);
        $budgets = Budget::getBudgetsForMonth($userId, $currentMonth, $currentYear);

        // 6. Savings Goals
        $savingsGoals = SavingsGoal::getAll($userId);

        // 7. Chart: Cashflow History (6 months)
        $cashflow = Transaction::getMonthlyCashflow($userId, 6);

        // 8. Chart: Category Expenses (current month)
        $categoryExpenses = Transaction::getCategoryExpenses($userId, $currentYear, $currentMonth);

        return [
            'total_balance' => round($totalBalance, 2),
            'monthly_income' => $monthly['income'],
            'monthly_expense' => $monthly['expense'],
            'monthly_savings' => $monthly['savings'],
            'savings_rate' => $monthly['savings_rate'],
            'income_growth' => $incomeGrowth,
            'expense_growth' => $expenseGrowth,
            'total_savings' => $totalSavings,
            'accounts' => $accounts,
            'recent_transactions' => $recentTransactions,
            'budget_summary' => $budgetSummary,
            'budgets' => array_slice($budgets, 0, 5),
            'savings_goals' => array_slice($savingsGoals, 0, 4),
            'chart_cashflow' => $cashflow,
            'chart_category_expenses' => $categoryExpenses,
        ];
    }

    public static function getReportData(int $userId, string $period, ?string $customStart = null, ?string $customEnd = null): array
    {
        $pdo = Database::getConnection();

        // Determine date range
        $today = date('Y-m-d');
        switch ($period) {
            case 'daily':
                $startDate = $today;
                $endDate = $today;
                $label = "Daily Report: " . date('d M Y');
                break;
            case 'weekly':
                $startDate = date('Y-m-d', strtotime('monday this week'));
                $endDate = date('Y-m-d', strtotime('sunday this week'));
                $label = "Weekly Report (" . date('d M', strtotime($startDate)) . " - " . date('d M Y', strtotime($endDate)) . ")";
                break;
            case 'yearly':
                $startDate = date('Y-01-01');
                $endDate = date('Y-12-31');
                $label = "Yearly Report: " . date('Y');
                break;
            case 'custom':
                $startDate = $customStart ?: date('Y-m-01');
                $endDate = $customEnd ?: $today;
                $label = "Custom Report (" . date('d M Y', strtotime($startDate)) . " - " . date('d M Y', strtotime($endDate)) . ")";
                break;
            case 'monthly':
            default:
                $period = 'monthly';
                $startDate = date('Y-m-01');
                $endDate = date('Y-m-t');
                $label = "Monthly Report: " . date('F Y');
                break;
        }

        // 1. Overall Income, Expense, Net Savings
        $stmtTotal = $pdo->prepare("
            SELECT 
                COALESCE(SUM(CASE WHEN type = 'income' THEN amount ELSE 0 END), 0) AS total_income,
                COALESCE(SUM(CASE WHEN type = 'expense' THEN amount ELSE 0 END), 0) AS total_expense,
                COUNT(id) AS transaction_count
            FROM transactions
            WHERE user_id = ? AND transaction_date BETWEEN ? AND ?
        ");
        $stmtTotal->execute([$userId, $startDate, $endDate]);
        $summary = $stmtTotal->fetch();

        $totalIncome = (float)$summary['total_income'];
        $totalExpense = (float)$summary['total_expense'];
        $netSavings = $totalIncome - $totalExpense;
        $savingsRate = $totalIncome > 0 ? round(($netSavings / $totalIncome) * 100, 1) : 0.0;

        // 2. Expense breakdown by category
        $stmtCatExp = $pdo->prepare("
            SELECT 
                COALESCE(c.name, 'Uncategorized') AS category_name,
                COALESCE(c.color, '#64748B') AS category_color,
                COALESCE(c.icon, 'bi-tag') AS category_icon,
                SUM(t.amount) AS total_amount,
                COUNT(t.id) AS count
            FROM transactions t
            LEFT JOIN categories c ON t.category_id = c.id
            WHERE t.user_id = ? AND t.type = 'expense' AND t.transaction_date BETWEEN ? AND ?
            GROUP BY t.category_id, c.name, c.color, c.icon
            ORDER BY total_amount DESC
        ");
        $stmtCatExp->execute([$userId, $startDate, $endDate]);
        $expenseCategories = $stmtCatExp->fetchAll();

        // Calculate % for each category
        foreach ($expenseCategories as &$ec) {
            $ec['percentage'] = $totalExpense > 0 ? round(((float)$ec['total_amount'] / $totalExpense) * 100, 1) : 0.0;
        }

        // 3. Income breakdown by category
        $stmtCatInc = $pdo->prepare("
            SELECT 
                COALESCE(c.name, 'Uncategorized') AS category_name,
                COALESCE(c.color, '#10B981') AS category_color,
                COALESCE(c.icon, 'bi-cash') AS category_icon,
                SUM(t.amount) AS total_amount,
                COUNT(t.id) AS count
            FROM transactions t
            LEFT JOIN categories c ON t.category_id = c.id
            WHERE t.user_id = ? AND t.type = 'income' AND t.transaction_date BETWEEN ? AND ?
            GROUP BY t.category_id, c.name, c.color, c.icon
            ORDER BY total_amount DESC
        ");
        $stmtCatInc->execute([$userId, $startDate, $endDate]);
        $incomeCategories = $stmtCatInc->fetchAll();

        foreach ($incomeCategories as &$ic) {
            $ic['percentage'] = $totalIncome > 0 ? round(((float)$ic['total_amount'] / $totalIncome) * 100, 1) : 0.0;
        }

        // 4. All transactions in period
        $stmtTx = $pdo->prepare("
            SELECT 
                t.*,
                a.name AS account_name,
                c.name AS category_name,
                c.color AS category_color
            FROM transactions t
            LEFT JOIN accounts a ON t.account_id = a.id
            LEFT JOIN categories c ON t.category_id = c.id
            WHERE t.user_id = ? AND t.transaction_date BETWEEN ? AND ?
            ORDER BY t.transaction_date DESC, t.id DESC
        ");
        $stmtTx->execute([$userId, $startDate, $endDate]);
        $transactions = $stmtTx->fetchAll();

        return [
            'period' => $period,
            'label' => $label,
            'start_date' => $startDate,
            'end_date' => $endDate,
            'total_income' => $totalIncome,
            'total_expense' => $totalExpense,
            'net_savings' => $netSavings,
            'savings_rate' => $savingsRate,
            'transaction_count' => (int)$summary['transaction_count'],
            'expense_categories' => $expenseCategories,
            'income_categories' => $incomeCategories,
            'transactions' => $transactions,
        ];
    }
}
