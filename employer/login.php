<?php
require_once '../includes/config.php';
require_once '../includes/functions.php';

// Already logged in
if (isEmployer()) {
    redirect(url('employer/dashboard.php'));
}

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = sanitize($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';

    if ($username && $password) {
        $stmt = $pdo->prepare("SELECT * FROM employers WHERE username = ? AND is_active = 1");
        $stmt->execute([$username]);
        $employer = $stmt->fetch();

        if ($employer && password_verify($password, $employer['password'])) {
            $_SESSION['employer_id']       = $employer['id'];
            $_SESSION['employer_username'] = $employer['username'];
            $_SESSION['employer_company']  = $employer['company_name'];
            $_SESSION['is_employer']       = true;
            redirect(url('employer/dashboard.php'));
        } else {
            $error = 'Invalid username or password.';
        }
    } else {
        $error = 'Please fill in all fields.';
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Employer Login - Job Portal</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body { background: #f1f5f9; display: flex; align-items: center; justify-content: center; min-height: 100vh; }
        .login-card { background: white; border-radius: 12px; padding: 40px; box-shadow: 0 4px 24px rgba(0,0,0,0.08); width: 100%; max-width: 420px; }
        .login-logo { font-size: 2.5rem; text-align: center; margin-bottom: 8px; }
        .login-title { text-align: center; font-weight: 700; font-size: 1.4rem; margin-bottom: 4px; color: #1e293b; }
        .login-sub { text-align: center; color: #64748b; font-size: 0.9rem; margin-bottom: 28px; }
        .btn-login { background: #6366f1; border: none; font-weight: 600; padding: 10px; }
        .btn-login:hover { background: #4f46e5; }
    </style>
</head>
<body>
<div class="login-card">
    <div class="login-logo">🏢</div>
    <div class="login-title">Employer Portal</div>
    <div class="login-sub">Sign in to manage your job postings</div>

    <?php if ($error): ?>
        <div class="alert alert-danger"><?= $error ?></div>
    <?php endif; ?>

    <form method="POST">
        <div class="mb-3">
            <label class="form-label fw-semibold">Username</label>
            <input type="text" name="username" class="form-control" placeholder="Enter username" required autofocus>
        </div>
        <div class="mb-4">
            <label class="form-label fw-semibold">Password</label>
            <input type="password" name="password" class="form-control" placeholder="Enter password" required>
        </div>
        <button type="submit" class="btn btn-primary btn-login w-100">Sign In</button>
    </form>

    <div class="text-center mt-3">
        <small class="text-muted">
            <a href="<?= url('index.php') ?>" class="text-decoration-none">← Back to Job Portal</a>
        </small>
    </div>
</div>
</body>
</html>
