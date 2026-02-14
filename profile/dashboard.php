<?php
require_once '../includes/session-check.php';
require_once '../includes/master-data-functions.php';

// Get candidate statistics
$userId = $candidateId;

// Profile completion
$profileCompletion = getCandidateProfileCompletion($pdo, $userId);

// Get education records
$eduStmt = $pdo->prepare("SELECT * FROM user_education WHERE user_id = ? ORDER BY end_year DESC");
$eduStmt->execute([$userId]);
$educations = $eduStmt->fetchAll(PDO::FETCH_ASSOC);

// Get experience records
$expStmt = $pdo->prepare("SELECT * FROM user_experience WHERE user_id = ? ORDER BY is_current DESC, end_date DESC");
$expStmt->execute([$userId]);
$experiences = $expStmt->fetchAll(PDO::FETCH_ASSOC);

// Get skills
$skillStmt = $pdo->prepare("SELECT * FROM user_skills WHERE user_id = ? ORDER BY proficiency_level DESC");
$skillStmt->execute([$userId]);
$skills = $skillStmt->fetchAll(PDO::FETCH_ASSOC);

// Get saved jobs count
$savedJobsCount = getSavedJobsCount($pdo, $userId);

// Get user's job preferences count
$prefCountStmt = $pdo->prepare("SELECT COUNT(*) as cnt FROM user_job_preferences WHERE user_id = ?");
$prefCountStmt->execute([$userId]);
$preferencesCount = $prefCountStmt->fetch()['cnt'];

