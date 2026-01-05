<?php
header('Content-Type: application/json');
require_once '../includes/config.php';
require_once '../includes/functions.php';

try {
    // Get filter parameters
    $search = isset($_GET['search']) ? trim($_GET['search']) : '';
    $category = isset($_GET['category']) ? intval($_GET['category']) : 0;
    $location = isset($_GET['location']) ? trim($_GET['location']) : '';
    $work_mode = isset($_GET['work_mode']) ? intval($_GET['work_mode']) : 0;
    $employment_type = isset($_GET['employment_type']) ? intval($_GET['employment_type']) : 0;
    $experience_level = isset($_GET['experience_level']) ? intval($_GET['experience_level']) : 0;
    $sort = isset($_GET['sort']) ? $_GET['sort'] : 'date_desc';
    $show_expired = isset($_GET['show_expired']) ? (bool)$_GET['show_expired'] : false;
    
    // Base query with JOINs
    $sql = "SELECT 
        j.id,
          j.slug,
        j.title,
        j.company,
        j.description,
        j.job_link,
        j.official_website,
        j.location,
        j.posted_date,
        j.application_deadline,
        j.is_active,
        j.created_at,
        c.category_name as category,
        c.icon as category_icon,
        w.mode_name as work_mode,
        w.icon as work_mode_icon,
        e.type_name as employment_type,
        e.icon as employment_type_icon,
        ex.level_name as experience_level,
        ex.icon as experience_level_icon,
        s.state_name,
        d.department_name,
        q.qualification_name
    FROM jobs j
    LEFT JOIN master_job_categories c ON j.job_category_id = c.id
    LEFT JOIN master_work_modes w ON j.work_mode_id = w.id
    LEFT JOIN master_employment_types e ON j.employment_type_id = e.id
    LEFT JOIN master_experience_levels ex ON j.experience_level_id = ex.id
    LEFT JOIN master_states s ON j.state_id = s.id
    LEFT JOIN master_departments d ON j.department_id = d.id
    LEFT JOIN master_qualifications q ON j.min_qualification_id = q.id
    WHERE j.is_active = 1";
    
    $params = [];
    
    // Filter by deadline status
    if (!$show_expired) {
        // Main page: Show jobs with no deadline OR deadline >= today
        $sql .= " AND (j.application_deadline IS NULL OR j.application_deadline >= CURDATE())";
    } else {
        // Archive page: Show jobs with deadline < today
        $sql .= " AND j.application_deadline IS NOT NULL AND j.application_deadline < CURDATE()";
    }
    
    // Search filter
    if (!empty($search)) {
        $sql .= " AND (j.title LIKE ? OR j.company LIKE ? OR j.description LIKE ? OR c.category_name LIKE ?)";
        $searchTerm = '%' . $search . '%';
        $params[] = $searchTerm;
        $params[] = $searchTerm;
        $params[] = $searchTerm;
        $params[] = $searchTerm;
    }
    
    // Category filter
    if ($category > 0) {
        $sql .= " AND j.job_category_id = ?";
        $params[] = $category;
    }
    
    // Location filter
    if (!empty($location)) {
        $sql .= " AND (j.location LIKE ? OR s.state_name LIKE ?)";
        $locationTerm = '%' . $location . '%';
        $params[] = $locationTerm;
        $params[] = $locationTerm;
    }
    
    // Work mode filter
    if ($work_mode > 0) {
        $sql .= " AND j.work_mode_id = ?";
        $params[] = $work_mode;
    }
    
    // Employment type filter
    if ($employment_type > 0) {
        $sql .= " AND j.employment_type_id = ?";
        $params[] = $employment_type;
    }
    
    // Experience level filter
    if ($experience_level > 0) {
        $sql .= " AND j.experience_level_id = ?";
        $params[] = $experience_level;
    }
    
    // Sorting
    switch ($sort) {
        case 'date_asc':
            $sql .= " ORDER BY j.posted_date ASC, j.id ASC";
            break;
        case 'deadline_asc':
            $sql .= " ORDER BY j.application_deadline ASC, j.posted_date DESC";
            break;
        case 'company_asc':
            $sql .= " ORDER BY j.company ASC, j.posted_date DESC";
            break;
        case 'date_desc':
        default:
            $sql .= " ORDER BY j.posted_date DESC, j.id DESC";
            break;
    }
    
    // Limit results
    $sql .= " LIMIT 200";
    
    // Execute query
    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    $jobs = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Get total active jobs count (for stats)
    $totalSql = "SELECT COUNT(*) FROM jobs WHERE is_active = 1";
    if (!$show_expired) {
        $totalSql .= " AND (application_deadline IS NULL OR application_deadline >= CURDATE())";
    } else {
        $totalSql .= " AND application_deadline IS NOT NULL AND application_deadline < CURDATE()";
    }
    $total = $pdo->query($totalSql)->fetchColumn();
    
    // Return JSON response
    echo json_encode([
        'success' => true,
        'jobs' => $jobs,
        'count' => count($jobs),
        'total' => $total,
        'filters_applied' => [
            'search' => $search,
            'category' => $category,
            'location' => $location,
            'work_mode' => $work_mode,
            'employment_type' => $employment_type,
            'experience_level' => $experience_level,
            'sort' => $sort,
            'show_expired' => $show_expired
        ]
    ], JSON_PRETTY_PRINT);
    
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'error' => 'Failed to fetch jobs: ' . $e->getMessage(),
        'jobs' => [],
        'count' => 0,
        'total' => 0
    ], JSON_PRETTY_PRINT);
}
?>
