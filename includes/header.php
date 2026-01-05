<?php
// Get counts for navigation badges
$activeJobsCount = $pdo->query("
    SELECT COUNT(*) FROM jobs 
    WHERE is_active = 1 
    AND (application_deadline IS NULL OR application_deadline >= CURDATE())
")->fetchColumn();

$archivedJobsCount = $pdo->query("
    SELECT COUNT(*) FROM jobs 
    WHERE is_active = 1 
    AND application_deadline IS NOT NULL 
    AND application_deadline < CURDATE()
")->fetchColumn();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $pageTitle ?? 'Job Portal'; ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="assets/css/styles.css">
</head>
<body>
    <!-- Public Navigation -->
    <nav class="navbar navbar-expand-lg navbar-light bg-white sticky-top shadow-sm">
        <div class="container">
            <a class="navbar-brand fw-bold" href="index.php">
                🎯 Job Portal
            </a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav ms-auto">
                    <li class="nav-item">
                        <a class="nav-link <?php echo basename($_SERVER['PHP_SELF']) == 'index.php' ? 'active fw-bold' : ''; ?>" href="index.php">
                            🏠 Active Jobs
                            <?php if ($activeJobsCount > 0): ?>
                                <span class="badge bg-success rounded-pill ms-1"><?php echo $activeJobsCount; ?></span>
                            <?php endif; ?>
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link <?php echo basename($_SERVER['PHP_SELF']) == 'archive.php' ? 'active fw-bold' : ''; ?>" href="archive.php">
                            📚 Archive
                            <?php if ($archivedJobsCount > 0): ?>
                                <span class="badge bg-secondary rounded-pill ms-1"><?php echo $archivedJobsCount; ?></span>
                            <?php endif; ?>
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="admin/login.php">
                            🔐 Admin Login
                        </a>
                    </li>
                </ul>
            </div>
        </div>
    </nav>

    <!-- Main Content -->
    <main class="container my-4">
