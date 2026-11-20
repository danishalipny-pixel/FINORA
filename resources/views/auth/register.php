<div class="mb-4 text-center">
    <h2 class="h4 fw-bold mb-1">Create Your Account</h2>
    <p class="text-secondary small">Start tracking smarter and building your wealth.</p>
</div>

<form method="POST" action="/register">
    <?= csrf_field() ?>

    <div class="mb-3">
        <label for="name" class="form-label">Full Name</label>
        <div class="input-group">
            <span class="input-group-text"><i class="bi bi-person"></i></span>
            <input type="text" class="form-control <?= has_error('name') ? 'is-invalid' : '' ?>" id="name" name="name" value="<?= e(old('name')) ?>" placeholder="e.g. Zain Ahmed" required autofocus>
        </div>
        <?php if (has_error('name')): ?>
            <div class="invalid-feedback d-block small"><?= e(error('name')) ?></div>
        <?php endif; ?>
    </div>

    <div class="mb-3">
        <label for="email" class="form-label">Email Address</label>
        <div class="input-group">
            <span class="input-group-text"><i class="bi bi-envelope"></i></span>
            <input type="email" class="form-control <?= has_error('email') ? 'is-invalid' : '' ?>" id="email" name="email" value="<?= e(old('email')) ?>" placeholder="name@domain.com" required>
        </div>
        <?php if (has_error('email')): ?>
            <div class="invalid-feedback d-block small"><?= e(error('email')) ?></div>
        <?php endif; ?>
    </div>

    <div class="mb-3">
        <label for="password" class="form-label">Password</label>
        <div class="input-group">
            <span class="input-group-text"><i class="bi bi-lock"></i></span>
            <input type="password" class="form-control <?= has_error('password') ? 'is-invalid' : '' ?>" id="password" name="password" placeholder="At least 8 characters" required>
        </div>
        <?php if (has_error('password')): ?>
            <div class="invalid-feedback d-block small"><?= e(error('password')) ?></div>
        <?php endif; ?>
    </div>

    <div class="mb-3">
        <label for="password_confirmation" class="form-label">Confirm Password</label>
        <div class="input-group">
            <span class="input-group-text"><i class="bi bi-shield-lock"></i></span>
            <input type="password" class="form-control <?= has_error('password_confirmation') ? 'is-invalid' : '' ?>" id="password_confirmation" name="password_confirmation" placeholder="Re-enter password" required>
        </div>
        <?php if (has_error('password_confirmation')): ?>
            <div class="invalid-feedback d-block small"><?= e(error('password_confirmation')) ?></div>
        <?php endif; ?>
    </div>

    <div class="d-grid mt-4">
        <button type="submit" class="btn btn-fin-primary py-2 fw-semibold">
            <i class="bi bi-person-check"></i> Register Free Account
        </button>
    </div>
</form>

<div class="text-center mt-4 pt-2 border-top">
    <p class="small text-secondary mb-0">
        Already have an account? 
        <a href="/login" class="fw-semibold text-decoration-none">Sign in</a>
    </p>
</div>
