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

if (!isset($data['request_id']) || !isset($data['action'])) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Missing required parameters']);
    exit();
}

$request_id = $data['request_id'];
$action = $data['action'];
$message = isset($data['message']) ? $data['message'] : '';

try {
    // Start transaction
    $conn->beginTransaction();

    // First, verify the request exists and check permissions
    $stmt = $conn->prepare("
        SELECT * FROM donor_requests 
        WHERE request_id = ? AND (donor_hospital_id = ? OR requesting_hospital_id = ?)
    ");
    $stmt->execute([$request_id, $hospital_id, $hospital_id]);
    $request = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$request) {
        throw new Exception('Request not found or you do not have permission to modify it');
    }

    // Check if the action is valid based on the current status and user role
    $isOwnerHospital = $request['donor_hospital_id'] == $hospital_id;
    $isRequesterHospital = $request['requesting_hospital_id'] == $hospital_id;

    if ($action === 'approve' && (!$isOwnerHospital || $request['status'] !== 'Pending')) {
        throw new Exception('Invalid action: Only the donor hospital can approve pending requests');
    }

    if ($action === 'reject' && (!$isOwnerHospital || $request['status'] !== 'Pending')) {
        throw new Exception('Invalid action: Only the donor hospital can reject pending requests');
    }

    if ($action === 'cancel' && (!$isRequesterHospital || $request['status'] !== 'Approved')) {
        throw new Exception('Invalid action: Only the requesting hospital can cancel approved requests');
    }

    // Update request status
    $newStatus = $action === 'approve' ? 'Approved' : 'Rejected';
    
    $stmt = $conn->prepare("
        UPDATE donor_requests 
        SET status = ?, response_date = NOW(), response_message = ? 
        WHERE request_id = ?
    ");
    $stmt->execute([$newStatus, $message, $request_id]);

    // If cancelling an approved request, also update any other pending requests
    if ($action === 'cancel') {
        $stmt = $conn->prepare("
            UPDATE donor_requests 
            SET status = 'Rejected', 
                response_date = NOW(), 
                response_message = 'Request cancelled by requesting hospital'
            WHERE donor_id = ? AND status = 'Pending'
        ");
        $stmt->execute([$request['donor_id']]);
    }

    // Commit transaction
    $conn->commit();

    echo json_encode(['success' => true]);

} catch (Exception $e) {
    // Rollback transaction on error
    $conn->rollBack();
    
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
?>
