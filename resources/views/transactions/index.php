<div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center mb-4 gap-3">
    <div>
        <h2 class="h4 fw-bold mb-1">Financial Transactions</h2>
        <p class="text-secondary small mb-0">Track and audit every income, expense, and account transfer.</p>
    </div>
    <div class="d-flex flex-wrap gap-2">
        <a href="/transactions/export/csv<?= !empty($_SERVER['QUERY_STRING']) ? '?' . htmlspecialchars($_SERVER['QUERY_STRING']) : '' ?>" class="btn btn-fin-secondary btn-sm">
            <i class="bi bi-file-earmark-spreadsheet"></i> Export CSV
        </a>
        <a href="/transactions/import" class="btn btn-fin-secondary btn-sm">
            <i class="bi bi-upload"></i> Import CSV
        </a>
        <a href="/transactions/create" class="btn btn-fin-primary btn-sm">
            <i class="bi bi-plus-lg"></i> Add Transaction
        </a>
    </div>
</div>

<!-- Filter & Search Toolbar -->
<div class="fin-card mb-4">
    <div class="fin-card-body p-3">
        <form method="GET" action="/transactions" class="row g-2 align-items-end">
            <!-- Search Text -->
            <div class="col-12 col-md-3">
                <label for="q" class="form-label small mb-1">Search</label>
                <div class="input-group input-group-sm">
                    <span class="input-group-text"><i class="bi bi-search"></i></span>
                    <input type="text" class="form-control" id="q" name="q" value="<?= e($filters['q'] ?? '') ?>" placeholder="Description, notes, ref...">
                </div>
            </div>

            <!-- Transaction Type -->
            <div class="col-6 col-md-2">
                <label for="type" class="form-label small mb-1">Type</label>
                <select class="form-select form-select-sm" id="type" name="type">
                    <option value="">All Types</option>
                    <option value="income" <?= ($filters['type'] ?? '') === 'income' ? 'selected' : '' ?>>Income</option>
                    <option value="expense" <?= ($filters['type'] ?? '') === 'expense' ? 'selected' : '' ?>>Expense</option>
                    <option value="transfer" <?= ($filters['type'] ?? '') === 'transfer' ? 'selected' : '' ?>>Transfer</option>
                </select>
            </div>

            <!-- Category -->
            <div class="col-6 col-md-2">
                <label for="category_id" class="form-label small mb-1">Category</label>
                <select class="form-select form-select-sm" id="category_id" name="category_id">
                    <option value="">All Categories</option>
                    <?php foreach ($categories as $cat): ?>
                        <option value="<?= $cat['id'] ?>" <?= ($filters['category_id'] ?? '') == $cat['id'] ? 'selected' : '' ?>>
                            <?= e($cat['name']) ?> (<?= ucfirst($cat['type']) ?>)
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <!-- Account -->
            <div class="col-6 col-md-2">
                <label for="account_id" class="form-label small mb-1">Account</label>
                <select class="form-select form-select-sm" id="account_id" name="account_id">
                    <option value="">All Accounts</option>
                    <?php foreach ($accounts as $acc): ?>
                        <option value="<?= $acc['id'] ?>" <?= ($filters['account_id'] ?? '') == $acc['id'] ? 'selected' : '' ?>>
                            <?= e($acc['name']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <!-- Sort -->
            <div class="col-6 col-md-2">
                <label for="sort" class="form-label small mb-1">Sort By</label>
                <select class="form-select form-select-sm" id="sort" name="sort">
                    <option value="date_desc" <?= ($filters['sort'] ?? '') === 'date_desc' ? 'selected' : '' ?>>Date (Newest)</option>
                    <option value="date_asc" <?= ($filters['sort'] ?? '') === 'date_asc' ? 'selected' : '' ?>>Date (Oldest)</option>
                    <option value="amount_desc" <?= ($filters['sort'] ?? '') === 'amount_desc' ? 'selected' : '' ?>>Amount (Highest)</option>
                    <option value="amount_asc" <?= ($filters['sort'] ?? '') === 'amount_asc' ? 'selected' : '' ?>>Amount (Lowest)</option>
                </select>
            </div>

            <!-- Action Buttons -->
            <div class="col-12 col-md-1 d-flex gap-1">
                <button type="submit" class="btn btn-fin-primary btn-sm w-100" title="Apply Filter">
                    <i class="bi bi-funnel"></i>
                </button>
                <a href="/transactions" class="btn btn-fin-secondary btn-sm" title="Clear Filters">
                    <i class="bi bi-x-lg"></i>
                </a>
            </div>
        </form>
    </div>
</div>

<!-- Transactions Table Card -->
<div class="fin-card">
    <div class="fin-card-body p-0">
        <?php if (empty($transactions)): ?>
            <div class="empty-state">
                <div class="empty-state-icon">
                    <i class="bi bi-search"></i>
                </div>
                <h4 class="empty-state-title">No Transactions Found</h4>
                <p class="empty-state-desc">No transactions matched your search or filters. Try adjusting your parameters or record a new entry.</p>
                <a href="/transactions/create" class="btn btn-fin-primary btn-sm">Add New Transaction</a>
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
                            <th>Account</th>
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
                                <td class="text-nowrap small text-secondary">
                                    <?= date_fmt($tx['transaction_date']) ?>
                                </td>
                                <td>
                                    <div class="fw-semibold text-truncate" style="max-width: 250px;"><?= e($tx['description']) ?></div>
                                    <?php if (!empty($tx['reference_id'])): ?>
                                        <div class="text-muted smaller">Ref: <?= e($tx['reference_id']) ?></div>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <?php if (!empty($tx['category_name'])): ?>
                                        <span class="badge-fin neutral">
                                            <i class="bi <?= e($tx['category_icon']) ?>" style="color: <?= e($tx['category_color']) ?>;"></i>
                                            <?= e($tx['category_name']) ?>
                                        </span>
                                    <?php else: ?>
                                        <span class="text-muted small">Account Transfer</span>
                                    <?php endif; ?>
                                </td>
                                <td class="small">
                                    <?php if ($tx['type'] === 'transfer'): ?>
                                        <span><?= e($tx['account_name']) ?> <i class="bi bi-arrow-right text-muted"></i> <?= e($tx['destination_account_name'] ?? 'Account') ?></span>
                                    <?php else: ?>
                                        <span><?= e($tx['account_name'] ?? '-') ?></span>
                                    <?php endif; ?>
                                </td>
                                <td class="small text-secondary"><?= e($tx['payment_method']) ?></td>
                                <td class="text-end fw-bold text-nowrap <?= $tx['type'] === 'income' ? 'text-success' : ($tx['type'] === 'expense' ? 'text-danger' : 'text-primary') ?>">
                                    <?= $tx['type'] === 'income' ? '+' : ($tx['type'] === 'expense' ? '-' : '') ?>
                                    <?= currency($tx['amount']) ?>
                                </td>
                                <td class="text-end text-nowrap">
                                    <div class="d-inline-flex gap-1">
                                        <a href="/transactions/<?= $tx['id'] ?>/edit" class="btn btn-sm btn-fin-secondary py-1 px-2" title="Edit">
                                            <i class="bi bi-pencil"></i>
                                        </a>
                                        <form method="POST" action="/transactions/<?= $tx['id'] ?>/delete" class="d-inline form-delete-confirm m-0">
                                            <?= csrf_field() ?>
                                            <button type="submit" class="btn btn-sm btn-fin-outline-danger py-1 px-2" title="Delete">
                                                <i class="bi bi-trash"></i>
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>

            <!-- Pagination Bar -->
            <?php if ($pagination['last_page'] > 1): ?>
                <div class="p-3 border-top d-flex justify-content-between align-items-center">
                    <span class="small text-secondary">
                        Showing <?= count($transactions) ?> of <?= $pagination['total'] ?> transactions (Page <?= $pagination['current_page'] ?> of <?= $pagination['last_page'] ?>)
                    </span>
                    <nav aria-label="Transactions navigation">
                        <ul class="pagination pagination-sm m-0">
                            <?php if ($pagination['current_page'] > 1): ?>
                                <li class="page-item">
                                    <a class="page-link" href="?<?= http_build_query(array_merge($filters, ['page' => $pagination['current_page'] - 1])) ?>">Previous</a>
                                </li>
                            <?php endif; ?>

                            <?php for ($p = 1; $p <= $pagination['last_page']; $p++): ?>
                                <?php if ($p == 1 || $p == $pagination['last_page'] || abs($p - $pagination['current_page']) <= 2): ?>
                                    <li class="page-item <?= $p == $pagination['current_page'] ? 'active' : '' ?>">
                                        <a class="page-link" href="?<?= http_build_query(array_merge($filters, ['page' => $p])) ?>"><?= $p ?></a>
                                    </li>
                                <?php endif; ?>
                            <?php endfor; ?>

                            <?php if ($pagination['current_page'] < $pagination['last_page']): ?>
                                <li class="page-item">
                                    <a class="page-link" href="?<?= http_build_query(array_merge($filters, ['page' => $pagination['current_page'] + 1])) ?>">Next</a>
                                </li>
                            <?php endif; ?>
                        </ul>
                    </nav>
                </div>
            <?php endif; ?>
        <?php endif; ?>
    </div>
</div>
