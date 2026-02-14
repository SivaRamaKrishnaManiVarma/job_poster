<?php
require_once '../includes/session-check.php';

$eduId = (int)($_GET['id'] ?? 0);

if ($eduId <= 0) {
    header('Location: ' . url('profile/edit-profile.php?error=Invalid education ID'));
    exit;
}

try {
    // Verify ownership before deleting
    $stmt = $pdo->prepare("DELETE FROM user_education WHERE id = ? AND user_id = ?");
    $stmt->execute([$eduId, $candidateId]);
    
    if ($stmt->rowCount() > 0) {
        header('Location: ' . url('profile/edit-profile.php?success=education_deleted#education'));
    } else {
        header('Location: ' . url('profile/edit-profile.php?error=Education not found'));
    }
    exit;
    
} catch(PDOException $e) {
    error_log("Delete education error: " . $e->getMessage());
    header('Location: ' . url('profile/edit-profile.php?error=Failed to delete education'));
    exit;
}
?>
