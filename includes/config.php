<?php
session_start();

if (isset($_SERVER['HTTP_HOST']) && ($_SERVER['HTTP_HOST'] == "localhost" || $_SERVER['HTTP_HOST'] == "127.0.0.1")) {
    define('DB_HOST', 'localhost');
    define('DB_NAME', 'job_portal');
    define('DB_USER', 'root');
    define('DB_PASS', '');

}
else {
    define('DB_HOST', 'localhost');
    define('DB_NAME', 'job_portal');
    define('DB_USER', 'root');
    define('DB_PASS', '');
}
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
?>
