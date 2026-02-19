<?php
require_once '../includes/config.php';
require_once '../includes/session-check.php';
require_once '../includes/master-data-functions.php';
require_once '../includes/job-matching-functions.php';

$userId = $candidateId;

// Get filter parameters
$selectedTier = $_GET['tier'] ?? 'all';
$selectedLocation = $_GET['location'] ?? '';
$selectedWorkMode = $_GET['work_mode'] ?? '';
$selectedSalaryMin = $_GET['salary_min'] ?? '';
$selectedSalaryMax = $_GET['salary_max'] ?? '';
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$perPage = 12;
$offset = ($page - 1) * $perPage;

// Build filters array
$filters = [];
if ($selectedLocation) $filters['location'] = [$selectedLocation];
if ($selectedWorkMode) $filters['work_mode'] = [$selectedWorkMode];
if ($selectedSalaryMin) $filters['salary_min'] = $selectedSalaryMin;
if ($selectedSalaryMax) $filters['salary_max'] = $selectedSalaryMax;

// Get recommendations
$recommendations = getRecommendedJobs($userId, $pdo, [
    'tier' => $selectedTier,
    'limit' => $perPage,
    'offset' => $offset,
    'filters' => $filters
]);

$jobs = $recommendations['jobs'];
$totalCount = $recommendations['total_count'];
$totalPages = ceil($totalCount / $perPage);

// Get counts for tabs
$jobCounts = getRecommendedJobCounts($userId, $pdo);

