<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

require_once 'backend/php/PHPMailer/PHPMailer.php';
require_once 'backend/php/PHPMailer/SMTP.php';
require_once 'backend/php/PHPMailer/Exception.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;
use PHPMailer\PHPMailer\Exception;

echo "<h2>Final Email Test</h2>";

try {
    // Create a new PHPMailer instance
    $mail = new PHPMailer();

    // Tell PHPMailer to use SMTP
    $mail->isSMTP();

    // Configure SMTP settings
    $mail->Host = 'smtp.gmail.com';
    $mail->Port = 587;
    $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
    $mail->SMTPAuth = true;

    // Gmail credentials
    $mail->Username = 'yourlifelink.org@gmail.com';
    $mail->Password = 'rnda lowl zgel ddim';

    // Set who the message is from
    $mail->setFrom('yourlifelink.org@gmail.com', 'LifeLink Admin');

    // Set who the message is to
    $mail->addAddress('yourlifelink.org@gmail.com');

    // Set email subject and body
    $mail->Subject = 'Test Email from LifeLink';
    $mail->Body = 'This is a test email. If you receive this, the email system is working!';

    // Try to send the email
    echo "<div style='font-family: monospace; background: #f5f5f5; padding: 10px; margin: 10px 0;'>";
    echo "Attempting to send email...<br>";
    
    if ($mail->send()) {
        echo "<p style='color: green'>✅ Email sent successfully!</p>";
    } else {
        echo "<p style='color: red'>❌ Failed to send email: " . $mail->ErrorInfo . "</p>";
    }
    echo "</div>";

} catch (Exception $e) {
    echo "<p style='color: red'>❌ Error: " . $e->getMessage() . "</p>";
}

// Show PHP and server information
echo "<h3>System Information:</h3>";
echo "<pre>";
echo "PHP Version: " . phpversion() . "\n";
echo "OpenSSL: " . (extension_loaded('openssl') ? "Enabled" : "Disabled") . "\n";
if (extension_loaded('openssl')) {
    echo "OpenSSL Version: " . OPENSSL_VERSION_TEXT . "\n";
}
echo "Server Software: " . $_SERVER['SERVER_SOFTWARE'] . "\n";
echo "</pre>";
?>
