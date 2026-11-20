<div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center mb-4 gap-3">
    <div>
        <h2 class="h4 fw-bold mb-1">Savings Goals & Wealth Growth</h2>
        <p class="text-secondary small mb-0">Build dedicated capital funds for your milestones, investments, and rainy-day safety nets.</p>
    </div>
    <div>
        <button type="button" class="btn btn-fin-primary btn-sm" data-bs-toggle="modal" data-bs-target="#createGoalModal">
            <i class="bi bi-plus-lg"></i> Create Savings Goal
        </button>
    </div>
</div>

<!-- Savings Overview Banner -->
<?php
    $totalTarget = 0.0;
    $completedGoals = 0;
    foreach ($goals as $g) {
        $totalTarget += (float)$g['target_amount'];
        if ($g['status'] === 'completed') $completedGoals++;
    }
    $overallProgress = $totalTarget > 0 ? round(($totalSaved / $totalTarget) * 100, 1) : 0.0;
?>
<div class="row g-3 mb-4">
    <div class="col-12 col-md-4">
        <div class="kpi-card">
            <div class="kpi-label">Total Accumulated Savings</div>
            <div class="kpi-amount fs-3 text-success"><?= currency($totalSaved) ?></div>
            <div class="small text-secondary">Funded across active & completed goals</div>
        </div>
    </div>
    <div class="col-12 col-md-4">
        <div class="kpi-card">
            <div class="kpi-label">Cumulative Target Capital</div>
            <div class="kpi-amount fs-3 text-primary"><?= currency($totalTarget) ?></div>
            <div class="small text-secondary"><?= count($goals) ?> total goals defined</div>
        </div>
    </div>
    <div class="col-12 col-md-4">
        <div class="kpi-card">
            <div class="kpi-label">Milestones Achieved</div>
            <div class="kpi-amount fs-3 text-warning"><?= $completedGoals ?> / <?= count($goals) ?></div>
            <div class="small text-secondary"><?= $overallProgress ?>% portfolio funding rate</div>
        </div>
    </div>
</div>

<!-- Goals Grid -->
<?php if (empty($goals)): ?>
    <div class="fin-card">
        <div class="fin-card-body">
            <div class="empty-state">
                <i class="bi bi-piggy-bank text-secondary fs-2 mb-2 d-block"></i>
                <h4 class="empty-state-title">No Savings Goals Defined Yet</h4>
                <p class="empty-state-desc">Define targets for Hajj/Umrah, emergency funds, property purchases, or electronics.</p>
                <button type="button" class="btn btn-fin-primary btn-sm" data-bs-toggle="modal" data-bs-target="#createGoalModal">
                    Create First Goal
                </button>
            </div>
        </div>
    </div>
