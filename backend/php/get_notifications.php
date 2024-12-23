<?php
session_start();
require_once 'connection.php';
require_once 'queries.php';

if (!isset($_SESSION['admin_id'])) {
    http_response_code(403);
    exit('Unauthorized');
}

header('Content-Type: application/json');

$action = $_GET['action'] ?? '';

switch ($action) {
    case 'get_unread':
        $notifications = getAdminNotifications($conn, 5);
        $unreadCount = 0;
        foreach ($notifications as &$notification) {
            if (!$notification['is_read']) {
                $unreadCount++;
            }
            // Format message based on type and action
            $notification['formatted_message'] = formatNotificationMessage($notification);
        }
        echo json_encode([
            'notifications' => $notifications,
            'unread_count' => $unreadCount
        ]);
        break;

    case 'mark_read':
        $notificationId = $_POST['notification_id'] ?? null;
        if ($notificationId) {
            $stmt = $conn->prepare("UPDATE notifications SET is_read = 1 WHERE notification_id = ?");
            $stmt->execute([$notificationId]);
            echo json_encode(['success' => true]);
        }
        break;

    default:
        http_response_code(400);
        echo json_encode(['error' => 'Invalid action']);
}

function formatNotificationMessage($notification) {
    $type = ucfirst($notification['type']);
    $action = $notification['action'];
    
    switch ($action) {
        case 'registered':
            return "New {$type} registration";
        case 'approved':
            return "{$type} has been approved";
        case 'rejected':
            return "{$type} has been rejected";
        default:
            return $notification['message'];
    }
}
