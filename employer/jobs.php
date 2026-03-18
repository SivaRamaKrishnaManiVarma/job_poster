<?php
require_once '../includes/config.php';
require_once '../includes/functions.php';

$pageTitle = 'My Jobs - Employer Panel';
include 'includes/header.php';

$empId  = getCurrentEmployerId();
$msg    = '';

// Toggle active status — ONLY own jobs
if (isset($_GET['toggle']) && is_numeric($_GET['toggle'])) {
    $jobId = (int)$_GET['toggle'];
    $owns  = $pdo->prepare("SELECT id FROM jobs WHERE id = ? AND employer_id = ?");
    $owns->execute([$jobId, $empId]);
    if ($owns->fetch()) {
        $pdo->prepare("UPDATE jobs SET is_active = !is_active WHERE id = ?")->execute([$jobId]);
        $msg = '<div class="alert alert-success">Job status updated.</div>';
    }
}

// Delete — ONLY own jobs
if (isset($_GET['delete']) && is_numeric($_GET['delete'])) {
    $jobId = (int)$_GET['delete'];
    $owns  = $pdo->prepare("SELECT id FROM jobs WHERE id = ? AND employer_id = ?");
    $owns->execute([$jobId, $empId]);
    if ($owns->fetch()) {
        $pdo->prepare("DELETE FROM jobs WHERE id = ?")->execute([$jobId]);
        $msg = '<div class="alert alert-success">Job deleted successfully.</div>';
    } else {
        $msg = '<div class="alert alert-danger">Unauthorized action.</div>';
    }
}

// Fetch only this employer's jobs
$search   = sanitize($_GET['search'] ?? '');
$statusF  = $_GET['status'] ?? 'all';

$sql    = "SELECT j.*, mc.category_name FROM jobs j LEFT JOIN master_job_categories mc ON j.job_category_id = mc.id WHERE j.employer_id = ?";
$params = [$empId];

if ($search) {
    $sql    .= " AND (j.title LIKE ? OR j.company LIKE ?)";
    $params[] = "%$search%";
    $params[] = "%$search%";
}
if ($statusF === 'active')   { $sql .= " AND j.is_active = 1"; }
if ($statusF === 'inactive') { $sql .= " AND j.is_active = 0"; }

$sql .= " ORDER BY j.posted_date DESC";
$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$jobs = $stmt->fetchAll();
?>

<?= $msg ?>

<div class="page-card">
    <div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-2">
        <h5 class="fw-bold mb-0">My Job Postings (<?= count($jobs) ?>)</h5>
        <a href="<?= url('employer/add-job.php') ?>" class="btn btn-primary">➕ Post New Job</a>
    </div>

    <!-- Filters -->
    <form method="GET" class="row g-2 mb-4">
        <div class="col-md-5">
            <input type="text" name="search" class="form-control" placeholder="Search job title or company..."
                   value="<?= htmlspecialchars($search) ?>">
        </div>
        <div class="col-md-3">
            <select name="status" class="form-select">
                <option value="all"      <?= $statusF === 'all'      ? 'selected' : '' ?>>All Status</option>
                <option value="active"   <?= $statusF === 'active'   ? 'selected' : '' ?>>Active</option>
                <option value="inactive" <?= $statusF === 'inactive' ? 'selected' : '' ?>>Inactive</option>
            </select>
        </div>
        <div class="col-md-2">
            <button type="submit" class="btn btn-primary w-100">Search</button>
        </div>
        <?php if ($search || $statusF !== 'all'): ?>
            <div class="col-md-2">
                <a href="<?= url('employer/jobs.php') ?>" class="btn btn-outline-secondary w-100">Clear</a>
            </div>
        <?php endif; ?>
    </form>

    <?php if (empty($jobs)): ?>
        <div class="text-center py-5 text-muted">
            <div style="font-size:3rem;">📭</div>
            <p class="mt-2">No jobs found.</p>
            <a href="<?= url('employer/add-job.php') ?>" class="btn btn-primary mt-2">Post Your First Job</a>
        </div>
    <?php else: ?>
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th>Job Title</th>
                        <th>Category</th>
                        <th>Location</th>
                        <th>Posted</th>
                        <th>Deadline</th>
                        <th>Views</th>
                        <th>Status</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($jobs as $job): ?>
                        <?php $dl = getDeadlineStatus($job['application_deadline']); ?>
                        <tr>
                            <td>
                                <strong><?= sanitize($job['title']) ?></strong><br>
                                <small class="text-muted">🏢 <?= sanitize($job['company']) ?></small>
                            </td>
                            <td><small><?= sanitize($job['category_name'] ?? '-') ?></small></td>
                            <td><small><?= sanitize($job['location'] ?? '-') ?></small></td>
                            <td><small><?= date('d M Y', strtotime($job['posted_date'])) ?></small></td>
                            <td>
                                <small class="<?= $dl['class'] === 'deadline-urgent' ? 'text-danger fw-bold' : 'text-muted' ?>">
                                    <?= $dl['label'] ?>
                                </small>
                            </td>
                            <td><small><?= number_format($job['view_count'] ?? 0) ?></small></td>
                            <td>
                                <a href="?toggle=<?= $job['id'] ?>" title="Toggle Status"
                                   onclick="return confirm('Toggle status for this job?')"
                                   class="text-decoration-none">
                                    <?php if ($job['is_active']): ?>
                                        <span class="badge-active">Active</span>
                                    <?php else: ?>
                                        <span class="badge-inactive">Inactive</span>
                                    <?php endif; ?>
                                </a>
                            </td>
                            <td>
                                <div class="d-flex gap-1 flex-wrap">
                                    <a href="<?= url('job-details.php?id=' . $job['id']) ?>"
                                       target="_blank" class="btn btn-sm btn-outline-secondary" title="View">👁️</a>
                                    <a href="<?= url('employer/edit-job.php?id=' . $job['id']) ?>"
                                       class="btn btn-sm btn-warning" title="Edit">✏️</a>
                                    <a href="?delete=<?= $job['id'] ?>"
                                       class="btn btn-sm btn-danger"
                                       onclick="return confirm('Permanently delete this job?')"
                                       title="Delete">🗑️</a>
                                </div>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    <?php endif; ?>
</div>

<?php include '../includes/footer.php'; ?>
