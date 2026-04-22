<?php
require_once '../includes/session-check.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: ' . url('profile/edit-profile.php'));
    exit;
}

$userId = $candidateId;
$job_category_id = (int)($_POST['job_category_id'] ?? 0);
$priority = (int)($_POST['priority'] ?? 2);

if ($job_category_id <= 0) {
    header('Location: ' . url('profile/edit-profile.php?error=Please select a job category'));
    exit;
}

// Check if already exists
try {
    $checkStmt = $pdo->prepare("SELECT id FROM user_job_preferences WHERE user_id = ? AND job_category_id = ?");
    $checkStmt->execute([$userId, $job_category_id]);
    
    if ($checkStmt->fetch()) {
        header('Location: ' . url('profile/edit-profile.php?error=This category is already in your interests'));
        exit;
    }
} catch(PDOException $e) {
    error_log("Check preference error: " . $e->getMessage());
}

try {
    $sql = "INSERT INTO user_job_preferences (user_id, job_category_id, priority) VALUES (?, ?, ?)";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$userId, $job_category_id, $priority]);
    
    // Invalidate recommendation cache — category preference affects match scores
    $_jpCf = sys_get_temp_dir() . DIRECTORY_SEPARATOR . 'jp_rec_' . $userId . '.cache';
    if (file_exists($_jpCf)) { @unlink($_jpCf); }
    unset($_jpCf);

    header('Location: ' . url('profile/edit-profile.php?success=preference_added#preferences'));
    exit;
    
} catch(PDOException $e) {
    error_log("Add preference error: " . $e->getMessage());
    header('Location: ' . url('profile/edit-profile.php?error=Failed to add job interest'));
    exit;
}
?>
