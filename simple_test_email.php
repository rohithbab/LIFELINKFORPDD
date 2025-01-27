<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

require_once 'backend/php/helpers/mailer.php';

echo "<h2>Simple Email Test</h2>";

try {
    $mailer = new Mailer();
    
    // First, let's test with the Gmail account itself
    $result = $mailer->sendTestEmail('yourlifelink.org@gmail.com');
    
    if ($result) {
        echo "<div style='color: green; margin: 10px 0;'>";
        echo "✅ Email sent successfully to yourlifelink.org@gmail.com!<br>";
        echo "Please check both inbox and spam folder.";
        echo "</div>";
    }
    
} catch (Exception $e) {
    echo "<div style='color: red; margin: 10px 0;'>";
    echo "<strong>Error:</strong> " . $e->getMessage() . "<br>";
    echo "<strong>Trace:</strong><br>";
    echo "<pre>" . $e->getTraceAsString() . "</pre>";
    echo "</div>";
}
?>
