<?php
require_once 'includes/config.php';
require_once 'includes/functions.php';
require_once 'includes/master-data-functions.php';

// ===== SMART REFERRER TRACKING =====
$referrer = $_GET['ref'] ?? '';
$fromDashboard = ($referrer === 'dashboard');
$fromRecommended = ($referrer === 'recommended');
$fromSaved = ($referrer === 'saved');
$fromBrowse = ($referrer === 'browse');

// Fallback: Check HTTP_REFERER
if (!$fromDashboard && !$fromRecommended && !$fromSaved && !$fromBrowse && !empty($_SERVER['HTTP_REFERER'])) {
    $refererUrl = $_SERVER['HTTP_REFERER'];
    if (strpos($refererUrl, '/profile/dashboard.php') !== false) {
        $fromDashboard = true;
    } elseif (strpos($refererUrl, '/profile/recommended-jobs.php') !== false) {
        $fromRecommended = true;
    } elseif (strpos($refererUrl, '/profile/saved-jobs.php') !== false) {
        $fromSaved = true;
    } elseif (strpos($refererUrl, '/browse-jobs.php') !== false) {
        $fromBrowse = true;
    }
}

// Determine back navigation
if ($fromDashboard) {
    $backUrl = url('profile/dashboard.php');
    $backText = 'Back to Dashboard';
    $backIcon = 'fa-tachometer-alt';
} elseif ($fromRecommended) {
    $backUrl = url('profile/recommended-jobs.php');
    $backText = 'Back to Recommendations';
    $backIcon = 'fa-star';
} elseif ($fromSaved) {
    $backUrl = url('profile/saved-jobs.php');
    $backText = 'Back to Saved Jobs';
    $backIcon = 'fa-bookmark';
} else {
    $backUrl = url('browse-jobs.php');
    $backText = 'Back to Browse Jobs';
    $backIcon = 'fa-search';
}

// Get job by slug or ID (fallback for old links)
$slug = isset($_GET['slug']) ? trim($_GET['slug']) : '';
$jobId = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if (empty($slug) && $jobId === 0) {
    header('Location: ' . url(''));
    exit;
}

// Prepare query based on what's available
if (!empty($slug)) {
    $whereClause = "j.slug = ? AND j.is_active = 1";
    $param = $slug;
} else {
    $whereClause = "j.id = ? AND j.is_active = 1";
    $param = $jobId;
}

