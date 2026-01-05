<?php
require_once 'includes/config.php';
require_once 'includes/functions.php';
require_once 'includes/master-data-functions.php';

// Get job ID from URL
$jobId = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if ($jobId === 0) {
    header('Location: index.php');
    exit;
}

// Get job details with all master data
$stmt = $pdo->prepare("SELECT j.*, 
    c.category_name, c.icon as category_icon,
    w.mode_name, w.icon as work_mode_icon,
    e.type_name, e.icon as employment_icon,
    ex.level_name, ex.icon as experience_icon,
    s.state_name,
    d.department_name, d.department_type,
    q.qualification_name, q.qualification_level
    FROM jobs j
    LEFT JOIN master_job_categories c ON j.job_category_id = c.id
    LEFT JOIN master_work_modes w ON j.work_mode_id = w.id
    LEFT JOIN master_employment_types e ON j.employment_type_id = e.id
    LEFT JOIN master_experience_levels ex ON j.experience_level_id = ex.id
    LEFT JOIN master_states s ON j.state_id = s.id
    LEFT JOIN master_departments d ON j.department_id = d.id
    LEFT JOIN master_qualifications q ON j.min_qualification_id = q.id
    WHERE j.id = ? AND j.is_active = 1");
$stmt->execute([$jobId]);
$job = $stmt->fetch();

// If job not found or inactive, redirect
if (!$job) {
    header('Location: index.php');
    exit;
}

// Check if job is expired
$isExpired = $job['application_deadline'] && strtotime($job['application_deadline']) < strtotime(date('Y-m-d'));

// Get related jobs (same category, excluding current job)
$relatedJobs = [];
if ($job['job_category_id']) {
    $stmt = $pdo->prepare("SELECT j.*, 
        c.category_name, c.icon as category_icon,
        w.mode_name, w.icon as work_mode_icon,
        e.type_name, e.icon as employment_icon,
        ex.level_name, ex.icon as experience_icon
        FROM jobs j
        LEFT JOIN master_job_categories c ON j.job_category_id = c.id
        LEFT JOIN master_work_modes w ON j.work_mode_id = w.id
        LEFT JOIN master_employment_types e ON j.employment_type_id = e.id
        LEFT JOIN master_experience_levels ex ON j.experience_level_id = ex.id
        WHERE j.job_category_id = ? 
        AND j.id != ? 
        AND j.is_active = 1
        AND (j.application_deadline IS NULL OR j.application_deadline >= CURDATE())
        ORDER BY j.posted_date DESC
        LIMIT 3");
    $stmt->execute([$job['job_category_id'], $jobId]);
    $relatedJobs = $stmt->fetchAll();
}

$pageTitle = $job['title'] . ' - ' . $job['company'];

include 'includes/header.php';
?>

<style>
/* Professional Job Details Page */
.job-details-container {
    max-width: 1200px;
    margin: 2rem auto;
    padding: 0 1rem;
}

/* Back Button */
.back-link {
    display: inline-flex;
    align-items: center;
    gap: 0.5rem;
    color: var(--primary);
    text-decoration: none;
    font-weight: 600;
    margin-bottom: 2rem;
    transition: gap 0.2s;
}

.back-link:hover {
    gap: 0.75rem;
    color: var(--primary-hover);
}

/* Header Section */
.job-header {
    background: white;
    border-radius: var(--radius-xl);
    padding: 2.5rem;
    margin-bottom: 2rem;
    box-shadow: var(--shadow-md);
    border: 1px solid var(--gray-200);
}

.company-info {
    display: flex;
    align-items: flex-start;
    gap: 1.5rem;
    margin-bottom: 2rem;
}

.company-logo-large {
    width: 80px;
    height: 80px;
    background: linear-gradient(135deg, var(--primary), var(--primary-hover));
    color: white;
    border-radius: var(--radius-lg);
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 2rem;
    font-weight: 700;
    flex-shrink: 0;
    box-shadow: var(--shadow);
}

.job-title-section {
    flex-grow: 1;
}

.job-title {
    font-size: 2rem;
    font-weight: 700;
    color: var(--gray-900);
    margin: 0 0 0.5rem 0;
    line-height: 1.2;
}

.company-name {
    font-size: 1.25rem;
    color: var(--gray-600);
    margin: 0;
    display: flex;
    align-items: center;
    gap: 0.5rem;
}

.company-website {
    color: var(--primary);
    text-decoration: none;
    font-size: 0.9375rem;
}

.company-website:hover {
    text-decoration: underline;
}

/* Job Meta */
.job-meta {
    display: flex;
    flex-wrap: wrap;
    gap: 0.75rem;
    padding-top: 1.5rem;
    border-top: 1px solid var(--gray-200);
}

.meta-tag {
    display: inline-flex;
    align-items: center;
    gap: 0.5rem;
    padding: 0.5rem 1rem;
    background: var(--gray-50);
    border-radius: var(--radius);
    font-size: 0.9375rem;
    font-weight: 500;
    color: var(--gray-700);
    border: 1px solid var(--gray-200);
}

/* Main Content Area */
.content-wrapper {
    display: grid;
    grid-template-columns: 1fr 380px;
    gap: 2rem;
    align-items: start;
}

.main-content {
    background: white;
    border-radius: var(--radius-xl);
    padding: 2.5rem;
    box-shadow: var(--shadow);
    border: 1px solid var(--gray-200);
}

.content-section {
    margin-bottom: 3rem;
}

.content-section:last-child {
    margin-bottom: 0;
}

.section-title {
    font-size: 1.5rem;
    font-weight: 700;
    color: var(--gray-900);
    margin: 0 0 1.5rem 0;
    padding-bottom: 0.75rem;
    border-bottom: 2px solid var(--gray-200);
}

.job-description {
    font-size: 1rem;
    line-height: 1.8;
    color: var(--gray-700);
    white-space: pre-wrap;
}

/* Details Grid */
.details-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(240px, 1fr));
    gap: 1.25rem;
}

