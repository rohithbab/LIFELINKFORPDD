<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

require_once 'backend/php/helpers/mailer.php';

echo "<h2>Testing All Email Types</h2>";
echo "<div style='font-family: Arial, sans-serif; max-width: 800px; margin: 20px auto;'>";

function testEmail($mailer, $type, $function, ...$args) {
    echo "<div style='margin: 20px 0; padding: 15px; border: 1px solid #ddd; border-radius: 5px;'>";
    echo "<h3>Testing $type</h3>";
    
    try {
        $result = $mailer->$function(...$args);
        if ($result) {
            echo "<p style='color: green'>✅ Email sent successfully!</p>";
        } else {
            echo "<p style='color: red'>❌ Failed to send email.</p>";
        }
    } catch (Exception $e) {
        echo "<p style='color: red'>❌ Error: " . htmlspecialchars($e->getMessage()) . "</p>";
    }
    
    echo "</div>";
}

try {
    $mailer = new Mailer();
    $testEmail = 'yourlifelink.org@gmail.com';
    
    // Test Donor Emails
    testEmail(
        $mailer,
        'Donor Approval Email',
        'sendDonorApproval',
        $testEmail,
        'John Doe',
        'ODML-D-12345'
    );
    
    testEmail(
        $mailer,
        'Donor Rejection Email',
        'sendDonorRejection',
        $testEmail,
        'John Doe',
        'Missing required medical documentation'
    );
    
    // Test Recipient Emails
    testEmail(
        $mailer,
        'Recipient Approval Email',
        'sendRecipientApproval',
        $testEmail,
        'Jane Smith',
        'ODML-R-12345'
    );
    
    testEmail(
        $mailer,
        'Recipient Rejection Email',
        'sendRecipientRejection',
        $testEmail,
        'Jane Smith',
        'Incomplete medical evaluation report'
    );
    
    echo "<div style='margin-top: 20px; padding: 10px; background: #f5f5f5;'>";
    echo "<p>All test emails have been sent to: $testEmail</p>";
    echo "<p>Please check your inbox for:</p>";
    echo "<ul>";
    echo "<li>Donor Approval Email</li>";
    echo "<li>Donor Rejection Email</li>";
    echo "<li>Recipient Approval Email</li>";
    echo "<li>Recipient Rejection Email</li>";
    echo "</ul>";
    echo "</div>";
    
} catch (Exception $e) {
    echo "<div style='color: red; margin: 20px 0; padding: 15px; border: 1px solid #ffcdd2; background: #ffebee;'>";
    echo "<strong>Error occurred:</strong><br>";
    echo htmlspecialchars($e->getMessage());
    echo "</div>";
}

echo "</div>";
?>
