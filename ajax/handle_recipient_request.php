<?php
session_start();
require_once '../config/db_connect.php';

// Check if hospital is logged in
if (!isset($_SESSION['hospital_logged_in']) || !$_SESSION['hospital_logged_in']) {
    http_response_code(401);
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit();
}

$hospital_id = $_SESSION['hospital_id'];

// Get JSON data
$data = json_decode(file_get_contents('php://input'), true);

if (!isset($data['approval_id']) || !isset($data['status'])) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Missing required parameters']);
    exit();
}

$approval_id = $data['approval_id'];
$status = $data['status'];

try {
    // Start transaction
    $conn->beginTransaction();

    // First, verify the approval exists and belongs to this hospital
    $stmt = $conn->prepare("
        SELECT hra.*, r.full_name 
        FROM hospital_recipient_approvals hra
        JOIN recipient_registration r ON r.id = hra.recipient_id
        WHERE hra.approval_id = ? AND hra.hospital_id = ? AND hra.status = 'Pending'
    ");
    $stmt->execute([$approval_id, $hospital_id]);
    $approval = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$approval) {
        throw new Exception('Approval not found or you do not have permission to modify it');
    }

    // Update approval status
    $stmt = $conn->prepare("
        UPDATE hospital_recipient_approvals 
        SET status = ?,
            response_date = NOW()
        WHERE approval_id = ? 
        AND hospital_id = ?
    ");
    $stmt->execute([$status, $approval_id, $hospital_id]);

    // If approved, we might want to notify the recipient or update other related records
    if ($status === 'Approved') {
        // Add any additional logic for approved recipients here
        error_log("Recipient {$approval['full_name']} approved by hospital {$hospital_id}");
    }

    // Commit transaction
    $conn->commit();

    echo json_encode([
        'success' => true,
        'message' => "Recipient successfully " . strtolower($status)
    ]);

} catch (Exception $e) {
    // Rollback transaction on error
    if ($conn->inTransaction()) {
        $conn->rollBack();
    }
    
    error_log("Error handling recipient request: " . $e->getMessage());
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}
?>
