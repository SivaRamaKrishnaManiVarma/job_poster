<?php
/**
 * Test Email Functionality
 * Run: php cron/test-email.php
 * Or double-click test-email.bat
 */

require_once __DIR__ . '/../includes/config.php';
require_once __DIR__ . '/../includes/email-config.php';

echo "======================================\n";
echo "  Email System Test\n";
echo "======================================\n\n";

// Get your email from prompt
echo "Enter your test email address: ";
$handle = fopen("php://stdin", "r");
$testEmail = trim(fgets($handle));
fclose($handle);

if (empty($testEmail) || !filter_var($testEmail, FILTER_VALIDATE_EMAIL)) {
    die("Invalid email address!\n");
}

echo "\nSending test email to: $testEmail\n\n";

// Build test email
$emailContent = '
    <h2>🎉 Email System Test</h2>
    <p>Congratulations! Your email system is working correctly.</p>
    
    <div class="job-card">
        <div class="job-title">Sample Job: Senior PHP Developer</div>
        <div class="job-company">🏢 Tech Company India</div>
        <div class="job-details">
            📁 IT/Software &nbsp;&nbsp; 
            📍 Mumbai, Maharashtra &nbsp;&nbsp; 
            💼 Remote
        </div>
        <div class="job-details" style="color: #28a745; font-weight: bold;">
            💰 ₹800,000 - ₹1,200,000
        </div>
        <a href="' . BASE_URL . '" class="job-link">View Job Details →</a>
    </div>
    
    <p><strong>This is a test email.</strong> If you received this, your email configuration is correct!</p>
    
    <div style="background: #f0f9ff; padding: 15px; border-left: 4px solid #0284c7; margin: 20px 0;">
        <strong>📝 Next Steps:</strong>
        <ul style="margin: 10px 0;">
            <li>Enable job alerts in your profile</li>
            <li>Set up Windows Task Scheduler for daily alerts</li>
            <li>Test the full alert system</li>
        </ul>
    </div>
';

$subject = 'Test Email - Job Portal Alert System';
$htmlBody = getEmailTemplate($emailContent, 'Testing email functionality');

// Send email
if (sendEmail($testEmail, $subject, $htmlBody)) {
    echo "✓ SUCCESS! Email sent to $testEmail\n";
    echo "\nCheck your inbox (and spam folder).\n";
    echo "If you don't see it, check email-config.php settings.\n";
} else {
    echo "✗ FAILED! Could not send email.\n";
    echo "\nTroubleshooting:\n";
    echo "1. Check email-config.php settings\n";
    echo "2. Make sure XAMPP is running\n";
    echo "3. If using Gmail, enable App Password\n";
    echo "4. Check error logs in logs/ folder\n";
}

echo "\n======================================\n";
echo "Test completed!\n";
echo "======================================\n";
?>
