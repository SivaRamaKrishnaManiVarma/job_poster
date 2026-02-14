<?php
require_once '../includes/session-check.php';

$prefId = (int)($_GET['id'] ?? 0);

if ($prefId <= 0) {
    header('Location: ' . url('profile/edit-profile.php?error=Invalid preference ID'));
    exit;
}

try {
    $stmt = $pdo->prepare("DELETE FROM user_job_preferences WHERE id = ? AND user_id = ?");
    $stmt->execute([$prefId, $candidateId]);
    
    if ($stmt->rowCount() > 0) {
        header('Location: ' . url('profile/edit-profile.php?success=preference_deleted#preferences'));
    } else {
        header('Location: ' . url('profile/edit-profile.php?error=Preference not found'));
    }
    exit;
    
} catch(PDOException $e) {
    error_log("Delete preference error: " . $e->getMessage());
    header('Location: ' . url('profile/edit-profile.php?error=Failed to delete preference'));
    exit;
}
?>
