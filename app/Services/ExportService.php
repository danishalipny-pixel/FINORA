<?php
/**
 * FINORA - Data Export & Import Service
 */

declare(strict_types=1);

namespace App\Services;

use App\Core\Database;
use App\Models\Account;
use App\Models\Category;
use App\Models\Transaction;

class ExportService
{
    public static function exportTransactionsCsv(array $transactions, string $filename = 'finora_transactions.csv'): void
    {
        $headers = [
            'Date',
            'Description',
            'Type',
            'Category',
            'Account',
            'Amount (PKR)',
            'Payment Method',
            'Reference ID',
            'Notes',
        ];

        $rows = [];
        foreach ($transactions as $t) {
            $rows[] = [
                $t['transaction_date'],
                $t['description'],
                ucfirst($t['type']),
                $t['category_name'] ?? 'Uncategorized',
                $t['account_name'] ?? '-',
                number_format((float)$t['amount'], 2, '.', ''),
                $t['payment_method'] ?? 'Cash',
                $t['reference_id'] ?? '',
                $t['notes'] ?? '',
            ];
        }

        \App\Core\Response::csv($headers, $rows, $filename);
    }

    public static function importTransactionsCsv(int $userId, string $filePath): array
    {
        if (!file_exists($filePath) || !is_readable($filePath)) {
            return ['success' => false, 'message' => 'Unable to read the uploaded CSV file.'];
        }

        $handle = fopen($filePath, 'r');
        if ($handle === false) {
            return ['success' => false, 'message' => 'Failed to open the uploaded CSV file.'];
        }

        // Check for BOM and read header
        $header = fgetcsv($handle);
        if (!$header) {
            fclose($handle);
            return ['success' => false, 'message' => 'The uploaded CSV file is empty.'];
        }

        // Clean UTF-8 BOM
        $header[0] = preg_replace('/[\x00-\x1F\x80-\xFF]/', '', $header[0]);
        $headerMap = array_map(fn($h) => strtolower(trim($h)), $header);

        // Fetch user accounts and categories for name resolution
        $accounts = Account::getAll($userId, false);
        if (empty($accounts)) {
            fclose($handle);
            return ['success' => false, 'message' => 'You must have at least one active financial account before importing transactions.'];
        }

        $accountMap = [];
        foreach ($accounts as $acc) {
            $accountMap[strtolower($acc['name'])] = (int)$acc['id'];
        }
        $defaultAccountId = (int)$accounts[0]['id'];

        $categories = Category::getAll($userId);
        $categoryMap = [];
        foreach ($categories as $cat) {
            $categoryMap[strtolower($cat['name'])] = (int)$cat['id'];
        }

        $imported = 0;
        $skipped = 0;
        $errors = [];

        $pdo = Database::getConnection();
        $pdo->beginTransaction();

        try {
            $rowNum = 1;
            while (($row = fgetcsv($handle)) !== false) {
                $rowNum++;
                // Skip empty lines
                if (count(array_filter($row)) === 0) {
                    continue;
                }

                $rowData = [];
                foreach ($headerMap as $index => $colName) {
                    $rowData[$colName] = isset($row[$index]) ? trim($row[$index]) : '';
                }

                // Resolve columns flexibly
                $date = $rowData['date'] ?? $rowData['transaction_date'] ?? '';
                $desc = $rowData['description'] ?? $rowData['desc'] ?? 'Imported Transaction';
                $type = strtolower($rowData['type'] ?? 'expense');
                $amount = (float)($rowData['amount'] ?? $rowData['amount (pkr)'] ?? 0);
                $accountName = strtolower($rowData['account'] ?? $rowData['account_name'] ?? '');
                $categoryName = strtolower($rowData['category'] ?? $rowData['category_name'] ?? '');
                $method = $rowData['payment method'] ?? $rowData['payment_method'] ?? 'Cash';
                $ref = $rowData['reference id'] ?? $rowData['reference_id'] ?? null;
                $notes = $rowData['notes'] ?? null;

                // Validate Date
                $parsedDate = date('Y-m-d', strtotime($date));
                if (!$date || $parsedDate === '1970-01-01' || !strtotime($date)) {
                    $skipped++;
                    $errors[] = "Row {$rowNum}: Invalid date format ('{$date}').";
                    continue;
                }

                // Validate Amount
                if ($amount <= 0) {
                    $skipped++;
                    $errors[] = "Row {$rowNum}: Amount must be greater than zero.";
                    continue;
                }

                // Validate Type
                if (!in_array($type, ['income', 'expense', 'transfer'], true)) {
                    $type = 'expense';
                }

                // Resolve Account
                $accountId = $accountMap[$accountName] ?? $defaultAccountId;

                // Resolve Category
                $categoryId = $categoryMap[$categoryName] ?? null;

                // Insert Transaction
                Transaction::create($userId, [
                    'account_id' => $accountId,
                    'category_id' => $categoryId,
                    'type' => $type,
                    'amount' => $amount,
                    'transaction_date' => $parsedDate,
                    'payment_method' => $method,
                    'description' => $desc,
                    'notes' => $notes,
                    'reference_id' => $ref,
                ]);

                $imported++;
            }

            $pdo->commit();
            fclose($handle);

            return [
                'success' => true,
                'imported' => $imported,
                'skipped' => $skipped,
                'errors' => array_slice($errors, 0, 5),
                'message' => "Successfully imported {$imported} transaction(s)." . ($skipped > 0 ? " ({$skipped} skipped)" : '')
            ];
        } catch (\Throwable $e) {
            $pdo->rollBack();
            fclose($handle);
            return [
                'success' => false,
                'message' => 'Import failed: ' . $e->getMessage()
            ];
        }
    }
}
