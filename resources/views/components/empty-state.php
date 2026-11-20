<?php
/**
 * FINORA Component: Empty State
 *
 * @var string $title Heading
 * @var string $description Explanatory message
 * @var string|null $actionUrl Action button URL
 * @var string|null $actionLabel Action button label
 * @var string|null $icon Icon class or default
 */
$icon = $icon ?? 'bi-inbox';
?>
<div class="text-center py-5 px-3">
    <div class="d-inline-flex align-items-center justify-content-center bg-light rounded-circle text-muted mb-3" style="width: 72px; height: 72px;">
        <i class="bi <?= htmlspecialchars($icon) ?> fs-1 text-secondary opacity-75"></i>
    </div>
    <h5 class="fw-bold text-dark mb-1"><?= htmlspecialchars($title) ?></h5>
    <p class="text-secondary small mb-4 mx-auto" style="max-width: 420px;"><?= htmlspecialchars($description) ?></p>
    <?php if (!empty($actionUrl) && !empty($actionLabel)): ?>
        <a href="<?= htmlspecialchars($actionUrl) ?>" class="btn btn-finora-primary px-4 py-2">
            <i class="bi bi-plus-lg me-1"></i> <?= htmlspecialchars($actionLabel) ?>
        </a>
    <?php endif; ?>
</div>
