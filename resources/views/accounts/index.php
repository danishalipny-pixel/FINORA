<div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center mb-4 gap-3">
    <div>
        <h2 class="h4 fw-bold mb-1">Financial Accounts & Wallets</h2>
        <p class="text-secondary small mb-0">Manage your bank accounts, physical cash, digital wallets, and savings reserves.</p>
    </div>
    <div>
        <button type="button" class="btn btn-fin-primary btn-sm" data-bs-toggle="modal" data-bs-target="#addAccountModal">
            <i class="bi bi-plus-lg"></i> Add New Account
        </button>
    </div>
</div>

<!-- Net Total Assets Banner -->
<div class="fin-card mb-4 p-4 text-white" style="background: linear-gradient(135deg, #101828 0%, #1D2939 100%); border-radius: 18px;">
    <div class="row align-items-center">
        <div class="col-12 col-md-8 mb-3 mb-md-0">
            <div class="text-uppercase small fw-bold tracking-wider opacity-75 mb-1">Total Net Liquidity & Assets</div>
            <div class="display-6 fw-bold mb-1"><?= currency($totalBalance) ?></div>
            <div class="small opacity-75">
                Aggregated balance across <?= count($accounts) ?> configured accounts.
            </div>
        </div>
        <div class="col-12 col-md-4 text-md-end">
            <a href="/transactions" class="btn btn-light btn-sm fw-semibold">
                <i class="bi bi-clock-history"></i> Global Activity
            </a>
        </div>
    </div>
</div>

