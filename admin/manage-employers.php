<?php
require_once '../includes/config.php';
require_once '../includes/functions.php';

if (!isAdmin()) redirect('login.php');

$pageTitle = 'Manage Employers';
$message   = '';

// Add Employer
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_employer'])) {
    $username     = sanitize($_POST['username']);
    $password     = $_POST['password'];
    $email        = sanitize($_POST['email']);
    $company_name = sanitize($_POST['company_name']);

    if (strlen($username) < 3) {
        $message = '<div class="alert alert-danger">Username must be at least 3 characters.</div>';
    } elseif (strlen($password) < 6) {
        $message = '<div class="alert alert-danger">Password must be at least 6 characters.</div>';
    } elseif (!$company_name) {
        $message = '<div class="alert alert-danger">Company name is required.</div>';
    } else {
        $check = $pdo->prepare("SELECT id FROM employers WHERE username = ? OR email = ?");
        $check->execute([$username, $email]);
        if ($check->fetch()) {
            $message = '<div class="alert alert-danger">Username or email already exists.</div>';
        } else {
            $pdo->prepare("INSERT INTO employers (username, email, password, company_name) VALUES (?, ?, ?, ?)")
                ->execute([$username, $email, password_hash($password, PASSWORD_DEFAULT), $company_name]);
            $message = '<div class="alert alert-success">Employer account created successfully!</div>';
        }
    }
}

// Toggle Active
if (isset($_GET['toggle']) && is_numeric($_GET['toggle'])) {
    $pdo->prepare("UPDATE employers SET is_active = !is_active WHERE id = ?")->execute([$_GET['toggle']]);
    $message = '<div class="alert alert-success">Employer status updated.</div>';
}

// Delete Employer
if (isset($_GET['delete']) && is_numeric($_GET['delete'])) {
    $delId = (int)$_GET['delete'];
    // Unlink their jobs (set employer_id to NULL so jobs still exist)
    $pdo->prepare("UPDATE jobs SET employer_id = NULL WHERE employer_id = ?")->execute([$delId]);
    $pdo->prepare("DELETE FROM employers WHERE id = ?")->execute([$delId]);
    $message = '<div class="alert alert-success">Employer deleted. Their jobs are now unassigned.</div>';
}

// Change Password
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['change_password'])) {
    $empId       = (int)$_POST['employer_id'];
    $newPassword = $_POST['new_password'];
    if (strlen($newPassword) < 6) {
        $message = '<div class="alert alert-danger">Password must be at least 6 characters.</div>';
    } else {
        $pdo->prepare("UPDATE employers SET password = ? WHERE id = ?")
            ->execute([password_hash($newPassword, PASSWORD_DEFAULT), $empId]);
        $message = '<div class="alert alert-success">Password updated successfully!</div>';
    }
}

// Get all employers with job counts
$employers = $pdo->query("
    SELECT e.*, COUNT(j.id) AS job_count
    FROM employers e
    LEFT JOIN jobs j ON e.id = j.employer_id
    GROUP BY e.id
    ORDER BY e.created_at DESC
")->fetchAll();

include 'includes/header.php';
?>

<h2>Manage Employer Accounts</h2>
<?= $message ?>
<hr>

<!-- Add Employer Form -->
<div class="card mb-4">
    <div class="card-header" style="background: linear-gradient(135deg, #0ea5e9, #0284c7); color: white;">
        <h4 class="mb-0">➕ Create Employer Account</h4>
    </div>
    <div class="card-body">
        <form method="POST">
            <div class="row g-3">
                <div class="col-md-3">
                    <label class="form-label">Username *</label>
                    <input type="text" name="username" class="form-control" required minlength="3" placeholder="employer_username">
                    <small class="text-muted">Min 3 characters</small>
                </div>
                <div class="col-md-3">
                    <label class="form-label">Company Name *</label>
                    <input type="text" name="company_name" class="form-control" required placeholder="Acme Pvt. Ltd.">
                </div>
                <div class="col-md-3">
                    <label class="form-label">Email</label>
                    <input type="email" name="email" class="form-control" placeholder="hr@company.com">
                </div>
                <div class="col-md-3">
                    <label class="form-label">Password *</label>
                    <input type="password" name="password" class="form-control" required minlength="6" placeholder="Min 6 characters">
                </div>
            </div>
            <button type="submit" name="add_employer" class="btn btn-primary mt-3">
                ➕ Create Employer
            </button>
        </form>
    </div>
</div>

<!-- Employers List -->
<div class="card">
    <div class="card-header">
        <h4 class="mb-0">Employer Accounts (<?= count($employers) ?>)</h4>
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover mb-0">
                <thead class="table-light">
                    <tr>
                        <th>ID</th>
                        <th>Username</th>
                        <th>Company</th>
                        <th>Email</th>
                        <th>Jobs Posted</th>
                        <th>Status</th>
                        <th>Created</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($employers as $emp): ?>
                        <tr>
                            <td><?= $emp['id'] ?></td>
                            <td><strong><?= sanitize($emp['username']) ?></strong></td>
                            <td>🏢 <?= sanitize($emp['company_name']) ?></td>
                            <td><?= sanitize($emp['email'] ?: '-') ?></td>
                            <td>
                                <a href="jobs.php?employer_id=<?= $emp['id'] ?>"
                                   class="badge bg-primary text-decoration-none">
                                    <?= $emp['job_count'] ?> jobs
                                </a>
                            </td>
                            <td>
                                <a href="?toggle=<?= $emp['id'] ?>"
                                   onclick="return confirm('Toggle this employer status?')"
                                   class="text-decoration-none">
                                    <?php if ($emp['is_active']): ?>
                                        <span class="badge bg-success">Active</span>
                                    <?php else: ?>
                                        <span class="badge bg-secondary">Inactive</span>
                                    <?php endif; ?>
                                </a>
                            </td>
                            <td><?= date('d M Y', strtotime($emp['created_at'])) ?></td>
                            <td>
                                <button class="btn btn-sm btn-warning"
                                        data-bs-toggle="modal"
                                        data-bs-target="#pwdModal<?= $emp['id'] ?>">
                                    🔑 Password
                                </button>
                                <a href="?delete=<?= $emp['id'] ?>"
                                   class="btn btn-sm btn-danger"
                                   onclick="return confirm('Delete this employer? Their jobs will be unassigned.')">
                                    🗑️ Delete
                                </a>
                            </td>
                        </tr>

                        <!-- Change Password Modal -->
                        <div class="modal fade" id="pwdModal<?= $emp['id'] ?>" tabindex="-1">
                            <div class="modal-dialog">
                                <div class="modal-content">
                                    <div class="modal-header">
                                        <h5 class="modal-title">Change Password: <?= sanitize($emp['username']) ?></h5>
                                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                    </div>
                                    <form method="POST">
                                        <div class="modal-body">
                                            <input type="hidden" name="employer_id" value="<?= $emp['id'] ?>">
                                            <label class="form-label">New Password</label>
                                            <input type="password" name="new_password" class="form-control"
                                                   required minlength="6" placeholder="Min 6 characters">
                                        </div>
                                        <div class="modal-footer">
                                            <button class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                                            <button type="submit" name="change_password" class="btn btn-primary">Update</button>
                                        </div>
                                    </form>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<?php include '../includes/footer.php'; ?>
