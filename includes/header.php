<?php
// Ensure config is loaded
if (!defined('BASE_PATH')) {
    require_once __DIR__ . '/config.php';
}

// Include auth functions only for non-admin pages
if (!defined('ADMIN_AREA') && file_exists(__DIR__ . '/auth-functions.php')) {
    require_once __DIR__ . '/auth-functions.php';
}

// Get counts for navigation badges
$activeJobsCount = $pdo->query("
    SELECT COUNT(*) FROM jobs 
    WHERE is_active = 1 
    AND (application_deadline IS NULL OR application_deadline >= CURDATE())
")->fetchColumn();

$expiredJobsCount = $pdo->query("
    SELECT COUNT(*) FROM jobs 
    WHERE is_active = 1 
    AND application_deadline IS NOT NULL 
    AND application_deadline < CURDATE()
")->fetchColumn();

// Get current page info for SEO
$currentPage = basename($_SERVER['PHP_SELF']);
$protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';
$currentUrl = $protocol . '://' . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'];

// Default SEO values
$pageTitle = $pageTitle ?? 'Job Portal - Find Your Dream Job | Government & Private Sector Opportunities in India';
$metaDescription = $metaDescription ?? 'Discover thousands of verified job opportunities from top companies and government departments across India. Search jobs by location, experience, salary, and category. Apply online today!';
$metaKeywords = $metaKeywords ?? 'job portal India, government jobs, private jobs, job search, career opportunities, employment, job vacancies, sarkari naukri, job openings, fresher jobs, experienced jobs';
$canonicalUrl = $canonicalUrl ?? $currentUrl;
$ogImage = BASE_URL . '/assets/images/og-image.jpg';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    
    <!-- Primary Meta Tags -->
    <title><?php echo htmlspecialchars($pageTitle); ?></title>
    <meta name="title" content="<?php echo htmlspecialchars($pageTitle); ?>">
    <meta name="description" content="<?php echo htmlspecialchars($metaDescription); ?>">
    <meta name="keywords" content="<?php echo htmlspecialchars($metaKeywords); ?>">
    <meta name="author" content="Job Portal">
    <meta name="robots" content="index, follow, max-image-preview:large, max-snippet:-1, max-video-preview:-1">
    <link rel="canonical" href="<?php echo htmlspecialchars($canonicalUrl); ?>">
    
    <!-- Alternate for Mobile -->
    <link rel="alternate" media="only screen and (max-width: 640px)" href="<?php echo htmlspecialchars($canonicalUrl); ?>">
    
    <!-- Open Graph / Facebook -->
    <meta property="og:type" content="website">
    <meta property="og:url" content="<?php echo htmlspecialchars($currentUrl); ?>">
    <meta property="og:site_name" content="Job Portal">
    <meta property="og:title" content="<?php echo htmlspecialchars($pageTitle); ?>">
    <meta property="og:description" content="<?php echo htmlspecialchars($metaDescription); ?>">
    <meta property="og:image" content="<?php echo htmlspecialchars($ogImage); ?>">
    <meta property="og:image:width" content="1200">
    <meta property="og:image:height" content="630">
    <meta property="og:locale" content="en_IN">
    
    <!-- Twitter Card -->
    <meta name="twitter:card" content="summary_large_image">
    <meta name="twitter:url" content="<?php echo htmlspecialchars($currentUrl); ?>">
    <meta name="twitter:title" content="<?php echo htmlspecialchars($pageTitle); ?>">
    <meta name="twitter:description" content="<?php echo htmlspecialchars($metaDescription); ?>">
    <meta name="twitter:image" content="<?php echo htmlspecialchars($ogImage); ?>">
    <meta name="twitter:creator" content="@JobPortal">
    
    <!-- Additional SEO Meta Tags -->
    <meta name="theme-color" content="#6366f1">
    <meta name="msapplication-TileColor" content="#6366f1">
    <meta name="apple-mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-status-bar-style" content="black-translucent">
    <meta name="format-detection" content="telephone=no">
    <meta name="color-scheme" content="light dark">
    
    <!-- Geo Tags -->
    <meta name="geo.region" content="IN">
    <meta name="geo.placename" content="India">
    
    <!-- Favicon -->
    <link rel="icon" type="image/x-icon" href="<?php echo BASE_PATH; ?>/assets/images/favicon.ico">
    <link rel="apple-touch-icon" sizes="180x180" href="<?php echo BASE_PATH; ?>/assets/images/apple-touch-icon.png">
    <link rel="icon" type="image/png" sizes="32x32" href="<?php echo BASE_PATH; ?>/assets/images/favicon-32x32.png">
    <link rel="icon" type="image/png" sizes="16x16" href="<?php echo BASE_PATH; ?>/assets/images/favicon-16x16.png">
    <link rel="manifest" href="<?php echo BASE_PATH; ?>/site.webmanifest">
    
    <!-- Preconnect to External Resources -->
    <link rel="preconnect" href="https://cdn.jsdelivr.net">
    <link rel="dns-prefetch" href="https://cdn.jsdelivr.net">
    
    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&family=Outfit:wght@500;600;700;800&display=swap" rel="stylesheet">

    <!-- Stylesheets -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-9ndCyUaIbzAi2FUVXJi0CjmCapSmO7SnpJef0486qhLnuZ2cdeRhO02iuK6FUUVM" crossorigin="anonymous">
    <!-- Font Awesome for Icons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="<?php echo BASE_PATH; ?>/assets/css/styles.css">
    
    <!-- Structured Data - WebSite Schema -->
    <script type="application/ld+json">
    {
        "@context": "https://schema.org",
        "@type": "WebSite",
        "name": "Job Portal",
        "alternateName": "Find Jobs Portal India",
        "url": "<?php echo BASE_URL; ?>/",
        "description": "India's leading job portal for government and private sector opportunities",
        "publisher": {
            "@type": "Organization",
            "name": "Job Portal",
            "logo": {
                "@type": "ImageObject",
                "url": "<?php echo BASE_URL; ?>/assets/images/logo.png"
            }
        },
        "potentialAction": {
            "@type": "SearchAction",
            "target": {
                "@type": "EntryPoint",
                "urlTemplate": "<?php echo BASE_URL; ?>/?search={search_term_string}"
            },
            "query-input": "required name=search_term_string"
        }
    }
    </script>
    
    <!-- Structured Data - Organization Schema -->
    <script type="application/ld+json">
    {
        "@context": "https://schema.org",
        "@type": "Organization",
        "name": "Job Portal",
        "url": "<?php echo BASE_URL; ?>/",
        "logo": "<?php echo BASE_URL; ?>/assets/images/logo.png",
        "description": "Connecting job seekers with employers across India",
        "address": {
            "@type": "PostalAddress",
            "addressCountry": "IN"
        },
        "sameAs": [
            "https://www.facebook.com/jobportal",
            "https://twitter.com/jobportal",
            "https://www.linkedin.com/company/jobportal"
        ]
    }
    </script>
    
    <!-- Structured Data - BreadcrumbList -->
    <script type="application/ld+json">
    {
        "@context": "https://schema.org",
        "@type": "BreadcrumbList",
        "itemListElement": [
            {
                "@type": "ListItem",
                "position": 1,
                "name": "Home",
                "item": "<?php echo BASE_URL; ?>/"
            }
            <?php if (isset($breadcrumbs) && is_array($breadcrumbs)): ?>
                <?php foreach ($breadcrumbs as $index => $crumb): ?>
                ,{
                    "@type": "ListItem",
                    "position": <?php echo $index + 2; ?>,
                    "name": "<?php echo htmlspecialchars($crumb['name']); ?>",
                    "item": "<?php echo htmlspecialchars($crumb['url']); ?>"
                }
                <?php endforeach; ?>
            <?php endif; ?>
        ]
    }
    </script>
    
    <?php if (isset($jobSchema) && $jobSchema): ?>
    <!-- Job Posting Schema -->
    <script type="application/ld+json">
    <?php echo $jobSchema; ?>
    </script>
    <?php endif; ?>
    
    <!-- Google Analytics (Add your tracking ID) -->
    <!-- <script async src="https://www.googletagmanager.com/gtag/js?id=G-XXXXXXXXXX"></script>
    <script>
        window.dataLayer = window.dataLayer || [];
        function gtag(){dataLayer.push(arguments);}
        gtag('js', new Date());
        gtag('config', 'G-XXXXXXXXXX');
    </script> -->
</head>

<body>
    <!-- Public Navigation -->
    <nav class="navbar navbar-expand-lg navbar-light bg-white sticky-top shadow-sm">
        <div class="container">
            <!-- Logo/Brand -->
            <a class="navbar-brand fw-bold" href="<?php echo BASE_PATH; ?>/" aria-label="Job Portal Home">
                <i class="fas fa-briefcase me-1 text-primary"></i> Job Portal
            </a>
            
            <!-- Mobile Toggle -->
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav" aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
                <span class="navbar-toggler-icon"></span>
            </button>
            
            <!-- Navigation Menu -->
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav ms-auto align-items-lg-center">
                    <!-- Active Jobs -->
                    <li class="nav-item">
                        <a class="nav-link <?php echo $currentPage == 'index.php' ? 'active fw-bold' : ''; ?>" 
                           href="<?php echo BASE_PATH; ?>/"
                           aria-label="View Active Jobs">
                            <i class="fas fa-home me-1"></i> Active Jobs
                            <?php if ($activeJobsCount > 0): ?>
                                <span class="badge bg-success rounded-pill ms-1" title="<?php echo $activeJobsCount; ?> active jobs">
                                    <?php echo $activeJobsCount; ?>
                                </span>
                            <?php endif; ?>
                        </a>
                    </li>
                    
                    <!-- Expired Jobs -->
                    <li class="nav-item">
                        <a class="nav-link <?php echo $currentPage == 'archive.php' ? 'active fw-bold' : ''; ?>" 
                           href="<?php echo BASE_PATH; ?>/archive.php"
                           aria-label="View Expired Jobs">
                            <i class="fas fa-history me-1"></i> Expired Jobs
                            <?php if ($expiredJobsCount > 0): ?>
                                <span class="badge bg-secondary rounded-pill ms-1" title="<?php echo $expiredJobsCount; ?> expired jobs">
                                    <?php echo $expiredJobsCount > 100 ? '100+' : $expiredJobsCount; ?>
                                </span>
                            <?php endif; ?>
                        </a>
                    </li>
                    
                    <!-- About -->
                    <!-- <li class="nav-item">
                        <a class="nav-link <?php echo $currentPage == 'about.php' ? 'active fw-bold' : ''; ?>" 
                           href="<?php echo BASE_PATH; ?>/about.php"
                           aria-label="About Us">
                            ℹ️ About
                        </a>
                    </li> -->
                    
                    <!-- Contact -->
                    <!-- <li class="nav-item">
                        <a class="nav-link <?php echo $currentPage == 'contact.php' ? 'active fw-bold' : ''; ?>" 
                           href="<?php echo BASE_PATH; ?>/contact.php"
                           aria-label="Contact Us">
                            📧 Contact
                        </a>
                    </li> -->                    
                    <!-- Candidate Authentication Menu -->
                    <?php if (function_exists('isCandidateLoggedIn') && isCandidateLoggedIn()): ?>
                        <!-- Logged In Candidate -->
                        <li class="nav-item dropdown">
                            <a class="nav-link dropdown-toggle d-flex align-items-center" href="#" id="userDropdown" 
                               data-bs-toggle="dropdown" aria-expanded="false" aria-label="User Menu">
                                <i class="fas fa-user-circle me-1"></i>
                                <span class="d-none d-lg-inline"><?php echo htmlspecialchars($_SESSION['candidate_name'] ?? 'User'); ?></span>
                            </a>
                            <ul class="dropdown-menu dropdown-menu-end shadow border-0" aria-labelledby="userDropdown">
                                <li>
                                    <a class="dropdown-item" href="<?php echo url('profile/dashboard.php'); ?>">
                                        <i class="fas fa-tachometer-alt me-2 text-primary"></i>Dashboard
                                    </a>
                                </li>
                                <li>
                                    <a class="dropdown-item" href="<?php echo url('profile/edit-profile.php'); ?>">
                                        <i class="fas fa-user-edit me-2 text-info"></i>Edit Profile
                                    </a>
                                </li>
                                <!-- <li>
                                    <a class="dropdown-item" href="<?php echo url('profile/saved-jobs.php'); ?>">
                                        <i class="fas fa-bookmark me-2 text-warning"></i>Saved Jobs
                                    </a>
                                </li> -->
                                <li><hr class="dropdown-divider"></li>
                                <li>
                                    <a class="dropdown-item text-danger" href="<?php echo url('auth/logout.php'); ?>">
                                        <i class="fas fa-sign-out-alt me-2"></i>Logout
                                    </a>
                                </li>
                            </ul>
                        </li>
                    <?php else: ?>
                        <!-- Not Logged In -->
                        <li class="nav-item">
                            <a class="nav-link" href="<?php echo url('auth/login.php'); ?>" aria-label="Login">
                                <i class="fas fa-sign-in-alt me-1"></i>Login
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="btn btn-primary btn-sm ms-lg-2" href="<?php echo url('auth/register.php'); ?>" aria-label="Register">
                                <i class="fas fa-user-plus me-1"></i>Register
                            </a>
                        </li>
                    <?php endif; ?>
                </ul>
            </div>
        </div>
    </nav>

    <!-- Main Content -->
    <main class="container my-4" role="main">
