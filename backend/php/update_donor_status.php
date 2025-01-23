<?php
session_start();
require_once 'connection.php';
require_once 'Mailer.php'; // Assuming Mailer class is in Mailer.php file
require_once 'notification.php'; // Assuming notification functions are in notification.php file

header('Content-Type: application/json');

// Check if admin is logged in
if (!isset($_SESSION['admin_id'])) {
    echo json_encode(['success' => false, 'message' => 'Unauthorized access']);
    exit();
}

// Get POST data
$data = json_decode(file_get_contents('php://input'), true);

// Validate required fields
if (!isset($data['donor_id']) || !isset($data['status'])) {
    echo json_encode(['success' => false, 'message' => 'Missing required fields']);
    exit();
}

// Validate status
$allowed_statuses = ['Pending', 'Approved', 'Rejected'];
if (!in_array($data['status'], $allowed_statuses)) {
    echo json_encode(['success' => false, 'message' => 'Invalid status']);
    exit();
}

function updateDonorStatus($conn, $donor_id, $status, $admin_id) {
    $stmt = $conn->prepare("UPDATE donor SET status = ?, updated_by = ? WHERE donor_id = ?");
    return $stmt->execute([$status, $admin_id, $donor_id]);
}

function createNotification($conn, $type, $status, $donor_id, $message) {
    // Assuming this function is defined in notification.php file
}

try {
    // Begin transaction
    $conn->beginTransaction();

    // Get donor details
    $stmt = $conn->prepare("SELECT name, email FROM donor WHERE donor_id = ?");
    $stmt->bind_param("i", $data['donor_id']);
    $stmt->execute();
    $result = $stmt->get_result();
    $donor = $result->fetch_assoc();

    if (!$donor) {
        throw new Exception("Donor not found");
    }

    // Update donor status
    if (updateDonorStatus($conn, $data['donor_id'], $data['status'], $_SESSION['admin_id'])) {
        // Send rejection email if status is rejected and reason is provided
        if ($data['status'] === 'Rejected' && isset($data['reason']) && !empty($data['reason'])) {
            $mailer = new Mailer();
            $mailer->sendRejectionNotification(
                $donor['email'],
                $donor['name'],
                'donor',
                $data['reason']
            );
        }

        // Create notification
        createNotification(
            $conn,
            'donor',
            $data['status'],
            $data['donor_id'],
            "Donor " . $donor['name'] . " has been " . $data['status'] . 
            ($data['status'] === 'Rejected' && !empty($data['reason']) ? "\nReason: " . $data['reason'] : "")
        );

        // Commit transaction
        $conn->commit();
        echo json_encode(['success' => true, 'message' => 'Donor status updated successfully']);
    } else {
        throw new Exception("Failed to update donor status");
    }
} catch (PDOException $e) {
    $conn->rollBack();
    error_log("Error in update_donor_status.php: " . $e->getMessage());
    echo json_encode(['success' => false, 'message' => 'Database error: ' . $e->getMessage()]);
} catch (Exception $e) {
    $conn->rollBack();
    error_log("Error in update_donor_status.php: " . $e->getMessage());
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
?>
