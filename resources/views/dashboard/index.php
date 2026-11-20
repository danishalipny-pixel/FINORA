<!-- Dashboard Header / Welcome -->
<div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center mb-4 gap-3">
    <div>
        <h2 class="h4 fw-bold mb-1">Financial Intelligence Overview</h2>
        <p class="text-secondary small mb-0">Real-time snapshot of your wealth, cashflow, and spending patterns.</p>
    </div>
    <div class="d-flex gap-2">
        <a href="/reports" class="btn btn-fin-secondary btn-sm">
            <i class="bi bi-bar-chart"></i> View Full Report
        </a>
        <a href="/transactions/create" class="btn btn-fin-primary btn-sm">
            <i class="bi bi-plus-lg"></i> Record Transaction
        </a>
    </div>
</div>

<!-- 4 Key Financial Summary Cards -->
<div class="row g-3 mb-4">
    <!-- Total Balance -->
    <div class="col-12 col-sm-6 col-xl-3">
        <div class="kpi-card">
            <div class="kpi-top">
                <span class="kpi-label">Total Net Balance</span>
                <div class="kpi-icon-badge emerald">
                    <i class="bi bi-wallet2"></i>
                </div>
            </div>
            <div class="kpi-amount"><?= currency($total_balance) ?></div>
            <div class="kpi-trend">
                <span class="text-secondary">Across <?= count($accounts) ?> active account<?= count($accounts) !== 1 ? 's' : '' ?></span>
            </div>
        </div>
    </div>

    <!-- Monthly Income -->
    <div class="col-12 col-sm-6 col-xl-3">
        <div class="kpi-card">
            <div class="kpi-top">
                <span class="kpi-label">Income (<?= date('M') ?>)</span>
                <div class="kpi-icon-badge blue">
                    <i class="bi bi-arrow-down-left-circle"></i>
                </div>
            </div>
            <div class="kpi-amount"><?= currency($monthly_income) ?></div>
            <div class="kpi-trend <?= $income_growth >= 0 ? 'positive' : 'negative' ?>">
                <i class="bi bi-<?= $income_growth >= 0 ? 'arrow-up-right' : 'arrow-down-right' ?>"></i>
                <span><?= abs($income_growth) ?>% vs last month</span>
            </div>
        </div>
    </div>

    <!-- Monthly Expenses -->
    <div class="col-12 col-sm-6 col-xl-3">
        <div class="kpi-card">
            <div class="kpi-top">
                <span class="kpi-label">Expenses (<?= date('M') ?>)</span>
                <div class="kpi-icon-badge danger">
                    <i class="bi bi-arrow-up-right-circle"></i>
                </div>
            </div>
            <div class="kpi-amount"><?= currency($monthly_expense) ?></div>
            <div class="kpi-trend <?= $expense_growth <= 0 ? 'positive' : 'negative' ?>">
                <i class="bi bi-<?= $expense_growth <= 0 ? 'arrow-down-right' : 'arrow-up-right' ?>"></i>
                <span><?= abs($expense_growth) ?>% vs last month</span>
            </div>
        </div>
    </div>

    <!-- Total Savings -->
    <div class="col-12 col-sm-6 col-xl-3">
        <div class="kpi-card">
            <div class="kpi-top">
                <span class="kpi-label">Savings & Goals</span>
                <div class="kpi-icon-badge warning">
                    <i class="bi bi-piggy-bank"></i>
                </div>
            </div>
            <div class="kpi-amount"><?= currency($total_savings) ?></div>
            <div class="kpi-trend positive">
                <i class="bi bi-graph-up-arrow"></i>
                <span><?= $savings_rate ?>% monthly savings rate</span>
            </div>
        </div>
    </div>
</div>

