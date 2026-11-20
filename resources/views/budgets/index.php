<div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center mb-4 gap-3">
    <div>
        <h2 class="h4 fw-bold mb-1">Monthly Budgets & Limits</h2>
        <p class="text-secondary small mb-0">Control spending per category and prevent overdrafts with automated threshold tracking.</p>
    </div>
    <div class="d-flex align-items-center gap-2">
        <!-- Month / Year Selector Form -->
        <form method="GET" action="/budgets" class="d-flex align-items-center gap-2 m-0">
            <select name="month" class="form-select form-select-sm" onchange="this.form.submit()">
                <?php for ($m = 1; $m <= 12; $m++): ?>
                    <option value="<?= $m ?>" <?= $selectedMonth == $m ? 'selected' : '' ?>>
                        <?= date('F', mktime(0, 0, 0, $m, 10)) ?>
                    </option>
                <?php endfor; ?>
            </select>
            <select name="year" class="form-select form-select-sm" onchange="this.form.submit()">
                <?php for ($y = date('Y') - 1; $y <= date('Y') + 2; $y++): ?>
                    <option value="<?= $y ?>" <?= $selectedYear == $y ? 'selected' : '' ?>><?= $y ?></option>
                <?php endfor; ?>
            </select>
        </form>

        <button type="button" class="btn btn-fin-primary btn-sm text-nowrap" data-bs-toggle="modal" data-bs-target="#setBudgetModal">
            <i class="bi bi-plus-lg"></i> Set Budget
        </button>
    </div>
</div>

<!-- Monthly Budget KPI Cards -->
<div class="row g-3 mb-4">
    <div class="col-12 col-sm-6 col-xl-3">
        <div class="kpi-card">
            <div class="kpi-label">Total Monthly Budget</div>
            <div class="kpi-amount fs-3 text-primary"><?= currency($summary['total_limit']) ?></div>
            <div class="small text-secondary">Across <?= $summary['items_count'] ?> budgeted categories</div>
        </div>
    </div>
    <div class="col-12 col-sm-6 col-xl-3">
        <div class="kpi-card">
            <div class="kpi-label">Actual Spent</div>
            <div class="kpi-amount fs-3 text-danger"><?= currency($summary['total_spent']) ?></div>
            <div class="small text-secondary"><?= $summary['percentage'] ?>% overall utilization</div>
        </div>
    </div>
    <div class="col-12 col-sm-6 col-xl-3">
        <div class="kpi-card">
            <div class="kpi-label">Remaining Safe Budget</div>
            <div class="kpi-amount fs-3 <?= $summary['total_remaining'] >= 0 ? 'text-success' : 'text-danger' ?>">
                <?= currency($summary['total_remaining']) ?>
            </div>
            <div class="small text-secondary"><?= $summary['total_remaining'] >= 0 ? 'Available to spend' : 'Budget overrun' ?></div>
        </div>
    </div>
    <div class="col-12 col-sm-6 col-xl-3">
        <div class="kpi-card">
            <div class="kpi-label">Risk & Warnings</div>
            <div class="kpi-amount fs-3 <?= ($summary['warning_count'] + $summary['exceeded_count']) > 0 ? 'text-warning' : 'text-success' ?>">
                <?= $summary['warning_count'] + $summary['exceeded_count'] ?>
            </div>
            <div class="small text-secondary">
                <?= $summary['exceeded_count'] ?> exceeded, <?= $summary['warning_count'] ?> near limit
            </div>
        </div>
    </div>
</div>

