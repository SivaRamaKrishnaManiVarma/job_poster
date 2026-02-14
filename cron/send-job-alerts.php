<?php
/**
 * Job Alert Email Cron Job
 * Run this script daily via cron to send job alerts
 * 
 * Cron command (run daily at 9 AM):
 * 0 9 * * * /usr/bin/php /path/to/job_poster/cron/send-job-alerts.php
 */

// Allow script to run from command line only
if (php_sapi_name() !== 'cli') {
    die('This script can only be run from command line');
}

// Set execution time limit
set_time_limit(300); // 5 minutes

// Include dependencies
require_once __DIR__ . '/../includes/config.php';
require_once __DIR__ . '/../includes/email-config.php';

echo "[" . date('Y-m-d H:i:s') . "] Starting job alert cron job...\n";

// Track sent emails
$emailsSent = 0;
$emailsFailed = 0;

// Get all users who want email alerts and have preferences
$usersStmt = $pdo->query("
    SELECT DISTINCT u.id, u.full_name, u.email, u.last_login
    FROM users u
    INNER JOIN user_job_preferences ujp ON u.id = ujp.user_id
    WHERE u.is_active = 1 
    AND u.job_alert_email = 1
    AND u.email_verified = 0
");

$users = $usersStmt->fetchAll(PDO::FETCH_ASSOC);

echo "Found " . count($users) . " users with email alerts enabled\n";

foreach ($users as $user) {
    echo "\nProcessing user: {$user['email']}...\n";
    
    // Get user's job preferences
    $prefsStmt = $pdo->prepare("
        SELECT ujp.*, mc.category_name 
        FROM user_job_preferences ujp
        JOIN master_job_categories mc ON ujp.job_category_id = mc.id
        WHERE ujp.user_id = ?
        ORDER BY ujp.priority DESC
    ");
    $prefsStmt->execute([$user['id']]);
    $preferences = $prefsStmt->fetchAll(PDO::FETCH_ASSOC);
    
    if (empty($preferences)) {
        echo "  No preferences found, skipping\n";
        continue;
    }
    
    // Get new jobs posted in last 24 hours matching preferences
    $categoryIds = array_column($preferences, 'job_category_id');
    $placeholders = str_repeat('?,', count($categoryIds) - 1) . '?';
    
    $jobsStmt = $pdo->prepare("
        SELECT j.*, 
               c.category_name,
               w.mode_name,
               et.type_name as employment_type_name
        FROM jobs j
        LEFT JOIN master_job_categories c ON j.job_category_id = c.id
        LEFT JOIN master_work_modes w ON j.work_mode_id = w.id
        LEFT JOIN master_employment_types et ON j.employment_type_id = et.id
        WHERE j.is_active = 1 
        AND j.job_category_id IN ($placeholders)
        AND j.posted_date >= DATE_SUB(NOW(), INTERVAL 1 DAY)
        AND (j.application_deadline IS NULL OR j.application_deadline >= CURDATE())
        ORDER BY j.posted_date DESC
        LIMIT 10
    ");
    $jobsStmt->execute($categoryIds);
    $newJobs = $jobsStmt->fetchAll(PDO::FETCH_ASSOC);
    
    if (empty($newJobs)) {
        echo "  No new jobs found, skipping\n";
        continue;
    }
    
    echo "  Found " . count($newJobs) . " new matching jobs\n";
    
    // Build email content
    $jobsHtml = '';
    foreach ($newJobs as $job) {
        $jobUrl = BASE_URL . '/jobs/' . htmlspecialchars($job['slug']);
        
        $jobsHtml .= '<div class="job-card">';
        $jobsHtml .= '<div class="job-title">' . htmlspecialchars($job['title']) . '</div>';
        $jobsHtml .= '<div class="job-company">🏢 ' . htmlspecialchars($job['company']) . '</div>';
        $jobsHtml .= '<div class="job-details">';
        
        if (!empty($job['category_name'])) {
            $jobsHtml .= '📁 ' . htmlspecialchars($job['category_name']) . ' &nbsp;&nbsp; ';
        }
        if (!empty($job['location'])) {
            $jobsHtml .= '📍 ' . htmlspecialchars($job['location']) . ' &nbsp;&nbsp; ';
        }
        if (!empty($job['work_mode_name'])) {
            $jobsHtml .= '💼 ' . htmlspecialchars($job['work_mode_name']);
        }
        
        $jobsHtml .= '</div>';
        
        if (!empty($job['min_salary']) || !empty($job['max_salary'])) {
            $jobsHtml .= '<div class="job-details" style="color: #28a745; font-weight: bold;">';
            $jobsHtml .= '💰 ';
            if (!empty($job['min_salary']) && !empty($job['max_salary'])) {
                $jobsHtml .= '₹' . number_format($job['min_salary']) . ' - ₹' . number_format($job['max_salary']);
            } elseif (!empty($job['min_salary'])) {
                $jobsHtml .= '₹' . number_format($job['min_salary']) . '+';
            }
            $jobsHtml .= '</div>';
        }
        
        $jobsHtml .= '<a href="' . $jobUrl . '" class="job-link">View Job Details →</a>';
        $jobsHtml .= '</div>';
    }
    
    // Email content
    $emailContent = '
        <h2>Hello ' . htmlspecialchars($user['full_name']) . '! 👋</h2>
        <p>We found <strong>' . count($newJobs) . ' new job' . (count($newJobs) > 1 ? 's' : '') . '</strong> matching your interests:</p>
        ' . $jobsHtml . '
        <div style="text-align: center; margin-top: 30px;">
            <a href="' . BASE_URL . '/profile/recommended-jobs.php" class="button">View All Recommended Jobs</a>
        </div>
        <hr style="border: none; border-top: 1px solid #e0e0e0; margin: 30px 0;">
        <p style="font-size: 14px; color: #666;">
            <strong>Your interests:</strong> ' . implode(', ', array_column($preferences, 'category_name')) . '
        </p>
        <p style="font-size: 12px; color: #999;">
            Don\'t want these emails? <a href="' . BASE_URL . '/profile/edit-profile.php#preferences">Update your preferences</a>
        </p>
    ';
    
    $subject = count($newJobs) . ' New Job' . (count($newJobs) > 1 ? 's' : '') . ' Matching Your Interests | ' . SITE_NAME;
    $preheader = 'Check out these new opportunities posted in the last 24 hours';
    
    $htmlBody = getEmailTemplate($emailContent, $preheader);
    
    // Send email
    if (sendEmail($user['email'], $subject, $htmlBody)) {
        echo "  ✓ Email sent successfully\n";
        $emailsSent++;
    } else {
        echo "  ✗ Failed to send email\n";
        $emailsFailed++;
    }
    
    // Small delay to avoid overwhelming mail server
    usleep(100000); // 0.1 second
}

echo "\n[" . date('Y-m-d H:i:s') . "] Job alert cron completed\n";
echo "Emails sent: $emailsSent\n";
echo "Emails failed: $emailsFailed\n";
?>
