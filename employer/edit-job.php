<?php
require_once '../includes/config.php';
require_once '../includes/functions.php';

$pageTitle = 'Edit Job - Employer Panel';
include 'includes/header.php';

$empId = getCurrentEmployerId();
$jobId = isset($_GET['id']) ? (int)$_GET['id'] : 0;
$msg   = '';

// ✅ SECURITY: Only fetch if this employer owns the job
$stmt = $pdo->prepare("SELECT * FROM jobs WHERE id = ? AND employer_id = ?");
$stmt->execute([$jobId, $empId]);
$job = $stmt->fetch();

if (!$job) {
    echo '<div class="alert alert-danger">❌ Job not found or you do not have permission to edit it.</div>';
    include '../includes/footer.php';
    exit;
}

// Master data
$categories      = $pdo->query("SELECT id, category_name, icon FROM master_job_categories WHERE is_active = 1 ORDER BY category_name")->fetchAll();
$workModes       = $pdo->query("SELECT id, mode_name FROM master_work_modes WHERE is_active = 1 ORDER BY display_order")->fetchAll();
$employmentTypes = $pdo->query("SELECT id, type_name FROM master_employment_types WHERE is_active = 1 ORDER BY display_order")->fetchAll();
$expLevels       = $pdo->query("SELECT id, level_name FROM master_experience_levels WHERE is_active = 1 ORDER BY display_order")->fetchAll();
$states          = $pdo->query("SELECT id, state_name FROM master_states WHERE is_active = 1 ORDER BY state_name")->fetchAll();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title       = sanitize($_POST['title'] ?? '');
    $company_val = sanitize($_POST['company'] ?? '');
    $location    = sanitize($_POST['location'] ?? '');
    $description = trim($_POST['description'] ?? '');
    $job_link    = sanitize($_POST['job_link'] ?? '');
    $deadline    = $_POST['application_deadline'] ?? null;
    $salary_min  = !empty($_POST['salary_min'])  ? (float)$_POST['salary_min']  : null;
    $salary_max  = !empty($_POST['salary_max'])  ? (float)$_POST['salary_max']  : null;
    $cat_id      = !empty($_POST['job_category_id'])    ? (int)$_POST['job_category_id']    : null;
    $wm_id       = !empty($_POST['work_mode_id'])       ? (int)$_POST['work_mode_id']       : null;
    $et_id       = !empty($_POST['employment_type_id']) ? (int)$_POST['employment_type_id'] : null;
    $el_id       = !empty($_POST['experience_level_id'])? (int)$_POST['experience_level_id']: null;
    $state_id    = !empty($_POST['state_id'])           ? (int)$_POST['state_id']           : null;
    $short_info  = trim($_POST['short_info'] ?? '');
    $is_active   = isset($_POST['is_active']) ? 1 : 0;

    if (!$title || !$job_link) {
        $msg = '<div class="alert alert-danger">Job Title and Application Link are required.</div>';
    } else {
        $slug = generateUniqueJobSlug($pdo, $title, $company_val, null, $jobId);

        // ✅ SECURITY: WHERE includes employer_id so only own jobs can be updated
        $update = $pdo->prepare("
            UPDATE jobs SET
                title = ?, company = ?, slug = ?, location = ?, description = ?,
                job_link = ?, application_deadline = ?, salary_min = ?, salary_max = ?,
                job_category_id = ?, work_mode_id = ?, employment_type_id = ?,
                experience_level_id = ?, state_id = ?, short_info = ?, is_active = ?
            WHERE id = ? AND employer_id = ?
        ");
        $update->execute([
            $title, $company_val, $slug, $location, $description,
            $job_link, $deadline ?: null, $salary_min, $salary_max,
            $cat_id, $wm_id, $et_id, $el_id, $state_id, $short_info, $is_active,
            $jobId, $empId
        ]);

        // Refresh job data
        $stmt = $pdo->prepare("SELECT * FROM jobs WHERE id = ? AND employer_id = ?");
        $stmt->execute([$jobId, $empId]);
        $job = $stmt->fetch();

        $msg = '<div class="alert alert-success">✅ Job updated successfully!</div>';
    }
}

// Helper: select option
function sel($val, $current) { return (string)$val === (string)$current ? 'selected' : ''; }
?>

<?= $msg ?>

