<?php
require_once '../includes/session-check.php';

$skillId = (int)($_GET['id'] ?? 0);

if ($skillId <= 0) {
    header('Location: ' . url('profile/edit-profile.php?error=Invalid skill ID'));
    exit;
}

try {
    // Verify ownership before deleting
    $stmt = $pdo->prepare("DELETE FROM user_skills WHERE id = ? AND user_id = ?");
    $stmt->execute([$skillId, $candidateId]);
    
    if ($stmt->rowCount() > 0) {
        header('Location: ' . url('profile/edit-profile.php?success=skill_deleted#skills'));
    } else {
        header('Location: ' . url('profile/edit-profile.php?error=Skill not found'));
    }
    exit;
    
} catch(PDOException $e) {
    error_log("Delete skill error: " . $e->getMessage());
    header('Location: ' . url('profile/edit-profile.php?error=Failed to delete skill'));
    exit;
}
?>
