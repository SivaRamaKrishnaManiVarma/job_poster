<?php
require_once '../includes/config.php';
require_once '../includes/functions.php';
require_once '../includes/master-data-functions.php';

if (!isAdmin()) redirect('login.php');

$pageTitle = 'Manage Jobs';
$isAdmin = true;
$message = '';
$errors = [];

// Handle success messages from redirects
if (isset($_GET['success'])) {
    if ($_GET['success'] === 'bulk_deactivated' && isset($_GET['count'])) {
        $count = (int)$_GET['count'];
        $message = '<div class="alert alert-success alert-dismissible fade show" role="alert">
            <strong>✅ Bulk Action Complete!</strong> Successfully deactivated ' . $count . ' expired job' . ($count > 1 ? 's' : '') . '.
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>';
    }
}

// Load all master data (active only for dropdowns)
$jobCategories = getAllJobCategories($pdo, true);
$workModes = getAllWorkModes($pdo, true);
$employmentTypes = getAllEmploymentTypes($pdo, true);
$experienceLevels = getAllExperienceLevels($pdo, true);
$states = getAllStates($pdo, true);
$qualifications = getAllQualifications($pdo, true);
$departments = getAllDepartments($pdo, true);

// Get job counts for filter buttons
$totalJobsAll = $pdo->query("SELECT COUNT(*) FROM jobs WHERE is_active = 1")->fetchColumn();

