<?php
require_once '../includes/session-check.php';

$expId = (int)($_GET['id'] ?? 0);

if ($expId <= 0) {
    header('Location: ' . url('profile/edit-profile.php?error=Invalid experience ID'));
    exit;
}

try {
    // Verify ownership before deleting
    $stmt = $pdo->prepare("DELETE FROM user_experience WHERE id = ? AND user_id = ?");
    $stmt->execute([$expId, $candidateId]);
    
    if ($stmt->rowCount() > 0) {
        header('Location: ' . url('profile/edit-profile.php?success=experience_deleted#experience'));
    } else {
        header('Location: ' . url('profile/edit-profile.php?error=Experience not found'));
    }
    exit;
    
} catch(PDOException $e) {
    error_log("Delete experience error: " . $e->getMessage());
    header('Location: ' . url('profile/edit-profile.php?error=Failed to delete experience'));
    exit;
}
?>
