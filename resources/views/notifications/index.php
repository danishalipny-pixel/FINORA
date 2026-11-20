<div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center mb-4 gap-3">
    <div>
        <h2 class="h4 fw-bold mb-1">In-App Notifications</h2>
        <p class="text-secondary small mb-0">System alerts, budget overspend warnings, and savings goal milestones.</p>
    </div>
    <?php if ($unreadCount > 0): ?>
        <form method="POST" action="/notifications/mark-all-read" class="m-0">
            <?= csrf_field() ?>
            <button type="submit" class="btn btn-fin-secondary btn-sm">
                <i class="bi bi-check-all"></i> Mark All as Read (<?= $unreadCount ?>)
            </button>
        </form>
    <?php endif; ?>
</div>

<div class="fin-card">
    <div class="fin-card-body p-0">
        <?php if (empty($notifications)): ?>
            <div class="empty-state">
                <i class="bi bi-bell-slash text-secondary fs-2 mb-2 d-block"></i>
                <h4 class="empty-state-title">No Notifications Logged</h4>
                <p class="empty-state-desc">You are all caught up! New budget warnings and milestones will appear here.</p>
            </div>
        <?php else: ?>
            <div class="list-group list-group-flush">
                <?php foreach ($notifications as $n): ?>
                    <div class="list-group-item p-3 d-flex align-items-start justify-content-between gap-3 <?= !$n['is_read'] ? 'bg-body-tertiary' : '' ?>">
                        <div class="d-flex gap-3 align-items-start">
                            <div class="kpi-icon-badge mt-1 flex-shrink-0 <?= $n['type'] === 'budget_warning' ? 'danger' : ($n['type'] === 'goal_milestone' ? 'emerald' : 'blue') ?>">
                                <?php if ($n['type'] === 'budget_warning'): ?>
                                    <i class="bi bi-exclamation-triangle"></i>
                                <?php elseif ($n['type'] === 'goal_milestone'): ?>
                                    <i class="bi bi-trophy"></i>
                                <?php elseif ($n['type'] === 'security'): ?>
                                    <i class="bi bi-shield-check"></i>
                                <?php else: ?>
                                    <i class="bi bi-info-circle"></i>
                                <?php endif; ?>
                            </div>

                            <div>
                                <div class="d-flex align-items-center gap-2 mb-1">
                                    <h4 class="h6 fw-bold mb-0"><?= e($n['title']) ?></h4>
                                    <?php if (!$n['is_read']): ?>
                                        <span class="badge bg-success small">New</span>
                                    <?php endif; ?>
                                </div>
                                <p class="text-secondary small mb-1"><?= e($n['message']) ?></p>
                                <div class="d-flex gap-3 align-items-center small text-muted">
                                    <span><i class="bi bi-clock me-1"></i> <?= date('d M Y, H:i', strtotime($n['created_at'])) ?></span>
                                    <?php if (!empty($n['link'])): ?>
                                        <a href="<?= e($n['link']) ?>" class="text-decoration-none text-primary fw-medium">View Details &rarr;</a>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>

                        <?php if (!$n['is_read']): ?>
                            <form method="POST" action="/notifications/<?= $n['id'] ?>/read" class="m-0 flex-shrink-0">
                                <?= csrf_field() ?>
                                <button type="submit" class="btn btn-sm btn-link text-secondary text-decoration-none" title="Mark as Read">
                                    <i class="bi bi-check2"></i>
                                </button>
                            </form>
                        <?php endif; ?>
                    </div>
                <?php endforeach; ?>
            </div>

            <?php if ($pagination['last_page'] > 1): ?>
                <div class="p-3 border-top d-flex justify-content-between align-items-center">
                    <span class="small text-secondary">
                        Showing <?= count($notifications) ?> of <?= $pagination['total'] ?> notifications
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
