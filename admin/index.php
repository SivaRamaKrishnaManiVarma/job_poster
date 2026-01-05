<?php
require_once '../includes/config.php';
require_once '../includes/functions.php';
require_once '../includes/master-data-functions.php';

if (!isAdmin()) redirect('login.php');

$pageTitle = 'Admin Dashboard';
$isAdmin = true;

// ==================== STATISTICS QUERIES ====================

// Job Statistics
$totalJobs = $pdo->query("SELECT COUNT(*) FROM jobs")->fetchColumn();
$activeJobs = $pdo->query("SELECT COUNT(*) FROM jobs WHERE is_active = 1")->fetchColumn();
$inactiveJobs = $totalJobs - $activeJobs;

// Jobs with upcoming deadlines (within next 7 days)
$upcomingDeadlines = $pdo->query("
    SELECT COUNT(*) FROM jobs 
    WHERE application_deadline >= CURDATE() 
    AND application_deadline <= DATE_ADD(CURDATE(), INTERVAL 7 DAY)
    AND is_active = 1
")->fetchColumn();

// Expired jobs
$expiredJobs = $pdo->query("
    SELECT COUNT(*) FROM jobs 
    WHERE application_deadline < CURDATE() 
    AND is_active = 1
")->fetchColumn();

// Master Data Counts
$totalCategories = $pdo->query("SELECT COUNT(*) FROM master_job_categories WHERE is_active = 1")->fetchColumn();
$totalWorkModes = $pdo->query("SELECT COUNT(*) FROM master_work_modes WHERE is_active = 1")->fetchColumn();
$totalEmploymentTypes = $pdo->query("SELECT COUNT(*) FROM master_employment_types WHERE is_active = 1")->fetchColumn();
$totalDepartments = $pdo->query("SELECT COUNT(*) FROM master_departments WHERE is_active = 1")->fetchColumn();

// Recent Jobs (with JOINs)
$recentJobs = $pdo->query("
    SELECT j.*, 
        c.category_name, c.icon as category_icon,
        w.mode_name, w.icon as work_mode_icon,
        e.type_name
    FROM jobs j
    LEFT JOIN master_job_categories c ON j.job_category_id = c.id
    LEFT JOIN master_work_modes w ON j.work_mode_id = w.id
    LEFT JOIN master_employment_types e ON j.employment_type_id = e.id
    ORDER BY j.created_at DESC 
    LIMIT 10
")->fetchAll();

// Category-wise Job Distribution
$categoryStats = $pdo->query("
    SELECT c.category_name, c.icon, COUNT(j.id) as job_count
    FROM master_job_categories c
    LEFT JOIN jobs j ON c.id = j.job_category_id AND j.is_active = 1
    WHERE c.is_active = 1
    GROUP BY c.id, c.category_name, c.icon
    ORDER BY job_count DESC
    LIMIT 8
")->fetchAll();

// Work Mode Distribution
$workModeStats = $pdo->query("
    SELECT w.mode_name, w.icon, COUNT(j.id) as job_count
    FROM master_work_modes w
    LEFT JOIN jobs j ON w.id = j.work_mode_id AND j.is_active = 1
    WHERE w.is_active = 1
    GROUP BY w.id, w.mode_name, w.icon
    ORDER BY job_count DESC
")->fetchAll();

// Jobs posted this month
$jobsThisMonth = $pdo->query("
    SELECT COUNT(*) FROM jobs 
    WHERE MONTH(created_at) = MONTH(CURDATE()) 
    AND YEAR(created_at) = YEAR(CURDATE())
")->fetchColumn();

// Jobs posted today
$jobsToday = $pdo->query("
    SELECT COUNT(*) FROM jobs 
    WHERE DATE(created_at) = CURDATE()
")->fetchColumn();

include 'includes/header.php';
?>

<style>
    /* Dashboard-specific styles */
    .welcome-banner {
        background: linear-gradient(135deg, var(--admin-primary), var(--admin-primary-hover));
        color: white;
        padding: 2rem;
        border-radius: 12px;
        margin-bottom: 2rem;
        box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
    }

    .welcome-banner h2 {
        color: white;
        margin: 0;
        font-size: 1.75rem;
    }

    .welcome-banner p {
        margin: 0.5rem 0 0 0;
        opacity: 0.9;
    }

    .stat-card {
        background: white;
        border-radius: 12px;
        padding: 1.5rem;
        box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
        transition: transform 0.2s, box-shadow 0.2s;
        border-left: 4px solid;
        height: 100%;
    }

    .stat-card:hover {
        transform: translateY(-4px);
        box-shadow: 0 6px 20px rgba(0, 0, 0, 0.15);
    }

    .stat-card.primary { border-left-color: var(--admin-primary); }
    .stat-card.success { border-left-color: var(--admin-success); }
    .stat-card.warning { border-left-color: var(--admin-warning); }
    .stat-card.danger { border-left-color: var(--admin-danger); }
    .stat-card.info { border-left-color: var(--admin-info); }

    .stat-card .stat-icon {
        font-size: 2.5rem;
        opacity: 0.2;
        float: right;
    }

    .stat-card .stat-value {
        font-size: 2.5rem;
        font-weight: 700;
        margin: 0;
        color: var(--admin-gray-900);
    }

    .stat-card .stat-label {
        color: var(--admin-gray-600);
        font-size: 0.875rem;
        font-weight: 600;
        text-transform: uppercase;
        letter-spacing: 0.5px;
        margin-top: 0.5rem;
    }

    .stat-card .stat-change {
        font-size: 0.75rem;
        margin-top: 0.5rem;
    }

    .quick-actions {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
        gap: 1rem;
        margin-bottom: 2rem;
    }

    .quick-action-btn {
        display: flex;
        align-items: center;
        gap: 1rem;
        padding: 1.25rem;
        background: white;
        border: 2px solid var(--admin-gray-200);
        border-radius: 12px;
        text-decoration: none;
        color: var(--admin-gray-700);
        font-weight: 600;
        transition: all 0.2s;
    }

    .quick-action-btn:hover {
        border-color: var(--admin-primary);
        color: var(--admin-primary);
        background: var(--admin-primary-light);
        transform: translateY(-2px);
        box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
    }

    .quick-action-btn .icon {
        font-size: 2rem;
    }

    .distribution-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(150px, 1fr));
        gap: 1rem;
        margin-top: 1rem;
    }

    .distribution-item {
        background: var(--admin-gray-50);
        padding: 1rem;
        border-radius: 8px;
        text-align: center;
        border: 1px solid var(--admin-gray-200);
    }

    .distribution-item .icon {
        font-size: 2rem;
        margin-bottom: 0.5rem;
    }

    .distribution-item .count {
        font-size: 1.5rem;
        font-weight: 700;
        color: var(--admin-primary);
    }

    .distribution-item .label {
        font-size: 0.875rem;
        color: var(--admin-gray-600);
        margin-top: 0.25rem;
    }

    @media (max-width: 768px) {
        .distribution-grid {
            grid-template-columns: repeat(2, 1fr);
        }
    }
</style>

<div class="admin-container">
    <!-- Welcome Banner -->
    <div class="welcome-banner">
        <h2>👋 Welcome back, <?php echo sanitize($_SESSION['admin_username']); ?>!</h2>
        <p>Here's what's happening with your job portal today</p>
    </div>

    <!-- Quick Stats Row -->
    <div class="row mb-4">
        <div class="col-md-3 mb-3">
            <div class="stat-card primary">
                <div class="stat-icon">📋</div>
                <div class="stat-value"><?php echo $totalJobs; ?></div>
                <div class="stat-label">Total Jobs</div>
                <div class="stat-change">
                    <span class="text-success">+<?php echo $jobsThisMonth; ?> this month</span>
                </div>
            </div>
        </div>
        <div class="col-md-3 mb-3">
            <div class="stat-card success">
                <div class="stat-icon">✅</div>
                <div class="stat-value"><?php echo $activeJobs; ?></div>
                <div class="stat-label">Active Jobs</div>
                <div class="stat-change">
                    <span class="text-info">📊 <?php echo $totalJobs > 0 ? round(($activeJobs / $totalJobs) * 100) : 0; ?>% of total</span>
                </div>
            </div>
        </div>
        <div class="col-md-3 mb-3">
            <div class="stat-card warning">
                <div class="stat-icon">⏰</div>
                <div class="stat-value"><?php echo $upcomingDeadlines; ?></div>
                <div class="stat-label">Expiring Soon</div>
                <div class="stat-change">
                    <span class="text-warning">Next 7 days</span>
                </div>
            </div>
        </div>
        <div class="col-md-3 mb-3">
            <div class="stat-card danger">
                <div class="stat-icon">⚠️</div>
                <div class="stat-value"><?php echo $expiredJobs; ?></div>
                <div class="stat-label">Expired Jobs</div>
                <div class="stat-change">
                    <?php if ($expiredJobs > 0): ?>
                        <a href="jobs.php?show_expired=1" class="text-danger">Review now →</a>
                    <?php else: ?>
                        <span class="text-success">All clear!</span>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>


    <!-- Quick Actions -->
    <div class="page-header">
        <h4>⚡ Quick Actions</h4>
    </div>
   <!-- Quick Actions -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card">
                <div class="card-body">
                    <h5 class="card-title mb-3">⚡ Quick Actions</h5>
                    <div class="row g-3">
                        <div class="col-md-3">
                            <a href="jobs.php?action=add" class="btn btn-primary w-100 btn-lg">
                                ➕ Post New Job
                            </a>
                        </div>
                        <div class="col-md-3">
                            <a href="jobs.php" class="btn btn-outline-primary w-100 btn-lg">
                                📋 Manage Jobs
                            </a>
                        </div>
                        <div class="col-md-3">
                            <a href="settings.php" class="btn btn-outline-secondary w-100 btn-lg">
                                ⚙️ Settings
                            </a>
                        </div>
                        <div class="col-md-3">
                            <a href="../index.php" target="_blank" class="btn btn-outline-success w-100 btn-lg">
                                🌐 View Portal
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Expired Jobs Alert -->
    <?php if ($expiredJobs > 0): ?>
    <div class="alert alert-warning alert-dismissible fade show" role="alert">
        <div class="d-flex align-items-center">
            <div style="font-size: 2rem; margin-right: 1rem;">⚠️</div>
            <div class="flex-grow-1">
                <h5 class="alert-heading mb-1">Action Required: Expired Jobs Detected</h5>
                <p class="mb-2">You have <strong><?php echo $expiredJobs; ?> active job<?php echo $expiredJobs > 1 ? 's' : ''; ?></strong> with passed deadlines.</p>
                <a href="jobs.php?show_expired=1" class="btn btn-sm btn-warning">
                    Review & Deactivate
                </a>
            </div>
        </div>
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
    <?php endif; ?>

    <!-- Expiring Soon Alert -->
    <?php if ($upcomingDeadlines > 0): ?>
    <div class="alert alert-info alert-dismissible fade show" role="alert">
        <div class="d-flex align-items-center">
            <div style="font-size: 2rem; margin-right: 1rem;">⏰</div>
            <div class="flex-grow-1">
                <h5 class="alert-heading mb-1">Jobs Expiring Soon</h5>
                <p class="mb-0"><strong><?php echo $upcomingDeadlines; ?> job<?php echo $upcomingDeadlines > 1 ? 's' : ''; ?></strong> will expire in the next 7 days.</p>
            </div>
        </div>
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
    <?php endif; ?>

    <!-- Category Distribution -->
    <div class="row mb-4">
        <div class="col-md-6 mb-3">
            <div class="card">
                <div class="card-header bg-primary text-white">
                    <h5 class="mb-0">📊 Jobs by Category</h5>
                </div>
                <div class="card-body">
                    <?php if (count($categoryStats) > 0): ?>
                    <div class="distribution-grid">
                        <?php foreach ($categoryStats as $stat): ?>
                        <div class="distribution-item">
                            <div class="icon"><?php echo $stat['icon']; ?></div>
                            <div class="count"><?php echo $stat['job_count']; ?></div>
                            <div class="label"><?php echo htmlspecialchars($stat['category_name']); ?></div>
                        </div>
                        <?php endforeach; ?>
                    </div>
                    <?php else: ?>
                    <p class="text-muted text-center py-3">No categories configured yet</p>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <div class="col-md-6 mb-3">
            <div class="card">
                <div class="card-header bg-info text-white">
                    <h5 class="mb-0">🏠 Jobs by Work Mode</h5>
                </div>
                <div class="card-body">
                    <?php if (count($workModeStats) > 0): ?>
                    <div class="distribution-grid">
                        <?php foreach ($workModeStats as $stat): ?>
                        <div class="distribution-item">
                            <div class="icon"><?php echo $stat['icon']; ?></div>
                            <div class="count"><?php echo $stat['job_count']; ?></div>
                            <div class="label"><?php echo htmlspecialchars($stat['mode_name']); ?></div>
                        </div>
                        <?php endforeach; ?>
                    </div>
                    <?php else: ?>
                    <p class="text-muted text-center py-3">No work modes configured yet</p>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>

    <!-- Master Data Overview -->
    <div class="row mb-4">
        <div class="col-md-3 mb-3">
            <div class="stat-card info">
                <div class="stat-icon">📂</div>
                <div class="stat-value"><?php echo $totalCategories; ?></div>
                <div class="stat-label">Categories</div>
            </div>
        </div>
        <div class="col-md-3 mb-3">
            <div class="stat-card info">
                <div class="stat-icon">💼</div>
                <div class="stat-value"><?php echo $totalEmploymentTypes; ?></div>
                <div class="stat-label">Employment Types</div>
            </div>
        </div>
        <div class="col-md-3 mb-3">
            <div class="stat-card info">
                <div class="stat-icon">🏢</div>
                <div class="stat-value"><?php echo $totalWorkModes; ?></div>
                <div class="stat-label">Work Modes</div>
            </div>
        </div>
        <div class="col-md-3 mb-3">
            <div class="stat-card info">
                <div class="stat-icon">🏛️</div>
                <div class="stat-value"><?php echo $totalDepartments; ?></div>
                <div class="stat-label">Departments</div>
            </div>
        </div>
    </div>

    <!-- Recent Jobs Table -->
    <div class="table-card">
        <div class="table-card-header">
            <h4>📋 Recent Job Postings</h4>
            <a href="jobs.php" class="btn btn-sm btn-primary">View All</a>
        </div>
        <div class="table-responsive">
            <?php if (count($recentJobs) > 0): ?>
            <table class="table table-hover mb-0">
                <thead>
                    <tr>
                        <th>Job Title</th>
                        <th>Company</th>
                        <th>Category</th>
                        <th>Work Mode</th>
                        <th>Type</th>
                        <th>Posted Date</th>
                        <th>Deadline</th>
                        <th>Status</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($recentJobs as $job): ?>
                    <tr>
                        <td><strong><?php echo sanitize($job['title']); ?></strong></td>
                        <td><?php echo sanitize($job['company']); ?></td>
                        <td>
                            <?php if ($job['category_name']): ?>
                                <span class="badge bg-primary">
                                    <?php echo $job['category_icon']; ?> <?php echo sanitize($job['category_name']); ?>
                                </span>
                            <?php else: ?>
                                <span class="text-muted">-</span>
                            <?php endif; ?>
                        </td>
                        <td>
                            <?php if ($job['mode_name']): ?>
                                <span class="badge bg-info">
                                    <?php echo $job['work_mode_icon']; ?> <?php echo $job['mode_name']; ?>
                                </span>
                            <?php else: ?>
                                <span class="text-muted">-</span>
                            <?php endif; ?>
                        </td>
                        <td>
                            <?php if ($job['type_name']): ?>
                                <span class="badge bg-secondary"><?php echo $job['type_name']; ?></span>
                            <?php else: ?>
                                <span class="text-muted">-</span>
                            <?php endif; ?>
                        </td>
                        <td><?php echo date('d M Y', strtotime($job['posted_date'])); ?></td>
                        <td>
                            <?php if ($job['application_deadline']): ?>
                                <?php 
                                $deadline = strtotime($job['application_deadline']);
                                $today = strtotime(date('Y-m-d'));
                                $isExpired = $deadline < $today;
                                ?>
                                <span class="badge <?php echo $isExpired ? 'bg-danger' : 'bg-warning text-dark'; ?>">
                                    <?php echo date('d M', $deadline); ?>
                                </span>
                            <?php else: ?>
                                <span class="text-muted">-</span>
                            <?php endif; ?>
                        </td>
                        <td>
                            <?php if ($job['is_active']): ?>
                                <span class="badge bg-success">✓ Active</span>
                            <?php else: ?>
                                <span class="badge bg-secondary">○ Inactive</span>
                            <?php endif; ?>
                        </td>
                        <td>
                            <div class="action-buttons">
                                <a href="jobs.php?edit=<?php echo $job['id']; ?>" class="btn btn-sm btn-warning" title="Edit">✏️</a>
                            </div>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
            <?php else: ?>
            <div class="empty-state-table">
                <div style="font-size: 3rem; margin-bottom: 1rem;">📭</div>
                <h5>No Jobs Posted Yet</h5>
                <p class="text-muted">Start by posting your first job</p>
                <a href="jobs.php" class="btn btn-primary mt-3">Post Your First Job</a>
            </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<?php include '../includes/footer.php'; ?>
