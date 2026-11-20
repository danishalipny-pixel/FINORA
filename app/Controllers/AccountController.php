<?php
/**
 * FINORA - Account Controller
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
use App\Models\Transaction;

class AccountController
{
    public function index(): void
    {
        $userId = Auth::id();
        $accounts = Account::getAll($userId, true); // Include archived
        $totalBalance = Account::getTotalBalance($userId);

        View::render('accounts.index', [
            'title' => 'Financial Accounts - FINORA',
            'accounts' => $accounts,
            'totalBalance' => $totalBalance,
        ]);
    }

    public function show(string $id, Request $request): void
    {
        $userId = Auth::id();
        $account = Account::findById($userId, (int)$id);

        if (!$account) {
            Session::setFlash('error', 'Account not found or access denied.');
            Response::redirect('/accounts');
        }

        $page = max(1, (int)$request->query('page', 1));
        $transactionsData = Transaction::getFiltered($userId, ['account_id' => $id], $page, 15);

        View::render('accounts.show', [
            'title' => htmlspecialchars($account['name']) . ' - FINORA',
            'account' => $account,
            'transactions' => $transactionsData['data'],
            'pagination' => $transactionsData,
        ]);
    }

    public function store(Request $request): void
    {
        $userId = Auth::id();
        $data = $request->all();

        $validator = Validator::make($data, [
            'name' => 'required|min:2|max:100',
            'type' => 'required|in:cash,bank,savings,other',
            'opening_balance' => 'required|numeric',
        ]);

        if ($validator->fails()) {
            Session::setErrors($validator->errors());
            Session::setFlash('error', $validator->firstError() ?? 'Invalid account data.');
            Response::redirect('/accounts');
        }

        try {
            Account::create($userId, $data);
            Session::setFlash('success', 'New account added successfully.');
            Response::redirect('/accounts');
        } catch (\Throwable $e) {
            Session::setFlash('error', 'Failed to add account: ' . $e->getMessage());
            Response::redirect('/accounts');
        }
    }

    public function update(string $id, Request $request): void
    {
        $userId = Auth::id();
        $account = Account::findById($userId, (int)$id);

        if (!$account) {
            Session::setFlash('error', 'Account not found.');
            Response::redirect('/accounts');
        }

        $data = $request->all();
        $validator = Validator::make($data, [
            'name' => 'required|min:2|max:100',
            'type' => 'required|in:cash,bank,savings,other',
            'opening_balance' => 'required|numeric',
        ]);

        if ($validator->fails()) {
            Session::setFlash('error', $validator->firstError() ?? 'Invalid account data.');
            Response::redirect('/accounts');
        }

        try {
            Account::update($userId, (int)$id, $data);
            Session::setFlash('success', 'Account details updated successfully.');
            Response::redirect('/accounts');
        } catch (\Throwable $e) {
            Session::setFlash('error', 'Failed to update account: ' . $e->getMessage());
            Response::redirect('/accounts');
        }
    }

    public function archive(string $id, Request $request): void
    {
        $userId = Auth::id();
        $account = Account::findById($userId, (int)$id);

        if (!$account) {
            Session::setFlash('error', 'Account not found.');
            Response::redirect('/accounts');
        }

        $isArchived = (int)$account['is_archived'] === 1;
        Account::archive($userId, (int)$id, !$isArchived);

        $action = $isArchived ? 'unarchived and restored' : 'archived';
        Session::setFlash('success', "Account has been {$action}.");
        Response::redirect('/accounts');
    }

    public function delete(string $id): void
    {
        $userId = Auth::id();
        $result = Account::delete($userId, (int)$id);

        if ($result['success']) {
            Session::setFlash('success', $result['message']);
        } else {
            Session::setFlash('error', $result['message']);
        }

        Response::redirect('/accounts');
    }
}
