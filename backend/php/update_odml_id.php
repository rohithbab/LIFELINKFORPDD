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
$type = $_POST['type'] ?? '';
$id = $_POST['id'] ?? '';
$odml_id = $_POST['odml_id'] ?? '';

if (empty($type) || empty($id) || empty($odml_id)) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Missing required parameters']);
    exit();
}

$success = false;
$message = '';

switch ($type) {
    case 'hospital':
        $success = updateHospitalODMLID($conn, $id, $odml_id);
        $message = $success ? 'Hospital ODML ID updated successfully' : 'Failed to update hospital ODML ID';
        break;
    case 'donor':
        $success = updateDonorODMLID($conn, $id, $odml_id);
        $message = $success ? 'Donor ODML ID updated successfully' : 'Failed to update donor ODML ID';
        break;
    case 'recipient':
        $success = updateRecipientODMLID($conn, $id, $odml_id);
        $message = $success ? 'Recipient ODML ID updated successfully' : 'Failed to update recipient ODML ID';
        break;
    default:
        $message = 'Invalid type specified';
        break;
}

echo json_encode(['success' => $success, 'message' => $message]);
?>
