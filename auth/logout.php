<?php
require_once '../includes/config.php';
require_once '../includes/auth-functions.php';

// Logout candidate
logoutCandidate();

// Redirect to home with message
header('Location: ' . fullUrl('auth/login.php') . '?logged_out=1');
exit;
?>
