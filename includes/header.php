<?php
// Get counts for navigation badges
$activeJobsCount = $pdo->query("
    SELECT COUNT(*) FROM jobs 
    WHERE is_active = 1 
    AND (application_deadline IS NULL OR application_deadline >= CURDATE())
")->fetchColumn();

$archivedJobsCount = $pdo->query("
    SELECT COUNT(*) FROM jobs 
    WHERE is_active = 1 
    AND application_deadline IS NOT NULL 
    AND application_deadline < CURDATE()
")->fetchColumn();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    
    <!-- SEO Meta Tags -->
    <title><?php echo $pageTitle ?? 'Job Portal - Find Your Dream Job | Government & Private Sector Opportunities'; ?></title>
    <meta name="description" content="<?php echo $metaDescription ?? 'Discover thousands of verified job opportunities from top companies and government departments across India. Search jobs by location, experience, and category.'; ?>">
    <meta name="keywords" content="<?php echo $metaKeywords ?? 'job portal, jobs in India, government jobs, private jobs, job search, career opportunities, employment, job vacancies'; ?>">
    <meta name="author" content="Job Portal">
    <meta name="robots" content="index, follow">
    <link rel="canonical" href="<?php echo $canonicalUrl ?? 'http://' . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI']; ?>">
    
    <!-- Open Graph / Facebook -->
    <meta property="og:type" content="website">
    <meta property="og:url" content="<?php echo $canonicalUrl ?? 'http://' . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI']; ?>">
    <meta property="og:title" content="<?php echo $pageTitle ?? 'Job Portal - Find Your Dream Job'; ?>">
    <meta property="og:description" content="<?php echo $metaDescription ?? 'Discover thousands of verified job opportunities from top companies across India.'; ?>">
    <meta property="og:image" content="http://<?php echo $_SERVER['HTTP_HOST']; ?>/job_poster/assets/images/og-image.jpg">
    
    <!-- Twitter -->
    <meta property="twitter:card" content="summary_large_image">
    <meta property="twitter:url" content="<?php echo $canonicalUrl ?? 'http://' . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI']; ?>">
    <meta property="twitter:title" content="<?php echo $pageTitle ?? 'Job Portal - Find Your Dream Job'; ?>">
    <meta property="twitter:description" content="<?php echo $metaDescription ?? 'Discover thousands of verified job opportunities from top companies across India.'; ?>">
    <meta property="twitter:image" content="http://<?php echo $_SERVER['HTTP_HOST']; ?>/job_poster/assets/images/og-image.jpg">
    
    <!-- Favicon -->
    <link rel="icon" type="image/x-icon" href="assets/images/favicon.ico">
    <link rel="apple-touch-icon" sizes="180x180" href="assets/images/apple-touch-icon.png">
    
    <!-- Stylesheets -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="assets/css/styles.css">
    
    <!-- Structured Data (JSON-LD) -->
    <script type="application/ld+json">
    {
        "@context": "https://schema.org",
        "@type": "WebSite",
        "name": "Job Portal",
        "url": "http://<?php echo $_SERVER['HTTP_HOST']; ?>/job_poster/",
        "description": "India's leading job portal for government and private sector opportunities",
        "potentialAction": {
            "@type": "SearchAction",
            "target": "http://<?php echo $_SERVER['HTTP_HOST']; ?>/job_poster/index.php?search={search_term_string}",
            "query-input": "required name=search_term_string"
        }
    }
    </script>
    
    <?php if (isset($jobSchema) && $jobSchema): ?>
    <!-- Job Posting Schema -->
    <script type="application/ld+json">
    <?php echo $jobSchema; ?>
    </script>
    <?php endif; ?>
</head>

<body>
    <!-- Public Navigation -->
    <nav class="navbar navbar-expand-lg navbar-light bg-white sticky-top shadow-sm">
        <div class="container">
            <a class="navbar-brand fw-bold" href="index.php">
                🎯 Job Portal
            </a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
           <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav ms-auto">
                    <li class="nav-item">
                        <a class="nav-link <?php echo basename($_SERVER['PHP_SELF']) == 'index.php' ? 'active fw-bold' : ''; ?>" href="index.php">
                            🏠 Active Jobs
                            <?php if ($activeJobsCount > 0): ?>
                                <span class="badge bg-success rounded-pill ms-1"><?php echo $activeJobsCount; ?></span>
                            <?php endif; ?>
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link <?php echo basename($_SERVER['PHP_SELF']) == 'archive.php' ? 'active fw-bold' : ''; ?>" href="archive.php">
                            📚 Archive
                            <?php if ($archivedJobsCount > 0): ?>
                                <span class="badge bg-secondary rounded-pill ms-1"><?php echo $archivedJobsCount; ?></span>
                            <?php endif; ?>
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link <?php echo basename($_SERVER['PHP_SELF']) == 'about.php' ? 'active fw-bold' : ''; ?>" href="about.php">
                            ℹ️ About
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link <?php echo basename($_SERVER['PHP_SELF']) == 'contact.php' ? 'active fw-bold' : ''; ?>" href="contact.php">
                            📧 Contact
                        </a>
                    </li>
                </ul>
            </div>

        </div>
    </nav>

    <!-- Main Content -->
    <main class="container my-4">
