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
$data = json_decode(file_get_contents('php://input'), true);

// Validate required fields
if (!isset($data['recipient_id']) || !isset($data['odml_id'])) {
    echo json_encode(['success' => false, 'message' => 'Missing required fields']);
    exit();
}

try {
    // Update recipient ODML ID
    $stmt = $conn->prepare("UPDATE recipient_registration SET odml_id = ? WHERE id = ?");
    $result = $stmt->execute([$data['odml_id'], $data['recipient_id']]);

    if ($result) {
        // Get recipient details
        $stmt = $conn->prepare("SELECT name, email FROM recipient_registration WHERE id = ?");
        $stmt->execute([$data['recipient_id']]);
        $result = $stmt->get_result();
        $recipient = $result->fetch_assoc();

        // Send ODML ID assignment email
        $mailer = new Mailer();
        $mailer->sendODMLAssignment(
            $recipient['email'],
            $recipient['name'],
            $data['odml_id'],
            'recipient'
        );

        // Log the update for debugging
        error_log("Successfully updated recipient ODML ID. Recipient ID: " . $data['recipient_id'] . ", New ODML ID: " . $data['odml_id']);
        
        echo json_encode(['success' => true, 'message' => 'ODML ID updated successfully']);
    } else {
        error_log("Failed to update recipient ODML ID. Recipient ID: " . $data['recipient_id']);
        echo json_encode(['success' => false, 'message' => 'Failed to update ODML ID']);
    }
} catch (PDOException $e) {
    error_log("Error in update_recipient_odml.php: " . $e->getMessage());
    echo json_encode(['success' => false, 'message' => 'Database error: ' . $e->getMessage()]);
}
?>
