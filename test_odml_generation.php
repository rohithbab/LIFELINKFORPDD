<?php
require_once 'backend/php/helpers/mailer.php';

// Test email address
$test_email = 'yourlifelink.org@gmail.com';

try {
    $mailer = new Mailer();
    
    echo "Testing ODML ID Assignment Emails...\n\n";
    
    // Test Donor ODML ID
    $donor_odml = 'DON' . time() . rand(1000, 9999);
    echo "Generated Donor ODML ID: $donor_odml\n";
    $mailer->sendODMLAssignment(
        $test_email,
        'Test Donor',
        $donor_odml,
        'donor'
    );
    echo "✓ Donor ODML ID email sent successfully\n\n";
    
    // Test Recipient ODML ID
    $recipient_odml = 'REC' . time() . rand(1000, 9999);
    echo "Generated Recipient ODML ID: $recipient_odml\n";
    $mailer->sendODMLAssignment(
        $test_email,
        'Test Recipient',
        $recipient_odml,
        'recipient'
    );
    echo "✓ Recipient ODML ID email sent successfully\n\n";
    
    // Test Hospital ODML ID
    $hospital_odml = 'HOS' . time() . rand(1000, 9999);
    echo "Generated Hospital ODML ID: $hospital_odml\n";
    $mailer->sendODMLAssignment(
        $test_email,
        'Test Hospital',
        $hospital_odml,
        'hospital'
    );
    echo "✓ Hospital ODML ID email sent successfully\n\n";
    
    echo "All ODML ID notifications tested successfully!\n";
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
