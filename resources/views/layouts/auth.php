<!DOCTYPE html>
<html lang="en" data-theme="light">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
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
    
    <style>
        .auth-container {
            min-height: 100vh;
            display: flex;
            flex-direction: column;
            justify-content: center;
            align-items: center;
            background: linear-gradient(135deg, #F5F7FA 0%, #E4E7EB 100%);
            padding: 32px 16px;
        }
        .auth-card {
            width: 100%;
            max-width: 440px;
            background: #FFFFFF;
            border-radius: 20px;
            border: 1px solid #EAECF0;
            box-shadow: 0px 20px 25px -5px rgba(16, 24, 40, 0.08), 0px 8px 10px -6px rgba(16, 24, 40, 0.03);
            overflow: hidden;
        }
        .auth-body {
            padding: 36px 32px;
        }
        .auth-footer-brand {
            margin-top: 24px;
            text-align: center;
            font-size: 13px;
            color: #667085;
        }
    </style>
</head>
<body>

<div class="auth-container">
    <div class="text-center mb-4">
        <a href="/">
            <img src="/assets/images/logo.svg" alt="FINORA" height="46">
        </a>
    </div>

    <div class="auth-card">
        <div class="auth-body">
            <!-- Flash Alerts -->
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

            <?= $content ?>
        </div>
    </div>

    <div class="auth-footer-brand">
        <div class="d-flex align-items-center justify-content-center gap-2 mb-1">
            <i class="bi bi-shield-check text-success"></i>
            <span>End-to-End Enterprise Encryption Active</span>
        </div>
        <div>&copy; <?= date('Y') ?> FINORA – Personal Finance Intelligence.</div>
    </div>
</div>

<!-- Bootstrap 5.3 JS -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

</body>
</html>
