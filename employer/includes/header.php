<?php
// Protect all employer pages
if (!isEmployer()) {
    redirect(url('employer/login.php'));
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $pageTitle ?? 'Employer Panel - Job Portal' ?></title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <style>
        * { font-family: 'Inter', sans-serif; }
        body { background: #f8fafc; }
        .emp-nav {
            background: linear-gradient(135deg, #0ea5e9 0%, #0284c7 100%);
            padding: 0;
            box-shadow: 0 2px 8px rgba(0,0,0,0.12);
        }
        .emp-nav .navbar-brand { font-weight: 700; font-size: 1.2rem; padding: 14px 0; }
        .emp-nav .nav-link { color: rgba(255,255,255,0.85) !important; font-weight: 500; padding: 14px 16px !important; transition: all 0.2s; }
        .emp-nav .nav-link:hover,
        .emp-nav .nav-link.active { color: white !important; background: rgba(255,255,255,0.15); border-radius: 6px; }
        .emp-container { max-width: 1200px; margin: 0 auto; padding: 28px 20px 60px; }
        .page-card { background: white; border-radius: 10px; border: 1px solid #e2e8f0; padding: 28px; margin-bottom: 24px; }
        .stat-card { background: white; border-radius: 10px; border: 1px solid #e2e8f0; padding: 20px 24px; }
        .stat-number { font-size: 2rem; font-weight: 800; color: #0284c7; }
        .stat-label { color: #64748b; font-size: 13px; }
        .badge-active { background: #dcfce7; color: #16a34a; padding: 4px 10px; border-radius: 20px; font-size: 12px; font-weight: 600; }
        .badge-inactive { background: #fee2e2; color: #dc2626; padding: 4px 10px; border-radius: 20px; font-size: 12px; font-weight: 600; }
    </style>
</head>
<body>

<nav class="emp-nav navbar navbar-expand-lg">
    <div class="container-fluid" style="max-width:1200px; margin:0 auto; padding:0 20px;">
        <a class="navbar-brand text-white" href="<?= url('employer/dashboard.php') ?>">
            🏢 Employer Panel
        </a>
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#empNav">
            <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse" id="empNav">
            <ul class="navbar-nav ms-auto align-items-lg-center gap-1">
                <li class="nav-item">
                    <a class="nav-link <?= basename($_SERVER['PHP_SELF']) == 'dashboard.php' ? 'active' : '' ?>"
                       href="<?= url('employer/dashboard.php') ?>">
                        📊 Dashboard
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link <?= basename($_SERVER['PHP_SELF']) == 'jobs.php' ? 'active' : '' ?>"
                       href="<?= url('employer/jobs.php') ?>">
                        💼 My Jobs
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link <?= basename($_SERVER['PHP_SELF']) == 'add-job.php' ? 'active' : '' ?>"
                       href="<?= url('employer/add-job.php') ?>">
                        ➕ Post Job
                    </a>
                </li>
                <li class="nav-item dropdown">
                    <a class="nav-link dropdown-toggle text-white" href="#" data-bs-toggle="dropdown">
                        👤 <?= htmlspecialchars($_SESSION['employer_username'] ?? 'Employer') ?>
                    </a>
                    <ul class="dropdown-menu dropdown-menu-end">
                        <li>
                            <span class="dropdown-item-text text-muted small">
                                🏢 <?= htmlspecialchars($_SESSION['employer_company'] ?? '') ?>
                            </span>
                        </li>
                        <li><hr class="dropdown-divider"></li>
                        <li><a class="dropdown-item" href="<?= url('index.php') ?>" target="_blank">🌐 View Site</a></li>
                        <li><a class="dropdown-item text-danger" href="<?= url('employer/logout.php') ?>">🚪 Logout</a></li>
                    </ul>
                </li>
            </ul>
        </div>
    </div>
</nav>

<div class="emp-container">