$activeJobs = $pdo->query("
    SELECT COUNT(*) FROM jobs 
    WHERE is_active = 1 
    AND (application_deadline IS NULL OR application_deadline >= CURDATE())
")->fetchColumn();

$expiredJobs = $pdo->query("
    SELECT COUNT(*) FROM jobs 
    WHERE is_active = 1 
    AND application_deadline IS NOT NULL 
    AND application_deadline < CURDATE()
")->fetchColumn();

// Handle Add/Edit with Validation
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Validate inputs
    if (empty($_POST['title'])) $errors[] = 'Job title is required';
    if (empty($_POST['company'])) $errors[] = 'Company name is required';
    if (empty($_POST['job_link']) || !filter_var($_POST['job_link'], FILTER_VALIDATE_URL)) {
        $errors[] = 'Valid application link is required';
    }
    if (empty($_POST['job_category_id'])) $errors[] = 'Job category is required';
    if (empty($_POST['work_mode_id'])) $errors[] = 'Work mode is required';
    if (empty($_POST['employment_type_id'])) $errors[] = 'Employment type is required';
    if (empty($_POST['experience_level_id'])) $errors[] = 'Experience level is required';
    
    // Date validation
    $posted_date = $_POST['posted_date'];
    $deadline = $_POST['application_deadline'];
    
    if ($deadline && strtotime($deadline) < strtotime($posted_date)) {
        $errors[] = 'Application deadline cannot be before posted date';
    }
    
    if ($deadline && strtotime($deadline) < strtotime(date('Y-m-d'))) {
        $errors[] = 'Application deadline cannot be in the past';
    }
    
    if (empty($errors)) {
        if (isset($_POST['add_job'])) {
            $stmt = $pdo->prepare("INSERT INTO jobs (
                title, company, description, job_link, official_website,
                job_category_id, work_mode_id, employment_type_id, experience_level_id, 
                state_id, department_id, min_qualification_id,
                location, posted_date, application_deadline, is_active
            ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
            
            $stmt->execute([
                sanitize($_POST['title']),
                sanitize($_POST['company']),
                $_POST['description'],
                sanitize($_POST['job_link']),
                sanitize($_POST['official_website']) ?: null,
                $_POST['job_category_id'],
                $_POST['work_mode_id'],
                $_POST['employment_type_id'],
                $_POST['experience_level_id'],
                $_POST['state_id'] ?: null,
                $_POST['department_id'] ?: null,
                $_POST['min_qualification_id'] ?: null,
                sanitize($_POST['location']),
                $posted_date,
                $deadline ?: null,
                isset($_POST['is_active']) ? 1 : 0
            ]);
            
            $message = '<div class="alert alert-success alert-dismissible fade show" role="alert">
                <strong>✅ Success!</strong> Job posted successfully!
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>';
        } 
        elseif (isset($_POST['edit_job'])) {
            $stmt = $pdo->prepare("UPDATE jobs SET 
                title=?, company=?, description=?, job_link=?, official_website=?,
                job_category_id=?, work_mode_id=?, employment_type_id=?, experience_level_id=?,
                state_id=?, department_id=?, min_qualification_id=?,
                location=?, posted_date=?, application_deadline=?, is_active=?
                WHERE id=?");
            
            $stmt->execute([
                sanitize($_POST['title']),
                sanitize($_POST['company']),
                $_POST['description'],
                sanitize($_POST['job_link']),
                sanitize($_POST['official_website']) ?: null,
                $_POST['job_category_id'],
                $_POST['work_mode_id'],
                $_POST['employment_type_id'],
                $_POST['experience_level_id'],
                $_POST['state_id'] ?: null,
                $_POST['department_id'] ?: null,
                $_POST['min_qualification_id'] ?: null,
                sanitize($_POST['location']),
                $posted_date,
                $deadline ?: null,
                isset($_POST['is_active']) ? 1 : 0,
                $_POST['job_id']
            ]);
            
            $message = '<div class="alert alert-success alert-dismissible fade show" role="alert">
                <strong>✅ Success!</strong> Job updated successfully!
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>';
        }
        
        // Refresh counts after add/edit
        $totalJobsAll = $pdo->query("SELECT COUNT(*) FROM jobs WHERE is_active = 1")->fetchColumn();
        $activeJobs = $pdo->query("
            SELECT COUNT(*) FROM jobs 
            WHERE is_active = 1 
            AND (application_deadline IS NULL OR application_deadline >= CURDATE())
        ")->fetchColumn();
        $expiredJobs = $pdo->query("
            SELECT COUNT(*) FROM jobs 
            WHERE is_active = 1 
            AND application_deadline IS NOT NULL 
            AND application_deadline < CURDATE()
        ")->fetchColumn();
    } else {
        $message = '<div class="alert alert-danger alert-dismissible fade show" role="alert">
            <strong>⚠️ Validation Error!</strong>
            <ul class="mb-0 mt-2">';
        foreach ($errors as $error) {
            $message .= '<li>' . $error . '</li>';
        }
        $message .= '</ul>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>';
    }
}

// Handle Delete with confirmation
if (isset($_GET['delete'])) {
    $stmt = $pdo->prepare("DELETE FROM jobs WHERE id = ?");
    $stmt->execute([$_GET['delete']]);
    $message = '<div class="alert alert-success alert-dismissible fade show" role="alert">
        <strong>🗑️ Deleted!</strong> Job removed successfully!
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>';
    header('Location: jobs.php');
    exit;
}

// Handle Toggle
if (isset($_GET['toggle'])) {
    $stmt = $pdo->prepare("UPDATE jobs SET is_active = NOT is_active WHERE id = ?");
    $stmt->execute([$_GET['toggle']]);
    header('Location: jobs.php');
    exit;
}

// Get job for editing (with master data joined)
$editJob = null;
if (isset($_GET['edit'])) {
    $stmt = $pdo->prepare("SELECT j.*, 
        c.category_name, c.icon as category_icon,
        w.mode_name, w.icon as work_mode_icon,
        e.type_name, e.icon as employment_icon,
        ex.level_name, ex.icon as experience_icon,
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
        WHERE j.id = ?");
    $stmt->execute([$_GET['edit']]);
    $editJob = $stmt->fetch();
}

// Get all jobs with pagination (with JOINs)
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$perPage = 10;
$offset = ($page - 1) * $perPage;

// Build WHERE clause based on filter
$whereClause = "WHERE j.is_active = 1";
$filterLabel = "All Jobs";

if (isset($_GET['show_expired'])) {
    $whereClause .= " AND j.application_deadline IS NOT NULL AND j.application_deadline < CURDATE()";
    $filterLabel = "Expired Jobs";
} elseif (!isset($_GET['show_all'])) {
    $whereClause .= " AND (j.application_deadline IS NULL OR j.application_deadline >= CURDATE())";
    $filterLabel = "Active Jobs";
}

$totalJobs = $pdo->query("SELECT COUNT(*) FROM jobs j $whereClause")->fetchColumn();
$totalPages = ceil($totalJobs / $perPage);

$jobsQuery = "SELECT j.*, 
    c.category_name, c.icon as category_icon,
    w.mode_name, w.icon as work_mode_icon,
    e.type_name, e.icon as employment_icon,
    ex.level_name, ex.icon as experience_icon,
    s.state_name
    FROM jobs j
    LEFT JOIN master_job_categories c ON j.job_category_id = c.id
    LEFT JOIN master_work_modes w ON j.work_mode_id = w.id
    LEFT JOIN master_employment_types e ON j.employment_type_id = e.id
    LEFT JOIN master_experience_levels ex ON j.experience_level_id = ex.id
    LEFT JOIN master_states s ON j.state_id = s.id
    $whereClause
    ORDER BY j.posted_date DESC, j.id DESC 
    LIMIT $perPage OFFSET $offset";
$jobs = $pdo->query($jobsQuery)->fetchAll();

include 'includes/header.php';
?>

<div class="admin-container">
    <!-- Page Header -->
    <div class="page-header">
        <h2>📋 Manage Job Postings</h2>
        <p>Add, edit, and manage all job listings on your portal</p>
    </div>

    <?php echo $message; ?>
    
    <!-- Job Status Filter -->
    <div class="card mb-4">
        <div class="card-body">
            <div class="d-flex justify-content-between align-items-center flex-wrap gap-3">
                <div>
                    <h6 class="mb-0">Filter by Deadline Status:</h6>
                </div>
                <div class="btn-group" role="group">
                    <a href="jobs.php" class="btn btn-sm <?php echo !isset($_GET['show_expired']) && !isset($_GET['show_all']) ? 'btn-primary' : 'btn-outline-primary'; ?>">
                        ✅ Active (<?php echo $activeJobs; ?>)
                    </a>
                    <a href="jobs.php?show_expired=1" class="btn btn-sm <?php echo isset($_GET['show_expired']) ? 'btn-warning' : 'btn-outline-warning'; ?>">
                        ⚠️ Expired (<?php echo $expiredJobs; ?>)
                    </a>
                    <a href="jobs.php?show_all=1" class="btn btn-sm <?php echo isset($_GET['show_all']) ? 'btn-secondary' : 'btn-outline-secondary'; ?>">
                        📋 All (<?php echo $totalJobsAll; ?>)
                    </a>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Bulk Action for Expired Jobs -->
    <?php if (isset($_GET['show_expired']) && $expiredJobs > 0): ?>
    <div class="alert alert-warning alert-dismissible fade show">
        <div class="d-flex justify-content-between align-items-center flex-wrap gap-3">
            <div>
                <strong>⚠️ Bulk Action:</strong> Deactivate all expired jobs to clean up your listings
            </div>
            <form method="POST" action="bulk-actions.php" style="display: inline;" 
                  onsubmit="return confirm('⚠️ Deactivate all <?php echo $expiredJobs; ?> expired jobs?\n\nThis will hide them from public view. You can reactivate them later if needed.')">
                <input type="hidden" name="bulk_action" value="deactivate_expired">
                <button type="submit" class="btn btn-warning">
                    🗑️ Deactivate All Expired (<?php echo $expiredJobs; ?>)
                </button>
            </form>
        </div>
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
    <?php endif; ?>

    <!-- Add/Edit Form -->
    <div class="form-card" id="jobForm">
        <div class="form-card-header">
            <h4><?php echo $editJob ? '✏️ Edit Job Posting' : '➕ Post New Job'; ?></h4>
        </div>
        <div class="form-card-body">
            <form method="POST" id="jobPostForm" novalidate>
                <?php if ($editJob): ?>
                    <input type="hidden" name="job_id" value="<?php echo $editJob['id']; ?>">
                <?php endif; ?>
                
                <!-- Basic Information -->
                <div class="form-section">
                    <div class="form-section-title">
                        <span>📝</span> Basic Information
                    </div>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Job Title <span class="required">*</span></label>
                            <input type="text" 
                                   name="title" 
                                   class="form-control" 
                                   value="<?php echo $editJob ? sanitize($editJob['title']) : ''; ?>" 
                                   required 
                                   maxlength="200"
                                   placeholder="e.g., Full Stack Developer"
                                   id="jobTitle">
                            <div class="form-text">Be specific and clear about the role</div>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Company Name <span class="required">*</span></label>
                            <input type="text" 
                                   name="company" 
                                   class="form-control" 
                                   value="<?php echo $editJob ? sanitize($editJob['company']) : ''; ?>" 
                                   required 
                                   maxlength="100"
                                   placeholder="e.g., TechCorp India">
                            <div class="form-text">Official company name</div>
                        </div>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label">Job Description</label>
                        <textarea name="description" 
                                  class="form-control" 
                                  rows="4" 
                                  maxlength="2000"
                                  id="jobDescription"
                                  placeholder="Describe the role, responsibilities, and what makes this opportunity exciting..."><?php echo $editJob ? sanitize($editJob['description']) : ''; ?></textarea>
                        <div class="char-counter" id="descCounter">0 / 2000 characters</div>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Application Link <span class="required">*</span></label>
                            <input type="url" 
                                   name="job_link" 
                                   class="form-control" 
                                   value="<?php echo $editJob ? sanitize($editJob['job_link']) : ''; ?>" 
                                   required 
                                   placeholder="https://company.com/careers/apply">
                            <div class="form-text">Direct link where students can apply</div>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Official Website</label>
                            <input type="url" 
                                   name="official_website" 
                                   class="form-control" 
                                   value="<?php echo $editJob ? sanitize($editJob['official_website']) : ''; ?>" 
                                   placeholder="https://company.com">
                            <div class="form-text">Company's main website (optional)</div>
                        </div>
                    </div>
                </div>

                <!-- Job Classification -->
                <div class="form-section">
                    <div class="form-section-title">
                        <span>🏷️</span> Job Classification
                    </div>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Job Category <span class="required">*</span></label>
                            <select name="job_category_id" class="form-select" required>
                                <option value="">-- Select Category --</option>
                                <?php foreach ($jobCategories as $cat): ?>
                                <option value="<?php echo $cat['id']; ?>" 
                                    <?php echo ($editJob && $editJob['job_category_id'] == $cat['id']) ? 'selected' : ''; ?>>
                                    <?php echo $cat['icon']; ?> <?php echo htmlspecialchars($cat['category_name']); ?>
                                </option>
                                <?php endforeach; ?>
                            </select>
                            <div class="form-text">Primary job category/industry</div>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Department/Organization</label>
                            <select name="department_id" class="form-select">
                                <option value="">-- Select Department (Optional) --</option>
                                <?php foreach ($departments as $dept): ?>
                                <option value="<?php echo $dept['id']; ?>"
                                    <?php echo ($editJob && $editJob['department_id'] == $dept['id']) ? 'selected' : ''; ?>>
                                    <?php echo htmlspecialchars($dept['department_name']); ?> (<?php echo $dept['department_type']; ?>)
                                </option>
                                <?php endforeach; ?>
                            </select>
                            <div class="form-text">For government/PSU jobs</div>
                        </div>
                    </div>
                </div>

                <!-- Job Details -->
                <div class="form-section">
                    <div class="form-section-title">
                        <span>💼</span> Job Details
                    </div>
                    <div class="row">
                        <div class="col-md-4 mb-3">
                            <label class="form-label">Work Mode <span class="required">*</span></label>
                            <select name="work_mode_id" class="form-select" required>
                                <option value="">-- Select --</option>
                                <?php foreach ($workModes as $mode): ?>
                                <option value="<?php echo $mode['id']; ?>"
                                    <?php echo ($editJob && $editJob['work_mode_id'] == $mode['id']) ? 'selected' : ''; ?>>
                                    <?php echo $mode['icon']; ?> <?php echo htmlspecialchars($mode['mode_name']); ?>
                                </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-4 mb-3">
                            <label class="form-label">Employment Type <span class="required">*</span></label>
                            <select name="employment_type_id" class="form-select" required>
                                <option value="">-- Select --</option>
                                <?php foreach ($employmentTypes as $type): ?>
                                <option value="<?php echo $type['id']; ?>"
                                    <?php echo ($editJob && $editJob['employment_type_id'] == $type['id']) ? 'selected' : ''; ?>>
                                    <?php echo $type['icon']; ?> <?php echo htmlspecialchars($type['type_name']); ?>
                                </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-4 mb-3">
                            <label class="form-label">Experience Level <span class="required">*</span></label>
                            <select name="experience_level_id" class="form-select" required>
                                <option value="">-- Select --</option>
                                <?php foreach ($experienceLevels as $level): ?>
                                <option value="<?php echo $level['id']; ?>"
                                    <?php echo ($editJob && $editJob['experience_level_id'] == $level['id']) ? 'selected' : ''; ?>>
                                    <?php echo $level['icon']; ?> <?php echo htmlspecialchars($level['level_name']); ?>
                                </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Minimum Qualification</label>
                            <select name="min_qualification_id" class="form-select">
                                <option value="">-- Select Qualification (Optional) --</option>
                                <?php 
                                $currentLevel = '';
                                foreach ($qualifications as $qual): 
                                    if ($qual['qualification_level'] != $currentLevel) {
                                        if ($currentLevel != '') echo '</optgroup>';
                                        $currentLevel = $qual['qualification_level'];
                                        echo '<optgroup label="' . htmlspecialchars($currentLevel) . '">';
                                    }
                                ?>
                                <option value="<?php echo $qual['id']; ?>"
                                    <?php echo ($editJob && $editJob['min_qualification_id'] == $qual['id']) ? 'selected' : ''; ?>>
                                    <?php echo htmlspecialchars($qual['qualification_name']); ?>
                                </option>
                                <?php endforeach; ?>
                                <?php if ($currentLevel != '') echo '</optgroup>'; ?>
                            </select>
                            <div class="form-text">Required educational qualification</div>
                        </div>
                    </div>
                </div>

                <!-- Location -->
                <div class="form-section">
                    <div class="form-section-title">
                        <span>📍</span> Location Details
                    </div>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">State/Region</label>
                            <select name="state_id" class="form-select">
                                <option value="">-- Select State (Optional) --</option>
                                <?php foreach ($states as $state): ?>
                                <option value="<?php echo $state['id']; ?>"
                                    <?php echo ($editJob && $editJob['state_id'] == $state['id']) ? 'selected' : ''; ?>>
                                    <?php echo htmlspecialchars($state['state_name']); ?>
                                </option>
                                <?php endforeach; ?>
                            </select>
                            <div class="form-text">Primary state/territory</div>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">City/Specific Location</label>
                            <input type="text" 
                                   name="location" 
                                   class="form-control" 
                                   value="<?php echo $editJob ? sanitize($editJob['location']) : ''; ?>" 
                                   placeholder="e.g., Visakhapatnam, Vijayawada, Multiple Cities">
                            <div class="form-text">City name or "All India", "Remote", etc.</div>
                        </div>
                    </div>
                </div>

                <!-- Dates -->
                <div class="form-section">
                    <div class="form-section-title">
                        <span>📅</span> Important Dates
                    </div>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Posted Date <span class="required">*</span></label>
                            <input type="date" 
                                   name="posted_date" 
                                   class="form-control" 
                                   value="<?php echo $editJob ? $editJob['posted_date'] : date('Y-m-d'); ?>" 
                                   required
                                   id="postedDate">
                            <div class="form-text">When this job was posted</div>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Application Deadline</label>
                            <input type="date" 
                                   name="application_deadline" 
                                   class="form-control" 
                                   value="<?php echo $editJob ? $editJob['application_deadline'] : ''; ?>"
                                   min="<?php echo date('Y-m-d'); ?>"
                                   id="applicationDeadline">
                            <div class="form-text" id="deadlineHelp">Last date for students to apply</div>
                        </div>
                    </div>
                </div>

                <!-- Status -->
                <div class="form-section">
                    <div class="form-section-title">
                        <span>⚙️</span> Visibility Settings
                    </div>
                    <div class="form-check form-switch">
                        <input class="form-check-input" 
                               type="checkbox" 
                               name="is_active" 
                               id="is_active" 
                               <?php echo (!$editJob || $editJob['is_active']) ? 'checked' : ''; ?>
                               style="width: 3rem; height: 1.5rem; cursor: pointer;">
                        <label class="form-check-label" for="is_active" style="margin-left: 0.5rem; cursor: pointer;">
                            <strong>Active</strong> - Job will be visible to students on the portal
                        </label>
                    </div>
                </div>

                <!-- Action Buttons -->
                <div class="btn-action-group">
                    <button type="submit" 
                            name="<?php echo $editJob ? 'edit_job' : 'add_job'; ?>" 
                            class="btn btn-primary btn-lg btn-icon">
                        <span><?php echo $editJob ? '💾' : '✅'; ?></span>
                        <?php echo $editJob ? 'Update Job' : 'Post Job'; ?>
                    </button>
                    <?php if ($editJob): ?>
                        <a href="jobs.php" class="btn btn-secondary btn-lg btn-icon">
                            <span>❌</span> Cancel
                        </a>
                    <?php endif; ?>
                </div>
            </form>
        </div>
    </div>

    <!-- Jobs List Table -->
    <div class="table-card">
        <div class="table-card-header">
            <h4>📊 <?php echo $filterLabel; ?> (<?php echo $totalJobs; ?>)</h4>
            <?php if ($editJob): ?>
                <a href="jobs.php" class="btn btn-sm btn-outline-primary">+ Add New Job</a>
            <?php endif; ?>
        </div>
        <div class="table-responsive">
            <?php if (count($jobs) > 0): ?>
            <table class="table table-hover mb-0">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Job Title</th>
                        <th>Company</th>
                        <th>Category</th>
                        <th>Work Mode</th>
                        <th>Type</th>
                        <th>Experience</th>
                        <th>Deadline</th>
                        <th>Status</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($jobs as $job): ?>
                    <tr>
                        <td data-label="ID"><?php echo $job['id']; ?></td>
                        <td data-label="Title"><strong><?php echo sanitize($job['title']); ?></strong></td>
                        <td data-label="Company"><?php echo sanitize($job['company']); ?></td>
                        <td data-label="Category">
                            <?php if ($job['category_name']): ?>
                                <span class="badge bg-primary">
                                    <?php echo $job['category_icon']; ?> <?php echo sanitize($job['category_name']); ?>
                                </span>
                            <?php else: ?>
                                <span class="text-muted">-</span>
                            <?php endif; ?>
                        </td>
                        <td data-label="Work Mode">
                            <?php if ($job['mode_name']): ?>
                                <span class="badge bg-info">
                                    <?php echo $job['work_mode_icon']; ?> <?php echo $job['mode_name']; ?>
                                </span>
                            <?php else: ?>
                                <span class="text-muted">-</span>
                            <?php endif; ?>
                        </td>
                        <td data-label="Type">
                            <?php if ($job['type_name']): ?>
                                <span class="badge bg-secondary">
                                    <?php echo $job['employment_icon']; ?> <?php echo $job['type_name']; ?>
                                </span>
                            <?php else: ?>
                                <span class="text-muted">-</span>
                            <?php endif; ?>
                        </td>
                        <td data-label="Experience">
                            <?php if ($job['level_name']): ?>
                                <?php echo $job['experience_icon']; ?> <?php echo $job['level_name']; ?>
                            <?php else: ?>
                                <span class="text-muted">-</span>
                            <?php endif; ?>
                        </td>
                        <td data-label="Deadline">
                            <?php if ($job['application_deadline']): ?>
                                <?php 
                                $deadline = strtotime($job['application_deadline']);
                                $today = strtotime(date('Y-m-d'));
                                $isExpired = $deadline < $today;
                                ?>
                                <span class="badge <?php echo $isExpired ? 'bg-danger' : 'bg-warning text-dark'; ?>">
                                    <?php echo date('d M Y', $deadline); ?>
                                    <?php echo $isExpired ? ' ⚠️' : ''; ?>
                                </span>
                            <?php else: ?>
                                <span class="text-muted">No deadline</span>
                            <?php endif; ?>
                        </td>
                        <td data-label="Status">
                            <?php if ($job['is_active']): ?>
                                <span class="badge bg-success">✓ Active</span>
                            <?php else: ?>
                                <span class="badge bg-secondary">○ Inactive</span>
                            <?php endif; ?>
                        </td>
                        <td data-label="Actions">
                            <div class="action-buttons">
                                <a href="?edit=<?php echo $job['id']; ?>#jobForm" class="btn btn-sm btn-warning" title="Edit Job">✏️</a>
                                <a href="?toggle=<?php echo $job['id']; ?>" class="btn btn-sm btn-info" title="Toggle Active/Inactive">
                                    <?php echo $job['is_active'] ? '⏸️' : '▶️'; ?>
                                </a>
                                <a href="?delete=<?php echo $job['id']; ?>" 
                                   class="btn btn-sm btn-danger" 
                                   onclick="return confirm('⚠️ Are you sure you want to delete this job?\n\nJob: <?php echo addslashes($job['title']); ?>\nCompany: <?php echo addslashes($job['company']); ?>\n\nThis action cannot be undone!')" 
                                   title="Delete Job">
                                    🗑️
                                </a>
                            </div>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
            <?php else: ?>
            <div class="empty-state-table">
                <div style="font-size: 3rem; margin-bottom: 1rem;">📭</div>
                <h5>No Jobs Found</h5>
                <p class="text-muted">
                    <?php if (isset($_GET['show_expired'])): ?>
                        No expired jobs at the moment.
                    <?php else: ?>
                        Start by posting your first job listing above.
                    <?php endif; ?>
                </p>
            </div>
            <?php endif; ?>
        </div>
    </div>

    <!-- Pagination -->
    <?php if ($totalPages > 1): ?>
    <nav aria-label="Job listings pagination">
        <ul class="pagination">
            <?php if ($page > 1): ?>
                <li class="page-item">
                    <a class="page-link" href="?page=<?php echo $page - 1; ?><?php echo isset($_GET['show_expired']) ? '&show_expired=1' : ''; ?><?php echo isset($_GET['show_all']) ? '&show_all=1' : ''; ?>">Previous</a>
                </li>
            <?php endif; ?>
            
            <?php for ($i = 1; $i <= $totalPages; $i++): ?>
                <li class="page-item <?php echo $page == $i ? 'active' : ''; ?>">
                    <a class="page-link" href="?page=<?php echo $i; ?><?php echo isset($_GET['show_expired']) ? '&show_expired=1' : ''; ?><?php echo isset($_GET['show_all']) ? '&show_all=1' : ''; ?>"><?php echo $i; ?></a>
                </li>
            <?php endfor; ?>
            
            <?php if ($page < $totalPages): ?>
                <li class="page-item">
                    <a class="page-link" href="?page=<?php echo $page + 1; ?><?php echo isset($_GET['show_expired']) ? '&show_expired=1' : ''; ?><?php echo isset($_GET['show_all']) ? '&show_all=1' : ''; ?>">Next</a>
                </li>
            <?php endif; ?>
        </ul>
    </nav>
    <?php endif; ?>
</div>

<script>
// Character Counter for Description
const descTextarea = document.getElementById('jobDescription');
const descCounter = document.getElementById('descCounter');

if (descTextarea) {
    descTextarea.addEventListener('input', function() {
        const length = this.value.length;
        descCounter.textContent = `${length} / 2000 characters`;
        if (length > 1900) {
            descCounter.style.color = 'var(--danger)';
        } else {
            descCounter.style.color = 'var(--gray-500)';
        }
    });
    // Trigger on load
    descTextarea.dispatchEvent(new Event('input'));
}

// Date Validation
const postedDate = document.getElementById('postedDate');
const deadlineDate = document.getElementById('applicationDeadline');
const deadlineHelp = document.getElementById('deadlineHelp');

function validateDates() {
    if (postedDate && deadlineDate) {
        const posted = new Date(postedDate.value);
        const deadline = new Date(deadlineDate.value);
        const today = new Date();
        today.setHours(0, 0, 0, 0);
        
        // Set minimum deadline to posted date
        if (postedDate.value) {
            deadlineDate.min = postedDate.value;
        }
        
        // Validate deadline
        if (deadlineDate.value) {
            if (deadline < posted) {
                deadlineHelp.textContent = '⚠️ Deadline cannot be before posted date';
                deadlineHelp.style.color = 'var(--danger)';
                deadlineDate.setCustomValidity('Deadline must be after posted date');
            } else if (deadline < today) {
                deadlineHelp.textContent = '⚠️ Deadline cannot be in the past';
                deadlineHelp.style.color = 'var(--danger)';
                deadlineDate.setCustomValidity('Deadline cannot be in the past');
            } else {
                const daysLeft = Math.ceil((deadline - today) / (1000 * 60 * 60 * 24));
                deadlineHelp.textContent = `✓ ${daysLeft} days from today`;
                deadlineHelp.style.color = 'var(--success)';
                deadlineDate.setCustomValidity('');
            }
        } else {
            deadlineHelp.textContent = 'Last date for students to apply (optional)';
            deadlineHelp.style.color = 'var(--gray-500)';
            deadlineDate.setCustomValidity('');
        }
    }
}

if (postedDate) postedDate.addEventListener('change', validateDates);
if (deadlineDate) deadlineDate.addEventListener('change', validateDates);

// Run validation on load
validateDates();

// Form Submission Confirmation
const jobForm = document.getElementById('jobPostForm');
if (jobForm) {
    jobForm.addEventListener('submit', function(e) {
        if (!this.checkValidity()) {
            e.preventDefault();
            e.stopPropagation();
            alert('⚠️ Please fill in all required fields correctly');
        }
        this.classList.add('was-validated');
    });
}

// Auto-scroll to form when editing
if (window.location.hash === '#jobForm') {
    setTimeout(() => {
        document.getElementById('jobForm').scrollIntoView({ behavior: 'smooth', block: 'start' });
    }, 100);
}

// Confirm before leaving with unsaved changes
let formChanged = false;
if (jobForm) {
    jobForm.addEventListener('change', () => { formChanged = true; });
    jobForm.addEventListener('submit', () => { formChanged = false; });
    
    window.addEventListener('beforeunload', (e) => {
        if (formChanged) {
            e.preventDefault();
            e.returnValue = '';
        }
    });
}
</script>

<?php include '../includes/footer.php'; ?>
