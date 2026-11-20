<?php
/**
 * FINORA - Transaction Controller
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
use App\Models\Category;
use App\Models\Transaction;
use App\Services\ExportService;
use App\Services\NotificationService;

class TransactionController
{
    public function index(Request $request): void
    {
        $userId = Auth::id();
        $filters = [
            'q' => $request->query('q'),
            'type' => $request->query('type'),
            'category_id' => $request->query('category_id'),
            'account_id' => $request->query('account_id'),
            'date_from' => $request->query('date_from'),
            'date_to' => $request->query('date_to'),
            'sort' => $request->query('sort', 'date_desc'),
        ];

        $page = max(1, (int)$request->query('page', 1));
        $transactionsData = Transaction::getFiltered($userId, $filters, $page, 15);

        $accounts = Account::getAll($userId, false);
        $categories = Category::getAll($userId);

        View::render('transactions.index', [
            'title' => 'Transactions - FINORA',
            'transactions' => $transactionsData['data'],
            'pagination' => $transactionsData,
            'filters' => $filters,
            'accounts' => $accounts,
            'categories' => $categories,
        ]);
    }

    public function create(): void
    {
        $userId = Auth::id();
        $accounts = Account::getAll($userId, false);
        $incomeCategories = Category::getAll($userId, 'income');
        $expenseCategories = Category::getAll($userId, 'expense');

        View::render('transactions.create', [
            'title' => 'Add Transaction - FINORA',
            'accounts' => $accounts,
            'incomeCategories' => $incomeCategories,
            'expenseCategories' => $expenseCategories,
        ]);
    }

    public function store(Request $request): void
    {
        $userId = Auth::id();
        $data = $request->all();
        Session::setOldInput($data);

        $rules = [
            'type' => 'required|in:income,expense,transfer',
            'amount' => 'required|positive_number',
            'account_id' => 'required|numeric|exists:accounts,id,auth_user',
            'transaction_date' => 'required|date',
            'description' => 'required|min:2|max:255',
            'payment_method' => 'required|in:Cash,Bank Transfer,Debit Card,Credit Card,Online,Cheque,Other',
        ];

        if (($data['type'] ?? '') === 'transfer') {
            $rules['destination_account_id'] = 'required|numeric|exists:accounts,id,auth_user';
        }

        $validator = Validator::make($data, $rules, [
            'account_id' => 'Please select a valid account you own.',
            'destination_account_id' => 'Please select a valid destination account you own.',
            'amount' => 'Please enter a valid amount greater than zero.',
        ]);

        if ($validator->fails()) {
            Session::setErrors($validator->errors());
            Response::redirect('/transactions/create');
        }

        // Additional validation: cannot transfer to same account
        if ($data['type'] === 'transfer' && $data['account_id'] == $data['destination_account_id']) {
            Session::setErrors(['destination_account_id' => 'Source and destination accounts cannot be the same.']);
            Response::redirect('/transactions/create');
        }

        try {
            $txId = Transaction::create($userId, $data);

            // Check budget alert if expense
            if ($data['type'] === 'expense' && !empty($data['category_id'])) {
                NotificationService::checkBudgetAlerts($userId, (int)$data['category_id'], $data['transaction_date']);
            }

            Session::clearOldInput();
            Session::setFlash('success', 'Transaction recorded successfully.');
            Response::redirect('/transactions');
        } catch (\Throwable $e) {
            Session::setFlash('error', 'Failed to save transaction: ' . $e->getMessage());
            Response::redirect('/transactions/create');
        }
    }

    public function edit(string $id): void
    {
        $userId = Auth::id();
        $tx = Transaction::findById($userId, (int)$id);

        if (!$tx) {
            Session::setFlash('error', 'Transaction not found or unauthorized.');
            Response::redirect('/transactions');
        }

        $accounts = Account::getAll($userId, false);
        $incomeCategories = Category::getAll($userId, 'income');
        $expenseCategories = Category::getAll($userId, 'expense');

        View::render('transactions.edit', [
            'title' => 'Edit Transaction - FINORA',
            'transaction' => $tx,
            'accounts' => $accounts,
            'incomeCategories' => $incomeCategories,
            'expenseCategories' => $expenseCategories,
        ]);
    }

    public function update(string $id, Request $request): void
    {
        $userId = Auth::id();
        $tx = Transaction::findById($userId, (int)$id);

        if (!$tx) {
            Session::setFlash('error', 'Transaction not found or unauthorized.');
            Response::redirect('/transactions');
        }

        $data = $request->all();
        $rules = [
            'type' => 'required|in:income,expense,transfer',
            'amount' => 'required|positive_number',
            'account_id' => 'required|numeric|exists:accounts,id,auth_user',
            'transaction_date' => 'required|date',
            'description' => 'required|min:2|max:255',
            'payment_method' => 'required|in:Cash,Bank Transfer,Debit Card,Credit Card,Online,Cheque,Other',
        ];

        if (($data['type'] ?? '') === 'transfer') {
            $rules['destination_account_id'] = 'required|numeric|exists:accounts,id,auth_user';
        }

        $validator = Validator::make($data, $rules);

        if ($validator->fails()) {
            Session::setErrors($validator->errors());
            Response::redirect("/transactions/{$id}/edit");
        }

        if ($data['type'] === 'transfer' && $data['account_id'] == $data['destination_account_id']) {
            Session::setErrors(['destination_account_id' => 'Source and destination accounts cannot be the same.']);
            Response::redirect("/transactions/{$id}/edit");
        }

        try {
            Transaction::update($userId, (int)$id, $data);

            if ($data['type'] === 'expense' && !empty($data['category_id'])) {
                NotificationService::checkBudgetAlerts($userId, (int)$data['category_id'], $data['transaction_date']);
            }

            Session::setFlash('success', 'Transaction updated successfully.');
            Response::redirect('/transactions');
        } catch (\Throwable $e) {
            Session::setFlash('error', 'Failed to update transaction: ' . $e->getMessage());
            Response::redirect("/transactions/{$id}/edit");
        }
    }

    public function delete(string $id): void
    {
        $userId = Auth::id();
        $tx = Transaction::findById($userId, (int)$id);

        if (!$tx) {
            Session::setFlash('error', 'Transaction not found or unauthorized.');
            Response::redirect('/transactions');
        }

        Transaction::delete($userId, (int)$id);
        Session::setFlash('success', 'Transaction deleted successfully.');
        Response::redirect('/transactions');
    }

    public function exportCsv(Request $request): void
    {
        $userId = Auth::id();
        $filters = [
            'q' => $request->query('q'),
            'type' => $request->query('type'),
            'category_id' => $request->query('category_id'),
            'account_id' => $request->query('account_id'),
            'date_from' => $request->query('date_from'),
            'date_to' => $request->query('date_to'),
            'sort' => $request->query('sort', 'date_desc'),
        ];

        $transactions = Transaction::getAllForExport($userId, $filters);
        $filename = 'finora_transactions_' . date('Ymd_His') . '.csv';

        ExportService::exportTransactionsCsv($transactions, $filename);
    }

    public function importForm(): void
    {
        View::render('transactions.import', [
            'title' => 'Import Transactions CSV - FINORA',
        ]);
    }

    public function importCsv(Request $request): void
    {
        $userId = Auth::id();
        $file = $request->file('csv_file');

        if (!$file || $file['error'] !== UPLOAD_ERR_OK) {
            Session::setFlash('error', 'Please upload a valid CSV file.');
            Response::redirect('/transactions/import');
        }

        $extension = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
        if ($extension !== 'csv') {
            Session::setFlash('error', 'Only valid .csv files are supported.');
            Response::redirect('/transactions/import');
        }

        $result = ExportService::importTransactionsCsv($userId, $file['tmp_name']);

        if ($result['success']) {
            Session::setFlash('success', $result['message']);
            Response::redirect('/transactions');
        } else {
            Session::setFlash('error', $result['message']);
            Response::redirect('/transactions/import');
        }
    }
}
