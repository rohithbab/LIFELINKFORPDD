<?php
session_start();
require_once 'connection.php';
require_once 'queries.php';
require_once 'Mailer.php'; // Assuming Mailer class is in Mailer.php file

header('Content-Type: application/json');

// Check if admin is logged in
if (!isset($_SESSION['admin_id'])) {
    echo json_encode(['success' => false, 'message' => 'Unauthorized access']);
    exit();
}

// Get POST data
$data = json_decode(file_get_contents('php://input'), true);

// Validate required fields
if (!isset($data['recipient_id']) || !isset($data['status'])) {
    echo json_encode(['success' => false, 'message' => 'Missing required fields']);
    exit();
}

// Validate status
$allowed_statuses = ['Pending', 'Accepted', 'Rejected'];
if (!in_array($data['status'], $allowed_statuses)) {
    echo json_encode(['success' => false, 'message' => 'Invalid status']);
    exit();
}

try {
    // Begin transaction
    $conn->beginTransaction();

    // Get recipient details
    $stmt = $conn->prepare("SELECT name, email FROM recipient_registration WHERE id = ?");
    $stmt->execute([$data['recipient_id']]);
    $recipient = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$recipient) {
        throw new Exception("Recipient not found");
    }

    // Update recipient status
    $stmt = $conn->prepare("UPDATE recipient_registration SET request_status = ? WHERE id = ?");
    $result = $stmt->execute([$data['status'], $data['recipient_id']]);

    if ($result) {
        // Send rejection email if status is rejected and reason is provided
        if ($data['status'] === 'Rejected' && isset($data['reason']) && !empty($data['reason'])) {
            $mailer = new Mailer();
            $mailer->sendRejectionNotification(
                $recipient['email'],
                $recipient['name'],
                'recipient',
                $data['reason']
            );
        }

        // Create notification
        $action = strtolower($data['status']);
        $message = "Recipient " . $recipient['name'] . " has been " . $action . 
            ($data['status'] === 'Rejected' && !empty($data['reason']) ? "\nReason: " . $data['reason'] : "");
        
        createNotification(
            $conn,
            'recipient',
            $action,
            $data['recipient_id'],
            $message
        );

        // Commit transaction
        $conn->commit();
        
        // Log the update for debugging
        error_log("Successfully updated recipient status. Recipient ID: " . $data['recipient_id'] . ", New Status: " . $data['status']);
        
        echo json_encode(['success' => true, 'message' => 'Status updated successfully']);
    } else {
        $conn->rollback();
        error_log("Failed to update recipient status. Recipient ID: " . $data['recipient_id']);
        echo json_encode(['success' => false, 'message' => 'Failed to update status']);
    }
} catch (PDOException $e) {
    if ($conn->inTransaction()) {
        $conn->rollback();
    }
    error_log("Error in update_recipient_status.php: " . $e->getMessage());
    echo json_encode(['success' => false, 'message' => 'Database error: ' . $e->getMessage()]);
} catch (Exception $e) {
    if ($conn->inTransaction()) {
        $conn->rollback();
    }
    error_log("Error in update_recipient_status.php: " . $e->getMessage());
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
?>
