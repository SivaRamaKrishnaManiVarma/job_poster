<?php
/**
 * Protect candidate pages
 * Use: require_once '../includes/session-check.php';
 * at the top of profile pages
 */

// Config already loaded, just need auth functions
if (!file_exists(__DIR__ . '/config.php')) {
    die('Configuration file not found');
}

require_once __DIR__ . '/config.php';
require_once __DIR__ . '/auth-functions.php';

// Check authentication
if (!isCandidateLoggedIn()) {
    $currentUrl = urlencode($_SERVER['REQUEST_URI']);
    header('Location: ' . fullUrl('auth/login.php') . '?redirect=' . $currentUrl);
    exit;
}

// Get current user for protected pages
$currentUser = getCurrentCandidate($pdo);
$candidateId = $_SESSION['candidate_id'];

// If user data couldn't be fetched, logout
if (!$currentUser) {
    logoutCandidate();
    header('Location: ' . fullUrl('auth/login.php') . '?error=session_expired');
    exit;
}
?>
