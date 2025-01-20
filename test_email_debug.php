<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "PHP Version: " . PHP_VERSION . "\n";
echo "Loaded extensions:\n";
print_r(get_loaded_extensions());

echo "\nChecking OpenSSL:\n";
if (extension_loaded('openssl')) {
    echo "OpenSSL is loaded\n";
    echo "OpenSSL version: " . OPENSSL_VERSION_TEXT . "\n";
} else {
    echo "OpenSSL is NOT loaded\n";
}

require_once 'backend/php/SimpleEmailService.php';

try {
    echo "\nInitializing email service...\n";
    $emailService = new SimpleEmailService();
    
    echo "\nSending test email...\n";
    $result = $emailService->sendEmail(
        'yourlifelink.org@gmail.com',
        'LifeLink Test Email',
        'This is a test email from LifeLink system. If you receive this, the email configuration is working correctly.'
    );

    if ($result['success']) {
        echo "Email sent successfully!\n";
    } else {
        echo "Error sending email: " . $result['message'] . "\n";
    }
} catch (Exception $e) {
    echo "Exception caught: " . $e->getMessage() . "\n";
    echo "Stack trace:\n" . $e->getTraceAsString() . "\n";
}
?>
