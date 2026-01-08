<?php
require_once '../includes/config.php';
require_once '../includes/functions.php';
require_once '../includes/master-data-functions.php';

if (!isAdmin()) redirect('login.php');

$pageTitle = 'Settings - Master Data Management';
$isAdmin = true;
$message = '';

// Handle success message from URL
if (isset($_GET['success'])) {
    switch ($_GET['success']) {
        case 'added':
            $message = '<div class="alert alert-success">✅ Item added successfully!</div>';
            break;
        case 'updated':
            $message = '<div class="alert alert-success">✅ Item updated successfully!</div>';
            break;
        case 'deleted':
            $message = '<div class="alert alert-success">🗑️ Item deleted successfully!</div>';
            break;
        case 'toggled':
            $message = '<div class="alert alert-success">🔄 Status updated successfully!</div>';
            break;
    }
}

if (isset($_GET['error'])) {
    $message = '<div class="alert alert-danger">⚠️ ' . htmlspecialchars($_GET['error']) . '</div>';
}

// Get all master data
$jobCategories = getAllJobCategories($pdo, false);
$workModes = getAllWorkModes($pdo, false);
$employmentTypes = getAllEmploymentTypes($pdo, false);
$experienceLevels = getAllExperienceLevels($pdo, false);
$states = getAllStates($pdo, false);
$qualifications = getAllQualifications($pdo, false);
$departments = getAllDepartments($pdo, false);

// Get active tab from URL
$activeTab = isset($_GET['tab']) ? $_GET['tab'] : 'categories';

include 'includes/header.php';
?>

<style>
/* Minimal Page-Specific Styles (Rest uses admin.css) */
.settings-tabs {
    display: flex;
    gap: 0.5rem;
    margin-bottom: 2rem;
    flex-wrap: wrap;
    background: white;
    padding: 1rem;
    border-radius: 12px;
    box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
}

.tab-btn {
    padding: 0.75rem 1.5rem;
    border: 2px solid var(--admin-gray-200);
    background: white;
    color: var(--admin-gray-700);
    border-radius: 8px;
    font-weight: 600;
    font-size: 0.9375rem;
    cursor: pointer;
    transition: all 0.2s;
    display: flex;
    align-items: center;
    gap: 0.5rem;
}

.tab-btn:hover {
    border-color: var(--admin-primary);
    color: var(--admin-primary);
    background: var(--admin-primary-light);
}

.tab-btn.active {
    background: linear-gradient(135deg, var(--admin-primary), var(--admin-primary-hover));
    color: white;
    border-color: var(--admin-primary);
}

.tab-content {
    display: none;
}

.tab-content.active {
    display: block;
    animation: fadeIn 0.3s ease;
}

.icon-preview {
    font-size: 1.5rem;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    width: 40px;
    height: 40px;
    background: var(--admin-gray-100);
    border-radius: 8px;
}

.usage-badge {
    display: inline-flex;
    align-items: center;
    gap: 0.25rem;
    padding: 0.25rem 0.625rem;
    background: var(--admin-gray-100);
    color: var(--admin-gray-700);
    border-radius: 6px;
    font-size: 0.8125rem;
    font-weight: 600;
}

.modal-overlay {
    display: none;
    position: fixed;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    background: rgba(0, 0, 0, 0.5);
    z-index: 9999;
    backdrop-filter: blur(5px);
    align-items: center;
    justify-content: center;
}

.modal-overlay.active {
    display: flex;
}

.modal-box {
    background: white;
    border-radius: 12px;
    max-width: 600px;
    width: 90%;
    max-height: 90vh;
    overflow-y: auto;
    box-shadow: 0 20px 50px rgba(0, 0, 0, 0.3);
    animation: modalSlideIn 0.3s ease;
}

@keyframes modalSlideIn {
    from { opacity: 0; transform: translateY(-50px); }
    to { opacity: 1; transform: translateY(0); }
}

