<?php
require_once '../includes/session-check.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: ' . url('profile/edit-profile.php'));
    exit;
}

$userId = $candidateId;
$preferred_locations = trim($_POST['preferred_locations'] ?? '');
$willing_to_relocate = isset($_POST['willing_to_relocate']) ? 1 : 0;
$job_alert_email = isset($_POST['job_alert_email']) ? 1 : 0;

try {
    $sql = "UPDATE users SET 
            preferred_locations = ?,
            willing_to_relocate = ?,
            job_alert_email = ?
            WHERE id = ?";
    
    $stmt = $pdo->prepare($sql);
    $stmt->execute([
        $preferred_locations,
        $willing_to_relocate,
        $job_alert_email,
        $userId
    ]);
    
    // Invalidate recommendation cache — location preferences affect match scores
    $_jpCf = sys_get_temp_dir() . DIRECTORY_SEPARATOR . 'jp_rec_' . $userId . '.cache';
    if (file_exists($_jpCf)) { @unlink($_jpCf); }
    unset($_jpCf);

    header('Location: ' . url('profile/edit-profile.php?success=location_preferences_updated#preferences'));
    exit;
    
} catch(PDOException $e) {
    error_log("Update location preferences error: " . $e->getMessage());
    header('Location: ' . url('profile/edit-profile.php?error=Failed to update preferences'));
    exit;
}
?>