.detail-item {
    padding: 1.25rem;
    background: var(--gray-50);
    border-radius: var(--radius);
    border-left: 3px solid var(--primary);
}

.detail-label {
    font-size: 0.8125rem;
    font-weight: 600;
    color: var(--gray-500);
    text-transform: uppercase;
    letter-spacing: 0.5px;
    margin: 0 0 0.5rem 0;
}

.detail-value {
    font-size: 1rem;
    font-weight: 600;
    color: var(--gray-900);
    margin: 0;
    display: flex;
    align-items: center;
    gap: 0.5rem;
}

/* Sidebar */
.sidebar {
    position: sticky;
    top: 100px;
}

.apply-card {
    background: white;
    border-radius: var(--radius-xl);
    padding: 2rem;
    box-shadow: var(--shadow-lg);
    border: 2px solid var(--primary);
}

.apply-card-title {
    font-size: 1.25rem;
    font-weight: 700;
    color: var(--gray-900);
    margin: 0 0 1.5rem 0;
}

.apply-btn {
    display: block;
    width: 100%;
    padding: 1rem 1.5rem;
    background: var(--primary);
    color: white;
    text-align: center;
    text-decoration: none;
    border-radius: var(--radius);
    font-weight: 700;
    font-size: 1.125rem;
    transition: var(--transition);
    border: none;
    cursor: pointer;
}

.apply-btn:hover {
    background: var(--primary-hover);
    transform: translateY(-2px);
    box-shadow: var(--shadow-lg);
    color: white;
}

.apply-btn.disabled {
    background: var(--gray-400);
    cursor: not-allowed;
    transform: none;
}

.apply-btn.disabled:hover {
    background: var(--gray-400);
    transform: none;
}

.deadline-info {
    margin-top: 1.5rem;
    padding: 1rem;
    border-radius: var(--radius);
    font-size: 0.9375rem;
}

.deadline-info.urgent {
    background: #fef3c7;
    border: 1px solid var(--warning);
    color: #92400e;
}

.deadline-info.normal {
    background: #dbeafe;
    border: 1px solid var(--info);
    color: #1e40af;
}

.deadline-info.expired {
    background: #fee2e2;
    border: 1px solid var(--danger);
    color: #991b1b;
}

.deadline-info strong {
    display: block;
    margin-bottom: 0.25rem;
}

/* Share Section */
.share-section {
    margin-top: 2rem;
    padding-top: 2rem;
    border-top: 1px solid var(--gray-200);
}

.share-title {
    font-size: 0.875rem;
    font-weight: 600;
    color: var(--gray-600);
    text-transform: uppercase;
    letter-spacing: 0.5px;
    margin: 0 0 1rem 0;
}

.share-buttons {
    display: grid;
    grid-template-columns: repeat(2, 1fr);
    gap: 0.75rem;
}

.share-btn {
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 0.5rem;
    padding: 0.75rem;
    border-radius: var(--radius);
    text-decoration: none;
    font-weight: 600;
    font-size: 0.875rem;
    transition: var(--transition);
}

.share-btn:hover {
    transform: translateY(-2px);
    box-shadow: var(--shadow-md);
}

.share-btn.facebook {
    background: #1877f2;
    color: white;
}

.share-btn.twitter {
    background: #1da1f2;
    color: white;
}

.share-btn.linkedin {
    background: #0077b5;
    color: white;
}

.share-btn.whatsapp {
    background: #25d366;
    color: white;
}

/* Related Jobs */
.related-section {
    margin-top: 4rem;
}

.related-title {
    font-size: 1.75rem;
    font-weight: 700;
    color: var(--gray-900);
    margin: 0 0 1.5rem 0;
}

