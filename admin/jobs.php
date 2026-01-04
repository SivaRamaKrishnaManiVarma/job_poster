<?php
require_once '../includes/config.php';
require_once '../includes/functions.php';

if (!isAdmin()) redirect('login.php');

$pageTitle = 'Manage Jobs';
$isAdmin = true;
$message = '';
$errors = [];

// Handle Add/Edit with Validation
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Validate inputs
    if (empty($_POST['title'])) $errors[] = 'Job title is required';
    if (empty($_POST['company'])) $errors[] = 'Company name is required';
    if (empty($_POST['job_link']) || !filter_var($_POST['job_link'], FILTER_VALIDATE_URL)) {
        $errors[] = 'Valid application link is required';
    }
    
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
            $stmt = $pdo->prepare("INSERT INTO jobs (title, company, description, job_link, work_mode, employment_type, experience_level, location, category, posted_date, application_deadline, is_active) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
            $stmt->execute([
                sanitize($_POST['title']),
                sanitize($_POST['company']),
                $_POST['description'],
                sanitize($_POST['job_link']),
                $_POST['work_mode'],
                $_POST['employment_type'],
                $_POST['experience_level'],
                sanitize($_POST['location']),
                sanitize($_POST['category']),
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
            $stmt = $pdo->prepare("UPDATE jobs SET title=?, company=?, description=?, job_link=?, work_mode=?, employment_type=?, experience_level=?, location=?, category=?, posted_date=?, application_deadline=?, is_active=? WHERE id=?");
            $stmt->execute([
                sanitize($_POST['title']),
                sanitize($_POST['company']),
                $_POST['description'],
                sanitize($_POST['job_link']),
                $_POST['work_mode'],
                $_POST['employment_type'],
                $_POST['experience_level'],
                sanitize($_POST['location']),
                sanitize($_POST['category']),
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

// Get job for editing
$editJob = null;
if (isset($_GET['edit'])) {
    $stmt = $pdo->prepare("SELECT * FROM jobs WHERE id = ?");
    $stmt->execute([$_GET['edit']]);
    $editJob = $stmt->fetch();
}

// Get all jobs with pagination
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$perPage = 10;
$offset = ($page - 1) * $perPage;

$totalJobs = $pdo->query("SELECT COUNT(*) FROM jobs")->fetchColumn();
$totalPages = ceil($totalJobs / $perPage);

$jobs = $pdo->query("SELECT * FROM jobs ORDER BY posted_date DESC LIMIT $perPage OFFSET $offset")->fetchAll();

include 'includes/header.php';
?>

<div class="admin-container">
    <!-- Page Header -->
    <div class="page-header">
        <h2>📋 Manage Job Postings</h2>
        <p>Add, edit, and manage all job listings on your portal</p>
    </div>

    <?php echo $message; ?>

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
                    
                    <div class="mb-3">
                        <label class="form-label">Application Link <span class="required">*</span></label>
                        <input type="url" 
                               name="job_link" 
                               class="form-control" 
                               value="<?php echo $editJob ? sanitize($editJob['job_link']) : ''; ?>" 
                               required 
                               placeholder="https://company.com/careers/apply">
                        <div class="form-text">Direct link where students can apply</div>
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
                            <select name="work_mode" class="form-select" required>
                                <option value="Work from Home" <?php echo ($editJob && $editJob['work_mode'] == 'Work from Home') ? 'selected' : ''; ?>>🏠 Work from Home</option>
                                <option value="On-site" <?php echo ($editJob && $editJob['work_mode'] == 'On-site') ? 'selected' : ''; ?>>🏢 On-site</option>
                                <option value="Hybrid" <?php echo ($editJob && $editJob['work_mode'] == 'Hybrid') ? 'selected' : ''; ?>>🔄 Hybrid</option>
                            </select>
                        </div>
                        <div class="col-md-4 mb-3">
                            <label class="form-label">Employment Type <span class="required">*</span></label>
                            <select name="employment_type" class="form-select" required>
                                <option value="Full-time" <?php echo ($editJob && $editJob['employment_type'] == 'Full-time') ? 'selected' : ''; ?>>Full-time</option>
                                <option value="Part-time" <?php echo ($editJob && $editJob['employment_type'] == 'Part-time') ? 'selected' : ''; ?>>Part-time</option>
                                <option value="Internship" <?php echo ($editJob && $editJob['employment_type'] == 'Internship') ? 'selected' : ''; ?>>Internship</option>
                            </select>
                        </div>
                        <div class="col-md-4 mb-3">
                            <label class="form-label">Experience Level <span class="required">*</span></label>
                            <select name="experience_level" class="form-select" required>
                                <option value="Freshers" <?php echo ($editJob && $editJob['experience_level'] == 'Freshers') ? 'selected' : ''; ?>>🎓 Freshers</option>
                                <option value="0-2 years" <?php echo ($editJob && $editJob['experience_level'] == '0-2 years') ? 'selected' : ''; ?>>0-2 years</option>
                                <option value="2-5 years" <?php echo ($editJob && $editJob['experience_level'] == '2-5 years') ? 'selected' : ''; ?>>2-5 years</option>
                                <option value="5+ years" <?php echo ($editJob && $editJob['experience_level'] == '5+ years') ? 'selected' : ''; ?>>5+ years</option>
                            </select>
                        </div>
                    </div>
                </div>

                <!-- Location & Category -->
                <div class="form-section">
                    <div class="form-section-title">
                        <span>📍</span> Location & Category
                    </div>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Location</label>
                            <input type="text" 
                                   name="location" 
                                   class="form-control" 
                                   value="<?php echo $editJob ? sanitize($editJob['location']) : ''; ?>" 
                                   placeholder="e.g., Visakhapatnam, Andhra Pradesh"
                                   list="locationSuggestions">
                            <datalist id="locationSuggestions">
                                <option value="Visakhapatnam, Andhra Pradesh">
                                <option value="Vijayawada, Andhra Pradesh">
                                <option value="Hyderabad, Telangana">
                                <option value="Bangalore, Karnataka">
                                <option value="Mumbai, Maharashtra">
                                <option value="Remote">
                            </datalist>
                            <div class="form-text">City, State or "Remote"</div>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Category</label>
                            <input type="text" 
                                   name="category" 
                                   class="form-control" 
                                   value="<?php echo $editJob ? sanitize($editJob['category']) : ''; ?>" 
                                   placeholder="e.g., IT & Software"
                                   list="categorySuggestions">
                            <datalist id="categorySuggestions">
                                <option value="IT & Software">
                                <option value="Engineering">
                                <option value="Marketing">
                                <option value="Sales">
                                <option value="Finance">
                                <option value="HR">
                                <option value="Design">
                                <option value="Content Writing">
                                <option value="Data Science">
                            </datalist>
                            <div class="form-text">Job industry/domain</div>
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
            <h4>📊 All Job Listings (<?php echo $totalJobs; ?>)</h4>
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
                        <td data-label="Work Mode">
                            <span class="badge bg-info"><?php echo $job['work_mode']; ?></span>
                        </td>
                        <td data-label="Type">
                            <span class="badge bg-secondary"><?php echo $job['employment_type']; ?></span>
                        </td>
                        <td data-label="Experience"><?php echo $job['experience_level']; ?></td>
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
                                <a href="?edit=<?php echo $job['id']; ?>#jobForm" class="btn btn-sm btn-warning">✏️ Edit</a>
                                <a href="?toggle=<?php echo $job['id']; ?>" class="btn btn-sm btn-info" title="Toggle Active/Inactive">
                                    <?php echo $job['is_active'] ? '⏸️' : '▶️'; ?>
                                </a>
                                <a href="?delete=<?php echo $job['id']; ?>" 
                                   class="btn btn-sm btn-danger" 
                                   onclick="return confirm('⚠️ Are you sure you want to delete this job?\n\nJob: <?php echo addslashes($job['title']); ?>\nCompany: <?php echo addslashes($job['company']); ?>\n\nThis action cannot be undone!')">
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
                <h5>No Jobs Posted Yet</h5>
                <p class="text-muted">Start by posting your first job listing above</p>
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
                    <a class="page-link" href="?page=<?php echo $page - 1; ?>">Previous</a>
                </li>
            <?php endif; ?>
            
            <?php for ($i = 1; $i <= $totalPages; $i++): ?>
                <li class="page-item <?php echo $page == $i ? 'active' : ''; ?>">
                    <a class="page-link" href="?page=<?php echo $i; ?>"><?php echo $i; ?></a>
                </li>
            <?php endfor; ?>
            
            <?php if ($page < $totalPages): ?>
                <li class="page-item">
                    <a class="page-link" href="?page=<?php echo $page + 1; ?>">Next</a>
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
