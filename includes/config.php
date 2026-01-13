<?php
session_start();

// ============================================
// ENVIRONMENT DETECTION
// ============================================
$isLocalhost = (isset($_SERVER['HTTP_HOST']) && 
                ($_SERVER['HTTP_HOST'] == "localhost" || 
                 $_SERVER['HTTP_HOST'] == "127.0.0.1"));

// ============================================
// DATABASE CONFIGURATION
// ============================================
if ($isLocalhost) {
    // LOCALHOST Database
    define('DB_HOST', 'localhost');
    define('DB_NAME', 'job_portal');
    define('DB_USER', 'root');
    define('DB_PASS', '');
    define('ENVIRONMENT', 'local');
} else {
    // PRODUCTION Database
    define('DB_HOST', 'localhost');
    define('DB_NAME', 'u409529889_job_poster');
    define('DB_USER', 'u409529889_job_poster');
    define('DB_PASS', '!teKKdx9G');
    define('ENVIRONMENT', 'production');
}

// ============================================
// BASE PATH CONFIGURATION (For URLs)
// ============================================
if ($isLocalhost) {
    // LOCALHOST - with subdirectory /job_poster
    define('BASE_PATH', '/job_poster');
} else {
    // PRODUCTION - at root level
    define('BASE_PATH', '');
}

// Generate full base URL
$protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';
define('BASE_URL', $protocol . '://' . $_SERVER['HTTP_HOST'] . BASE_PATH);

// ============================================
// URL HELPER FUNCTION
// ============================================
/**
 * Generate URL with correct base path
 * @param string $path - Path to append (e.g., 'jobs/my-job' or '?category=5')
 * @return string - Complete path with base
 */
function url($path = '') {
    $path = ltrim($path, '/');
    return BASE_PATH . ($path ? '/' . $path : '');
}

/**
 * Generate absolute URL (with domain)
 * @param string $path - Path to append
 * @return string - Complete URL with domain
 */
function fullUrl($path = '') {
    $path = ltrim($path, '/');
    return BASE_URL . ($path ? '/' . $path : '');
}

// ============================================
// DATABASE CONNECTION
// ============================================
try {
    $pdo = new PDO(
        "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=utf8mb4",
        DB_USER, 
        DB_PASS,
        [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES => false
        ]
    );
} catch(PDOException $e) {
    die("Database connection failed: " . $e->getMessage());
}

// ============================================
// DEBUG MODE (Comment out in production)
// ============================================
// Uncomment below to see environment info
/*
echo "<!-- Environment: " . ENVIRONMENT . " -->\n";
echo "<!-- Base Path: " . BASE_PATH . " -->\n";
echo "<!-- Base URL: " . BASE_URL . " -->\n";
*/
?>