// Get job details with ALL master data
$stmt = $pdo->prepare("SELECT j.*, 
    c.category_name, c.icon as category_icon,
    w.mode_name, w.icon as work_mode_icon,
    e.type_name, e.icon as employment_icon,
    ex.level_name, ex.icon as experience_icon,
    s.state_name,
    d.department_name, d.department_type,
    q.qualification_name, q.qualification_level
    FROM jobs j
    LEFT JOIN master_job_categories c ON j.job_category_id = c.id
    LEFT JOIN master_work_modes w ON j.work_mode_id = w.id
    LEFT JOIN master_employment_types e ON j.employment_type_id = e.id
    LEFT JOIN master_experience_levels ex ON j.experience_level_id = ex.id
    LEFT JOIN master_states s ON j.state_id = s.id
    LEFT JOIN master_departments d ON j.department_id = d.id
    LEFT JOIN master_qualifications q ON j.min_qualification_id = q.id
    WHERE $whereClause");
$stmt->execute([$param]);
$job = $stmt->fetch();

// If job not found or inactive, redirect
if (!$job) {
    header('Location: ' . url(''));
    exit;
}

// If accessed by ID, redirect to slug URL (for SEO)
if ($jobId > 0 && !empty($job['slug'])) {
    header('Location: ' . url('jobs/' . urlencode($job['slug'])), true, 301);
    exit;
}

// Check if job is expired
$isExpired = $job['application_deadline'] && strtotime($job['application_deadline']) < strtotime(date('Y-m-d'));

// Calculate days until deadline
$daysUntilDeadline = null;
if ($job['application_deadline']) {
    $deadline = strtotime($job['application_deadline']);
    $today = strtotime(date('Y-m-d'));
    $daysUntilDeadline = floor(($deadline - $today) / (60 * 60 * 24));
}

// Get related jobs (same category, excluding current job)
$relatedJobs = [];
if ($job['job_category_id']) {
    $stmt = $pdo->prepare("SELECT j.slug, j.id, j.title, j.company, j.location, j.posted_date,
        j.total_vacancies, j.application_deadline, j.salary_min, j.description,
        c.category_name, c.icon as category_icon,
        w.mode_name, w.icon as work_mode_icon,
        e.type_name, e.icon as employment_icon,
        ex.level_name, ex.icon as experience_icon
        FROM jobs j
        LEFT JOIN master_job_categories c ON j.job_category_id = c.id
        LEFT JOIN master_work_modes w ON j.work_mode_id = w.id
        LEFT JOIN master_employment_types e ON j.employment_type_id = e.id
        LEFT JOIN master_experience_levels ex ON j.experience_level_id = ex.id
        WHERE j.job_category_id = ? 
        AND j.id != ?
        AND j.is_active = 1
        AND (j.application_deadline IS NULL OR j.application_deadline >= CURDATE())
        ORDER BY j.posted_date DESC
        LIMIT 6");
    $stmt->execute([$job['job_category_id'], $job['id']]);
    $relatedJobs = $stmt->fetchAll();
}

// Update view count
$updateViews = $pdo->prepare("UPDATE jobs SET view_count = view_count + 1 WHERE id = ?");
$updateViews->execute([$job['id']]);

// SEO Meta Tags
$pageTitle = $job['title'] . ' at ' . $job['company'] . ' - Job Portal';
$metaDescription = substr(strip_tags($job['description']), 0, 155) . '... Apply now for this ' . ($job['type_name'] ?: 'job') . ' position in ' . $job['location'];

// ===== TIMELINE PROGRESSION CALCULATION =====
$currentDate = strtotime(date('Y-m-d'));

// Define timeline dates
$timelineDates = [];
if ($job['notification_date']) {
    $timelineDates[] = [
        'date' => $job['notification_date'],
        'label' => 'Notification Release Date'
    ];
}
if ($job['posted_date']) {
    $timelineDates[] = [
        'date' => $job['posted_date'],
        'label' => 'Application Start Date'
    ];
}
if ($job['application_deadline']) {
    $timelineDates[] = [
        'date' => $job['application_deadline'],
        'label' => 'Last Date to Apply'
    ];
}
if ($job['exam_date']) {
    $timelineDates[] = [
        'date' => $job['exam_date'],
        'label' => 'Examination Date'
    ];
}
if ($job['result_date']) {
    $timelineDates[] = [
        'date' => $job['result_date'],
        'label' => 'Expected Result Date'
    ];
}

include 'includes/header.php';
?>

<style>
/* =====================================================
   CLEAN BLUE JOB DETAILS PAGE - NO GRADIENTS
   Matches Dashboard Style
   ===================================================== */

:root {
    --primary-dark: #1e3a8a;
    --primary: #2563eb;
    --primary-light: #3b82f6;
    --blue-50: #eff6ff;
    --blue-100: #dbeafe;
    --blue-200: #bfdbfe;
    --blue-300: #93c5fd;
    --success: #059669;
    --success-light: #10b981;
    --warning: #f59e0b;
    --danger: #dc2626;
    --gray-50: #f9fafb;
    --gray-100: #f3f4f6;
    --gray-200: #e5e7eb;
    --gray-300: #d1d5db;
    --gray-400: #9ca3af;
    --gray-500: #6b7280;
    --gray-600: #4b5563;
    --gray-700: #374151;
    --gray-800: #1f2937;
    --gray-900: #111827;
}

* { box-sizing: border-box; }

body {
    font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
    line-height: 1.6;
    color: var(--gray-700);
    background: var(--gray-50);
    margin: 0;
    padding: 0;
}

.job-details-container {
    max-width: 1200px;
    margin: 2rem auto;
    padding: 0 1rem;
}

/* =====================================================
   BACK BUTTON
   ===================================================== */

.back-link {
    display: inline-flex;
    align-items: center;
    gap: 0.5rem;
    color: var(--primary);
    text-decoration: none;
    font-weight: 600;
    margin-bottom: 2rem;
    padding: 0.75rem 1.5rem;
    background: white;
    border-radius: 8px;
    transition: all 0.3s ease;
    border: 2px solid var(--blue-200);
}

.back-link:hover {
    background: var(--blue-50);
    border-color: var(--primary);
    transform: translateX(-5px);
}

/* =====================================================
   CONTENT WRAPPER
   ===================================================== */

.content-wrapper {
    display: grid;
    grid-template-columns: 1fr;
    gap: 2rem;
    align-items: start;
}

@media (min-width: 992px) {
    .content-wrapper {
        grid-template-columns: 1fr 380px;
    }
    .sidebar { order: 2; }
    .main-content { order: 1; }
}

/* =====================================================
   QUICK STATS BAR
   ===================================================== */

.quick-stats {
    display: grid;
    grid-template-columns: 1fr;
    gap: 1rem;
    margin-bottom: 2rem;
}

@media (min-width: 768px) {
    .quick-stats {
        grid-template-columns: repeat(2, 1fr);
    }
}

@media (min-width: 992px) {
    .quick-stats {
        grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
    }
}

.stat-card {
    background: white;
    color: var(--gray-900);
    padding: 1.5rem;
    border-radius: 8px;
    text-align: center;
    box-shadow: 0 2px 8px rgba(0, 0, 0, 0.08);
    transition: all 0.3s ease;
    border: 2px solid var(--gray-200);
}

.stat-card:hover {
    transform: translateY(-4px);
    box-shadow: 0 8px 20px rgba(37, 99, 235, 0.15);
    border-color: var(--blue-300);
}

.stat-card.salary { border-color: var(--success); background: #f0fdf4; }
.stat-card.vacancies { border-color: var(--primary); background: var(--blue-50); }
.stat-card.deadline { border-color: var(--warning); background: #fffbeb; }
.stat-card.views { border-color: var(--gray-400); background: var(--gray-100); }

.stat-number {
    font-size: 2rem;
    font-weight: 800;
    margin: 0;
    color: var(--gray-900);
}

.stat-label {
    font-size: 0.875rem;
    color: var(--gray-600);
    margin: 0.5rem 0 0 0;
    font-weight: 600;
}

/* =====================================================
   JOB HEADER
   ===================================================== */

.job-header {
    background: white;
    border-radius: 8px;
    padding: 2rem;
    margin-bottom: 2rem;
    box-shadow: 0 2px 8px rgba(0, 0, 0, 0.08);
    border: 2px solid var(--gray-200);
}

.company-info {
    display: flex;
    align-items: flex-start;
    gap: 1.5rem;
    margin-bottom: 1.5rem;
}

@media (max-width: 767px) {
    .company-info {
        flex-direction: column;
        align-items: center;
        text-align: center;
    }
}

.company-logo-large {
    width: 90px;
    height: 90px;
    background: var(--primary-dark);
    color: white;
    border-radius: 12px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 2rem;
    font-weight: 800;
    flex-shrink: 0;
    box-shadow: 0 4px 12px rgba(30, 58, 138, 0.3);
}

.job-title {
    font-size: 2rem;
    font-weight: 800;
    color: var(--primary-dark);
    margin: 0 0 0.5rem 0;
    line-height: 1.3;
}

.company-name {
    font-size: 1.125rem;
    color: var(--gray-600);
    margin: 0;
    display: flex;
    align-items: center;
    gap: 0.5rem;
}

.company-website {
    color: var(--primary);
    text-decoration: none;
    font-weight: 600;
}

.company-website:hover {
    text-decoration: underline;
}

.job-meta {
    display: flex;
    flex-wrap: wrap;
    gap: 0.75rem;
    padding-top: 1.5rem;
    border-top: 2px solid var(--gray-200);
}

.meta-tag {
    display: inline-flex;
    align-items: center;
    gap: 0.5rem;
    padding: 0.625rem 1.25rem;
    background: var(--gray-100);
    border-radius: 20px;
    font-size: 0.9375rem;
    font-weight: 600;
    color: var(--gray-700);
    transition: all 0.3s ease;
    border: 2px solid var(--gray-200);
}

.meta-tag:hover {
    background: var(--blue-50);
    border-color: var(--primary);
}

/* =====================================================
   MAIN CONTENT
   ===================================================== */

.main-content {
    background: white;
    border-radius: 8px;
    padding: 2rem;
    box-shadow: 0 2px 8px rgba(0, 0, 0, 0.08);
    border: 2px solid var(--gray-200);
}

.content-section {
    margin-bottom: 3rem;
}

.section-title {
    font-size: 1.5rem;
    font-weight: 700;
    color: var(--gray-900);
    margin: 0 0 1.5rem 0;
    padding-bottom: 0.75rem;
    display: flex;
    align-items: center;
    gap: 0.75rem;
    border-bottom: 3px solid var(--primary);
}

.job-description {
    font-size: 1.0625rem;
    line-height: 1.8;
    color: var(--gray-700);
}

/* =====================================================
   TIMELINE
   ===================================================== */

.timeline {
    position: relative;
    padding-left: 2.25rem;
    margin-top: 1rem;
}

.timeline::before {
    content: '';
    position: absolute;
    left: 0.6rem;
    top: 0.15rem;
    bottom: 0.15rem;
    width: 2px;
    background: var(--gray-200);
    border-radius: 999px;
}

.timeline-item {
    position: relative;
    padding: 0 0 1.75rem 0;
}

.timeline-item:last-child { padding-bottom: 0; }

.timeline-item::before {
    content: '';
    position: absolute;
    left: -1.1rem;
    top: 0.35rem;
    width: 14px;
    height: 14px;
    border-radius: 50%;
    background: white;
    border: 3px solid var(--gray-300);
}

.timeline-item.passed::before {
    background: var(--success);
    border-color: var(--success);
}

.timeline-item.current::before {
    background: var(--primary);
    border-color: var(--primary);
}

.timeline-date {
    margin: 0;
    font-size: 1.05rem;
    font-weight: 700;
    color: var(--gray-900);
}

.timeline-item.passed .timeline-date { color: var(--success); }
.timeline-item.current .timeline-date { color: var(--primary); }

.timeline-label {
    margin: 0.1rem 0 0;
    font-size: 0.9rem;
    color: var(--gray-500);
}

.timeline-status {
    display: inline-flex;
    align-items: center;
    padding: 0.25rem 0.7rem;
    border-radius: 999px;
    font-size: 0.72rem;
    font-weight: 600;
    margin-top: 0.4rem;
    text-transform: uppercase;
    letter-spacing: 0.04em;
}

.timeline-item.passed .timeline-status {
    background: #dcfce7;
    color: #166534;
}

.timeline-item.current .timeline-status {
    background: var(--blue-100);
    color: var(--primary-dark);
}

.timeline-item.upcoming .timeline-status {
    background: var(--gray-200);
    color: var(--gray-700);
}

/* =====================================================
   INFO CARDS
   ===================================================== */

.info-cards, .details-grid {
    display: grid;
    grid-template-columns: 1fr;
    gap: 1.25rem;
}

@media (min-width: 768px) {
    .info-cards, .details-grid {
        grid-template-columns: repeat(auto-fit, minmax(240px, 1fr));
    }
}

.info-card {
    background: var(--gray-50);
    border: 2px solid var(--gray-200);
    border-radius: 8px;
    padding: 1.5rem;
    display: flex;
    align-items: flex-start;
    gap: 1rem;
    transition: all 0.3s ease;
}

.info-card:hover {
    border-color: var(--primary);
    background: var(--blue-50);
}

.info-card-icon { 
    font-size: 2.5rem; 
    flex-shrink: 0; 
}

.info-card-content h4 {
    margin: 0 0 0.5rem 0;
    font-size: 0.875rem;
    color: var(--gray-600);
    text-transform: uppercase;
    font-weight: 700;
}

.info-card-content p {
    margin: 0;
    font-size: 1.25rem;
    font-weight: 700;
    color: var(--gray-900);
}

.detail-item {
    padding: 1.5rem;
    background: var(--gray-50);
    border-radius: 8px;
    border-left: 4px solid var(--primary);
    transition: all 0.3s ease;
    border: 2px solid var(--gray-200);
    border-left-width: 4px;
}

.detail-item:hover {
    background: var(--blue-50);
    border-left-color: var(--primary-dark);
}

.detail-label {
    font-size: 0.8125rem;
    font-weight: 700;
    color: var(--gray-500);
    text-transform: uppercase;
    margin: 0 0 0.75rem 0;
}

.detail-value {
    font-size: 1.125rem;
    font-weight: 700;
    color: var(--gray-900);
    margin: 0;
    display: flex;
    align-items: center;
    gap: 0.5rem;
}

/* =====================================================
   FEE TABLE
   ===================================================== */

.fee-table {
    width: 100%;
    border-collapse: separate;
    border-spacing: 0;
    margin-top: 1rem;
    border-radius: 8px;
    overflow: hidden;
    border: 2px solid var(--gray-200);
}

.fee-table th {
    background: var(--primary-dark);
    color: white;
    padding: 1rem 1.5rem;
    font-weight: 700;
    text-align: left;
}

.fee-table td {
    padding: 1rem 1.5rem;
    font-weight: 600;
    background: white;
    border-bottom: 1px solid var(--gray-200);
}

.fee-table tr:last-child td {
    border-bottom: none;
}

.fee-amount {
    color: var(--primary);
    font-size: 1.25rem;
    font-weight: 800;
}

.fee-free {
    color: var(--success);
    font-weight: 800;
}

/* =====================================================
   SIDEBAR
   ===================================================== */

.sidebar {
    order: 2;
    position: sticky;
    top: 100px;
}

@media (max-width: 991px) {
    .sidebar {
        position: static;
        margin-top: 2rem;
    }
}

.apply-card {
    background: white;
    border-radius: 8px;
    padding: 2rem;
    box-shadow: 0 2px 8px rgba(0, 0, 0, 0.08);
    border: 2px solid var(--blue-200);
    margin-bottom: 1.5rem;
}

.apply-card-title {
    font-size: 1.375rem;
    font-weight: 800;
    color: var(--gray-900);
    margin: 0 0 1.75rem 0;
    text-align: center;
}

.apply-btn {
    display: block;
    width: 100%;
    padding: 1.25rem 1.5rem;
    background: var(--primary);
    color: white;
    text-align: center;
    text-decoration: none;
    border-radius: 8px;
    font-weight: 800;
    font-size: 1.125rem;
    transition: all 0.3s ease;
    border: none;
    cursor: pointer;
}

.apply-btn:hover {
    background: var(--primary-dark);
    transform: translateY(-2px);
    box-shadow: 0 4px 12px rgba(37, 99, 235, 0.3);
}

.apply-btn.disabled {
    background: var(--gray-400);
    cursor: not-allowed;
    transform: none;
}

.deadline-info {
    margin-top: 1.5rem;
    padding: 1.25rem;
    border-radius: 8px;
    font-size: 0.9375rem;
    font-weight: 600;
    border: 2px solid;
}

.deadline-info.urgent {
    background: #fffbeb;
    border-color: var(--warning);
    color: #92400e;
}

.deadline-info.normal {
    background: var(--blue-100);
    border-color: var(--primary);
    color: var(--primary-dark);
}

.deadline-info.expired {
    background: #fee2e2;
    border-color: var(--danger);
    color: #991b1b;
}

.share-section {
    margin-top: 2rem;
    padding-top: 2rem;
    border-top: 2px solid var(--gray-200);
}

.share-title {
    font-weight: 700;
    color: var(--gray-700);
    margin-bottom: 1rem;
}

.share-buttons {
    display: grid;
    grid-template-columns: repeat(2, 1fr);
    gap: 0.875rem;
}

@media (max-width: 767px) {
    .share-buttons { grid-template-columns: 1fr; }
}

.share-btn {
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 0.5rem;
    padding: 0.875rem;
    border-radius: 8px;
    text-decoration: none;
    font-weight: 700;
    font-size: 0.875rem;
    transition: all 0.3s ease;
    color: white;
}

.share-btn:hover { 
    transform: translateY(-2px); 
    box-shadow: 0 4px 12px rgba(0, 0, 0, 0.2); 
}

.share-btn.facebook { background: #1877f2; }
.share-btn.twitter { background: #1da1f2; }
.share-btn.linkedin { background: #0077b5; }
.share-btn.whatsapp { background: #25d366; }

.highlight-box {
    background: var(--primary-dark);
    color: white;
    padding: 1.75rem;
    border-radius: 8px;
    border: 2px solid var(--primary);
}

.highlight-box h4 {
    margin: 0 0 1rem 0;
    font-size: 1.125rem;
}

.highlight-box p {
    margin: 0.5rem 0;
}

/* =====================================================
   RELATED JOBS
   ===================================================== */

.related-section {
    margin-top: 4rem;
    padding: 3rem 2rem;
    background: white;
    border-radius: 8px;
    box-shadow: 0 2px 8px rgba(0, 0, 0, 0.08);
    border: 2px solid var(--gray-200);
}

.related-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 2rem;
    flex-wrap: wrap;
    gap: 1rem;
}

.related-title {
    font-size: 1.75rem;
    font-weight: 800;
    color: var(--gray-900);
    margin: 0;
}

.view-all-link {
    color: var(--primary);
    text-decoration: none;
    font-weight: 600;
}

.view-all-link:hover {
    text-decoration: underline;
}

.related-jobs-grid {
    display: grid;
    grid-template-columns: 1fr;
    gap: 1.5rem;
}

@media (min-width: 768px) {
    .related-jobs-grid {
        grid-template-columns: repeat(auto-fill, minmax(320px, 1fr));
    }
}

.related-job-card {
    background: var(--gray-50);
    border-radius: 8px;
    border: 2px solid var(--gray-200);
    transition: all 0.3s ease;
    text-decoration: none;
    overflow: hidden;
    padding: 1.5rem;
}

.related-job-card:hover {
    transform: translateY(-4px);
    box-shadow: 0 8px 20px rgba(37, 99, 235, 0.15);
    border-color: var(--primary);
    background: white;
}

.related-job-logo {
    width: 60px;
    height: 60px;
    background: var(--primary);
    color: white;
    border-radius: 8px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 1.5rem;
    font-weight: 800;
    margin-bottom: 1rem;
}

.related-job-content {
    display: flex;
    flex-direction: column;
    gap: 0.75rem;
}

.related-job-title {
    font-size: 1.125rem;
    font-weight: 700;
    color: var(--gray-900);
    margin: 0;
}

.related-job-company {
    color: var(--gray-600);
    font-size: 0.9375rem;
    margin: 0;
    display: flex;
    align-items: center;
    gap: 0.5rem;
}

.related-job-preview {
    color: var(--gray-600);
    font-size: 0.875rem;
    line-height: 1.6;
    margin: 0;
}

.related-job-info-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(120px, 1fr));
    gap: 0.75rem;
    margin: 0.5rem 0;
}

.info-item {
    display: flex;
    align-items: center;
    gap: 0.5rem;
    font-size: 0.875rem;
    color: var(--gray-600);
}

.info-icon {
    font-size: 1rem;
}

.related-job-meta {
    display: flex;
    flex-wrap: wrap;
    gap: 0.5rem;
    margin-top: 0.75rem;
}

.job-badge {
    display: inline-flex;
    align-items: center;
    gap: 0.25rem;
    padding: 0.375rem 0.75rem;
    border-radius: 999px;
    font-size: 0.75rem;
    font-weight: 600;
    border: 2px solid;
}

.job-badge-blue {
    background: var(--blue-50);
    color: var(--primary-dark);
    border-color: var(--blue-200);
}

.job-badge-teal {
    background: #f0fdfa;
    color: #0d9488;
    border-color: #99f6e4;
}

.job-badge-green {
    background: #f0fdf4;
    color: #166534;
    border-color: #bbf7d0;
}

.related-job-footer {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-top: 1rem;
    padding-top: 1rem;
    border-top: 1px solid var(--gray-200);
    font-size: 0.8125rem;
}

.posted-time {
    color: var(--gray-500);
}

.deadline-badge {
    padding: 0.25rem 0.75rem;
    border-radius: 999px;
    font-weight: 600;
}

.deadline-badge.warning {
    background: var(--blue-100);
    color: var(--primary-dark);
}

.deadline-badge.urgent {
    background: #fee2e2;
    color: #991b1b;
}

/* =====================================================
   MOBILE OPTIMIZATIONS
   ===================================================== */

@media (max-width: 767px) {
    .job-details-container { padding: 0 0.75rem; margin: 1rem auto; }
    .job-header { padding: 1.5rem; }
    .job-title { font-size: 1.5rem; }
    .main-content { padding: 1.5rem; }
    .content-section { margin-bottom: 2.5rem; }
    .stat-card { padding: 1.25rem; }
    .stat-number { font-size: 1.75rem; }
    body { padding-bottom: 90px; }
}
</style>

<div class="job-details-container">
    <!-- Smart Back Button -->
    <a href="<?= $backUrl ?>" class="back-link">
        <i class="fas <?= $backIcon ?>"></i> <?= $backText ?>
    </a>

    <!-- Quick Stats -->
    <?php if ($job['total_vacancies'] || $job['salary_min'] || $job['application_deadline'] || $job['view_count']): ?>
    <div class="quick-stats">
        <?php if ($job['total_vacancies']): ?>
        <div class="stat-card vacancies">
            <p class="stat-number"><?= number_format($job['total_vacancies']) ?></p>
            <p class="stat-label">Total Vacancies</p>
        </div>
        <?php endif; ?>

        <?php if ($job['salary_min']): ?>
        <div class="stat-card salary">
            <p class="stat-number">₹<?= number_format($job['salary_min']) ?></p>
            <p class="stat-label">Minimum Salary/Month</p>
        </div>
        <?php endif; ?>

        <?php if ($job['application_deadline'] && !$isExpired): ?>
        <div class="stat-card deadline">
            <p class="stat-number"><?= $daysUntilDeadline >= 0 ? $daysUntilDeadline : 0 ?></p>
            <p class="stat-label">Days Left to Apply</p>
        </div>
        <?php endif; ?>

        <?php if ($job['view_count']): ?>
        <div class="stat-card views">
            <p class="stat-number"><?= number_format($job['view_count']) ?></p>
            <p class="stat-label">Views</p>
        </div>
        <?php endif; ?>
    </div>
    <?php endif; ?>

    <!-- Job Header -->
    <div class="job-header">
        <div class="company-info">
            <div class="company-logo-large">
                <?= strtoupper(substr($job['company'], 0, 2)) ?>
            </div>
            <div class="job-title-section">
                <h1 class="job-title"><?= htmlspecialchars($job['title']) ?></h1>
                <p class="company-name">
                    <span>🏢 <?= htmlspecialchars($job['company']) ?></span>
                    <?php if ($job['official_website']): ?>
                        | <a href="<?= htmlspecialchars($job['official_website']) ?>" target="_blank" rel="noopener" class="company-website">Visit Website ↗</a>
                    <?php endif; ?>
                </p>
            </div>
        </div>

        <div class="job-meta">
            <?php if ($job['location']): ?>
                <span class="meta-tag">📍 <?= htmlspecialchars($job['location']) ?></span>
            <?php endif; ?>
            <?php if ($job['mode_name']): ?>
                <span class="meta-tag"><?= $job['work_mode_icon'] ?> <?= htmlspecialchars($job['mode_name']) ?></span>
            <?php endif; ?>
            <?php if ($job['type_name']): ?>
                <span class="meta-tag"><?= $job['employment_icon'] ?> <?= htmlspecialchars($job['type_name']) ?></span>
            <?php endif; ?>
            <?php if ($job['level_name']): ?>
                <span class="meta-tag"><?= $job['experience_icon'] ?> <?= htmlspecialchars($job['level_name']) ?></span>
            <?php endif; ?>
            <span class="meta-tag">📅 Posted <?= date('M d, Y', strtotime($job['posted_date'])) ?></span>
        </div>
    </div>

    <!-- Content Wrapper -->
    <div class="content-wrapper">
        <!-- Main Content -->
        <div class="main-content">
            <!-- Job Description -->
            <?php if ($job['description']): ?>
            <div class="content-section">
                <h2 class="section-title">📄 About This Role</h2>
                <div class="job-description">
                    <?= nl2br(htmlspecialchars($job['description'])) ?>
                </div>
            </div>
            <?php endif; ?>

            <!-- Important Dates -->
            <?php if (count($timelineDates) > 0): ?>
            <div class="content-section">
                <h2 class="section-title">📆 Important Dates</h2>
                <div class="timeline">
                    <?php foreach ($timelineDates as $item): ?>
                        <?php
                            $itemDate = strtotime($item['date']);
                            if ($itemDate < $currentDate) {
                                $statusClass = 'passed';
                                $statusText  = 'Completed';
                            } elseif (date('Y-m-d', $itemDate) === date('Y-m-d', $currentDate)) {
                                $statusClass = 'current';
                                $statusText  = 'Today';
                            } else {
                                $statusClass = 'upcoming';
                                $statusText  = 'Upcoming';
                            }
                        ?>
                        <div class="timeline-item <?= $statusClass ?>">
                            <p class="timeline-date"><?= date('F d, Y', $itemDate) ?></p>
                            <p class="timeline-label"><?= htmlspecialchars($item['label']) ?></p>
                            <span class="timeline-status"><?= strtoupper($statusText) ?></span>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
            <?php endif; ?>

            <!-- Application Fees -->
            <?php if ($job['application_fee_general'] !== null || $job['application_fee_obc'] !== null || $job['application_fee_sc_st'] !== null): ?>
            <div class="content-section">
                <h2 class="section-title">💳 Application Fee</h2>
                <table class="fee-table">
                    <thead>
                        <tr>
                            <th>Category</th>
                            <th>Fee Amount</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if ($job['application_fee_general'] !== null): ?>
                        <tr>
                            <td>General / OBC</td>
                            <td class="fee-amount <?= $job['application_fee_general'] == 0 ? 'fee-free' : '' ?>">
                                <?= $job['application_fee_general'] == 0 ? 'FREE' : '₹' . number_format($job['application_fee_general'], 2) ?>
                            </td>
                        </tr>
                        <?php endif; ?>

                        <?php if ($job['application_fee_obc'] !== null): ?>
                        <tr>
                            <td>OBC / EWS</td>
                            <td class="fee-amount <?= $job['application_fee_obc'] == 0 ? 'fee-free' : '' ?>">
                                <?= $job['application_fee_obc'] == 0 ? 'FREE' : '₹' . number_format($job['application_fee_obc'], 2) ?>
                            </td>
                        </tr>
                        <?php endif; ?>

                        <?php if ($job['application_fee_sc_st'] !== null): ?>
                        <tr>
                            <td>SC / ST / PWD</td>
                            <td class="fee-amount <?= $job['application_fee_sc_st'] == 0 ? 'fee-free' : '' ?>">
                                <?= $job['application_fee_sc_st'] == 0 ? 'FREE' : '₹' . number_format($job['application_fee_sc_st'], 2) ?>
                            </td>
                        </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
                <?php if ($job['payment_mode']): ?>
                <p style="margin-top: 1rem; color: var(--gray-600); font-size: 0.9375rem;">
                    <strong>Payment Mode:</strong> <?= htmlspecialchars($job['payment_mode']) ?>
                </p>
                <?php endif; ?>
            </div>
            <?php endif; ?>

            <!-- Age Eligibility -->
            <?php if ($job['age_limit_min'] || $job['age_limit_max']): ?>
            <div class="content-section">
                <h2 class="section-title">📅 Age Eligibility</h2>
                <div class="info-cards">
                    <?php if ($job['age_limit_min']): ?>
                    <div class="info-card">
                        <div class="info-card-icon">👤</div>
                        <div class="info-card-content">
                            <h4>Minimum Age</h4>
                            <p><?= $job['age_limit_min'] ?> Years</p>
                        </div>
                    </div>
                    <?php endif; ?>

                    <?php if ($job['age_limit_max']): ?>
                    <div class="info-card">
                        <div class="info-card-icon">👴</div>
                        <div class="info-card-content">
                            <h4>Maximum Age</h4>
                            <p><?= $job['age_limit_max'] ?> Years</p>
                        </div>
                    </div>
                    <?php endif; ?>
                </div>
            </div>
            <?php endif; ?>

            <!-- Job Details -->
            <div class="content-section">
                <h2 class="section-title">💼 Job Details</h2>
                <div class="details-grid">
                    <?php if ($job['category_name']): ?>
                    <div class="detail-item">
                        <p class="detail-label">Category</p>
                        <p class="detail-value">
                            <?= $job['category_icon'] ?>
                            <?= htmlspecialchars($job['category_name']) ?>
                        </p>
                    </div>
                    <?php endif; ?>

                    <?php if ($job['qualification_name']): ?>
                    <div class="detail-item">
                        <p class="detail-label">Qualification</p>
                        <p class="detail-value">
                            🎓 <?= htmlspecialchars($job['qualification_name']) ?>
                        </p>
                    </div>
                    <?php endif; ?>

                    <?php if ($job['department_name']): ?>
                    <div class="detail-item">
                        <p class="detail-label">Department</p>
                        <p class="detail-value">
                            🏛️ <?= htmlspecialchars($job['department_name']) ?>
                        </p>
                    </div>
                    <?php endif; ?>

                    <?php if ($job['state_name']): ?>
                    <div class="detail-item">
                        <p class="detail-label">State/Region</p>
                        <p class="detail-value">
                            📍 <?= htmlspecialchars($job['state_name']) ?>
                        </p>
                    </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <!-- Sidebar -->
        <div class="sidebar">
            <!-- Apply Card -->
            <div class="apply-card">
                <h3 class="apply-card-title">Ready to Apply?</h3>

                <?php if ($isExpired): ?>
                    <button class="apply-btn disabled" disabled>
                        ❌ Application Closed
                    </button>
                    <div class="deadline-info expired">
                        <strong>Applications Closed</strong><br>
                        Deadline was <?= date('M d, Y', strtotime($job['application_deadline'])) ?>
                    </div>
                <?php else: ?>
                    <a href="<?= htmlspecialchars($job['job_link']) ?>" 
                       target="_blank" 
                       rel="noopener noreferrer" 
                       class="apply-btn">
                        Apply Now →
                    </a>

                    <?php if ($job['application_deadline']): ?>
                        <div class="deadline-info <?= $daysUntilDeadline <= 3 ? 'urgent' : 'normal' ?>">
                            <strong>⏰ Deadline:</strong> <?= date('M d, Y', strtotime($job['application_deadline'])) ?>
                            <?php if ($daysUntilDeadline >= 0 && $daysUntilDeadline <= 7): ?>
                                <br><strong><?= $daysUntilDeadline ?> day<?= $daysUntilDeadline != 1 ? 's' : '' ?> left!</strong>
                            <?php endif; ?>
                        </div>
                    <?php endif; ?>
                <?php endif; ?>

                <!-- Share Section -->
                <div class="share-section">
                    <p class="share-title">📤 Share This Job</p>
                    <div class="share-buttons">
                        <a href="https://www.facebook.com/sharer/sharer.php?u=<?= urlencode(fullUrl($_SERVER['REQUEST_URI'])) ?>" 
                           target="_blank" 
                           class="share-btn facebook">
                            Facebook
                        </a>
                        <a href="https://twitter.com/intent/tweet?url=<?= urlencode(fullUrl($_SERVER['REQUEST_URI'])) ?>&text=<?= urlencode($job['title'] . ' at ' . $job['company']) ?>" 
                           target="_blank" 
                           class="share-btn twitter">
                            Twitter
                        </a>
                        <a href="https://www.linkedin.com/sharing/share-offsite/?url=<?= urlencode(fullUrl($_SERVER['REQUEST_URI'])) ?>" 
                           target="_blank" 
                           class="share-btn linkedin">
                            LinkedIn
                        </a>
                        <a href="https://wa.me/?text=<?= urlencode($job['title'] . ' at ' . $job['company'] . ' - ' . fullUrl($_SERVER['REQUEST_URI'])) ?>" 
                           target="_blank" 
                           class="share-btn whatsapp">
                            WhatsApp
                        </a>
                    </div>
                </div>
            </div>

            <!-- Quick Info -->
            <?php if ($job['total_vacancies'] || $job['salary_min']): ?>
            <div class="highlight-box">
                <h4>💡 Quick Info</h4>
                <?php if ($job['total_vacancies']): ?>
                <p>👥 <strong><?= number_format($job['total_vacancies']) ?></strong> vacancies</p>
                <?php endif; ?>
                <?php if ($job['salary_min']): ?>
                <p>💰 Salary from <strong>₹<?= number_format($job['salary_min']) ?></strong></p>
                <?php endif; ?>
            </div>
            <?php endif; ?>
        </div>
    </div>

    <!-- Related Jobs -->
    <?php if (count($relatedJobs) > 0): ?>
    <div class="related-section">
        <div class="related-header">
            <h2 class="related-title">🔗 Similar Jobs You Might Like</h2>
            <a href="<?= url('?category=' . $job['job_category_id']) ?>" class="view-all-link">
                View All <?= htmlspecialchars($job['category_name']) ?> Jobs →
            </a>
        </div>
        
        <div class="related-jobs-grid">
            <?php foreach ($relatedJobs as $relJob): ?>
                <a href="<?= url('jobs/' . urlencode($relJob['slug'] ?: 'job-' . $relJob['id'])) . '?ref=' . $referrer ?>" class="related-job-card">
                    <div class="related-job-logo">
                        <?= strtoupper(substr($relJob['company'], 0, 2)) ?>
                    </div>
                    
                    <div class="related-job-content">
                        <h3 class="related-job-title"><?= htmlspecialchars($relJob['title']) ?></h3>
                        <p class="related-job-company">
                            <span class="company-icon">🏢</span>
                            <?= htmlspecialchars($relJob['company']) ?>
                        </p>

                        <?php if ($relJob['description']): ?>
                        <p class="related-job-preview">
                            <?= htmlspecialchars(substr(strip_tags($relJob['description']), 0, 80)) ?>...
                        </p>
                        <?php endif; ?>

                        <div class="related-job-info-grid">
                            <?php if ($relJob['location']): ?>
                            <div class="info-item">
                                <span class="info-icon">📍</span>
                                <span><?= htmlspecialchars($relJob['location']) ?></span>
                            </div>
                            <?php endif; ?>
                            
                            <?php if ($relJob['total_vacancies']): ?>
                            <div class="info-item">
                                <span class="info-icon">👥</span>
                                <span><?= number_format($relJob['total_vacancies']) ?> Vacancies</span>
                            </div>
                            <?php endif; ?>
                            
                            <?php if ($relJob['salary_min']): ?>
                            <div class="info-item">
                                <span class="info-icon">💰</span>
                                <span>₹<?= number_format($relJob['salary_min']) ?>/mo</span>
                            </div>
                            <?php endif; ?>
                        </div>

                        <div class="related-job-meta">
                            <?php if ($relJob['mode_name']): ?>
                                <span class="job-badge job-badge-blue">
                                    <?= $relJob['work_mode_icon'] ?> <?= htmlspecialchars($relJob['mode_name']) ?>
                                </span>
                            <?php endif; ?>
                            <?php if ($relJob['type_name']): ?>
                                <span class="job-badge job-badge-teal">
                                    <?= $relJob['employment_icon'] ?> <?= htmlspecialchars($relJob['type_name']) ?>
                                </span>
                            <?php endif; ?>
                            <?php if ($relJob['level_name']): ?>
                                <span class="job-badge job-badge-green">
                                    <?= $relJob['experience_icon'] ?> <?= htmlspecialchars($relJob['level_name']) ?>
                                </span>
                            <?php endif; ?>
                        </div>

                        <div class="related-job-footer">
                            <span class="posted-time">
                                Posted <?= date('M d, Y', strtotime($relJob['posted_date'])) ?>
                            </span>
                            <?php if ($relJob['application_deadline']): ?>
                                <?php
                                    $relDeadline = strtotime($relJob['application_deadline']);
                                    $relToday = strtotime(date('Y-m-d'));
                                    $relDaysLeft = floor(($relDeadline - $relToday) / (60 * 60 * 24));
                                ?>
                                <?php if ($relDaysLeft >= 0): ?>
                                <span class="deadline-badge <?= $relDaysLeft <= 3 ? 'urgent' : 'warning' ?>">
                                    <?= $relDaysLeft ?> days left
                                </span>
                                <?php endif; ?>
                            <?php endif; ?>
                        </div>
                    </div>
                </a>
            <?php endforeach; ?>
        </div>
    </div>
    <?php endif; ?>

</div>

<?php include 'includes/footer.php'; ?>
