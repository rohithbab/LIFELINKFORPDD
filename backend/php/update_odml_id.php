<?php
session_start();
require_once 'connection.php';
require_once 'queries.php';

// Check if admin is logged in
if (!isset($_SESSION['admin_id'])) {
    echo json_encode(['success' => false, 'message' => 'Unauthorized access']);
    exit();
}

// Check if required parameters are set
if (!isset($_POST['type']) || !isset($_POST['id']) || !isset($_POST['odml_id'])) {
    echo json_encode(['success' => false, 'message' => 'Missing required parameters']);
    exit();
}

$type = $_POST['type'];
$id = $_POST['id'];
$odml_id = $_POST['odml_id'];

// Update ODML ID based on type
$success = false;
$message = '';

try {
    switch ($type) {
        case 'hospital':
            $success = updateHospitalODMLID($conn, $id, $odml_id);
            break;
        default:
            $message = 'Invalid type specified';
    }

    if ($success) {
        echo json_encode(['success' => true]);
    } else {
        echo json_encode(['success' => false, 'message' => $message ?: 'Failed to update ODML ID']);
    }
} catch (Exception $e) {
    error_log("Error in update_odml_id.php: " . $e->getMessage());
    echo json_encode(['success' => false, 'message' => 'An error occurred']);
}
?>
