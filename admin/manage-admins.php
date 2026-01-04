<?php
require_once '../includes/config.php';
require_once '../includes/functions.php';

if (!isAdmin()) redirect('login.php');

$pageTitle = 'Manage Admins';
$isAdmin = true;
$message = '';

// Handle Add Admin
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['add_admin'])) {
    $username = sanitize($_POST['username']);
    $password = $_POST['password'];
    $email = sanitize($_POST['email']);
    
    // Validate
    if (strlen($username) < 3) {
        $message = '<div class="alert alert-danger">Username must be at least 3 characters</div>';
    } elseif (strlen($password) < 6) {
        $message = '<div class="alert alert-danger">Password must be at least 6 characters</div>';
    } else {
        // Check if username exists
        $check = $pdo->prepare("SELECT id FROM admins WHERE username = ?");
        $check->execute([$username]);
        
        if ($check->fetch()) {
            $message = '<div class="alert alert-danger">Username already exists!</div>';
        } else {
            // Hash password and insert
            $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
            $stmt = $pdo->prepare("INSERT INTO admins (username, password, email) VALUES (?, ?, ?)");
            $stmt->execute([$username, $hashedPassword, $email]);
            $message = '<div class="alert alert-success">Admin created successfully!</div>';
        }
    }
}

// Handle Delete Admin
if (isset($_GET['delete'])) {
    $deleteId = $_GET['delete'];
    
    // Don't allow deleting yourself
    if ($deleteId != $_SESSION['admin_id']) {
        $stmt = $pdo->prepare("DELETE FROM admins WHERE id = ?");
        $stmt->execute([$deleteId]);
        $message = '<div class="alert alert-success">Admin deleted successfully!</div>';
    } else {
        $message = '<div class="alert alert-danger">You cannot delete your own account!</div>';
    }
}

// Handle Change Password
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['change_password'])) {
    $adminId = $_POST['admin_id'];
    $newPassword = $_POST['new_password'];
    
    if (strlen($newPassword) < 6) {
        $message = '<div class="alert alert-danger">Password must be at least 6 characters</div>';
    } else {
        $hashedPassword = password_hash($newPassword, PASSWORD_DEFAULT);
        $stmt = $pdo->prepare("UPDATE admins SET password = ? WHERE id = ?");
        $stmt->execute([$hashedPassword, $adminId]);
        $message = '<div class="alert alert-success">Password changed successfully!</div>';
    }
}

// Get all admins
$admins = $pdo->query("SELECT * FROM admins ORDER BY created_at DESC")->fetchAll();

include 'includes/header.php';
?>

<h2>Manage Admin Accounts</h2>
<?php echo $message; ?>
<hr>

<!-- Add New Admin Form -->
<div class="card mb-4">
    <div class="card-header bg-primary text-white">
        <h4 class="mb-0">Create New Admin</h4>
    </div>
    <div class="card-body">
        <form method="POST">
            <div class="row">
                <div class="col-md-4 mb-3">
                    <label class="form-label">Username *</label>
                    <input type="text" name="username" class="form-control" required minlength="3" placeholder="Enter username">
                    <small class="text-muted">Minimum 3 characters</small>
                </div>
                <div class="col-md-4 mb-3">
                    <label class="form-label">Password *</label>
                    <input type="password" name="password" class="form-control" required minlength="6" placeholder="Enter password">
                    <small class="text-muted">Minimum 6 characters</small>
                </div>
                <div class="col-md-4 mb-3">
                    <label class="form-label">Email (Optional)</label>
                    <input type="email" name="email" class="form-control" placeholder="admin@example.com">
                </div>
            </div>
            <button type="submit" name="add_admin" class="btn btn-primary">
                ➕ Create Admin
            </button>
        </form>
    </div>
</div>

<!-- Current Admins List -->
<div class="card">
    <div class="card-header">
        <h4 class="mb-0">Current Admins (<?php echo count($admins); ?>)</h4>
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover mb-0">
                <thead class="table-light">
                    <tr>
                        <th>ID</th>
                        <th>Username</th>
                        <th>Email</th>
                        <th>Created Date</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($admins as $admin): ?>
                    <tr>
                        <td><?php echo $admin['id']; ?></td>
                        <td>
                            <strong><?php echo sanitize($admin['username']); ?></strong>
                            <?php if ($admin['id'] == $_SESSION['admin_id']): ?>
                                <span class="badge bg-success ms-2">You</span>
                            <?php endif; ?>
                        </td>
                        <td><?php echo sanitize($admin['email'] ?: '-'); ?></td>
                        <td><?php echo date('d M Y, h:i A', strtotime($admin['created_at'])); ?></td>
                        <td>
                            <!-- Change Password Button -->
                            <button type="button" class="btn btn-sm btn-warning" data-bs-toggle="modal" data-bs-target="#changePasswordModal<?php echo $admin['id']; ?>">
                                🔑 Change Password
                            </button>
                            
                            <?php if ($admin['id'] != $_SESSION['admin_id']): ?>
                                <a href="?delete=<?php echo $admin['id']; ?>" 
                                   class="btn btn-sm btn-danger" 
                                   onclick="return confirm('Are you sure you want to delete this admin?')">
                                    🗑️ Delete
                                </a>
                            <?php endif; ?>
                        </td>
                    </tr>
                    
                    <!-- Change Password Modal -->
                    <div class="modal fade" id="changePasswordModal<?php echo $admin['id']; ?>" tabindex="-1">
                        <div class="modal-dialog">
                            <div class="modal-content">
                                <div class="modal-header">
                                    <h5 class="modal-title">Change Password for: <?php echo sanitize($admin['username']); ?></h5>
                                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                </div>
                                <form method="POST">
                                    <div class="modal-body">
                                        <input type="hidden" name="admin_id" value="<?php echo $admin['id']; ?>">
                                        <div class="mb-3">
                                            <label class="form-label">New Password</label>
                                            <input type="password" name="new_password" class="form-control" required minlength="6" placeholder="Enter new password">
                                            <small class="text-muted">Minimum 6 characters</small>
                                        </div>
                                        <div class="alert alert-info">
                                            <small>⚠️ Password will be securely hashed before saving</small>
                                        </div>
                                    </div>
                                    <div class="modal-footer">
                                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                                        <button type="submit" name="change_password" class="btn btn-primary">Update Password</button>
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

<!-- Security Info -->
<div class="alert alert-info mt-4">
    <h5>🔒 Security Information</h5>
    <ul class="mb-0">
        <!-- <li>All passwords are encrypted using bcrypt hashing (PHP password_hash)</li> -->
        <li>Passwords cannot be retrieved - only reset</li>
        <li>Minimum password length: 6 characters (recommended: 8+)</li>
        <li>You cannot delete your own admin account while logged in</li>
    </ul>
</div>

<?php include '../includes/footer.php'; ?>
