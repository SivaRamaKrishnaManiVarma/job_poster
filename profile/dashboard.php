<?php
require_once '../includes/session-check.php';
require_once '../includes/master-data-functions.php';
require_once '../includes/job-matching-functions.php';

// Get candidate data
$userId = $candidateId;

// Profile completion
$profileCompletion = getProfileCompletion($userId, $pdo);

// Get education, experience, skills counts
$eduStmt = $pdo->prepare("SELECT COUNT(*) FROM user_education WHERE user_id = ?");
$eduStmt->execute([$userId]);
$educationCount = $eduStmt->fetchColumn();

$expStmt = $pdo->prepare("SELECT COUNT(*) FROM user_experience WHERE user_id = ?");
$expStmt->execute([$userId]);
$experienceCount = $expStmt->fetchColumn();

$skillStmt = $pdo->prepare("SELECT COUNT(*) FROM user_skills WHERE user_id = ?");
$skillStmt->execute([$userId]);
$skillsCount = $skillStmt->fetchColumn();

// Get saved jobs count
$savedJobsCount = getSavedJobsCount($pdo, $userId);

// Get user's job preferences count
$prefCountStmt = $pdo->prepare("SELECT COUNT(*) FROM user_job_preferences WHERE user_id = ?");
$prefCountStmt->execute([$userId]);
$preferencesCount = $prefCountStmt->fetchColumn();

// Check if user has resume
$hasResume = !empty($currentUser['resume_path']);

// Get recommended job counts
$jobCounts = getRecommendedJobCounts($userId, $pdo);

// ============================================================
// FIX: Track shown job IDs to prevent duplicates across tiers
// ============================================================
$shownJobIds = [];

// Helper function to filter out already shown jobs
function filterShownJobs($jobsResult, &$shownIds, $limit = 5) {
    if (empty($jobsResult['jobs'])) return ['jobs' => []];
    
    $filtered = [];
    foreach ($jobsResult['jobs'] as $item) {
        $jobId = $item['job']['id'];
        if (!in_array($jobId, $shownIds)) {
            $filtered[] = $item;
            $shownIds[] = $jobId;
        }
        if (count($filtered) >= $limit) break;
    }
    return ['jobs' => $filtered];
}

// Get jobs with higher limit to account for filtering
$highPriorityRaw  = getRecommendedJobs($userId, $pdo, ['tier' => 'high_priority', 'limit' => 20]);
$bestMatchRaw     = getRecommendedJobs($userId, $pdo, ['tier' => 'best',          'limit' => 20]);
$strongMatchRaw   = getRecommendedJobs($userId, $pdo, ['tier' => 'strong',        'limit' => 20]);
$otherMatchRaw    = getRecommendedJobs($userId, $pdo, ['tier' => 'other',         'limit' => 20]);

// Filter each tier - jobs shown in earlier tiers won't appear again
$highPriorityJobs = filterShownJobs($highPriorityRaw,  $shownJobIds, 5);
$bestMatchJobs    = filterShownJobs($bestMatchRaw,     $shownJobIds, 5);
$strongMatchJobs  = filterShownJobs($strongMatchRaw,   $shownJobIds, 5);
$otherMatchJobs   = filterShownJobs($otherMatchRaw,    $shownJobIds, 5);

// Get category suggestions
$categoryCards = getCategoryJobCounts($userId, $pdo, 8);

// Page title
$pageTitle = 'My Dashboard - Job Portal';
include '../includes/header.php';
?>

<style>
/* Card Hover Effect */
.hover-card {
    transition: all 0.3s ease;
    border: 2px solid #e5e7eb;
}
.hover-card:hover {
    transform: translateY(-3px);
    box-shadow: 0 6px 20px rgba(37, 99, 235, 0.15) !important;
    border-color: #93c5fd;
}

