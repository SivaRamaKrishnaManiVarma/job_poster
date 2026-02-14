<?php
// Start session only if not in CLI mode
if (php_sapi_name() !== 'cli') {
    session_start();
}

// ============================================
// ENVIRONMENT DETECTION
// ============================================
// Check if running from command line (cron) or web
if (php_sapi_name() === 'cli') {
    // Running from command line - assume localhost for development
    $isLocalhost = true;
} else {
    // Running from web - check HTTP_HOST
    $isLocalhost = (isset($_SERVER['HTTP_HOST']) && 
                    ($_SERVER['HTTP_HOST'] == "localhost" || 
                     $_SERVER['HTTP_HOST'] == "127.0.0.1"));
}

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

// ============================================
// BASE URL CONFIGURATION
// ============================================
// Generate full base URL
if (php_sapi_name() === 'cli') {
    // Command line - use hardcoded URL
    if ($isLocalhost) {
        define('BASE_URL', 'http://localhost' . BASE_PATH);
    } else {
        define('BASE_URL', 'https://findwork.mindrevel.in/' . BASE_PATH); // Update for production
    }
} else {
    // Web request - detect from HTTP_HOST
    $protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';
    define('BASE_URL', $protocol . '://' . $_SERVER['HTTP_HOST'] . BASE_PATH);
}

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
    // Log error
    error_log("Database connection failed: " . $e->getMessage());
    
    // Show appropriate message based on context
    if (php_sapi_name() === 'cli') {
        die("Database connection failed. Check error logs.\n");
    } else {
        die("Database connection error. Please contact administrator.");
    }
}

// ============================================
// TIMEZONE
// ============================================
date_default_timezone_set('Asia/Kolkata');

// ============================================
// DEBUG MODE (Comment out in production)
// ============================================
// Uncomment below to see environment info (web only)
/*
if (php_sapi_name() !== 'cli') {
    echo "<!-- Environment: " . ENVIRONMENT . " -->\n";
    echo "<!-- Base Path: " . BASE_PATH . " -->\n";
    echo "<!-- Base URL: " . BASE_URL . " -->\n";
}
*/
?>
