<?php
// Show all errors
error_reporting(E_ALL);
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);

echo "<h2>Simple Email Test</h2>";

// Check if PHPMailer files exist
$files_to_check = [
    'backend/php/PHPMailer/PHPMailer.php',
    'backend/php/PHPMailer/SMTP.php',
    'backend/php/PHPMailer/Exception.php',
    'backend/php/helpers/mailer.php'
];

echo "<h3>Checking Required Files:</h3>";
foreach ($files_to_check as $file) {
    if (file_exists($file)) {
        echo "✅ Found: {$file}<br>";
    } else {
        echo "❌ Missing: {$file}<br>";
    }
}

echo "<h3>Testing Email Configuration:</h3>";

try {
    echo "Loading mailer.php...<br>";
    require_once 'backend/php/helpers/mailer.php';
    echo "✅ Mailer.php loaded successfully<br>";
    
    echo "Creating Mailer instance...<br>";
    $mailer = new Mailer();
    echo "✅ Mailer instance created<br>";
    
    echo "<br>Attempting to send test email...<br>";
    // Test sending to your email
    $result = $mailer->sendTestEmail('rohithbabu2244@gmail.com');
    
    if ($result) {
        echo "<div style='color: green; margin: 10px 0;'>";
        echo "✅ Email sent successfully to rohithbabu2244@gmail.com!<br>";
        echo "Please check both inbox and spam folder.";
        echo "</div>";
    }
    
    echo "<br>Attempting to send hospital approval email...<br>";
    // Now test hospital approval email
    $testHospitalName = "Test Hospital";
    $testODMLID = "ODML" . rand(1000, 9999);
    
    $approvalResult = $mailer->sendHospitalApprovalEmail(
        'rohithbabu2244@gmail.com',
        $testHospitalName,
        $testODMLID
    );
    
    if ($approvalResult) {
        echo "<div style='color: green; margin: 10px 0;'>";
        echo "✅ Hospital Approval Email sent successfully!<br>";
        echo "ODML ID used: " . $testODMLID . "<br>";
        echo "Please check both inbox and spam folder.";
        echo "</div>";
    }
    
} catch (Throwable $e) {
    echo "<div style='color: red; margin: 10px 0;'>";
    echo "<strong>Error:</strong> " . $e->getMessage() . "<br>";
    echo "<strong>File:</strong> " . $e->getFile() . "<br>";
    echo "<strong>Line:</strong> " . $e->getLine() . "<br>";
    echo "<strong>Trace:</strong><br>";
    echo "<pre>" . $e->getTraceAsString() . "</pre>";
    echo "</div>";
}

echo "<h3>SMTP Testing:</h3>";

require_once 'backend/php/PHPMailer/PHPMailer.php';
require_once 'backend/php/PHPMailer/SMTP.php';
require_once 'backend/php/PHPMailer/Exception.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;
use PHPMailer\PHPMailer\Exception;

try {
    $mail = new PHPMailer(true);

    // Enable verbose debug output
    $mail->SMTPDebug = SMTP::DEBUG_SERVER;
    $mail->Debugoutput = 'html';

    // Server settings
    $mail->isSMTP();
    $mail->Host = 'smtp.gmail.com';
    $mail->Port = 587;
    $mail->SMTPAuth = true;
    $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;

    // Gmail credentials
    $mail->Username = 'yourlifelink.org@gmail.com';
    $mail->Password = 'wxhj ppdl ebsh wing'; // Your App Password

    // Recipients
    $mail->setFrom('yourlifelink.org@gmail.com', 'LifeLink Admin');
    $mail->addAddress('rohithbabu2244@gmail.com'); // Your test email

    // Content
    $mail->isHTML(false);
    $mail->Subject = 'Simple Test Email';
    $mail->Body = 'This is a simple test email to verify SMTP connection.';

    echo "<h2>Testing SMTP Connection...</h2>";
    
    // Test SMTP connection first
    if ($mail->smtpConnect()) {
        echo "<p style='color: green'>✅ SMTP Connection successful!</p>";
        $mail->smtpClose();
        
        echo "<h2>Sending Test Email...</h2>";
        if ($mail->send()) {
            echo "<p style='color: green'>✅ Test email sent successfully!</p>";
        }
    } else {
        echo "<p style='color: red'>❌ SMTP Connection failed</p>";
    }

} catch (Exception $e) {
    echo "<h2>Error</h2>";
    echo "<p style='color: red'>Message could not be sent. Mailer Error: {$mail->ErrorInfo}</p>";
    echo "<p>Error details: " . $e->getMessage() . "</p>";
}

// Check PHP version and extensions
echo "<h3>System Information:</h3>";
echo "PHP Version: " . phpversion() . "<br>";
echo "Loaded Extensions:<br>";
echo "<pre>";
print_r(get_loaded_extensions());
echo "</pre>";
?>