<!-- Charts Row: Cashflow Trends & Expense Breakdown -->
<div class="row g-4 mb-4">
    <!-- Income vs Expense Trend Chart -->
    <div class="col-12 col-xl-8">
        <div class="fin-card h-100 mb-0">
            <div class="fin-card-header">
                <h3 class="fin-card-title">
                    <i class="bi bi-graph-up text-success"></i> Cash Flow & Growth (Past 6 Months)
                </h3>
                <span class="badge badge-fin neutral">Income vs Expenses</span>
            </div>
            <div class="fin-card-body">
                <div style="height: 300px; position: relative;">
                    <canvas id="cashflowChart"></canvas>
                </div>
            </div>
        </div>
    </div>

    <!-- Expense Category Breakdown Doughnut -->
    <div class="col-12 col-xl-4">
        <div class="fin-card h-100 mb-0">
            <div class="fin-card-header">
                <h3 class="fin-card-title">
                    <i class="bi bi-pie-chart text-primary"></i> <?= date('F') ?> Spending
                </h3>
                <a href="/reports" class="small text-decoration-none">Details</a>
            </div>
            <div class="fin-card-body d-flex flex-column align-items-center justify-content-center">
                <div style="height: 250px; width: 100%; position: relative;">
                    <canvas id="categoryDoughnutChart"></canvas>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Middle Row: Monthly Budgets & Savings Goals -->
<div class="row g-4 mb-4">
    <!-- Monthly Budget Utilization -->
    <div class="col-12 col-lg-6">
        <div class="fin-card h-100 mb-0">
            <div class="fin-card-header">
                <h3 class="fin-card-title">
                    <i class="bi bi-bullseye text-warning"></i> <?= date('F') ?> Budget Utilization
                </h3>
                <a href="/budgets" class="small text-decoration-none">Manage Budgets</a>
            </div>
            <div class="fin-card-body">
                <?php if (empty($budgets)): ?>
                    <div class="empty-state py-3">
                        <i class="bi bi-pie-chart text-secondary fs-2 mb-2 d-block"></i>
                        <h4 class="empty-state-title fs-6">No Budgets Defined</h4>
                        <p class="empty-state-desc">Set category spending limits to keep your expenses in control.</p>
                        <a href="/budgets" class="btn btn-fin-primary btn-sm">Set Budget</a>
                    </div>
                <?php else: ?>
                    <div class="d-flex flex-column gap-3">
                        <?php foreach ($budgets as $b): ?>
                            <div>
                                <div class="d-flex justify-content-between align-items-center mb-1">
                                    <span class="fw-semibold small d-flex align-items-center gap-2">
                                        <i class="bi <?= e($b['category_icon']) ?>" style="color: <?= e($b['category_color']) ?>;"></i>
                                        <?= e($b['category_name']) ?>
                                    </span>
                                    <span class="small text-secondary">
                                        <strong><?= currency($b['spent_amount']) ?></strong> / <?= currency($b['budget_limit']) ?>
                                        <span class="badge ms-1 <?= $b['status'] === 'exceeded' ? 'bg-danger' : ($b['status'] === 'warning' ? 'bg-warning text-dark' : 'bg-success') ?>">
                                            <?= $b['percentage'] ?>%
                                        </span>
                                    </span>
                                </div>
                                <div class="progress-fin">
                                    <div class="progress-fin-bar <?= $b['status'] === 'exceeded' ? 'bg-danger' : ($b['status'] === 'warning' ? 'bg-warning' : 'bg-success') ?>"
                                         style="width: <?= min(100, $b['percentage']) ?>%;"></div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <!-- Savings Goals -->
    <div class="col-12 col-lg-6">
        <div class="fin-card h-100 mb-0">
            <div class="fin-card-header">
                <h3 class="fin-card-title">
                    <i class="bi bi-trophy text-success"></i> Active Savings Goals
                </h3>
                <a href="/savings" class="small text-decoration-none">View All Goals</a>
            </div>
            <div class="fin-card-body">
                <?php if (empty($savings_goals)): ?>
                    <div class="empty-state py-3">
                        <i class="bi bi-piggy-bank text-secondary fs-2 mb-2 d-block"></i>
                        <h4 class="empty-state-title fs-6">No Savings Goals Active</h4>
                        <p class="empty-state-desc">Define targets for home purchase, emergency funds, or travel.</p>
                        <a href="/savings" class="btn btn-fin-primary btn-sm">Create Goal</a>
                    </div>
                <?php else: ?>
                    <div class="d-flex flex-column gap-3">
                        <?php foreach ($savings_goals as $g): ?>
                            <div>
                                <div class="d-flex justify-content-between align-items-center mb-1">
                                    <span class="fw-semibold small"><?= e($g['name']) ?></span>
                                    <span class="small text-secondary">
                                        <strong><?= currency($g['current_amount']) ?></strong> / <?= currency($g['target_amount']) ?>
                                        <span class="badge ms-1 bg-success"><?= $g['percentage'] ?>%</span>
                                    </span>
                                </div>
                                <div class="progress-fin">
                                    <div class="progress-fin-bar" style="width: <?= $g['percentage'] ?>%; background-color: <?= e($g['color'] ?? '#10B981') ?>;"></div>
                                </div>
                                <div class="d-flex justify-content-between mt-1 text-secondary" style="font-size: 11px;">
                                    <span>Target: <?= date_fmt($g['target_date']) ?></span>
                                    <span><?= $g['days_remaining'] >= 0 ? $g['days_remaining'] . ' days left' : 'Goal date reached' ?></span>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<!-- Bottom Row: Recent Transactions -->
