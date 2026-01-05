<?php
// Prevent any output before JSON
ob_start();

require_once '../includes/config.php';
require_once '../includes/functions.php';
require_once '../includes/master-data-functions.php';

if (!isAdmin()) {
    if (isset($_GET['action']) && $_GET['action'] === 'get') {
        ob_clean();
        header('Content-Type: application/json');
        echo json_encode(['success' => false, 'error' => 'Unauthorized access']);
        exit;
    }
    die('Unauthorized access');
}

$action = $_GET['action'] ?? $_POST['action'] ?? '';
$type = $_GET['type'] ?? $_POST['type'] ?? '';
$id = $_GET['id'] ?? $_POST['id'] ?? 0;

try {
    switch ($action) {
        case 'add':
            handleAdd($pdo, $type, $_POST);
            break;
        
        case 'edit':
            handleEdit($pdo, $type, $id, $_POST);
            break;
        
        case 'delete':
            handleDelete($pdo, $type, $id);
            break;
        
        case 'toggle':
            handleToggle($pdo, $type, $id);
            break;
        
        case 'get':
            handleGet($pdo, $type, $id);
            break;
        
        default:
            throw new Exception('Invalid action');
    }
} catch (Exception $e) {
    // If it's an AJAX GET request, return JSON error
    if ($action === 'get') {
        ob_clean();
        header('Content-Type: application/json');
        echo json_encode(['success' => false, 'error' => $e->getMessage()]);
        exit;
    }
    // Otherwise redirect to settings page
    redirect('settings.php?error=' . urlencode($e->getMessage()));
}

// ==================== ADD FUNCTIONS ====================

function handleAdd($pdo, $type, $data) {
    switch ($type) {
        case 'category':
            addJobCategory($pdo, $data);
            break;
        case 'work_mode':
            addWorkMode($pdo, $data);
            break;
        case 'employment_type':
            addEmploymentType($pdo, $data);
            break;
        case 'experience_level':
            addExperienceLevel($pdo, $data);
            break;
        case 'state':
            addState($pdo, $data);
            break;
        case 'qualification':
            addQualification($pdo, $data);
            break;
        case 'department':
            addDepartment($pdo, $data);
            break;
        default:
            throw new Exception('Invalid type');
    }
    redirect('settings.php?success=added&tab=' . getTabName($type));
}

