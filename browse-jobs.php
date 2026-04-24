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

<!-- Page Header -->
<div class="browse-header">
    <div class="container">
        <div class="animate-up">
            <?php if ($categoryInfo): ?>
                <div class="d-flex align-items-center">
                    <div class="category-icon-wrapper me-4">
                         <i class="<?= htmlspecialchars($categoryInfo['icon'] ?: 'fas fa-briefcase') ?> fa-2x"></i>
                    </div>
                    <div>
                        <h1 class="mb-1"><?= htmlspecialchars($categoryInfo['category_name']) ?> Jobs</h1>
                        <p class="mb-0"><?= number_format($totalCount) ?> opportunities available</p>
                    </div>
                </div>
            <?php else: ?>
                <h1>Browse All Jobs</h1>
                <p class="mb-0">Discover <?= number_format($totalCount) ?>+ opportunities available today</p>
            <?php endif; ?>
        </div>
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
