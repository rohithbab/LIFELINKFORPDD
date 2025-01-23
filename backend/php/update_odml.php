<?php
header('Content-Type: application/json');
require_once 'connection.php';
require_once 'helpers/email_sender.php';

try {
    // Get JSON input
    $input = json_decode(file_get_contents('php://input'), true);
    
    if (!isset($input['type']) || !isset($input['id']) || !isset($input['odmlId'])) {
        throw new Exception('Missing required parameters');
    }

    $type = $input['type'];
    $id = $input['id'];
    $odmlId = trim($input['odmlId']);

    // Validate ODML ID format (you can adjust this based on your requirements)
    if (!preg_match('/^[A-Za-z0-9-]+$/', $odmlId)) {
        throw new Exception('Invalid ODML ID format');
    }

    // Start transaction
    $conn->beginTransaction();

    // Update ODML ID and status based on type
    switch ($type) {
        case 'hospital':
            $sql = "UPDATE hospitals SET odml_id = ?, status = 'approved', approved_at = NOW() WHERE id = ?";
            $table = "hospitals";
            $email_template = "hospital_approval.php";
            break;
            
        case 'donor':
            $sql = "UPDATE donors SET odml_id = ?, status = 'approved', approved_at = NOW() WHERE id = ?";
            $table = "donors";
            $email_template = "donor_approval.php";
            break;
            
        case 'recipient':
            $sql = "UPDATE recipients SET odml_id = ?, status = 'approved', approved_at = NOW() WHERE id = ?";
            $table = "recipients";
            $email_template = "recipient_approval.php";
            break;
            
        default:
            throw new Exception('Invalid type');
    }

    // Execute update
    $stmt = $conn->prepare($sql);
    $stmt->execute([$odmlId, $id]);

    // Get email and name for notification
    $select_sql = "SELECT name, email FROM $table WHERE id = ?";
    $stmt = $conn->prepare($select_sql);
    $stmt->execute([$id]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$user) {
        throw new Exception('User not found');
    }

    // Send email notification
    $emailData = [
        'name' => $user['name'],
        'odml_id' => $odmlId,
        'type' => $type
    ];
    
    sendEmail(
        $user['email'],
        "Your $type registration has been approved",
        $email_template,
        $emailData
    );

    // Create notification
    $notification_sql = "INSERT INTO notifications (type, user_type, user_id, message, status) 
                        VALUES (?, ?, ?, ?, 'unread')";
    $stmt = $conn->prepare($notification_sql);
    $stmt->execute([
        'registration_approved',
        $type,
        $id,
        "Your registration has been approved. Your ODML ID is: $odmlId"
    ]);

    // Commit transaction
    $conn->commit();

    echo json_encode([
        'success' => true,
        'message' => "ODML ID updated and notification sent successfully"
    ]);

} catch (Exception $e) {
    // Rollback transaction if active
    if (isset($conn) && $conn->inTransaction()) {
        $conn->rollback();
    }

    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}
