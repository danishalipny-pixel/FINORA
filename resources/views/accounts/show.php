<div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center mb-4 gap-3">
    <div>
        <div class="d-flex align-items-center gap-2 mb-1">
            <h2 class="h4 fw-bold mb-0"><?= e($account['name']) ?></h2>
            <span class="badge bg-secondary-subtle text-secondary small text-capitalize"><?= e($account['type']) ?></span>
            <?php if ($account['is_archived']): ?>
                <span class="badge bg-warning-subtle text-warning small">Archived</span>
            <?php endif; ?>
        </div>
        <p class="text-secondary small mb-0">Ledger history and reconciled balance for this account.</p>
    </div>
    <div class="d-flex gap-2">
        <a href="/accounts" class="btn btn-fin-secondary btn-sm">
            <i class="bi bi-arrow-left"></i> All Accounts
        </a>
        <a href="/transactions/create?account_id=<?= $account['id'] ?>" class="btn btn-fin-primary btn-sm">
            <i class="bi bi-plus-lg"></i> Add Transaction
        </a>
    </div>
</div>

<!-- Account Summary Cards -->
<div class="row g-3 mb-4">
    <div class="col-12 col-md-4">
        <div class="kpi-card" style="border-left: 4px solid <?= e($account['color']) ?>;">
            <div class="kpi-label">Current Calculated Balance</div>
            <div class="kpi-amount fs-3"><?= currency($account['current_balance']) ?></div>
            <div class="small text-secondary">Reconciled real-time balance</div>
        </div>
    </div>
    <div class="col-12 col-md-4">
        <div class="kpi-card">
            <div class="kpi-label">Opening Balance</div>
            <div class="kpi-amount fs-3 text-secondary"><?= currency($account['opening_balance']) ?></div>
            <div class="small text-secondary">Initial starting capital</div>
        </div>
    </div>
    <div class="col-12 col-md-4">
        <div class="kpi-card">
            <div class="kpi-label">Transaction Count</div>
            <div class="kpi-amount fs-3 text-primary"><?= $account['transaction_count'] ?></div>
            <div class="small text-secondary">Total recorded operations</div>
        </div>
    </div>
</div>

<!-- Account Transactions Ledger -->
<div class="fin-card">
    <div class="fin-card-header">
        <h3 class="fin-card-title">
            <i class="bi bi-journal-text text-secondary"></i> Account Ledger & Transactions
        </h3>
    </div>
    <div class="fin-card-body p-0">
        <?php if (empty($transactions)): ?>
            <div class="empty-state">
                <i class="bi bi-receipt text-secondary fs-2 mb-2 d-block"></i>
                <h4 class="empty-state-title">No Transactions Recorded on this Account</h4>
                <p class="empty-state-desc">Transactions logged using this account will appear here in chronological order.</p>
                <a href="/transactions/create?account_id=<?= $account['id'] ?>" class="btn btn-fin-primary btn-sm">Record Entry</a>
            </div>
        <?php else: ?>
            <div class="table-responsive">
                <table class="table-fin">
                    <thead>
                        <tr>
                            <th>Type</th>
                            <th>Date</th>
                            <th>Description</th>
                            <th>Category</th>
                            <th>Method</th>
                            <th class="text-end">Amount</th>
                            <th class="text-end">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($transactions as $tx): ?>
                            <tr>
                                <td>
                                    <?php if ($tx['type'] === 'income'): ?>
                                        <span class="badge-fin income"><i class="bi bi-arrow-down-left"></i> Income</span>
                                    <?php elseif ($tx['type'] === 'expense'): ?>
                                        <span class="badge-fin expense"><i class="bi bi-arrow-up-right"></i> Expense</span>
                                    <?php else: ?>
                                        <span class="badge-fin transfer"><i class="bi bi-arrow-left-right"></i> Transfer</span>
                                    <?php endif; ?>
                                </td>
                                <td class="text-nowrap small text-secondary"><?= date_fmt($tx['transaction_date']) ?></td>
                                <td class="fw-semibold">
                                    <div class="text-truncate" style="max-width: 250px;"><?= e($tx['description']) ?></div>
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
                                <td class="small text-secondary"><?= e($tx['payment_method']) ?></td>
                                <td class="text-end fw-bold text-nowrap <?= $tx['type'] === 'income' ? 'text-success' : ($tx['type'] === 'expense' ? 'text-danger' : 'text-primary') ?>">
                                    <?= $tx['type'] === 'income' ? '+' : ($tx['type'] === 'expense' ? '-' : '') ?>
                                    <?= currency($tx['amount']) ?>
                                </td>
                                <td class="text-end text-nowrap">
                                    <a href="/transactions/<?= $tx['id'] ?>/edit" class="btn btn-sm btn-fin-secondary py-1 px-2" title="Edit">
                                        <i class="bi bi-pencil"></i>
                                    </a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>

            <?php if ($pagination['last_page'] > 1): ?>
                <div class="p-3 border-top d-flex justify-content-between align-items-center">
                    <span class="small text-secondary">
                        Showing <?= count($transactions) ?> of <?= $pagination['total'] ?> transactions
                    </span>
                    <nav>
                        <ul class="pagination pagination-sm m-0">
                            <?php for ($p = 1; $p <= $pagination['last_page']; $p++): ?>
                                <li class="page-item <?= $p == $pagination['current_page'] ? 'active' : '' ?>">
                                    <a class="page-link" href="?page=<?= $p ?>"><?= $p ?></a>
                                </li>
                            <?php endfor; ?>
                        </ul>
                    </nav>
                </div>
            <?php endif; ?>
        <?php endif; ?>
    </div>
</div>
