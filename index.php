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

<style>
    :root {
        --dark-blue: #1e40af;
        --light-blue: #3b82f6;
        --very-light-blue: #dbeafe;
        --text-dark: #1f2937;
        --text-gray: #6b7280;
        --border-gray: #e5e7eb;
        --white: #ffffff;
    }

    .hero-banner {
        background: var(--dark-blue);
        color: white;
        padding: 80px 0 60px;
        position: relative;
        overflow: hidden;
    }
    .hero-banner::before {
        content: '';
        position: absolute;
        top: 0; right: 0; bottom: 0; left: 0;
        background: url('data:image/svg+xml,<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 1200 120"><path d="M0,0V46.29c47.79,22.2,103.59,32.17,158,28,70.36-5.37,136.33-33.31,206.8-37.5C438.64,32.43,512.34,53.67,583,72.05c69.27,18,138.3,24.88,209.4,13.08,36.15-6,69.85-17.84,104.45-29.34C989.49,25,1113-14.29,1200,52.47V0Z" opacity=".15" fill="%23ffffff"/></svg>') no-repeat bottom;
        background-size: cover;
        opacity: 0.1;
    }
    .hero-content { position: relative; z-index: 1; }
    .hero-title { font-size: 3rem; font-weight: 800; margin-bottom: 16px; }
    .hero-subtitle { font-size: 1.25rem; opacity: 0.95; margin-bottom: 32px; }
    .search-box-hero {
        background: white;
        border-radius: 50px;
        padding: 8px;
        box-shadow: 0 10px 40px rgba(0,0,0,0.15);
        max-width: 700px;
        margin: 0 auto;
    }
    .search-box-hero form { display: flex; gap: 8px; }
    .search-box-hero input {
        flex: 1; border: none;
        padding: 12px 24px; font-size: 16px; outline: none;
        border-radius: 50px;
    }
    .search-box-hero button {
        background: var(--light-blue); color: white;
        border: none; padding: 12px 32px;
        border-radius: 50px; font-weight: 600;
        cursor: pointer; transition: all 0.2s;
        white-space: nowrap;
    }
    .search-box-hero button:hover { background: var(--dark-blue); }

    .stats-bar { background: white; padding: 32px 0; box-shadow: 0 2px 8px rgba(0,0,0,0.05); }
    .stat-item { text-align: center; }
    .stat-number { font-size: 2.5rem; font-weight: 800; color: var(--light-blue); margin-bottom: 8px; }
    .stat-label { color: var(--text-gray); font-size: 14px; }

    .section { padding: 60px 0; }
    .section-header { text-align: center; margin-bottom: 48px; }
    .section-title { font-size: 2rem; font-weight: 700; color: var(--text-dark); margin-bottom: 12px; }
    .section-subtitle { color: var(--text-gray); font-size: 1.1rem; }

    .category-grid {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(160px, 1fr));
        gap: 20px;
    }
    .category-card {
        background: white; border: 2px solid var(--border-gray);
        border-radius: 12px; padding: 24px 16px;
        text-align: center; text-decoration: none;
        transition: all 0.3s; display: block;
    }
    .category-card:hover {
        border-color: var(--light-blue);
        transform: translateY(-4px);
        box-shadow: 0 8px 24px rgba(59, 130, 246, 0.15);
    }
    .category-icon { font-size: 2.5rem; margin-bottom: 12px; display: block; }
    .category-name { font-weight: 600; color: var(--text-dark); font-size: 14px; margin-bottom: 8px; }
    .category-count { color: #9ca3af; font-size: 13px; }

    /* ✅ FIXED: Job cards are now fully clickable */
    .job-card-compact {
        background: white; border: 1px solid var(--border-gray);
        border-radius: 12px; padding: 20px;
        transition: all 0.3s; height: 100%;
        display: block; text-decoration: none;
        color: inherit;
    }
    .job-card-compact:hover {
        border-color: var(--light-blue);
        box-shadow: 0 4px 16px rgba(0,0,0,0.1);
        transform: translateY(-2px);
        color: inherit;
        text-decoration: none;
    }
    .job-company { color: var(--light-blue); font-weight: 600; font-size: 14px; margin-bottom: 8px; }
    .job-title { font-weight: 700; color: var(--text-dark); font-size: 16px; margin-bottom: 12px; line-height: 1.4; }
    .job-meta { display: flex; flex-wrap: wrap; gap: 12px; font-size: 13px; color: var(--text-gray); margin-bottom: 16px; }
    .job-tags { display: flex; flex-wrap: wrap; gap: 6px; }
    .job-tag { background: var(--very-light-blue); padding: 4px 10px; border-radius: 12px; font-size: 12px; color: var(--dark-blue); }

    .match-badge-small {
        display: inline-block;
        background: #10b981; color: white;
        padding: 4px 10px; border-radius: 12px;
        font-size: 12px; font-weight: 600;
        margin-bottom: 10px;
    }

    .cta-section {
        background: var(--dark-blue); color: white;
        padding: 60px 0; text-align: center;
        border-radius: 16px; margin: 60px 0;
    }
    .cta-title { font-size: 2rem; font-weight: 700; margin-bottom: 16px; }
    .cta-subtitle { font-size: 1.1rem; opacity: 0.9; margin-bottom: 32px; }
    .cta-buttons { display: flex; gap: 16px; justify-content: center; flex-wrap: wrap; }
    .btn-cta { padding: 14px 32px; border-radius: 50px; font-weight: 600; text-decoration: none; transition: all 0.3s; }
    .btn-cta-primary { background: white; color: var(--dark-blue); }
    .btn-cta-primary:hover { background: var(--very-light-blue); color: var(--dark-blue); transform: scale(1.05); }
    .btn-cta-secondary { background: transparent; color: white; border: 2px solid white; }
    .btn-cta-secondary:hover { background: white; color: var(--dark-blue); }

    .company-list { display: flex; flex-wrap: wrap; gap: 16px; justify-content: center; }
    .company-item {
        background: #f9fafb; padding: 12px 24px;
        border-radius: 8px; font-weight: 500;
        color: var(--text-dark); display: flex;
        align-items: center; gap: 8px;
        text-decoration: none; transition: all 0.2s;
        border: 1px solid var(--border-gray);
    }
    .company-item:hover {
        border-color: var(--light-blue);
        background: var(--very-light-blue);
        color: var(--dark-blue);
    }
    .company-badge { background: var(--light-blue); color: white; padding: 2px 8px; border-radius: 12px; font-size: 11px; }

    @media (max-width: 768px) {
        .hero-title { font-size: 2rem; }
        .hero-subtitle { font-size: 1rem; }
        .cta-buttons { flex-direction: column; }
    }
</style>

<!-- Hero Section -->
<div class="hero-banner">
    <div class="container">
        <div class="hero-content text-center">
            <?php if ($isLoggedIn): ?>
                <h1 class="hero-title">Welcome Back! 👋</h1>
                <p class="hero-subtitle">We found <?= number_format($totalJobs) ?>+ jobs matching your profile</p>
            <?php else: ?>
                <h1 class="hero-title">Find Your Dream Job Today</h1>
                <p class="hero-subtitle">Discover <?= number_format($totalJobs) ?>+ opportunities from top companies</p>
            <?php endif; ?>

            <div class="search-box-hero">
                <form action="<?= url('browse-jobs.php') ?>" method="GET">
                    <input type="text" name="search" placeholder="🔍 Search by job title, skills, or company..." required>
                    <button type="submit">Search Jobs</button>
                </form>
            </div>

            <?php if (!$isLoggedIn): ?>
                <div class="mt-4">
                    <p class="mb-0" style="opacity: 0.9;">
                        New here?
                        <a href="<?= url('auth/register.php') ?>" style="color: white; font-weight: 600; text-decoration: underline;">
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
                <h2 class="section-title">🎯 Recommended For You</h2>
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
            <h2 class="section-title">🔍 Browse by Category</h2>
            <p class="section-subtitle">Explore jobs in your field of interest</p>
        </div>

        <div class="category-grid">
            <?php foreach ($topCategories as $category): ?>
                <a href="<?= url('browse-jobs.php?category=' . $category['id']) ?>" class="category-card">
                    <span class="category-icon"><?= $category['icon'] ?></span>
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
    <div class="section" style="background: #f9fafb; margin: 0 -15px; padding: 60px 15px;">
        <div class="section-header">
            <h2 class="section-title">🆕 Latest Job Openings</h2>
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
                                    <?= $job['work_mode_icon'] ?> <?= htmlspecialchars($job['mode_name']) ?>
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
            <h2 class="section-title">🏢 Top Companies Hiring</h2>
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