.related-jobs-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(320px, 1fr));
    gap: 1.5rem;
}

.related-job-card {
    background: white;
    border-radius: var(--radius-xl);
    padding: 1.5rem;
    box-shadow: var(--shadow);
    border: 1px solid var(--gray-200);
    transition: var(--transition);
    text-decoration: none;
    display: block;
    color: inherit;
}

.related-job-card:hover {
    transform: translateY(-4px);
    box-shadow: var(--shadow-xl);
    border-color: var(--primary);
}

.related-job-title {
    font-size: 1.125rem;
    font-weight: 700;
    color: var(--gray-900);
    margin: 0 0 0.5rem 0;
}

.related-job-company {
    font-size: 0.9375rem;
    color: var(--gray-600);
    margin: 0 0 1rem 0;
}

.related-job-meta {
    display: flex;
    flex-wrap: wrap;
    gap: 0.5rem;
}

/* Responsive */
@media (max-width: 991px) {
    .content-wrapper {
        grid-template-columns: 1fr;
    }
    
    .sidebar {
        position: static;
        order: -1;
    }
}

@media (max-width: 767px) {
    .job-header {
        padding: 1.5rem;
    }
    
    .company-info {
        flex-direction: column;
        align-items: center;
        text-align: center;
    }
    
    .company-name {
        justify-content: center;
    }
    
    .job-title {
        font-size: 1.5rem;
    }
    
    .main-content {
        padding: 1.5rem;
    }
    
    .details-grid {
        grid-template-columns: 1fr;
    }
    
    .related-jobs-grid {
        grid-template-columns: 1fr;
    }
    
    .share-buttons {
        grid-template-columns: 1fr;
    }
}
</style>

