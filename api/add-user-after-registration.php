// After successful user creation in auth/register.php
require_once '../includes/email-config.php';

$emailContent = '
    <h2>Welcome to ' . SITE_NAME . '! 🎉</h2>
    <p>Hi ' . htmlspecialchars($full_name) . ',</p>
    <p>Thank you for creating an account with us. We\'re excited to help you find your dream job!</p>
    
    <h3>Get Started:</h3>
    <ol>
        <li><strong>Complete Your Profile:</strong> Add your education, experience, and skills</li>
        <li><strong>Upload Your Resume:</strong> Make it easy for recruiters to find you</li>
        <li><strong>Set Job Interests:</strong> Get personalized job recommendations</li>
        <li><strong>Browse Jobs:</strong> Find opportunities that match your goals</li>
    </ol>
    
    <div style="text-align: center; margin: 30px 0;">
        <a href="' . BASE_URL . '/profile/dashboard.php" class="button">Go to Dashboard</a>
    </div>
    
    <p>Need help? Reply to this email and we\'ll assist you.</p>
    <p>Happy job hunting!</p>
';

$subject = 'Welcome to ' . SITE_NAME . ' - Let\'s Get Started!';
$htmlBody = getEmailTemplate($emailContent);
sendEmail($email, $subject, $htmlBody);