.modal-header {
    background: linear-gradient(135deg, var(--admin-primary), var(--admin-primary-hover));
    color: white;
    padding: 1.5rem 2rem;
    display: flex;
    justify-content: space-between;
    align-items: center;
    border-radius: 12px 12px 0 0;
}

.modal-header h4 {
    margin: 0;
    color: white;
}

.modal-close {
    background: none;
    border: none;
    color: white;
    font-size: 1.5rem;
    cursor: pointer;
    padding: 0;
    width: 32px;
    height: 32px;
    display: flex;
    align-items: center;
    justify-content: center;
    border-radius: 6px;
    transition: background 0.2s;
}

.modal-close:hover {
    background: rgba(255, 255, 255, 0.2);
}

.modal-body {
    padding: 2rem;
}

.modal-footer {
    padding: 1rem 2rem;
    background: var(--admin-gray-50);
    border-top: 1px solid var(--admin-gray-200);
    display: flex;
    gap: 1rem;
    justify-content: flex-end;
}

.empty-state {
    text-align: center;
    padding: 3rem 2rem;
    color: var(--admin-gray-500);
}

.empty-state-icon {
    font-size: 3rem;
    margin-bottom: 1rem;
    opacity: 0.5;
}

.loading-spinner {
    display: inline-block;
    width: 20px;
    height: 20px;
    border: 3px solid rgba(255,255,255,.3);
    border-radius: 50%;
    border-top-color: white;
    animation: spin 1s ease-in-out infinite;
}

@keyframes spin {
    to { transform: rotate(360deg); }
}

@media (max-width: 768px) {
    .settings-tabs {
        overflow-x: auto;
        flex-wrap: nowrap;
    }
    
    .tab-btn {
        flex-shrink: 0;
    }
}
</style>

