<?php
require_once 'backend/php/helpers/email_validator.php';

// Test cases
$testEmails = [
    'test@gmail.com',          // Valid free email
    'invalid.email@test',      // Invalid format
    'nonexistent@domain.xyz',  // Likely non-existent domain
    'test@tempmail.com'        // Likely disposable email
];

$validator = new EmailValidator();

foreach ($testEmails as $email) {
    echo "Testing email: $email\n";
    try {
        $validator->validateEmail($email);
        echo "✓ Email is valid and deliverable\n";
    } catch (Exception $e) {
        echo "✗ " . $e->getMessage() . "\n";
    }
    echo "\n";
}
