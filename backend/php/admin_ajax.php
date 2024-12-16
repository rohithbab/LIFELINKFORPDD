<?php
session_start();
require_once 'connection.php';
require_once 'queries.php';
require_once 'organ_matches.php';

// Check if admin is logged in
if (!isset($_SESSION['admin_id'])) {
    http_response_code(403);
    echo json_encode(['error' => 'Unauthorized']);
    exit();
}

header('Content-Type: application/json');

$action = $_GET['action'] ?? '';

switch ($action) {
    case 'get_stats':
        $stats = getDashboardStats($conn);
        echo json_encode($stats);
        break;

    case 'get_pending_hospitals':
        $pendingHospitals = getPendingHospitals($conn);
        echo json_encode($pendingHospitals);
        break;

    case 'get_urgent_recipients':
        $urgentRecipients = getUrgentRecipients($conn);
        echo json_encode($urgentRecipients);
        break;

    case 'get_notifications':
        $notifications = getAdminNotifications($conn, 5);
        echo json_encode($notifications);
        break;

    case 'get_pending_donors':
        $pendingDonors = getPendingDonors($conn);
        echo json_encode($pendingDonors);
        break;

    case 'get_pending_recipients':
        $pendingRecipients = getPendingRecipients($conn);
        echo json_encode($pendingRecipients);
        break;

    case 'update_hospital_status':
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            http_response_code(405);
            echo json_encode(['error' => 'Method not allowed']);
            break;
        }

        $hospital_id = $_POST['hospital_id'] ?? null;
        $status = $_POST['status'] ?? null;

        if (!$hospital_id || !$status) {
            http_response_code(400);
            echo json_encode(['error' => 'Missing parameters']);
            break;
        }

        $result = updateHospitalStatus($conn, $hospital_id, $status);
        echo json_encode(['success' => $result]);
        break;

    case 'update_donor_status':
        if (isset($_POST['donor_id']) && isset($_POST['status'])) {
            $success = updateDonorStatus($conn, $_POST['donor_id'], $_POST['status']);
            if ($success) {
                // Add notification
                $status = $_POST['status'] === 'approved' ? 'approved' : 'rejected';
                $message = "Donor ID " . $_POST['donor_id'] . " has been " . $status;
                addAdminNotification($conn, $message);
            }
            echo json_encode(['success' => $success]);
        } else {
            echo json_encode(['success' => false, 'error' => 'Missing parameters']);
        }
        break;

    case 'update_recipient_status':
        if (isset($_POST['recipient_id']) && isset($_POST['status'])) {
            $success = updateRecipientStatus($conn, $_POST['recipient_id'], $_POST['status']);
            if ($success) {
                // Add notification
                $status = $_POST['status'] === 'approved' ? 'approved' : 'rejected';
                $message = "Recipient ID " . $_POST['recipient_id'] . " has been " . $status;
                addAdminNotification($conn, $message);
            }
            echo json_encode(['success' => $success]);
        } else {
            echo json_encode(['success' => false, 'error' => 'Missing parameters']);
        }
        break;

    default:
        http_response_code(400);
        echo json_encode(['error' => 'Invalid action']);
}
?>
