<?php
require_once 'backend/php/helpers/mailer.php';

// Enable error reporting
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Create mailer instance
$mailer = new Mailer();

// Test email address (change this to your email)
$testEmail = 'yourlifelink.org@gmail.com';

echo "<h2>LifeLink Email Testing</h2>";

try {
    // 1. Test basic email functionality
    echo "<h3>1. Testing Basic Email:</h3>";
    $result = $mailer->sendTestEmail($testEmail);
    echo $result ? "✅ Basic email sent successfully!" : "❌ Failed to send basic email";
    echo "<br><br>";

    // 2. Test Hospital Emails
    echo "<h3>2. Testing Hospital Emails:</h3>";
    
    // Approval
    $result = $mailer->sendHospitalApproval(
        $testEmail,
        'City General Hospital',
        'ODML-H-12345'
    );
    echo $result ? "✅ Hospital approval email sent!" : "❌ Failed to send hospital approval email";
    echo "<br>";
    
    // Rejection
    $result = $mailer->sendHospitalRejection(
        $testEmail,
        'City General Hospital',
        'Missing required certification documents. Please provide valid hospital registration certificate.'
    );
    echo $result ? "✅ Hospital rejection email sent!" : "❌ Failed to send hospital rejection email";
    echo "<br><br>";

    // 3. Test Donor Emails
    echo "<h3>3. Testing Donor Emails:</h3>";
    
    // Approval
    $result = $mailer->sendDonorApproval(
        $testEmail,
        'John Doe',
        'ODML-D-12345'
    );
    echo $result ? "✅ Donor approval email sent!" : "❌ Failed to send donor approval email";
    echo "<br>";
    
    // Rejection
    $result = $mailer->sendDonorRejection(
        $testEmail,
        'John Doe',
        'Medical history documentation incomplete. Please provide complete medical records.'
    );
    echo $result ? "✅ Donor rejection email sent!" : "❌ Failed to send donor rejection email";
    echo "<br><br>";

    // 4. Test Recipient Emails
    echo "<h3>4. Testing Recipient Emails:</h3>";
    
    // Approval
    $result = $mailer->sendRecipientApproval(
        $testEmail,
        'Jane Smith',
        'ODML-R-12345'
    );
    echo $result ? "✅ Recipient approval email sent!" : "❌ Failed to send recipient approval email";
    echo "<br>";
    
    // Rejection
    $result = $mailer->sendRecipientRejection(
        $testEmail,
        'Jane Smith',
        'Medical evaluation report missing. Please submit recent medical evaluation from your healthcare provider.'
    );
    echo $result ? "✅ Recipient rejection email sent!" : "❌ Failed to send recipient rejection email";
    echo "<br>";

} catch (Exception $e) {
    echo "<div style='color: red; margin: 20px 0;'>";
    echo "<strong>Error:</strong> " . $e->getMessage();
    echo "</div>";
}
?>

<style>
    body {
        font-family: Arial, sans-serif;
        max-width: 800px;
        margin: 20px auto;
        padding: 20px;
        line-height: 1.6;
    }
    h2 {
        color: #2196F3;
        border-bottom: 2px solid #eee;
        padding-bottom: 10px;
    }
    h3 {
        color: #666;
        margin-top: 20px;
    }
</style>
