<?php
require_once '../includes/config.php';
require_once '../includes/functions.php';

$pageTitle = 'Post New Job - Employer Panel';
include 'includes/header.php';

$empId   = getCurrentEmployerId();
$company = sanitize($_SESSION['employer_company'] ?? '');
$msg     = '';

// Load master data
$categories      = $pdo->query("SELECT id, category_name, icon FROM master_job_categories WHERE is_active = 1 ORDER BY category_name")->fetchAll();
$workModes       = $pdo->query("SELECT id, mode_name FROM master_work_modes WHERE is_active = 1 ORDER BY display_order")->fetchAll();
$employmentTypes = $pdo->query("SELECT id, type_name FROM master_employment_types WHERE is_active = 1 ORDER BY display_order")->fetchAll();
$expLevels       = $pdo->query("SELECT id, level_name FROM master_experience_levels WHERE is_active = 1 ORDER BY display_order")->fetchAll();
$states          = $pdo->query("SELECT id, state_name FROM master_states WHERE is_active = 1 ORDER BY state_name")->fetchAll();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title       = sanitize($_POST['title'] ?? '');
    $company_val = sanitize($_POST['company'] ?? $company);
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

    if (!$title || !$job_link) {
        $msg = '<div class="alert alert-danger">Job Title and Application Link are required.</div>';
    } else {
        $slug = generateUniqueJobSlug($pdo, $title, $company_val);

        $stmt = $pdo->prepare("
            INSERT INTO jobs
                (employer_id, title, company, slug, location, description, job_link,
                 application_deadline, salary_min, salary_max,
                 job_category_id, work_mode_id, employment_type_id, experience_level_id,
                 state_id, short_info, posted_date, is_active)
            VALUES
                (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, CURDATE(), 1)
        ");
        $stmt->execute([
            $empId, $title, $company_val, $slug, $location, $description, $job_link,
            $deadline ?: null, $salary_min, $salary_max,
            $cat_id, $wm_id, $et_id, $el_id, $state_id, $short_info
        ]);
        $newId = $pdo->lastInsertId();
        $msg = '<div class="alert alert-success">✅ Job posted successfully! <a href="' . url('employer/edit-job.php?id=' . $newId) . '">Edit it</a> or <a href="' . url('employer/add-job.php') . '">Post another</a>.</div>';
    }
}
?>

<?= $msg ?>

<div class="page-card">
    <h5 class="fw-bold mb-4">📝 Post a New Job</h5>

    <form method="POST">
        <div class="row g-3">

            <!-- Basic Info -->
            <div class="col-12"><h6 class="text-muted fw-semibold border-bottom pb-2">Basic Information</h6></div>

            <div class="col-md-8">
                <label class="form-label fw-semibold">Job Title *</label>
                <input type="text" name="title" class="form-control" placeholder="e.g. Software Engineer" required>
            </div>
            <div class="col-md-4">
                <label class="form-label fw-semibold">Company Name *</label>
                <input type="text" name="company" class="form-control"
                       value="<?= htmlspecialchars($company) ?>" required>
            </div>

            <div class="col-md-6">
                <label class="form-label fw-semibold">Location</label>
                <input type="text" name="location" class="form-control" placeholder="e.g. Mumbai, Maharashtra">
            </div>
            <div class="col-md-6">
                <label class="form-label fw-semibold">State</label>
                <select name="state_id" class="form-select">
                    <option value="">Select State</option>
                    <?php foreach ($states as $s): ?>
                        <option value="<?= $s['id'] ?>"><?= htmlspecialchars($s['state_name']) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="col-12">
                <label class="form-label fw-semibold">Short Description</label>
                <input type="text" name="short_info" class="form-control" placeholder="One-line job summary (shown in listings)">
            </div>

            <div class="col-12">
                <label class="form-label fw-semibold">Full Job Description</label>
                <textarea name="description" class="form-control" rows="6"
                          placeholder="Detailed job description, responsibilities, requirements..."></textarea>
            </div>

            <!-- Job Details -->
            <div class="col-12 mt-2"><h6 class="text-muted fw-semibold border-bottom pb-2">Job Details</h6></div>

            <div class="col-md-3">
                <label class="form-label fw-semibold">Category</label>
                <select name="job_category_id" class="form-select">
                    <option value="">Select Category</option>
                    <?php foreach ($categories as $cat): ?>
                        <option value="<?= $cat['id'] ?>"><?= $cat['icon'] ?> <?= htmlspecialchars($cat['category_name']) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-md-3">
                <label class="form-label fw-semibold">Work Mode</label>
                <select name="work_mode_id" class="form-select">
                    <option value="">Select Mode</option>
                    <?php foreach ($workModes as $wm): ?>
                        <option value="<?= $wm['id'] ?>"><?= htmlspecialchars($wm['mode_name']) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-md-3">
                <label class="form-label fw-semibold">Employment Type</label>
                <select name="employment_type_id" class="form-select">
                    <option value="">Select Type</option>
                    <?php foreach ($employmentTypes as $et): ?>
                        <option value="<?= $et['id'] ?>"><?= htmlspecialchars($et['type_name']) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-md-3">
                <label class="form-label fw-semibold">Experience Level</label>
                <select name="experience_level_id" class="form-select">
                    <option value="">Select Level</option>
                    <?php foreach ($expLevels as $el): ?>
                        <option value="<?= $el['id'] ?>"><?= htmlspecialchars($el['level_name']) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>

            <!-- Salary & Dates -->
            <div class="col-12 mt-2"><h6 class="text-muted fw-semibold border-bottom pb-2">Salary & Dates</h6></div>

            <div class="col-md-3">
                <label class="form-label fw-semibold">Min Salary (₹)</label>
                <input type="number" name="salary_min" class="form-control" placeholder="e.g. 30000">
            </div>
            <div class="col-md-3">
                <label class="form-label fw-semibold">Max Salary (₹)</label>
                <input type="number" name="salary_max" class="form-control" placeholder="e.g. 60000">
            </div>
            <div class="col-md-3">
                <label class="form-label fw-semibold">Application Deadline</label>
                <input type="date" name="application_deadline" class="form-control"
                       min="<?= date('Y-m-d') ?>">
            </div>

            <!-- Link -->
            <div class="col-12 mt-2"><h6 class="text-muted fw-semibold border-bottom pb-2">Application Link</h6></div>

            <div class="col-md-8">
                <label class="form-label fw-semibold">Application / Job Link *</label>
                <input type="url" name="job_link" class="form-control"
                       placeholder="https://company.com/careers/apply" required>
            </div>

        </div><!-- /.row -->

        <div class="mt-4 d-flex gap-2">
            <button type="submit" class="btn btn-primary px-4">🚀 Post Job</button>
            <a href="<?= url('employer/jobs.php') ?>" class="btn btn-outline-secondary">Cancel</a>
        </div>
    </form>
</div>

<?php include '../includes/footer.php'; ?>