/* Match Badges */
.match-badge {
    display: inline-block;
    padding: 5px 12px;
    border-radius: 20px;
    font-size: 12px;
    font-weight: 600;
    color: white;
}
.match-badge-excellent { background-color: #059669; }
.match-badge-great     { background-color: #2563eb; }
.match-badge-good      { background-color: #f59e0b; }
.match-badge-fair      { background-color: #9ca3af; }

/* Job Cards */
.job-card {
    background: white;
    border: 2px solid #e5e7eb;
    border-radius: 8px;
    padding: 20px;
    height: 100%;
    transition: all 0.2s;
    position: relative;
}
.job-card:hover {
    border-color: #2563eb;
    box-shadow: 0 4px 12px rgba(37, 99, 235, 0.1);
    transform: translateY(-2px);
}
.job-card .match-badge {
    position: absolute;
    top: 12px;
    right: 12px;
}
.job-card h6 a {
    color: #1f2937;
    text-decoration: none;
}
.job-card h6 a:hover {
    color: #2563eb;
}

/* Section Headers */
.section-header {
    border-bottom: 3px solid #2563eb;
    padding-bottom: 12px;
    margin-bottom: 20px;
}
.section-title {
    font-size: 1.25rem;
    font-weight: 700;
    color: #1f2937;
    margin: 0;
}

/* Category Cards */
.category-card {
    background: white;
    border: 2px solid #e5e7eb;
    border-radius: 8px;
    padding: 20px;
    text-align: center;
    cursor: pointer;
    transition: all 0.2s;
    height: 100%;
}
.category-card:hover {
    border-color: #2563eb;
    box-shadow: 0 4px 12px rgba(37, 99, 235, 0.1);
    transform: translateY(-2px);
}
.category-icon {
    font-size: 2rem;
    color: #2563eb;
    margin-bottom: 12px;
}

/* Profile Banner */
.profile-banner {
    background: #dbeafe;
    border: 2px solid #93c5fd;
    border-radius: 8px;
    padding: 16px 20px;
    margin-bottom: 24px;
}

/* Welcome Header */
.welcome-header {
    background: #1e3a8a;
    border: 3px solid #1e40af;
}

/* Empty State */
.empty-state {
    text-align: center;
    padding: 60px 20px;
    color: #6b7280;
}
.empty-state i {
    color: #9ca3af;
}

/* Stats Cards */
.stat-card {
    border: 2px solid #e5e7eb;
    transition: all 0.2s;
}
.stat-card:hover {
    border-color: #93c5fd;
    background: #eff6ff;
}

/* Progress Bar */
.progress-custom {
    height: 8px;
    background-color: #dbeafe;
    border-radius: 4px;
}
.progress-bar-custom {
    background-color: #059669;
    border-radius: 4px;
}
</style>

<div class="container py-4">

    <!-- Welcome Header -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card border-0 shadow-sm welcome-header">
                <div class="card-body p-4 text-white">
                    <div class="row align-items-center">
                        <div class="col-auto">
                            <?php if (!empty($currentUser['profile_photo'])): ?>
                                <img src="<?= url('uploads/profile_photos/' . htmlspecialchars($currentUser['profile_photo'])) ?>"
                                     class="rounded-circle border border-3 border-white"
                                     width="90" height="90"
                                     style="object-fit: cover;"
                                     alt="Profile Photo">
                            <?php else: ?>
                                <div class="rounded-circle bg-white text-primary d-flex align-items-center justify-content-center border border-3"
                                     style="width: 90px; height: 90px; font-size: 2.5rem; font-weight: bold;">
                                    <?= strtoupper(substr($currentUser['full_name'], 0, 1)) ?>
                                </div>
                            <?php endif; ?>
                        </div>
                        <div class="col">
                            <h3 class="mb-1 fw-bold">Welcome back, <?= htmlspecialchars($currentUser['full_name']) ?>!</h3>
                            <p class="mb-2">
                                <i class="fas fa-envelope me-2"></i><?= htmlspecialchars($currentUser['email']) ?>
                                <?php if (!empty($currentUser['location'])): ?>
                                    <span class="ms-3">
                                        <i class="fas fa-map-marker-alt me-2"></i><?= htmlspecialchars($currentUser['location']) ?>
                                    </span>
                                <?php endif; ?>
                            </p>
                        </div>
                        <div class="col-auto text-end">
                            <a href="<?= url('profile/edit-profile.php') ?>" class="btn btn-light btn-sm">
                                <i class="fas fa-edit me-1"></i>Edit Profile
                            </a>
                        </div>
                    </div>

                    <!-- Profile Completion Progress -->
                    <div class="mt-3">
                        <div class="d-flex justify-content-between align-items-center mb-2">
                            <small class="fw-semibold">Profile Completion</small>
                            <small class="fw-bold"><?= $profileCompletion ?>%</small>
                        </div>
                        <div class="progress progress-custom">
                            <div class="progress-bar progress-bar-custom"
                                 role="progressbar"
                                 style="width: <?= $profileCompletion ?>%"
                                 aria-valuenow="<?= $profileCompletion ?>"
                                 aria-valuemin="0"
                                 aria-valuemax="100">
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Quick Stats Cards -->
    <div class="row g-3 mb-4">
        <div class="col-lg-2 col-md-4 col-6">
            <div class="card border-0 shadow-sm h-100 hover-card stat-card">
                <div class="card-body text-center p-3">
                    <div class="fs-2 mb-2" style="color: #2563eb;"><i class="fas fa-graduation-cap"></i></div>
                    <h3 class="mb-0 fw-bold text-dark"><?= $educationCount ?></h3>
                    <small class="text-muted">Education</small>
                </div>
            </div>
        </div>
        <div class="col-lg-2 col-md-4 col-6">
            <div class="card border-0 shadow-sm h-100 hover-card stat-card">
                <div class="card-body text-center p-3">
                    <div class="fs-2 mb-2" style="color: #059669;"><i class="fas fa-briefcase"></i></div>
                    <h3 class="mb-0 fw-bold text-dark"><?= $experienceCount ?></h3>
                    <small class="text-muted">Experience</small>
                </div>
            </div>
        </div>
        <div class="col-lg-2 col-md-4 col-6">
            <div class="card border-0 shadow-sm h-100 hover-card stat-card">
                <div class="card-body text-center p-3">
                    <div class="fs-2 mb-2" style="color: #0891b2;"><i class="fas fa-code"></i></div>
                    <h3 class="mb-0 fw-bold text-dark"><?= $skillsCount ?></h3>
                    <small class="text-muted">Skills</small>
                </div>
            </div>
        </div>
        <div class="col-lg-2 col-md-4 col-6">
            <div class="card border-0 shadow-sm h-100 hover-card stat-card">
                <div class="card-body text-center p-3">
                    <div class="fs-2 mb-2" style="color: #f59e0b;"><i class="fas fa-bookmark"></i></div>
                    <h3 class="mb-0 fw-bold text-dark"><?= $savedJobsCount ?></h3>
                    <small class="text-muted">Saved Jobs</small>
                </div>
            </div>
        </div>
        <div class="col-lg-2 col-md-4 col-6">
            <div class="card border-0 shadow-sm h-100 hover-card stat-card">
                <div class="card-body text-center p-3">
                    <div class="fs-2 mb-2" style="color: #dc2626;"><i class="fas fa-heart"></i></div>
                    <h3 class="mb-0 fw-bold text-dark"><?= $preferencesCount ?></h3>
                    <small class="text-muted">Interests</small>
                </div>
            </div>
        </div>
        <div class="col-lg-2 col-md-4 col-6">
            <div class="card border-0 shadow-sm h-100 hover-card stat-card">
                <div class="card-body text-center p-3">
                    <div class="fs-2 mb-2" style="color: #6b7280;"><i class="fas fa-percentage"></i></div>
                    <h3 class="mb-0 fw-bold text-dark"><?= $profileCompletion ?>%</h3>
                    <small class="text-muted">Complete</small>
                </div>
            </div>
        </div>
    </div>

    <!-- Profile Completion Banner (if < 60%) -->
    <?php if ($profileCompletion < 60): ?>
        <div class="profile-banner" id="profile-banner">
            <div class="d-flex justify-content-between align-items-center flex-wrap gap-3">
                <div class="d-flex align-items-center gap-3">
                    <i class="fas fa-info-circle fs-4" style="color: #2563eb;"></i>
                    <span class="fw-semibold" style="color: #1e40af;">
                        Your profile is only <?= $profileCompletion ?>% complete. Complete it to get better recommendations.
                    </span>
                </div>
                <div class="d-flex gap-2">
                    <a href="<?= url('profile/edit-profile.php') ?>" class="btn btn-sm" style="background: #2563eb; color: white;">
                        Update Profile
                    </a>
                    <button class="btn btn-sm btn-link text-decoration-none" style="color: #6b7280;"
                            onclick="document.getElementById('profile-banner').style.display='none'">
                        Dismiss
                    </button>
                </div>
            </div>
        </div>
    <?php endif; ?>

    <?php
    // Reusable job card renderer to avoid repeating HTML 4 times
    function renderJobCards($jobsArray) {
        foreach ($jobsArray as $item):
            $job      = $item['job'];
            $score    = $item['match_score'];
            $scoreData = formatMatchScore($score);
            $jobSlug  = $job['slug'] ?? 'job-' . $job['id'];
    ?>
            <div class="col-md-6">
                <div class="job-card">
                    <span class="match-badge match-badge-<?= $scoreData['class'] ?>">
                        <?= $score ?>% Match
                    </span>

                    <h6 class="fw-bold mb-2 mt-3">
                        <a href="<?= url('job-details.php?id=' . $job['id']) ?>">
                            <?= htmlspecialchars($job['title']) ?>
                        </a>
                    </h6>

                    <p class="text-muted mb-2 small">
                        <i class="fas fa-building me-1"></i>
                        <?= htmlspecialchars($job['company']) ?>
                    </p>

                    <div class="d-flex flex-wrap gap-2 small text-muted mb-2">
                        <?php if (!empty($job['category_name'])): ?>
                            <span><i class="fas fa-briefcase me-1"></i><?= htmlspecialchars($job['category_name']) ?></span>
                        <?php endif; ?>
                        <?php if (!empty($job['location'])): ?>
                            <span><i class="fas fa-map-marker-alt me-1"></i><?= htmlspecialchars($job['location']) ?></span>
                        <?php endif; ?>
                        <?php if (!empty($job['work_mode_name'])): ?>
                            <span><i class="fas fa-laptop-house me-1"></i><?= htmlspecialchars($job['work_mode_name']) ?></span>
                        <?php endif; ?>
                    </div>

                    <?php if (!empty($job['salary_min'])): ?>
                        <p class="small mb-2 fw-semibold" style="color: #059669;">
                            <i class="fas fa-rupee-sign me-1"></i>
                            <?php if (!empty($job['salary_max'])): ?>
                                <?= number_format($job['salary_min']) ?> - <?= number_format($job['salary_max']) ?>
                            <?php else: ?>
                                <?= number_format($job['salary_min']) ?>+
                            <?php endif; ?>
                        </p>
                    <?php endif; ?>

                    <small class="text-muted">
                        <i class="far fa-clock me-1"></i>
                        Posted <?= date('M d, Y', strtotime($job['posted_date'])) ?>
                    </small>
                </div>
            </div>
    <?php
        endforeach;
    }
    ?>

    <!-- HIGH PRIORITY RECOMMENDATIONS -->
    <?php if (!empty($highPriorityJobs['jobs'])): ?>
        <div class="card border-0 shadow-sm mb-4" style="border: 2px solid #e5e7eb !important;">
            <div class="card-body p-4">
                <div class="section-header">
                    <div class="d-flex justify-content-between align-items-center">
                        <h4 class="section-title">
                            <i class="fas fa-star me-2" style="color: #f59e0b;"></i>High Priority Recommendations
                        </h4>
                        <a href="<?= url('profile/recommended-jobs.php?tier=high_priority') ?>"
                           class="btn btn-sm" style="background: #2563eb; color: white;">
                            View All (<?= $jobCounts['high_priority'] ?>)
                            <i class="fas fa-arrow-right ms-1"></i>
                        </a>
                    </div>
                    <p class="text-muted small mb-0 mt-2">Jobs matching your top interests</p>
                </div>
                <div class="row g-3">
                    <?php renderJobCards($highPriorityJobs['jobs']); ?>
                </div>
            </div>
        </div>
    <?php endif; ?>

    <!-- BEST MATCHES -->
    <?php if (!empty($bestMatchJobs['jobs'])): ?>
        <div class="card border-0 shadow-sm mb-4" style="border: 2px solid #e5e7eb !important;">
            <div class="card-body p-4">
                <div class="section-header">
                    <div class="d-flex justify-content-between align-items-center">
                        <h4 class="section-title">
                            <i class="fas fa-check-circle me-2" style="color: #059669;"></i>Best Matches
                        </h4>
                        <a href="<?= url('profile/recommended-jobs.php?tier=best') ?>"
                           class="btn btn-sm" style="background: #2563eb; color: white;">
                            View All (<?= $jobCounts['best'] ?>)
                            <i class="fas fa-arrow-right ms-1"></i>
                        </a>
                    </div>
                    <p class="text-muted small mb-0 mt-2">Perfect matches for your profile</p>
                </div>
                <div class="row g-3">
                    <?php renderJobCards($bestMatchJobs['jobs']); ?>
                </div>
            </div>
        </div>
    <?php endif; ?>

    <!-- STRONG MATCHES -->
    <?php if (!empty($strongMatchJobs['jobs'])): ?>
        <div class="card border-0 shadow-sm mb-4" style="border: 2px solid #e5e7eb !important;">
            <div class="card-body p-4">
                <div class="section-header">
                    <div class="d-flex justify-content-between align-items-center">
                        <h4 class="section-title">
                            <i class="fas fa-thumbs-up me-2" style="color: #2563eb;"></i>Strong Matches
                        </h4>
                        <a href="<?= url('profile/recommended-jobs.php?tier=strong') ?>"
                           class="btn btn-sm" style="background: #2563eb; color: white;">
                            View All (<?= $jobCounts['strong'] ?>)
                            <i class="fas fa-arrow-right ms-1"></i>
                        </a>
                    </div>
                    <p class="text-muted small mb-0 mt-2">Great opportunities for you</p>
                </div>
                <div class="row g-3">
                    <?php renderJobCards($strongMatchJobs['jobs']); ?>
                </div>
            </div>
        </div>
    <?php endif; ?>

    <!-- OTHER OPPORTUNITIES -->
    <?php if (!empty($otherMatchJobs['jobs'])): ?>
        <div class="card border-0 shadow-sm mb-4" style="border: 2px solid #e5e7eb !important;">
            <div class="card-body p-4">
                <div class="section-header">
                    <div class="d-flex justify-content-between align-items-center">
                        <h4 class="section-title">
                            <i class="fas fa-list me-2" style="color: #6b7280;"></i>Other Opportunities
                        </h4>
                        <a href="<?= url('profile/recommended-jobs.php?tier=other') ?>"
                           class="btn btn-sm" style="background: #2563eb; color: white;">
                            View All (<?= $jobCounts['other'] ?>)
                            <i class="fas fa-arrow-right ms-1"></i>
                        </a>
                    </div>
                    <p class="text-muted small mb-0 mt-2">More jobs you might consider</p>
                </div>
                <div class="row g-3">
                    <?php renderJobCards($otherMatchJobs['jobs']); ?>
                </div>
            </div>
        </div>
    <?php endif; ?>

    <!-- NO RECOMMENDATIONS STATE -->
    <?php if (
        empty($highPriorityJobs['jobs']) &&
        empty($bestMatchJobs['jobs']) &&
        empty($strongMatchJobs['jobs']) &&
        empty($otherMatchJobs['jobs'])
    ): ?>
        <div class="card border-0 shadow-sm mb-4" style="border: 2px solid #e5e7eb !important;">
            <div class="card-body p-5">
                <div class="empty-state">
                    <i class="fas fa-search fa-4x mb-3"></i>
                    <h5 class="mb-3" style="color: #374151;">No personalized recommendations yet</h5>
                    <p class="mb-4">Complete your profile to get smart job recommendations!</p>
                    <div class="d-flex justify-content-center gap-3">
                        <a href="<?= url('profile/edit-profile.php') ?>" class="btn" style="background: #2563eb; color: white;">
                            <i class="fas fa-user-edit me-2"></i>Complete Profile
                        </a>
                        <a href="<?= url('browse-jobs.php') ?>" class="btn btn-outline-primary">
                            <i class="fas fa-search me-2"></i>Browse All Jobs
                        </a>
                    </div>
                </div>
            </div>
        </div>
    <?php endif; ?>

    <!-- CATEGORY EXPLORATION -->
    <?php if (!empty($categoryCards)): ?>
        <div class="card border-0 shadow-sm mb-4" style="border: 2px solid #e5e7eb !important;">
            <div class="card-body p-4">
                <div class="section-header">
                    <h4 class="section-title">
                        <i class="fas fa-th-large me-2" style="color: #0891b2;"></i>Explore Categories You May Like
                    </h4>
                    <p class="text-muted small mb-0 mt-2">Discover jobs in these popular categories</p>
                </div>
                <div class="row g-3">
                    <?php foreach ($categoryCards as $cat): ?>
                        <div class="col-lg-3 col-md-4 col-6">
                            <a href="<?= url('browse-jobs.php?category=' . $cat['id']) ?>" class="text-decoration-none">
                                <div class="category-card">
                                    <div class="category-icon">
                                        <?= htmlspecialchars($cat['icon']) ?>
                                    </div>
                                    <h6 class="mb-1 fw-semibold text-dark">
                                        <?= htmlspecialchars($cat['category_name']) ?>
                                    </h6>
                                    <small class="text-muted"><?= $cat['job_count'] ?> jobs</small>
                                    <?php if ($cat['is_user_interest']): ?>
                                        <div class="mt-2">
                                            <span class="badge rounded-pill" style="background: #2563eb; color: white;">
                                                Your Interest
                                            </span>
                                        </div>
                                    <?php endif; ?>
                                </div>
                            </a>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>
    <?php endif; ?>

</div>

<?php include '../includes/footer.php'; ?>