<?php else: ?>
    <div class="row g-4">
        <?php foreach ($goals as $g): ?>
            <div class="col-12 col-md-6 col-xl-4">
                <div class="fin-card h-100 mb-0 d-flex flex-column justify-content-between" style="border-top: 4px solid <?= e($g['color'] ?? '#10B981') ?>;">
                    <div class="fin-card-body">
                        <div class="d-flex justify-content-between align-items-start mb-2">
                            <h3 class="h6 fw-bold mb-0 text-truncate" style="max-width: 200px;"><?= e($g['name']) ?></h3>
                            <?php if ($g['status'] === 'completed'): ?>
                                <span class="badge bg-success-subtle text-success border border-success-subtle small">
                                    <i class="bi bi-check2-all me-1"></i> Completed
                                </span>
                            <?php elseif ($g['status'] === 'paused'): ?>
                                <span class="badge bg-secondary-subtle text-secondary small">Paused</span>
                            <?php else: ?>
                                <span class="badge bg-primary-subtle text-primary small">In Progress</span>
                            <?php endif; ?>
                        </div>

                        <?php if (!empty($g['account_name'])): ?>
                            <div class="text-secondary smaller mb-3">
                                <i class="bi bi-wallet2 me-1"></i> <?= e($g['account_name']) ?>
                            </div>
                        <?php endif; ?>

                        <!-- Amounts -->
                        <div class="d-flex justify-content-between align-items-end my-3">
                            <div>
                                <span class="smaller text-secondary text-uppercase fw-semibold d-block">Saved</span>
                                <span class="fs-4 fw-extrabold text-success"><?= currency($g['current_amount']) ?></span>
                            </div>
                            <div class="text-end">
                                <span class="smaller text-secondary text-uppercase fw-semibold d-block">Target</span>
                                <span class="fs-6 fw-bold text-secondary"><?= currency($g['target_amount']) ?></span>
                            </div>
                        </div>

                        <!-- Progress Bar -->
                        <div class="mb-2">
                            <div class="progress-fin" style="height: 10px;">
                                <div class="progress-fin-bar" style="width: <?= $g['percentage'] ?>%; background-color: <?= e($g['color'] ?? '#10B981') ?>;"></div>
                            </div>
                        </div>

                        <div class="d-flex justify-content-between small text-secondary">
                            <span class="fw-bold" style="color: <?= e($g['color'] ?? '#10B981') ?>;"><?= $g['percentage'] ?>% completed</span>
                            <span>Target: <?= date_fmt($g['target_date']) ?></span>
                        </div>

                        <?php if (!empty($g['notes'])): ?>
                            <div class="p-2 rounded mt-3 small text-secondary bg-body-tertiary">
                                <?= e($g['notes']) ?>
                            </div>
                        <?php endif; ?>
                    </div>

                    <div class="fin-card-header bg-transparent py-2 px-3 border-top d-flex justify-content-between align-items-center">
                        <div class="small text-secondary">
                            <i class="bi bi-hourglass-split me-1"></i>
                            <?= $g['days_remaining'] >= 0 ? $g['days_remaining'] . ' days left' : 'Due date passed' ?>
                        </div>
                        <div class="d-flex gap-1">
                            <a href="/savings/<?= $g['id'] ?>/contributions" class="btn btn-fin-primary btn-sm py-1 px-2" title="Add Contribution">
                                <i class="bi bi-plus-circle"></i> Contribute
                            </a>
                            <button type="button" class="btn btn-fin-secondary btn-sm py-1 px-2" data-bs-toggle="modal" data-bs-target="#editGoalModal<?= $g['id'] ?>" title="Edit Goal">
                                <i class="bi bi-pencil"></i>
                            </button>
                            <form method="POST" action="/savings/<?= $g['id'] ?>/delete" class="d-inline form-delete-confirm m-0">
                                <?= csrf_field() ?>
                                <button type="submit" class="btn btn-fin-outline-danger btn-sm py-1 px-2" title="Delete">
                                    <i class="bi bi-trash"></i>
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Edit Goal Modal -->
            <div class="modal fade" id="editGoalModal<?= $g['id'] ?>" tabindex="-1" aria-hidden="true">
                <div class="modal-dialog modal-dialog-centered">
                    <div class="modal-content">
                        <form method="POST" action="/savings/<?= $g['id'] ?>/edit">
                            <?= csrf_field() ?>
                            <div class="modal-header">
                                <h5 class="modal-title fw-bold">Edit Savings Goal</h5>
                                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                            </div>
                            <div class="modal-body">
                                <div class="mb-3">
                                    <label class="form-label">Goal Name <span class="text-danger">*</span></label>
                                    <input type="text" class="form-control" name="name" value="<?= e($g['name']) ?>" required>
                                </div>
                                <div class="mb-3">
                                    <label class="form-label">Target Amount (PKR) <span class="text-danger">*</span></label>
                                    <input type="number" step="0.01" min="0.01" class="form-control" name="target_amount" value="<?= e($g['target_amount']) ?>" required>
                                </div>
                                <div class="mb-3">
                                    <label class="form-label">Target Date <span class="text-danger">*</span></label>
                                    <input type="date" class="form-control" name="target_date" value="<?= e($g['target_date']) ?>" required>
                                </div>
                                <div class="mb-3">
                                    <label class="form-label">Status</label>
                                    <select class="form-select" name="status">
                                        <option value="in_progress" <?= $g['status'] === 'in_progress' ? 'selected' : '' ?>>In Progress</option>
                                        <option value="completed" <?= $g['status'] === 'completed' ? 'selected' : '' ?>>Completed</option>
                                        <option value="paused" <?= $g['status'] === 'paused' ? 'selected' : '' ?>>Paused</option>
                                    </select>
                                </div>
                                <div class="mb-3">
                                    <label class="form-label">Linked Account</label>
                                    <select class="form-select" name="account_id">
                                        <option value="">None / Flexible</option>
                                        <?php foreach ($accounts as $acc): ?>
                                            <option value="<?= $acc['id'] ?>" <?= $g['account_id'] == $acc['id'] ? 'selected' : '' ?>>
                                                <?= e($acc['name']) ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                                <div class="mb-3">
                                    <label class="form-label">Color Code</label>
                                    <input type="color" class="form-control form-control-color w-100" name="color" value="<?= e($g['color'] ?? '#10B981') ?>">
                                </div>
                                <div class="mb-3">
                                    <label class="form-label">Notes</label>
                                    <textarea class="form-control" name="notes" rows="2"><?= e($g['notes']) ?></textarea>
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
<?php endif; ?>

<!-- Create Goal Modal -->
<div class="modal fade" id="createGoalModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <form method="POST" action="/savings">
                <?= csrf_field() ?>
                <div class="modal-header">
                    <h5 class="modal-title fw-bold">Establish New Savings Goal</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">Goal Title <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" name="name" placeholder="e.g. Umrah 2027, Car Down Payment" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Target Capital (PKR) <span class="text-danger">*</span></label>
                        <div class="input-group">
                            <span class="input-group-text">Rs.</span>
                            <input type="number" step="0.01" min="0.01" class="form-control" name="target_amount" placeholder="e.g. 500000.00" required>
                        </div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Target Completion Date <span class="text-danger">*</span></label>
                        <input type="date" class="form-control" name="target_date" value="<?= date('Y-m-d', strtotime('+6 months')) ?>" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Linked Account <span class="text-secondary small">(Optional)</span></label>
                        <select class="form-select" name="account_id">
                            <option value="">None / Flexible</option>
                            <?php foreach ($accounts as $acc): ?>
                                <option value="<?= $acc['id'] ?>"><?= e($acc['name']) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Color Code</label>
                        <input type="color" class="form-control form-control-color w-100" name="color" value="#10B981">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Notes & Purpose</label>
                        <textarea class="form-control" name="notes" rows="2" placeholder="Describe the purpose or milestone triggers..."></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-fin-secondary btn-sm" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-fin-primary btn-sm">Create Goal</button>
                </div>
            </form>
        </div>
    </div>
</div>
