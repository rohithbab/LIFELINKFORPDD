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

if (!isset($data['recipient_id']) || !isset($data['recipient_hospital_id'])) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Missing required parameters']);
    exit();
}

$recipient_id = $data['recipient_id'];
$recipient_hospital_id = $data['recipient_hospital_id'];

try {
    // Start transaction
    $conn->beginTransaction();

    // Check if recipient exists and is approved
    $stmt = $conn->prepare("
        SELECT r.*, ha.status as approval_status, ha.required_organ, ha.blood_group
        FROM recipient r
        JOIN hospital_recipient_approvals ha ON r.recipient_id = ha.recipient_id
        WHERE r.recipient_id = ? AND ha.hospital_id = ? AND ha.status = 'Approved'
    ");
    $stmt->execute([$recipient_id, $recipient_hospital_id]);
    $recipient = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$recipient) {
        throw new Exception('Recipient not found or not approved');
    }

    // Check if recipient is already matched
    $stmt = $conn->prepare("
        SELECT is_matched 
        FROM hospital_recipient_approvals 
        WHERE recipient_id = ? AND status = 'Approved' AND is_matched = TRUE
    ");
    $stmt->execute([$recipient_id]);
    if ($stmt->fetch()) {
        throw new Exception('This recipient has already been matched');
    }

    // Check if a request already exists
    $stmt = $conn->prepare("
        SELECT request_id, status 
        FROM recipient_requests 
        WHERE recipient_id = ? 
        AND requesting_hospital_id = ? 
        AND recipient_hospital_id = ?
        AND status = 'Pending'
    ");
    $stmt->execute([$recipient_id, $hospital_id, $recipient_hospital_id]);
    if ($stmt->fetch()) {
        throw new Exception('A pending request already exists for this recipient');
    }

    // Create the request
    $stmt = $conn->prepare("
        INSERT INTO recipient_requests (
            recipient_id,
            requesting_hospital_id,
            recipient_hospital_id,
            request_date,
            status
        ) VALUES (?, ?, ?, NOW(), 'Pending')
    ");
    $stmt->execute([
        $recipient_id,
        $hospital_id,
        $recipient_hospital_id
    ]);

    // Commit transaction
    $conn->commit();

    echo json_encode([
        'success' => true, 
        'message' => 'Request sent successfully'
    ]);

} catch (Exception $e) {
    // Rollback transaction on error
    if ($conn->inTransaction()) {
        $conn->rollBack();
    }
    
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
