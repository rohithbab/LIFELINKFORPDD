<?php
require_once 'backend/php/SimpleEmailService.php';

// Test sending email
$emailService = new SimpleEmailService();
$result = $emailService->sendEmail(
    'yourlifelink.org@gmail.com', // sending to the same email for testing
    'LifeLink Test Email',
    'This is a test email from LifeLink system. If you receive this, the email configuration is working correctly.'
);

if ($result['success']) {
    echo "Email sent successfully!\n";
} else {
    echo "Error sending email: " . $result['message'] . "\n";
}
?>
