<?php
require_once 'includes/config.php';
require_once 'includes/functions.php';
require_once 'includes/master-data-functions.php';

// Get job by slug or ID (fallback for old links)
$slug = isset($_GET['slug']) ? trim($_GET['slug']) : '';
$jobId = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if (empty($slug) && $jobId === 0) {
    header('Location: /job_poster/');
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

// Get job details with ALL master data including NEW fields
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
    header('Location: /job_poster/');
    exit;
}

// If accessed by ID, redirect to slug URL (for SEO)
if ($jobId > 0 && !empty($job['slug'])) {
    header('Location: /job_poster/jobs/' . urlencode($job['slug']), true, 301);
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

// Calculate progress
$totalDates = count($timelineDates);
$passedDates = 0;

foreach ($timelineDates as $item) {
    if (strtotime($item['date']) < $currentDate) {
        $passedDates++;
    }
}

$progressPercentage = $totalDates > 0 ? round(($passedDates / $totalDates) * 100) : 0;

// Calculate timeline progress height for the green bar
$timelineProgressHeight = $totalDates > 0 ? ($passedDates / $totalDates) * 100 : 0;

include 'includes/header.php';
?>
<style>
/* =====================================================
   ULTRA-MODERN JOB DETAILS PAGE - COMPLETE CSS
   Mobile-First Student Flow: Content First → Apply Last
   ===================================================== */

/* =====================================================
   ROOT VARIABLES
   ===================================================== */

:root {
    --primary: #667eea;
    --primary-hover: #764ba2;
    --gray-900: #111827;
    --gray-800: #1f2937;
    --gray-700: #374151;
    --gray-600: #4b5563;
    --gray-500: #6b7280;
    --gray-400: #9ca3af;
    --gray-300: #d1d5db;
    --gray-200: #e5e7eb;
    --gray-100: #f3f4f6;
    --radius: 12px;
}

/* =====================================================
   1. BASE & CONTAINER
   ===================================================== */

* {
    box-sizing: border-box;
}

body {
    font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
    line-height: 1.6;
    color: var(--gray-700);
    background: linear-gradient(135deg, #f5f7fa 0%, #c3cfe2 100%);
    margin: 0;
    padding: 0;
}

.job-details-container {
    max-width: 1200px;
    margin: 2rem auto;
    padding: 0 1rem;
    animation: fadeIn 0.6s ease-out;
}

@keyframes fadeIn {
    from { opacity: 0; transform: translateY(20px); }
    to { opacity: 1; transform: translateY(0); }
}

/* =====================================================
   2. MOBILE-FIRST CONTENT WRAPPER
   ===================================================== */

.content-wrapper {
    display: grid;
    grid-template-columns: 1fr;
    gap: 2rem;
    align-items: start;
}

/* DESKTOP: Sidebar right */
@media (min-width: 992px) {
    .content-wrapper {
        grid-template-columns: 1fr 380px;
    }
    .sidebar { order: 2; }
    .main-content { order: 1; }
}

.back-link {
    display: inline-flex;
    align-items: center;
    gap: 0.5rem;
    color: var(--primary);
    text-decoration: none;
    font-weight: 600;
    margin-bottom: 2rem;
    padding: 0.75rem 1.5rem;
    background: rgba(102, 126, 234, 0.1);
    border-radius: var(--radius);
    transition: all 0.4s ease;
    border: 1px solid transparent;
}

.back-link:hover {
    color: white;
    background: linear-gradient(135deg, var(--primary), var(--primary-hover));
    box-shadow: 0 8px 25px rgba(102, 126, 234, 0.3);
    transform: translateX(-5px);
}

/* =====================================================
   4. QUICK STATS BAR
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
    background: linear-gradient(135deg, var(--primary), var(--primary-hover));
    color: white;
    padding: 1.5rem;
    border-radius: 16px;
    text-align: center;
    box-shadow: 0 8px 30px rgba(102, 126, 234, 0.25);
    transition: all 0.4s ease;
    position: relative;
    overflow: hidden;
}

.stat-card:hover {
    transform: translateY(-8px);
    box-shadow: 0 15px 45px rgba(102, 126, 234, 0.4);
}

.stat-card.salary { background: linear-gradient(135deg, #f093fb, #f5576c); }
.stat-card.vacancies { background: linear-gradient(135deg, #4facfe, #00f2fe); }
.stat-card.deadline { background: linear-gradient(135deg, #43e97b, #38f9d7); }
.stat-card.views { background: linear-gradient(135deg, #fa709a, #fee140); }

.stat-number {
    font-size: 1.875rem;
    font-weight: 800;
    margin: 0;
    line-height: 1.2;
}

@media (min-width: 992px) {
    .stat-number { font-size: 2.5rem; }
}

.stat-label {
    font-size: 0.875rem;
    opacity: 0.95;
    margin: 0.75rem 0 0 0;
    font-weight: 500;
}

/* =====================================================
   5. JOB HEADER
   ===================================================== */

.job-header {
    background: white;
    border-radius: 20px;
    padding: 2rem;
    margin-bottom: 2rem;
    box-shadow: 0 8px 35px rgba(0, 0, 0, 0.08);
    border: 1px solid var(--gray-200);
    position: relative;
    overflow: hidden;
}

.job-header::before {
    content: '';
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    height: 5px;
    background: linear-gradient(90deg, var(--primary), var(--primary-hover));
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
        gap: 1rem;
    }
}

.company-logo-large {
    width: 80px;
    height: 80px;
    background: linear-gradient(135deg, var(--primary), var(--primary-hover));
    color: white;
    border-radius: 18px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 2rem;
    font-weight: 800;
    flex-shrink: 0;
    box-shadow: 0 8px 25px rgba(0, 0, 0, 0.2);
}

@media (min-width: 992px) {
    .company-logo-large {
        width: 90px;
        height: 90px;
        font-size: 2.25rem;
    }
}

.job-title {
    font-size: 1.75rem;
    font-weight: 800;
    color: var(--gray-900);
    margin: 0 0 0.5rem 0;
    line-height: 1.3;
    background: linear-gradient(135deg, var(--primary), var(--primary-hover));
    -webkit-background-clip: text;
    -webkit-text-fill-color: transparent;
    background-clip: text;
}

@media (min-width: 992px) {
    .job-title { font-size: 2.25rem; }
}

.company-name {
    font-size: 1.125rem;
    color: var(--gray-600);
    margin: 0;
    display: flex;
    align-items: center;
    gap: 0.5rem;
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
    background: linear-gradient(135deg, #f8f9fa, #e9ecef);
    border-radius: 25px;
    font-size: 0.9375rem;
    font-weight: 600;
    color: var(--gray-700);
    transition: all 0.3s ease;
}

.meta-tag:hover {
    background: white;
    border: 2px solid var(--primary);
    box-shadow: 0 4px 15px rgba(102, 126, 234, 0.2);
}

/* =====================================================
   6. MAIN CONTENT (Shows FIRST on Mobile)
   ===================================================== */

.main-content {
    background: white;
    border-radius: 20px;
    padding: 2rem;
    box-shadow: 0 4px 25px rgba(0, 0, 0, 0.06);
    border: 1px solid var(--gray-200);
    order: 1;
}

@media (max-width: 767px) {
    .main-content {
        padding: 1.5rem;
        margin-bottom: 2rem;
    }
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
    position: relative;
}

@media (max-width: 767px) {
    .section-title { font-size: 1.25rem; }
}

.section-title::before {
    content: '';
    position: absolute;
    bottom: -3px;
    left: 0;
    width: 60px;
    height: 3px;
    background: linear-gradient(90deg, var(--primary), var(--primary-hover));
    border-radius: 10px;
}

.job-description {
    font-size: 1.0625rem;
    line-height: 1.8;
    color: var(--gray-700);
}

/* =====================================================
   7. CLEAN TIMELINE (No Progress Bar)
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
    background: #e5e7eb;
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
    background: #ffffff;
    border: 2px solid #cbd5f5;
    box-shadow: 0 0 0 2px #eef2ff;
}

.timeline-item.passed::before {
    background: #10b981;
    border-color: #10b981;
    box-shadow: 0 0 0 2px rgba(16, 185, 129, 0.15);
}

.timeline-item.current::before {
    background: #3b82f6;
    border-color: #3b82f6;
    box-shadow: 0 0 0 2px rgba(59, 130, 246, 0.2);
}

@media (max-width: 767px) {
    .timeline { padding-left: 2rem; }
    .timeline-item::before { left: -1rem; width: 12px; height: 12px; }
}

.timeline-date {
    margin: 0;
    font-size: 1.05rem;
    font-weight: 700;
    color: #111827;
}

.timeline-item.passed .timeline-date { color: #15803d; }
.timeline-item.current .timeline-date { color: #1d4ed8; }

.timeline-label {
    margin: 0.1rem 0 0;
    font-size: 0.9rem;
    color: #6b7280;
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
    background: #dbeafe;
    color: #1d4ed8;
}

.timeline-item.upcoming .timeline-status {
    background: #e5e7eb;
    color: #374151;
}

/* =====================================================
   8. INFO CARDS & DETAILS GRID
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
    background: linear-gradient(135deg, #ffffff, #f8f9fa);
    border: 2px solid var(--gray-200);
    border-radius: 16px;
    padding: 1.5rem;
    display: flex;
    align-items: flex-start;
    gap: 1rem;
    transition: all 0.3s ease;
}

.info-card:hover {
    border-color: var(--primary);
    box-shadow: 0 8px 30px rgba(102, 126, 234, 0.15);
}

.info-card-icon { font-size: 2.5rem; flex-shrink: 0; }

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
    background: linear-gradient(135deg, #ffffff, #f8f9fa);
    border-radius: 16px;
    border-left: 4px solid var(--primary);
    transition: all 0.3s ease;
}

.detail-item:hover {
    box-shadow: 0 8px 30px rgba(102, 126, 234, 0.15);
    transform: translateY(-5px);
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
   9. FEE TABLE
   ===================================================== */

.fee-table {
    width: 100%;
    border-collapse: separate;
    border-spacing: 0;
    margin-top: 1rem;
    border-radius: 12px;
    box-shadow: 0 4px 20px rgba(0, 0, 0, 0.08);
    overflow: hidden;
}

.fee-table th {
    background: linear-gradient(135deg, var(--primary), var(--primary-hover));
    color: white;
    padding: 1rem 1.5rem;
    font-weight: 700;
    text-align: left;
}

.fee-table td {
    padding: 1rem 1.5rem;
    font-weight: 600;
    background: white;
}

.fee-amount {
    color: var(--primary);
    font-size: 1.25rem;
    font-weight: 800;
}

.fee-free {
    color: #10b981;
    font-weight: 800;
}

/* =====================================================
   10. SIDEBAR (Shows LAST on Mobile)
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
        order: 2;
    }
}

.apply-card {
    background: white;
    border-radius: 20px;
    padding: 2rem;
    box-shadow: 0 8px 40px rgba(102, 126, 234, 0.2);
    border: 1px solid var(--gray-200);
    margin-bottom: 1.5rem;
}

@media (max-width: 767px) {
    .apply-card {
        padding: 1.75rem;
        box-shadow: 0 -4px 25px rgba(102, 126, 234, 0.15);
    }
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
    background: linear-gradient(135deg, var(--primary), var(--primary-hover));
    color: white;
    text-align: center;
    text-decoration: none;
    border-radius: 12px;
    font-weight: 800;
    font-size: 1.125rem;
    transition: all 0.4s ease;
    border: none;
    cursor: pointer;
    box-shadow: 0 8px 25px rgba(102, 126, 234, 0.3);
}

.apply-btn:hover {
    transform: translateY(-4px);
    box-shadow: 0 12px 35px rgba(102, 126, 234, 0.4);
}

.apply-btn.disabled {
    background: #9ca3af;
    cursor: not-allowed;
    transform: none;
    box-shadow: none;
}

.deadline-info {
    margin-top: 1.5rem;
    padding: 1.25rem;
    border-radius: 12px;
    font-size: 0.9375rem;
    font-weight: 600;
    border: 2px solid;
}

.deadline-info.urgent {
    background: linear-gradient(135deg, #fef3c7, #fde68a);
    border-color: #f59e0b;
    color: #92400e;
}

.deadline-info.normal {
    background: linear-gradient(135deg, #dbeafe, #bfdbfe);
    border-color: #3b82f6;
    color: #1e40af;
}

.deadline-info.expired {
    background: linear-gradient(135deg, #fee2e2, #fecaca);
    border-color: #ef4444;
    color: #991b1b;
}

.share-section {
    margin-top: 2rem;
    padding-top: 2rem;
    border-top: 2px dashed var(--gray-200);
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
    border-radius: 12px;
    text-decoration: none;
    font-weight: 700;
    font-size: 0.875rem;
    transition: all 0.3s ease;
}

.share-btn:hover { transform: translateY(-3px); box-shadow: 0 8px 25px rgba(0, 0, 0, 0.2); }

.share-btn.facebook { background: linear-gradient(135deg, #1877f2, #0c63d4); color: white; }
.share-btn.twitter { background: linear-gradient(135deg, #1da1f2, #0c8bd9); color: white; }
.share-btn.linkedin { background: linear-gradient(135deg, #0077b5, #00669c); color: white; }
.share-btn.whatsapp { background: linear-gradient(135deg, #25d366, #1ebe57); color: white; }

.highlight-box {
    background: linear-gradient(135deg, var(--primary), var(--primary-hover));
    color: white;
    padding: 1.75rem;
    border-radius: 16px;
    box-shadow: 0 8px 30px rgba(102, 126, 234, 0.3);
}

/* =====================================================
   11. RELATED JOBS
   ===================================================== */

.related-section {
    margin-top: 4rem;
    padding: 3rem 2rem;
    background: linear-gradient(135deg, #f9fafb, #ffffff);
    border-radius: 24px;
    box-shadow: 0 4px 25px rgba(0, 0, 0, 0.06);
}

@media (max-width: 767px) {
    .related-section { padding: 2rem 1rem; }
}

.related-jobs-grid {
    display: grid;
    grid-template-columns: 1fr;
    gap: 1.5rem;
}

@media (min-width: 768px) {
    .related-jobs-grid {
        grid-template-columns: repeat(auto-fill, minmax(320px, 1fr));
        gap: 1.75rem;
    }
}

.related-job-card {
    background: white;
    border-radius: 20px;
    box-shadow: 0 4px 25px rgba(0, 0, 0, 0.08);
    border: 2px solid var(--gray-200);
    transition: all 0.4s ease;
    text-decoration: none;
    overflow: hidden;
}

.related-job-card:hover {
    transform: translateY(-8px);
    box-shadow: 0 20px 40px rgba(102, 126, 234, 0.15);
    border-color: var(--primary);
}

/* =====================================================
   12. MOBILE ENHANCEMENTS
   ===================================================== */

@media (max-width: 767px) {
    .job-details-container { padding: 0 0.75rem; margin: 1rem auto; }
    .job-header { padding: 1.5rem; }
    .job-title { font-size: 1.5rem; }
    .main-content { padding: 1.5rem; }
    .content-section { margin-bottom: 2.5rem; }
    .stat-card { padding: 1.25rem; }
    .stat-number { font-size: 1.75rem; }
}

/* =====================================================
   13. FIXED BOTTOM APPLY BUTTON (Mobile Only)
   ===================================================== */

@media (max-width: 767px) {
    body { padding-bottom: 90px; }
    
    .mobile-apply-bar {
        display: none;
        position: fixed;
        bottom: 0;
        left: 0;
        right: 0;
        background: white;
        padding: 1rem;
        box-shadow: 0 -4px 20px rgba(0,0,0,0.15);
        z-index: 999;
        border-top: 3px solid var(--primary);
    }
    
    .mobile-apply-bar.show { display: block; }
    
    .mobile-apply-btn {
        width: 100%;
        padding: 1.125rem;
        background: linear-gradient(135deg, var(--primary), var(--primary-hover));
        color: white;
        border: none;
        border-radius: 12px;
        font-size: 1.0625rem;
        font-weight: 700;
        cursor: pointer;
        box-shadow: 0 4px 15px rgba(102, 126, 234, 0.3);
    }
}

</style>
<div class="job-details-container">
   <!-- Smart Back Button - Preserves Search/Filters -->
    <?php
    // Check if user came from index page with filters
    $referrer = isset($_SERVER['HTTP_REFERER']) ? $_SERVER['HTTP_REFERER'] : '';
    $sameHost = strpos($referrer, $_SERVER['HTTP_HOST']) !== false;
    $fromIndex = strpos($referrer, BASE_PATH . '/') !== false && strpos($referrer, '/jobs/') === false;

    // If came from same site index with filters, go back there
    $backUrl = ($sameHost && $fromIndex) ? $referrer : BASE_URL;
    ?>
    <a href="<?php echo htmlspecialchars($backUrl); ?>" class="back-link">
        ← Back to All Jobs
    </a>


    <!-- Quick Stats -->
    <?php if ($job['total_vacancies'] || $job['salary_min'] || $job['application_deadline'] || $job['view_count']): ?>
    <div class="quick-stats">
        <?php if ($job['total_vacancies']): ?>
        <div class="stat-card vacancies">
            <p class="stat-number"><?php echo number_format($job['total_vacancies']); ?></p>
            <p class="stat-label">Total Vacancies</p>
        </div>
        <?php endif; ?>

        <?php if ($job['salary_min']): ?>
        <div class="stat-card salary">
            <p class="stat-number">₹<?php echo number_format($job['salary_min']); ?></p>
            <p class="stat-label">Minimum Salary/Month</p>
        </div>
        <?php endif; ?>

        <?php if ($job['application_deadline'] && !$isExpired): ?>
        <div class="stat-card deadline">
            <p class="stat-number"><?php echo $daysUntilDeadline >= 0 ? $daysUntilDeadline : 0; ?></p>
            <p class="stat-label">Days Left to Apply</p>
        </div>
        <?php endif; ?>

        <?php if ($job['view_count']): ?>
        <div class="stat-card views">
            <p class="stat-number"><?php echo number_format($job['view_count']); ?></p>
            <p class="stat-label">Views</p>
        </div>
        <?php endif; ?>
    </div>
    <?php endif; ?>


    <!-- Job Header -->
    <div class="job-header">
        <div class="company-info">
            <div class="company-logo-large">
                <?php echo strtoupper(substr($job['company'], 0, 2)); ?>
            </div>
            <div class="job-title-section">
                <h1 class="job-title"><?php echo htmlspecialchars($job['title']); ?></h1>
                <p class="company-name">
                    <span>🏢 <?php echo htmlspecialchars($job['company']); ?></span>
                    <?php if ($job['official_website']): ?>
                        | <a href="<?php echo htmlspecialchars($job['official_website']); ?>" target="_blank" rel="noopener" class="company-website">Visit Website ↗</a>
                    <?php endif; ?>
                </p>
            </div>
        </div>

        <div class="job-meta">
            <?php if ($job['location']): ?>
                <span class="meta-tag">📍 <?php echo htmlspecialchars($job['location']); ?></span>
            <?php endif; ?>
            <?php if ($job['mode_name']): ?>
                <span class="meta-tag"><?php echo $job['work_mode_icon']; ?> <?php echo htmlspecialchars($job['mode_name']); ?></span>
            <?php endif; ?>
            <?php if ($job['type_name']): ?>
                <span class="meta-tag"><?php echo $job['employment_icon']; ?> <?php echo htmlspecialchars($job['type_name']); ?></span>
            <?php endif; ?>
            <?php if ($job['level_name']): ?>
                <span class="meta-tag"><?php echo $job['experience_icon']; ?> <?php echo htmlspecialchars($job['level_name']); ?></span>
            <?php endif; ?>
            <span class="meta-tag">📅 Posted <?php echo date('M d, Y', strtotime($job['posted_date'])); ?></span>
        </div>
    </div>


    <!-- Content Wrapper -->
    <div class="content-wrapper">
        <!-- Main Content (Shows FIRST on mobile) -->
        <div class="main-content">
            <!-- Job Description -->
            <?php if ($job['description']): ?>
            <div class="content-section">
                <h2 class="section-title">📄 About This Role</h2>
                <div class="job-description">
                    <?php echo nl2br(htmlspecialchars($job['description'])); ?>
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
                        <div class="timeline-item <?php echo $statusClass; ?>">
                            <p class="timeline-date"><?php echo date('F d, Y', $itemDate); ?></p>
                            <p class="timeline-label"><?php echo htmlspecialchars($item['label']); ?></p>
                            <span class="timeline-status"><?php echo strtoupper($statusText); ?></span>
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
                            <td class="fee-amount <?php echo $job['application_fee_general'] == 0 ? 'fee-free' : ''; ?>">
                                <?php echo $job['application_fee_general'] == 0 ? 'FREE' : '₹' . number_format($job['application_fee_general'], 2); ?>
                            </td>
                        </tr>
                        <?php endif; ?>

                        <?php if ($job['application_fee_obc'] !== null): ?>
                        <tr>
                            <td>OBC / EWS</td>
                            <td class="fee-amount <?php echo $job['application_fee_obc'] == 0 ? 'fee-free' : ''; ?>">
                                <?php echo $job['application_fee_obc'] == 0 ? 'FREE' : '₹' . number_format($job['application_fee_obc'], 2); ?>
                            </td>
                        </tr>
                        <?php endif; ?>

                        <?php if ($job['application_fee_sc_st'] !== null): ?>
                        <tr>
                            <td>SC / ST / PWD</td>
                            <td class="fee-amount <?php echo $job['application_fee_sc_st'] == 0 ? 'fee-free' : ''; ?>">
                                <?php echo $job['application_fee_sc_st'] == 0 ? 'FREE' : '₹' . number_format($job['application_fee_sc_st'], 2); ?>
                            </td>
                        </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
                <?php if ($job['payment_mode']): ?>
                <p style="margin-top: 1rem; color: var(--gray-600); font-size: 0.9375rem;">
                    <strong>Payment Mode:</strong> <?php echo htmlspecialchars($job['payment_mode']); ?>
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
                            <p><?php echo $job['age_limit_min']; ?> Years</p>
                        </div>
                    </div>
                    <?php endif; ?>

                    <?php if ($job['age_limit_max']): ?>
                    <div class="info-card">
                        <div class="info-card-icon">👴</div>
                        <div class="info-card-content">
                            <h4>Maximum Age</h4>
                            <p><?php echo $job['age_limit_max']; ?> Years</p>
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
                            <?php echo $job['category_icon']; ?>
                            <?php echo htmlspecialchars($job['category_name']); ?>
                        </p>
                    </div>
                    <?php endif; ?>

                    <?php if ($job['qualification_name']): ?>
                    <div class="detail-item">
                        <p class="detail-label">Qualification</p>
                        <p class="detail-value">
                            🎓 <?php echo htmlspecialchars($job['qualification_name']); ?>
                        </p>
                    </div>
                    <?php endif; ?>

                    <?php if ($job['department_name']): ?>
                    <div class="detail-item">
                        <p class="detail-label">Department</p>
                        <p class="detail-value">
                            🏛️ <?php echo htmlspecialchars($job['department_name']); ?>
                        </p>
                    </div>
                    <?php endif; ?>

                    <?php if ($job['state_name']): ?>
                    <div class="detail-item">
                        <p class="detail-label">State/Region</p>
                        <p class="detail-value">
                            📍 <?php echo htmlspecialchars($job['state_name']); ?>
                        </p>
                    </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>


        <!-- Sidebar (Shows AFTER main content on mobile) -->
        <div class="sidebar">
            <!-- Apply Card -->
            <div class="apply-card">
                <h3 class="apply-card-title">Ready to Apply?</h3>

                <?php if ($isExpired): ?>
                    <button class="apply-btn disabled" disabled>
                        ❌ Application Closed
                    </button>
                    <div class="deadline-info expired">
                        <strong>Applications Closed</strong>
                        Deadline was <?php echo date('M d, Y', strtotime($job['application_deadline'])); ?>
                    </div>
                <?php else: ?>
                    <a href="<?php echo htmlspecialchars($job['job_link']); ?>" 
                    target="_blank" 
                    rel="noopener noreferrer" 
                    class="apply-btn">
                        Apply Now →
                    </a>

                    <?php if ($job['application_deadline']): ?>
                        <div class="deadline-info <?php echo $daysUntilDeadline <= 3 ? 'urgent' : 'normal'; ?>">
                            <strong>⏰ Deadline</strong>
                            <?php echo date('M d, Y', strtotime($job['application_deadline'])); ?>
                            <?php if ($daysUntilDeadline >= 0 && $daysUntilDeadline <= 7): ?>
                                <br><strong><?php echo $daysUntilDeadline; ?> day<?php echo $daysUntilDeadline != 1 ? 's' : ''; ?> left!</strong>
                            <?php endif; ?>
                        </div>
                    <?php endif; ?>
                <?php endif; ?>

                <!-- Share Section -->
                <div class="share-section">
                    <p class="share-title">📤 Share This Job</p>
                    <div class="share-buttons">
                        <a href="https://www.facebook.com/sharer/sharer.php?u=<?php echo urlencode(fullUrl($_SERVER['REQUEST_URI'])); ?>" 
                        target="_blank" 
                        class="share-btn facebook">
                            Facebook
                        </a>
                        <a href="https://twitter.com/intent/tweet?url=<?php echo urlencode(fullUrl($_SERVER['REQUEST_URI'])); ?>&text=<?php echo urlencode($job['title'] . ' at ' . $job['company']); ?>" 
                        target="_blank" 
                        class="share-btn twitter">
                            Twitter
                        </a>
                        <a href="https://www.linkedin.com/sharing/share-offsite/?url=<?php echo urlencode(fullUrl($_SERVER['REQUEST_URI'])); ?>" 
                        target="_blank" 
                        class="share-btn linkedin">
                            LinkedIn
                        </a>
                        <a href="https://wa.me/?text=<?php echo urlencode($job['title'] . ' at ' . $job['company'] . ' - ' . fullUrl($_SERVER['REQUEST_URI'])); ?>" 
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
                <p>👥 <strong><?php echo number_format($job['total_vacancies']); ?></strong> vacancies</p>
                <?php endif; ?>
                <?php if ($job['salary_min']): ?>
                <p>💰 Salary from <strong>₹<?php echo number_format($job['salary_min']); ?></strong></p>
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
            <a href="<?php echo url('?category=' . $job['job_category_id']); ?>" class="view-all-link">
                View All <?php echo htmlspecialchars($job['category_name']); ?> Jobs →
            </a>
        </div>
        
        <div class="related-jobs-grid">
            <?php foreach ($relatedJobs as $relJob): ?>
                <a href="<?php echo url('jobs/' . urlencode($relJob['slug'] ?: 'job-' . $relJob['id'])); ?>" class="related-job-card">
                    <!-- Company Logo Badge -->
                    <div class="related-job-logo">
                        <?php echo strtoupper(substr($relJob['company'], 0, 2)); ?>
                    </div>
                    
                    <div class="related-job-content">
                        <h3 class="related-job-title"><?php echo htmlspecialchars($relJob['title']); ?></h3>
                        <p class="related-job-company">
                            <span class="company-icon">🏢</span>
                            <?php echo htmlspecialchars($relJob['company']); ?>
                        </p>

                        <!-- Job Preview -->
                        <?php if ($relJob['description']): ?>
                        <p class="related-job-preview">
                            <?php echo htmlspecialchars(substr(strip_tags($relJob['description']), 0, 80)); ?>...
                        </p>
                        <?php endif; ?>

                        <!-- Job Info Grid -->
                        <div class="related-job-info-grid">
                            <?php if ($relJob['location']): ?>
                            <div class="info-item">
                                <span class="info-icon">📍</span>
                                <span><?php echo htmlspecialchars($relJob['location']); ?></span>
                            </div>
                            <?php endif; ?>
                            
                            <?php if ($relJob['total_vacancies']): ?>
                            <div class="info-item">
                                <span class="info-icon">👥</span>
                                <span><?php echo number_format($relJob['total_vacancies']); ?> Vacancies</span>
                            </div>
                            <?php endif; ?>
                            
                            <?php if ($relJob['salary_min']): ?>
                            <div class="info-item">
                                <span class="info-icon">💰</span>
                                <span>₹<?php echo number_format($relJob['salary_min']); ?>/mo</span>
                            </div>
                            <?php endif; ?>
                        </div>

                        <!-- Badges -->
                        <div class="related-job-meta">
                            <?php if ($relJob['mode_name']): ?>
                                <span class="job-badge job-badge-blue">
                                    <?php echo $relJob['work_mode_icon']; ?> <?php echo htmlspecialchars($relJob['mode_name']); ?>
                                </span>
                            <?php endif; ?>
                            <?php if ($relJob['type_name']): ?>
                                <span class="job-badge job-badge-teal">
                                    <?php echo $relJob['employment_icon']; ?> <?php echo htmlspecialchars($relJob['type_name']); ?>
                                </span>
                            <?php endif; ?>
                            <?php if ($relJob['level_name']): ?>
                                <span class="job-badge job-badge-green">
                                    <?php echo $relJob['experience_icon']; ?> <?php echo htmlspecialchars($relJob['level_name']); ?>
                                </span>
                            <?php endif; ?>
                        </div>

                        <!-- Footer -->
                        <div class="related-job-footer">
                            <span class="posted-time">
                                Posted <?php echo date('M d, Y', strtotime($relJob['posted_date'])); ?>
                            </span>
                            <?php if ($relJob['application_deadline']): ?>
                                <?php
                                    $relDeadline = strtotime($relJob['application_deadline']);
                                    $relToday = strtotime(date('Y-m-d'));
                                    $relDaysLeft = floor(($relDeadline - $relToday) / (60 * 60 * 24));
                                ?>
                                <?php if ($relDaysLeft >= 0): ?>
                                <span class="deadline-badge <?php echo $relDaysLeft <= 3 ? 'urgent' : 'warning'; ?>">
                                    <?php echo $relDaysLeft; ?> days left
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