<!-- Accounts Grid -->
<div class="row g-4">
    <?php foreach ($accounts as $acc): ?>
        <div class="col-12 col-md-6 col-xl-4">
            <div class="fin-card h-100 mb-0 <?= $acc['is_archived'] ? 'opacity-75' : '' ?>" style="border-top: 4px solid <?= e($acc['color']) ?>;">
                <div class="fin-card-body d-flex flex-column justify-content-between h-100">
                    <div>
                        <div class="d-flex justify-content-between align-items-start mb-3">
                            <div class="d-flex align-items-center gap-2">
                                <span class="kpi-icon-badge" style="background-color: <?= e($acc['color']) ?>20; color: <?= e($acc['color']) ?>;">
                                    <?php if ($acc['type'] === 'cash'): ?>
                                        <i class="bi bi-cash-stack"></i>
                                    <?php elseif ($acc['type'] === 'savings'): ?>
                                        <i class="bi bi-piggy-bank"></i>
                                    <?php else: ?>
                                        <i class="bi bi-bank"></i>
                                    <?php endif; ?>
                                </span>
                                <div>
                                    <h3 class="h6 fw-bold mb-0 text-truncate" style="max-width: 170px;"><?= e($acc['name']) ?></h3>
                                    <span class="badge bg-secondary-subtle text-secondary small text-capitalize"><?= e($acc['type']) ?></span>
                                    <?php if ($acc['is_archived']): ?>
                                        <span class="badge bg-warning-subtle text-warning small">Archived</span>
                                    <?php endif; ?>
                                </div>
                            </div>
                            <div class="dropdown">
                                <button class="btn btn-sm btn-link text-secondary p-0" type="button" data-bs-toggle="dropdown" aria-expanded="false">
                                    <i class="bi bi-three-dots-vertical fs-5"></i>
                                </button>
                                <ul class="dropdown-menu dropdown-menu-end shadow-sm border-0 p-1">
                                    <li>
                                        <a class="dropdown-item small rounded" href="/accounts/<?= $acc['id'] ?>">
                                            <i class="bi bi-journal-text me-2"></i> View Ledger
                                        </a>
                                    </li>
                                    <li>
                                        <button class="dropdown-item small rounded" type="button" data-bs-toggle="modal" data-bs-target="#editAccountModal<?= $acc['id'] ?>">
                                            <i class="bi bi-pencil me-2"></i> Edit Account
                                        </button>
                                    </li>
                                    <li>
                                        <form method="POST" action="/accounts/<?= $acc['id'] ?>/archive" class="m-0">
                                            <?= csrf_field() ?>
                                            <button type="submit" class="dropdown-item small rounded">
                                                <i class="bi bi-archive me-2"></i> <?= $acc['is_archived'] ? 'Unarchive' : 'Archive' ?>
                                            </button>
                                        </form>
                                    </li>
                                    <?php if ($acc['transaction_count'] === 0): ?>
                                        <li><hr class="dropdown-divider my-1"></li>
                                        <li>
                                            <form method="POST" action="/accounts/<?= $acc['id'] ?>/delete" class="m-0 form-delete-confirm">
                                                <?= csrf_field() ?>
                                                <button type="submit" class="dropdown-item small rounded text-danger">
                                                    <i class="bi bi-trash me-2"></i> Delete
                                                </button>
                                            </form>
                                        </li>
                                    <?php endif; ?>
                                </ul>
                            </div>
                        </div>

                        <?php if (!empty($acc['account_number'])): ?>
                            <div class="text-secondary small font-monospace mb-2">
                                <i class="bi bi-credit-card-2-front me-1"></i> <?= e($acc['account_number']) ?>
                            </div>
                        <?php endif; ?>

                        <div class="my-3">
                            <div class="text-secondary smaller text-uppercase fw-semibold">Current Balance</div>
                            <div class="fs-4 fw-extrabold text-nowrap <?= $acc['current_balance'] >= 0 ? 'text-primary-navy' : 'text-danger' ?>">
                                <?= currency($acc['current_balance']) ?>
                            </div>
                        </div>
                    </div>

                    <div class="pt-3 border-top d-flex justify-content-between align-items-center small text-secondary">
                        <span>Opening: <?= currency($acc['opening_balance']) ?></span>
                        <a href="/accounts/<?= $acc['id'] ?>" class="text-decoration-none fw-medium text-success">
                            <?= $acc['transaction_count'] ?> transaction<?= $acc['transaction_count'] !== 1 ? 's' : '' ?> <i class="bi bi-chevron-right"></i>
                        </a>
                    </div>
                </div>
            </div>
        </div>

        <!-- Edit Account Modal -->
        <div class="modal fade" id="editAccountModal<?= $acc['id'] ?>" tabindex="-1" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered">
                <div class="modal-content">
                    <form method="POST" action="/accounts/<?= $acc['id'] ?>/edit">
                        <?= csrf_field() ?>
                        <div class="modal-header">
                            <h5 class="modal-title fw-bold">Edit Account</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                        </div>
                        <div class="modal-body">
                            <div class="mb-3">
                                <label class="form-label">Account Name <span class="text-danger">*</span></label>
                                <input type="text" class="form-control" name="name" value="<?= e($acc['name']) ?>" required>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Account Type <span class="text-danger">*</span></label>
                                <select class="form-select" name="type" required>
                                    <option value="bank" <?= $acc['type'] === 'bank' ? 'selected' : '' ?>>Bank Account</option>
                                    <option value="savings" <?= $acc['type'] === 'savings' ? 'selected' : '' ?>>Savings Account</option>
                                    <option value="cash" <?= $acc['type'] === 'cash' ? 'selected' : '' ?>>Cash Wallet</option>
                                    <option value="other" <?= $acc['type'] === 'other' ? 'selected' : '' ?>>Other Financial Account</option>
                                </select>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Account Number / IBAN</label>
                                <input type="text" class="form-control" name="account_number" value="<?= e($acc['account_number']) ?>">
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Opening Balance (PKR)</label>
                                <input type="number" step="0.01" class="form-control" name="opening_balance" value="<?= e($acc['opening_balance']) ?>" required>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Accent Color</label>
                                <input type="color" class="form-control form-control-color w-100" name="color" value="<?= e($acc['color']) ?>">
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-fin-secondary btn-sm" data-bs-dismiss="modal">Cancel</button>
                            <button type="submit" class="btn btn-fin-primary btn-sm">Save Changes</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    <?php endforeach; ?>
</div>

<!-- Add Account Modal -->
<div class="modal fade" id="addAccountModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <form method="POST" action="/accounts">
                <?= csrf_field() ?>
                <div class="modal-header">
                    <h5 class="modal-title fw-bold">Add Financial Account</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">Account Name <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" name="name" placeholder="e.g. Meezan Bank Checking" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Account Type <span class="text-danger">*</span></label>
                        <select class="form-select" name="type" required>
                            <option value="bank">Bank Account</option>
                            <option value="savings">Savings Account</option>
                            <option value="cash">Cash Wallet</option>
                            <option value="other">Other Financial Account</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Account Number / IBAN <span class="text-secondary small">(Optional)</span></label>
                        <input type="text" class="form-control" name="account_number" placeholder="e.g. PK36MEZN00010101">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Opening Balance (PKR) <span class="text-danger">*</span></label>
                        <input type="number" step="0.01" class="form-control" name="opening_balance" value="0.00" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Color Identifier</label>
                        <input type="color" class="form-control form-control-color w-100" name="color" value="#10B981">
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-fin-secondary btn-sm" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-fin-primary btn-sm">Create Account</button>
                </div>
            </form>
        </div>
    </div>
</div>
