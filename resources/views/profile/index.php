<div class="row justify-content-center">
    <div class="col-12 col-lg-8">
        <div class="d-flex align-items-center justify-content-between mb-4">
            <div>
                <h2 class="h4 fw-bold mb-1">User Profile</h2>
                <p class="text-secondary small mb-0">Manage your personal credentials, identity, and profile picture.</p>
            </div>
            <a href="/dashboard" class="btn btn-fin-secondary btn-sm">
                <i class="bi bi-arrow-left"></i> Dashboard
            </a>
        </div>

        <div class="fin-card">
            <div class="fin-card-body">
                <form method="POST" action="/profile" enctype="multipart/form-data">
                    <?= csrf_field() ?>

                    <!-- Avatar Section -->
                    <div class="d-flex align-items-center gap-4 mb-4 pb-3 border-bottom">
                        <?php if (!empty($user['avatar'])): ?>
                            <img src="<?= e($user['avatar']) ?>" alt="Avatar" class="user-avatar" style="width: 72px; height: 72px;">
                        <?php else: ?>
                            <div class="user-avatar fs-3" style="width: 72px; height: 72px;">
                                <?= strtoupper(substr($user['name'] ?? 'U', 0, 1)) ?>
                            </div>
                        <?php endif; ?>

                        <div>
                            <label for="avatar" class="form-label mb-1">Upload Profile Avatar</label>
                            <input type="file" class="form-control form-control-sm" id="avatar" name="avatar" accept="image/jpeg,image/png,image/webp">
                            <span class="text-secondary small">Supported formats: JPG, PNG, WebP (Max 2MB).</span>
                        </div>
                    </div>

                    <div class="row g-3">
                        <div class="col-12 col-md-6">
                            <label for="name" class="form-label">Full Name <span class="text-danger">*</span></label>
                            <input type="text" class="form-control <?= has_error('name') ? 'is-invalid' : '' ?>" id="name" name="name" value="<?= e(old('name', $user['name'])) ?>" required>
                            <?php if (has_error('name')): ?>
                                <div class="invalid-feedback"><?= e(error('name')) ?></div>
                            <?php endif; ?>
                        </div>

                        <div class="col-12 col-md-6">
                            <label for="email" class="form-label">Email Address <span class="text-danger">*</span></label>
                            <input type="email" class="form-control <?= has_error('email') ? 'is-invalid' : '' ?>" id="email" name="email" value="<?= e(old('email', $user['email'])) ?>" required>
                            <?php if (has_error('email')): ?>
                                <div class="invalid-feedback"><?= e(error('email')) ?></div>
                            <?php endif; ?>
                        </div>

                        <div class="col-12 col-md-6">
                            <label class="form-label">Account Role</label>
                            <input type="text" class="form-control bg-body-tertiary" value="<?= ucfirst($user['role']) ?>" disabled>
                        </div>

                        <div class="col-12 col-md-6">
                            <label class="form-label">Member Since</label>
                            <input type="text" class="form-control bg-body-tertiary" value="<?= date('d M Y, H:i', strtotime($user['created_at'])) ?>" disabled>
                        </div>
                    </div>

                    <div class="d-flex justify-content-end gap-2 mt-4 pt-3 border-top">
                        <a href="/dashboard" class="btn btn-fin-secondary">Cancel</a>
                        <button type="submit" class="btn btn-fin-primary">
                            <i class="bi bi-check2"></i> Save Profile Changes
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
