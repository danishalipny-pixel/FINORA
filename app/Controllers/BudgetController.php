<?php
/**
 * FINORA - Budget Controller
 */

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Auth;
use App\Core\Request;
use App\Core\Response;
use App\Core\Session;
use App\Core\Validator;
use App\Core\View;
use App\Models\Budget;
use App\Models\Category;

class BudgetController
{
    public function index(Request $request): void
    {
        $userId = Auth::id();
        $month = (int)$request->query('month', (int)date('m'));
        $year = (int)$request->query('year', (int)date('Y'));

        // Clamp month & year
        if ($month < 1 || $month > 12) {
            $month = (int)date('m');
        }
        if ($year < 2020 || $year > 2050) {
            $year = (int)date('Y');
        }

        $budgets = Budget::getBudgetsForMonth($userId, $month, $year);
        $summary = Budget::getTotalBudgetSummary($userId, $month, $year);
        $expenseCategories = Category::getAll($userId, 'expense');

        View::render('budgets.index', [
            'title' => 'Budgets & Limits - FINORA',
            'budgets' => $budgets,
            'summary' => $summary,
            'expenseCategories' => $expenseCategories,
            'selectedMonth' => $month,
            'selectedYear' => $year,
        ]);
    }

    public function store(Request $request): void
    {
        $userId = Auth::id();
        $data = $request->all();

        $validator = Validator::make($data, [
            'category_id' => 'required|numeric',
            'amount' => 'required|positive_number',
            'month' => 'required|numeric|min:1|max:12',
            'year' => 'required|numeric|min:2020|max:2050',
        ]);

        if ($validator->fails()) {
            Session::setFlash('error', $validator->firstError() ?? 'Invalid budget parameters.');
            Response::redirect('/budgets?month=' . ($data['month'] ?? date('m')) . '&year=' . ($data['year'] ?? date('Y')));
        }

        try {
            Budget::createOrUpdate(
                $userId,
                (int)$data['category_id'],
                (float)$data['amount'],
                (int)$data['month'],
                (int)$data['year']
            );

            Session::setFlash('success', 'Monthly budget limit successfully set.');
            Response::redirect('/budgets?month=' . $data['month'] . '&year=' . $data['year']);
        } catch (\Throwable $e) {
            Session::setFlash('error', 'Failed to save budget: ' . $e->getMessage());
            Response::redirect('/budgets');
        }
    }

    public function delete(string $id, Request $request): void
    {
        $userId = Auth::id();
        $budget = Budget::findById($userId, (int)$id);

        if (!$budget) {
            Session::setFlash('error', 'Budget not found or unauthorized.');
            Response::redirect('/budgets');
        }

        Budget::delete($userId, (int)$id);
        Session::setFlash('success', 'Budget deleted successfully.');
        Response::redirect('/budgets?month=' . $budget['month'] . '&year=' . $budget['year']);
    }
}
