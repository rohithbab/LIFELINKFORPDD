<?php
header('Content-Type: application/json');
require_once 'connection.php';
require_once 'helpers/enhanced_email_validator.php';

// Get API key from config
$config = require_once __DIR__ . '/config/config.php';
$api_key = $config['email_validator_api_key'];

try {
    if (!isset($_GET['email'])) {
        throw new Exception('Email parameter is required');
    }

    $email = trim($_GET['email']);
    $validator = new EnhancedEmailValidator($conn, $api_key);

    // Perform basic format validation first
    if (!$validator->validateEmailFormat($email)) {
        throw new Exception('Invalid email format');
    }

    // If table parameter is provided, check for duplicates
    $table = $_GET['table'] ?? null;
    if ($table) {
        try {
            $validator->validateEmail($email, $table);
        } catch (Exception $e) {
            if (strpos($e->getMessage(), 'already registered') !== false) {
                echo json_encode([
                    'valid' => false,
                    'message' => $e->getMessage()
                ]);
                exit;
            }
        }
    }

    // Perform API validation
    try {
        $validator->validateEmailAPI($email);
        echo json_encode([
            'valid' => true,
            'message' => 'Email is valid'
        ]);
    } catch (Exception $e) {
        echo json_encode([
            'valid' => false,
            'message' => $e->getMessage()
        ]);
    }

} catch (Exception $e) {
    echo json_encode([
        'valid' => false,
        'message' => $e->getMessage()
    ]);
}
