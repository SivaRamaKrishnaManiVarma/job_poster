<?php
/**
 * Email Configuration
 * Configure SMTP or PHP mail() for sending emails
 */

// Email settings
define('MAIL_FROM', 'noreply@yourdomain.com');
define('MAIL_FROM_NAME', 'Job Portal Alerts');
define('SITE_NAME', 'Job Portal');

// SMTP Configuration (recommended for production)
define('USE_SMTP', false); // Set to true to use SMTP, false to use PHP mail()

// SMTP Settings (if USE_SMTP is true)
define('SMTP_HOST', 'smtp.gmail.com'); // e.g., smtp.gmail.com
define('SMTP_PORT', 587);
define('SMTP_USERNAME', 'your-email@gmail.com');
define('SMTP_PASSWORD', 'your-app-password');
define('SMTP_ENCRYPTION', 'tls'); // 'tls' or 'ssl'


// Production email settings
// define('USE_SMTP', true); // Use SMTP on production
// define('SMTP_HOST', 'smtp.gmail.com'); // Or your hosting SMTP
// define('SMTP_PORT', 587);
// define('SMTP_USERNAME', 'noreply@yourdomain.com'); // Real email
// define('SMTP_PASSWORD', 'your-app-password'); // Real password
// define('SMTP_ENCRYPTION', 'tls');

// define('MAIL_FROM', 'noreply@yourdomain.com');
// define('MAIL_FROM_NAME', 'Job Portal');
// define('SITE_NAME', 'Job Portal');

/**
 * Send email using PHP mail() or SMTP
 */
function sendEmail($to, $subject, $htmlBody, $textBody = '') {
    if (USE_SMTP) {
        return sendEmailSMTP($to, $subject, $htmlBody, $textBody);
    } else {
        return sendEmailPHP($to, $subject, $htmlBody, $textBody);
    }
}

/**
 * Send email using PHP mail()
 */
function sendEmailPHP($to, $subject, $htmlBody, $textBody = '') {
    $headers = "MIME-Version: 1.0" . "\r\n";
    $headers .= "Content-type:text/html;charset=UTF-8" . "\r\n";
    $headers .= "From: " . MAIL_FROM_NAME . " <" . MAIL_FROM . ">" . "\r\n";
    $headers .= "Reply-To: " . MAIL_FROM . "\r\n";
    $headers .= "X-Mailer: PHP/" . phpversion();
    
    return mail($to, $subject, $htmlBody, $headers);
}

/**
 * Send email using SMTP (requires PHPMailer)
 */
function sendEmailSMTP($to, $subject, $htmlBody, $textBody = '') {
    // Check if PHPMailer is available
    if (!class_exists('PHPMailer\PHPMailer\PHPMailer')) {
        error_log("PHPMailer not found. Please install: composer require phpmailer/phpmailer");
        return false;
    }
    
    require_once __DIR__ . '/../vendor/autoload.php';
    
    $mail = new PHPMailer\PHPMailer\PHPMailer(true);
    
    try {
        // SMTP settings
        $mail->isSMTP();
        $mail->Host = SMTP_HOST;
        $mail->SMTPAuth = true;
        $mail->Username = SMTP_USERNAME;
        $mail->Password = SMTP_PASSWORD;
        $mail->SMTPSecure = SMTP_ENCRYPTION;
        $mail->Port = SMTP_PORT;
        
        // Sender and recipient
        $mail->setFrom(MAIL_FROM, MAIL_FROM_NAME);
        $mail->addAddress($to);
        
        // Content
        $mail->isHTML(true);
        $mail->Subject = $subject;
        $mail->Body = $htmlBody;
        $mail->AltBody = $textBody ?: strip_tags($htmlBody);
        
        $mail->send();
        return true;
    } catch (Exception $e) {
        error_log("Email send failed: {$mail->ErrorInfo}");
        return false;
    }
}

/**
 * Email template wrapper
 */
function getEmailTemplate($content, $preheader = '') {
    ob_start();
    ?>
    <!DOCTYPE html>
    <html lang="en">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title><?= SITE_NAME ?></title>
        <style>
            body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; margin: 0; padding: 0; background-color: #f4f4f4; }
            .container { max-width: 600px; margin: 20px auto; background: #ffffff; border-radius: 8px; overflow: hidden; box-shadow: 0 2px 8px rgba(0,0,0,0.1); }
            .header { background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white; padding: 30px 20px; text-align: center; }
            .header h1 { margin: 0; font-size: 24px; }
            .content { padding: 30px 20px; }
            .job-card { border: 1px solid #e0e0e0; border-radius: 6px; padding: 15px; margin-bottom: 15px; background: #f9f9f9; }
            .job-title { font-size: 18px; font-weight: bold; color: #667eea; margin-bottom: 8px; }
            .job-company { font-size: 16px; color: #555; margin-bottom: 8px; }
            .job-details { font-size: 14px; color: #777; margin-bottom: 10px; }
            .job-link { display: inline-block; background: #667eea; color: white; padding: 10px 20px; text-decoration: none; border-radius: 4px; margin-top: 10px; }
            .footer { background: #f8f9fa; padding: 20px; text-align: center; font-size: 12px; color: #666; }
            .footer a { color: #667eea; text-decoration: none; }
            .button { background: #667eea; color: white; padding: 12px 30px; text-decoration: none; border-radius: 4px; display: inline-block; margin: 10px 0; }
        </style>
    </head>
    <body>
        <?php if ($preheader): ?>
            <div style="display:none;font-size:1px;color:#fefefe;line-height:1px;max-height:0px;max-width:0px;opacity:0;overflow:hidden;">
                <?= htmlspecialchars($preheader) ?>
            </div>
        <?php endif; ?>
        
        <div class="container">
            <div class="header">
                <h1><?= SITE_NAME ?></h1>
            </div>
            <div class="content">
                <?= $content ?>
            </div>
            <div class="footer">
                <p>&copy; <?= date('Y') ?> <?= SITE_NAME ?>. All rights reserved.</p>
                <p>
                    <a href="<?= BASE_URL ?>/profile/edit-profile.php#preferences">Manage Preferences</a> | 
                    <a href="<?= BASE_URL ?>/auth/logout.php">Unsubscribe</a>
                </p>
            </div>
        </div>
    </body>
    </html>
    <?php
    return ob_get_clean();
}
?>