<div class="admin-container">
    <!-- Page Header -->
    <div class="page-header">
        <h2>⚙️ Settings - Master Data Management</h2>
        <p>Manage all dropdown values and master data for the job portal</p>
    </div>

    <?php echo $message; ?>

    <!-- Tabs Navigation -->
    <div class="settings-tabs">
        <button class="tab-btn <?php echo $activeTab === 'categories' ? 'active' : ''; ?>" onclick="switchTab('categories')">
            📂 Job Categories (<?php echo count($jobCategories); ?>)
        </button>
        <button class="tab-btn <?php echo $activeTab === 'work-modes' ? 'active' : ''; ?>" onclick="switchTab('work-modes')">
            🏠 Work Modes (<?php echo count($workModes); ?>)
        </button>
        <button class="tab-btn <?php echo $activeTab === 'employment' ? 'active' : ''; ?>" onclick="switchTab('employment')">
            💼 Employment Types (<?php echo count($employmentTypes); ?>)
        </button>
        <button class="tab-btn <?php echo $activeTab === 'experience' ? 'active' : ''; ?>" onclick="switchTab('experience')">
            📊 Experience Levels (<?php echo count($experienceLevels); ?>)
        </button>
        <button class="tab-btn <?php echo $activeTab === 'states' ? 'active' : ''; ?>" onclick="switchTab('states')">
            📍 States (<?php echo count($states); ?>)
        </button>
        <button class="tab-btn <?php echo $activeTab === 'qualifications' ? 'active' : ''; ?>" onclick="switchTab('qualifications')">
            🎓 Qualifications (<?php echo count($qualifications); ?>)
        </button>
        <button class="tab-btn <?php echo $activeTab === 'departments' ? 'active' : ''; ?>" onclick="switchTab('departments')">
            🏢 Departments (<?php echo count($departments); ?>)
        </button>
    </div>

    <!-- Tab 1: Job Categories -->
    <div id="tab-categories" class="tab-content <?php echo $activeTab === 'categories' ? 'active' : ''; ?>">
        <div class="card">
            <div class="card-header bg-primary text-white">
                <div style="display: flex; justify-content: space-between; align-items: center;">
                    <h4>📂 Job Categories</h4>
                    <button class="btn btn-sm btn-success" onclick="openAddModal('category')">
                        ➕ Add New
                    </button>
                </div>
            </div>
            <div class="card-body p-0">
                <?php if (count($jobCategories) > 0): ?>
                <div class="table-responsive">
                    <table class="table table-hover mb-0">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Icon</th>
                                <th>Category Name</th>
                                <th>Slug</th>
                                <th>Order</th>
                                <th>Status</th>
                                <th>Usage</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            $i=1;
                             foreach ($jobCategories as $cat): 
                                $usageCount = getJobCategoryUsageCount($pdo, $cat['id']); 
                            ?>
                            <tr>
                                <td><?php echo $i;$i++; ?></td>
                                <td><div class="icon-preview"><?php echo $cat['icon']; ?></div></td>
                                <td><strong><?php echo htmlspecialchars($cat['category_name']); ?></strong></td>
                                <td><code><?php echo $cat['category_slug']; ?></code></td>
                                <td><?php echo $cat['display_order']; ?></td>
                                <td>
                                    <?php if ($cat['is_active']): ?>
                                        <span class="badge bg-success">✓ Active</span>
                                    <?php else: ?>
                                        <span class="badge bg-secondary">○ Inactive</span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <span class="usage-badge">📊 <?php echo $usageCount; ?> jobs</span>
                                </td>
                                <td>
                                    <div class="action-buttons">
                                        <button class="btn btn-sm btn-warning" onclick="editItem('category', <?php echo $cat['id']; ?>)">✏️</button>
                                        <a href="settings-handler.php?action=toggle&type=category&id=<?php echo $cat['id']; ?>" class="btn btn-sm btn-info" title="Toggle Status">
                                            <?php echo $cat['is_active'] ? '⏸️' : '▶️'; ?>
                                        </a>
                                        <?php if ($usageCount == 0): ?>
                                        <a href="settings-handler.php?action=delete&type=category&id=<?php echo $cat['id']; ?>" 
                                           class="btn btn-sm btn-danger" 
                                           onclick="return confirm('Delete this category?')">🗑️</a>
                                        <?php endif; ?>
                                    </div>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
                <?php else: ?>
                <div class="empty-state">
                    <div class="empty-state-icon">📂</div>
                    <h4>No Categories Found</h4>
                    <p>Start by adding your first job category</p>
                </div>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <!-- Tab 2: Work Modes -->
    <div id="tab-work-modes" class="tab-content <?php echo $activeTab === 'work-modes' ? 'active' : ''; ?>">
        <div class="card">
            <div class="card-header bg-primary text-white">
                <div style="display: flex; justify-content: space-between; align-items: center;">
                    <h4>🏠 Work Modes</h4>
                    <button class="btn btn-sm btn-success" onclick="openAddModal('work_mode')">➕ Add New</button>
                </div>
            </div>
            <div class="card-body p-0">
                <?php if (count($workModes) > 0): ?>
                <div class="table-responsive">
                    <table class="table table-hover mb-0">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Icon</th>
                                <th>Work Mode</th>
                                <th>Order</th>
                                <th>Status</th>
                                <th>Usage</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php $i=1; foreach ($workModes as $mode): 
                                $usageCount = getWorkModeUsageCount($pdo, $mode['id']);
                            ?>
                            <tr>
                                <td><?php echo $i; $i++; ?></td>
                                <td><div class="icon-preview"><?php echo $mode['icon']; ?></div></td>
                                <td><strong><?php echo htmlspecialchars($mode['mode_name']); ?></strong></td>
                                <td><?php echo $mode['display_order']; ?></td>
                                <td>
                                    <?php if ($mode['is_active']): ?>
                                        <span class="badge bg-success">✓ Active</span>
                                    <?php else: ?>
                                        <span class="badge bg-secondary">○ Inactive</span>
                                    <?php endif; ?>
                                </td>
                                <td><span class="usage-badge">📊 <?php echo $usageCount; ?> jobs</span></td>
                                <td>
                                    <div class="action-buttons">
                                        <button class="btn btn-sm btn-warning" onclick="editItem('work_mode', <?php echo $mode['id']; ?>)">✏️</button>
                                        <a href="settings-handler.php?action=toggle&type=work_mode&id=<?php echo $mode['id']; ?>" class="btn btn-sm btn-info">
                                            <?php echo $mode['is_active'] ? '⏸️' : '▶️'; ?>
                                        </a>
                                        <?php if ($usageCount == 0): ?>
                                        <a href="settings-handler.php?action=delete&type=work_mode&id=<?php echo $mode['id']; ?>" 
                                           class="btn btn-sm btn-danger" 
                                           onclick="return confirm('Delete this work mode?')">🗑️</a>
                                        <?php endif; ?>
                                    </div>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
                <?php else: ?>
                <div class="empty-state">
                    <div class="empty-state-icon">🏠</div>
                    <h4>No Work Modes Found</h4>
                </div>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <!-- Tab 3: Employment Types -->
    <div id="tab-employment" class="tab-content <?php echo $activeTab === 'employment' ? 'active' : ''; ?>">
        <div class="card">
            <div class="card-header bg-primary text-white">
                <div style="display: flex; justify-content: space-between; align-items: center;">
                    <h4>💼 Employment Types</h4>
                    <button class="btn btn-sm btn-success" onclick="openAddModal('employment_type')">➕ Add New</button>
                </div>
            </div>
            <div class="card-body p-0">
                <?php if (count($employmentTypes) > 0): ?>
                <div class="table-responsive">
                    <table class="table table-hover mb-0">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Icon</th>
                                <th>Employment Type</th>
                                <th>Order</th>
                                <th>Status</th>
                                <th>Usage</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php $i=1; foreach ($employmentTypes as $type): 
                                $usageCount = getEmploymentTypeUsageCount($pdo, $type['id']);
                            ?>
                            <tr>
                                <td><?php echo $i; $i++; ?></td>
                                <td><div class="icon-preview"><?php echo $type['icon']; ?></div></td>
                                <td><strong><?php echo htmlspecialchars($type['type_name']); ?></strong></td>
                                <td><?php echo $type['display_order']; ?></td>
                                <td>
                                    <?php if ($type['is_active']): ?>
                                        <span class="badge bg-success">✓ Active</span>
                                    <?php else: ?>
                                        <span class="badge bg-secondary">○ Inactive</span>
                                    <?php endif; ?>
                                </td>
                                <td><span class="usage-badge">📊 <?php echo $usageCount; ?> jobs</span></td>
                                <td>
                                    <div class="action-buttons">
                                        <button class="btn btn-sm btn-warning" onclick="editItem('employment_type', <?php echo $type['id']; ?>)">✏️</button>
                                        <a href="settings-handler.php?action=toggle&type=employment_type&id=<?php echo $type['id']; ?>" class="btn btn-sm btn-info">
                                            <?php echo $type['is_active'] ? '⏸️' : '▶️'; ?>
                                        </a>
                                        <?php if ($usageCount == 0): ?>
                                        <a href="settings-handler.php?action=delete&type=employment_type&id=<?php echo $type['id']; ?>" 
                                           class="btn btn-sm btn-danger" 
                                           onclick="return confirm('Delete this employment type?')">🗑️</a>
                                        <?php endif; ?>
                                    </div>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
                <?php else: ?>
                <div class="empty-state">
                    <div class="empty-state-icon">💼</div>
                    <h4>No Employment Types Found</h4>
                </div>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <!-- Tab 4: Experience Levels -->
    <div id="tab-experience" class="tab-content <?php echo $activeTab === 'experience' ? 'active' : ''; ?>">
        <div class="card">
            <div class="card-header bg-primary text-white">
                <div style="display: flex; justify-content: space-between; align-items: center;">
                    <h4>📊 Experience Levels</h4>
                    <button class="btn btn-sm btn-success" onclick="openAddModal('experience_level')">➕ Add New</button>
                </div>
            </div>
            <div class="card-body p-0">
                <?php if (count($experienceLevels) > 0): ?>
                <div class="table-responsive">
                    <table class="table table-hover mb-0">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Icon</th>
                                <th>Experience Level</th>
                                <th>Order</th>
                                <th>Status</th>
                                <th>Usage</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php $i=1; foreach ($experienceLevels as $level): 
                                $usageCount = getExperienceLevelUsageCount($pdo, $level['id']);
                            ?>
                            <tr>
                                <td><?php echo $i; $i++ ?></td>
                                <td><div class="icon-preview"><?php echo $level['icon']; ?></div></td>
                                <td><strong><?php echo htmlspecialchars($level['level_name']); ?></strong></td>
                                <td><?php echo $level['display_order']; ?></td>
                                <td>
                                    <?php if ($level['is_active']): ?>
                                        <span class="badge bg-success">✓ Active</span>
                                    <?php else: ?>
                                        <span class="badge bg-secondary">○ Inactive</span>
                                    <?php endif; ?>
                                </td>
                                <td><span class="usage-badge">📊 <?php echo $usageCount; ?> jobs</span></td>
                                <td>
                                    <div class="action-buttons">
                                        <button class="btn btn-sm btn-warning" onclick="editItem('experience_level', <?php echo $level['id']; ?>)">✏️</button>
                                        <a href="settings-handler.php?action=toggle&type=experience_level&id=<?php echo $level['id']; ?>" class="btn btn-sm btn-info">
                                            <?php echo $level['is_active'] ? '⏸️' : '▶️'; ?>
                                        </a>
                                        <?php if ($usageCount == 0): ?>
                                        <a href="settings-handler.php?action=delete&type=experience_level&id=<?php echo $level['id']; ?>" 
                                           class="btn btn-sm btn-danger" 
                                           onclick="return confirm('Delete this experience level?')">🗑️</a>
                                        <?php endif; ?>
                                    </div>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
                <?php else: ?>
                <div class="empty-state">
                    <div class="empty-state-icon">📊</div>
                    <h4>No Experience Levels Found</h4>
                </div>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <!-- Tab 5: States -->
    <div id="tab-states" class="tab-content <?php echo $activeTab === 'states' ? 'active' : ''; ?>">
        <div class="card">
            <div class="card-header bg-primary text-white">
                <div style="display: flex; justify-content: space-between; align-items: center;">
                    <h4>📍 States & Locations</h4>
                    <button class="btn btn-sm btn-success" onclick="openAddModal('state')">➕ Add New</button>
                </div>
            </div>
            <div class="card-body p-0">
                <?php if (count($states) > 0): ?>
                <div class="table-responsive">
                    <table class="table table-hover mb-0">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>State Name</th>
                                <th>Code</th>
                                <th>Status</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php $i=1; foreach ($states as $state): ?>
                            <tr>
                                <td><?php echo $i; $i++ ?></td>
                                <td><strong><?php echo htmlspecialchars($state['state_name']); ?></strong></td>
                                <td><code><?php echo $state['state_code']; ?></code></td>
                                <td>
                                    <?php if ($state['is_active']): ?>
                                        <span class="badge bg-success">✓ Active</span>
                                    <?php else: ?>
                                        <span class="badge bg-secondary">○ Inactive</span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <div class="action-buttons">
                                        <button class="btn btn-sm btn-warning" onclick="editItem('state', <?php echo $state['id']; ?>)">✏️</button>
                                        <a href="settings-handler.php?action=toggle&type=state&id=<?php echo $state['id']; ?>" class="btn btn-sm btn-info">
                                            <?php echo $state['is_active'] ? '⏸️' : '▶️'; ?>
                                        </a>
                                    </div>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
                <?php else: ?>
                <div class="empty-state">
                    <div class="empty-state-icon">📍</div>
                    <h4>No States Found</h4>
                </div>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <!-- Tab 6: Qualifications -->
    <div id="tab-qualifications" class="tab-content <?php echo $activeTab === 'qualifications' ? 'active' : ''; ?>">
        <div class="card">
            <div class="card-header bg-primary text-white">
                <div style="display: flex; justify-content: space-between; align-items: center;">
                    <h4>🎓 Qualifications</h4>
                    <button class="btn btn-sm btn-success" onclick="openAddModal('qualification')">➕ Add New</button>
                </div>
            </div>
            <div class="card-body p-0">
                <?php if (count($qualifications) > 0): ?>
                <div class="table-responsive">
                    <table class="table table-hover mb-0">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Qualification Name</th>
                                <th>Level</th>
                                <th>Order</th>
                                <th>Status</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php $i=1; foreach ($qualifications as $qual): ?>
                            <tr>
                                <td><?php echo $i; $i++; ?></td>
                                <td><strong><?php echo htmlspecialchars($qual['qualification_name']); ?></strong></td>
                                <td><span class="badge bg-info"><?php echo $qual['qualification_level']; ?></span></td>
                                <td><?php echo $qual['display_order']; ?></td>
                                <td>
                                    <?php if ($qual['is_active']): ?>
                                        <span class="badge bg-success">✓ Active</span>
                                    <?php else: ?>
                                        <span class="badge bg-secondary">○ Inactive</span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <div class="action-buttons">
                                        <button class="btn btn-sm btn-warning" onclick="editItem('qualification', <?php echo $qual['id']; ?>)">✏️</button>
                                        <a href="settings-handler.php?action=toggle&type=qualification&id=<?php echo $qual['id']; ?>" class="btn btn-sm btn-info">
                                            <?php echo $qual['is_active'] ? '⏸️' : '▶️'; ?>
                                        </a>
                                    </div>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
                <?php else: ?>
                <div class="empty-state">
                    <div class="empty-state-icon">🎓</div>
                    <h4>No Qualifications Found</h4>
                </div>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <!-- Tab 7: Departments -->
    <div id="tab-departments" class="tab-content <?php echo $activeTab === 'departments' ? 'active' : ''; ?>">
        <div class="card">
            <div class="card-header bg-primary text-white">
                <div style="display: flex; justify-content: space-between; align-items: center;">
                    <h4>🏢 Departments</h4>
                    <button class="btn btn-sm btn-success" onclick="openAddModal('department')">➕ Add New</button>
                </div>
            </div>
            <div class="card-body p-0">
                <?php if (count($departments) > 0): ?>
                <div class="table-responsive">
                    <table class="table table-hover mb-0">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Department Name</th>
                                <th>Type</th>
                                <th>Status</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php $i=1; foreach ($departments as $dept): ?>
                            <tr>
                                <td><?php echo $i; $i++; ?></td>
                                <td><strong><?php echo htmlspecialchars($dept['department_name']); ?></strong></td>
                                <td><span class="badge bg-primary"><?php echo $dept['department_type']; ?></span></td>
                                <td>
                                    <?php if ($dept['is_active']): ?>
                                        <span class="badge bg-success">✓ Active</span>
                                    <?php else: ?>
                                        <span class="badge bg-secondary">○ Inactive</span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <div class="action-buttons">
                                        <button class="btn btn-sm btn-warning" onclick="editItem('department', <?php echo $dept['id']; ?>)">✏️</button>
                                        <a href="settings-handler.php?action=toggle&type=department&id=<?php echo $dept['id']; ?>" class="btn btn-sm btn-info">
                                            <?php echo $dept['is_active'] ? '⏸️' : '▶️'; ?>
                                        </a>
                                    </div>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
                <?php else: ?>
                <div class="empty-state">
                    <div class="empty-state-icon">🏢</div>
                    <h4>No Departments Found</h4>
                </div>
                <?php endif; ?>
            </div>
        </div>
    </div>

