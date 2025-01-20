<?php
require_once 'backend/php/helpers/mailer.php';

// Enable error reporting for debugging
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Test email address
$test_email = 'yourlifelink.org@gmail.com';

try {
    // First check if email templates exist
    $template_dir = __DIR__ . '/email_templates/';
    $templates = [
        'odml_assignment.html',
        'approval_notification.html',
        'rejection_notification.html'
    ];
    
    echo "Checking email templates...\n";
    foreach ($templates as $template) {
        $template_path = $template_dir . $template;
        if (file_exists($template_path)) {
            echo "✓ Found template: $template\n";
            // Read first few characters to verify content
            $content = file_get_contents($template_path);
            if (strpos($content, '<!DOCTYPE html>') !== false) {
                echo "  ✓ Template content looks valid\n";
            } else {
                echo "  ⚠ Template might have issues\n";
            }
        } else {
            echo "✕ Missing template: $template\n";
            exit("Please ensure all email templates are present before testing.\n");
        }
    }
    echo "\n";
    
    $mailer = new Mailer();
    
    echo "Testing ODML ID Assignment Email...\n";
    
    // Test with a sample ODML ID
    $test_odml = 'TEST123456';
    
    echo "Sending test email to: $test_email\n";
    echo "Using ODML ID: $test_odml\n\n";
    
    $mailer->sendODMLAssignment(
        $test_email,
        'Test User',
        $test_odml,
        'donor'
    );
    
    echo "✓ Email sent successfully!\n";
    echo "Please check your inbox at: $test_email\n";
    
} catch (Exception $e) {
    echo "Error sending email:\n";
    echo $e->getMessage() . "\n";
    
    // Print detailed error information
    if (isset($mailer) && isset($mailer->mail)) {
        echo "\nSMTP Error Info:\n";
        print_r($mailer->mail->ErrorInfo);
    }
}
