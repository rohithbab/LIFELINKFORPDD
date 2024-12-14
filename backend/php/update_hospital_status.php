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

if (!isset($data['hospital_id']) || !isset($data['status'])) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Missing required parameters']);
    exit();
}

$hospital_id = $data['hospital_id'];
$status = $data['status'];

// Validate status
if (!in_array($status, ['approved', 'rejected'])) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Invalid status']);
    exit();
}

try {
    // Update hospital status
    if (updateHospitalStatus($conn, $hospital_id, $status, $_SESSION['admin_id'])) {
        // Get hospital details
        $stmt = $conn->prepare("SELECT hospital_name, email FROM hospitals WHERE hospital_id = ?");
        $stmt->bind_param("i", $hospital_id);
        $stmt->execute();
        $result = $stmt->get_result();
        $hospital = $result->fetch_assoc();

        // Send email notification to hospital
        $subject = $status === 'approved' 
            ? "LifeLink: Your Hospital Registration has been Approved"
            : "LifeLink: Your Hospital Registration Status Update";
            
        $message = $status === 'approved'
            ? "Congratulations! Your hospital registration has been approved. You can now log in to your account."
            : "Your hospital registration has been reviewed. Unfortunately, we cannot approve your registration at this time.";

        // Send email (you'll need to implement your email sending function)
        // mail($hospital['email'], $subject, $message);

        echo json_encode([
            'success' => true,
            'message' => "Hospital has been " . $status . " successfully"
        ]);
    } else {
        throw new Exception("Failed to update hospital status");
    }
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'An error occurred while updating hospital status: ' . $e->getMessage()
    ]);
}