function addJobCategory($pdo, $data) {
    $name = trim($data['category_name'] ?? '');
    $icon = trim($data['icon'] ?? '📁');
    $order = intval($data['display_order'] ?? 0);
    $description = trim($data['description'] ?? '');
    
    if (empty($name)) {
        throw new Exception('Category name is required');
    }
    
    // Generate slug
    $slug = generateSlug($name);
    
    // Check if slug exists
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM master_job_categories WHERE category_slug = ?");
    $stmt->execute([$slug]);
    if ($stmt->fetchColumn() > 0) {
        $slug = $slug . '-' . time();
    }
    
    $stmt = $pdo->prepare("
        INSERT INTO master_job_categories 
        (category_name, category_slug, icon, display_order, description, is_active, created_at) 
        VALUES (?, ?, ?, ?, ?, 1, NOW())
    ");
    $stmt->execute([$name, $slug, $icon, $order, $description]);
}

function addWorkMode($pdo, $data) {
    $name = trim($data['mode_name'] ?? '');
    $icon = trim($data['icon'] ?? '💼');
    $order = intval($data['display_order'] ?? 0);
    
    if (empty($name)) {
        throw new Exception('Work mode name is required');
    }
    
    $stmt = $pdo->prepare("
        INSERT INTO master_work_modes 
        (mode_name, icon, display_order, is_active, created_at) 
        VALUES (?, ?, ?, 1, NOW())
    ");
    $stmt->execute([$name, $icon, $order]);
}

function addEmploymentType($pdo, $data) {
    $name = trim($data['type_name'] ?? '');
    $icon = trim($data['icon'] ?? '⏰');
    $order = intval($data['display_order'] ?? 0);
    
    if (empty($name)) {
        throw new Exception('Employment type name is required');
    }
    
    $stmt = $pdo->prepare("
        INSERT INTO master_employment_types 
        (type_name, icon, display_order, is_active, created_at) 
        VALUES (?, ?, ?, 1, NOW())
    ");
    $stmt->execute([$name, $icon, $order]);
}

function addExperienceLevel($pdo, $data) {
    $name = trim($data['level_name'] ?? '');
    $icon = trim($data['icon'] ?? '📊');
    $order = intval($data['display_order'] ?? 0);
    
    if (empty($name)) {
        throw new Exception('Experience level name is required');
    }
    
    $stmt = $pdo->prepare("
        INSERT INTO master_experience_levels 
        (level_name, icon, display_order, is_active, created_at) 
        VALUES (?, ?, ?, 1, NOW())
    ");
    $stmt->execute([$name, $icon, $order]);
}

function addState($pdo, $data) {
    $name = trim($data['state_name'] ?? '');
    $code = trim($data['state_code'] ?? '');
    
    if (empty($name)) {
        throw new Exception('State name is required');
    }
    
    $stmt = $pdo->prepare("
        INSERT INTO master_states 
        (state_name, state_code, is_active, created_at) 
        VALUES (?, ?, 1, NOW())
    ");
    $stmt->execute([$name, $code]);
}

function addQualification($pdo, $data) {
    $name = trim($data['qualification_name'] ?? '');
    $level = trim($data['qualification_level'] ?? 'Graduate');
    $order = intval($data['display_order'] ?? 0);
    
    if (empty($name)) {
        throw new Exception('Qualification name is required');
    }
    
    $stmt = $pdo->prepare("
        INSERT INTO master_qualifications 
        (qualification_name, qualification_level, display_order, is_active, created_at) 
        VALUES (?, ?, ?, 1, NOW())
    ");
    $stmt->execute([$name, $level, $order]);
}

function addDepartment($pdo, $data) {
    $name = trim($data['department_name'] ?? '');
    $type = trim($data['department_type'] ?? 'Central');
    
    if (empty($name)) {
        throw new Exception('Department name is required');
    }
    
    $stmt = $pdo->prepare("
        INSERT INTO master_departments 
        (department_name, department_type, is_active, created_at) 
        VALUES (?, ?, 1, NOW())
    ");
    $stmt->execute([$name, $type]);
}

// ==================== EDIT FUNCTIONS ====================

function handleEdit($pdo, $type, $id, $data) {
    switch ($type) {
        case 'category':
            editJobCategory($pdo, $id, $data);
            break;
        case 'work_mode':
            editWorkMode($pdo, $id, $data);
            break;
        case 'employment_type':
            editEmploymentType($pdo, $id, $data);
            break;
        case 'experience_level':
            editExperienceLevel($pdo, $id, $data);
            break;
        case 'state':
            editState($pdo, $id, $data);
            break;
        case 'qualification':
            editQualification($pdo, $id, $data);
            break;
        case 'department':
            editDepartment($pdo, $id, $data);
            break;
        default:
            throw new Exception('Invalid type');
    }
    redirect('settings.php?success=updated&tab=' . getTabName($type));
}

function editJobCategory($pdo, $id, $data) {
    $name = trim($data['category_name'] ?? '');
    $icon = trim($data['icon'] ?? '📁');
    $order = intval($data['display_order'] ?? 0);
    $description = trim($data['description'] ?? '');
    
    if (empty($name)) {
        throw new Exception('Category name is required');
    }
    
    // Generate slug
    $slug = generateSlug($name);
    
    // Check if slug exists (excluding current record)
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM master_job_categories WHERE category_slug = ? AND id != ?");
    $stmt->execute([$slug, $id]);
    if ($stmt->fetchColumn() > 0) {
        $slug = $slug . '-' . $id;
    }
    
    $stmt = $pdo->prepare("
        UPDATE master_job_categories 
        SET category_name = ?, category_slug = ?, icon = ?, display_order = ?, description = ?, updated_at = NOW()
        WHERE id = ?
    ");
    $stmt->execute([$name, $slug, $icon, $order, $description, $id]);
}

function editWorkMode($pdo, $id, $data) {
    $name = trim($data['mode_name'] ?? '');
    $icon = trim($data['icon'] ?? '💼');
    $order = intval($data['display_order'] ?? 0);
    
    if (empty($name)) {
        throw new Exception('Work mode name is required');
    }
    
    $stmt = $pdo->prepare("
        UPDATE master_work_modes 
        SET mode_name = ?, icon = ?, display_order = ?, updated_at = NOW()
        WHERE id = ?
    ");
    $stmt->execute([$name, $icon, $order, $id]);
}

function editEmploymentType($pdo, $id, $data) {
    $name = trim($data['type_name'] ?? '');
    $icon = trim($data['icon'] ?? '⏰');
    $order = intval($data['display_order'] ?? 0);
    
    if (empty($name)) {
        throw new Exception('Employment type name is required');
    }
    
    $stmt = $pdo->prepare("
        UPDATE master_employment_types 
        SET type_name = ?, icon = ?, display_order = ?, updated_at = NOW()
        WHERE id = ?
    ");
    $stmt->execute([$name, $icon, $order, $id]);
}

function editExperienceLevel($pdo, $id, $data) {
    $name = trim($data['level_name'] ?? '');
    $icon = trim($data['icon'] ?? '📊');
    $order = intval($data['display_order'] ?? 0);
    
    if (empty($name)) {
        throw new Exception('Experience level name is required');
    }
    
    $stmt = $pdo->prepare("
        UPDATE master_experience_levels 
        SET level_name = ?, icon = ?, display_order = ?, updated_at = NOW()
        WHERE id = ?
    ");
    $stmt->execute([$name, $icon, $order, $id]);
}

function editState($pdo, $id, $data) {
    $name = trim($data['state_name'] ?? '');
    $code = trim($data['state_code'] ?? '');
    
    if (empty($name)) {
        throw new Exception('State name is required');
    }
    
    $stmt = $pdo->prepare("
        UPDATE master_states 
        SET state_name = ?, state_code = ?, updated_at = NOW()
        WHERE id = ?
    ");
    $stmt->execute([$name, $code, $id]);
}

function editQualification($pdo, $id, $data) {
    $name = trim($data['qualification_name'] ?? '');
    $level = trim($data['qualification_level'] ?? 'Graduate');
    $order = intval($data['display_order'] ?? 0);
    
    if (empty($name)) {
        throw new Exception('Qualification name is required');
    }
    
    $stmt = $pdo->prepare("
        UPDATE master_qualifications 
        SET qualification_name = ?, qualification_level = ?, display_order = ?, updated_at = NOW()
        WHERE id = ?
    ");
    $stmt->execute([$name, $level, $order, $id]);
}

function editDepartment($pdo, $id, $data) {
    $name = trim($data['department_name'] ?? '');
    $type = trim($data['department_type'] ?? 'Central');
    
    if (empty($name)) {
        throw new Exception('Department name is required');
    }
    
    $stmt = $pdo->prepare("
        UPDATE master_departments 
        SET department_name = ?, department_type = ?, updated_at = NOW()
        WHERE id = ?
    ");
    $stmt->execute([$name, $type, $id]);
}

// ==================== DELETE FUNCTION ====================

function handleDelete($pdo, $type, $id) {
    // Check if item is being used
    $usageCount = 0;
    
    switch ($type) {
        case 'category':
            $usageCount = getJobCategoryUsageCount($pdo, $id);
            $table = 'master_job_categories';
            break;
        case 'work_mode':
            $usageCount = getWorkModeUsageCount($pdo, $id);
            $table = 'master_work_modes';
            break;
        case 'employment_type':
            $usageCount = getEmploymentTypeUsageCount($pdo, $id);
            $table = 'master_employment_types';
            break;
        case 'experience_level':
            $usageCount = getExperienceLevelUsageCount($pdo, $id);
            $table = 'master_experience_levels';
            break;
        case 'state':
            $table = 'master_states';
            break;
        case 'qualification':
            $table = 'master_qualifications';
            break;
        case 'department':
            $table = 'master_departments';
            break;
        default:
            throw new Exception('Invalid type');
    }
    
    if ($usageCount > 0) {
        throw new Exception("Cannot delete! This item is being used in {$usageCount} job(s). Please deactivate instead.");
    }
    
    $stmt = $pdo->prepare("DELETE FROM {$table} WHERE id = ?");
    $stmt->execute([$id]);
    
    redirect('settings.php?success=deleted&tab=' . getTabName($type));
}

// ==================== TOGGLE STATUS FUNCTION ====================

function handleToggle($pdo, $type, $id) {
    switch ($type) {
        case 'category':
            $table = 'master_job_categories';
            break;
        case 'work_mode':
            $table = 'master_work_modes';
            break;
        case 'employment_type':
            $table = 'master_employment_types';
            break;
        case 'experience_level':
            $table = 'master_experience_levels';
            break;
        case 'state':
            $table = 'master_states';
            break;
        case 'qualification':
            $table = 'master_qualifications';
            break;
        case 'department':
            $table = 'master_departments';
            break;
        default:
            throw new Exception('Invalid type');
    }
    
    $stmt = $pdo->prepare("UPDATE {$table} SET is_active = NOT is_active, updated_at = NOW() WHERE id = ?");
    $stmt->execute([$id]);
    
    redirect('settings.php?success=toggled&tab=' . getTabName($type));
}

// ==================== GET SINGLE ITEM (FOR AJAX EDIT) ====================

function handleGet($pdo, $type, $id) {
    // Clear any output buffer
    ob_clean();
    
    $table = '';
    
    switch ($type) {
        case 'category':
            $table = 'master_job_categories';
            break;
        case 'work_mode':
            $table = 'master_work_modes';
            break;
        case 'employment_type':
            $table = 'master_employment_types';
            break;
        case 'experience_level':
            $table = 'master_experience_levels';
            break;
        case 'state':
            $table = 'master_states';
            break;
        case 'qualification':
            $table = 'master_qualifications';
            break;
        case 'department':
            $table = 'master_departments';
            break;
        default:
            header('Content-Type: application/json');
            echo json_encode(['success' => false, 'error' => 'Invalid type']);
            exit;
    }
    
    $stmt = $pdo->prepare("SELECT * FROM {$table} WHERE id = ?");
    $stmt->execute([$id]);
    $data = $stmt->fetch();
    
    if (!$data) {
        header('Content-Type: application/json');
        echo json_encode(['success' => false, 'error' => 'Item not found']);
        exit;
    }
    
    header('Content-Type: application/json');
    echo json_encode(['success' => true, 'data' => $data]);
    exit;
}

// ==================== HELPER FUNCTIONS ====================

function generateSlug($text) {
    $text = strtolower($text);
    $text = preg_replace('/[^a-z0-9\s-]/', '', $text);
    $text = preg_replace('/[\s-]+/', '-', $text);
    $text = trim($text, '-');
    return $text;
}

function getTabName($type) {
    $tabs = [
        'category' => 'categories',
        'work_mode' => 'work-modes',
        'employment_type' => 'employment',
        'experience_level' => 'experience',
        'state' => 'states',
        'qualification' => 'qualifications',
        'department' => 'departments'
    ];
    return $tabs[$type] ?? 'categories';
}
?>
