<?php
// Enable error reporting
error_reporting(E_ALL);
ini_set('display_errors', 1);

require_once 'vendor/autoload.php';
require_once 'config/email_config.php';
require_once 'backend/php/helpers/mailer.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;
use PHPMailer\PHPMailer\Exception;

// Test configuration
$testEmail = 'rohithbabu2244@gmail.com';
$testHospitalName = 'Test Hospital';
$testODMLID = 'ODML' . rand(1000, 9999); // Random ODML ID for testing
$testRejectionReason = 'Documentation incomplete. Please provide updated medical license.'; // Sample rejection reason

function sendTestEmail($mail, $subject, $body) {
    try {
        $mail->clearAddresses();
        $mail->clearAttachments();
        $mail->Subject = $subject;
        $mail->Body = $body;
        $mail->addAddress('rohithbabu2244@gmail.com');
        $mail->send();
        return true;
    } catch (Exception $e) {
        echo "Error sending email: " . $e->getMessage() . "\n";
        return false;
    }
}

echo "Starting Email Tests...\n\n";

try {
    // Initialize PHPMailer
    $mail = new PHPMailer(true);
    
    // Server settings with debug output
    $mail->SMTPDebug = SMTP::DEBUG_SERVER;
    $mail->isSMTP();
    $mail->Host = 'smtp.gmail.com';
    $mail->SMTPAuth = true;
    $mail->Username = 'yourlifelink.org@gmail.com';
    $mail->Password = 'gfnb wnxc pmgj eikm';
    $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
    $mail->Port = 587;
    $mail->isHTML(true);
    $mail->setFrom('yourlifelink.org@gmail.com', 'LifeLink Admin');
    
    echo "Testing Hospital Approval Email (Dashboard)...\n";
    $approvalSubject = "LifeLink - Hospital Registration Approved";
    $approvalBody = "
        <div style='font-family: Arial, sans-serif; max-width: 600px; margin: 0 auto;'>
            <h2 style='color: #2196F3;'>Hospital Registration Approved</h2>
            <p>Dear {$testHospitalName},</p>
            <p>We are pleased to inform you that your hospital registration has been approved.</p>
            <p>Your ODML ID is: <strong>{$testODMLID}</strong></p>
            <p>You can now access all features of the LifeLink platform.</p>
            <p>Best regards,<br>LifeLink Admin Team</p>
        </div>";
    
    if(sendTestEmail($mail, $approvalSubject, $approvalBody)) {
        echo "✓ Hospital Approval Email sent successfully\n\n";
    }
    
    echo "Testing Hospital Rejection Email...\n";
    $rejectionSubject = "LifeLink - Hospital Registration Status Update";
    $rejectionBody = "
        <div style='font-family: Arial, sans-serif; max-width: 600px; margin: 0 auto;'>
            <h2 style='color: #f44336;'>Registration Update</h2>
            <p>Dear {$testHospitalName},</p>
            <p>We regret to inform you that your hospital registration could not be approved at this time.</p>
            <p><strong>Reason:</strong> {$testRejectionReason}</p>
            <p>You may address these concerns and submit a new application.</p>
            <p>Best regards,<br>LifeLink Admin Team</p>
        </div>";
    
    if(sendTestEmail($mail, $rejectionSubject, $rejectionBody)) {
        echo "✓ Hospital Rejection Email sent successfully\n\n";
    }
    
    $mailer = new Mailer();
    
    // Replace this with your email address where you want to receive the test email
    $testEmail = 'yourlifelink.org@gmail.com'; // Change this to your email
    
    echo "<h2>Testing Email Configuration</h2>";
    echo "<p>Attempting to send test email to: " . htmlspecialchars($testEmail) . "</p>";
    
    $mailer->sendTestEmail($testEmail);
    
    echo "<p style='color: green;'>✓ Test email sent successfully! Check your inbox.</p>";
    
} catch (Exception $e) {
    echo "<p style='color: red;'>✗ Error sending email: " . htmlspecialchars($e->getMessage()) . "</p>";
    
    // Additional debugging information
    echo "<h3>Debug Information:</h3>";
    echo "<pre style='background: #f5f5f5; padding: 15px;'>";
    echo "Error Details: " . htmlspecialchars($e->getMessage()) . "\n";
    echo "File: " . htmlspecialchars($e->getFile()) . "\n";
    echo "Line: " . $e->getLine() . "\n";
    echo "</pre>";
}

echo "\nEmail testing completed. Check your inbox at {$testEmail}\n";
echo "Test ODML ID used: {$testODMLID}\n";
echo "Test Rejection Reason used: {$testRejectionReason}\n";
