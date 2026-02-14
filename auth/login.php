<?php
require_once '../includes/config.php';
require_once '../includes/auth-functions.php';

// Redirect if already logged in
if (isCandidateLoggedIn()) {
    header('Location: ' . fullUrl('profile/dashboard.php'));
    exit;
}

$error = '';
$success = '';

// Check for registration success message
if (isset($_GET['registered'])) {
    $success = 'Registration successful! Please login with your credentials.';
}

// Check for logout message
if (isset($_GET['logged_out'])) {
    $success = 'You have been logged out successfully.';
}

// Handle login form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    
    if (empty($email) || empty($password)) {
        $error = 'Please enter both email and password';
    } elseif (loginCandidateUser($pdo, $email, $password)) {
        // Check for redirect parameter
        $redirect = $_GET['redirect'] ?? fullUrl('profile/dashboard.php');
        header('Location: ' . $redirect);
        exit;
    } else {
        $error = 'Invalid email or password';
    }
}

include '../includes/header.php';
?>

<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-md-5 col-lg-4">
            <div class="card shadow-lg border-0">
                <div class="card-body p-5">
                    <!-- Header -->
                    <div class="text-center mb-4">
                        <div class="mb-3">
                            <i class="fas fa-sign-in-alt fa-3x text-primary"></i>
                        </div>
                        <h2 class="fw-bold mb-2">Welcome Back</h2>
                        <p class="text-muted">Login to your account</p>
                    </div>
                    
                    <!-- Success Message -->
                    <?php if ($success): ?>
                        <div class="alert alert-success alert-dismissible fade show" role="alert">
                            <i class="fas fa-check-circle me-2"></i>
                            <?= htmlspecialchars($success) ?>
                            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                        </div>
                    <?php endif; ?>
                    
                    <!-- Error Message -->
                    <?php if ($error): ?>
                        <div class="alert alert-danger alert-dismissible fade show" role="alert">
                            <i class="fas fa-exclamation-circle me-2"></i>
                            <?= htmlspecialchars($error) ?>
                            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                        </div>
                    <?php endif; ?>
                    
                    <!-- Login Form -->
                    <form method="POST" action="">
                        <div class="mb-3">
                            <label class="form-label fw-semibold">Email Address</label>
                            <div class="input-group">
                                <span class="input-group-text"><i class="fas fa-envelope"></i></span>
                                <input type="email" name="email" class="form-control" 
                                       placeholder="your.email@example.com"
                                       value="<?= htmlspecialchars($_POST['email'] ?? '') ?>" 
                                       required autofocus>
                            </div>
                        </div>
                        
                        <div class="mb-4">
                            <label class="form-label fw-semibold">Password</label>
                            <div class="input-group">
                                <span class="input-group-text"><i class="fas fa-lock"></i></span>
                                <input type="password" name="password" class="form-control" 
                                       placeholder="Enter your password"
                                       required>
                            </div>
                        </div>
                        
                        <button type="submit" class="btn btn-primary w-100 py-2 mb-3">
                            <i class="fas fa-sign-in-alt me-2"></i>Login
                        </button>
                        
                        <div class="text-center">
                            <p class="mb-0">Don't have an account? 
                                <a href="<?= url('auth/register.php') ?>" class="text-decoration-none fw-semibold">Register here</a>
                            </p>
                        </div>
                    </form>
                </div>
            </div>
            
            <!-- Admin Login Link -->
            <div class="text-center mt-3">
                <small class="text-muted">
                    Are you an admin? <a href="<?= url('admin/login.php') ?>">Admin Login</a>
                </small>
            </div>
        </div>
    </div>
</div>

<style>
.input-group-text {
    background-color: #f8f9fa;
    border-right: none;
}
.input-group .form-control {
    border-left: none;
}
.input-group .form-control:focus {
    border-color: #ced4da;
    box-shadow: none;
}
</style>

<?php include '../includes/footer.php'; ?>