</div>

<!-- Add/Edit Modal -->
<div id="masterModal" class="modal-overlay">
    <div class="modal-box">
        <div class="modal-header">
            <h4 id="modalTitle">Add New Item</h4>
            <button class="modal-close" onclick="closeModal()">&times;</button>
        </div>
        <form id="masterForm" method="POST" action="settings-handler.php">
            <div class="modal-body">
                <input type="hidden" name="action" id="formAction">
                <input type="hidden" name="type" id="formType">
                <input type="hidden" name="id" id="formId">
                
                <div id="formFields">
                    <!-- Dynamic form fields -->
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" onclick="closeModal()">Cancel</button>
                <button type="submit" class="btn btn-primary" id="submitBtn">💾 Save Changes</button>
            </div>
        </form>
    </div>
</div>

<script>
// Tab switching
function switchTab(tabName) {
    document.querySelectorAll('.tab-content').forEach(tab => tab.classList.remove('active'));
    document.querySelectorAll('.tab-btn').forEach(btn => btn.classList.remove('active'));
    
    document.getElementById('tab-' + tabName).classList.add('active');
    event.target.classList.add('active');
    
    history.pushState(null, '', '?tab=' + tabName);
}

// Modal functions
function openAddModal(type) {
    let title = {
        'category': 'Job Category',
        'work_mode': 'Work Mode',
        'employment_type': 'Employment Type',
        'experience_level': 'Experience Level',
        'state': 'State',
        'qualification': 'Qualification',
        'department': 'Department'
    };
    
    document.getElementById('modalTitle').textContent = 'Add New ' + title[type];
    document.getElementById('formAction').value = 'add';
    document.getElementById('formType').value = type;
    document.getElementById('formId').value = '';
    
    buildFormFields(type);
    document.getElementById('masterModal').classList.add('active');
}

