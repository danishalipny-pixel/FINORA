<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>404 - Page Not Found | FINORA</title>
    <link rel="icon" type="image/svg+xml" href="/assets/images/logo-icon.svg">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <link rel="stylesheet" href="/assets/css/finora.css">
    <style>
        .error-wrapper {
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            background: linear-gradient(135deg, #F5F7FA 0%, #E4E7EB 100%);
            padding: 24px;
        }
        .error-card {
            max-width: 500px;
            background: #FFFFFF;
            border-radius: 20px;
            padding: 48px 36px;
            text-align: center;
            box-shadow: 0px 20px 25px -5px rgba(16, 24, 40, 0.08);
            border: 1px solid #EAECF0;
        }
    </style>
</head>
<body>

<div class="error-wrapper">
    <div class="error-card">
        <a href="/" class="d-inline-block mb-4">
            <img src="/assets/images/logo.svg" alt="FINORA" height="42">
        </a>
        <div class="display-1 fw-extrabold text-danger mb-2">404</div>
        <h2 class="h4 fw-bold mb-2">Resource or Page Not Found</h2>
        <p class="text-secondary small mb-4">
            The financial view, transaction, or URL you requested does not exist or may have been relocated.
        </p>
        <div class="d-flex justify-content-center gap-2">
            <a href="/dashboard" class="btn btn-fin-primary">
                <i class="bi bi-house me-1"></i> Return to Dashboard
            </a>
            <a href="/transactions" class="btn btn-fin-secondary">
                <i class="bi bi-arrow-left-right me-1"></i> View Transactions
            </a>
        </div>
    </div>
</div>

</body>
</html>
