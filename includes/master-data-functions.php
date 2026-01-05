<?php
/**
 * Master Data Helper Functions
 * Functions to fetch and manage master data
 */

// Get all active job categories
function getAllJobCategories($pdo, $activeOnly = true) {
    $sql = "SELECT * FROM master_job_categories";
    if ($activeOnly) $sql .= " WHERE is_active = 1";
    $sql .= " ORDER BY display_order ASC";
    return $pdo->query($sql)->fetchAll(PDO::FETCH_ASSOC);
}

// Get all active work modes
function getAllWorkModes($pdo, $activeOnly = true) {
    $sql = "SELECT * FROM master_work_modes";
    if ($activeOnly) $sql .= " WHERE is_active = 1";
    $sql .= " ORDER BY display_order ASC";
    return $pdo->query($sql)->fetchAll(PDO::FETCH_ASSOC);
}

// Get all active employment types
function getAllEmploymentTypes($pdo, $activeOnly = true) {
    $sql = "SELECT * FROM master_employment_types";
    if ($activeOnly) $sql .= " WHERE is_active = 1";
    $sql .= " ORDER BY display_order ASC";
    return $pdo->query($sql)->fetchAll(PDO::FETCH_ASSOC);
}

// Get all active experience levels
function getAllExperienceLevels($pdo, $activeOnly = true) {
    $sql = "SELECT * FROM master_experience_levels";
    if ($activeOnly) $sql .= " WHERE is_active = 1";
    $sql .= " ORDER BY display_order ASC";
    return $pdo->query($sql)->fetchAll(PDO::FETCH_ASSOC);
}

// Get all active states
function getAllStates($pdo, $activeOnly = true) {
    $sql = "SELECT * FROM master_states";
    if ($activeOnly) $sql .= " WHERE is_active = 1";
    $sql .= " ORDER BY state_name ASC";
    return $pdo->query($sql)->fetchAll(PDO::FETCH_ASSOC);
}

// Get all active qualifications
function getAllQualifications($pdo, $activeOnly = true) {
    $sql = "SELECT * FROM master_qualifications";
    if ($activeOnly) $sql .= " WHERE is_active = 1";
    $sql .= " ORDER BY display_order ASC";
    return $pdo->query($sql)->fetchAll(PDO::FETCH_ASSOC);
}

// Get all active departments
function getAllDepartments($pdo, $activeOnly = true) {
    $sql = "SELECT * FROM master_departments";
    if ($activeOnly) $sql .= " WHERE is_active = 1";
    $sql .= " ORDER BY department_name ASC";
    return $pdo->query($sql)->fetchAll(PDO::FETCH_ASSOC);
}

// Get single job category by ID
function getJobCategoryById($pdo, $id) {
    $stmt = $pdo->prepare("SELECT * FROM master_job_categories WHERE id = ?");
    $stmt->execute([$id]);
    return $stmt->fetch(PDO::FETCH_ASSOC);
}

// Get single work mode by ID
function getWorkModeById($pdo, $id) {
    $stmt = $pdo->prepare("SELECT * FROM master_work_modes WHERE id = ?");
    $stmt->execute([$id]);
    return $stmt->fetch(PDO::FETCH_ASSOC);
}


// Check if job category name exists
function jobCategoryNameExists($pdo, $name, $excludeId = null) {
    $sql = "SELECT COUNT(*) FROM master_job_categories WHERE category_name = ?";
    $params = [$name];
    if ($excludeId) {
        $sql .= " AND id != ?";
        $params[] = $excludeId;
    }
    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    return $stmt->fetchColumn() > 0;
}

// Get usage count for job category
function getJobCategoryUsageCount($pdo, $categoryId) {
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM jobs WHERE job_category_id = ?");
    $stmt->execute([$categoryId]);
    return $stmt->fetchColumn();
}

// Get usage count for work mode
function getWorkModeUsageCount($pdo, $modeId) {
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM jobs WHERE work_mode_id = ?");
    $stmt->execute([$modeId]);
    return $stmt->fetchColumn();
}

// Get usage count for employment type
function getEmploymentTypeUsageCount($pdo, $typeId) {
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM jobs WHERE employment_type_id = ?");
    $stmt->execute([$typeId]);
    return $stmt->fetchColumn();
}

// Get usage count for experience level
function getExperienceLevelUsageCount($pdo, $levelId) {
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM jobs WHERE experience_level_id = ?");
    $stmt->execute([$levelId]);
    return $stmt->fetchColumn();
}
?>
