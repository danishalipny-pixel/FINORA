<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= e($title ?? 'FINORA Financial Statement') ?></title>
    <link rel="icon" type="image/svg+xml" href="/assets/images/logo-icon.svg">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <style>
        body {
            font-family: 'Inter', -apple-system, sans-serif;
            color: #101828;
            background: #FFFFFF;
            padding: 32px;
            font-size: 13px;
        }
        .header-brand {
            display: flex;
            justify-content: space-between;
            align-items: center;
            border-bottom: 2px solid #10B981;
            padding-bottom: 16px;
            margin-bottom: 24px;
        }
        .kpi-box {
            border: 1px solid #EAECF0;
            border-radius: 12px;
            padding: 14px;
            background: #F9FAFB;
        }
        .table-print th {
            background-color: #F3F4F6 !important;
            font-size: 11px;
            text-transform: uppercase;
            letter-spacing: 0.05em;
            padding: 8px 12px;
        }
        .table-print td {
            padding: 8px 12px;
            border-bottom: 1px solid #EAECF0;
        }
        @media print {
            .no-print { display: none !important; }
            body { padding: 0; }
        }
    </style>
</head>
<body>

<div class="no-print d-flex justify-content-between align-items-center mb-4 p-3 bg-light rounded border">
    <div>
        <strong>Print Preview / PDF Generation</strong> &bull; Click below to print or save as PDF.
    </div>
    <div class="d-flex gap-2">
        <button onclick="window.print()" class="btn btn-success btn-sm fw-semibold">
            <i class="bi bi-printer"></i> Print Statement
        </button>
        <button onclick="window.close()" class="btn btn-outline-secondary btn-sm">Close</button>
    </div>
</div>

<div class="header-brand">
    <div>
        <h1 class="h4 fw-bold mb-0 text-dark">FINORA</h1>
        <div class="small text-success fw-bold tracking-wider">PERSONAL FINANCE INTELLIGENCE</div>
    </div>
    <div class="text-end small text-secondary">
        <div><strong>Statement:</strong> <?= e($label) ?></div>
        <div><strong>Scope:</strong> <?= date_fmt($start_date) ?> &ndash; <?= date_fmt($end_date) ?></div>
        <div><strong>Generated:</strong> <?= date('d M Y, H:i') ?> (PKT)</div>
    </div>
</div>

<!-- Statement KPIs -->
<div class="row g-3 mb-4">
    <div class="col-3">
        <div class="kpi-box">
            <div class="text-secondary small text-uppercase">Total Income</div>
            <div class="fs-5 fw-bold text-success"><?= currency($total_income) ?></div>
        </div>
    </div>
    <div class="col-3">
        <div class="kpi-box">
            <div class="text-secondary small text-uppercase">Total Expenses</div>
            <div class="fs-5 fw-bold text-danger"><?= currency($total_expense) ?></div>
        </div>
    </div>
    <div class="col-3">
        <div class="kpi-box">
            <div class="text-secondary small text-uppercase">Net Surplus / Savings</div>
            <div class="fs-5 fw-bold <?= $net_savings >= 0 ? 'text-dark' : 'text-danger' ?>"><?= currency($net_savings) ?></div>
        </div>
    </div>
    <div class="col-3">
        <div class="kpi-box">
            <div class="text-secondary small text-uppercase">Savings Rate</div>
            <div class="fs-5 fw-bold text-primary"><?= $savings_rate ?>%</div>
        </div>
    </div>
</div>

<!-- Category Distribution -->
<div class="row g-4 mb-4">
    <div class="col-6">
        <h6 class="fw-bold mb-2">Expense Category Breakdown</h6>
        <table class="table table-sm table-print border">
            <thead>
                <tr>
                    <th>Category</th>
                    <th class="text-end">Share</th>
                    <th class="text-end">Amount</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($expense_categories)): ?>
                    <tr><td colspan="3" class="text-center text-muted">No expenses recorded</td></tr>
                <?php else: ?>
                    <?php foreach ($expense_categories as $ec): ?>
                        <tr>
                            <td><?= e($ec['category_name']) ?></td>
                            <td class="text-end"><?= $ec['percentage'] ?>%</td>
                            <td class="text-end fw-semibold text-danger"><?= currency($ec['total_amount']) ?></td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>

    <div class="col-6">
        <h6 class="fw-bold mb-2">Income Category Breakdown</h6>
        <table class="table table-sm table-print border">
            <thead>
                <tr>
                    <th>Category</th>
                    <th class="text-end">Share</th>
                    <th class="text-end">Amount</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($income_categories)): ?>
                    <tr><td colspan="3" class="text-center text-muted">No income recorded</td></tr>
                <?php else: ?>
                    <?php foreach ($income_categories as $ic): ?>
                        <tr>
                            <td><?= e($ic['category_name']) ?></td>
                            <td class="text-end"><?= $ic['percentage'] ?>%</td>
                            <td class="text-end fw-semibold text-success"><?= currency($ic['total_amount']) ?></td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<!-- Itemized Transaction Log -->
<h6 class="fw-bold mb-2">Itemized Financial Activity (<?= count($transactions) ?>)</h6>
<table class="table table-sm table-print border mb-4">
    <thead>
        <tr>
            <th>Date</th>
            <th>Description</th>
            <th>Type</th>
            <th>Category</th>
            <th>Account</th>
            <th class="text-end">Amount</th>
        </tr>
    </thead>
    <tbody>
        <?php foreach ($transactions as $t): ?>
            <tr>
                <td><?= date_fmt($t['transaction_date']) ?></td>
                <td><?= e($t['description']) ?></td>
                <td class="text-capitalize"><?= e($t['type']) ?></td>
                <td><?= e($t['category_name'] ?? '-') ?></td>
                <td><?= e($t['account_name'] ?? '-') ?></td>
                <td class="text-end fw-bold <?= $t['type'] === 'income' ? 'text-success' : ($t['type'] === 'expense' ? 'text-danger' : 'text-primary') ?>">
                    <?= $t['type'] === 'income' ? '+' : ($t['type'] === 'expense' ? '-' : '') ?>
                    <?= currency($t['amount']) ?>
                </td>
            </tr>
        <?php endforeach; ?>
    </tbody>
</table>

<div class="pt-3 border-top text-center text-secondary smaller">
    FINORA Personal Finance Intelligence &bull; Verified Database Export &bull; <?= date('Y') ?>
</div>

<script>
    // Auto trigger print dialog on open
    window.addEventListener('load', () => {
        setTimeout(() => {
            window.print();
        }, 400);
    });
</script>

</body>
</html>
