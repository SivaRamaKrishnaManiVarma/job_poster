<?php
require_once '../includes/session-check.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: ' . url('profile/edit-profile.php'));
    exit;
}

$userId = $candidateId;

// Sanitize inputs
$skill_name = trim($_POST['skill_name'] ?? '');
$proficiency_level = trim($_POST['proficiency_level'] ?? 'Intermediate');
$years_of_experience = (int)($_POST['years_of_experience'] ?? 0);

// Validation
if (empty($skill_name)) {
    header('Location: ' . url('profile/edit-profile.php?error=Skill name is required'));
    exit;
}

// Check if skill already exists for this user
try {
    $checkStmt = $pdo->prepare("SELECT id FROM user_skills WHERE user_id = ? AND skill_name = ?");
    $checkStmt->execute([$userId, $skill_name]);
    
    if ($checkStmt->fetch()) {
        header('Location: ' . url('profile/edit-profile.php?error=Skill already exists'));
        exit;
    }
} catch(PDOException $e) {
    error_log("Check skill error: " . $e->getMessage());
}

try {
    $sql = "INSERT INTO user_skills (user_id, skill_name, proficiency_level, years_of_experience) 
            VALUES (?, ?, ?, ?)";
    
    $stmt = $pdo->prepare($sql);
    $stmt->execute([
        $userId,
        $skill_name,
        $proficiency_level,
        $years_of_experience
    ]);
    
    header('Location: ' . url('profile/edit-profile.php?success=skill_added#skills'));
    exit;
    
} catch(PDOException $e) {
    error_log("Add skill error: " . $e->getMessage());
    header('Location: ' . url('profile/edit-profile.php?error=Failed to add skill'));
    exit;
}
?>
