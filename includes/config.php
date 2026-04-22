<?php
// Start session only if not in CLI mode
if (php_sapi_name() !== 'cli') {
    session_start();
}

// ============================================
// LOAD ENVIRONMENT FILE (.env)
// ============================================
// env.php provides loadEnvFile(). The .env file is optional — if absent,
// we fall through to the hardcoded fallback values below.
require_once __DIR__ . '/env.php';
loadEnvFile(__DIR__ . '/../.env');

// ============================================
// ENVIRONMENT DETECTION
// ============================================
if (php_sapi_name() === 'cli') {
    $isLocalhost = true;
} else {
    $isLocalhost = (isset($_SERVER['HTTP_HOST']) &&
                    ($_SERVER['HTTP_HOST'] === 'localhost' ||
                     $_SERVER['HTTP_HOST'] === '127.0.0.1'));
}

// ============================================
// DATABASE CONFIGURATION
// Priority: .env value → legacy hardcoded fallback
// All constant names are unchanged — zero callers affected.
// ============================================
if (getenv('DB_NAME') !== false) {
    // .env file is present and parsed
    define('DB_HOST',    getenv('DB_HOST') ?: 'localhost');
    define('DB_NAME',    getenv('DB_NAME'));
    define('DB_USER',    getenv('DB_USER') ?: 'root');
    define('DB_PASS',    getenv('DB_PASS') ?: '');
    define('ENVIRONMENT', getenv('APP_ENV') ?: ($isLocalhost ? 'local' : 'production'));
} elseif ($isLocalhost) {
    // Legacy localhost fallback
    define('DB_HOST', 'localhost');
    define('DB_NAME', 'job_portal');
    define('DB_USER', 'root');
    define('DB_PASS', '');
    define('ENVIRONMENT', 'local');
} else {
    // Legacy production fallback
    define('DB_HOST', 'localhost');
    define('DB_NAME', 'u409529889_db_JMqRABK6');
    define('DB_USER', 'u409529889_usr_JMqRABK6');
    define('DB_PASS', 'pL8@?CBvh');
    define('ENVIRONMENT', 'production');
}

// ============================================
// BASE PATH CONFIGURATION (For URLs)
// ============================================
if (getenv('APP_BASE_PATH') !== false) {
    define('BASE_PATH', getenv('APP_BASE_PATH'));
} elseif ($isLocalhost) {
    define('BASE_PATH', '/job_poster');
} else {
    define('BASE_PATH', '');
}

// ============================================
// BASE URL CONFIGURATION
// ============================================
if (php_sapi_name() === 'cli') {
    if (getenv('APP_URL') !== false) {
        define('BASE_URL', getenv('APP_URL'));
    } elseif ($isLocalhost) {
        define('BASE_URL', 'http://localhost' . BASE_PATH);
    } else {
        define('BASE_URL', 'https://findworks.mindrevel.in/' . BASE_PATH);
    }
} else {
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
