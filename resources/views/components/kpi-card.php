<?php
/**
 * FINORA Component: KPI Card
 *
 * @var string $title Metric title
 * @var string $amount Formatted metric value
 * @var string|null $change Percentage or contextual delta
 * @var string|null $changeType 'positive', 'negative', or 'neutral'
 * @var string $icon Bootstrap icon class (e.g. 'bi-wallet2')
 * @var string $color Theme color prefix (e.g. 'primary', 'success', 'danger', 'info')
 */
$changeType = $changeType ?? 'positive';
$badgeClass = match ($changeType) {
    'positive' => 'badge-soft-success',
    'negative' => 'badge-soft-danger',
    default    => 'badge-soft-secondary',
};
$iconName = match ($changeType) {
    'positive' => 'bi-arrow-up-right',
    'negative' => 'bi-arrow-down-right',
    default    => 'bi-dash',
};
?>
<div class="finora-card p-4">
    <div class="d-flex align-items-center justify-content-between mb-3">
        <span class="text-secondary fw-semibold small text-uppercase tracking-wider"><?= htmlspecialchars($title) ?></span>
        <div class="finora-stat-icon bg-<?= htmlspecialchars($color ?? 'primary') ?>-subtle text-<?= htmlspecialchars($color ?? 'primary') ?>">
            <i class="bi <?= htmlspecialchars($icon) ?>"></i>
        </div>
    </div>
    <div class="d-flex align-items-baseline justify-content-between">
        <h3 class="fw-bold mb-0 text-dark"><?= htmlspecialchars($amount) ?></h3>
        <?php if (!empty($change)): ?>
            <span class="badge <?= $badgeClass ?> d-inline-flex align-items-center gap-1">
                <i class="bi <?= $iconName ?>"></i> <?= htmlspecialchars($change) ?>
            </span>
        <?php endif; ?>
    </div>
</div>
