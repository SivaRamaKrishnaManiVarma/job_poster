<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $pageTitle ?? 'Job Portal'; ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="<?php echo isset($isAdmin) ? '../' : ''; ?>assets/css/style.css" rel="stylesheet">
</head>
<body>
<nav class="navbar navbar-expand-lg navbar-dark bg-primary">
    <div class="container">
        <a class="navbar-brand" href="<?php echo isset($isAdmin) ? '../index.php' : 'index.php'; ?>">Job Portal</a>
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
            <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse" id="navbarNav">
            <ul class="navbar-nav ms-auto">
                <?php if (isAdmin()): ?>
                    <li class="nav-item"><a class="nav-link" href="<?php echo isset($isAdmin) ? 'index.php' : 'admin/index.php'; ?>">Dashboard</a></li>
                    <li class="nav-item"><a class="nav-link" href="<?php echo isset($isAdmin) ? 'jobs.php' : 'admin/jobs.php'; ?>">Manage Jobs</a></li>
                    <li class="nav-item"><a class="nav-link" href="<?php echo isset($isAdmin) ? 'logout.php' : 'admin/logout.php'; ?>">Logout</a></li>
                <?php endif; ?>
            </ul>
        </div>
    </div>
</nav>
<main class="container my-4">
