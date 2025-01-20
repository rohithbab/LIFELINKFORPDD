<?php
session_start();
require_once 'connection.php';
require_once 'helpers/mailer.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
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
    if (!isset($data['hospital_id']) || !isset($data['odml_id'])) {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'Missing required fields']);
        exit();
    }

    try {
        $hospital_id = $data['hospital_id'];
        $odml_id = $data['odml_id'];
        
        // Get hospital details
        $stmt = $conn->prepare("SELECT name, email FROM hospitals WHERE id = ?");
        $stmt->bind_param("i", $hospital_id);
        $stmt->execute();
        $result = $stmt->get_result();
        $hospital = $result->fetch_assoc();
        
        // Update ODML ID
        $stmt = $conn->prepare("UPDATE hospitals SET odml_id = ? WHERE id = ?");
        $stmt->bind_param("si", $odml_id, $hospital_id);
        
        if ($stmt->execute()) {
            // Send ODML ID assignment email
            $mailer = new Mailer();
            $mailer->sendODMLAssignment(
                $hospital['email'],
                $hospital['name'],
                $odml_id,
                'hospital'
            );
            
            echo json_encode(['success' => true, 'message' => 'ODML ID updated successfully']);
        } else {
            throw new Exception("Error updating ODML ID");
        }
    } catch (Exception $e) {
        http_response_code(500);
        echo json_encode(['success' => false, 'message' => $e->getMessage()]);
    }
} else {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Method not allowed']);
}
