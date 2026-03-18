<?php
require_once '../includes/config.php';
require_once '../includes/functions.php';

$pageTitle = 'Dashboard - Employer Panel';
include 'includes/header.php';

$empId = getCurrentEmployerId();

// Stats: only this employer's jobs
$totalJobs = $pdo->prepare("SELECT COUNT(*) FROM jobs WHERE employer_id = ?");
$totalJobs->execute([$empId]);
$totalJobs = $totalJobs->fetchColumn();

$activeJobs = $pdo->prepare("SELECT COUNT(*) FROM jobs WHERE employer_id = ? AND is_active = 1 AND (application_deadline IS NULL OR application_deadline >= CURDATE())");
$activeJobs->execute([$empId]);
$activeJobs = $activeJobs->fetchColumn();

$expiredJobs = $pdo->prepare("SELECT COUNT(*) FROM jobs WHERE employer_id = ? AND application_deadline < CURDATE()");
$expiredJobs->execute([$empId]);
$expiredJobs = $expiredJobs->fetchColumn();

$totalViews = $pdo->prepare("SELECT COALESCE(SUM(view_count),0) FROM jobs WHERE employer_id = ?");
$totalViews->execute([$empId]);
$totalViews = $totalViews->fetchColumn();

// Recent 5 jobs
$recentStmt = $pdo->prepare("
    SELECT j.*, mc.category_name
    FROM jobs j
    LEFT JOIN master_job_categories mc ON j.job_category_id = mc.id
    WHERE j.employer_id = ?
    ORDER BY j.posted_date DESC
    LIMIT 5
");
$recentStmt->execute([$empId]);
$recentJobs = $recentStmt->fetchAll();
?>

<!-- Stats -->
<div class="row g-3 mb-4">
    <div class="col-sm-6 col-lg-3">
        <div class="stat-card">
            <div class="stat-number"><?= $totalJobs ?></div>
            <div class="stat-label">Total Jobs Posted</div>
        </div>
    </div>
    <div class="col-sm-6 col-lg-3">
        <div class="stat-card">
            <div class="stat-number text-success"><?= $activeJobs ?></div>
            <div class="stat-label">Active Jobs</div>
        </div>
    </div>
    <div class="col-sm-6 col-lg-3">
        <div class="stat-card">
            <div class="stat-number text-danger"><?= $expiredJobs ?></div>
            <div class="stat-label">Expired Jobs</div>
        </div>
    </div>
    <div class="col-sm-6 col-lg-3">
        <div class="stat-card">
            <div class="stat-number text-info"><?= number_format($totalViews) ?></div>
            <div class="stat-label">Total Views</div>
        </div>
    </div>
</div>

<!-- Recent Jobs -->
<div class="page-card">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h5 class="fw-bold mb-0">Recent Job Postings</h5>
        <a href="<?= url('employer/add-job.php') ?>" class="btn btn-sm btn-primary">➕ Post New Job</a>
    </div>

    <?php if (empty($recentJobs)): ?>
        <div class="text-center py-5 text-muted">
            <div style="font-size:3rem;">📭</div>
            <p class="mt-2">No jobs posted yet.</p>
            <a href="<?= url('employer/add-job.php') ?>" class="btn btn-primary">Post Your First Job</a>
        </div>
    <?php else: ?>
        <div class="table-responsive">
            <table class="table table-hover mb-0">
                <thead class="table-light">
                    <tr>
                        <th>Job Title</th>
                        <th>Category</th>
                        <th>Posted</th>
                        <th>Deadline</th>
                        <th>Views</th>
                        <th>Status</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($recentJobs as $job): ?>
                        <?php $dl = getDeadlineStatus($job['application_deadline']); ?>
                        <tr>
                            <td><strong><?= sanitize($job['title']) ?></strong><br>
                                <small class="text-muted"><?= sanitize($job['company']) ?></small>
                            </td>
                            <td><small><?= sanitize($job['category_name'] ?? '-') ?></small></td>
                            <td><small><?= date('d M Y', strtotime($job['posted_date'])) ?></small></td>
                            <td>
                                <small class="<?= $dl['class'] === 'deadline-urgent' ? 'text-danger fw-bold' : 'text-muted' ?>">
                                    <?= $dl['label'] ?>
                                </small>
                            </td>
                            <td><small><?= number_format($job['view_count'] ?? 0) ?></small></td>
                            <td>
                                <?php if ($job['is_active']): ?>
                                    <span class="badge-active">Active</span>
                                <?php else: ?>
                                    <span class="badge-inactive">Inactive</span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <a href="<?= url('employer/edit-job.php?id=' . $job['id']) ?>"
                                   class="btn btn-sm btn-warning">✏️ Edit</a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
        <div class="mt-3">
            <a href="<?= url('employer/jobs.php') ?>" class="btn btn-outline-primary btn-sm">View All Jobs →</a>
        </div>
    <?php endif; ?>
</div>

<?php
include '../includes/footer.php';
// footer.php just closes tags:
?>