function editItem(type, id) {
    let title = {
        'category': 'Job Category',
        'work_mode': 'Work Mode',
        'employment_type': 'Employment Type',
        'experience_level': 'Experience Level',
        'state': 'State',
        'qualification': 'Qualification',
        'department': 'Department'
    };
    
    document.getElementById('modalTitle').innerHTML = 'Edit ' + title[type] + ' <span class="loading-spinner"></span>';
    document.getElementById('submitBtn').disabled = true;
    
    // Fetch data via AJAX
    fetch(`settings-handler.php?action=get&type=${type}&id=${id}`)
        .then(response => response.json())
        .then(result => {
            if (result.success) {
                document.getElementById('modalTitle').textContent = 'Edit ' + title[type];
                document.getElementById('formAction').value = 'edit';
                document.getElementById('formType').value = type;
                document.getElementById('formId').value = id;
                
                buildFormFields(type, result.data);
                document.getElementById('submitBtn').disabled = false;
                document.getElementById('masterModal').classList.add('active');
            } else {
                alert('Error: ' + result.error);
            }
        })
        .catch(error => {
            alert('Failed to load data: ' + error.message);
            console.error('Error:', error);
        });
}

function closeModal() {
    document.getElementById('masterModal').classList.remove('active');
    document.getElementById('masterForm').reset();
}

