<?php
require_once 'backend/php/connection.php';
require_once 'backend/php/helpers/mailer.php';

// Show all errors for debugging
error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "<h2>Hospital Email Test</h2>";

try {
    // Get a pending hospital
    $stmt = $conn->prepare("SELECT hospital_id, name, email FROM hospitals WHERE status = 'pending' LIMIT 1");
    $stmt->execute();
    $hospital = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$hospital) {
        die("<p style='color: red'>❌ No pending hospitals found to test with.</p>");
    }

    echo "<div style='margin: 20px 0; padding: 15px; background: #f5f5f5; border-radius: 5px;'>";
    echo "<h3>Testing with Hospital:</h3>";
    echo "<p><strong>Name:</strong> " . htmlspecialchars($hospital['name']) . "</p>";
    echo "<p><strong>Email:</strong> " . htmlspecialchars($hospital['email']) . "</p>";
    echo "</div>";

    // Generate a test ODML ID
    $testOdmlId = 'TEST' . rand(1000, 9999);
    echo "<p><strong>Test ODML ID:</strong> " . htmlspecialchars($testOdmlId) . "</p>";

    // Create mailer instance
    $mailer = new Mailer();
    
    echo "<h3>Attempting to send test email...</h3>";
    
    // Try to send the email
    $mailer->sendHospitalApprovalEmail(
        $hospital['email'],
        $hospital['name'],
        $testOdmlId
    );

    echo "<div style='margin: 20px 0; padding: 15px; background: #e8f5e9; border-radius: 5px;'>";
    echo "<p style='color: green'>✅ Test email sent successfully!</p>";
    echo "<p>An approval email has been sent to: " . htmlspecialchars($hospital['email']) . "</p>";
    echo "<p><strong>Note:</strong> The hospital status is still 'pending' in the database. No changes were made.</p>";
    echo "</div>";

    echo "<div style='margin: 20px 0; padding: 15px; background: #fff3e0; border-radius: 5px;'>";
    echo "<h3>What to check:</h3>";
    echo "<ol>";
    echo "<li>Check your email inbox (and spam folder) for the approval email</li>";
    echo "<li>Verify that the ODML ID appears correctly in the email</li>";
    echo "<li>Confirm that the email formatting looks good</li>";
    echo "</ol>";
    echo "</div>";

    // Verify database wasn't changed
    $stmt = $conn->prepare("SELECT status FROM hospitals WHERE hospital_id = ?");
    $stmt->execute([$hospital['hospital_id']]);
    $currentStatus = $stmt->fetch(PDO::FETCH_COLUMN);

    echo "<div style='margin: 20px 0; padding: 15px; background: #e8f5e9; border-radius: 5px;'>";
    echo "<h3>Database Check:</h3>";
    echo "<p>Current hospital status: <strong>" . htmlspecialchars($currentStatus) . "</strong></p>";
    if (strtolower($currentStatus) === 'pending') {
        echo "<p style='color: green'>✅ Database integrity verified: Hospital status remained unchanged</p>";
    } else {
        echo "<p style='color: red'>❌ Warning: Hospital status has changed! This should not happen!</p>";
    }
    echo "</div>";

} catch (Exception $e) {
    echo "<div style='margin: 20px 0; padding: 15px; background: #ffebee; border-radius: 5px;'>";
    echo "<h3>Error Occurred:</h3>";
    echo "<p style='color: red'>" . htmlspecialchars($e->getMessage()) . "</p>";
    echo "</div>";
}
?>
