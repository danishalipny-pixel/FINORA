<?php
/**
 * FINORA Component: Dismissible Alert
 *
 * @var string $message Alert text content
 * @var string $type 'success', 'danger', 'warning', 'info'
 * @var bool $dismissible Whether alert can be dismissed
 */
$type = $type ?? 'info';
$dismissible = $dismissible ?? true;
$icon = match ($type) {
    'success' => 'bi-check-circle-fill',
    'danger'  => 'bi-exclamation-triangle-fill',
    'warning' => 'bi-exclamation-circle-fill',
    default   => 'bi-info-circle-fill',
};
?>
<div class="alert alert-<?= htmlspecialchars($type) ?> d-flex align-items-center gap-2 <?= $dismissible ? 'alert-dismissible fade show' : '' ?> shadow-sm border-0" role="alert">
    <i class="bi <?= $icon ?> fs-5"></i>
    <div class="flex-grow-1">
        <?= htmlspecialchars($message) ?>
    </div>
    <?php if ($dismissible): ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    <?php endif; ?>
</div>
