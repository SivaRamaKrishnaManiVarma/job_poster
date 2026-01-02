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

function getJobs($pdo, $filters = []) {
    $sql = "SELECT * FROM jobs WHERE is_active = 1";
    $params = [];
    
    if (!empty($filters['search'])) {
        $sql .= " AND (title LIKE ? OR company LIKE ? OR description LIKE ?)";
        $searchTerm = '%' . $filters['search'] . '%';
        $params = array_merge($params, [$searchTerm, $searchTerm, $searchTerm]);
    }
    
    if (!empty($filters['category'])) {
        $sql .= " AND category = ?";
        $params[] = $filters['category'];
    }
    
    if (!empty($filters['location'])) {
        $sql .= " AND location LIKE ?";
        $params[] = '%' . $filters['location'] . '%';
    }
    
    $sql .= " ORDER BY posted_date DESC LIMIT 100";
    
    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    return $stmt->fetchAll();
}
?>