<div class="page-card">
    <div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-2">
        <h5 class="fw-bold mb-0">✏️ Edit Job</h5>
        <div class="d-flex gap-2">
            <a href="<?= url('job-details.php?id=' . $jobId) ?>" target="_blank"
               class="btn btn-sm btn-outline-secondary">👁️ View Live</a>
            <a href="<?= url('employer/jobs.php') ?>" class="btn btn-sm btn-outline-secondary">← Back</a>
        </div>
    </div>

    <form method="POST">
        <div class="row g-3">

            <div class="col-12"><h6 class="text-muted fw-semibold border-bottom pb-2">Basic Information</h6></div>

            <div class="col-md-8">
                <label class="form-label fw-semibold">Job Title *</label>
                <input type="text" name="title" class="form-control"
                       value="<?= htmlspecialchars($job['title']) ?>" required>
            </div>
            <div class="col-md-4">
                <label class="form-label fw-semibold">Company Name *</label>
                <input type="text" name="company" class="form-control"
                       value="<?= htmlspecialchars($job['company']) ?>" required>
            </div>

            <div class="col-md-6">
                <label class="form-label fw-semibold">Location</label>
                <input type="text" name="location" class="form-control"
                       value="<?= htmlspecialchars($job['location'] ?? '') ?>">
            </div>
            <div class="col-md-6">
                <label class="form-label fw-semibold">State</label>
                <select name="state_id" class="form-select">
                    <option value="">Select State</option>
                    <?php foreach ($states as $s): ?>
                        <option value="<?= $s['id'] ?>" <?= sel($s['id'], $job['state_id']) ?>>
                            <?= htmlspecialchars($s['state_name']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="col-12">
                <label class="form-label fw-semibold">Short Description</label>
                <input type="text" name="short_info" class="form-control"
                       value="<?= htmlspecialchars($job['short_info'] ?? '') ?>">
            </div>

            <div class="col-12">
                <label class="form-label fw-semibold">Full Job Description</label>
                <textarea name="description" class="form-control" rows="7"><?= htmlspecialchars($job['description'] ?? '') ?></textarea>
            </div>

            <div class="col-12 mt-2"><h6 class="text-muted fw-semibold border-bottom pb-2">Job Details</h6></div>

            <div class="col-md-3">
                <label class="form-label fw-semibold">Category</label>
                <select name="job_category_id" class="form-select">
                    <option value="">Select Category</option>
                    <?php foreach ($categories as $cat): ?>
                        <option value="<?= $cat['id'] ?>" <?= sel($cat['id'], $job['job_category_id']) ?>>
                            <?= $cat['icon'] ?> <?= htmlspecialchars($cat['category_name']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-md-3">
                <label class="form-label fw-semibold">Work Mode</label>
                <select name="work_mode_id" class="form-select">
                    <option value="">Select Mode</option>
                    <?php foreach ($workModes as $wm): ?>
                        <option value="<?= $wm['id'] ?>" <?= sel($wm['id'], $job['work_mode_id']) ?>>
                            <?= htmlspecialchars($wm['mode_name']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-md-3">
                <label class="form-label fw-semibold">Employment Type</label>
                <select name="employment_type_id" class="form-select">
                    <option value="">Select Type</option>
                    <?php foreach ($employmentTypes as $et): ?>
                        <option value="<?= $et['id'] ?>" <?= sel($et['id'], $job['employment_type_id']) ?>>
                            <?= htmlspecialchars($et['type_name']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-md-3">
                <label class="form-label fw-semibold">Experience Level</label>
                <select name="experience_level_id" class="form-select">
                    <option value="">Select Level</option>
                    <?php foreach ($expLevels as $el): ?>
                        <option value="<?= $el['id'] ?>" <?= sel($el['id'], $job['experience_level_id']) ?>>
                            <?= htmlspecialchars($el['level_name']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="col-12 mt-2"><h6 class="text-muted fw-semibold border-bottom pb-2">Salary & Dates</h6></div>

            <div class="col-md-3">
                <label class="form-label fw-semibold">Min Salary (₹)</label>
                <input type="number" name="salary_min" class="form-control"
                       value="<?= $job['salary_min'] ?? '' ?>">
            </div>
            <div class="col-md-3">
                <label class="form-label fw-semibold">Max Salary (₹)</label>
                <input type="number" name="salary_max" class="form-control"
                       value="<?= $job['salary_max'] ?? '' ?>">
            </div>
            <div class="col-md-3">
                <label class="form-label fw-semibold">Application Deadline</label>
                <input type="date" name="application_deadline" class="form-control"
                       value="<?= $job['application_deadline'] ?? '' ?>">
            </div>

            <div class="col-12 mt-2"><h6 class="text-muted fw-semibold border-bottom pb-2">Application Link & Status</h6></div>

            <div class="col-md-8">
                <label class="form-label fw-semibold">Application / Job Link *</label>
                <input type="url" name="job_link" class="form-control"
                       value="<?= htmlspecialchars($job['job_link'] ?? '') ?>" required>
            </div>
            <div class="col-md-4 d-flex align-items-end">
                <div class="form-check form-switch mb-2">
                    <input class="form-check-input" type="checkbox" name="is_active"
                           id="isActive" <?= $job['is_active'] ? 'checked' : '' ?>>
                    <label class="form-check-label fw-semibold" for="isActive">
                        Job is Active / Visible
                    </label>
                </div>
            </div>

        </div><!-- /.row -->

        <div class="mt-4 d-flex gap-2">
            <button type="submit" class="btn btn-primary px-4">💾 Save Changes</button>
            <a href="<?= url('employer/jobs.php') ?>" class="btn btn-outline-secondary">Cancel</a>
        </div>
    </form>
</div>

<?php include '../includes/footer.php'; ?>
