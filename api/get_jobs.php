<?php
header('Content-Type: application/json');
require_once '../includes/config.php';
require_once '../includes/functions.php';

try {
    // Get filter parameters
    $page = isset($_GET['page']) ? max(1, intval($_GET['page'])) : 1;
    $limit = isset($_GET['limit']) ? max(1, intval($_GET['limit'])) : 25;
    $offset = ($page - 1) * $limit;
    
    $search = isset($_GET['search']) ? trim($_GET['search']) : '';
    $category = isset($_GET['category']) ? intval($_GET['category']) : 0;
    $location = isset($_GET['location']) ? trim($_GET['location']) : '';
    $work_mode = isset($_GET['work_mode']) ? intval($_GET['work_mode']) : 0;
    $employment_type = isset($_GET['employment_type']) ? intval($_GET['employment_type']) : 0;
    $experience_level = isset($_GET['experience_level']) ? intval($_GET['experience_level']) : 0;
    $sort = isset($_GET['sort']) ? $_GET['sort'] : 'date_desc';
    $show_expired = isset($_GET['show_expired']) ? (isset($_GET['show_expired']) && ($_GET['show_expired'] === 'true' || $_GET['show_expired'] == 1)) : false;
    
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
        // Expired jobs: Show jobs with deadline < today, limited to top 100 recently ended
        // We use a subquery to ensure we only look at the 100 most recent expirations
        $sql .= " AND j.application_deadline IS NOT NULL AND j.application_deadline < CURDATE()";
        $sql .= " AND j.id IN (SELECT id FROM (SELECT id FROM jobs WHERE is_active = 1 AND application_deadline < CURDATE() ORDER BY application_deadline DESC LIMIT 100) as expired_pool)";
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
    
    // Final pagination limit and offset
    $sql .= " LIMIT ? OFFSET ?";
    $params[] = $limit;
    $params[] = $offset;
    
    // Execute query
    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    $jobs = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Get total count for this filtered view (respecting the 100 limit if expired)
    $totalSql = "SELECT COUNT(*) FROM jobs j WHERE j.is_active = 1";
    $totalParams = [];
    if (!$show_expired) {
        $totalSql .= " AND (j.application_deadline IS NULL OR j.application_deadline >= CURDATE())";
    } else {
        $totalSql .= " AND j.application_deadline IS NOT NULL AND j.application_deadline < CURDATE()";
        $totalSql .= " AND j.id IN (SELECT id FROM (SELECT id FROM jobs WHERE is_active = 1 AND application_deadline < CURDATE() ORDER BY application_deadline DESC LIMIT 100) as expired_pool)";
    }
    
    // Apply the same filters to total count query
    if (!empty($search)) {
        $totalSql .= " AND (j.title LIKE ? OR j.company LIKE ? OR j.description LIKE ?)";
        $totalParams[] = '%' . $search . '%';
        $totalParams[] = '%' . $search . '%';
        $totalParams[] = '%' . $search . '%';
    }
    if ($category > 0) {
        $totalSql .= " AND j.job_category_id = ?";
        $totalParams[] = $category;
    }
    if (!empty($location)) {
        $totalSql .= " AND (j.location LIKE ?)";
        $totalParams[] = '%' . $location . '%';
    }
    if ($work_mode > 0) {
        $totalSql .= " AND j.work_mode_id = ?";
        $totalParams[] = $work_mode;
    }
    if ($employment_type > 0) {
        $totalSql .= " AND j.employment_type_id = ?";
        $totalParams[] = $employment_type;
    }
    if ($experience_level > 0) {
        $totalSql .= " AND j.experience_level_id = ?";
        $totalParams[] = $experience_level;
    }
    
    $totalStmt = $pdo->prepare($totalSql);
    $totalStmt->execute($totalParams);
    $total = $totalStmt->fetchColumn();
    
    // Return JSON response
    echo json_encode([
        'success' => true,
        'jobs' => $jobs,
        'count' => count($jobs),
        'total' => (int)$total,
        'page' => $page,
        'limit' => $limit,
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
