<?php
session_start();
require_once 'connection.php';
require_once 'helpers/mailer.php';

header('Content-Type: application/json');

// Check if admin is logged in
if (!isset($_SESSION['admin_id'])) {
    echo json_encode(['success' => false, 'message' => 'Unauthorized access']);
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
if (!isset($data['donor_id']) || !isset($data['status'])) {
    echo json_encode(['success' => false, 'message' => 'Missing required fields']);
    exit();
}

// Validate status
$allowed_statuses = ['pending', 'approved', 'rejected'];
if (!in_array(strtolower($data['status']), $allowed_statuses)) {
    echo json_encode(['success' => false, 'message' => 'Invalid status']);
    exit();
}

try {
    // Begin transaction
    $conn->beginTransaction();

    // Get donor details
    $stmt = $conn->prepare("SELECT name, email FROM donor WHERE donor_id = ?");
    $stmt->execute([$data['donor_id']]);
    $donor = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$donor) {
        throw new Exception('Donor not found');
    }

    // Update donor status
    $stmt = $conn->prepare("UPDATE donor SET status = ?, updated_at = NOW() WHERE donor_id = ?");
    $stmt->execute([strtolower($data['status']), $data['donor_id']]);

    // Send email notification
    $mailer = new Mailer();
    if ($data['status'] === 'rejected') {
        if (!isset($data['reason'])) {
            throw new Exception('Rejection reason is required');
        }
        $mailer->sendRejectionNotification(
            $donor['email'],
            $donor['name'],
            $data['reason'],
            'donor'
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

    error_log("Error updating donor status: " . $e->getMessage());
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
?>
