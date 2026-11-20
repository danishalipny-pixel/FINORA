<?php
/**
 * FINORA - Savings Goals Controller
 */

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Auth;
use App\Core\Request;
use App\Core\Response;
use App\Core\Session;
use App\Core\Validator;
use App\Core\View;
use App\Models\Account;
use App\Models\SavingsContribution;
use App\Models\SavingsGoal;

class SavingsGoalController
{
    public function index(): void
    {
        $userId = Auth::id();
        $goals = SavingsGoal::getAll($userId);
        $accounts = Account::getAll($userId, false);
        $totalSaved = SavingsGoal::getTotalSaved($userId);

        View::render('savings.index', [
            'title' => 'Savings Goals & Wealth Growth - FINORA',
            'goals' => $goals,
            'accounts' => $accounts,
            'totalSaved' => $totalSaved,
        ]);
    }

    public function store(Request $request): void
    {
        $userId = Auth::id();
        $data = $request->all();

        $validator = Validator::make($data, [
            'name' => 'required|min:2|max:150',
            'target_amount' => 'required|positive_number',
            'target_date' => 'required|date',
        ]);

        if ($validator->fails()) {
            Session::setFlash('error', $validator->firstError() ?? 'Invalid goal data.');
            Response::redirect('/savings');
        }

        try {
            SavingsGoal::create($userId, $data);
            Session::setFlash('success', 'New savings goal established! Consistency builds wealth.');
            Response::redirect('/savings');
        } catch (\Throwable $e) {
            Session::setFlash('error', 'Failed to create savings goal: ' . $e->getMessage());
            Response::redirect('/savings');
        }
    }

    public function update(string $id, Request $request): void
    {
        $userId = Auth::id();
        $goal = SavingsGoal::findById($userId, (int)$id);

        if (!$goal) {
            Session::setFlash('error', 'Goal not found or unauthorized.');
            Response::redirect('/savings');
        }

        $data = $request->all();
        $validator = Validator::make($data, [
            'name' => 'required|min:2|max:150',
            'target_amount' => 'required|positive_number',
            'target_date' => 'required|date',
            'status' => 'required|in:in_progress,completed,paused',
        ]);

        if ($validator->fails()) {
            Session::setFlash('error', $validator->firstError() ?? 'Invalid goal data.');
            Response::redirect('/savings');
        }

        try {
            SavingsGoal::update($userId, (int)$id, $data);
            Session::setFlash('success', 'Savings goal updated successfully.');
            Response::redirect('/savings');
        } catch (\Throwable $e) {
            Session::setFlash('error', 'Failed to update goal: ' . $e->getMessage());
            Response::redirect('/savings');
        }
    }

    public function delete(string $id): void
    {
        $userId = Auth::id();
        $goal = SavingsGoal::findById($userId, (int)$id);

        if (!$goal) {
            Session::setFlash('error', 'Goal not found or unauthorized.');
            Response::redirect('/savings');
        }

        SavingsGoal::delete($userId, (int)$id);
        Session::setFlash('success', 'Savings goal and associated history removed.');
        Response::redirect('/savings');
    }

    public function contributions(string $id): void
    {
        $userId = Auth::id();
        $goal = SavingsGoal::findById($userId, (int)$id);

        if (!$goal) {
            Session::setFlash('error', 'Goal not found or unauthorized.');
            Response::redirect('/savings');
        }

        $contributions = SavingsContribution::getByGoal($userId, (int)$id);
        $accounts = Account::getAll($userId, false);

        View::render('savings.contributions', [
            'title' => 'Contributions: ' . htmlspecialchars($goal['name']) . ' - FINORA',
            'goal' => $goal,
            'contributions' => $contributions,
            'accounts' => $accounts,
        ]);
    }

    public function addContribution(string $id, Request $request): void
    {
        $userId = Auth::id();
        $goal = SavingsGoal::findById($userId, (int)$id);

        if (!$goal) {
            Session::setFlash('error', 'Goal not found or unauthorized.');
            Response::redirect('/savings');
        }

        $data = $request->all();
        $validator = Validator::make($data, [
            'amount' => 'required|positive_number',
            'contribution_date' => 'required|date',
        ]);

        if ($validator->fails()) {
            Session::setFlash('error', $validator->firstError() ?? 'Invalid contribution data.');
            Response::redirect("/savings/{$id}/contributions");
        }

        try {
            SavingsContribution::add($userId, (int)$id, $data);
            Session::setFlash('success', 'Savings contribution added successfully!');
            Response::redirect("/savings/{$id}/contributions");
        } catch (\Throwable $e) {
            Session::setFlash('error', 'Failed to record contribution: ' . $e->getMessage());
            Response::redirect("/savings/{$id}/contributions");
        }
    }

    public function deleteContribution(string $id, string $contributionId): void
    {
        $userId = Auth::id();
        SavingsContribution::delete($userId, (int)$contributionId);
        Session::setFlash('success', 'Contribution record deleted.');
        Response::redirect("/savings/{$id}/contributions");
    }
}
