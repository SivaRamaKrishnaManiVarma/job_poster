<?php
require_once '../includes/config.php';

if (php_sapi_name() !== 'cli') {
    session_start();
}

if (!isset($_SESSION['candidate_id'])) {
    header('Location: ' . url('auth/login.php'));
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: ' . url('profile/edit-profile.php'));
    exit;
}

$userId = $_SESSION['candidate_id'];

// Sanitize inputs
$degree = trim($_POST['degree'] ?? '');
$custom_degree = trim($_POST['custom_degree'] ?? '');
$institution = trim($_POST['institution'] ?? '');
$field_of_study = trim($_POST['field_of_study'] ?? '');
$start_year = !empty($_POST['start_year']) ? (int)$_POST['start_year'] : null;
$end_year = !empty($_POST['end_year']) ? (int)$_POST['end_year'] : null;
$percentage_cgpa = trim($_POST['percentage_cgpa'] ?? '');
$is_current = isset($_POST['is_current']) ? 1 : 0;

// Use custom degree if "Other" was selected
if ($degree === 'Other' && !empty($custom_degree)) {
    $degree = $custom_degree;
}

// Validation
if (empty($degree) || empty($institution)) {
    header('Location: ' . url('profile/edit-profile.php?error=Degree and Institution are required#education'));
    exit;
}

if (empty($start_year)) {
    header('Location: ' . url('profile/edit-profile.php?error=Start year is required#education'));
    exit;
}

// Validate end year is after start year
if (!$is_current && !empty($end_year) && $end_year < $start_year) {
    header('Location: ' . url('profile/edit-profile.php?error=End year must be after start year#education'));
    exit;
}

// If currently studying, set end_year to null
if ($is_current) {
    $end_year = null;
}

try {
    // Check if education already exists
    $checkStmt = $pdo->prepare("SELECT id FROM user_education WHERE user_id = ?");
    $checkStmt->execute([$userId]);
    $existing = $checkStmt->fetch();
    
    if ($existing) {
        // Update existing education
        $sql = "UPDATE user_education SET 
                degree = ?,
                institution = ?,
                field_of_study = ?,
                start_year = ?,
                end_year = ?,
                percentage_cgpa = ?,
                is_current = ?
                WHERE user_id = ?";
        
        $stmt = $pdo->prepare($sql);
        $stmt->execute([
            $degree,
            $institution,
            $field_of_study,
            $start_year,
            $end_year,
            $percentage_cgpa,
            $is_current,
            $userId
        ]);
    } else {
        // Insert new education
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
    }
    
    header('Location: ' . url('profile/edit-profile.php?success=education_updated#education'));
    exit;
    
} catch(PDOException $e) {
    error_log("Update education error: " . $e->getMessage());
    header('Location: ' . url('profile/edit-profile.php?error=Failed to save education. Please try again#education'));
    exit;
}
?>
