<?php
require_once __DIR__ . '/backend/php/helpers/mailer.php';

try {
    $mailer = new Mailer();
    
    // Test hospital approval email
    echo "<h2>Testing Hospital Approval Email</h2>";
    $mailer->sendHospitalApprovalEmail(
        'rohithbabu2244@gmail.com',
        'City General Hospital',
        'ODML-H-2024001'
    );
    echo "<p style='color: green;'>✅ Hospital approval email sent successfully!</p>";
    
    // Wait a bit before sending the next email
    sleep(2);
    
    // Test hospital rejection email
    echo "<h2>Testing Hospital Rejection Email</h2>";
    $mailer->sendHospitalRejectionEmail(
        'rohithbabu2244@gmail.com',
        'City General Hospital',
        'Missing required certifications and documentation. Please ensure all necessary medical licenses and facility certifications are provided.'
    );
    echo "<p style='color: green;'>✅ Hospital rejection email sent successfully!</p>";
    
} catch (Exception $e) {
    echo "<div style='color: red; padding: 10px; background: #fee; border: 1px solid #faa; margin: 10px 0;'>";
    echo "<strong>Error:</strong><br>";
    echo htmlspecialchars($e->getMessage());
    echo "</div>";
    
    if (property_exists($e, 'errorInfo')) {
        echo "<pre>" . print_r($e->errorInfo, true) . "</pre>";
    }
}
?>
