<?php
require_once __DIR__ . '/backend/php/helpers/mailer.php';

try {
    $mailer = new Mailer();
    
    // Test approval email
    echo "<h2>Testing Hospital Approval Email</h2>";
    $mailer->sendHospitalApprovalEmail(
        'rohithbabu2244@gmail.com',
        'Test Hospital',
        'ODML123456'
    );
    echo "<p style='color: green;'>✅ Approval email sent successfully!</p>";
    
    // Test rejection email
    echo "<h2>Testing Hospital Rejection Email</h2>";
    $mailer->sendHospitalRejectionEmail(
        'rohithbabu2244@gmail.com',
        'Test Hospital',
        'Missing required documentation and license information.'
    );
    echo "<p style='color: green;'>✅ Rejection email sent successfully!</p>";
    
} catch (Exception $e) {
    echo "<p style='color: red;'>❌ Error: " . htmlspecialchars($e->getMessage()) . "</p>";
}
?>
