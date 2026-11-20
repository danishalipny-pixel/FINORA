<?php
/**
 * FINORA Component: Status Badge
 *
 * @var string $label Badge display label
 * @var string $variant 'success', 'danger', 'warning', 'info', 'primary', 'secondary'
 * @var string|null $icon Optional icon class
 */
$variant = $variant ?? 'secondary';
?>
<span class="badge badge-soft-<?= htmlspecialchars($variant) ?> d-inline-flex align-items-center gap-1 px-2.5 py-1 rounded-pill fw-medium">
    <?php if (!empty($icon)): ?>
        <i class="bi <?= htmlspecialchars($icon) ?>"></i>
    <?php endif; ?>
    <?= htmlspecialchars($label) ?>
</span>
