<div class="row justify-content-center">
    <div class="col-12 col-lg-8">
        <div class="d-flex align-items-center justify-content-between mb-4">
            <div>
                <h2 class="h4 fw-bold mb-1">Preferences & Security Settings</h2>
                <p class="text-secondary small mb-0">Configure your financial display format, interface theme, and account security.</p>
            </div>
            <a href="/dashboard" class="btn btn-fin-secondary btn-sm">
                <i class="bi bi-arrow-left"></i> Dashboard
            </a>
        </div>

        <!-- Preferences Form -->
        <div class="fin-card mb-4">
            <div class="fin-card-header">
                <h3 class="fin-card-title">
                    <i class="bi bi-sliders text-success"></i> Display & Financial Preferences
                </h3>
            </div>
            <div class="fin-card-body">
                <form method="POST" action="/settings/preferences">
                    <?= csrf_field() ?>

                    <div class="row g-3">
                        <div class="col-12 col-md-6">
                            <label for="currency" class="form-label">Default Currency</label>
                            <select class="form-select" id="currency" name="currency">
                                <option value="PKR" <?= ($settings['currency'] ?? '') === 'PKR' ? 'selected' : '' ?>>PKR – Pakistani Rupee (Rs.)</option>
                                <option value="USD" <?= ($settings['currency'] ?? '') === 'USD' ? 'selected' : '' ?>>USD – US Dollar ($)</option>
                                <option value="EUR" <?= ($settings['currency'] ?? '') === 'EUR' ? 'selected' : '' ?>>EUR – Euro (€)</option>
                                <option value="GBP" <?= ($settings['currency'] ?? '') === 'GBP' ? 'selected' : '' ?>>GBP – British Pound (£)</option>
                                <option value="AED" <?= ($settings['currency'] ?? '') === 'AED' ? 'selected' : '' ?>>AED – UAE Dirham (AED)</option>
                                <option value="SAR" <?= ($settings['currency'] ?? '') === 'SAR' ? 'selected' : '' ?>>SAR – Saudi Riyal (SAR)</option>
                                <option value="CAD" <?= ($settings['currency'] ?? '') === 'CAD' ? 'selected' : '' ?>>CAD – Canadian Dollar (CA$)</option>
                            </select>
                        </div>

                        <div class="col-12 col-md-6">
                            <label for="date_format" class="form-label">Date Format</label>
                            <select class="form-select" id="date_format" name="date_format">
                                <option value="d M Y" <?= ($settings['date_format'] ?? '') === 'd M Y' ? 'selected' : '' ?>>15 Nov 2026 (d M Y)</option>
                                <option value="Y-m-d" <?= ($settings['date_format'] ?? '') === 'Y-m-d' ? 'selected' : '' ?>>2026-11-15 (Y-m-d)</option>
                                <option value="d/m/Y" <?= ($settings['date_format'] ?? '') === 'd/m/Y' ? 'selected' : '' ?>>15/11/2026 (d/m/Y)</option>
                                <option value="m/d/Y" <?= ($settings['date_format'] ?? '') === 'm/d/Y' ? 'selected' : '' ?>>11/15/2026 (m/d/Y)</option>
                            </select>
                        </div>

                        <div class="col-12 col-md-6">
                            <label for="theme" class="form-label">Interface Theme</label>
                            <select class="form-select" id="theme" name="theme">
                                <option value="light" <?= ($settings['theme'] ?? '') === 'light' ? 'selected' : '' ?>>Light Theme (Fintech Clean)</option>
                                <option value="dark" <?= ($settings['theme'] ?? '') === 'dark' ? 'selected' : '' ?>>Dark Theme (Midnight Navy)</option>
                            </select>
                        </div>
                    </div>

                    <div class="mt-4 pt-3 border-top">
                        <h4 class="h6 fw-bold mb-3">Notification Preferences</h4>
                        <div class="form-check form-switch mb-2">
                            <input class="form-check-input" type="checkbox" id="notify_budget_warning" name="notify_budget_warning" <?= !empty($settings['notify_budget_warning']) ? 'checked' : '' ?>>
                            <label class="form-check-label small" for="notify_budget_warning">
                                Receive budget utilization warnings when category expenses reach 80% or exceed limit
                            </label>
                        </div>
                        <div class="form-check form-switch mb-2">
                            <input class="form-check-input" type="checkbox" id="notify_goal_reached" name="notify_goal_reached" <?= !empty($settings['notify_goal_reached']) ? 'checked' : '' ?>>
                            <label class="form-check-label small" for="notify_goal_reached">
                                Receive milestone celebration alerts when a savings goal is fully funded
                            </label>
                        </div>
                        <div class="form-check form-switch mb-2">
                            <input class="form-check-input" type="checkbox" id="notify_monthly_summary" name="notify_monthly_summary" <?= !empty($settings['notify_monthly_summary']) ? 'checked' : '' ?>>
                            <label class="form-check-label small" for="notify_monthly_summary">
                                Generate end-of-month financial audit digest reminders
                            </label>
                        </div>
                    </div>

                    <div class="d-flex justify-content-end mt-4 pt-3 border-top">
                        <button type="submit" class="btn btn-fin-primary">
                            <i class="bi bi-check2"></i> Save Preferences
                        </button>
                    </div>
                </form>
            </div>
        </div>

        <!-- Security & Password Change -->
        <div class="fin-card">
            <div class="fin-card-header">
                <h3 class="fin-card-title">
                    <i class="bi bi-shield-lock text-danger"></i> Change Password & Security
                </h3>
            </div>
            <div class="fin-card-body">
                <form method="POST" action="/settings/password">
                    <?= csrf_field() ?>

                    <div class="row g-3">
                        <div class="col-12">
                            <label for="current_password" class="form-label">Current Password <span class="text-danger">*</span></label>
                            <input type="password" class="form-control <?= has_error('current_password') ? 'is-invalid' : '' ?>" id="current_password" name="current_password" required>
                            <?php if (has_error('current_password')): ?>
                                <div class="invalid-feedback"><?= e(error('current_password')) ?></div>
                            <?php endif; ?>
                        </div>

                        <div class="col-12 col-md-6">
                            <label for="new_password" class="form-label">New Password <span class="text-danger">*</span></label>
                            <input type="password" class="form-control <?= has_error('new_password') ? 'is-invalid' : '' ?>" id="new_password" name="new_password" placeholder="At least 8 characters" required>
                            <?php if (has_error('new_password')): ?>
                                <div class="invalid-feedback"><?= e(error('new_password')) ?></div>
                            <?php endif; ?>
                        </div>

                        <div class="col-12 col-md-6">
                            <label for="new_password_confirmation" class="form-label">Confirm New Password <span class="text-danger">*</span></label>
                            <input type="password" class="form-control <?= has_error('new_password_confirmation') ? 'is-invalid' : '' ?>" id="new_password_confirmation" name="new_password_confirmation" required>
                            <?php if (has_error('new_password_confirmation')): ?>
                                <div class="invalid-feedback"><?= e(error('new_password_confirmation')) ?></div>
                            <?php endif; ?>
                        </div>
                    </div>

                    <div class="d-flex justify-content-end mt-4 pt-3 border-top">
                        <button type="submit" class="btn btn-fin-primary">
                            <i class="bi bi-key"></i> Update Password
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
