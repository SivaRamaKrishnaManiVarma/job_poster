<?php
require_once '../includes/config.php';

// Start session
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
$errors = [];

// Sanitize inputs
$full_name = trim($_POST['full_name'] ?? '');
$phone = trim($_POST['phone'] ?? '');
$location = trim($_POST['location'] ?? '');
$bio = trim($_POST['bio'] ?? '');
$linkedin_url = trim($_POST['linkedin_url'] ?? '');
$github_url = trim($_POST['github_url'] ?? '');
$portfolio_url = trim($_POST['portfolio_url'] ?? '');
$current_company = trim($_POST['current_company'] ?? '');
$current_designation = trim($_POST['current_designation'] ?? '');
$total_experience_years = (int)($_POST['total_experience_years'] ?? 0);
$preferred_work_mode_id = !empty($_POST['preferred_work_mode_id']) ? (int)$_POST['preferred_work_mode_id'] : null;
$preferred_job_type_id = !empty($_POST['preferred_job_type_id']) ? (int)$_POST['preferred_job_type_id'] : null;
$expected_salary_min = !empty($_POST['expected_salary_min']) ? (float)$_POST['expected_salary_min'] : null;
$expected_salary_max = !empty($_POST['expected_salary_max']) ? (float)$_POST['expected_salary_max'] : null;

// Validate
if (empty($full_name)) {
    $errors[] = 'Full name is required';
}

if (!empty($phone) && !preg_match('/^[0-9]{10}$/', $phone)) {
    $errors[] = 'Phone must be 10 digits';
}

// Get current user data
try {
    $stmt = $pdo->prepare("SELECT profile_photo FROM users WHERE id = ?");
    $stmt->execute([$userId]);
    $currentUser = $stmt->fetch();
} catch(PDOException $e) {
    error_log("Database error: " . $e->getMessage());
    header('Location: ' . url('profile/edit-profile.php?error=Database error#basic'));
    exit;
}

// Handle profile photo upload
$profile_photo = $currentUser['profile_photo'] ?? '';
if (!empty($_FILES['profile_photo']['name'])) {
    $file = $_FILES['profile_photo'];
    $allowed = ['image/jpeg', 'image/png', 'image/jpg'];
    $maxSize = 2 * 1024 * 1024; // 2MB
    
    if ($file['error'] === UPLOAD_ERR_OK) {
        if (!in_array($file['type'], $allowed)) {
            $errors[] = 'Only JPG, JPEG, PNG images allowed';
        } elseif ($file['size'] > $maxSize) {
            $errors[] = 'Image must be less than 2MB';
        } else {
            // Generate unique filename
            $extension = pathinfo($file['name'], PATHINFO_EXTENSION);
            $filename = 'profile_' . $userId . '_' . time() . '.' . $extension;
            $uploadDir = __DIR__ . '/../uploads/profile_photos/';
            $uploadPath = $uploadDir . $filename;
            
            // Create directory if not exists
            if (!is_dir($uploadDir)) {
                mkdir($uploadDir, 0755, true);
            }
            
            // Delete old photo if exists
            if (!empty($currentUser['profile_photo']) && file_exists($uploadDir . $currentUser['profile_photo'])) {
                unlink($uploadDir . $currentUser['profile_photo']);
            }
            
            // Upload new photo
            if (move_uploaded_file($file['tmp_name'], $uploadPath)) {
                $profile_photo = $filename;
            } else {
                $errors[] = 'Failed to upload profile photo';
            }
        }
    }
}

if (!empty($errors)) {
    header('Location: ' . url('profile/edit-profile.php?error=' . urlencode(implode(', ', $errors)) . '#basic'));
    exit;
}

// Update database
try {
    $sql = "UPDATE users SET 
            full_name = ?,
            phone = ?,
            location = ?,
            bio = ?,
            linkedin_url = ?,
            github_url = ?,
            portfolio_url = ?,
            current_company = ?,
            current_designation = ?,
            total_experience_years = ?,
            preferred_work_mode_id = ?,
            preferred_job_type_id = ?,
            expected_salary_min = ?,
            expected_salary_max = ?,
            profile_photo = ?
            WHERE id = ?";
    
    $stmt = $pdo->prepare($sql);
    $stmt->execute([
        $full_name,
        $phone,
        $location,
        $bio,
        $linkedin_url,
        $github_url,
        $portfolio_url,
        $current_company,
        $current_designation,
        $total_experience_years,
        $preferred_work_mode_id,
        $preferred_job_type_id,
        $expected_salary_min,
        $expected_salary_max,
        $profile_photo,
        $userId
    ]);
    
    // Update session name if changed
    $_SESSION['candidate_name'] = $full_name;
    
    // Invalidate recommendation cache — profile changes affect match scores
    $_jpCf = sys_get_temp_dir() . DIRECTORY_SEPARATOR . 'jp_rec_' . $userId . '.cache';
    if (file_exists($_jpCf)) { @unlink($_jpCf); }
    unset($_jpCf);

    header('Location: ' . url('profile/edit-profile.php?success=profile_updated#basic'));
    exit;
    
} catch(PDOException $e) {
    error_log("Profile update error: " . $e->getMessage());
    header('Location: ' . url('profile/edit-profile.php?error=Update failed. Please try again#basic'));
    exit;
}
?>
