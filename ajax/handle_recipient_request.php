<?php
session_start();
require_once '../config/db_connect.php';

// Enable error reporting for debugging
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Set headers
header('Content-Type: application/json');

// Check if hospital is logged in
if (!isset($_SESSION['hospital_logged_in']) || !$_SESSION['hospital_logged_in']) {
    http_response_code(401);
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit();
}

$hospital_id = $_SESSION['hospital_id'];

try {
    // Check if we have the required data
    $data = json_decode(file_get_contents('php://input'), true);
    if (!isset($data['approval_id']) || !isset($data['status'])) {
        throw new Exception('Missing required parameters');
    }

    $approval_id = $data['approval_id'];
    $status = $data['status'];
    $rejection_reason = isset($data['rejection_reason']) ? $data['rejection_reason'] : null;
    $current_date = date('Y-m-d H:i:s');

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
            approval_date = ?,
            rejection_reason = ?
        WHERE approval_id = ? 
        AND hospital_id = ?
    ");
    $stmt->execute([
        $status,
        $current_date,
        $status === 'Rejected' ? $rejection_reason : null,
        $approval_id,
        $hospital_id
    ]);

    // If approved, we might want to notify the recipient or update other related records
    if ($status === 'Approved') {
        // Add any additional logic for approved recipients here
        error_log("Recipient {$approval['full_name']} approved by hospital {$hospital_id}");
    }

    // Commit transaction
    $conn->commit();

    // Return success response
    echo json_encode([
        'success' => true,
        'message' => "Recipient successfully " . strtolower($status)
    ]);

} catch (Exception $e) {
    // Rollback transaction if active
    if ($conn->inTransaction()) {
        $conn->rollBack();
    }

    // Log the error
    error_log("Error in handle_recipient_request.php: " . $e->getMessage());
    
    // Return error response
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Error: ' . $e->getMessage()
    ]);
}
?>
