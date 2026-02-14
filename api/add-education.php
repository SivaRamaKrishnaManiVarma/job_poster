<?php
require_once '../includes/session-check.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: ' . url('profile/edit-profile.php'));
    exit;
}

$userId = $candidateId;

// Sanitize inputs
$degree = trim($_POST['degree'] ?? '');
$institution = trim($_POST['institution'] ?? '');
$field_of_study = trim($_POST['field_of_study'] ?? '');
$start_year = !empty($_POST['start_year']) ? (int)$_POST['start_year'] : null;
$end_year = !empty($_POST['end_year']) ? (int)$_POST['end_year'] : null;
$percentage_cgpa = trim($_POST['percentage_cgpa'] ?? '');
$is_current = isset($_POST['is_current']) ? 1 : 0;

// Validation
if (empty($degree) || empty($institution)) {
    header('Location: ' . url('profile/edit-profile.php?error=Degree and Institution are required'));
    exit;
}

// If currently studying, set end_year to null
if ($is_current) {
    $end_year = null;
}

try {
    $sql = "INSERT INTO user_education 
            (user_id, degree, institution, field_of_study, start_year, end_year, percentage_cgpa, is_current) 
            VALUES (?, ?, ?, ?, ?, ?, ?, ?)";
    
    $stmt = $pdo->prepare($sql);
    $stmt->execute([
        $userId,
        $degree,
        $institution,
        $field_of_study,
        $start_year,
        $end_year,
        $percentage_cgpa,
        $is_current
    ]);
    
    header('Location: ' . url('profile/edit-profile.php?success=education_added#education'));
    exit;
    
} catch(PDOException $e) {
    error_log("Add education error: " . $e->getMessage());
    header('Location: ' . url('profile/edit-profile.php?error=Failed to add education'));
    exit;
}
?>
