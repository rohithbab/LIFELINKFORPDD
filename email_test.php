<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

require_once 'backend/php/helpers/mailer.php';

try {
    $mailer = new Mailer();
    
    // Test Hospital Approval Email
    echo "<h3>Testing Hospital Approval Email</h3>";
    $result = $mailer->sendHospitalApproval('yourlifelink.org@gmail.com', 'Test Hospital', 'ODML-H-12345');
    echo $result ? "✅ Hospital approval email sent successfully<br>" : "❌ Failed to send hospital approval email<br>";
    
    // Test Donor Approval Email
    echo "<h3>Testing Donor Approval Email</h3>";
    $result = $mailer->sendDonorApproval('yourlifelink.org@gmail.com', 'Test Donor', 'ODML-D-12345');
    echo $result ? "✅ Donor approval email sent successfully<br>" : "❌ Failed to send donor approval email<br>";
    
    // Test Recipient Approval Email
    echo "<h3>Testing Recipient Approval Email</h3>";
    $result = $mailer->sendRecipientApproval('yourlifelink.org@gmail.com', 'Test Recipient', 'ODML-R-12345');
    echo $result ? "✅ Recipient approval email sent successfully<br>" : "❌ Failed to send recipient approval email<br>";
    
} catch (Exception $e) {
    echo "<h2 style='color: red;'>Error:</h2>";
    echo "<pre>" . htmlspecialchars($e->getMessage()) . "</pre>";
}
?>
