<!DOCTYPE html>
<html lang="en" data-theme="<?= e($userSettings['theme'] ?? 'light') ?>">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <meta name="csrf-token" content="<?= e($csrfToken) ?>">
    <title><?= e($title ?? 'FINORA – Personal Finance Intelligence') ?></title>
    
    <!-- Favicon -->
    <link rel="icon" type="image/svg+xml" href="/assets/images/logo-icon.svg">
    
    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    
    <!-- Bootstrap 5.3 CSS -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css">
    
    <!-- Bootstrap Icons -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    
    <!-- Custom FINORA Design System -->
    <link rel="stylesheet" href="/assets/css/finora.css">
    
    <!-- Instant theme initialization to avoid flash of light mode -->
    <script>
        (function() {
            const saved = localStorage.getItem('finora_theme');
            if (saved) {
                document.documentElement.setAttribute('data-theme', saved);
            }
        })();
    </script>
</head>
<body>

<div class="app-wrapper">
    <!-- Mobile Sidebar Backdrop -->
    <div class="sidebar-backdrop"></div>

    <!-- Desktop Sidebar (250px) -->
    <aside class="app-sidebar">
        <div class="sidebar-header">
            <a href="/dashboard" class="d-flex align-items-center">
                <img src="/assets/images/logo-white.svg" alt="FINORA" height="38">
            </a>
        </div>

        <nav class="sidebar-nav">
            <div class="nav-section-title">Overview</div>
            <a href="/dashboard" class="nav-link <?= is_active('/dashboard') ?> <?= is_active('/') ?>">
                <i class="bi bi-grid-1x2"></i>
                <span>Dashboard</span>
            </a>

            <div class="nav-section-title">Finances</div>
            <a href="/transactions" class="nav-link <?= is_active('/transactions') ?>">
                <i class="bi bi-arrow-left-right"></i>
                <span>Transactions</span>
            </a>
            <a href="/accounts" class="nav-link <?= is_active('/accounts') ?>">
                <i class="bi bi-wallet2"></i>
                <span>Accounts</span>
            </a>

            <div class="nav-section-title">Planning & Wealth</div>
            <a href="/budgets" class="nav-link <?= is_active('/budgets') ?>">
                <i class="bi bi-pie-chart"></i>
                <span>Budgets</span>
            </a>
            <a href="/savings" class="nav-link <?= is_active('/savings') ?>">
                <i class="bi bi-piggy-bank"></i>
                <span>Savings Goals</span>
            </a>
            <a href="/categories" class="nav-link <?= is_active('/categories') ?>">
                <i class="bi bi-tags"></i>
                <span>Categories</span>
            </a>

            <div class="nav-section-title">Analytics</div>
            <a href="/reports" class="nav-link <?= is_active('/reports') ?>">
                <i class="bi bi-bar-chart-line"></i>
                <span>Reports & Analytics</span>
            </a>
            <a href="/notifications" class="nav-link <?= is_active('/notifications') ?>">
                <i class="bi bi-bell"></i>
                <span>Notifications</span>
            </a>

            <div class="nav-section-title">Account</div>
            <a href="/profile" class="nav-link <?= is_active('/profile') ?>">
                <i class="bi bi-person-circle"></i>
                <span>Profile</span>
            </a>
            <a href="/settings" class="nav-link <?= is_active('/settings') ?>">
                <i class="bi bi-gear"></i>
                <span>Settings</span>
            </a>
        </nav>

        <div class="sidebar-footer">
            <div class="user-mini-card">
                <?php if (!empty($currentUser['avatar'])): ?>
                    <img src="<?= e($currentUser['avatar']) ?>" alt="Avatar" class="user-avatar">
                <?php else: ?>
                    <div class="user-avatar"><?= strtoupper(substr($currentUser['name'] ?? 'U', 0, 1)) ?></div>
                <?php endif; ?>
                <div class="user-mini-info">
                    <div class="user-mini-name"><?= e($currentUser['name'] ?? 'User') ?></div>
                    <div class="user-mini-email"><?= e($currentUser['email'] ?? '') ?></div>
                </div>
                <a href="/logout" title="Sign Out" class="text-white opacity-75 hover-opacity-100 fs-5 ms-1">
                    <i class="bi bi-box-arrow-right"></i>
                </a>
            </div>
        </div>
    </aside>

    <!-- Main Container -->
    <div class="app-main">
        <!-- Top Sticky Header (72px) -->
        <header class="app-header">
            <div class="d-flex align-items-center gap-3">
                <button type="button" class="btn btn-sm btn-fin-secondary d-lg-none mobile-sidebar-toggle" aria-label="Toggle navigation">
                    <i class="bi bi-list fs-5"></i>
                </button>
                <div class="header-title-box">
                    <h1><?= e($title ?? 'FINORA Intelligence') ?></h1>
                    <p>Track Smarter. Spend Better. Grow Wealth.</p>
                </div>
            </div>

            <div class="header-actions">
                <!-- Quick Add Transaction Button -->
                <a href="/transactions/create" class="btn btn-fin-primary btn-sm d-none d-sm-inline-flex">
                    <i class="bi bi-plus-circle"></i>
                    <span>Add Transaction</span>
                </a>

                <!-- Theme Switcher -->
                <button type="button" class="theme-toggle-btn" title="Toggle Theme" aria-label="Toggle Theme">
                    <i class="bi bi-moon-stars"></i>
                </button>

                <!-- Notifications Dropdown -->
                <?php
                    $unreadNotifCount = \App\Models\Notification::getUnreadCount($currentUser['id'] ?? 0);
                    $recentUnreadNotifs = \App\Models\Notification::getUnread($currentUser['id'] ?? 0, 4);
                ?>
                <div class="dropdown">
                    <button class="notification-bell-btn" type="button" data-bs-toggle="dropdown" aria-expanded="false" title="Notifications">
                        <i class="bi bi-bell"></i>
                        <?php if ($unreadNotifCount > 0): ?>
                            <span class="notification-badge-dot"></span>
                        <?php endif; ?>
                    </button>
                    <ul class="dropdown-menu dropdown-menu-end shadow-lg border-0 p-2" style="width: 320px; border-radius: 14px;">
                        <li class="d-flex align-items-center justify-content-between px-2 py-1 mb-1">
                            <span class="fw-bold small">Notifications (<?= $unreadNotifCount ?>)</span>
                            <?php if ($unreadNotifCount > 0): ?>
                                <form method="POST" action="/notifications/mark-all-read" class="m-0">
                                    <?= csrf_field() ?>
                                    <button type="submit" class="btn btn-link p-0 text-decoration-none small text-success">Mark all read</button>
                                </form>
                            <?php endif; ?>
                        </li>
                        <li><hr class="dropdown-divider my-1"></li>
                        <?php if (empty($recentUnreadNotifs)): ?>
                            <li class="text-center py-3 text-secondary small">No unread notifications</li>
                        <?php else: ?>
                            <?php foreach ($recentUnreadNotifs as $notif): ?>
                                <li class="p-2 rounded hover-bg-light small">
                                    <div class="fw-semibold text-truncate"><?= e($notif['title']) ?></div>
                                    <div class="text-secondary text-truncate small"><?= e($notif['message']) ?></div>
                                    <div class="text-muted smaller mt-1"><?= date('d M, H:i', strtotime($notif['created_at'])) ?></div>
                                </li>
                            <?php endforeach; ?>
                        <?php endif; ?>
                        <li><hr class="dropdown-divider my-1"></li>
                        <li>
                            <a class="dropdown-item text-center small text-primary fw-medium py-1" href="/notifications">View all notifications</a>
                        </li>
                    </ul>
                </div>

                <!-- User Dropdown Menu -->
                <div class="dropdown">
                    <button class="btn p-0 border-0" type="button" data-bs-toggle="dropdown" aria-expanded="false">
                        <?php if (!empty($currentUser['avatar'])): ?>
                            <img src="<?= e($currentUser['avatar']) ?>" alt="Avatar" class="user-avatar" style="width: 38px; height: 38px;">
                        <?php else: ?>
                            <div class="user-avatar" style="width: 38px; height: 38px;"><?= strtoupper(substr($currentUser['name'] ?? 'U', 0, 1)) ?></div>
                        <?php endif; ?>
                    </button>
                    <ul class="dropdown-menu dropdown-menu-end shadow-lg border-0 p-2" style="border-radius: 14px; min-width: 200px;">
                        <li class="px-3 py-2 border-bottom mb-1">
                            <div class="fw-bold small"><?= e($currentUser['name'] ?? 'User') ?></div>
                            <div class="text-secondary smaller text-truncate"><?= e($currentUser['email'] ?? '') ?></div>
                        </li>
                        <li><a class="dropdown-item rounded py-2 small" href="/profile"><i class="bi bi-person me-2"></i> User Profile</a></li>
                        <li><a class="dropdown-item rounded py-2 small" href="/settings"><i class="bi bi-gear me-2"></i> Settings</a></li>
                        <li><hr class="dropdown-divider my-1"></li>
                        <li>
                            <form method="POST" action="/logout" class="m-0">
                                <?= csrf_field() ?>
                                <button type="submit" class="dropdown-item rounded py-2 small text-danger"><i class="bi bi-box-arrow-right me-2"></i> Sign Out</button>
                            </form>
                        </li>
                    </ul>
                </div>
            </div>
        </header>

        <!-- Main Body Content -->
        <main class="content-body">
            <!-- Dismissible Flash Alerts -->
            <?php if (!empty($flashSuccess)): ?>
                <div class="alert alert-success alert-dismissible fade show border-0 shadow-xs mb-4" role="alert">
                    <i class="bi bi-check-circle-fill me-2"></i> <?= e($flashSuccess) ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            <?php endif; ?>

            <?php if (!empty($flashError)): ?>
                <div class="alert alert-danger alert-dismissible fade show border-0 shadow-xs mb-4" role="alert">
                    <i class="bi bi-exclamation-triangle-fill me-2"></i> <?= e($flashError) ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            <?php endif; ?>

            <?php if (!empty($flashWarning)): ?>
                <div class="alert alert-warning alert-dismissible fade show border-0 shadow-xs mb-4" role="alert">
                    <i class="bi bi-exclamation-circle-fill me-2"></i> <?= e($flashWarning) ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            <?php endif; ?>

            <?php if (!empty($flashInfo)): ?>
                <div class="alert alert-info alert-dismissible fade show border-0 shadow-xs mb-4" role="alert">
                    <i class="bi bi-info-circle-fill me-2"></i> <?= e($flashInfo) ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            <?php endif; ?>

            <!-- View Specific Body -->
            <?= $content ?>
        </main>

        <!-- App Footer -->
        <footer class="py-3 px-4 border-top text-center text-secondary small bg-body">
            <div class="container-fluid d-flex flex-column flex-sm-row justify-content-between align-items-center gap-2">
                <div>&copy; <?= date('Y') ?> <strong>FINORA</strong> – Personal Finance Intelligence. All rights reserved.</div>
                <div class="d-flex gap-3">
                    <a href="/reports" class="text-secondary text-decoration-none">Analytics</a>
                    <a href="/settings" class="text-secondary text-decoration-none">Preferences</a>
                    <span class="text-muted">v1.0.0</span>
                </div>
            </div>
        </footer>
    </div>
</div>

<!-- Bootstrap 5.3 JS -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

<!-- Chart.js -->
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.2/dist/chart.umd.min.js"></script>

<!-- FINORA Interactive Logic -->
<script src="/assets/js/finora.js"></script>

</body>
</html>
