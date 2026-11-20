<div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center mb-4 gap-3">
    <div>
        <h2 class="h4 fw-bold mb-1">Financial Intelligence & Reports</h2>
        <p class="text-secondary small mb-0">Detailed auditing and structural breakdown of your personal finances.</p>
    </div>
    <div class="d-flex flex-wrap gap-2">
        <a href="/reports/export/csv?period=<?= urlencode($period) ?>&start_date=<?= urlencode($start_date) ?>&end_date=<?= urlencode($end_date) ?>" class="btn btn-fin-secondary btn-sm">
            <i class="bi bi-file-earmark-spreadsheet"></i> Export CSV
        </a>
        <a href="/reports/print?period=<?= urlencode($period) ?>&start_date=<?= urlencode($start_date) ?>&end_date=<?= urlencode($end_date) ?>" target="_blank" class="btn btn-fin-primary btn-sm">
            <i class="bi bi-printer"></i> Print / Download PDF
        </a>
    </div>
</div>

<!-- Period Selection Navigation & Custom Date Filter -->
<div class="fin-card mb-4">
    <div class="fin-card-body p-3">
        <div class="d-flex flex-column flex-lg-row justify-content-between align-items-lg-center gap-3">
            <div class="btn-group btn-group-sm" role="group">
                <a href="/reports?period=daily" class="btn <?= $period === 'daily' ? 'btn-fin-primary' : 'btn-fin-secondary' ?>">Daily</a>
                <a href="/reports?period=weekly" class="btn <?= $period === 'weekly' ? 'btn-fin-primary' : 'btn-fin-secondary' ?>">Weekly</a>
                <a href="/reports?period=monthly" class="btn <?= $period === 'monthly' ? 'btn-fin-primary' : 'btn-fin-secondary' ?>">Monthly</a>
                <a href="/reports?period=yearly" class="btn <?= $period === 'yearly' ? 'btn-fin-primary' : 'btn-fin-secondary' ?>">Yearly</a>
                <a href="/reports?period=custom" class="btn <?= $period === 'custom' ? 'btn-fin-primary' : 'btn-fin-secondary' ?>">Custom Range</a>
            </div>

            <!-- Custom Date Range Filter Form -->
            <form method="GET" action="/reports" class="d-flex align-items-center gap-2 m-0 flex-wrap">
                <input type="hidden" name="period" value="custom">
                <div class="input-group input-group-sm">
                    <span class="input-group-text">From</span>
                    <input type="date" class="form-control" name="start_date" value="<?= e($start_date) ?>" required>
                </div>
                <div class="input-group input-group-sm">
                    <span class="input-group-text">To</span>
                    <input type="date" class="form-control" name="end_date" value="<?= e($end_date) ?>" required>
                </div>
                <button type="submit" class="btn btn-fin-primary btn-sm">Apply</button>
            </form>
        </div>
    </div>
</div>

<!-- Active Reporting Scope Banner -->
<div class="alert alert-light border shadow-xs d-flex align-items-center justify-content-between mb-4">
    <div class="d-flex align-items-center gap-2">
        <i class="bi bi-calendar3 text-primary fs-5"></i>
        <div>
            <strong><?= e($label) ?></strong>
            <span class="text-secondary small ms-2">(<?= date_fmt($start_date) ?> to <?= date_fmt($end_date) ?>)</span>
        </div>
    </div>
    <span class="badge bg-secondary-subtle text-secondary small"><?= $transaction_count ?> Transactions Analyzed</span>
</div>

<!-- Report KPIs -->
<div class="row g-3 mb-4">
    <div class="col-12 col-sm-6 col-xl-3">
        <div class="kpi-card">
            <div class="kpi-label">Total Period Income</div>
            <div class="kpi-amount fs-3 text-success"><?= currency($total_income) ?></div>
            <div class="small text-secondary">Inflow revenue</div>
        </div>
    </div>
    <div class="col-12 col-sm-6 col-xl-3">
        <div class="kpi-card">
            <div class="kpi-label">Total Period Expenses</div>
            <div class="kpi-amount fs-3 text-danger"><?= currency($total_expense) ?></div>
            <div class="small text-secondary">Outflow payments</div>
        </div>
    </div>
    <div class="col-12 col-sm-6 col-xl-3">
        <div class="kpi-card">
            <div class="kpi-label">Net Surplus / Savings</div>
            <div class="kpi-amount fs-3 <?= $net_savings >= 0 ? 'text-primary' : 'text-danger' ?>">
                <?= currency($net_savings) ?>
            </div>
            <div class="small text-secondary">Retained capital</div>
        </div>
    </div>
    <div class="col-12 col-sm-6 col-xl-3">
        <div class="kpi-card">
            <div class="kpi-label">Effective Savings Rate</div>
            <div class="kpi-amount fs-3 text-warning"><?= $savings_rate ?>%</div>
            <div class="small text-secondary">Income retention efficiency</div>
        </div>
    </div>
</div>

