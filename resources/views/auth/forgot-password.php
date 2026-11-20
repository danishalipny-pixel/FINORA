<div class="mb-4 text-center">
    <h2 class="h4 fw-bold mb-1">Reset Password</h2>
    <p class="text-secondary small">Enter your account email to receive recovery instructions.</p>
</div>

<form method="POST" action="/forgot-password">
    <?= csrf_field() ?>

    <div class="mb-3">
        <label for="email" class="form-label">Email Address</label>
        <div class="input-group">
            <span class="input-group-text"><i class="bi bi-envelope"></i></span>
            <input type="email" class="form-control <?= has_error('email') ? 'is-invalid' : '' ?>" id="email" name="email" value="<?= e(old('email')) ?>" placeholder="name@domain.com" required autofocus>
        </div>
        <?php if (has_error('email')): ?>
            <div class="invalid-feedback d-block small"><?= e(error('email')) ?></div>
        <?php endif; ?>
    </div>

    <div class="d-grid mt-4">
        <button type="submit" class="btn btn-fin-primary py-2 fw-semibold">
            <i class="bi bi-send"></i> Send Reset Link
        </button>
    </div>
</form>

<div class="text-center mt-4 pt-2 border-top">
    <p class="small text-secondary mb-0">
        Remembered your password? 
        <a href="/login" class="fw-semibold text-decoration-none">Back to login</a>
    </p>
</div>
