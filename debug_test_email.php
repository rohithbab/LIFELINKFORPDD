<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

require_once 'backend/php/helpers/mailer.php';

echo "<h2>Debug Email Test</h2>";
echo "<pre>";

try {
    echo "Creating mailer instance...\n";
    $mailer = new Mailer();
    
    echo "Attempting to send test email...\n";
    $result = $mailer->sendTestEmail('yourlifelink.org@gmail.com');
    
    if ($result) {
        echo "\n✅ Success! Email sent successfully.\n";
        echo "Please check both inbox and spam folder of yourlifelink.org@gmail.com\n";
    } else {
        echo "\n❌ Failed to send email.\n";
    }
    
} catch (Exception $e) {
    echo "\n❌ Error occurred:\n";
    echo "Message: " . $e->getMessage() . "\n";
    echo "File: " . $e->getFile() . " (Line: " . $e->getLine() . ")\n";
    echo "\nStack trace:\n" . $e->getTraceAsString() . "\n";
}

echo "</pre>";
?>
