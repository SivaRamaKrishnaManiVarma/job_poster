<?php
/**
 * Candidate Authentication Functions
 * Completely separate from admin authentication
 * Works with existing config.php structure
 */

// Hash password
function hashUserPassword($password) {
    return password_hash($password, PASSWORD_BCRYPT, ['cost' => 12]);
}

// Verify password
function verifyUserPassword($password, $hash) {
    return password_verify($password, $hash);
}

// Check if email exists
function userEmailExists($pdo, $email) {
    try {
        $stmt = $pdo->prepare("SELECT id FROM users WHERE email = ?");
        $stmt->execute([$email]);
        return $stmt->fetch() !== false;
    } catch(PDOException $e) {
        error_log("Email check error: " . $e->getMessage());
        return false;
    }
}

// Create new user
function createCandidateUser($pdo, $data) {
    try {
        $sql = "INSERT INTO users (full_name, email, password_hash, phone, location) 
                VALUES (?, ?, ?, ?, ?)";
        $stmt = $pdo->prepare($sql);
        return $stmt->execute([
            $data['full_name'],
            $data['email'],
            hashUserPassword($data['password']),
            $data['phone'] ?? null,
            $data['location'] ?? null
        ]);
    } catch(PDOException $e) {
        error_log("User creation error: " . $e->getMessage());
        return false;
    }
}

// Login user
function loginCandidateUser($pdo, $email, $password) {
    try {
        $stmt = $pdo->prepare("SELECT * FROM users WHERE email = ? AND is_active = 1");
        $stmt->execute([$email]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if ($user && verifyUserPassword($password, $user['password_hash'])) {
            // Update last login
            $updateStmt = $pdo->prepare("UPDATE users SET last_login = NOW() WHERE id = ?");
            $updateStmt->execute([$user['id']]);
            
            // Set session (DIFFERENT from admin to avoid conflicts)
            $_SESSION['candidate_id'] = $user['id'];
            $_SESSION['candidate_name'] = $user['full_name'];
            $_SESSION['candidate_email'] = $user['email'];
            $_SESSION['user_type'] = 'candidate'; // Differentiator
            
            return true;
        }
        return false;
    } catch(PDOException $e) {
        error_log("Login error: " . $e->getMessage());
        return false;
    }
}

// Check if candidate is logged in (won't interfere with admin)
function isCandidateLoggedIn() {
    return isset($_SESSION['candidate_id']) && 
           isset($_SESSION['user_type']) && 
           $_SESSION['user_type'] === 'candidate';
}

// Get current candidate
function getCurrentCandidate($pdo) {
    if (!isCandidateLoggedIn()) {
        return null;
    }
    
    try {
        $stmt = $pdo->prepare("SELECT * FROM users WHERE id = ?");
        $stmt->execute([$_SESSION['candidate_id']]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    } catch(PDOException $e) {
        error_log("Get candidate error: " . $e->getMessage());
        return null;
    }
}

// Logout candidate (doesn't touch admin session)
function logoutCandidate() {
    unset($_SESSION['candidate_id']);
    unset($_SESSION['candidate_name']);
    unset($_SESSION['candidate_email']);
    unset($_SESSION['user_type']);
}

// Get profile completion percentage
function getCandidateProfileCompletion($pdo, $candidateId) {
    $score = 0;
    $total = 9;
    
    try {
        // Get user data
        $stmt = $pdo->prepare("SELECT * FROM users WHERE id = ?");
        $stmt->execute([$candidateId]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if (!$user) return 0;
        
        // Basic info (6 points)
        if (!empty($user['full_name'])) $score++;
        if (!empty($user['email'])) $score++;
        if (!empty($user['phone'])) $score++;
        if (!empty($user['location'])) $score++;
        if (!empty($user['bio'])) $score++;
        if (!empty($user['resume_path'])) $score++;
        
        // Education (1 point)
        $eduStmt = $pdo->prepare("SELECT COUNT(*) as cnt FROM user_education WHERE user_id = ?");
        $eduStmt->execute([$candidateId]);
        if ($eduStmt->fetch()['cnt'] > 0) $score++;
        
        // Experience (1 point)
        $expStmt = $pdo->prepare("SELECT COUNT(*) as cnt FROM user_experience WHERE user_id = ?");
        $expStmt->execute([$candidateId]);
        if ($expStmt->fetch()['cnt'] > 0) $score++;
        
        // Skills (1 point)
        $skillStmt = $pdo->prepare("SELECT COUNT(*) as cnt FROM user_skills WHERE user_id = ?");
        $skillStmt->execute([$candidateId]);
        if ($skillStmt->fetch()['cnt'] > 0) $score++;
        
        return round(($score / $total) * 100);
    } catch(PDOException $e) {
        return 0;
    }
}

// Check if job is saved by user
function isJobSaved($pdo, $candidateId, $jobId) {
    try {
        $stmt = $pdo->prepare("SELECT id FROM saved_jobs WHERE user_id = ? AND job_id = ?");
        $stmt->execute([$candidateId, $jobId]);
        return $stmt->fetch() !== false;
    } catch(PDOException $e) {
        return false;
    }
}

// Get saved jobs count
function getSavedJobsCount($pdo, $candidateId) {
    try {
        $stmt = $pdo->prepare("SELECT COUNT(*) as cnt FROM saved_jobs WHERE user_id = ?");
        $stmt->execute([$candidateId]);
        return $stmt->fetch()['cnt'];
    } catch(PDOException $e) {
        return 0;
    }
}
?>