<!-- Category Distributions (Side by Side) -->
<div class="row g-4 mb-4">
    <!-- Expense Breakdown Table -->
    <div class="col-12 col-lg-6">
        <div class="fin-card h-100 mb-0">
            <div class="fin-card-header">
                <h3 class="fin-card-title">
                    <i class="bi bi-arrow-up-right-circle text-danger"></i> Expense Breakdown by Category
                </h3>
            </div>
            <div class="fin-card-body p-0">
                <?php if (empty($expense_categories)): ?>
                    <div class="empty-state py-4">
                        <i class="bi bi-tag text-secondary fs-2 mb-2 d-block"></i>
                        <p class="text-secondary small mb-0">No expenses recorded during this period.</p>
                    </div>
                <?php else: ?>
                    <div class="table-responsive">
                        <table class="table-fin">
                            <thead>
                                <tr>
                                    <th>Category</th>
                                    <th>Transactions</th>
                                    <th>Share</th>
                                    <th class="text-end">Amount</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($expense_categories as $ec): ?>
                                    <tr>
                                        <td>
                                            <span class="d-inline-flex align-items-center gap-2">
                                                <i class="bi <?= e($ec['category_icon']) ?>" style="color: <?= e($ec['category_color']) ?>;"></i>
                                                <span class="fw-semibold small"><?= e($ec['category_name']) ?></span>
                                            </span>
                                        </td>
                                        <td class="small text-secondary"><?= $ec['count'] ?></td>
                                        <td>
                                            <div class="d-flex align-items-center gap-2">
                                                <div class="progress-fin flex-grow-1" style="height: 6px;">
                                                    <div class="progress-fin-bar" style="width: <?= $ec['percentage'] ?>%; background-color: <?= e($ec['category_color']) ?>;"></div>
                                                </div>
                                                <span class="smaller fw-bold text-nowrap"><?= $ec['percentage'] ?>%</span>
                                            </div>
                                        </td>
                                        <td class="text-end fw-bold text-danger text-nowrap"><?= currency($ec['total_amount']) ?></td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <!-- Income Breakdown Table -->
    <div class="col-12 col-lg-6">
        <div class="fin-card h-100 mb-0">
            <div class="fin-card-header">
                <h3 class="fin-card-title">
                    <i class="bi bi-arrow-down-left-circle text-success"></i> Income Breakdown by Category
                </h3>
            </div>
            <div class="fin-card-body p-0">
                <?php if (empty($income_categories)): ?>
                    <div class="empty-state py-4">
                        <i class="bi bi-cash-stack text-secondary fs-2 mb-2 d-block"></i>
                        <p class="text-secondary small mb-0">No income recorded during this period.</p>
                    </div>
                <?php else: ?>
                    <div class="table-responsive">
                        <table class="table-fin">
                            <thead>
                                <tr>
                                    <th>Category</th>
                                    <th>Transactions</th>
                                    <th>Share</th>
                                    <th class="text-end">Amount</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($income_categories as $ic): ?>
                                    <tr>
                                        <td>
                                            <span class="d-inline-flex align-items-center gap-2">
                                                <i class="bi <?= e($ic['category_icon']) ?>" style="color: <?= e($ic['category_color']) ?>;"></i>
                                                <span class="fw-semibold small"><?= e($ic['category_name']) ?></span>
                                            </span>
                                        </td>
                                        <td class="small text-secondary"><?= $ic['count'] ?></td>
                                        <td>
                                            <div class="d-flex align-items-center gap-2">
                                                <div class="progress-fin flex-grow-1" style="height: 6px;">
                                                    <div class="progress-fin-bar" style="width: <?= $ic['percentage'] ?>%; background-color: <?= e($ic['category_color']) ?>;"></div>
                                                </div>
                                                <span class="smaller fw-bold text-nowrap"><?= $ic['percentage'] ?>%</span>
                                            </div>
                                        </td>
                                        <td class="text-end fw-bold text-success text-nowrap"><?= currency($ic['total_amount']) ?></td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<!-- Detailed Transactions Register -->
<div class="fin-card">
    <div class="fin-card-header">
        <h3 class="fin-card-title">
            <i class="bi bi-card-checklist text-secondary"></i> Itemized Transaction Log (<?= count($transactions) ?>)
        </h3>
    </div>
    <div class="fin-card-body p-0">
        <?php if (empty($transactions)): ?>
            <div class="empty-state py-4">
                <i class="bi bi-journal-x text-secondary fs-2 mb-2 d-block"></i>
                <h4 class="empty-state-title">No Transactions Recorded</h4>
                <p class="empty-state-desc">No transaction entries fall within this date range.</p>
            </div>
        <?php else: ?>
            <div class="table-responsive">
                <table class="table-fin">
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
                                <td class="text-nowrap small text-secondary"><?= date_fmt($t['transaction_date']) ?></td>
                                <td class="fw-semibold small"><?= e($t['description']) ?></td>
                                <td>
                                    <?php if ($t['type'] === 'income'): ?>
                                        <span class="badge-fin income">Income</span>
                                    <?php elseif ($t['type'] === 'expense'): ?>
                                        <span class="badge-fin expense">Expense</span>
                                    <?php else: ?>
                                        <span class="badge-fin transfer">Transfer</span>
                                    <?php endif; ?>
                                </td>
                                <td class="small text-secondary"><?= e($t['category_name'] ?? '-') ?></td>
                                <td class="small text-secondary"><?= e($t['account_name'] ?? '-') ?></td>
                                <td class="text-end fw-bold text-nowrap <?= $t['type'] === 'income' ? 'text-success' : ($t['type'] === 'expense' ? 'text-danger' : 'text-primary') ?>">
                                    <?= $t['type'] === 'income' ? '+' : ($t['type'] === 'expense' ? '-' : '') ?>
                                    <?= currency($t['amount']) ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>
    </div>
</div>
