<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');

require_once __DIR__ . '/../includes/config.php';

try {
    $filters = [
        'search' => $_GET['search'] ?? '',
        'category' => $_GET['category'] ?? '',
        'location' => $_GET['location'] ?? '',
        'work_mode' => $_GET['work_mode'] ?? '',
        'employment_type' => $_GET['employment_type'] ?? '',
        'experience_level' => $_GET['experience_level'] ?? '',
        'sort' => $_GET['sort'] ?? 'date_desc'
    ];

    // Build query
    $sql = "SELECT * FROM jobs WHERE is_active = 1";
    $params = [];

    if (!empty($filters['search'])) {
        $sql .= " AND (title LIKE ? OR company LIKE ? OR description LIKE ? OR category LIKE ?)";
        $searchTerm = '%' . $filters['search'] . '%';
        $params = array_merge($params, [$searchTerm, $searchTerm, $searchTerm, $searchTerm]);
    }

    if (!empty($filters['category'])) {
        $sql .= " AND category = ?";
        $params[] = $filters['category'];
    }

    if (!empty($filters['location'])) {
        $sql .= " AND location LIKE ?";
        $params[] = '%' . $filters['location'] . '%';
    }

    if (!empty($filters['work_mode'])) {
        $sql .= " AND work_mode = ?";
        $params[] = $filters['work_mode'];
    }

    if (!empty($filters['employment_type'])) {
        $sql .= " AND employment_type = ?";
        $params[] = $filters['employment_type'];
    }

    if (!empty($filters['experience_level'])) {
        $sql .= " AND experience_level = ?";
        $params[] = $filters['experience_level'];
    }

    // Sorting
    switch ($filters['sort']) {
        case 'date_asc':
            $sql .= " ORDER BY posted_date ASC";
            break;
        case 'deadline_asc':
            $sql .= " ORDER BY CASE WHEN application_deadline IS NULL THEN 1 ELSE 0 END, application_deadline ASC";
            break;
        case 'company_asc':
            $sql .= " ORDER BY company ASC";
            break;
        default:
            $sql .= " ORDER BY posted_date DESC";
    }

    $sql .= " LIMIT 100";

    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    $jobs = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Get total count
    $total = $pdo->query("SELECT COUNT(*) FROM jobs WHERE is_active = 1")->fetchColumn();

    echo json_encode([
        'success' => true,
        'jobs' => $jobs,
        'total' => (int)$total,
        'count' => count($jobs)
    ]);

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'error' => 'Failed to load jobs',
        'message' => $e->getMessage()
    ]);
}
