<?php
require_once 'includes/config.php';
require_once 'includes/auth-functions.php';
require_once 'includes/master-data-functions.php';

// Check if user is logged in
$isLoggedIn = isCandidateLoggedIn();
$userId = $isLoggedIn && isset($_SESSION['candidate_id']) ? $_SESSION['candidate_id'] : null;

// Initialize defaults
$recommendedJobs = [];
$userPreferences = [];

// ✅ FIXED: Only ONE block, no duplicate
if ($isLoggedIn && $userId !== null) {
    require_once 'includes/job-matching-functions.php';

    try {
        $recommendations  = getRecommendedJobs($userId, $pdo, ['tier' => 'all', 'limit' => 6]);
        $recommendedJobs  = $recommendations['jobs'] ?? [];

        $userPrefsStmt = $pdo->prepare("
            SELECT ujp.*, mc.category_name, mc.icon 
            FROM user_job_preferences ujp
            JOIN master_job_categories mc ON ujp.job_category_id = mc.id
            WHERE ujp.user_id = ?
            ORDER BY ujp.priority DESC
            LIMIT 3
        ");
        $userPrefsStmt->execute([$userId]);
        $userPreferences = $userPrefsStmt->fetchAll(PDO::FETCH_ASSOC);

    } catch (Exception $e) {
        error_log("Error loading personalized content: " . $e->getMessage());
        $recommendedJobs = [];
        $userPreferences = [];
    }
}

// Get featured jobs (latest 8)
$featuredJobsStmt = $pdo->query("
    SELECT j.*, 
           mc.category_name, mc.icon as category_icon,
           mw.mode_name, mw.icon as work_mode_icon
    FROM jobs j
    LEFT JOIN master_job_categories mc ON j.job_category_id = mc.id
    LEFT JOIN master_work_modes mw ON j.work_mode_id = mw.id
    WHERE j.is_active = 1 
    AND (j.application_deadline IS NULL OR j.application_deadline >= CURDATE())
    ORDER BY j.posted_date DESC
    LIMIT 8
");
$featuredJobs = $featuredJobsStmt->fetchAll(PDO::FETCH_ASSOC);

// Get top categories with job counts
$topCategoriesStmt = $pdo->query("
    SELECT mc.id, mc.category_name, mc.icon, mc.category_slug,
           COUNT(j.id) as job_count
    FROM master_job_categories mc
    LEFT JOIN jobs j ON mc.id = j.job_category_id 
        AND j.is_active = 1 
        AND (j.application_deadline IS NULL OR j.application_deadline >= CURDATE())
    WHERE mc.is_active = 1
    GROUP BY mc.id
    HAVING job_count > 0
    ORDER BY job_count DESC
    LIMIT 12
");
$topCategories = $topCategoriesStmt->fetchAll(PDO::FETCH_ASSOC);

// Get stats
$totalJobs       = $pdo->query("SELECT COUNT(*) FROM jobs WHERE is_active = 1 AND (application_deadline IS NULL OR application_deadline >= CURDATE())")->fetchColumn();
$totalCompanies  = $pdo->query("SELECT COUNT(DISTINCT company) FROM jobs WHERE is_active = 1")->fetchColumn();
$totalCategories = count($topCategories);

// Get top companies
$topCompaniesStmt = $pdo->query("
    SELECT company, COUNT(*) as job_count
    FROM jobs
    WHERE is_active = 1
    GROUP BY company
    ORDER BY job_count DESC
    LIMIT 10
");
$topCompanies = $topCompaniesStmt->fetchAll(PDO::FETCH_ASSOC);

$pageTitle = 'Job Portal - Find Your Dream Job';
include 'includes/header.php';
?>

<!-- Hero Section -->
<div class="hero-banner">
    <div class="container">
        <div class="hero-content">
            <?php if ($isLoggedIn): ?>
                <h1 class="hero-title">Welcome Back! 👋</h1>
                <p class="hero-subtitle">We found <?= number_format($totalJobs) ?>+ jobs matching your profile</p>
            <?php else: ?>
                <h1 class="hero-title">Find Your Dream Job Today</h1>
                <p class="hero-subtitle">Discover <?= number_format($totalJobs) ?>+ opportunities from top companies</p>
            <?php endif; ?>

            <div class="search-box-hero">
                <form action="<?= url('browse-jobs.php') ?>" method="GET">
                    <input type="text" name="search" placeholder="Search by job title, skills, or company..." required>
                    <button type="submit">Search Jobs</button>
                </form>
            </div>

            <?php if (!$isLoggedIn): ?>
                <div class="mt-4">
                    <p class="mb-0 text-muted">
                        New here?
                        <a href="<?= url('auth/register.php') ?>" class="fw-bold text-primary">
                            Create account
                        </a>
                        to get personalized job recommendations
                    </p>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>


<!-- Stats Bar -->
<div class="stats-bar">
    <div class="container">
        <div class="row">
            <div class="col-md-4">
                <div class="stat-item">
                    <div class="stat-number"><?= number_format($totalJobs) ?>+</div>
                    <div class="stat-label">Active Jobs</div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="stat-item">
                    <div class="stat-number"><?= $totalCompanies ?>+</div>
                    <div class="stat-label">Companies Hiring</div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="stat-item">
                    <div class="stat-number"><?= $totalCategories ?>+</div>
                    <div class="stat-label">Job Categories</div>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="container">

    <!-- Personalized Recommendations (Logged In Users Only) -->
    <?php if ($isLoggedIn && !empty($recommendedJobs)): ?>
        <div class="section">
            <div class="section-header">
                <h2 class="section-title">Recommended For You</h2>
                <p class="section-subtitle">Jobs matching your profile and interests</p>
            </div>

            <div class="row g-3">
                <?php foreach ($recommendedJobs as $item):
                    $job   = $item['job'];
                    $score = $item['match_score'];
                ?>
                    <div class="col-md-6 col-lg-4">
                        <!-- ✅ FIXED: Entire card is the link, no nested <a> tags -->
                        <a href="<?= url('job-details.php?id=' . $job['id']) ?>" class="job-card-compact">
                            <span class="match-badge-small"><?= $score ?>% Match</span>

                            <div class="job-company">
                                <i class="fas fa-building"></i> <?= htmlspecialchars($job['company']) ?>
                            </div>

                            <div class="job-title"><?= htmlspecialchars($job['title']) ?></div>

                            <div class="job-meta">
                                <?php if (!empty($job['location'])): ?>
                                    <span><i class="fas fa-map-marker-alt"></i> <?= htmlspecialchars($job['location']) ?></span>
                                <?php endif; ?>
                                <?php if (!empty($job['salary_min'])): ?>
                                    <span><i class="fas fa-rupee-sign"></i> <?= number_format($job['salary_min']) ?>+</span>
                                <?php endif; ?>
                            </div>

                            <div class="job-tags">
                                <?php if (!empty($job['work_mode_name'])): ?>
                                    <span class="job-tag"><?= htmlspecialchars($job['work_mode_name']) ?></span>
                                <?php endif; ?>
                                <?php if (!empty($job['category_name'])): ?>
                                    <span class="job-tag"><?= htmlspecialchars($job['category_name']) ?></span>
                                <?php endif; ?>
                            </div>
                        </a>
                    </div>
                <?php endforeach; ?>
            </div>

            <div class="text-center mt-4">
                <a href="<?= url('profile/recommended-jobs.php') ?>" class="btn btn-primary btn-lg">
                    View All Recommendations <i class="fas fa-arrow-right ms-2"></i>
                </a>
            </div>
        </div>
    <?php endif; ?>

    <!-- Browse by Category -->
    <div class="section">
        <div class="section-header">
            <h2 class="section-title">Browse by Category</h2>
            <p class="section-subtitle">Explore jobs in your field of interest</p>
        </div>

        <div class="category-grid">
            <?php foreach ($topCategories as $category): ?>
                <a href="<?= url('browse-jobs.php?category=' . $category['id']) ?>" class="category-card">
                    <div class="category-name"><?= htmlspecialchars($category['category_name']) ?></div>
                    <div class="category-count"><?= $category['job_count'] ?> jobs</div>
                </a>
            <?php endforeach; ?>
        </div>

        <div class="text-center mt-4">
            <a href="<?= url('browse-jobs.php') ?>" class="btn btn-outline-primary">
                View All Categories <i class="fas fa-arrow-right ms-2"></i>
            </a>
        </div>
    </div>

    <!-- Latest Jobs -->
    <div class="section">
        <div class="section-header">
            <h2 class="section-title">Latest Job Openings</h2>
            <p class="section-subtitle">Fresh opportunities posted recently</p>
        </div>

        <div class="row g-3">
            <?php foreach ($featuredJobs as $job): ?>
                <div class="col-md-6 col-lg-3">
                    <!-- ✅ FIXED: Entire card is the link -->
                    <a href="<?= url('job-details.php?id=' . $job['id']) ?>" class="job-card-compact">
                        <div class="job-company">
                            <i class="fas fa-building"></i> <?= htmlspecialchars($job['company']) ?>
                        </div>

                        <div class="job-title"><?= htmlspecialchars($job['title']) ?></div>

                        <div class="job-meta">
                            <?php if (!empty($job['location'])): ?>
                                <span><i class="fas fa-map-marker-alt"></i> <?= htmlspecialchars($job['location']) ?></span>
                            <?php endif; ?>
                        </div>

                        <div class="job-tags">
                            <?php if (!empty($job['mode_name'])): ?>
                                <span class="job-tag">
                                    <i class="fas fa-laptop-house me-1"></i><?= htmlspecialchars($job['mode_name']) ?>
                                </span>
                            <?php endif; ?>
                        </div>

                        <div class="mt-3">
                            <small class="text-muted">
                                <i class="far fa-clock"></i>
                                <?= date('M d', strtotime($job['posted_date'])) ?>
                            </small>
                        </div>
                    </a>
                </div>
            <?php endforeach; ?>
        </div>

        <div class="text-center mt-4">
            <a href="<?= url('browse-jobs.php') ?>" class="btn btn-primary">
                Browse All Jobs <i class="fas fa-arrow-right ms-2"></i>
            </a>
        </div>
    </div>

    <!-- CTA Section (Guests Only) -->
    <?php if (!$isLoggedIn): ?>
        <div class="cta-section">
            <h2 class="cta-title">Ready to Find Your Dream Job?</h2>
            <p class="cta-subtitle">Join thousands of job seekers who found their perfect match</p>
            <div class="cta-buttons">
                <a href="<?= url('auth/register.php') ?>" class="btn-cta btn-cta-primary">
                    <i class="fas fa-user-plus me-2"></i>Create Free Account
                </a>
                <a href="<?= url('browse-jobs.php') ?>" class="btn-cta btn-cta-secondary">
                    <i class="fas fa-briefcase me-2"></i>Browse Jobs
                </a>
            </div>
        </div>
    <?php endif; ?>

    <!-- Top Companies -->
    <div class="section">
        <div class="section-header">
            <h2 class="section-title">Top Companies Hiring</h2>
            <p class="section-subtitle">Explore opportunities with leading employers</p>
        </div>

        <div class="company-list">
            <?php foreach ($topCompanies as $company): ?>
                <!-- ✅ FIXED: Companies link to browse-jobs filtered by company -->
                <a href="<?= url('browse-jobs.php?search=' . urlencode($company['company'])) ?>" class="company-item">
                    <i class="fas fa-building"></i>
                    <?= htmlspecialchars($company['company']) ?>
                    <span class="company-badge"><?= $company['job_count'] ?> jobs</span>
                </a>
            <?php endforeach; ?>
        </div>
    </div>

</div>

<?php include 'includes/footer.php'; ?>
