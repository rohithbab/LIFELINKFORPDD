<?php
require_once 'backend/php/helpers/mailer.php';

// Test email address
$test_email = 'yourlifelink.org@gmail.com';

try {
    $mailer = new Mailer();
    
    echo "Testing ODML ID Assignment Email...\n";
    $mailer->sendODMLAssignment(
        $test_email,
        'Test User',
        'DON' . time() . rand(1000, 9999),
        'donor'
    );
    echo "✓ ODML ID Assignment email sent successfully\n\n";
    
    echo "Testing Approval Notification Email...\n";
    $mailer->sendApprovalNotification(
        $test_email,
        'Test User',
        'donor',
        'DON' . time() . rand(1000, 9999)
    );
    echo "✓ Approval notification email sent successfully\n\n";
    
    echo "Testing Rejection Notification Email...\n";
    $mailer->sendRejectionNotification(
        $test_email,
        'Test User',
        'donor',
        'Application documents were incomplete'
    );
    echo "✓ Rejection notification email sent successfully\n\n";
    
    echo "All email notifications tested successfully!\n";
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
