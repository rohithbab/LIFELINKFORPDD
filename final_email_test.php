<?php
require_once 'backend/php/connection.php';
require_once 'backend/php/helpers/mailer.php';

error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "<h2>🔍 Final Email Test</h2>";

try {
    // Get the pending hospital
    $stmt = $conn->prepare("SELECT hospital_id, name, email FROM hospitals WHERE status = 'pending' LIMIT 1");
    $stmt->execute();
    $hospital = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$hospital) {
        die("<p style='color: red'>❌ No pending hospitals found to test with.</p>");
    }

    echo "<div style='margin: 20px; padding: 20px; border: 1px solid #ddd; border-radius: 5px;'>";
    echo "<h3>Hospital Details:</h3>";
    echo "<p><strong>Name:</strong> " . htmlspecialchars($hospital['name']) . "</p>";
    echo "<p><strong>Email:</strong> " . htmlspecialchars($hospital['email']) . "</p>";
    echo "</div>";

    // Create test ODML ID
    $testOdmlId = 'TEST' . rand(1000, 9999);
    echo "<p><strong>Test ODML ID:</strong> " . htmlspecialchars($testOdmlId) . "</p>";

    echo "<h3>Step 1: Testing SMTP Connection</h3>";
    $mailer = new Mailer();
    
    if ($mailer->testConnection()) {
        echo "<p style='color: green'>✅ SMTP Connection successful!</p>";
    } else {
        throw new Exception("Failed to connect to SMTP server");
    }

    echo "<h3>Step 2: Sending Test Email</h3>";
    $mailer->sendHospitalApprovalEmail(
        $hospital['email'],
        $hospital['name'],
        $testOdmlId
    );

    echo "<div style='margin: 20px; padding: 20px; background-color: #e8f5e9; border-radius: 5px;'>";
    echo "<h3>✅ Test Completed Successfully!</h3>";
    echo "<p>1. SMTP Connection: <span style='color: green'>Working</span></p>";
    echo "<p>2. Email Sending: <span style='color: green'>Working</span></p>";
    echo "<p>An approval email has been sent to: " . htmlspecialchars($hospital['email']) . "</p>";
    echo "</div>";

    echo "<div style='margin: 20px; padding: 20px; background-color: #fff3e0; border-radius: 5px;'>";
    echo "<h3>Next Steps:</h3>";
    echo "<ol>";
    echo "<li>Check your email inbox for the test email</li>";
    echo "<li>Verify the email formatting looks correct</li>";
    echo "<li>Confirm the ODML ID appears in the email</li>";
    echo "</ol>";
    echo "<p><strong>If everything looks good, you can proceed with the real ODML ID update!</strong></p>";
    echo "</div>";

    // Double check database wasn't affected
    $stmt = $conn->prepare("SELECT status FROM hospitals WHERE hospital_id = ?");
    $stmt->execute([$hospital['hospital_id']]);
    $status = $stmt->fetch(PDO::FETCH_COLUMN);
    
    echo "<div style='margin: 20px; padding: 20px; background-color: #e8f5e9; border-radius: 5px;'>";
    echo "<h3>Database Check:</h3>";
    echo "<p>Hospital Status: <strong>" . htmlspecialchars($status) . "</strong></p>";
    if ($status === 'pending') {
        echo "<p style='color: green'>✅ Database integrity verified: No changes were made</p>";
    } else {
        echo "<p style='color: red'>❌ Warning: Database was modified!</p>";
    }
    echo "</div>";

} catch (Exception $e) {
    echo "<div style='margin: 20px; padding: 20px; background-color: #ffebee; border-radius: 5px;'>";
    echo "<h3>❌ Error Occurred:</h3>";
    echo "<p style='color: red'>" . htmlspecialchars($e->getMessage()) . "</p>";
    echo "<p>Please fix this error before proceeding with the real update!</p>";
    echo "</div>";
}
?>
