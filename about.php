<?php
require_once 'includes/config.php';
require_once 'includes/functions.php';

$pageTitle = 'About Us - Job Portal | Your Career Partner';
$metaDescription = 'Learn about Job Portal - India\'s leading platform for government and private sector job opportunities. Discover our mission, vision, and commitment to your career growth.';
$metaKeywords = 'about job portal, career platform, government jobs, private jobs, job search India';
$canonicalUrl = 'http://' . $_SERVER['HTTP_HOST'] . '/job_poster/about.php';

include 'includes/header.php';
?>

<style>
.about-hero {
    background: linear-gradient(135deg, var(--primary) 0%, var(--primary-hover) 100%);
    color: white;
    padding: 4rem 0;
    margin-bottom: 3rem;
    border-radius: var(--radius-xl);
    position: relative;
    overflow: hidden;
}

.about-hero::before {
    content: '';
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    background: url('data:image/svg+xml,<svg width="100" height="100" xmlns="http://www.w3.org/2000/svg"><defs><pattern id="grid" width="40" height="40" patternUnits="userSpaceOnUse"><path d="M 40 0 L 0 0 0 40" fill="none" stroke="rgba(255,255,255,0.05)" stroke-width="1"/></pattern></defs><rect width="100" height="100" fill="url(%23grid)"/></svg>');
    opacity: 0.5;
}

.about-hero-content {
    position: relative;
    z-index: 1;
}

.about-section {
    background: white;
    border-radius: var(--radius-xl);
    padding: 2.5rem;
    margin-bottom: 2rem;
    box-shadow: var(--shadow);
    border: 1px solid var(--gray-200);
}

.section-title {
    font-size: 2rem;
    font-weight: 700;
    color: var(--gray-900);
    margin-bottom: 1.5rem;
    position: relative;
    padding-bottom: 1rem;
}

.section-title::after {
    content: '';
    position: absolute;
    bottom: 0;
    left: 0;
    width: 60px;
    height: 4px;
    background: linear-gradient(90deg, var(--primary), var(--primary-hover));
    border-radius: 2px;
}

.feature-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
    gap: 2rem;
    margin-top: 2rem;
}

.feature-card {
    text-align: center;
    padding: 2rem;
    background: var(--gray-50);
    border-radius: var(--radius-lg);
    transition: var(--transition);
}

.feature-card:hover {
    transform: translateY(-5px);
    box-shadow: var(--shadow-lg);
    background: white;
}

.feature-icon {
    font-size: 3rem;
    margin-bottom: 1rem;
}

.feature-card h3 {
    font-size: 1.25rem;
    font-weight: 700;
    color: var(--gray-900);
    margin-bottom: 0.75rem;
}

.feature-card p {
    color: var(--gray-600);
    margin: 0;
}

.stats-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
    gap: 2rem;
    margin: 2rem 0;
}

.stat-card {
    text-align: center;
    padding: 2rem;
    background: linear-gradient(135deg, var(--primary-light), white);
    border-radius: var(--radius-lg);
    border: 2px solid var(--primary);
}

.stat-number {
    font-size: 3rem;
    font-weight: 800;
    color: var(--primary);
    line-height: 1;
}

.stat-label {
    font-size: 1rem;
    color: var(--gray-600);
    margin-top: 0.5rem;
    font-weight: 600;
}

.value-list {
    list-style: none;
    padding: 0;
}

.value-list li {
    padding: 1rem;
    margin-bottom: 1rem;
    background: var(--gray-50);
    border-left: 4px solid var(--primary);
    border-radius: var(--radius);
    display: flex;
    align-items: flex-start;
    gap: 1rem;
}

.value-icon {
    font-size: 1.5rem;
    flex-shrink: 0;
}

.cta-section {
    background: linear-gradient(135deg, var(--primary), var(--primary-hover));
    color: white;
    padding: 3rem;
    border-radius: var(--radius-xl);
    text-align: center;
    margin-top: 3rem;
}

.cta-section h2 {
    font-size: 2rem;
    margin-bottom: 1rem;
}

.cta-section p {
    font-size: 1.125rem;
    opacity: 0.9;
    margin-bottom: 2rem;
}

@media (max-width: 768px) {
    .about-hero {
        padding: 2rem 0;
    }
    
    .section-title {
        font-size: 1.5rem;
    }
    
    .feature-grid,
    .stats-grid {
        grid-template-columns: 1fr;
    }
}
</style>