<div class="fin-card">
    <div class="fin-card-header">
        <h3 class="fin-card-title">
            <i class="bi bi-clock-history text-secondary"></i> Recent Transactions
        </h3>
        <a href="/transactions" class="btn btn-fin-secondary btn-sm">
            <span>View All</span>
            <i class="bi bi-arrow-right"></i>
        </a>
    </div>
    <div class="fin-card-body p-0">
        <?php if (empty($recent_transactions)): ?>
            <div class="empty-state">
                <div class="empty-state-icon">
                    <i class="bi bi-receipt"></i>
                </div>
                <h4 class="empty-state-title">No Transactions Recorded Yet</h4>
                <p class="empty-state-desc">Begin logging your daily income, bills, and spending to see your analytics flourish.</p>
                <a href="/transactions/create" class="btn btn-fin-primary">
                    <i class="bi bi-plus-circle"></i> Add First Transaction
                </a>
            </div>
        <?php else: ?>
            <div class="table-responsive">
                <table class="table-fin">
                    <thead>
                        <tr>
                            <th>Date</th>
                            <th>Description</th>
                            <th>Category</th>
                            <th>Account</th>
                            <th>Method</th>
                            <th class="text-end">Amount</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($recent_transactions as $tx): ?>
                            <tr>
                                <td class="text-nowrap text-secondary small"><?= date_fmt($tx['transaction_date']) ?></td>
                                <td class="fw-semibold">
                                    <div class="text-truncate" style="max-width: 260px;"><?= e($tx['description']) ?></div>
                                </td>
                                <td>
                                    <?php if (!empty($tx['category_name'])): ?>
                                        <span class="badge-fin neutral">
                                            <i class="bi <?= e($tx['category_icon']) ?>" style="color: <?= e($tx['category_color']) ?>;"></i>
                                            <?= e($tx['category_name']) ?>
                                        </span>
                                    <?php else: ?>
                                        <span class="text-muted small">Transfer</span>
                                    <?php endif; ?>
                                </td>
                                <td class="text-secondary small"><?= e($tx['account_name'] ?? '-') ?></td>
                                <td class="text-secondary small"><?= e($tx['payment_method']) ?></td>
                                <td class="text-end fw-bold text-nowrap <?= $tx['type'] === 'income' ? 'text-success' : ($tx['type'] === 'expense' ? 'text-danger' : 'text-primary') ?>">
                                    <?= $tx['type'] === 'income' ? '+' : ($tx['type'] === 'expense' ? '-' : '') ?>
                                    <?= currency($tx['amount']) ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>
    </div>
</div>

<!-- Inject dynamic chart datasets into frontend scope -->
<script>
    window.finoraCashflowData = <?= json_encode($chart_cashflow) ?>;
    window.finoraCategoryData = <?= json_encode($chart_category_expenses) ?>;
</script>
