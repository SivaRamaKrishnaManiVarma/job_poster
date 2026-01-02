<?php
require_once '../includes/config.php';
require_once '../includes/functions.php';

if (!isAdmin()) redirect('login.php');

$pageTitle = 'Manage Jobs';
$isAdmin = true;
$message = '';

// Handle form submissions
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (isset($_POST['add_job'])) {
        $stmt = $pdo->prepare("INSERT INTO jobs (title, company, description, job_link, eligibility, location, category, posted_date, is_active) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)");
        $stmt->execute([
            sanitize($_POST['title']),
            sanitize($_POST['company']),
            $_POST['description'],
            sanitize($_POST['job_link']),
            sanitize($_POST['eligibility']),
            sanitize($_POST['location']),
            sanitize($_POST['category']),
            $_POST['posted_date'],
            isset($_POST['is_active']) ? 1 : 0
        ]);
        $message = '<div class="alert alert-success">Job added successfully!</div>';
    } 
    elseif (isset($_POST['edit_job'])) {
        $stmt = $pdo->prepare("UPDATE jobs SET title=?, company=?, description=?, job_link=?, eligibility=?, location=?, category=?, posted_date=?, is_active=? WHERE id=?");
        $stmt->execute([
            sanitize($_POST['title']),
            sanitize($_POST['company']),
            $_POST['description'],
            sanitize($_POST['job_link']),
            sanitize($_POST['eligibility']),
            sanitize($_POST['location']),
            sanitize($_POST['category']),
            $_POST['posted_date'],
            isset($_POST['is_active']) ? 1 : 0,
            $_POST['job_id']
        ]);
        $message = '<div class="alert alert-success">Job updated successfully!</div>';
    }
}

// Handle delete
if (isset($_GET['delete'])) {
    $stmt = $pdo->prepare("DELETE FROM jobs WHERE id = ?");
    $stmt->execute([$_GET['delete']]);
    $message = '<div class="alert alert-success">Job deleted successfully!</div>';
}

// Handle toggle status
if (isset($_GET['toggle'])) {
    $stmt = $pdo->prepare("UPDATE jobs SET is_active = NOT is_active WHERE id = ?");
    $stmt->execute([$_GET['toggle']]);
    $message = '<div class="alert alert-success">Job status updated!</div>';
}

// Get job for editing
$editJob = null;
if (isset($_GET['edit'])) {
    $stmt = $pdo->prepare("SELECT * FROM jobs WHERE id = ?");
    $stmt->execute([$_GET['edit']]);
    $editJob = $stmt->fetch();
}

// Get all jobs
$jobs = $pdo->query("SELECT * FROM jobs ORDER BY posted_date DESC")->fetchAll();

include '../includes/header.php';
?>

<h2>Manage Jobs</h2>
<?php echo $message; ?>
<hr>

<!-- Add/Edit Form -->
<div class="card mb-4">
    <div class="card-header">
        <h4><?php echo $editJob ? 'Edit Job' : 'Add New Job'; ?></h4>
    </div>
    <div class="card-body">
        <form method="POST">
            <?php if ($editJob): ?>
                <input type="hidden" name="job_id" value="<?php echo $editJob['id']; ?>">
            <?php endif; ?>
            
            <div class="row">
                <div class="col-md-6 mb-3">
                    <label class="form-label">Job Title *</label>
                    <input type="text" name="title" class="form-control" value="<?php echo $editJob ? sanitize($editJob['title']) : ''; ?>" required>
                </div>
                <div class="col-md-6 mb-3">
                    <label class="form-label">Company *</label>
                    <input type="text" name="company" class="form-control" value="<?php echo $editJob ? sanitize($editJob['company']) : ''; ?>" required>
                </div>
            </div>
            
            <div class="mb-3">
                <label class="form-label">Description</label>
                <textarea name="description" class="form-control" rows="4"><?php echo $editJob ? sanitize($editJob['description']) : ''; ?></textarea>
            </div>
            
            <div class="row">
                <div class="col-md-6 mb-3">
                    <label class="form-label">Job Link (URL) *</label>
                    <input type="url" name="job_link" class="form-control" value="<?php echo $editJob ? sanitize($editJob['job_link']) : ''; ?>" required placeholder="https://example.com/apply">
                </div>
                <div class="col-md-6 mb-3">
                    <label class="form-label">Category</label>
                    <input type="text" name="category" class="form-control" value="<?php echo $editJob ? sanitize($editJob['category']) : ''; ?>" placeholder="e.g., IT, Engineering, Marketing">
                </div>
            </div>
            
            <div class="row">
                <div class="col-md-4 mb-3">
                    <label class="form-label">Location</label>
                    <input type="text" name="location" class="form-control" value="<?php echo $editJob ? sanitize($editJob['location']) : ''; ?>" placeholder="e.g., Vijayawada, Remote">
                </div>
                <div class="col-md-4 mb-3">
                    <label class="form-label">Eligibility</label>
                    <input type="text" name="eligibility" class="form-control" value="<?php echo $editJob ? sanitize($editJob['eligibility']) : ''; ?>" placeholder="e.g., B.Tech CS, 7.5 CGPA">
                </div>
                <div class="col-md-4 mb-3">
                    <label class="form-label">Posted Date *</label>
                    <input type="date" name="posted_date" class="form-control" value="<?php echo $editJob ? $editJob['posted_date'] : date('Y-m-d'); ?>" required>
                </div>
            </div>
            
            <div class="form-check mb-3">
                <input type="checkbox" name="is_active" class="form-check-input" id="is_active" <?php echo (!$editJob || $editJob['is_active']) ? 'checked' : ''; ?>>
                <label class="form-check-label" for="is_active">Active (visible to students)</label>
            </div>
            
            <button type="submit" name="<?php echo $editJob ? 'edit_job' : 'add_job'; ?>" class="btn btn-primary">
                <?php echo $editJob ? 'Update Job' : 'Add Job'; ?>
            </button>
            <?php if ($editJob): ?>
                <a href="jobs.php" class="btn btn-secondary">Cancel</a>
            <?php endif; ?>
        </form>
    </div>
</div>

<!-- Jobs List -->
<h4>All Jobs (<?php echo count($jobs); ?>)</h4>
<div class="table-responsive">
    <table class="table table-striped">
        <thead>
            <tr>
                <th>ID</th>
                <th>Title</th>
                <th>Company</th>
                <th>Category</th>
                <th>Posted Date</th>
                <th>Status</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($jobs as $job): ?>
            <tr>
                <td><?php echo $job['id']; ?></td>
                <td><?php echo sanitize($job['title']); ?></td>
                <td><?php echo sanitize($job['company']); ?></td>
                <td><?php echo sanitize($job['category']); ?></td>
                <td><?php echo date('d M Y', strtotime($job['posted_date'])); ?></td>
                <td>
                    <?php if ($job['is_active']): ?>
                        <span class="badge bg-success">Active</span>
                    <?php else: ?>
                        <span class="badge bg-secondary">Inactive</span>
                    <?php endif; ?>
                </td>
                <td>
                    <a href="?edit=<?php echo $job['id']; ?>" class="btn btn-sm btn-warning">Edit</a>
                    <a href="?toggle=<?php echo $job['id']; ?>" class="btn btn-sm btn-info" onclick="return confirm('Toggle status?')">Toggle</a>
                    <a href="?delete=<?php echo $job['id']; ?>" class="btn btn-sm btn-danger" onclick="return confirm('Delete this job?')">Delete</a>
                </td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>

<?php include '../includes/footer.php'; ?>
