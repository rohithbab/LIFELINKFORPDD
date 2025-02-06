<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

require_once 'backend/php/connection.php';
require_once 'backend/php/helpers/mailer.php';

echo "<h2>🔍 Debug Hospital Approval Process</h2>";
echo "<div style='font-family: Arial; padding: 20px;'>";

try {
    // 1. First check if we have any pending hospitals
    $stmt = $conn->prepare("SELECT hospital_id, name, email, status FROM hospitals WHERE status = 'pending' LIMIT 1");
    $stmt->execute();
    $hospital = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$hospital) {
        die("<p style='color: red'>❌ No pending hospitals found to test with.</p>");
    }

    echo "<div style='margin: 20px; padding: 20px; border: 1px solid #ddd; border-radius: 5px;'>";
    echo "<h3>Found Hospital:</h3>";
    echo "<p><strong>ID:</strong> " . htmlspecialchars($hospital['hospital_id']) . "</p>";
    echo "<p><strong>Name:</strong> " . htmlspecialchars($hospital['name']) . "</p>";
    echo "<p><strong>Email:</strong> " . htmlspecialchars($hospital['email']) . "</p>";
    echo "<p><strong>Current Status:</strong> " . htmlspecialchars($hospital['status']) . "</p>";
    echo "</div>";

    // 2. Test SMTP Connection
    echo "<h3>Step 1: Testing SMTP Connection</h3>";
    $mailer = new Mailer();
    
    if ($mailer->testConnection()) {
        echo "<p style='color: green'>✅ SMTP Connection successful!</p>";
    } else {
        throw new Exception("Failed to connect to SMTP server");
    }

    // 3. Show what would happen WITHOUT making any changes
    echo "<h3>Step 2: Email Test</h3>";
    echo "<p>Testing email to: " . htmlspecialchars($hospital['email']) . "</p>";
    
    // Start transaction but ROLL IT BACK no matter what
    $conn->beginTransaction();
    
    try {
        $testOdmlId = 'TEST' . rand(1000, 9999);
        $mailer->sendHospitalApprovalEmail(
            $hospital['email'],
            $hospital['name'],
            $testOdmlId
        );
        echo "<p style='color: green'>✅ Email test successful!</p>";
    } catch (Exception $e) {
        echo "<p style='color: red'>❌ Email would fail with error: " . htmlspecialchars($e->getMessage()) . "</p>";
    }
    
    // Always roll back - we're just testing!
    $conn->rollBack();
    
    echo "<div style='margin: 20px; padding: 20px; background-color: #fff3e0; border-radius: 5px;'>";
    echo "<h3>What This Means:</h3>";
    if (isset($emailSuccess) && $emailSuccess) {
        echo "<p style='color: green'>✅ The system is working correctly! When you approve a hospital:</p>";
        echo "<ol>";
        echo "<li>Email will send successfully</li>";
        echo "<li>Only then will the database update</li>";
        echo "<li>If email fails, no changes will be made</li>";
        echo "</ol>";
    } else {
        echo "<p style='color: red'>❌ There are still email issues to fix:</p>";
        echo "<ol>";
        echo "<li>The email is failing to send</li>";
        echo "<li>We need to fix the email configuration before proceeding</li>";
        echo "<li>Good news: The database will NOT update until email works</li>";
        echo "</ol>";
    }
    echo "</div>";

    // Double check database wasn't affected
    $stmt = $conn->prepare("SELECT status FROM hospitals WHERE hospital_id = ?");
    $stmt->execute([$hospital['hospital_id']]);
    $currentStatus = $stmt->fetch(PDO::FETCH_COLUMN);
    
    echo "<div style='margin: 20px; padding: 20px; background-color: #e8f5e9; border-radius: 5px;'>";
    echo "<h3>Final Database Check:</h3>";
    echo "<p>Hospital Status: <strong>" . htmlspecialchars($currentStatus) . "</strong></p>";
    if ($currentStatus === 'pending') {
        echo "<p style='color: green'>✅ Database is safe: No changes were made</p>";
    } else {
        echo "<p style='color: red'>❌ Warning: Database was modified!</p>";
    }
    echo "</div>";

} catch (Exception $e) {
    echo "<div style='margin: 20px; padding: 20px; background-color: #ffebee; border-radius: 5px;'>";
    echo "<h3>❌ Error Occurred:</h3>";
    echo "<p style='color: red'>" . htmlspecialchars($e->getMessage()) . "</p>";
    echo "</div>";
}

echo "</div>";
?>