<div class="container">
    <!-- Hero Section -->
    <div class="about-hero">
        <div class="about-hero-content text-center">
            <h1 style="font-size: 3rem; margin-bottom: 1rem; font-weight: 800;">About Job Portal</h1>
            <p style="font-size: 1.25rem; opacity: 0.9; max-width: 700px; margin: 0 auto;">
                Your trusted partner in finding the perfect career opportunities across India
            </p>
        </div>
    </div>

    <!-- Mission & Vision -->
    <div class="about-section">
        <h2 class="section-title">Our Mission</h2>
        <p style="font-size: 1.125rem; line-height: 1.8; color: var(--gray-700); margin-bottom: 2rem;">
            At Job Portal, our mission is to bridge the gap between talented job seekers and leading employers across India. 
            We are committed to providing a comprehensive, user-friendly platform that simplifies the job search process and 
            empowers individuals to achieve their career goals.
        </p>

        <h2 class="section-title" style="margin-top: 3rem;">Our Vision</h2>
        <p style="font-size: 1.125rem; line-height: 1.8; color: var(--gray-700);">
            To become India's most trusted and innovative job portal, revolutionizing the way people discover and apply for 
            career opportunities. We envision a future where every job seeker has equal access to quality employment opportunities, 
            regardless of their background or location.
        </p>
    </div>

    <!-- What We Offer -->
    <div class="about-section">
        <h2 class="section-title">What We Offer</h2>
        <div class="feature-grid">
            <div class="feature-card">
                <div class="feature-icon">🏢</div>
                <h3>Government Jobs</h3>
                <p>Latest updates on government sector opportunities from central and state departments</p>
            </div>
            <div class="feature-card">
                <div class="feature-icon">💼</div>
                <h3>Private Sector Jobs</h3>
                <p>Exclusive openings from top private companies and startups across industries</p>
            </div>
            <div class="feature-card">
                <div class="feature-icon">🎓</div>
                <h3>All Experience Levels</h3>
                <p>Opportunities for freshers, mid-level professionals, and senior executives</p>
            </div>
            <div class="feature-card">
                <div class="feature-icon">📍</div>
                <h3>Pan-India Coverage</h3>
                <p>Jobs across all states and major cities in India, including remote positions</p>
            </div>
            <div class="feature-card">
                <div class="feature-icon">🔔</div>
                <h3>Real-Time Updates</h3>
                <p>Get instant notifications about new job postings matching your profile</p>
            </div>
            <div class="feature-card">
                <div class="feature-icon">✅</div>
                <h3>Verified Listings</h3>
                <p>All job postings are verified to ensure authenticity and quality</p>
            </div>
        </div>
    </div>

    <!-- Statistics -->
    <div class="about-section">
        <h2 class="section-title">Our Impact</h2>
        <div class="stats-grid">
            <?php
            $totalJobs = $pdo->query("SELECT COUNT(*) FROM jobs")->fetchColumn();
            $activeJobs = $pdo->query("SELECT COUNT(*) FROM jobs WHERE is_active = 1 AND (application_deadline IS NULL OR application_deadline >= CURDATE())")->fetchColumn();
            $companies = $pdo->query("SELECT COUNT(DISTINCT company) FROM jobs")->fetchColumn();
            $categories = $pdo->query("SELECT COUNT(*) FROM master_job_categories WHERE is_active = 1")->fetchColumn();
            ?>
            <div class="stat-card">
                <div class="stat-number"><?php echo $totalJobs; ?>+</div>
                <div class="stat-label">Total Jobs Listed</div>
            </div>
            <div class="stat-card">
                <div class="stat-number"><?php echo $activeJobs; ?></div>
                <div class="stat-label">Active Opportunities</div>
            </div>
            <div class="stat-card">
                <div class="stat-number"><?php echo $companies; ?>+</div>
                <div class="stat-label">Partner Companies</div>
            </div>
            <div class="stat-card">
                <div class="stat-number"><?php echo $categories; ?></div>
                <div class="stat-label">Job Categories</div>
            </div>
        </div>
    </div>

    <!-- Core Values -->
    <div class="about-section">
        <h2 class="section-title">Our Core Values</h2>
        <ul class="value-list">
            <li>
                <span class="value-icon">🎯</span>
                <div>
                    <strong style="display: block; margin-bottom: 0.25rem; color: var(--gray-900);">Integrity</strong>
                    <span style="color: var(--gray-600);">We maintain the highest standards of honesty and transparency in all our dealings</span>
                </div>
            </li>
            <li>
                <span class="value-icon">🤝</span>
                <div>
                    <strong style="display: block; margin-bottom: 0.25rem; color: var(--gray-900);">Trust</strong>
                    <span style="color: var(--gray-600);">Building lasting relationships through reliable and verified job listings</span>
                </div>
            </li>
            <li>
                <span class="value-icon">⚡</span>
                <div>
                    <strong style="display: block; margin-bottom: 0.25rem; color: var(--gray-900);">Innovation</strong>
                    <span style="color: var(--gray-600);">Continuously improving our platform with cutting-edge technology</span>
                </div>
            </li>
            <li>
                <span class="value-icon">🌟</span>
                <div>
                    <strong style="display: block; margin-bottom: 0.25rem; color: var(--gray-900);">Excellence</strong>
                    <span style="color: var(--gray-600);">Committed to delivering superior user experience and quality service</span>
                </div>
            </li>
        </ul>
    </div>

    <!-- CTA Section -->
    <div class="cta-section">
        <h2>Ready to Find Your Dream Job?</h2>
        <p>Explore thousands of verified job opportunities from top employers</p>
        <div style="display: flex; gap: 1rem; justify-content: center; flex-wrap: wrap;">
            <a href="index.php" class="btn btn-light btn-lg" style="font-weight: 600;">
                🔍 Browse Active Jobs
            </a>
            <a href="contact.php" class="btn btn-outline-light btn-lg" style="font-weight: 600;">
                📧 Contact Us
            </a>
        </div>
    </div>
</div>

<?php include 'includes/footer.php'; ?>
