<?php
require_once '../includes/config.php';

// Start session and check if user is logged in
if (php_sapi_name() !== 'cli') {
    session_start();
}

if (!isset($_SESSION['candidate_id'])) {
    header('Location: ' . url('auth/login.php'));
    exit;
}

$userId = $_SESSION['candidate_id'];

// Check if this is a POST request with file
if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !isset($_FILES['resume'])) {
    header('Location: ' . url('profile/edit-profile.php?error=No file uploaded'));
    exit;
}

$file = $_FILES['resume'];

// Validation
$allowedTypes = ['application/pdf'];
$maxSize = 5 * 1024 * 1024; // 5MB

if ($file['error'] !== UPLOAD_ERR_OK) {
    header('Location: ' . url('profile/edit-profile.php?error=Upload failed. Please try again#resume'));
    exit;
}

// Check file type
$finfo = finfo_open(FILEINFO_MIME_TYPE);
$mimeType = finfo_file($finfo, $file['tmp_name']);
finfo_close($finfo);

if (!in_array($mimeType, $allowedTypes) && $file['type'] !== 'application/pdf') {
    header('Location: ' . url('profile/edit-profile.php?error=Only PDF files are allowed#resume'));
    exit;
}

if ($file['size'] > $maxSize) {
    header('Location: ' . url('profile/edit-profile.php?error=File size must be less than 5MB#resume'));
    exit;
}

// Generate unique filename
$extension = pathinfo($file['name'], PATHINFO_EXTENSION);
if (empty($extension)) {
    $extension = 'pdf';
}
$filename = 'resume_' . $userId . '_' . time() . '.' . $extension;
$uploadDir = __DIR__ . '/../uploads/resumes/';
$uploadPath = $uploadDir . $filename;

// Create directory if not exists
if (!is_dir($uploadDir)) {
    mkdir($uploadDir, 0755, true);
}

// Get old resume path
try {
    $stmt = $pdo->prepare("SELECT resume_path FROM users WHERE id = ?");
    $stmt->execute([$userId]);
    $user = $stmt->fetch();
    $oldResume = $user['resume_path'] ?? '';
} catch(PDOException $e) {
    error_log("Database error: " . $e->getMessage());
    $oldResume = '';
}

// Delete old resume if exists
if (!empty($oldResume) && file_exists($uploadDir . $oldResume)) {
    unlink($uploadDir . $oldResume);
}

// Upload file
if (move_uploaded_file($file['tmp_name'], $uploadPath)) {
    // Update database
    try {
        $stmt = $pdo->prepare("UPDATE users SET resume_path = ? WHERE id = ?");
        $stmt->execute([$filename, $userId]);
        
        header('Location: ' . url('profile/edit-profile.php?success=resume_uploaded#resume'));
        exit;
    } catch(PDOException $e) {
        error_log("Resume update error: " . $e->getMessage());
        // Delete uploaded file if database update fails
        if (file_exists($uploadPath)) {
            unlink($uploadPath);
        }
        header('Location: ' . url('profile/edit-profile.php?error=Database update failed#resume'));
        exit;
    }
} else {
    header('Location: ' . url('profile/edit-profile.php?error=File upload failed. Check folder permissions#resume'));
    exit;
}
?>
