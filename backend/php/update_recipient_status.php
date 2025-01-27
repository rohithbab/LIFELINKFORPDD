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
if (!isset($data['recipient_id']) || !isset($data['status'])) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Missing required fields']);
    exit();
}

// Validate status
$allowed_statuses = ['pending', 'approved', 'rejected'];
if (!in_array(strtolower($data['status']), $allowed_statuses)) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Invalid status']);
    exit();
}

try {
    // Begin transaction
    $conn->beginTransaction();

    // Get recipient details
    $stmt = $conn->prepare("SELECT full_name as name, email FROM recipient_registration WHERE id = ?");
    $stmt->execute([$data['recipient_id']]);
    $recipient = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$recipient) {
        throw new Exception("Recipient not found");
    }

    // Update recipient status
    if ($data['status'] === 'rejected' && isset($data['reason'])) {
        $stmt = $conn->prepare("UPDATE recipient_registration SET request_status = ?, rejection_reason = ?, updated_at = NOW() WHERE id = ?");
        $stmt->execute([$data['status'], $data['reason'], $data['recipient_id']]);
    } else {
        $stmt = $conn->prepare("UPDATE recipient_registration SET request_status = ?, updated_at = NOW() WHERE id = ?");
        $stmt->execute([$data['status'], $data['recipient_id']]);
    }

    // Send email notification
    $mailer = new Mailer();
    if ($data['status'] === 'rejected') {
        $mailer->sendRejectionNotification(
            $recipient['email'],
            $recipient['name'],
            $data['reason'] ?? 'No reason provided',
            'recipient'
        );
    }

    // Commit transaction
    $conn->commit();
    
    echo json_encode(['success' => true, 'message' => 'Status updated successfully']);

} catch (Exception $e) {
    // Rollback transaction on error
    if ($conn->inTransaction()) {
        $conn->rollBack();
    }
    
    error_log("Error updating recipient status: " . $e->getMessage());
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Failed to update status']);
}
?>
