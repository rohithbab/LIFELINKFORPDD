<?php
session_start();
require_once 'connection.php';

header('Content-Type: application/json');

// Check if admin is logged in
if (!isset($_SESSION['admin_id'])) {
    echo json_encode(['success' => false, 'message' => 'Unauthorized access']);
    exit();
}

// Get POST data
$data = json_decode(file_get_contents('php://input'), true);

if (!isset($data['recipient_id']) || !isset($data['reason'])) {
    echo json_encode(['success' => false, 'message' => 'Missing required fields']);
    exit();
}

try {
    // Update recipient status to rejected
    $stmt = $conn->prepare("UPDATE recipient_registration SET request_status = 'rejected', rejection_reason = ? WHERE id = ?");
    $result = $stmt->execute([$data['reason'], $data['recipient_id']]);

    if ($result) {
        // Log the rejection
        error_log("Recipient rejected. ID: " . $data['recipient_id'] . ", Reason: " . $data['reason']);
        
        // TODO: Send email notification to recipient (future implementation)
        
        echo json_encode(['success' => true, 'message' => 'Recipient rejected successfully']);
    } else {
        echo json_encode(['success' => false, 'message' => 'Failed to reject recipient']);
    }
} catch (PDOException $e) {
    error_log("Error in reject_recipient.php: " . $e->getMessage());
    echo json_encode(['success' => false, 'message' => 'Database error: ' . $e->getMessage()]);
}
?>
