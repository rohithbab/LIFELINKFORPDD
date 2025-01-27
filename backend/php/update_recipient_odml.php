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
if (!isset($data['recipient_id']) || !isset($data['odml_id']) || !isset($data['action'])) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Missing required fields']);
    exit();
}

try {
    // Start transaction
    $conn->beginTransaction();

    $recipient_id = $data['recipient_id'];
    $odml_id = $data['odml_id'];
    $action = $data['action'];
    
    // Update recipient ODML ID and status if approving
    if ($action === 'approve') {
        $stmt = $conn->prepare("UPDATE recipient_registration SET odml_id = ?, request_status = 'approved', updated_at = NOW() WHERE id = ?");
        $stmt->execute([$odml_id, $recipient_id]);
    } else {
        $stmt = $conn->prepare("UPDATE recipient_registration SET odml_id = ?, updated_at = NOW() WHERE id = ?");
        $stmt->execute([$odml_id, $recipient_id]);
    }
    
    // Get recipient details
    $stmt = $conn->prepare("SELECT full_name as name, email FROM recipient_registration WHERE id = ?");
    $stmt->execute([$recipient_id]);
    $recipient = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$recipient) {
        throw new Exception('Recipient not found');
    }
    
    // Send ODML ID assignment email
    $mailer = new Mailer();
    $mailer->sendODMLAssignment(
        $recipient['email'],
        $recipient['name'],
        $odml_id,
        'recipient'
    );
    
    // Commit transaction
    $conn->commit();
    
    echo json_encode(['success' => true, 'message' => 'ODML ID updated successfully']);
    
} catch (Exception $e) {
    // Rollback transaction on error
    if ($conn->inTransaction()) {
        $conn->rollBack();
    }
    
    error_log("Error updating recipient ODML ID: " . $e->getMessage());
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Failed to update ODML ID']);
}
?>
