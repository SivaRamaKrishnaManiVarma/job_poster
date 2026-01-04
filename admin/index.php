<?php
require_once '../includes/config.php';
require_once '../includes/functions.php';

if (!isAdmin()) redirect('login.php');
// $_SESSION['admin_username'] = isset($_SESSION['admin_username']) ? $_SESSION['admin_username'] : 'Admin';
$pageTitle = 'Admin Dashboard';
$isAdmin = true;

$totalJobs = $pdo->query("SELECT COUNT(*) FROM jobs")->fetchColumn();
$activeJobs = $pdo->query("SELECT COUNT(*) FROM jobs WHERE is_active = 1")->fetchColumn();
$recentJobs = $pdo->query("SELECT * FROM jobs ORDER BY created_at DESC LIMIT 5")->fetchAll();

include 'includes/header.php';
?>

<h2>Welcome, <?php echo sanitize($_SESSION['admin_username']); ?>!</h2>
<hr>

<div class="row mb-4">
    <div class="col-md-4">
        <div class="card bg-primary text-white">
            <div class="card-body text-center">
                <h2><?php echo $totalJobs; ?></h2>
                <p class="mb-0">Total Jobs</p>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card bg-success text-white">
            <div class="card-body text-center">
                <h2><?php echo $activeJobs; ?></h2>
                <p class="mb-0">Active Jobs</p>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card bg-secondary text-white">
            <div class="card-body text-center">
                <h2><?php echo $totalJobs - $activeJobs; ?></h2>
                <p class="mb-0">Inactive Jobs</p>
            </div>
        </div>
    </div>
</div>

<div class="mb-4">
    <a href="jobs.php" class="btn btn-primary">Manage Jobs</a>
    <a href="jobs.php?action=add" class="btn btn-success">Add New Job</a>
</div>

<h4>Recent Jobs</h4>
<div class="table-responsive">
    <table class="table table-striped">
        <thead>
            <tr>
                <th>Title</th>
                <th>Company</th>
                <th>Posted Date</th>
                <th>Status</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($recentJobs as $job): ?>
            <tr>
                <td><?php echo sanitize($job['title']); ?></td>
                <td><?php echo sanitize($job['company']); ?></td>
                <td><?php echo date('d M Y', strtotime($job['posted_date'])); ?></td>
                <td>
                    <?php if ($job['is_active']): ?>
                        <span class="badge bg-success">Active</span>
                    <?php else: ?>
                        <span class="badge bg-secondary">Inactive</span>
                    <?php endif; ?>
                </td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>

<?php include '../includes/footer.php'; ?>
