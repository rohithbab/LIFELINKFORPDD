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
if (!isset($data['donor_id']) || !isset($data['odml_id'])) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Missing required fields']);
    exit();
}

try {
    $donor_id = $data['donor_id'];
    $odml_id = $data['odml_id'];
    
    // Update donor ODML ID
    $stmt = $conn->prepare("UPDATE donor SET odml_id = ? WHERE donor_id = ?");
    $stmt->bind_param("si", $odml_id, $donor_id);
    
    if ($stmt->execute()) {
        // Get donor details
        $stmt = $conn->prepare("SELECT name, email FROM donor WHERE donor_id = ?");
        $stmt->bind_param("i", $donor_id);
        $stmt->execute();
        $result = $stmt->get_result();
        $donor = $result->fetch_assoc();
        
        // Send ODML ID assignment email
        $mailer = new Mailer();
        $mailer->sendODMLAssignment(
            $donor['email'],
            $donor['name'],
            $odml_id,
            'donor'
        );
        
        echo json_encode(['success' => true, 'message' => 'ODML ID updated successfully']);
    } else {
        throw new Exception("Error updating ODML ID");
    }
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
