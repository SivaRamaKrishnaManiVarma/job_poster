<?php
require_once 'includes/config.php';
require_once 'includes/auth-functions.php';
require_once 'includes/master-data-functions.php';

// Check if user is logged in
$isLoggedIn = false;
$userId = null;

if (function_exists('isCandidateLoggedIn') && isCandidateLoggedIn()) {
    $isLoggedIn = true;
    if (isset($_SESSION['candidate_id']))     $userId = $_SESSION['candidate_id'];
    elseif (isset($_SESSION['user_id']))      $userId = $_SESSION['user_id'];
    elseif (isset($_SESSION['id']))           $userId = $_SESSION['id'];
}

// Get category from URL
$categoryId   = isset($_GET['category']) ? (int)$_GET['category'] : null;
$categoryInfo = null;

if ($categoryId) {
    $stmt = $pdo->prepare("SELECT * FROM master_job_categories WHERE id = ? AND is_active = 1");
    $stmt->execute([$categoryId]);
    $categoryInfo = $stmt->fetch(PDO::FETCH_ASSOC);
}

// Filter parameters
$selectedLocation       = $_GET['location']        ?? '';
$selectedWorkMode       = isset($_GET['work_mode'])       ? (int)$_GET['work_mode']       : 0;
$selectedEmploymentType = isset($_GET['employment_type']) ? (int)$_GET['employment_type'] : 0;
$selectedExperience     = isset($_GET['experience'])      ? (int)$_GET['experience']      : 0;
$searchQuery            = $_GET['search'] ?? '';
$page                   = isset($_GET['page']) ? max(1, (int)$_GET['page']) : 1;
$perPage                = 15;
$offset                 = ($page - 1) * $perPage;

// Build SQL
$sql = "
    SELECT j.*,
           mc.category_name, mc.icon AS category_icon,
           mw.mode_name AS work_mode_name,
           met.type_name AS employment_type_name,
           mel.level_name AS experience_level_name
    FROM jobs j
    LEFT JOIN master_job_categories mc    ON j.job_category_id    = mc.id
    LEFT JOIN master_work_modes mw        ON j.work_mode_id        = mw.id
    LEFT JOIN master_employment_types met ON j.employment_type_id  = met.id
    LEFT JOIN master_experience_levels mel ON j.experience_level_id = mel.id
    WHERE j.is_active = 1
      AND (j.application_deadline IS NULL OR j.application_deadline >= CURDATE())
";
$params = [];

if ($categoryId) {
    $sql .= " AND j.job_category_id = ?";
    $params[] = $categoryId;
}
if ($searchQuery) {
    $sql .= " AND (j.title LIKE ? OR j.company LIKE ? OR j.description LIKE ?)";
    $t = '%' . $searchQuery . '%';
    $params[] = $t; $params[] = $t; $params[] = $t;
}
if ($selectedLocation) {
    $sql .= " AND j.location LIKE ?";
    $params[] = '%' . $selectedLocation . '%';
}
if ($selectedWorkMode) {
    $sql .= " AND j.work_mode_id = ?";
    $params[] = $selectedWorkMode;
}
if ($selectedEmploymentType) {
    $sql .= " AND j.employment_type_id = ?";
    $params[] = $selectedEmploymentType;
}
if ($selectedExperience) {
    $sql .= " AND j.experience_level_id = ?";
    $params[] = $selectedExperience;
}

// Count
$countStmt = $pdo->prepare("SELECT COUNT(*) " . substr($sql, strpos($sql, "FROM")));
$countStmt->execute($params);
$totalCount = $countStmt->fetchColumn();
$totalPages = ceil($totalCount / $perPage);

