<?php
session_start();
require_once 'connection.php';
require_once 'helpers/mailer.php';

header('Content-Type: application/json');

// Check if admin is logged in
if (!isset($_SESSION['admin_id'])) {
    http_response_code(401);
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit();
}

// Get POST data
$contentType = isset($_SERVER["CONTENT_TYPE"]) ? trim($_SERVER["CONTENT_TYPE"]) : '';
if ($contentType === 'application/json') {
    $data = json_decode(file_get_contents('php://input'), true);
} else {
    $data = $_POST;
}

// Validate required fields
if (!isset($data['recipient_id']) || !isset($data['odml_id'])) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Missing required fields']);
    exit();
}

try {
    $recipient_id = $data['recipient_id'];
    $odml_id = $data['odml_id'];
    
    // Update recipient ODML ID
    $stmt = $conn->prepare("UPDATE recipient_registration SET odml_id = ? WHERE id = ?");
    $stmt->bind_param("si", $odml_id, $recipient_id);
    
    if ($stmt->execute()) {
        // Get recipient details
        $stmt = $conn->prepare("SELECT name, email FROM recipient_registration WHERE id = ?");
        $stmt->bind_param("i", $recipient_id);
        $stmt->execute();
        $result = $stmt->get_result();
        $recipient = $result->fetch_assoc();
        
        // Send ODML ID assignment email
        $mailer = new Mailer();
        $mailer->sendODMLAssignment(
            $recipient['email'],
            $recipient['name'],
            $odml_id,
            'recipient'
        );
        
        echo json_encode(['success' => true, 'message' => 'ODML ID updated successfully']);
    } else {
        throw new Exception("Error updating ODML ID");
    }
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
