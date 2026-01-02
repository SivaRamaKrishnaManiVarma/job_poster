<?php
require_once 'includes/config.php';
require_once 'includes/functions.php';

$pageTitle = 'Available Jobs';

$filters = [
    'search' => $_GET['search'] ?? '',
    'category' => $_GET['category'] ?? '',
    'location' => $_GET['location'] ?? ''
];

$jobs = getJobs($pdo, $filters);
$categories = $pdo->query("SELECT DISTINCT category FROM jobs WHERE is_active = 1 AND category IS NOT NULL ORDER BY category")->fetchAll(PDO::FETCH_COLUMN);

include 'includes/header.php';
?>

<div class="row mb-4">
    <div class="col-md-12">
        <h1 class="mb-4">Latest Job Opportunities</h1>
        
        <!-- Search and Filter -->
        <form method="GET" class="row g-3 mb-4">
            <div class="col-md-4">
                <input type="text" name="search" class="form-control" placeholder="Search jobs..." value="<?php echo sanitize($filters['search']); ?>">
            </div>
            <div class="col-md-3">
                <select name="category" class="form-select">
                    <option value="">All Categories</option>
                    <?php foreach ($categories as $cat): ?>
                        <option value="<?php echo sanitize($cat); ?>" <?php echo $filters['category'] == $cat ? 'selected' : ''; ?>>
                            <?php echo sanitize($cat); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-md-3">
                <input type="text" name="location" class="form-control" placeholder="Location" value="<?php echo sanitize($filters['location']); ?>">
            </div>
            <div class="col-md-2">
                <button type="submit" class="btn btn-primary w-100">Search</button>
            </div>
        </form>

        <!-- Job Listings -->
        <?php if (count($jobs) > 0): ?>
            <div class="row">
                <?php foreach ($jobs as $job): ?>
                    <div class="col-md-6 mb-4">
                        <div class="card h-100">
                            <div class="card-body">
                                <h5 class="card-title"><?php echo sanitize($job['title']); ?></h5>
                                <h6 class="card-subtitle mb-3 text-muted"><?php echo sanitize($job['company']); ?></h6>
                                
                                <?php if ($job['location']): ?>
                                    <p class="mb-1"><small><strong>📍 Location:</strong> <?php echo sanitize($job['location']); ?></small></p>
                                <?php endif; ?>
                                
                                <?php if ($job['category']): ?>
                                    <p class="mb-1"><small><strong>🏷️ Category:</strong> <?php echo sanitize($job['category']); ?></small></p>
                                <?php endif; ?>
                                
                                <?php if ($job['eligibility']): ?>
                                    <p class="mb-2"><small><strong>✓ Eligibility:</strong> <?php echo sanitize($job['eligibility']); ?></small></p>
                                <?php endif; ?>
                                
                                <p class="card-text"><?php echo nl2br(sanitize(substr($job['description'], 0, 150))); ?><?php echo strlen($job['description']) > 150 ? '...' : ''; ?></p>
                                
                                <p class="text-muted mb-3"><small>Posted: <?php echo date('d M Y', strtotime($job['posted_date'])); ?></small></p>
                                
                                <a href="<?php echo sanitize($job['job_link']); ?>" target="_blank" rel="noopener noreferrer" class="btn btn-primary">
                                    Apply Now →
                                </a>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php else: ?>
            <div class="alert alert-info">No jobs found matching your criteria.</div>
        <?php endif; ?>
    </div>
</div>

<?php include 'includes/footer.php'; ?>