function buildFormFields(type, data = {}) {
    const fieldsContainer = document.getElementById('formFields');
    let html = '';
    
    switch(type) {
        case 'category':
            html = `
                <div class="mb-3">
                    <label class="form-label">Category Name *</label>
                    <input type="text" name="category_name" class="form-control" value="${data.category_name || ''}" required>
                </div>
                <div class="mb-3">
                    <label class="form-label">Icon (Emoji)</label>
                    <input type="text" name="icon" class="form-control" value="${data.icon || '📁'}" maxlength="10">
                </div>
                <div class="mb-3">
                    <label class="form-label">Display Order</label>
                    <input type="number" name="display_order" class="form-control" value="${data.display_order || 0}" min="0">
                </div>
                <div class="mb-3">
                    <label class="form-label">Description</label>
                    <textarea name="description" class="form-control" rows="3">${data.description || ''}</textarea>
                </div>
            `;
            break;
        case 'work_mode':
            html = `
                <div class="mb-3">
                    <label class="form-label">Work Mode Name *</label>
                    <input type="text" name="mode_name" class="form-control" value="${data.mode_name || ''}" required>
                </div>
                <div class="mb-3">
                    <label class="form-label">Icon (Emoji)</label>
                    <input type="text" name="icon" class="form-control" value="${data.icon || '💼'}" maxlength="10">
                </div>
                <div class="mb-3">
                    <label class="form-label">Display Order</label>
                    <input type="number" name="display_order" class="form-control" value="${data.display_order || 0}" min="0">
                </div>
            `;
            break;
        case 'employment_type':
            html = `
                <div class="mb-3">
                    <label class="form-label">Employment Type Name *</label>
                    <input type="text" name="type_name" class="form-control" value="${data.type_name || ''}" required>
                </div>
                <div class="mb-3">
                    <label class="form-label">Icon (Emoji)</label>
                    <input type="text" name="icon" class="form-control" value="${data.icon || '⏰'}" maxlength="10">
                </div>
                <div class="mb-3">
                    <label class="form-label">Display Order</label>
                    <input type="number" name="display_order" class="form-control" value="${data.display_order || 0}" min="0">
                </div>
            `;
            break;
        case 'experience_level':
            html = `
                <div class="mb-3">
                    <label class="form-label">Experience Level Name *</label>
                    <input type="text" name="level_name" class="form-control" value="${data.level_name || ''}" required placeholder="e.g., 0-2 years">
                </div>
                <div class="mb-3">
                    <label class="form-label">Icon (Emoji)</label>
                    <input type="text" name="icon" class="form-control" value="${data.icon || '📊'}" maxlength="10">
                </div>
                <div class="mb-3">
                    <label class="form-label">Display Order</label>
                    <input type="number" name="display_order" class="form-control" value="${data.display_order || 0}" min="0">
                </div>
            `;
            break;
        case 'state':
            html = `
                <div class="mb-3">
                    <label class="form-label">State Name *</label>
                    <input type="text" name="state_name" class="form-control" value="${data.state_name || ''}" required>
                </div>
                <div class="mb-3">
                    <label class="form-label">State Code</label>
                    <input type="text" name="state_code" class="form-control" value="${data.state_code || ''}" maxlength="10" placeholder="e.g., AP">
                </div>
            `;
            break;
        case 'qualification':
            const levels = ['10th', '12th', 'Diploma', 'Graduate', 'Post Graduate', 'Doctorate', 'Other'];
            html = `
                <div class="mb-3">
                    <label class="form-label">Qualification Name *</label>
                    <input type="text" name="qualification_name" class="form-control" value="${data.qualification_name || ''}" required>
                </div>
                <div class="mb-3">
                    <label class="form-label">Level</label>
                    <select name="qualification_level" class="form-select">
                        ${levels.map(level => `<option value="${level}" ${data.qualification_level === level ? 'selected' : ''}>${level}</option>`).join('')}
                    </select>
                </div>
                <div class="mb-3">
                    <label class="form-label">Display Order</label>
                    <input type="number" name="display_order" class="form-control" value="${data.display_order || 0}" min="0">
                </div>
            `;
            break;
        case 'department':
            const types = ['Central', 'State', 'PSU', 'Private', 'Other'];
            html = `
                <div class="mb-3">
                    <label class="form-label">Department Name *</label>
                    <input type="text" name="department_name" class="form-control" value="${data.department_name || ''}" required>
                </div>
                <div class="mb-3">
                    <label class="form-label">Department Type</label>
                    <select name="department_type" class="form-select">
                        ${types.map(type => `<option value="${type}" ${data.department_type === type ? 'selected' : ''}>${type}</option>`).join('')}
                    </select>
                </div>
            `;
            break;
    }
    
    fieldsContainer.innerHTML = html;
}

// Close modal on outside click
document.getElementById('masterModal').addEventListener('click', function(e) {
    if (e.target === this) {
        closeModal();
    }
});
</script>

<?php include '../includes/footer.php'; ?>
