<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

require_once 'backend/php/PHPMailer/PHPMailer.php';
require_once 'backend/php/PHPMailer/SMTP.php';
require_once 'backend/php/PHPMailer/Exception.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;
use PHPMailer\PHPMailer\Exception;

echo "<h1>SMTP Connection Test</h1>";

try {
    echo "<h2>Step 1: Creating PHPMailer Instance</h2>";
    $mail = new PHPMailer(true);
    echo "✅ PHPMailer instance created<br>";

    echo "<h2>Step 2: Setting up SMTP</h2>";
    $mail->isSMTP();
    $mail->SMTPDebug = SMTP::DEBUG_SERVER;
    $mail->Debugoutput = function($str, $level) {
        echo "Debug ($level): $str<br>";
    };
    echo "✅ SMTP debugging enabled<br>";

    echo "<h2>Step 3: Configuring Server Settings</h2>";
    $mail->Host = 'smtp.gmail.com';
    $mail->Port = 587;
    $mail->SMTPAuth = true;
    $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
    echo "✅ Server settings configured<br>";

    echo "<h2>Step 4: Setting Credentials</h2>";
    $mail->Username = 'yourlifelink.org@gmail.com';
    $mail->Password = 'wxhj ppdl ebsh wing';
    echo "✅ Credentials set<br>";

    echo "<h2>Step 5: Testing SMTP Connection</h2>";
    if (!$mail->smtpConnect()) {
        throw new Exception("SMTP connection failed");
    }
    echo "✅ SMTP connection successful!<br>";
    $mail->smtpClose();

    echo "<h2>Step 6: Testing Email Send</h2>";
    // Set up email parameters
    $mail->setFrom('yourlifelink.org@gmail.com', 'LifeLink Admin');
    $mail->addAddress('rohithbabu2244@gmail.com'); // Your test email
    $mail->Subject = 'SMTP Test Email';
    $mail->Body = 'This is a test email to verify SMTP configuration.';

    // Try to send
    if(!$mail->send()) {
        throw new Exception("Email send failed: " . $mail->ErrorInfo);
    }
    echo "✅ Test email sent successfully!<br>";

    echo "<h2>✅ All Tests Passed!</h2>";
    echo "Your email configuration is working correctly.";

} catch (Exception $e) {
    echo "<h2>❌ Test Failed</h2>";
    echo "<div style='color: red; margin: 10px 0; padding: 10px; border: 1px solid red;'>";
    echo "Error: " . htmlspecialchars($e->getMessage()) . "<br>";
    echo "</div>";
    
    echo "<h3>Troubleshooting Steps:</h3>";
    echo "<ol>";
    echo "<li>Verify that your Gmail account has 2-Step Verification enabled</li>";
    echo "<li>Confirm that you've generated an App Password correctly</li>";
    echo "<li>Make sure you're using the correct Gmail address</li>";
    echo "<li>Check if your Gmail account has any security alerts</li>";
    echo "<li>Try generating a new App Password</li>";
    echo "</ol>";
}
?>