<div class="job-details-container">
    <!-- Back Button -->
    <a href="index.php" class="back-link">
        ← Back to All Jobs
    </a>

    <!-- Job Header -->
    <div class="job-header">
        <div class="company-info">
            <div class="company-logo-large">
                <?php echo strtoupper(substr($job['company'], 0, 2)); ?>
            </div>
            <div class="job-title-section">
                <h1 class="job-title"><?php echo htmlspecialchars($job['title']); ?></h1>
                <p class="company-name">
                    <span>🏢 <?php echo htmlspecialchars($job['company']); ?></span>
                    <?php if ($job['official_website']): ?>
                        | <a href="<?php echo htmlspecialchars($job['official_website']); ?>" target="_blank" rel="noopener" class="company-website">Visit Website</a>
                    <?php endif; ?>
                </p>
            </div>
        </div>

        <div class="job-meta">
            <?php if ($job['location']): ?>
                <span class="meta-tag">📍 <?php echo htmlspecialchars($job['location']); ?></span>
            <?php endif; ?>
            <?php if ($job['mode_name']): ?>
                <span class="meta-tag"><?php echo $job['work_mode_icon']; ?> <?php echo htmlspecialchars($job['mode_name']); ?></span>
            <?php endif; ?>
            <?php if ($job['type_name']): ?>
                <span class="meta-tag"><?php echo $job['employment_icon']; ?> <?php echo htmlspecialchars($job['type_name']); ?></span>
            <?php endif; ?>
            <?php if ($job['level_name']): ?>
                <span class="meta-tag"><?php echo $job['experience_icon']; ?> <?php echo htmlspecialchars($job['level_name']); ?></span>
            <?php endif; ?>
            <span class="meta-tag">📅 Posted <?php echo date('M d, Y', strtotime($job['posted_date'])); ?></span>
        </div>
    </div>

    <!-- Content Wrapper -->
    <div class="content-wrapper">
        <!-- Main Content -->
        <div class="main-content">
            <!-- Job Description -->
            <?php if ($job['description']): ?>
            <div class="content-section">
                <h2 class="section-title">About This Role</h2>
                <div class="job-description">
                    <?php echo nl2br(htmlspecialchars($job['description'])); ?>
                </div>
            </div>
            <?php endif; ?>

            <!-- Job Details -->
            <div class="content-section">
                <h2 class="section-title">Job Details</h2>
                <div class="details-grid">
                    <?php if ($job['category_name']): ?>
                    <div class="detail-item">
                        <p class="detail-label">Category</p>
                        <p class="detail-value">
                            <?php echo $job['category_icon']; ?>
                            <?php echo htmlspecialchars($job['category_name']); ?>
                        </p>
                    </div>
                    <?php endif; ?>

                    <?php if ($job['qualification_name']): ?>
                    <div class="detail-item">
                        <p class="detail-label">Qualification Required</p>
                        <p class="detail-value">
                            🎓 <?php echo htmlspecialchars($job['qualification_name']); ?>
                        </p>
                    </div>
                    <?php endif; ?>

                    <?php if ($job['department_name']): ?>
                    <div class="detail-item">
                        <p class="detail-label">Department</p>
                        <p class="detail-value">
                            🏛️ <?php echo htmlspecialchars($job['department_name']); ?>
                        </p>
                    </div>
                    <?php endif; ?>

                    <?php if ($job['state_name']): ?>
                    <div class="detail-item">
                        <p class="detail-label">State/Region</p>
                        <p class="detail-value">
                            📍 <?php echo htmlspecialchars($job['state_name']); ?>
                        </p>
                    </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <!-- Sidebar -->
        <div class="sidebar">
            <div class="apply-card">
                <h3 class="apply-card-title">Apply for this job</h3>
                
                <?php if ($isExpired): ?>
                    <button class="apply-btn disabled" disabled>
                        Application Closed
                    </button>
                    <div class="deadline-info expired">
                        <strong>Applications Closed</strong>
                        Deadline was <?php echo date('M d, Y', strtotime($job['application_deadline'])); ?>
                    </div>
                <?php else: ?>
                    <a href="<?php echo htmlspecialchars($job['job_link']); ?>" 
                       target="_blank" 
                       rel="noopener noreferrer" 
                       class="apply-btn">
                        Apply Now →
                    </a>
                    
                    <?php if ($job['application_deadline']): ?>
                        <?php
                        $deadline = strtotime($job['application_deadline']);
                        $today = strtotime(date('Y-m-d'));
                        $daysLeft = floor(($deadline - $today) / (60 * 60 * 24));
                        ?>
                        <div class="deadline-info <?php echo $daysLeft <= 3 ? 'urgent' : 'normal'; ?>">
                            <strong>Application Deadline</strong>
                            <?php echo date('M d, Y', $deadline); ?>
                            <?php if ($daysLeft >= 0 && $daysLeft <= 7): ?>
                                <br><?php echo $daysLeft; ?> day<?php echo $daysLeft != 1 ? 's' : ''; ?> remaining
                            <?php endif; ?>
                        </div>
                    <?php endif; ?>
                <?php endif; ?>

                <!-- Share Section -->
                <div class="share-section">
                    <p class="share-title">Share This Job</p>
                    <div class="share-buttons">
                        <a href="https://www.facebook.com/sharer/sharer.php?u=<?php echo urlencode('http://' . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI']); ?>" 
                           target="_blank" 
                           class="share-btn facebook">
                            Facebook
                        </a>
                        <a href="https://twitter.com/intent/tweet?url=<?php echo urlencode('http://' . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI']); ?>&text=<?php echo urlencode($job['title'] . ' at ' . $job['company']); ?>" 
                           target="_blank" 
                           class="share-btn twitter">
                            Twitter
                        </a>
                        <a href="https://www.linkedin.com/sharing/share-offsite/?url=<?php echo urlencode('http://' . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI']); ?>" 
                           target="_blank" 
                           class="share-btn linkedin">
                            LinkedIn
                        </a>
                        <a href="https://wa.me/?text=<?php echo urlencode($job['title'] . ' at ' . $job['company'] . ' - ' . 'http://' . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI']); ?>" 
                           target="_blank" 
                           class="share-btn whatsapp">
                            WhatsApp
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Related Jobs -->
    <?php if (count($relatedJobs) > 0): ?>
    <div class="related-section">
        <h2 class="related-title">Similar Jobs</h2>
        <div class="related-jobs-grid">
            <?php foreach ($relatedJobs as $relJob): ?>
            <a href="job-details.php?id=<?php echo $relJob['id']; ?>" class="related-job-card">
                <h3 class="related-job-title"><?php echo htmlspecialchars($relJob['title']); ?></h3>
                <p class="related-job-company"><?php echo htmlspecialchars($relJob['company']); ?></p>
                <div class="related-job-meta">
                    <?php if ($relJob['mode_name']): ?>
                        <span class="job-badge job-badge-blue">
                            <?php echo $relJob['work_mode_icon']; ?> <?php echo htmlspecialchars($relJob['mode_name']); ?>
                        </span>
                    <?php endif; ?>
                    <?php if ($relJob['type_name']): ?>
                        <span class="job-badge job-badge-teal">
                            <?php echo $relJob['employment_icon']; ?> <?php echo htmlspecialchars($relJob['type_name']); ?>
                        </span>
                    <?php endif; ?>
                    <?php if ($relJob['level_name']): ?>
                        <span class="job-badge job-badge-green">
                            <?php echo $relJob['experience_icon']; ?> <?php echo htmlspecialchars($relJob['level_name']); ?>
                        </span>
                    <?php endif; ?>
                </div>
            </a>
            <?php endforeach; ?>
        </div>
    </div>
    <?php endif; ?>
</div>

<?php include 'includes/footer.php'; ?>
