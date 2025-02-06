<?php
session_start();
require_once 'connection.php';
require_once 'helpers/mailer.php';

// Show all errors for debugging
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Check if admin is logged in
if (!isset($_SESSION['admin_id'])) {
    http_response_code(401);
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit();
}

// Get POST data
$contentType = isset($_SERVER["CONTENT_TYPE"]) ? trim($_SERVER["CONTENT_TYPE"]) : '';

if ($contentType === 'application/json') {
    $data = json_decode(file_get_contents('php://input'), true);
} else {
    $data = $_POST;
}

if (!isset($data['hospital_id'])) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Missing hospital_id']);
    exit();
}

$hospital_id = $data['hospital_id'];
$action = $data['action'] ?? '';

try {
    // Get hospital details first
    $stmt = $conn->prepare("SELECT name, email FROM hospitals WHERE hospital_id = ?");
    $stmt->execute([$hospital_id]);
    $hospital = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$hospital) {
        throw new Exception("Hospital not found");
    }

    $mailer = new Mailer();
    
    if ($action === 'approve') {
        if (!isset($data['odml_id']) || empty($data['odml_id'])) {
            throw new Exception("ODML ID is required for approval");
        }

        error_log("Starting hospital approval process for ID: $hospital_id");
        
        try {
            // First try to send the email
            error_log("Attempting to send approval email to: " . $hospital['email']);
            $emailSent = $mailer->sendHospitalApprovalEmail(
                $hospital['email'],
                $hospital['name'],
                $data['odml_id']
            );
            
            error_log("Email sent successfully, proceeding with database update");
            
            // Only if email succeeds, update database
            $conn->beginTransaction();
            
            $stmt = $conn->prepare("UPDATE hospitals SET status = 'Approved', odml_id = ?, approved_by = ?, approved_at = NOW() WHERE hospital_id = ?");
            if (!$stmt->execute([$data['odml_id'], $_SESSION['admin_id'], $hospital_id])) {
                throw new Exception("Failed to update hospital status");
            }

            // Create notification
            $notification_message = "Hospital {$hospital['name']} has been approved with ODML ID: {$data['odml_id']}";
            $stmt = $conn->prepare("INSERT INTO notifications (type, action, reference_id, message, created_at) VALUES (?, ?, ?, ?, NOW())");
            $stmt->execute(['hospital', 'approve', $hospital_id, $notification_message]);

            $conn->commit();
            error_log("Database updated successfully");
            
            echo json_encode([
                'success' => true,
                'message' => "Hospital approved successfully and email sent with ODML ID."
            ]);
            
        } catch (Exception $e) {
            error_log("Error in approval process: " . $e->getMessage());
            if ($conn && $conn->inTransaction()) {
                $conn->rollBack();
                error_log("Transaction rolled back");
            }
            throw new Exception("Failed to process approval: " . $e->getMessage());
        }
    } 
    else if ($action === 'reject') {
        if (!isset($data['reason']) || empty($data['reason'])) {
            throw new Exception("Reason is required for rejection");
        }

        error_log("Starting hospital rejection process for ID: $hospital_id");
        
        try {
            // First try to send the rejection email
            error_log("Attempting to send rejection email to: " . $hospital['email']);
            $emailSent = $mailer->sendHospitalRejectionEmail(
                $hospital['email'],
                $hospital['name'],
                $data['reason']
            );
            
            error_log("Email sent successfully, proceeding with database update");
            
            // Only if email succeeds, update database
            $conn->beginTransaction();
            
            $stmt = $conn->prepare("UPDATE hospitals SET status = 'Rejected', rejected_by = ?, rejected_at = NOW(), rejection_reason = ? WHERE hospital_id = ?");
            if (!$stmt->execute([$_SESSION['admin_id'], $data['reason'], $hospital_id])) {
                throw new Exception("Failed to update hospital status");
            }

            // Create notification
            $notification_message = "Hospital {$hospital['name']} has been rejected. Reason: {$data['reason']}";
            $stmt = $conn->prepare("INSERT INTO notifications (type, action, reference_id, message, created_at) VALUES (?, ?, ?, ?, NOW())");
            $stmt->execute(['hospital', 'reject', $hospital_id, $notification_message]);

            $conn->commit();
            error_log("Database updated successfully");
            
            echo json_encode([
                'success' => true,
                'message' => "Hospital rejected successfully and notification email sent."
            ]);
            
        } catch (Exception $e) {
            error_log("Error in rejection process: " . $e->getMessage());
            if ($conn && $conn->inTransaction()) {
                $conn->rollBack();
                error_log("Transaction rolled back");
            }
            throw new Exception("Failed to process rejection: " . $e->getMessage());
        }
    } 
    else {
        throw new Exception("Invalid action specified");
    }

} catch (Exception $e) {
    error_log("Error in update_hospital_status.php: " . $e->getMessage());
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}
?>