// Paginated results
$sql .= " ORDER BY j.posted_date DESC LIMIT ? OFFSET ?";
$params[] = $perPage;
$params[] = $offset;
$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$jobs = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Filter options
$locations = $pdo->query("
    SELECT DISTINCT location FROM jobs
    WHERE is_active = 1 AND location IS NOT NULL AND location != ''
    ORDER BY location
")->fetchAll(PDO::FETCH_COLUMN);

$workModes = $pdo->query("
    SELECT id, mode_name FROM master_work_modes WHERE is_active = 1 ORDER BY display_order
")->fetchAll(PDO::FETCH_ASSOC);

$employmentTypes = $pdo->query("
    SELECT id, type_name FROM master_employment_types WHERE is_active = 1 ORDER BY display_order
")->fetchAll(PDO::FETCH_ASSOC);

$experienceLevels = $pdo->query("
    SELECT id, level_name FROM master_experience_levels WHERE is_active = 1 ORDER BY display_order
")->fetchAll(PDO::FETCH_ASSOC);

$allCategories = $pdo->query("
    SELECT mc.*, COUNT(j.id) AS job_count
    FROM master_job_categories mc
    LEFT JOIN jobs j
      ON mc.id = j.job_category_id
     AND j.is_active = 1
     AND (j.application_deadline IS NULL OR j.application_deadline >= CURDATE())
    WHERE mc.is_active = 1
    GROUP BY mc.id
    HAVING job_count > 0
    ORDER BY job_count DESC
")->fetchAll(PDO::FETCH_ASSOC);

function buildFilterUrl($currentFilters, $removeKey) {
    $f = $currentFilters;
    unset($f[$removeKey]);
    return '?' . http_build_query(array_filter($f));
}

$currentFilters = array_filter([
    'category'        => $categoryId,
    'search'          => $searchQuery,
    'location'        => $selectedLocation,
    'work_mode'       => $selectedWorkMode,
    'employment_type' => $selectedEmploymentType,
    'experience'      => $selectedExperience,
]);

$pageTitle = $categoryInfo ? $categoryInfo['category_name'] . ' Jobs' : 'Browse Jobs';
include 'includes/header.php';
?>

<style>
:root {
    --dark-blue:       #1e40af;
    --light-blue:      #3b82f6;
    --very-light-blue: #eff6ff;
    --text-dark:       #1f2937;
    --text-gray:       #6b7280;
    --border-gray:     #e5e7eb;
}

/* ── Page Header ── */
.browse-header {
    background: var(--dark-blue);
    color: white;
    padding: 40px 0;
    margin-bottom: 24px;
    border-bottom: 3px solid var(--light-blue);
}
.browse-header h1 { font-weight: 700; font-size: 1.75rem; margin-bottom: 6px; }
.browse-header p  { font-size: 0.95rem; opacity: 0.9; }

/* ── Sidebar ── */
.filter-sidebar {
    background: #fff;
    border: 1px solid var(--border-gray);
    border-radius: 8px;
    padding: 20px;
    position: sticky;
    top: 80px;
}
.filter-section {
    margin-bottom: 20px;
    padding-bottom: 20px;
    border-bottom: 1px solid var(--border-gray);
}
.filter-section:last-child { border-bottom: none; margin-bottom: 0; padding-bottom: 0; }
.filter-title {
    font-size: 13px; font-weight: 700; color: var(--text-dark);
    margin-bottom: 10px; text-transform: uppercase; letter-spacing: 0.5px;
}

/* Category items */
.category-item {
    padding: 8px 10px; margin-bottom: 4px; border-radius: 6px;
    display: flex; justify-content: space-between; align-items: center;
    text-decoration: none; color: #374151;
    transition: all 0.2s; border: 1px solid transparent; font-size: 14px;
}
.category-item:hover         { background: var(--very-light-blue); color: var(--light-blue); border-color: #bfdbfe; }
.category-item.active        { background: #dbeafe; color: var(--dark-blue); font-weight: 600; border-color: #93c5fd; }
.category-item .badge        { background: #f3f4f6; color: var(--text-gray); font-size: 11px; padding: 2px 6px; border-radius: 10px; }
.category-item.active .badge { background: #bfdbfe; color: var(--dark-blue); }

/* Search box */
.search-box       { position: relative; margin-bottom: 12px; }
.search-box input {
    padding: 8px 12px 8px 36px; border: 1px solid var(--border-gray);
    border-radius: 6px; font-size: 14px; width: 100%; box-sizing: border-box;
}
.search-box input:focus { border-color: var(--light-blue); box-shadow: 0 0 0 3px rgba(37,99,235,.1); outline: none; }
.search-box i { position: absolute; left: 12px; top: 50%; transform: translateY(-50%); color: #9ca3af; font-size: 14px; }

/* Form controls */
.form-select, .form-control {
    border: 1px solid var(--border-gray); border-radius: 6px;
    font-size: 14px; padding: 8px 12px;
}
.form-select:focus, .form-control:focus {
    border-color: var(--light-blue); box-shadow: 0 0 0 3px rgba(37,99,235,.1); outline: none;
}

/* Active filter tags */
.active-filters {
    display: flex; gap: 6px; flex-wrap: wrap; margin-bottom: 16px;
    padding: 12px; background: #f9fafb; border-radius: 6px;
    border: 1px solid var(--border-gray);
}
.filter-tag {
    background: #dbeafe; color: var(--dark-blue); padding: 4px 10px;
    border-radius: 16px; font-size: 12px; font-weight: 500;
    display: flex; align-items: center; gap: 6px; border: 1px solid #93c5fd;
}
.filter-tag .remove { cursor: pointer; color: #dc2626; font-weight: bold; font-size: 14px; line-height: 1; text-decoration: none; }
.filter-tag .remove:hover { color: #991b1b; }

/* Results info */
.results-info {
    background: #f9fafb; padding: 12px 16px; border-radius: 6px;
    border: 1px solid var(--border-gray); margin-bottom: 16px; font-size: 14px;
}

/* ══════════════════════════════════════════
   JOB CARD  — fixed compact flex layout
══════════════════════════════════════════ */
.job-card {
    background: white;
    border: 1px solid var(--border-gray);
    border-radius: 6px;
    padding: 12px 16px;
    margin-bottom: 8px;
    /* ✅ CRITICAL: flex row, height = content only */
    display: flex !important;
    flex-direction: row !important;
    align-items: flex-start !important;
    gap: 12px;
    height: auto !important;
    min-height: 0 !important;
    overflow: hidden;
    transition: border-color 0.2s, box-shadow 0.2s;
}
.job-card:hover {
    border-color: var(--light-blue);
    box-shadow: 0 2px 8px rgba(37,99,235,.08);
}

/* Left: text content */
.job-card-body {
    flex: 1 1 0%;
    min-width: 0;       /* ✅ prevents flex overflow */
    overflow: hidden;
}
.job-card-body h5 {
    font-size: 0.95rem; font-weight: 600; color: var(--text-dark);
    margin: 0 0 3px 0; line-height: 1.35;
    white-space: nowrap; overflow: hidden; text-overflow: ellipsis;
}
.job-card-body h5 a { color: inherit; text-decoration: none; }
.job-card-body h5 a:hover { color: var(--light-blue); }

.company-name {
    color: var(--text-gray); font-size: 13px; font-weight: 500;
    margin: 0 0 6px 0;
    white-space: nowrap; overflow: hidden; text-overflow: ellipsis;
}

/* Meta chips */
.job-meta-info {
    display: flex; flex-wrap: wrap; align-items: center;
    gap: 3px 10px; font-size: 12px; color: var(--text-gray); margin: 0;
}
.job-meta-info span { display: inline-flex; align-items: center; gap: 3px; white-space: nowrap; }
.job-meta-info i    { font-size: 10px; opacity: .7; flex-shrink: 0; }
.salary-badge       { color: #059669 !important; font-weight: 600; }

/* Right: date + button */
.job-card-action {
    flex: 0 0 auto;         /* ✅ fixed width, never grows */
    width: 105px;
    display: flex;
    flex-direction: column;
    align-items: flex-end;
    justify-content: flex-start;   /* ✅ button stays at TOP */
    gap: 6px;
    padding-top: 1px;
}
.job-posted-date {
    font-size: 11px; color: #9ca3af;
    white-space: nowrap; text-align: right; line-height: 1.3;
}

/* Buttons */
.btn-primary {
    background: var(--light-blue); border-color: var(--light-blue);
    font-weight: 600; font-size: 12px; padding: 5px 10px;
    white-space: nowrap; border-radius: 4px;
}
.btn-primary:hover    { background: var(--dark-blue); border-color: var(--dark-blue); }
.btn-outline-danger   { border-color: #dc2626; color: #dc2626; font-weight: 600; font-size: 13px; }
.btn-outline-danger:hover { background: #dc2626; border-color: #dc2626; color: white; }
.btn-outline-secondary { font-size: 13px; }

/* Empty state */
.empty-state {
    text-align: center; padding: 60px 20px; color: var(--text-gray);
    background: #f9fafb; border-radius: 8px;
    border: 2px dashed #d1d5db; margin-bottom: 40px;
}
.empty-state i  { color: #9ca3af; margin-bottom: 16px; display: block; }
.empty-state h5 { color: #374151; font-weight: 700; margin-bottom: 10px; }

/* Pagination */
.pagination {
    display: flex; justify-content: center; gap: 6px;
    margin-top: 20px; margin-bottom: 40px; flex-wrap: wrap;
}
.page-link {
    padding: 7px 13px; background: white; border: 1px solid var(--border-gray);
    border-radius: 6px; color: #374151; text-decoration: none;
    transition: all 0.2s; font-weight: 500; font-size: 14px;
}
.page-link:hover  { border-color: var(--light-blue); background: var(--very-light-blue); color: var(--light-blue); }
.page-link.active { background: var(--light-blue); border-color: var(--light-blue); color: white; }
.page-ellipsis    { padding: 7px 13px; border: none; cursor: default; color: #9ca3af; }

/* Responsive */
@media (max-width: 991px) {
    .filter-sidebar { position: static; margin-bottom: 20px; }
}
@media (max-width: 576px) {
    .job-card { flex-direction: column !important; }
    .job-card-action {
        flex-direction: row; align-items: center;
        width: 100%; justify-content: space-between;
    }
    .job-card-body h5,
    .company-name { white-space: normal; overflow: visible; text-overflow: unset; }
}
</style>

<!-- Page Header -->
<div class="browse-header">
    <div class="container">
        <?php if ($categoryInfo): ?>
            <div class="d-flex align-items-center">
                <i class="fas fa-briefcase fs-2 me-3"></i>
                <div>
                    <h1><?= htmlspecialchars($categoryInfo['category_name']) ?> Jobs</h1>
                    <p class="mb-0"><?= number_format($totalCount) ?> opportunities available</p>
                </div>
            </div>
        <?php else: ?>
            <h1>Browse All Jobs</h1>
            <p class="mb-0"><?= number_format($totalCount) ?> opportunities available</p>
        <?php endif; ?>
    </div>
</div>

<div class="container py-4">
    <div class="row">

        <!-- ══ SIDEBAR ══ -->
        <div class="col-lg-3">
            <div class="filter-sidebar">

                <!-- Search -->
                <div class="filter-section">
                    <div class="filter-title">Search</div>
                    <form method="GET" action="">
                        <?php if ($categoryId): ?>
                            <input type="hidden" name="category" value="<?= $categoryId ?>">
                        <?php endif; ?>
                        <div class="search-box">
                            <i class="fas fa-search"></i>
                            <input type="text" name="search" class="form-control"
                                   placeholder="Job title, company..."
                                   value="<?= htmlspecialchars($searchQuery) ?>">
                        </div>
                        <button type="submit" class="btn btn-primary w-100">
                            <i class="fas fa-search me-2"></i>Search
                        </button>
                    </form>
                </div>

                <!-- Categories -->
                <?php if (!$categoryId): ?>
                    <div class="filter-section">
                        <div class="filter-title">Categories</div>
                        <div style="max-height:300px; overflow-y:auto;">
                            <?php foreach (array_slice($allCategories, 0, 15) as $cat): ?>
                                <a href="?category=<?= $cat['id'] ?>" class="category-item">
                                    <span><?= htmlspecialchars($cat['category_name']) ?></span>
                                    <span class="badge"><?= $cat['job_count'] ?></span>
                                </a>
                            <?php endforeach; ?>
                        </div>
                    </div>
                <?php else: ?>
                    <div class="filter-section">
                        <div class="filter-title">Category</div>
                        <div class="category-item active">
                            <span><?= htmlspecialchars($categoryInfo['category_name']) ?></span>
                        </div>
                        <a href="?" class="btn btn-outline-secondary btn-sm w-100 mt-2">
                            <i class="fas fa-th me-1"></i>View All Categories
                        </a>
                    </div>
                <?php endif; ?>

                <!-- Location -->
                <div class="filter-section">
                    <div class="filter-title">Location</div>
                    <form method="GET" action="">
                        <?php foreach ($currentFilters as $k => $v): ?>
                            <?php if ($k !== 'location'): ?>
                                <input type="hidden" name="<?= $k ?>" value="<?= htmlspecialchars($v) ?>">
                            <?php endif; ?>
                        <?php endforeach; ?>
                        <select name="location" class="form-select" onchange="this.form.submit()">
                            <option value="">All Locations</option>
                            <?php foreach ($locations as $loc): ?>
                                <option value="<?= htmlspecialchars($loc) ?>"
                                    <?= $selectedLocation === $loc ? 'selected' : '' ?>>
                                    <?= htmlspecialchars($loc) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </form>
                </div>

                <!-- Work Mode -->
                <div class="filter-section">
                    <div class="filter-title">Work Mode</div>
                    <form method="GET" action="">
                        <?php foreach ($currentFilters as $k => $v): ?>
                            <?php if ($k !== 'work_mode'): ?>
                                <input type="hidden" name="<?= $k ?>" value="<?= htmlspecialchars($v) ?>">
                            <?php endif; ?>
                        <?php endforeach; ?>
                        <select name="work_mode" class="form-select" onchange="this.form.submit()">
                            <option value="">All Modes</option>
                            <?php foreach ($workModes as $mode): ?>
                                <option value="<?= $mode['id'] ?>"
                                    <?= $selectedWorkMode == $mode['id'] ? 'selected' : '' ?>>
                                    <?= htmlspecialchars($mode['mode_name']) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </form>
                </div>

                <!-- Job Type -->
                <div class="filter-section">
                    <div class="filter-title">Job Type</div>
                    <form method="GET" action="">
                        <?php foreach ($currentFilters as $k => $v): ?>
                            <?php if ($k !== 'employment_type'): ?>
                                <input type="hidden" name="<?= $k ?>" value="<?= htmlspecialchars($v) ?>">
                            <?php endif; ?>
                        <?php endforeach; ?>
                        <select name="employment_type" class="form-select" onchange="this.form.submit()">
                            <option value="">All Types</option>
                            <?php foreach ($employmentTypes as $type): ?>
                                <option value="<?= $type['id'] ?>"
                                    <?= $selectedEmploymentType == $type['id'] ? 'selected' : '' ?>>
                                    <?= htmlspecialchars($type['type_name']) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </form>
                </div>

                <!-- Experience -->
                <div class="filter-section">
                    <div class="filter-title">Experience</div>
                    <form method="GET" action="">
                        <?php foreach ($currentFilters as $k => $v): ?>
                            <?php if ($k !== 'experience'): ?>
                                <input type="hidden" name="<?= $k ?>" value="<?= htmlspecialchars($v) ?>">
                            <?php endif; ?>
                        <?php endforeach; ?>
                        <select name="experience" class="form-select" onchange="this.form.submit()">
                            <option value="">All Levels</option>
                            <?php foreach ($experienceLevels as $level): ?>
                                <option value="<?= $level['id'] ?>"
                                    <?= $selectedExperience == $level['id'] ? 'selected' : '' ?>>
                                    <?= htmlspecialchars($level['level_name']) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </form>
                </div>

                <!-- Clear All -->
                <?php if (count($currentFilters) > ($categoryId ? 1 : 0)): ?>
                    <a href="<?= $categoryId ? '?category=' . $categoryId : '?' ?>"
                       class="btn btn-outline-danger w-100">
                        <i class="fas fa-times me-2"></i>Clear All Filters
                    </a>
                <?php endif; ?>

            </div>
        </div>
        <!-- /.col-lg-3 -->

        <!-- ══ JOBS LIST ══ -->
        <div class="col-lg-9">

            <!-- Active filter tags -->
            <?php if ($searchQuery || $selectedLocation || $selectedWorkMode || $selectedEmploymentType || $selectedExperience): ?>
                <div class="active-filters">
                    <span class="text-muted fw-semibold">Active Filters:</span>

                    <?php if ($searchQuery): ?>
                        <div class="filter-tag">
                            Search: "<?= htmlspecialchars($searchQuery) ?>"
                            <a href="<?= buildFilterUrl($currentFilters, 'search') ?>" class="remove">×</a>
                        </div>
                    <?php endif; ?>

                    <?php if ($selectedLocation): ?>
                        <div class="filter-tag">
                            <i class="fas fa-map-marker-alt me-1"></i><?= htmlspecialchars($selectedLocation) ?>
                            <a href="<?= buildFilterUrl($currentFilters, 'location') ?>" class="remove">×</a>
                        </div>
                    <?php endif; ?>

                    <?php if ($selectedWorkMode): ?>
                        <?php
                        $modeName = '';
                        foreach ($workModes as $m) {
                            if ($m['id'] == $selectedWorkMode) $modeName = $m['mode_name'];
                        }
                        ?>
                        <div class="filter-tag">
                            <?= htmlspecialchars($modeName) ?>
                            <a href="<?= buildFilterUrl($currentFilters, 'work_mode') ?>" class="remove">×</a>
                        </div>
                    <?php endif; ?>

                    <?php if ($selectedEmploymentType): ?>
                        <?php
                        $typeName = '';
                        foreach ($employmentTypes as $t) {
                            if ($t['id'] == $selectedEmploymentType) $typeName = $t['type_name'];
                        }
                        ?>
                        <div class="filter-tag">
                            <?= htmlspecialchars($typeName) ?>
                            <a href="<?= buildFilterUrl($currentFilters, 'employment_type') ?>" class="remove">×</a>
                        </div>
                    <?php endif; ?>

                    <?php if ($selectedExperience): ?>
                        <?php
                        $expName = '';
                        foreach ($experienceLevels as $e) {
                            if ($e['id'] == $selectedExperience) $expName = $e['level_name'];
                        }
                        ?>
                        <div class="filter-tag">
                            <i class="fas fa-user-tie me-1"></i><?= htmlspecialchars($expName) ?>
                            <a href="<?= buildFilterUrl($currentFilters, 'experience') ?>" class="remove">×</a>
                        </div>
                    <?php endif; ?>
                </div>
            <?php endif; ?>

            <!-- Results info bar -->
            <?php if (!empty($jobs)): ?>
                <div class="results-info">
                    <div class="d-flex justify-content-between align-items-center flex-wrap gap-2">
                        <div>
                            <strong><?= number_format($totalCount) ?></strong> jobs found
                            <?php if ($page > 1): ?>
                                <span class="text-muted">• Page <?= $page ?> of <?= $totalPages ?></span>
                            <?php endif; ?>
                        </div>
                        <div class="text-muted small">
                            Showing <?= number_format($offset + 1) ?>–<?= number_format(min($offset + $perPage, $totalCount)) ?>
                        </div>
                    </div>
                </div>
            <?php endif; ?>

            <!-- ══ JOB CARDS ══ -->
            <?php if (empty($jobs)): ?>
                <div class="empty-state">
                    <i class="fas fa-search fa-4x"></i>
                    <h5>No Jobs Found</h5>
                    <p class="mb-4">We couldn't find any jobs matching your criteria.<br>
                       Try adjusting your filters or search terms.</p>
                    <a href="<?= $categoryId ? '?category=' . $categoryId : '?' ?>" class="btn btn-primary">
                        <i class="fas fa-redo me-2"></i>Clear Filters
                    </a>
                </div>

            <?php else: ?>

                <?php foreach ($jobs as $job): ?>
                    <div class="job-card">

                        <!-- LEFT: job info -->
                        <div class="job-card-body">
                            <h5>
                                <a href="<?= url('job-details.php?id=' . $job['id']) ?>">
                                    <?= htmlspecialchars($job['title']) ?>
                                </a>
                            </h5>

                            <div class="company-name">
                                <i class="fas fa-building"></i>
                                <?= htmlspecialchars($job['company']) ?>
                            </div>

                            <div class="job-meta-info">
                                <?php if (!empty($job['category_name'])): ?>
                                    <span>
                                        <i class="fas fa-tag"></i>
                                        <?= htmlspecialchars($job['category_name']) ?>
                                    </span>
                                <?php endif; ?>

                                <?php if (!empty($job['location'])): ?>
                                    <span>
                                        <i class="fas fa-map-marker-alt"></i>
                                        <?= htmlspecialchars($job['location']) ?>
                                    </span>
                                <?php endif; ?>

                                <?php if (!empty($job['work_mode_name'])): ?>
                                    <span>
                                        <i class="fas fa-laptop-house"></i>
                                        <?= htmlspecialchars($job['work_mode_name']) ?>
                                    </span>
                                <?php endif; ?>

                                <?php if (!empty($job['experience_level_name'])): ?>
                                    <span>
                                        <i class="fas fa-user-tie"></i>
                                        <?= htmlspecialchars($job['experience_level_name']) ?>
                                    </span>
                                <?php endif; ?>

                                <?php if (!empty($job['salary_min'])): ?>
                                    <span class="salary-badge">
                                        <i class="fas fa-rupee-sign"></i>
                                        <?php if (!empty($job['salary_max'])): ?>
                                            <?= number_format($job['salary_min']) ?> – <?= number_format($job['salary_max']) ?>
                                        <?php else: ?>
                                            <?= number_format($job['salary_min']) ?>+
                                        <?php endif; ?>
                                    </span>
                                <?php endif; ?>
                            </div>
                        </div>

                        <!-- RIGHT: date + button -->
                        <div class="job-card-action">
                            <div class="job-posted-date">
                                <i class="far fa-clock"></i>
                                <?php
                                $days = floor((time() - strtotime($job['posted_date'])) / 86400);
                                if ($days == 0)     echo 'Today';
                                elseif ($days == 1) echo 'Yesterday';
                                elseif ($days < 7)  echo $days . 'd ago';
                                else                echo date('M d, Y', strtotime($job['posted_date']));
                                ?>
                            </div>
                            <a href="<?= url('job-details.php?id=' . $job['id']) ?>"
                               class="btn btn-primary btn-sm">
                                View Details
                            </a>
                        </div>

                    </div>
                <?php endforeach; ?>

                <!-- Pagination -->
                <?php if ($totalPages > 1): ?>
                    <div class="pagination">

                        <?php if ($page > 1): ?>
                            <a href="?<?= http_build_query(array_merge($currentFilters, ['page' => $page - 1])) ?>"
                               class="page-link">
                                <i class="fas fa-chevron-left me-1"></i>Previous
                            </a>
                        <?php endif; ?>

                        <?php
                        $startPage = max(1, $page - 2);
                        $endPage   = min($totalPages, $page + 2);
                        if ($startPage > 1): ?>
                            <a href="?<?= http_build_query(array_merge($currentFilters, ['page' => 1])) ?>"
                               class="page-link">1</a>
                            <?php if ($startPage > 2): ?>
                                <span class="page-ellipsis">…</span>
                            <?php endif; ?>
                        <?php endif; ?>

                        <?php for ($i = $startPage; $i <= $endPage; $i++): ?>
                            <a href="?<?= http_build_query(array_merge($currentFilters, ['page' => $i])) ?>"
                               class="page-link <?= $i === $page ? 'active' : '' ?>">
                                <?= $i ?>
                            </a>
                        <?php endfor; ?>

                        <?php if ($endPage < $totalPages): ?>
                            <?php if ($endPage < $totalPages - 1): ?>
                                <span class="page-ellipsis">…</span>
                            <?php endif; ?>
                            <a href="?<?= http_build_query(array_merge($currentFilters, ['page' => $totalPages])) ?>"
                               class="page-link"><?= $totalPages ?></a>
                        <?php endif; ?>

                        <?php if ($page < $totalPages): ?>
                            <a href="?<?= http_build_query(array_merge($currentFilters, ['page' => $page + 1])) ?>"
                               class="page-link">
                                Next <i class="fas fa-chevron-right ms-1"></i>
                            </a>
                        <?php endif; ?>

                    </div>
                <?php endif; ?>

            <?php endif; ?>
        </div>
        <!-- /.col-lg-9 -->

    </div>
    <!-- /.row -->
</div>
<!-- /.container -->

<?php include 'includes/footer.php'; ?>
