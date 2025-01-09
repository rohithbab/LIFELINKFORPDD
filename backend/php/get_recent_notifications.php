<?php
require_once 'connection.php';
require_once 'queries.php';

header('Content-Type: application/json');

try {
    // Get unread count
    $stmt = $conn->prepare("SELECT COUNT(*) FROM notifications WHERE is_read = 0");
    $stmt->execute();
    $unread_count = $stmt->fetchColumn();
    
    // Get 3 most recent notifications
    $stmt = $conn->prepare("
        SELECT 
            notification_id,
            type,
            action,
            entity_id,
            message,
            is_read,
            created_at,
            link_url
        FROM notifications 
        ORDER BY created_at DESC 
        LIMIT 3
    ");
    $stmt->execute();
    $notifications = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Format timestamps
    foreach ($notifications as &$notification) {
        $notification['created_at'] = date('M d, Y H:i', strtotime($notification['created_at']));
    }
    
    echo json_encode([
        'success' => true,
        'unread_count' => $unread_count,
        'notifications' => $notifications
    ]);
    
} catch (PDOException $e) {
    echo json_encode([
        'success' => false,
        'message' => 'Error fetching notifications: ' . $e->getMessage()
    ]);
}
