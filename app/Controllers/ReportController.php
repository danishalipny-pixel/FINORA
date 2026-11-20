<?php
/**
 * FINORA - Reports & Analytics Controller
 */

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Auth;
use App\Core\Request;
use App\Core\View;
use App\Services\ExportService;
use App\Services\FinancialAnalyticsService;

class ReportController
{
    public function index(Request $request): void
    {
        $userId = Auth::id();
        $period = $request->query('period', 'monthly');
        $customStart = $request->query('start_date');
        $customEnd = $request->query('end_date');

        $reportData = FinancialAnalyticsService::getReportData($userId, $period, $customStart, $customEnd);

        View::render('reports.index', array_merge($reportData, [
            'title' => 'Financial Reports & Intelligence - FINORA',
        ]));
    }

    public function exportCsv(Request $request): void
    {
        $userId = Auth::id();
        $period = $request->query('period', 'monthly');
        $customStart = $request->query('start_date');
        $customEnd = $request->query('end_date');

        $reportData = FinancialAnalyticsService::getReportData($userId, $period, $customStart, $customEnd);
        $filename = 'finora_report_' . $period . '_' . date('Ymd_His') . '.csv';

        ExportService::exportTransactionsCsv($reportData['transactions'], $filename);
    }

    public function printReport(Request $request): void
    {
        $userId = Auth::id();
        $period = $request->query('period', 'monthly');
        $customStart = $request->query('start_date');
        $customEnd = $request->query('end_date');

        $reportData = FinancialAnalyticsService::getReportData($userId, $period, $customStart, $customEnd);

        View::render('reports.print', array_merge($reportData, [
            'title' => 'Print Report - ' . $reportData['label'] . ' - FINORA',
        ]), 'none');
    }

    public function downloadPdf(Request $request): void
    {
        // Renders clean printable report with auto-print headers
        $this->printReport($request);
    }
}
