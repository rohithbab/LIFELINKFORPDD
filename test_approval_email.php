<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

require_once 'backend/php/helpers/mailer.php';

echo "<h2>Testing Approval Email</h2>";

try {
    $mailer = new Mailer();
    
    // Test hospital approval email
    $result = $mailer->sendHospitalApproval(
        'yourlifelink.org@gmail.com',
        'City General Hospital',
        'ODML-H-12345'
    );
    
    if ($result) {
        echo "<p style='color: green'>✅ Hospital approval email sent successfully!</p>";
        echo "<p>Check your inbox for the approval email.</p>";
    } else {
        echo "<p style='color: red'>❌ Failed to send approval email.</p>";
    }
} catch (Exception $e) {
    echo "<p style='color: red'>❌ Error: " . htmlspecialchars($e->getMessage()) . "</p>";
}
?>