// Get unique locations and work modes for filters
$locationsStmt = $pdo->query("
    SELECT DISTINCT location 
    FROM jobs 
    WHERE is_active = 1 
    AND location IS NOT NULL 
    AND location != '' 
    ORDER BY location
");
$locations = $locationsStmt->fetchAll(PDO::FETCH_COLUMN);

$workModesStmt = $pdo->query("
    SELECT id, mode_name, icon 
    FROM master_work_modes 
    WHERE is_active = 1 
    ORDER BY display_order
");
$workModes = $workModesStmt->fetchAll(PDO::FETCH_ASSOC);

$pageTitle = 'Recommended Jobs - Job Portal';
include '../includes/header.php';
?>

<style>
.filter-card {
    background: white;
    border: 1px solid #e5e7eb;
    border-radius: 8px;
    padding: 20px;
    margin-bottom: 20px;
}
.filter-label {
    font-size: 14px;
    font-weight: 600;
    color: #374151;
    margin-bottom: 8px;
    display: block;
}
.tier-tabs {
    display: flex;
    gap: 8px;
    flex-wrap: wrap;
    margin-bottom: 24px;
}
.tier-tab {
    padding: 12px 20px;
    background: white;
    border: 2px solid #e5e7eb;
    border-radius: 8px;
    cursor: pointer;
    transition: all 0.2s;
    text-decoration: none;
    color: #374151;
    font-weight: 500;
    display: flex;
    align-items: center;
    gap: 8px;
}
.tier-tab:hover {
    border-color: #3b82f6;
    background: #eff6ff;
    color: #3b82f6;
}
.tier-tab.active {
    background: #3b82f6;
    border-color: #3b82f6;
    color: white;
}
.tier-tab .badge {
    background: rgba(0,0,0,0.1);
    padding: 2px 8px;
    border-radius: 12px;
    font-size: 12px;
}
.tier-tab.active .badge {
    background: rgba(255,255,255,0.2);
}
.job-card {
    background: white;
    border: 1px solid #e5e7eb;
    border-radius: 8px;
    padding: 20px;
    height: 100%;
    transition: all 0.2s;
    position: relative;
}
.job-card:hover {
    border-color: #3b82f6;
    box-shadow: 0 4px 12px rgba(0,0,0,0.1);
    transform: translateY(-2px);
}
.match-badge {
    position: absolute;
    top: 16px;
    right: 16px;
    padding: 6px 12px;
    border-radius: 20px;
    font-size: 13px;
    font-weight: 600;
    color: white;
}
.match-badge-excellent { background: #10b981; }
.match-badge-great { background: #3b82f6; }
.match-badge-good { background: #f59e0b; }
.match-badge-fair { background: #9ca3af; }
.high-priority-banner {
    background: linear-gradient(135deg, #fef3c7 0%, #fde047 100%);
    padding: 4px 12px;
    border-radius: 6px;
    font-size: 12px;
    font-weight: 600;
    color: #92400e;
    display: inline-flex;
    align-items: center;
    gap: 4px;
    margin-bottom: 12px;
}
.pagination {
    display: flex;
    justify-content: center;
    gap: 8px;
    margin-top: 32px;
}
.page-link {
    padding: 8px 16px;
    background: white;
    border: 1px solid #e5e7eb;
    border-radius: 6px;
    color: #374151;
    text-decoration: none;
    transition: all 0.2s;
}
.page-link:hover {
    border-color: #3b82f6;
    background: #eff6ff;
    color: #3b82f6;
}
.page-link.active {
    background: #3b82f6;
    border-color: #3b82f6;
    color: white;
}
.page-link.disabled {
    opacity: 0.5;
    pointer-events: none;
}
.empty-state {
    text-align: center;
    padding: 60px 20px;
    color: #6b7280;
}
.filter-reset-btn {
    color: #ef4444;
    font-size: 14px;
    text-decoration: none;
    font-weight: 500;
}
.filter-reset-btn:hover {
    text-decoration: underline;
}
</style>

<div class="container py-4">
    <!-- Page Header -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h2 class="fw-bold mb-1">Recommended Jobs</h2>
            <p class="text-muted mb-0">Jobs personalized for your profile</p>
        </div>
        <a href="<?= url('profile/dashboard.php') ?>" class="btn btn-outline-secondary">
            <i class="fas fa-arrow-left me-2"></i>Back to Dashboard
        </a>
    </div>

    <!-- Filters Card -->
    <div class="filter-card">
        <form method="GET" action="" id="filterForm">
            <input type="hidden" name="tier" value="<?= htmlspecialchars($selectedTier) ?>">
            
            <div class="row g-3">
                <div class="col-md-3">
                    <label class="filter-label">Location</label>
                    <select name="location" class="form-select" onchange="document.getElementById('filterForm').submit()">
                        <option value="">All Locations</option>
                        <?php foreach ($locations as $loc): ?>
                            <option value="<?= htmlspecialchars($loc) ?>" <?= $selectedLocation === $loc ? 'selected' : '' ?>>
                                <?= htmlspecialchars($loc) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="col-md-3">
                    <label class="filter-label">Work Mode</label>
                    <select name="work_mode" class="form-select" onchange="document.getElementById('filterForm').submit()">
                        <option value="">All Modes</option>
                        <?php foreach ($workModes as $mode): ?>
                            <option value="<?= $mode['id'] ?>" <?= $selectedWorkMode == $mode['id'] ? 'selected' : '' ?>>
                                <?= htmlspecialchars($mode['mode_name']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="col-md-2">
                    <label class="filter-label">Min Salary (₹)</label>
                    <input type="number" name="salary_min" class="form-control" 
                           placeholder="e.g. 30000" 
                           value="<?= htmlspecialchars($selectedSalaryMin) ?>"
                           onchange="document.getElementById('filterForm').submit()">
                </div>

                <div class="col-md-2">
                    <label class="filter-label">Max Salary (₹)</label>
                    <input type="number" name="salary_max" class="form-control" 
                           placeholder="e.g. 100000" 
                           value="<?= htmlspecialchars($selectedSalaryMax) ?>"
                           onchange="document.getElementById('filterForm').submit()">
                </div>

                <div class="col-md-2 d-flex align-items-end">
                    <?php if ($selectedLocation || $selectedWorkMode || $selectedSalaryMin || $selectedSalaryMax): ?>
                        <a href="?tier=<?= htmlspecialchars($selectedTier) ?>" class="filter-reset-btn w-100 text-center">
                            <i class="fas fa-times-circle me-1"></i>Clear Filters
                        </a>
                    <?php endif; ?>
                </div>
            </div>
        </form>
    </div>

    <!-- Tier Tabs -->
    <div class="tier-tabs">
        <a href="?tier=all" class="tier-tab <?= $selectedTier === 'all' ? 'active' : '' ?>">
            <i class="fas fa-list"></i>
            All Jobs
            <span class="badge"><?= $jobCounts['total'] ?></span>
        </a>
        <a href="?tier=high_priority" class="tier-tab <?= $selectedTier === 'high_priority' ? 'active' : '' ?>">
            <i class="fas fa-star"></i>
            High Priority
            <span class="badge"><?= $jobCounts['high_priority'] ?></span>
        </a>
        <a href="?tier=best" class="tier-tab <?= $selectedTier === 'best' ? 'active' : '' ?>">
            <i class="fas fa-check-circle"></i>
            Best Matches
            <span class="badge"><?= $jobCounts['best'] ?></span>
        </a>
        <a href="?tier=strong" class="tier-tab <?= $selectedTier === 'strong' ? 'active' : '' ?>">
            <i class="fas fa-thumbs-up"></i>
            Strong Matches
            <span class="badge"><?= $jobCounts['strong'] ?></span>
        </a>
        <a href="?tier=other" class="tier-tab <?= $selectedTier === 'other' ? 'active' : '' ?>">
            <i class="fas fa-briefcase"></i>
            Other
            <span class="badge"><?= $jobCounts['other'] ?></span>
        </a>
    </div>

    <!-- Results Count -->
    <div class="mb-3">
        <p class="text-muted mb-0">
            Showing <strong><?= count($jobs) ?></strong> of <strong><?= $totalCount ?></strong> jobs
            <?php if ($selectedTier !== 'all'): ?>
                in <strong><?= ucfirst(str_replace('_', ' ', $selectedTier)) ?></strong>
            <?php endif; ?>
        </p>
    </div>

    <!-- Jobs Grid -->
    <?php if (empty($jobs)): ?>
        <div class="empty-state">
            <i class="fas fa-search fa-4x mb-3"></i>
            <h5 class="mb-3">No jobs found</h5>
            <p class="mb-4">Try adjusting your filters or exploring other categories</p>
            <div class="d-flex justify-content-center gap-3">
                <a href="?tier=all" class="btn btn-primary">View All Jobs</a>
                <a href="<?= url('profile/edit-profile.php') ?>" class="btn btn-outline-primary">Update Profile</a>
            </div>
        </div>
    <?php else: ?>
        <div class="row g-3">
            <?php foreach ($jobs as $item): 
                $job = $item['job'];
                $score = $item['match_score'];
                $scoreData = formatMatchScore($score);
            ?>
                <div class="col-md-6 col-lg-4">
                    <div class="job-card">
                        <?php if ($item['is_high_priority']): ?>
                            <div class="high-priority-banner">
                                <i class="fas fa-star"></i>
                                High Priority
                            </div>
                        <?php endif; ?>

                        <span class="match-badge match-badge-<?= $scoreData['class'] ?>" 
                              title="Match Score: <?= $score ?>%">
                            <?= $score ?>%
                        </span>

                        <h6 class="fw-bold mb-2 mt-3">
                            <a href="<?= url('job-details.php?id=' . $job['id']) ?>" 
                               class="text-dark text-decoration-none">
                                <?= htmlspecialchars($job['title']) ?>
                            </a>
                        </h6>

                        <p class="text-muted mb-2 small">
                            <i class="fas fa-building me-1"></i>
                            <?= htmlspecialchars($job['company']) ?>
                        </p>

                        <div class="d-flex flex-wrap gap-2 small text-muted mb-3">
                            <?php if (!empty($job['category_name'])): ?>
                                <span>
                                    <i class="<?= htmlspecialchars($job['category_icon']) ?> me-1"></i>
                                    <?= htmlspecialchars($job['category_name']) ?>
                                </span>
                            <?php endif; ?>
                            
                            <?php if (!empty($job['location'])): ?>
                                <span>
                                    <i class="fas fa-map-marker-alt me-1"></i>
                                    <?= htmlspecialchars($job['location']) ?>
                                </span>
                            <?php endif; ?>
                        </div>

                        <?php if (!empty($job['work_mode_name'])): ?>
                            <div class="mb-2">
                                <span class="badge bg-light text-dark">
                                    <i class="fas fa-laptop-house me-1"></i>
                                    <?= htmlspecialchars($job['work_mode_name']) ?>
                                </span>
                            </div>
                        <?php endif; ?>

                        <?php if (!empty($job['salary_min']) || !empty($job['salary_max'])): ?>
                            <p class="text-success small mb-2 fw-semibold">
                                <i class="fas fa-rupee-sign me-1"></i>
                                <?php if (!empty($job['salary_min']) && !empty($job['salary_max'])): ?>
                                    <?= number_format($job['salary_min']) ?> - <?= number_format($job['salary_max']) ?>
                                <?php elseif (!empty($job['salary_min'])): ?>
                                    <?= number_format($job['salary_min']) ?>+
                                <?php endif; ?>
                            </p>
                        <?php endif; ?>

                        <small class="text-muted d-block mb-3">
                            <i class="far fa-clock me-1"></i>
                            Posted <?= date('M d, Y', strtotime($job['posted_date'])) ?>
                        </small>

                        <a href="<?= url('job-details.php?id=' . $job['id']) ?>" 
                           class="btn btn-sm btn-primary w-100">
                            View Details <i class="fas fa-arrow-right ms-1"></i>
                        </a>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>

        <!-- Pagination -->
        <?php if ($totalPages > 1): ?>
            <div class="pagination">
                <?php if ($page > 1): ?>
                    <a href="?tier=<?= $selectedTier ?>&page=<?= $page - 1 ?><?= $selectedLocation ? '&location=' . urlencode($selectedLocation) : '' ?><?= $selectedWorkMode ? '&work_mode=' . $selectedWorkMode : '' ?><?= $selectedSalaryMin ? '&salary_min=' . $selectedSalaryMin : '' ?><?= $selectedSalaryMax ? '&salary_max=' . $selectedSalaryMax : '' ?>" 
                       class="page-link">
                        <i class="fas fa-chevron-left"></i> Previous
                    </a>
                <?php endif; ?>

                <?php for ($i = max(1, $page - 2); $i <= min($totalPages, $page + 2); $i++): ?>
                    <a href="?tier=<?= $selectedTier ?>&page=<?= $i ?><?= $selectedLocation ? '&location=' . urlencode($selectedLocation) : '' ?><?= $selectedWorkMode ? '&work_mode=' . $selectedWorkMode : '' ?><?= $selectedSalaryMin ? '&salary_min=' . $selectedSalaryMin : '' ?><?= $selectedSalaryMax ? '&salary_max=' . $selectedSalaryMax : '' ?>" 
                       class="page-link <?= $i === $page ? 'active' : '' ?>">
                        <?= $i ?>
                    </a>
                <?php endfor; ?>

                <?php if ($page < $totalPages): ?>
                    <a href="?tier=<?= $selectedTier ?>&page=<?= $page + 1 ?><?= $selectedLocation ? '&location=' . urlencode($selectedLocation) : '' ?><?= $selectedWorkMode ? '&work_mode=' . $selectedWorkMode : '' ?><?= $selectedSalaryMin ? '&salary_min=' . $selectedSalaryMin : '' ?><?= $selectedSalaryMax ? '&salary_max=' . $selectedSalaryMax : '' ?>" 
                       class="page-link">
                        Next <i class="fas fa-chevron-right"></i>
                    </a>
                <?php endif; ?>
            </div>
        <?php endif; ?>
    <?php endif; ?>
</div>

<?php include '../includes/footer.php'; ?>
