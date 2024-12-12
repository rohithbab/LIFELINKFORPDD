<?php
session_start();
require_once 'connection.php';
require_once 'queries.php';

// Check if admin is logged in
if (!isset($_SESSION['admin_id'])) {
    http_response_code(401);
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit();
}

// Get POST data
$data = json_decode(file_get_contents('php://input'), true);

if (!isset($data['hospital_id']) || !isset($data['action'])) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Missing required parameters']);
    exit();
}

$hospital_id = $data['hospital_id'];
$action = $data['action'];

try {
    // Begin transaction
    $conn->beginTransaction();

    // Update hospital status
    $status = ($action === 'approve') ? 'approved' : 'rejected';
    $stmt = $conn->prepare("UPDATE hospitals SET status = :status WHERE id = :id");
    $stmt->execute([':status' => $status, ':id' => $hospital_id]);

    // Get hospital details for email notification
    $stmt = $conn->prepare("SELECT name, email FROM hospitals WHERE id = :id");
    $stmt->execute([':id' => $hospital_id]);
    $hospital = $stmt->fetch(PDO::FETCH_ASSOC);

    // Add notification
    $message = ($action === 'approve') 
        ? "Hospital {$hospital['name']} has been approved"
        : "Hospital {$hospital['name']} has been rejected";
    
    addNotification($conn, 'hospital_' . $action, $message);

    // Send email notification
    $emailTemplate = ($action === 'approve') ? 'approval-email.php' : 'rejection-email.php';
    require_once "../emails/{$emailTemplate}";
    
    // Commit transaction
    $conn->commit();

    echo json_encode([
        'success' => true,
        'message' => "Hospital has been successfully " . ($action === 'approve' ? 'approved' : 'rejected')
    ]);

} catch (Exception $e) {
    // Rollback transaction on error
    $conn->rollBack();
    
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'An error occurred while processing your request'
    ]);
}
?>
