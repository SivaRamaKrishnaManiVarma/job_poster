<?php
require_once '../includes/session-check.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: ' . url('profile/edit-profile.php'));
    exit;
}

$userId = $candidateId;

// Sanitize inputs
$designation = trim($_POST['designation'] ?? '');
$company_name = trim($_POST['company_name'] ?? '');
$employment_type = trim($_POST['employment_type'] ?? 'Full-time');
$location = trim($_POST['location'] ?? '');
$start_date = trim($_POST['start_date'] ?? '');
$end_date = trim($_POST['end_date'] ?? '');
$description = trim($_POST['description'] ?? '');
$is_current = isset($_POST['is_current']) ? 1 : 0;

// Validation
if (empty($designation) || empty($company_name) || empty($start_date)) {
    header('Location: ' . url('profile/edit-profile.php?error=Job title, company name and start date are required'));
    exit;
}

// If currently working, set end_date to null
if ($is_current) {
    $end_date = null;
}

try {
    $sql = "INSERT INTO user_experience 
            (user_id, designation, company_name, employment_type, location, start_date, end_date, is_current, description) 
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)";
    
    $stmt = $pdo->prepare($sql);
    $stmt->execute([
        $userId,
        $designation,
        $company_name,
        $employment_type,
        $location,
        $start_date,
        $end_date,
        $is_current,
        $description
    ]);
    
    header('Location: ' . url('profile/edit-profile.php?success=experience_added#experience'));
    exit;
    
} catch(PDOException $e) {
    error_log("Add experience error: " . $e->getMessage());
    header('Location: ' . url('profile/edit-profile.php?error=Failed to add experience'));
    exit;
}
?>