<!-- Budget Items List -->
<div class="fin-card">
    <div class="fin-card-header">
        <h3 class="fin-card-title">
            <i class="bi bi-pie-chart text-secondary"></i> Category Allocations for <?= date('F Y', mktime(0, 0, 0, $selectedMonth, 10, $selectedYear)) ?>
        </h3>
    </div>
    <div class="fin-card-body p-0">
        <?php if (empty($budgets)): ?>
            <div class="empty-state">
                <i class="bi bi-pie-chart text-secondary fs-2 mb-2 d-block"></i>
                <h4 class="empty-state-title">No Budgets Configured for this Month</h4>
                <p class="empty-state-desc">Set limits for your primary spending categories like Groceries, Rent, Utilities, or Fuel.</p>
                <button type="button" class="btn btn-fin-primary btn-sm" data-bs-toggle="modal" data-bs-target="#setBudgetModal">
                    Set First Budget
                </button>
            </div>
        <?php else: ?>
            <div class="table-responsive">
                <table class="table-fin">
                    <thead>
                        <tr>
                            <th>Category</th>
                            <th>Budget Limit</th>
                            <th>Spent Amount</th>
                            <th>Remaining</th>
                            <th style="min-width: 180px;">Utilization Progress</th>
                            <th>Status</th>
                            <th class="text-end">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($budgets as $b): ?>
                            <tr>
                                <td class="fw-semibold">
                                    <span class="d-inline-flex align-items-center gap-2">
                                        <i class="bi <?= e($b['category_icon']) ?>" style="color: <?= e($b['category_color']) ?>; font-size: 18px;"></i>
                                        <?= e($b['category_name']) ?>
                                    </span>
                                </td>
                                <td class="fw-semibold"><?= currency($b['budget_limit']) ?></td>
                                <td class="fw-bold text-danger"><?= currency($b['spent_amount']) ?></td>
                                <td class="fw-semibold <?= $b['remaining'] >= 0 ? 'text-success' : 'text-danger' ?>">
                                    <?= currency($b['remaining']) ?>
                                </td>
                                <td>
                                    <div class="d-flex align-items-center gap-2">
                                        <div class="progress-fin flex-grow-1">
                                            <div class="progress-fin-bar <?= $b['status'] === 'exceeded' ? 'bg-danger' : ($b['status'] === 'warning' ? 'bg-warning' : 'bg-success') ?>"
                                                 style="width: <?= min(100, $b['percentage']) ?>%;"></div>
                                        </div>
                                        <span class="small fw-bold text-nowrap"><?= $b['percentage'] ?>%</span>
                                    </div>
                                </td>
                                <td>
                                    <?php if ($b['status'] === 'exceeded'): ?>
                                        <span class="badge bg-danger-subtle text-danger border border-danger-subtle small">
                                            <i class="bi bi-exclamation-octagon me-1"></i> Exceeded
                                        </span>
                                    <?php elseif ($b['status'] === 'warning'): ?>
                                        <span class="badge bg-warning-subtle text-warning border border-warning-subtle small">
                                            <i class="bi bi-exclamation-triangle me-1"></i> Near Limit (>80%)
                                        </span>
                                    <?php else: ?>
                                        <span class="badge bg-success-subtle text-success border border-success-subtle small">
                                            <i class="bi bi-check-circle me-1"></i> On Track
                                        </span>
                                    <?php endif; ?>
                                </td>
                                <td class="text-end text-nowrap">
                                    <button type="button" class="btn btn-sm btn-fin-secondary py-1 px-2" data-bs-toggle="modal" data-bs-target="#editBudgetModal<?= $b['id'] ?>" title="Edit Limit">
                                        <i class="bi bi-pencil"></i>
                                    </button>
                                    <form method="POST" action="/budgets/<?= $b['id'] ?>/delete" class="d-inline form-delete-confirm m-0">
                                        <?= csrf_field() ?>
                                        <button type="submit" class="btn btn-sm btn-fin-outline-danger py-1 px-2" title="Delete">
                                            <i class="bi bi-trash"></i>
                                        </button>
                                    </form>
                                </td>
                            </tr>

                            <!-- Edit Budget Modal -->
                            <div class="modal fade" id="editBudgetModal<?= $b['id'] ?>" tabindex="-1" aria-hidden="true">
                                <div class="modal-dialog modal-dialog-centered">
                                    <div class="modal-content">
                                        <form method="POST" action="/budgets">
                                            <?= csrf_field() ?>
                                            <input type="hidden" name="category_id" value="<?= $b['category_id'] ?>">
                                            <input type="hidden" name="month" value="<?= $selectedMonth ?>">
                                            <input type="hidden" name="year" value="<?= $selectedYear ?>">

                                            <div class="modal-header">
                                                <h5 class="modal-title fw-bold">Update Budget: <?= e($b['category_name']) ?></h5>
                                                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                            </div>
                                            <div class="modal-body">
                                                <div class="mb-3">
                                                    <label class="form-label">Monthly Limit (PKR) <span class="text-danger">*</span></label>
                                                    <div class="input-group">
                                                        <span class="input-group-text">Rs.</span>
                                                        <input type="number" step="0.01" min="0.01" class="form-control" name="amount" value="<?= e($b['budget_limit']) ?>" required>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="modal-footer">
                                                <button type="button" class="btn btn-fin-secondary btn-sm" data-bs-dismiss="modal">Cancel</button>
                                                <button type="submit" class="btn btn-fin-primary btn-sm">Update Limit</button>
                                            </div>
                                        </form>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>
    </div>
</div>

<!-- Set New Budget Modal -->
<div class="modal fade" id="setBudgetModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <form method="POST" action="/budgets">
                <?= csrf_field() ?>
                <input type="hidden" name="month" value="<?= $selectedMonth ?>">
                <input type="hidden" name="year" value="<?= $selectedYear ?>">

                <div class="modal-header">
                    <h5 class="modal-title fw-bold">Set Category Budget Limit</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">Expense Category <span class="text-danger">*</span></label>
                        <select class="form-select" name="category_id" required>
                            <option value="">Select Category</option>
                            <?php foreach ($expenseCategories as $ec): ?>
                                <option value="<?= $ec['id'] ?>"><?= e($ec['name']) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Monthly Spending Limit (PKR) <span class="text-danger">*</span></label>
                        <div class="input-group">
                            <span class="input-group-text">Rs.</span>
                            <input type="number" step="0.01" min="0.01" class="form-control" name="amount" placeholder="e.g. 35000.00" required>
                        </div>
                    </div>
                    <div class="small text-secondary">
                        Applies to <strong><?= date('F Y', mktime(0, 0, 0, $selectedMonth, 10, $selectedYear)) ?></strong>.
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-fin-secondary btn-sm" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-fin-primary btn-sm">Save Budget Limit</button>
                </div>
            </form>
        </div>
    </div>
</div>
