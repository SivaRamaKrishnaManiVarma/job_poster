<?php
function sanitize($data) {
    return htmlspecialchars(strip_tags(trim($data)), ENT_QUOTES, 'UTF-8');
}

function isAdmin() {
    return isset($_SESSION['admin_id']) && isset($_SESSION['is_admin']);
}

function redirect($url) {
    header("Location: $url");
    exit();
}

/**
 * Get jobs with filters and master data JOINs
 * @param PDO $pdo - Database connection
 * @param array $filters - Array of filter parameters
 * @return array - Array of job records
 */
function getJobs($pdo, $filters = []) {
    $sql = "SELECT 
        j.id,
        j.title,
        j.company,
        j.description,
        j.job_link,
        j.official_website,
        j.location,
        j.posted_date,
        j.application_deadline,
        j.is_active,
        c.category_name,
        c.icon as category_icon,
        w.mode_name as work_mode,
        w.icon as work_mode_icon,
        e.type_name as employment_type,
        e.icon as employment_type_icon,
        ex.level_name as experience_level,
        ex.icon as experience_level_icon,
        s.state_name
    FROM jobs j
    LEFT JOIN master_job_categories c ON j.job_category_id = c.id
    LEFT JOIN master_work_modes w ON j.work_mode_id = w.id
    LEFT JOIN master_employment_types e ON j.employment_type_id = e.id
    LEFT JOIN master_experience_levels ex ON j.experience_level_id = ex.id
    LEFT JOIN master_states s ON j.state_id = s.id
    WHERE j.is_active = 1";
    
    $params = [];
    
    // Filter expired jobs by default (unless explicitly requested)
    $showExpired = isset($filters['show_expired']) ? $filters['show_expired'] : false;
    if (!$showExpired) {
        $sql .= " AND (j.application_deadline IS NULL OR j.application_deadline >= CURDATE())";
    }
    
    // Search filter
    if (!empty($filters['search'])) {
        $sql .= " AND (j.title LIKE ? OR j.company LIKE ? OR j.description LIKE ?)";
        $searchTerm = '%' . $filters['search'] . '%';
        $params = array_merge($params, [$searchTerm, $searchTerm, $searchTerm]);
    }
    
    // Category filter (by ID)
    if (!empty($filters['category'])) {
        $sql .= " AND j.job_category_id = ?";
        $params[] = $filters['category'];
    }
    
    // Location filter
    if (!empty($filters['location'])) {
        $sql .= " AND j.location LIKE ?";
        $params[] = '%' . $filters['location'] . '%';
    }
    
    // Work mode filter
    if (!empty($filters['work_mode'])) {
        $sql .= " AND j.work_mode_id = ?";
        $params[] = $filters['work_mode'];
    }
    
    // Employment type filter
    if (!empty($filters['employment_type'])) {
        $sql .= " AND j.employment_type_id = ?";
        $params[] = $filters['employment_type'];
    }
    
    // Experience level filter
    if (!empty($filters['experience_level'])) {
        $sql .= " AND j.experience_level_id = ?";
        $params[] = $filters['experience_level'];
    }
    
    $sql .= " ORDER BY j.posted_date DESC LIMIT 200";
    
    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    return $stmt->fetchAll();
}

/**
 * Get deadline status and urgency level
 * @param string $deadline - Deadline date (Y-m-d format)
 * @return array - ['status' => string, 'days_left' => int, 'class' => string]
 */
function getDeadlineStatus($deadline) {
    if (empty($deadline)) {
        return [
            'status' => 'no_deadline',
            'days_left' => null,
            'class' => 'deadline-none',
            'label' => 'No Deadline'
        ];
    }
    
    $deadlineDate = strtotime($deadline);
    $today = strtotime(date('Y-m-d'));
    $daysLeft = floor(($deadlineDate - $today) / (60 * 60 * 24));
    
    if ($daysLeft < 0) {
        return [
            'status' => 'expired',
            'days_left' => $daysLeft,
            'class' => 'deadline-expired',
            'label' => 'Expired'
        ];
    } elseif ($daysLeft == 0) {
        return [
            'status' => 'today',
            'days_left' => 0,
            'class' => 'deadline-urgent',
            'label' => 'Last Day!'
        ];
    } elseif ($daysLeft <= 3) {
        return [
            'status' => 'urgent',
            'days_left' => $daysLeft,
            'class' => 'deadline-urgent',
            'label' => "$daysLeft day" . ($daysLeft > 1 ? 's' : '') . ' left'
        ];
    } elseif ($daysLeft <= 7) {
        return [
            'status' => 'warning',
            'days_left' => $daysLeft,
            'class' => 'deadline-warning',
            'label' => "$daysLeft days left"
        ];
    } else {
        return [
            'status' => 'normal',
            'days_left' => $daysLeft,
            'class' => 'deadline-normal',
            'label' => date('d M Y', $deadlineDate)
        ];
    }
}

/**
 * Get count of jobs expiring soon (for admin dashboard)
 * @param PDO $pdo
 * @param int $days - Number of days threshold
 * @return int
 */
function getJobsExpiringSoon($pdo, $days = 7) {
    $stmt = $pdo->prepare("
        SELECT COUNT(*) FROM jobs 
        WHERE is_active = 1 
        AND application_deadline IS NOT NULL
        AND application_deadline >= CURDATE() 
        AND application_deadline <= DATE_ADD(CURDATE(), INTERVAL ? DAY)
    ");
    $stmt->execute([$days]);
    return $stmt->fetchColumn();
}

/**
 * Get count of expired jobs (for admin alerts)
 * @param PDO $pdo
 * @return int
 */
function getExpiredActiveJobs($pdo) {
    $stmt = $pdo->query("
        SELECT COUNT(*) FROM jobs 
        WHERE is_active = 1 
        AND application_deadline IS NOT NULL
        AND application_deadline < CURDATE()
    ");
    return $stmt->fetchColumn();
}

/**
 * Get archived (expired) jobs
 * @param PDO $pdo
 * @param array $filters
 * @return array
 */
function getArchivedJobs($pdo, $filters = []) {
    $filters['show_expired'] = true;
    return getJobs($pdo, $filters);
}

/**
 * Generate URL-friendly slug from text
 */
function generateSlug($text) {
    // Convert to lowercase
    $text = strtolower($text);
    
    // Remove special characters
    $text = preg_replace('/[^a-z0-9\s-]/', '', $text);
    
    // Replace spaces with hyphens
    $text = preg_replace('/[\s-]+/', '-', $text);
    
    // Remove leading/trailing hyphens
    $text = trim($text, '-');
    
    return $text;
}

/**
 * Generate unique slug for job
 */
function generateUniqueJobSlug($pdo, $title, $company, $existingSlug = null, $jobId = null) {
    // Create base slug from title and company
    $baseSlug = generateSlug($title . ' at ' . $company);
    
    // Limit length
    if (strlen($baseSlug) > 200) {
        $baseSlug = substr($baseSlug, 0, 200);
    }
    
    $slug = $baseSlug;
    $counter = 1;
    
    // Check if slug exists
    while (true) {
        $checkSql = "SELECT id FROM jobs WHERE slug = ?";
        $params = [$slug];
        
        // Exclude current job ID when editing
        if ($jobId) {
            $checkSql .= " AND id != ?";
            $params[] = $jobId;
        }
        
        $stmt = $pdo->prepare($checkSql);
        $stmt->execute($params);
        
        if (!$stmt->fetch()) {
            // Slug is unique
            break;
        }
        
        // Add counter to make unique
        $slug = $baseSlug . '-' . $counter;
        $counter++;
    }
    
    return $slug;
}

?>
