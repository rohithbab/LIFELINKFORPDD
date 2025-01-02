<?php
session_start();
require_once '../config/db_connect.php';

// Check if hospital is logged in
if (!isset($_SESSION['hospital_logged_in']) || !$_SESSION['hospital_logged_in']) {
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit();
}

// Get JSON input
$data = json_decode(file_get_contents('php://input'), true);
$donor_id = $data['donor_id'] ?? 0;
$requesting_hospital_id = $_SESSION['hospital_id'];

if (!$donor_id) {
    echo json_encode(['success' => false, 'message' => 'Invalid donor ID']);
    exit();
}

try {
    // Get donor and hospital information
    $stmt = $conn->prepare("
        SELECT d.blood_group, ha.organ_type, ha.hospital_id as requested_hospital_id
        FROM donor d
        JOIN hospital_donor_approvals ha ON d.donor_id = ha.donor_id
        WHERE d.donor_id = ? AND ha.status = 'Approved'
    ");
    $stmt->execute([$donor_id]);
    $donorInfo = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$donorInfo) {
        echo json_encode(['success' => false, 'message' => 'Donor not found or not approved']);
        exit();
    }

    // Check if request already exists
    $stmt = $conn->prepare("
        SELECT status 
        FROM donor_and_recipient_requests 
        WHERE donor_id = ? AND requesting_hospital_id = ? 
        AND status IN ('pending', 'approved')
    ");
    $stmt->execute([$donor_id, $requesting_hospital_id]);
    if ($stmt->fetch()) {
        echo json_encode(['success' => false, 'message' => 'A request already exists for this donor']);
        exit();
    }

    // Insert new request
    $stmt = $conn->prepare("
        INSERT INTO donor_and_recipient_requests 
        (requesting_hospital_id, requested_hospital_id, donor_id, blood_type, organ_type, status)
        VALUES (?, ?, ?, ?, ?, 'pending')
    ");
    $stmt->execute([
        $requesting_hospital_id,
        $donorInfo['requested_hospital_id'],
        $donor_id,
        $donorInfo['blood_group'],
        $donorInfo['organ_type']
    ]);

    // Create notification for the requested hospital
    $stmt = $conn->prepare("
        INSERT INTO match_notifications 
        (hospital_id, message, notification_type, match_id)
        VALUES (?, ?, 'match_request', ?)
    ");
    $stmt->execute([
        $donorInfo['requested_hospital_id'],
        "New donor match request received",
        $conn->lastInsertId()
    ]);

    echo json_encode(['success' => true, 'message' => 'Request sent successfully']);

} catch(PDOException $e) {
    error_log("Error sending donor request: " . $e->getMessage());
    echo json_encode(['success' => false, 'message' => 'An error occurred while sending the request']);
}
?>
