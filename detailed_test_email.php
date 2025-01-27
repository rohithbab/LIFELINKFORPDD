<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "<h2>Detailed Email Test</h2>";
echo "<div style='font-family: monospace; background: #f5f5f5; padding: 20px; margin: 20px 0;'>";

// Check PHP version
echo "PHP Version: " . phpversion() . "<br>";

// Check if required extensions are loaded
echo "<br>Checking required extensions:<br>";
echo "- OpenSSL: " . (extension_loaded('openssl') ? '✅' : '❌') . "<br>";
echo "- SMTP: " . (extension_loaded('smtp') ? '✅' : '❌') . "<br>";

// Test SMTP connection
echo "<br>Testing SMTP connection...<br>";
$smtp = fsockopen('smtp.gmail.com', 587, $errno, $errstr, 30);
if (!$smtp) {
    echo "❌ SMTP connection failed: $errstr ($errno)<br>";
} else {
    echo "✅ SMTP connection successful<br>";
    fclose($smtp);
}

echo "<br>Starting email test...<br>";
require_once 'backend/php/helpers/mailer.php';

try {
    $mailer = new Mailer();
    $result = $mailer->sendTestEmail('yourlifelink.org@gmail.com');
    
    if ($result) {
        echo "<br>✅ Email test completed successfully!<br>";
    } else {
        echo "<br>❌ Email test failed. Check the debug output above for details.<br>";
    }
} catch (Exception $e) {
    echo "<br>❌ Test failed with error:<br>";
    echo $e->getMessage() . "<br>";
}

echo "</div>";
?>