// Get recent saved jobs (last 3)
$recentSavedStmt = $pdo->prepare("
    SELECT j.*, sj.saved_at,
           c.category_name, c.icon as category_icon,
           w.mode_name, w.icon as work_mode_icon
    FROM saved_jobs sj
    JOIN jobs j ON sj.job_id = j.id
    LEFT JOIN master_job_categories c ON j.job_category_id = c.id
    LEFT JOIN master_work_modes w ON j.work_mode_id = w.id
    WHERE sj.user_id = ?
    ORDER BY sj.saved_at DESC
    LIMIT 3
");
$recentSavedStmt->execute([$userId]);
$recentSavedJobs = $recentSavedStmt->fetchAll(PDO::FETCH_ASSOC);

// Page title
$pageTitle = 'My Dashboard - Job Portal';
include '../includes/header.php';
?>

<div class="container py-4">
    <!-- Welcome Header -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card border-0 shadow-sm" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);">
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
                            <h3 class="mb-1 fw-bold">Welcome back, <?= htmlspecialchars($currentUser['full_name']) ?>! 👋</h3>
                            <p class="mb-2 opacity-90">
                                <i class="fas fa-envelope me-2"></i><?= htmlspecialchars($currentUser['email']) ?>
                                <?php if (!empty($currentUser['location'])): ?>
                                    <span class="ms-3"><i class="fas fa-map-marker-alt me-2"></i><?= htmlspecialchars($currentUser['location']) ?></span>
                                <?php endif; ?>
                            </p>
                            <?php if (!empty($currentUser['phone'])): ?>
                                <p class="mb-0 opacity-90">
                                    <i class="fas fa-phone me-2"></i><?= htmlspecialchars($currentUser['phone']) ?>
                                </p>
                            <?php endif; ?>
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
                        <div class="progress" style="height: 8px; background-color: rgba(255,255,255,0.3);">
                            <div class="progress-bar bg-success" 
                                 role="progressbar" 
                                 style="width: <?= $profileCompletion ?>%"
                                 aria-valuenow="<?= $profileCompletion ?>" 
                                 aria-valuemin="0" 
                                 aria-valuemax="100">
                            </div>
                        </div>
                        <?php if ($profileCompletion < 100): ?>
                            <small class="opacity-90 d-block mt-1">
                                <i class="fas fa-info-circle me-1"></i>
                                <a href="<?= url('profile/edit-profile.php') ?>" class="text-white text-decoration-underline">
                                    Complete your profile
                                </a> to get better job recommendations
                            </small>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Quick Stats Cards -->
    <div class="row g-3 mb-4">
        <div class="col-lg-2 col-md-4 col-6">
            <div class="card border-0 shadow-sm h-100 hover-card">
                <div class="card-body text-center p-3">
                    <div class="fs-2 text-primary mb-2">
                        <i class="fas fa-graduation-cap"></i>
                    </div>
                    <h3 class="mb-0 fw-bold"><?= count($educations) ?></h3>
                    <small class="text-muted">Education</small>
                </div>
            </div>
        </div>
        <div class="col-lg-2 col-md-4 col-6">
            <div class="card border-0 shadow-sm h-100 hover-card">
                <div class="card-body text-center p-3">
                    <div class="fs-2 text-success mb-2">
                        <i class="fas fa-briefcase"></i>
                    </div>
                    <h3 class="mb-0 fw-bold"><?= count($experiences) ?></h3>
                    <small class="text-muted">Experience</small>
                </div>
            </div>
        </div>
        <div class="col-lg-2 col-md-4 col-6">
            <div class="card border-0 shadow-sm h-100 hover-card">
                <div class="card-body text-center p-3">
                    <div class="fs-2 text-info mb-2">
                        <i class="fas fa-code"></i>
                    </div>
                    <h3 class="mb-0 fw-bold"><?= count($skills) ?></h3>
                    <small class="text-muted">Skills</small>
                </div>
            </div>
        </div>
        <div class="col-lg-2 col-md-4 col-6">
            <div class="card border-0 shadow-sm h-100 hover-card">
                <div class="card-body text-center p-3">
                    <div class="fs-2 text-warning mb-2">
                        <i class="fas fa-bookmark"></i>
                    </div>
                    <h3 class="mb-0 fw-bold"><?= $savedJobsCount ?></h3>
                    <small class="text-muted">Saved Jobs</small>
                </div>
            </div>
        </div>
        <div class="col-lg-2 col-md-4 col-6">
            <div class="card border-0 shadow-sm h-100 hover-card">
                <div class="card-body text-center p-3">
                    <div class="fs-2 text-danger mb-2">
                        <i class="fas fa-heart"></i>
                    </div>
                    <h3 class="mb-0 fw-bold"><?= $preferencesCount ?></h3>
                    <small class="text-muted">Interests</small>
                </div>
            </div>
        </div>
        <div class="col-lg-2 col-md-4 col-6">
            <div class="card border-0 shadow-sm h-100 hover-card">
                <div class="card-body text-center p-3">
                    <div class="fs-2 text-secondary mb-2">
                        <i class="fas fa-percentage"></i>
                    </div>
                    <h3 class="mb-0 fw-bold"><?= $profileCompletion ?>%</h3>
                    <small class="text-muted">Complete</small>
                </div>
            </div>
        </div>
    </div>

    <!-- Recommended Jobs Section (if user has preferences) -->
    <?php
    // Get user's job preferences
    $userPrefsStmt = $pdo->prepare("
        SELECT ujp.*, mc.category_name, mc.icon 
        FROM user_job_preferences ujp
        JOIN master_job_categories mc ON ujp.job_category_id = mc.id
        WHERE ujp.user_id = ?
        ORDER BY ujp.priority DESC
    ");
    $userPrefsStmt->execute([$userId]);
    $userJobPreferences = $userPrefsStmt->fetchAll(PDO::FETCH_ASSOC);

    if (!empty($userJobPreferences)):
        // Get matching jobs
        $categoryIds = array_column($userJobPreferences, 'job_category_id');
        $placeholders = str_repeat('?,', count($categoryIds) - 1) . '?';
        
        $recommendedJobsStmt = $pdo->prepare("
            SELECT j.*, 
                   c.category_name, c.icon as category_icon,
                   w.mode_name, w.icon as work_mode_icon
            FROM jobs j
            LEFT JOIN master_job_categories c ON j.job_category_id = c.id
            LEFT JOIN master_work_modes w ON j.work_mode_id = w.id
            WHERE j.is_active = 1 
            AND j.job_category_id IN ($placeholders)
            AND (j.application_deadline IS NULL OR j.application_deadline >= CURDATE())
            ORDER BY j.posted_date DESC
            LIMIT 6
        ");
        $recommendedJobsStmt->execute($categoryIds);
        $recommendedJobs = $recommendedJobsStmt->fetchAll(PDO::FETCH_ASSOC);
        
        if (!empty($recommendedJobs)):
    ?>
        <div class="row mb-4">
            <div class="col-12">
                <div class="card border-0 shadow-sm">
                    <div class="card-header bg-white border-0 py-3">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <h5 class="mb-1 fw-bold">
                                    <i class="fas fa-star text-warning me-2"></i>Recommended Jobs For You
                                </h5>
                                <p class="text-muted small mb-0">Based on your interests: 
                                    <?php 
                                    $categoryNames = array_slice(array_column($userJobPreferences, 'category_name'), 0, 3);
                                    echo implode(', ', $categoryNames);
                                    if (count($userJobPreferences) > 3) echo ' +' . (count($userJobPreferences) - 3) . ' more';
                                    ?>
                                </p>
                            </div>
                            <a href="<?= url('profile/recommended-jobs.php') ?>" class="btn btn-sm btn-primary">
                                View All <i class="fas fa-arrow-right ms-1"></i>
                            </a>
                        </div>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <?php foreach ($recommendedJobs as $job): ?>
                                <div class="col-md-6 mb-3">
                                    <div class="card border h-100 hover-card">
                                        <div class="card-body">
                                            <h6 class="fw-bold mb-2">
                                                <a href="<?= url('jobs/' . htmlspecialchars($job['slug'])) ?>" 
                                                   class="text-decoration-none text-dark stretched-link">
                                                    <?= htmlspecialchars($job['title']) ?>
                                                </a>
                                            </h6>
                                            <p class="text-primary small mb-2">
                                                <i class="fas fa-building me-1"></i><?= htmlspecialchars($job['company']) ?>
                                            </p>
                                            <div class="d-flex flex-wrap gap-2 small text-muted mb-2">
                                                <?php if (!empty($job['category_name'])): ?>
                                                    <span>
                                                        <i class="<?= htmlspecialchars($job['category_icon']) ?> me-1"></i>
                                                        <?= htmlspecialchars($job['category_name']) ?>
                                                    </span>
                                                <?php endif; ?>
                                                <?php if (!empty($job['location'])): ?>
                                                    <span>
                                                        <i class="fas fa-map-marker-alt me-1"></i>
                                                        <?= htmlspecialchars($job['location']) ?>
                                                    </span>
                                                <?php endif; ?>
                                            </div>
                                            <?php if (!empty($job['min_salary']) || !empty($job['max_salary'])): ?>
                                                <p class="text-success small mb-2">
                                                    <i class="fas fa-rupee-sign me-1"></i>
                                                    <?php if (!empty($job['min_salary']) && !empty($job['max_salary'])): ?>
                                                        <?= number_format($job['min_salary']) ?> - <?= number_format($job['max_salary']) ?>
                                                    <?php elseif (!empty($job['min_salary'])): ?>
                                                        <?= number_format($job['min_salary']) ?>+
                                                    <?php endif; ?>
                                                </p>
                                            <?php endif; ?>
                                            <small class="text-muted">
                                                <i class="fas fa-clock me-1"></i>Posted <?= date('M d', strtotime($job['posted_date'])) ?>
                                            </small>
                                        </div>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    <?php 
        else:
    ?>
        <!-- No matching jobs, show message -->
        <div class="alert alert-info border-0 shadow-sm mb-4">
            <div class="d-flex align-items-center">
                <i class="fas fa-info-circle fa-2x me-3"></i>
                <div class="flex-grow-1">
                    <h6 class="mb-1 fw-bold">No matching jobs right now</h6>
                    <p class="mb-0 small">We couldn't find jobs matching your interests. Try 
                        <a href="<?= url('profile/edit-profile.php#preferences') ?>">updating your preferences</a> or 
                        <a href="<?= url() ?>">browse all jobs</a>.
                    </p>
                </div>
            </div>
        </div>
    <?php
        endif;
    else:
    ?>
        <!-- No preferences set -->
        <div class="alert alert-warning border-0 shadow-sm mb-4">
            <div class="d-flex align-items-center">
                <i class="fas fa-heart fa-2x me-3"></i>
                <div class="flex-grow-1">
                    <h6 class="mb-1 fw-bold">Set Your Job Interests</h6>
                    <p class="mb-2 small">Tell us what type of jobs you're interested in to get personalized recommendations!</p>
                    <a href="<?= url('profile/edit-profile.php#preferences') ?>" class="btn btn-sm btn-warning">
                        <i class="fas fa-heart me-1"></i>Add Job Interests
                    </a>
                </div>
            </div>
        </div>
    <?php
    endif;
    ?>

    <div class="row">
        <!-- Left Column - Profile Sections -->
        <div class="col-lg-8">
            <!-- Resume Section -->
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-header bg-white border-0 d-flex justify-content-between align-items-center py-3">
                    <h5 class="mb-0 fw-bold"><i class="fas fa-file-pdf text-danger me-2"></i>Resume</h5>
                    <a href="<?= url('profile/edit-profile.php#resume') ?>" class="btn btn-sm btn-primary">
                        <i class="fas fa-upload me-1"></i><?= !empty($currentUser['resume_path']) ? 'Update' : 'Upload' ?>
                    </a>
                </div>
                <div class="card-body">
                    <?php if (!empty($currentUser['resume_path'])): ?>
                        <div class="d-flex align-items-center justify-content-between p-3 bg-light rounded">
                            <div class="d-flex align-items-center">
                                <i class="fas fa-file-pdf fa-2x text-danger me-3"></i>
                                <div>
                                    <h6 class="mb-0"><?= basename($currentUser['resume_path']) ?></h6>
                                    <small class="text-muted">
                                        Uploaded on <?= date('M d, Y', strtotime($currentUser['updated_at'])) ?>
                                    </small>
                                </div>
                            </div>
                            <div>
                                <a href="<?= url('uploads/resumes/' . htmlspecialchars($currentUser['resume_path'])) ?>" 
                                   target="_blank" 
                                   class="btn btn-sm btn-outline-primary me-2">
                                    <i class="fas fa-eye me-1"></i>View
                                </a>
                                <a href="<?= url('uploads/resumes/' . htmlspecialchars($currentUser['resume_path'])) ?>" 
                                   download 
                                   class="btn btn-sm btn-outline-secondary">
                                    <i class="fas fa-download me-1"></i>Download
                                </a>
                            </div>
                        </div>
                    <?php else: ?>
                        <div class="text-center py-4">
                            <i class="fas fa-file-upload fa-3x text-muted mb-3"></i>
                            <p class="text-muted mb-3">No resume uploaded yet</p>
                            <a href="<?= url('profile/edit-profile.php#resume') ?>" class="btn btn-primary">
                                <i class="fas fa-upload me-2"></i>Upload Your Resume
                            </a>
                        </div>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Education Section -->
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-header bg-white border-0 d-flex justify-content-between align-items-center py-3">
                    <h5 class="mb-0 fw-bold"><i class="fas fa-graduation-cap text-primary me-2"></i>Education</h5>
                    <a href="<?= url('profile/edit-profile.php#education') ?>" class="btn btn-sm btn-primary">
                        <i class="fas fa-plus me-1"></i>Add Education
                    </a>
                </div>
                <div class="card-body">
                    <?php if (empty($educations)): ?>
                        <div class="text-center py-4">
                            <i class="fas fa-graduation-cap fa-3x text-muted mb-3"></i>
                            <p class="text-muted mb-3">No education records added yet</p>
                            <a href="<?= url('profile/edit-profile.php#education') ?>" class="btn btn-outline-primary">
                                <i class="fas fa-plus me-2"></i>Add Your Education
                            </a>
                        </div>
                    <?php else: ?>
                        <?php foreach ($educations as $edu): ?>
                            <div class="border-bottom pb-3 mb-3">
                                <div class="d-flex justify-content-between align-items-start">
                                    <div class="flex-grow-1">
                                        <h6 class="mb-1 fw-bold"><?= htmlspecialchars($edu['degree']) ?></h6>
                                        <p class="text-muted mb-1"><?= htmlspecialchars($edu['institution']) ?></p>
                                        <?php if (!empty($edu['field_of_study'])): ?>
                                            <p class="text-muted mb-1 small">
                                                <i class="fas fa-book me-1"></i><?= htmlspecialchars($edu['field_of_study']) ?>
                                            </p>
                                        <?php endif; ?>
                                        <small class="text-muted">
                                            <i class="fas fa-calendar me-1"></i>
                                            <?= htmlspecialchars($edu['start_year']) ?> - 
                                            <?= $edu['is_current'] ? '<span class="badge bg-success">Current</span>' : htmlspecialchars($edu['end_year']) ?>
                                            <?php if (!empty($edu['percentage_cgpa'])): ?>
                                                <span class="ms-2">
                                                    <i class="fas fa-award me-1"></i><?= htmlspecialchars($edu['percentage_cgpa']) ?>
                                                </span>
                                            <?php endif; ?>
                                        </small>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                        <div class="text-center mt-3">
                            <a href="<?= url('profile/edit-profile.php#education') ?>" class="btn btn-sm btn-outline-primary">
                                Manage Education <i class="fas fa-arrow-right ms-1"></i>
                            </a>
                        </div>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Experience Section -->
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-header bg-white border-0 d-flex justify-content-between align-items-center py-3">
                    <h5 class="mb-0 fw-bold"><i class="fas fa-briefcase text-success me-2"></i>Work Experience</h5>
                    <a href="<?= url('profile/edit-profile.php#experience') ?>" class="btn btn-sm btn-primary">
                        <i class="fas fa-plus me-1"></i>Add Experience
                    </a>
                </div>
                <div class="card-body">
                    <?php if (empty($experiences)): ?>
                        <div class="text-center py-4">
                            <i class="fas fa-briefcase fa-3x text-muted mb-3"></i>
                            <p class="text-muted mb-3">No work experience added yet</p>
                            <a href="<?= url('profile/edit-profile.php#experience') ?>" class="btn btn-outline-primary">
                                <i class="fas fa-plus me-2"></i>Add Your Experience
                            </a>
                        </div>
                    <?php else: ?>
                        <?php foreach ($experiences as $exp): ?>
                            <div class="border-bottom pb-3 mb-3">
                                <div class="d-flex justify-content-between align-items-start">
                                    <div class="flex-grow-1">
                                        <h6 class="mb-1 fw-bold">
                                            <?= htmlspecialchars($exp['designation']) ?>
                                            <?php if ($exp['is_current']): ?>
                                                <span class="badge bg-success ms-2">Current</span>
                                            <?php endif; ?>
                                        </h6>
                                        <p class="text-muted mb-1"><?= htmlspecialchars($exp['company_name']) ?></p>
                                        <small class="text-muted d-block mb-2">
                                            <i class="fas fa-calendar me-1"></i>
                                            <?= date('M Y', strtotime($exp['start_date'])) ?> - 
                                            <?= $exp['is_current'] ? 'Present' : date('M Y', strtotime($exp['end_date'])) ?>
                                            <span class="ms-2">
                                                <i class="fas fa-briefcase me-1"></i><?= htmlspecialchars($exp['employment_type']) ?>
                                            </span>
                                            <?php if (!empty($exp['location'])): ?>
                                                <span class="ms-2">
                                                    <i class="fas fa-map-marker-alt me-1"></i><?= htmlspecialchars($exp['location']) ?>
                                                </span>
                                            <?php endif; ?>
                                        </small>
                                        <?php if (!empty($exp['description'])): ?>
                                            <p class="small mb-0 text-muted">
                                                <?= nl2br(htmlspecialchars(substr($exp['description'], 0, 150))) ?>
                                                <?= strlen($exp['description']) > 150 ? '...' : '' ?>
                                            </p>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                        <div class="text-center mt-3">
                            <a href="<?= url('profile/edit-profile.php#experience') ?>" class="btn btn-sm btn-outline-primary">
                                Manage Experience <i class="fas fa-arrow-right ms-1"></i>
                            </a>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <!-- Right Column - Skills & Quick Actions -->
        <div class="col-lg-4">
            <!-- Skills Section -->
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-header bg-white border-0 d-flex justify-content-between align-items-center py-3">
                    <h5 class="mb-0 fw-bold"><i class="fas fa-code text-info me-2"></i>Skills</h5>
                    <a href="<?= url('profile/edit-profile.php#skills') ?>" class="btn btn-sm btn-primary">
                        <i class="fas fa-plus me-1"></i>Add
                    </a>
                </div>
                <div class="card-body">
                    <?php if (empty($skills)): ?>
                        <div class="text-center py-4">
                            <i class="fas fa-code fa-3x text-muted mb-3"></i>
                            <p class="text-muted mb-3 small">No skills added yet</p>
                            <a href="<?= url('profile/edit-profile.php#skills') ?>" class="btn btn-sm btn-outline-primary">
                                <i class="fas fa-plus me-1"></i>Add Skills
                            </a>
                        </div>
                    <?php else: ?>
                        <div class="d-flex flex-wrap gap-2">
                            <?php foreach ($skills as $skill): ?>
                                <span class="badge bg-primary rounded-pill px-3 py-2">
                                    <?= htmlspecialchars($skill['skill_name']) ?>
                                    <?php if (!empty($skill['proficiency_level'])): ?>
                                        <small class="opacity-75 ms-1">(<?= htmlspecialchars($skill['proficiency_level']) ?>)</small>
                                    <?php endif; ?>
                                </span>
                            <?php endforeach; ?>
                        </div>
                        <div class="text-center mt-3">
                            <a href="<?= url('profile/edit-profile.php#skills') ?>" class="btn btn-sm btn-outline-primary">
                                Manage Skills <i class="fas fa-arrow-right ms-1"></i>
                            </a>
                        </div>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Saved Jobs Section -->
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-header bg-white border-0 py-3">
                    <h5 class="mb-0 fw-bold"><i class="fas fa-bookmark text-warning me-2"></i>Recent Saved Jobs</h5>
                </div>
                <div class="card-body">
                    <?php if (empty($recentSavedJobs)): ?>
                        <div class="text-center py-4">
                            <i class="fas fa-bookmark fa-3x text-muted mb-3"></i>
                            <p class="text-muted mb-3 small">No saved jobs yet</p>
                            <a href="<?= url() ?>" class="btn btn-sm btn-outline-primary">
                                <i class="fas fa-search me-1"></i>Browse Jobs
                            </a>
                        </div>
                    <?php else: ?>
                        <?php foreach ($recentSavedJobs as $job): ?>
                            <div class="border-bottom pb-2 mb-2">
                                <a href="<?= url('jobs/' . htmlspecialchars($job['slug'])) ?>" 
                                   class="text-decoration-none text-dark">
                                    <h6 class="mb-1 small fw-bold"><?= htmlspecialchars($job['title']) ?></h6>
                                    <p class="mb-1 small text-muted"><?= htmlspecialchars($job['company']) ?></p>
                                    <small class="text-muted">
                                        Saved <?= date('M d', strtotime($job['saved_at'])) ?>
                                    </small>
                                </a>
                            </div>
                        <?php endforeach; ?>
                        <div class="text-center mt-3">
                            <a href="<?= url('profile/saved-jobs.php') ?>" class="btn btn-sm btn-outline-warning">
                                View All Saved Jobs <i class="fas fa-arrow-right ms-1"></i>
                            </a>
                        </div>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Quick Actions Card -->
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white border-0 py-3">
                    <h5 class="mb-0 fw-bold"><i class="fas fa-bolt me-2"></i>Quick Actions</h5>
                </div>
                <div class="list-group list-group-flush">
                    <a href="<?= url() ?>" class="list-group-item list-group-item-action">
                        <i class="fas fa-search text-primary me-2"></i>Browse All Jobs
                    </a>
                    <a href="<?= url('profile/edit-profile.php') ?>" class="list-group-item list-group-item-action">
                        <i class="fas fa-user-edit text-info me-2"></i>Edit Profile
                    </a>
                    <a href="<?= url('profile/edit-profile.php#resume') ?>" class="list-group-item list-group-item-action">
                        <i class="fas fa-file-upload text-success me-2"></i>Upload Resume
                    </a>
                    <a href="<?= url('profile/edit-profile.php#preferences') ?>" class="list-group-item list-group-item-action">
                        <i class="fas fa-heart text-danger me-2"></i>Job Interests
                        <?php if ($preferencesCount > 0): ?>
                            <span class="badge bg-danger rounded-pill float-end"><?= $preferencesCount ?></span>
                        <?php endif; ?>
                    </a>
                    <a href="<?= url('profile/saved-jobs.php') ?>" class="list-group-item list-group-item-action">
                        <i class="fas fa-bookmark text-warning me-2"></i>My Saved Jobs
                        <?php if ($savedJobsCount > 0): ?>
                            <span class="badge bg-warning rounded-pill float-end"><?= $savedJobsCount ?></span>
                        <?php endif; ?>
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
.hover-card {
    transition: all 0.3s ease;
    cursor: pointer;
}
.hover-card:hover {
    transform: translateY(-3px);
    box-shadow: 0 6px 20px rgba(0,0,0,0.15) !important;
}
.badge {
    font-weight: 500;
}
.list-group-item {
    transition: all 0.2s ease;
}
.list-group-item:hover {
    background-color: #f8f9fa;
    padding-left: 1.5rem;
}
.stretched-link::after {
    position: absolute;
    top: 0;
    right: 0;
    bottom: 0;
    left: 0;
    z-index: 1;
    content: "";
}
</style>

<?php include '../includes/footer.php'; ?>
