<?php
session_start();
require_once 'connection.php';
require_once 'helpers/mailer.php';

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

// Validate required fields
if (!isset($data['donor_id']) || !isset($data['odml_id']) || !isset($data['action'])) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Missing required fields']);
    exit();
}

try {
    $donor_id = $data['donor_id'];
    $odml_id = $data['odml_id'];
    $action = $data['action'];
    
    // Start transaction
    $conn->beginTransaction();
    
    // Update donor ODML ID and status if approving
    if ($action === 'approve') {
        $stmt = $conn->prepare("UPDATE donor SET odml_id = ?, status = 'approved', updated_at = NOW() WHERE donor_id = ?");
        $stmt->execute([$odml_id, $donor_id]);
    } else {
        $stmt = $conn->prepare("UPDATE donor SET odml_id = ?, updated_at = NOW() WHERE donor_id = ?");
        $stmt->execute([$odml_id, $donor_id]);
    }
    
    // Get donor details
    $stmt = $conn->prepare("SELECT name, email FROM donor WHERE donor_id = ?");
    $stmt->execute([$donor_id]);
    $donor = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$donor) {
        throw new Exception('Donor not found');
    }
    
    // Send ODML ID assignment email
    $mailer = new Mailer();
    $mailer->sendODMLAssignment(
        $donor['email'],
        $donor['name'],
        $odml_id,
        'donor'
    );
    
    // Commit transaction
    $conn->commit();
    
    echo json_encode(['success' => true, 'message' => 'ODML ID updated successfully']);
    
} catch (Exception $e) {
    // Rollback transaction on error
    if ($conn->inTransaction()) {
        $conn->rollBack();
    }
    
    error_log("Error updating donor ODML ID: " . $e->getMessage());
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Failed to update ODML ID']);
}
?>
