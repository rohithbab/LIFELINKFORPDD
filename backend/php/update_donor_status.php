<?php
session_start();
require_once '../../config/connection.php';

// Check if hospital is logged in
if (!isset($_SESSION['hospital_id'])) {
    http_response_code(401);
    echo json_encode(['error' => 'Unauthorized']);
    exit();
}

// Get POST data
$data = json_decode(file_get_contents('php://input'), true);
$hospital_id = $_SESSION['hospital_id'];

// Validate required fields
if (!isset($data['donorId']) || !isset($data['status'])) {
    http_response_code(400);
    echo json_encode(['error' => 'Missing required fields']);
    exit();
}

// Validate status
$allowed_statuses = ['pending', 'approved', 'rejected'];
if (!in_array($data['status'], $allowed_statuses)) {
    http_response_code(400);
    echo json_encode(['error' => 'Invalid status']);
    exit();
}

try {
    // Start transaction
    $conn->begin_transaction();

    // Update donor status
    $stmt = $conn->prepare("
        UPDATE donors 
        SET status = ?, updated_at = NOW() 
        WHERE id = ? AND hospital_id = ?
    ");

    $stmt->bind_param(
        "sii",
        $data['status'],
        $data['donorId'],
        $hospital_id
    );

    if (!$stmt->execute()) {
        throw new Exception("Error updating donor status");
    }

    // Get donor details for notification
    $donor_stmt = $conn->prepare("
        SELECT name, organ FROM donors 
        WHERE id = ? AND hospital_id = ?
    ");
    
    $donor_stmt->bind_param("ii", $data['donorId'], $hospital_id);
    $donor_stmt->execute();
    $donor_result = $donor_stmt->get_result();
    $donor = $donor_result->fetch_assoc();

    // Create notification
    $notification_stmt = $conn->prepare("
        INSERT INTO notifications (
            type, recipient_type, recipient_id,
            title, message, created_at
        ) VALUES (
            'donor_status_update', 'admin', 1,
            ?, ?, NOW()
        )
    ");

    $title = "Donor Status Update";
    $message = "Donor " . $donor['name'] . " (" . $donor['organ'] . ") has been " . 
               $data['status'] . " by " . $_SESSION['hospital_name'];

    $notification_stmt->bind_param("ss", $title, $message);
    $notification_stmt->execute();

    // Commit transaction
    $conn->commit();

    // Send response
    echo json_encode([
        'success' => true,
        'message' => 'Donor status updated successfully'
    ]);

} catch (Exception $e) {
    // Rollback transaction on error
    $conn->rollback();
    
    http_response_code(500);
    echo json_encode([
        'error' => 'Internal server error',
        'message' => $e->getMessage()
    ]);
}

$conn->close();
