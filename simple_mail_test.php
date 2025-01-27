<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

require_once 'backend/php/helpers/mailer.php';

echo "<h2>Simple Mail Test</h2>";

try {
    $mailer = new Mailer();
    $result = $mailer->sendTestEmail('yourlifelink.org@gmail.com');
    
    if ($result) {
        echo "<p style='color: green;'>✅ Email sent successfully! Check your inbox and spam folder.</p>";
    } else {
        echo "<p style='color: red;'>❌ Failed to send email. Check the error messages above.</p>";
    }
} catch (Exception $e) {
    echo "<p style='color: red;'>❌ Error: " . htmlspecialchars($e->getMessage()) . "</p>";
}
?>
