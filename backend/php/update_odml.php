<?php
error_reporting(E_ALL);
ini_set('display_errors', 0);
header('Content-Type: application/json');

require_once 'connection.php';
require_once __DIR__ . '/helpers/email_sender.php';

try {
    // Get JSON input
    $input = json_decode(file_get_contents('php://input'), true);
    
    if (!isset($input['type']) || !isset($input['id']) || !isset($input['odmlId'])) {
        throw new Exception('Missing required parameters');
    }

    $type = $input['type'];
    $id = $input['id'];
    $odmlId = trim($input['odmlId']);

    // Only check if ODML ID is not empty
    if (empty($odmlId)) {
        throw new Exception('ODML ID cannot be empty');
    }

    $pdo = getConnection();
    $emailSender = new EmailSender();

    // Update ODML ID based on type
    switch($type) {
        case 'donor':
            $table = 'donor';
            $idColumn = 'donor_id';
            break;
        case 'recipient':
            $table = 'recipient_registration';
            $idColumn = 'id';  
            break;
        case 'hospital':
            $table = 'hospitals';
            $idColumn = 'hospital_id';
            break;
        default:
            throw new Exception('Invalid type specified');
    }

    // Update the record
    $stmt = $pdo->prepare("UPDATE $table SET odml_id = ?, status = 'approved' WHERE $idColumn = ?");
    $stmt->execute([$odmlId, $id]);

    if ($stmt->rowCount() === 0) {
        throw new Exception('No record found to update');
    }

    // Get user details for email
    $stmt = $pdo->prepare("SELECT name, email FROM $table WHERE $idColumn = ?");
    $stmt->execute([$id]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$user) {
        throw new Exception('User not found');
    }

    // Send email notification
    $emailSender->sendODMLUpdateEmail($user['email'], $user['name'], $type, $odmlId);

    echo json_encode([
        'success' => true,
        'message' => 'ODML ID updated successfully and notification email sent'
    ]);

} catch (Exception $e) {
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}
