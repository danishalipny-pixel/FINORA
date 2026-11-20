<div class="mb-4 text-center">
    <h2 class="h4 fw-bold mb-1">Welcome Back</h2>
    <p class="text-secondary small">Sign in to access your financial intelligence dashboard.</p>
</div>

<!-- Quick Demo Account Fill Banner -->
<div class="p-3 mb-4 rounded-3 border" style="background-color: #F8FAFC; border-color: #E2E8F0 !important;">
    <div class="d-flex align-items-center justify-content-between mb-2">
        <span class="badge bg-success-subtle text-success border border-success-subtle px-2 py-1 small">
            <i class="bi bi-person-badge me-1"></i> Demo Portfolio Account
        </span>
        <button type="button" class="btn btn-sm btn-outline-success py-0 px-2 small" onclick="fillDemoCredentials()">
            Auto-Fill
        </button>
    </div>
    <div class="small text-secondary font-monospace" style="font-size: 11.5px;">
        <div><strong>Email:</strong> demo@finora.test</div>
        <div><strong>Password:</strong> FinoraDemo123!</div>
    </div>
</div>

<form method="POST" action="/login">
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

    <div class="mb-3">
        <div class="d-flex justify-content-between align-items-center mb-1">
            <label for="password" class="form-label mb-0">Password</label>
            <a href="/forgot-password" class="small text-decoration-none">Forgot password?</a>
        </div>
        <div class="input-group">
            <span class="input-group-text"><i class="bi bi-lock"></i></span>
            <input type="password" class="form-control <?= has_error('password') ? 'is-invalid' : '' ?>" id="password" name="password" placeholder="••••••••" required>
        </div>
        <?php if (has_error('password')): ?>
            <div class="invalid-feedback d-block small"><?= e(error('password')) ?></div>
        <?php endif; ?>
    </div>

    <div class="d-grid mt-4">
        <button type="submit" class="btn btn-fin-primary py-2 fw-semibold">
            <i class="bi bi-box-arrow-in-right"></i> Sign In to FINORA
        </button>
    </div>
</form>

<div class="text-center mt-4 pt-2 border-top">
    <p class="small text-secondary mb-0">
        Don’t have an account yet? 
        <a href="/register" class="fw-semibold text-decoration-none">Create an account</a>
    </p>
</div>

<script>
function fillDemoCredentials() {
    document.getElementById('email').value = 'demo@finora.test';
    document.getElementById('password').value = 'FinoraDemo123!';
}
</script>
