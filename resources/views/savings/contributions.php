<div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center mb-4 gap-3">
    <div>
        <div class="d-flex align-items-center gap-2 mb-1">
            <h2 class="h4 fw-bold mb-0"><?= e($goal['name']) ?></h2>
            <span class="badge <?= $goal['status'] === 'completed' ? 'bg-success' : 'bg-primary' ?> small text-capitalize">
                <?= str_replace('_', ' ', $goal['status']) ?>
            </span>
        </div>
        <p class="text-secondary small mb-0">Record deposits, track contribution milestones, and review funding history.</p>
    </div>
    <div class="d-flex gap-2">
        <a href="/savings" class="btn btn-fin-secondary btn-sm">
            <i class="bi bi-arrow-left"></i> All Goals
        </a>
        <button type="button" class="btn btn-fin-primary btn-sm" data-bs-toggle="modal" data-bs-target="#addContributionModal">
            <i class="bi bi-plus-lg"></i> Add Contribution
        </button>
    </div>
</div>

<!-- Progress KPI Panel -->
<div class="fin-card mb-4" style="border-left: 5px solid <?= e($goal['color'] ?? '#10B981') ?>;">
    <div class="fin-card-body">
        <div class="row g-3 align-items-center mb-3">
            <div class="col-12 col-md-4">
                <span class="text-secondary small text-uppercase fw-semibold d-block">Current Saved Balance</span>
                <span class="fs-3 fw-extrabold text-success"><?= currency($goal['current_amount']) ?></span>
            </div>
            <div class="col-12 col-md-4">
                <span class="text-secondary small text-uppercase fw-semibold d-block">Target Capital</span>
                <span class="fs-3 fw-bold"><?= currency($goal['target_amount']) ?></span>
            </div>
            <div class="col-12 col-md-4">
                <span class="text-secondary small text-uppercase fw-semibold d-block">Target Due Date</span>
                <span class="fs-4 fw-bold text-secondary"><?= date_fmt($goal['target_date']) ?></span>
                <span class="small text-muted d-block"><?= $goal['days_remaining'] >= 0 ? $goal['days_remaining'] . ' days remaining' : 'Due date reached' ?></span>
            </div>
        </div>

        <div>
            <div class="d-flex justify-content-between mb-1 small fw-bold">
                <span>Progress: <?= $goal['percentage'] ?>%</span>
                <span>Remaining: <?= currency(max(0, $goal['target_amount'] - $goal['current_amount'])) ?></span>
            </div>
            <div class="progress-fin" style="height: 12px;">
                <div class="progress-fin-bar" style="width: <?= $goal['percentage'] ?>%; background-color: <?= e($goal['color'] ?? '#10B981') ?>;"></div>
            </div>
        </div>
    </div>
</div>

<!-- Contributions History Ledger -->
<div class="fin-card">
    <div class="fin-card-header">
        <h3 class="fin-card-title">
            <i class="bi bi-clock-history text-secondary"></i> Contribution Deposit History
        </h3>
    </div>
    <div class="fin-card-body p-0">
        <?php if (empty($contributions)): ?>
            <div class="empty-state">
                <i class="bi bi-piggy-bank text-secondary fs-2 mb-2 d-block"></i>
                <h4 class="empty-state-title">No Contributions Logged Yet</h4>
                <p class="empty-state-desc">Make your first deposit contribution towards this goal today.</p>
                <button type="button" class="btn btn-fin-primary btn-sm" data-bs-toggle="modal" data-bs-target="#addContributionModal">
                    Add First Contribution
                </button>
            </div>
        <?php else: ?>
            <div class="table-responsive">
                <table class="table-fin">
                    <thead>
                        <tr>
                            <th>Deposit Date</th>
                            <th>Source Account</th>
                            <th>Notes / Memo</th>
                            <th class="text-end">Amount (PKR)</th>
                            <th class="text-end">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($contributions as $c): ?>
                            <tr>
                                <td class="text-nowrap small text-secondary"><?= date_fmt($c['contribution_date']) ?></td>
                                <td class="small"><?= e($c['account_name'] ?? 'Direct Deposit') ?></td>
                                <td class="small text-secondary"><?= e($c['notes'] ?? '-') ?></td>
                                <td class="text-end fw-bold text-success text-nowrap">+<?= currency($c['amount']) ?></td>
                                <td class="text-end text-nowrap">
                                    <form method="POST" action="/savings/<?= $goal['id'] ?>/contributions/<?= $c['id'] ?>/delete" class="d-inline form-delete-confirm m-0">
                                        <?= csrf_field() ?>
                                        <button type="submit" class="btn btn-sm btn-fin-outline-danger py-1 px-2" title="Delete Contribution">
                                            <i class="bi bi-trash"></i>
                                        </button>
                                    </form>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>
    </div>
</div>

<!-- Add Contribution Modal -->
<div class="modal fade" id="addContributionModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <form method="POST" action="/savings/<?= $goal['id'] ?>/contributions">
                <?= csrf_field() ?>
                <div class="modal-header">
                    <h5 class="modal-title fw-bold">Add Deposit to <?= e($goal['name']) ?></h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">Contribution Amount (PKR) <span class="text-danger">*</span></label>
                        <div class="input-group">
                            <span class="input-group-text">Rs.</span>
                            <input type="number" step="0.01" min="0.01" class="form-control" name="amount" placeholder="e.g. 25000.00" required>
                        </div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Contribution Date <span class="text-danger">*</span></label>
                        <input type="date" class="form-control" name="contribution_date" value="<?= date('Y-m-d') ?>" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Funding Account <span class="text-secondary small">(Optional)</span></label>
                        <select class="form-select" name="account_id">
                            <option value="">Direct / External Deposit</option>
                            <?php foreach ($accounts as $acc): ?>
                                <option value="<?= $acc['id'] ?>" <?= $goal['account_id'] == $acc['id'] ? 'selected' : '' ?>>
                                    <?= e($acc['name']) ?> (Balance: <?= currency($acc['current_balance']) ?>)
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Notes / Description</label>
                        <input type="text" class="form-control" name="notes" placeholder="e.g. Monthly salary allotment, Project bonus">
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-fin-secondary btn-sm" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-fin-primary btn-sm">Confirm Contribution</button>
                </div>
            </form>
        </div>
    </div>
</div>
