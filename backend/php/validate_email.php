<?php
require_once 'EmailValidator.php';

header('Content-Type: application/json');

if (!isset($_GET['email'])) {
    echo json_encode([
        'valid' => false,
        'message' => 'Email parameter is required'
    ]);
    exit;
}

$validator = new EmailValidator();
$result = $validator->validateEmail($_GET['email']);

echo json_encode($result);
?>
