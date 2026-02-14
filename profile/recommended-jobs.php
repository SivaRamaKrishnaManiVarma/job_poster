<?php
require_once '../includes/session-check.php';
require_once '../includes/master-data-functions.php';

$userId = $candidateId;

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

if (empty($userJobPreferences)) {
    header('Location: ' . url('profile/edit-profile.php?error=Please add job interests first#preferences'));
    exit;
}

// Get matching jobs
$categoryIds = array_column($userJobPreferences, 'job_category_id');
$placeholders = str_repeat('?,', count($categoryIds) - 1) . '?';

$recommendedJobsStmt = $pdo->prepare("
    SELECT j.*, 
           c.category_name, c.icon as category_icon,
           w.mode_name, w.icon as work_mode_icon,
           et.type_name as employment_type_name
    FROM jobs j
    LEFT JOIN master_job_categories c ON j.job_category_id = c.id
    LEFT JOIN master_work_modes w ON j.work_mode_id = w.id
    LEFT JOIN master_employment_types et ON j.employment_type_id = et.id
    WHERE j.is_active = 1 
    AND j.job_category_id IN ($placeholders)
    AND (j.application_deadline IS NULL OR j.application_deadline >= CURDATE())
    ORDER BY j.posted_date DESC
");
$recommendedJobsStmt->execute($categoryIds);
$recommendedJobs = $recommendedJobsStmt->fetchAll(PDO::FETCH_ASSOC);

$pageTitle = 'Recommended Jobs - Job Portal';
include '../includes/header.php';
?>

<div class="container py-4">
    <!-- Header -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <div>
                    <h2 class="mb-1"><i class="fas fa-star text-warning me-2"></i>Jobs Recommended For You</h2>
                    <p class="text-muted mb-0">Based on your job interests and preferences</p>
                </div>
                <div>
                    <a href="<?= url('profile/dashboard.php') ?>" class="btn btn-outline-secondary me-2">
                        <i class="fas fa-arrow-left me-2"></i>Dashboard
                    </a>
                    <a href="<?= url('profile/edit-profile.php#preferences') ?>" class="btn btn-primary">
                        <i class="fas fa-cog me-2"></i>Update Interests
                    </a>
                </div>
            </div>

            <!-- Your Interests -->
            <div class="card border-0 bg-light mb-4">
                <div class="card-body">
                    <h6 class="fw-bold mb-3"><i class="fas fa-heart text-danger me-2"></i>Your Interests</h6>
                    <div class="d-flex flex-wrap gap-2">
                        <?php 
                        $priorityColors = [3 => 'danger', 2 => 'warning', 1 => 'info'];
                        foreach ($userJobPreferences as $pref): 
                            $color = $priorityColors[$pref['priority']] ?? 'secondary';
                        ?>
                            <span class="badge bg-<?= $color ?> rounded-pill px-3 py-2">
                                <i class="<?= htmlspecialchars($pref['icon']) ?> me-1"></i>
                                <?= htmlspecialchars($pref['category_name']) ?>
                            </span>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <?php if (empty($recommendedJobs)): ?>
        <!-- No Matching Jobs -->
        <div class="card border-0 shadow-sm">
            <div class="card-body text-center py-5">
                <i class="fas fa-search fa-4x text-muted mb-3"></i>
                <h4 class="mb-3">No Matching Jobs Right Now</h4>
                <p class="text-muted mb-4">We don't have any active jobs matching your interests at the moment. Check back soon!</p>
                <div class="btn-group">
                    <a href="<?= url() ?>" class="btn btn-primary">
                        <i class="fas fa-briefcase me-2"></i>Browse All Jobs
                    </a>
                    <a href="<?= url('profile/edit-profile.php#preferences') ?>" class="btn btn-outline-primary">
                        <i class="fas fa-edit me-2"></i>Update Interests
                    </a>
                </div>
            </div>
        </div>
    <?php else: ?>
        <!-- Job Listings -->
        <div class="row mb-3">
            <div class="col-12">
                <h5 class="mb-0">Found <?= count($recommendedJobs) ?> matching job<?= count($recommendedJobs) != 1 ? 's' : '' ?></h5>
            </div>
        </div>

        <?php foreach ($recommendedJobs as $job): ?>
            <div class="card border-0 shadow-sm mb-3 hover-card">
                <div class="card-body">
                    <div class="row">
                        <div class="col-lg-9">
                            <h5 class="mb-2">
                                <a href="<?= url('jobs/' . htmlspecialchars($job['slug'])) ?>" 
                                   class="text-dark text-decoration-none">
                                    <?= htmlspecialchars($job['title']) ?>
                                </a>
                            </h5>
                            <h6 class="text-primary mb-3">
                                <i class="fas fa-building me-1"></i><?= htmlspecialchars($job['company']) ?>
                            </h6>
                            
                            <div class="d-flex flex-wrap gap-3 mb-2 small text-muted">
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
                                
                                <?php if (!empty($job['work_mode_name'])): ?>
                                    <span>
                                        <i class="<?= htmlspecialchars($job['work_mode_icon']) ?> me-1"></i>
                                        <?= htmlspecialchars($job['work_mode_name']) ?>
                                    </span>
                                <?php endif; ?>
                                
                                <?php if (!empty($job['employment_type_name'])): ?>
                                    <span>
                                        <i class="fas fa-briefcase me-1"></i>
                                        <?= htmlspecialchars($job['employment_type_name']) ?>
                                    </span>
                                <?php endif; ?>
                            </div>
                            
                            <div class="d-flex flex-wrap gap-3 small">
                                <?php if (!empty($job['min_salary']) || !empty($job['max_salary'])): ?>
                                    <span class="text-success fw-semibold">
                                        <i class="fas fa-rupee-sign me-1"></i>
                                        <?php if (!empty($job['min_salary']) && !empty($job['max_salary'])): ?>
                                            <?= number_format($job['min_salary']) ?> - <?= number_format($job['max_salary']) ?>
                                        <?php elseif (!empty($job['min_salary'])): ?>
                                            <?= number_format($job['min_salary']) ?>+
                                        <?php endif; ?>
                                    </span>
                                <?php endif; ?>
                                
                                <?php if (!empty($job['min_experience']) || !empty($job['max_experience'])): ?>
                                    <span>
                                        <i class="fas fa-user-clock me-1"></i>
                                        <?php if (!empty($job['min_experience']) && !empty($job['max_experience'])): ?>
                                            <?= $job['min_experience'] ?> - <?= $job['max_experience'] ?> years
                                        <?php elseif (!empty($job['min_experience'])): ?>
                                            <?= $job['min_experience'] ?>+ years
                                        <?php endif; ?>
                                    </span>
                                <?php endif; ?>
                            </div>
                        </div>
                        
                        <div class="col-lg-3 text-lg-end mt-3 mt-lg-0">
                            <a href="<?= url('jobs/' . htmlspecialchars($job['slug'])) ?>" class="btn btn-primary w-100 mb-2">
                                <i class="fas fa-eye me-2"></i>View Details
                            </a>
                            <small class="text-muted d-block">
                                <i class="fas fa-clock me-1"></i>Posted <?= date('M d, Y', strtotime($job['posted_date'])) ?>
                            </small>
                        </div>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>
    <?php endif; ?>
</div>

<style>
.hover-card {
    transition: all 0.3s ease;
}
.hover-card:hover {
    transform: translateY(-2px);
    box-shadow: 0 8px 16px rgba(0,0,0,0.1) !important;
}
</style>

<?php include '../includes/footer.php'; ?>
