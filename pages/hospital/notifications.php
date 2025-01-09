<?php
session_start();
require_once '../../config/db_connect.php';

// Check if hospital is logged in
if (!isset($_SESSION['hospital_logged_in']) || !$_SESSION['hospital_logged_in']) {
    header("Location: ../../pages/hospital_login.php");
    exit();
}

$hospital_id = $_SESSION['hospital_id'];

// Get all notifications for this hospital
try {
    // Get donor requests notifications
    $stmt = $conn->prepare("
        SELECT 
            'donor_request' as type,
            dr.request_id,
            dr.requesting_hospital_id,
            dr.donor_hospital_id,
            dr.request_date,
            dr.status,
            dr.response_date,
            dr.response_message,
            h.name as hospital_name,
            d.name as donor_name,
            d.blood_group,
            ha.organ_type,
            NULL as is_read,
            dr.request_date as created_at,
            NULL as notification_id,
            NULL as link_url
        FROM donor_requests dr
        JOIN hospitals h ON (h.hospital_id = dr.requesting_hospital_id OR h.hospital_id = dr.donor_hospital_id)
        JOIN donor d ON d.donor_id = dr.donor_id
        JOIN hospital_donor_approvals ha ON ha.donor_id = d.donor_id
        WHERE (dr.requesting_hospital_id = ? OR dr.donor_hospital_id = ?)
    ");
    $stmt->execute([$hospital_id, $hospital_id]);
    $request_notifications = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Get registration notifications
    $stmt = $conn->prepare("
        SELECT *
        FROM hospital_notifications
        WHERE hospital_id = ? 
        AND (type = 'donor_registration' OR type = 'recipient_registration')
    ");
    $stmt->execute([$hospital_id]);
    $registration_notifications = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Merge and sort notifications by date
    $notifications = array_merge($request_notifications, $registration_notifications);
    usort($notifications, function($a, $b) {
        $date_a = isset($a['request_date']) ? $a['request_date'] : $a['created_at'];
        $date_b = isset($b['request_date']) ? $b['request_date'] : $b['created_at'];
        return strtotime($date_b) - strtotime($date_a);
    });

} catch(PDOException $e) {
    error_log("Error: " . $e->getMessage());
    $notifications = [];
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Notifications - LifeLink</title>
    <link rel="stylesheet" href="../../assets/css/hospital-dashboard.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        .notifications-container {
            padding: 2rem;
        }

        .notification-card {
            background: white;
            border-radius: 10px;
            padding: 1.5rem;
            margin-bottom: 1rem;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
            transition: transform 0.3s ease;
            border-left: 4px solid transparent;
        }

        .notification-card.unread {
            border-left-color: var(--primary-blue);
        }

        .notification-card:hover {
            transform: translateY(-2px);
        }

        .notification-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 1rem;
        }

        .notification-type {
            background: var(--primary-blue);
            color: white;
            padding: 0.3rem 0.8rem;
            border-radius: 15px;
            font-size: 0.9em;
        }

        .notification-date {
            color: #666;
            font-size: 0.9em;
        }

        .notification-content {
            margin: 1rem 0;
        }

        .notification-status {
            display: inline-block;
            padding: 0.3rem 0.8rem;
            border-radius: 15px;
            font-size: 0.9em;
            margin-top: 0.5rem;
        }

        .status-pending {
            background: #ffd700;
            color: #000;
        }

        .status-approved {
            background: #4CAF50;
            color: white;
        }

        .status-rejected {
            background: #f44336;
            color: white;
        }

        .notification-actions {
            margin-top: 1rem;
            display: flex;
            gap: 1rem;
        }

        .action-btn {
            padding: 0.5rem 1rem;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            transition: all 0.3s ease;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
        }

        .view-btn {
            background: var(--primary-blue);
            color: white;
        }

        .view-btn:hover {
            background: var(--primary-green);
        }

        .empty-state {
            text-align: center;
            padding: 3rem;
            color: #666;
        }

        .empty-state i {
            font-size: 3em;
            color: #ddd;
            margin-bottom: 1rem;
        }

        .response-message {
            margin-top: 1rem;
            padding: 1rem;
            background: #f5f5f5;
            border-radius: 5px;
        }
    </style>
</head>
<body>
    <div class="dashboard-container">
        <?php include '../../includes/hospital_sidebar.php'; ?>
        
        <main class="main-content">
            <div class="dashboard-header">
                <h1>Notifications</h1>
            </div>

            <div class="notifications-container">
                <?php if (empty($notifications)): ?>
                    <div class="empty-state">
                        <i class="fas fa-bell-slash"></i>
                        <h2>No Notifications</h2>
                        <p>You don't have any notifications at the moment.</p>
                    </div>
                <?php else: ?>
                    <?php foreach ($notifications as $notification): ?>
                        <div class="notification-card <?php echo isset($notification['is_read']) && !$notification['is_read'] ? 'unread' : ''; ?>">
                            <div class="notification-header">
                                <span class="notification-type">
                                    <?php if ($notification['type'] === 'donor_request'): ?>
                                        <i class="fas fa-user"></i> Donor Request
                                    <?php elseif ($notification['type'] === 'donor_registration'): ?>
                                        <i class="fas fa-user-plus"></i> New Donor
                                    <?php elseif ($notification['type'] === 'recipient_registration'): ?>
                                        <i class="fas fa-procedures"></i> New Recipient
                                    <?php endif; ?>
                                </span>
                                <span class="notification-date">
                                    <?php 
                                        $date = isset($notification['request_date']) ? 
                                            $notification['request_date'] : 
                                            $notification['created_at'];
                                        echo date('M d, Y h:i A', strtotime($date)); 
                                    ?>
                                </span>
                            </div>
                            <div class="notification-content">
                                <?php if ($notification['type'] === 'donor_request'): ?>
                                    <p>
                                        <strong><?php echo htmlspecialchars($notification['hospital_name']); ?></strong>
                                        has requested donor 
                                        <strong><?php echo htmlspecialchars($notification['donor_name']); ?></strong>
                                        (<?php echo htmlspecialchars($notification['blood_group']); ?>, 
                                        <?php echo htmlspecialchars($notification['organ_type']); ?>)
                                    </p>
                                    <span class="notification-status status-<?php echo strtolower($notification['status']); ?>">
                                        <?php echo ucfirst($notification['status']); ?>
                                    </span>
                                    <?php if ($notification['response_message']): ?>
                                        <p class="response-message">
                                            <strong>Response:</strong> <?php echo htmlspecialchars($notification['response_message']); ?>
                                        </p>
                                    <?php endif; ?>
                                <?php else: ?>
                                    <p><?php echo $notification['message']; ?></p>
                                <?php endif; ?>
                            </div>
                            <div class="notification-actions">
                                <?php if ($notification['type'] === 'donor_request'): ?>
                                    <a href="donor_requests.php?type=<?php echo $notification['requesting_hospital_id'] == $hospital_id ? 'outgoing' : 'incoming'; ?>" class="action-btn view-btn">
                                        <i class="fas fa-eye"></i> View Details
                                    </a>
                                <?php else: ?>
                                    <a href="<?php echo $notification['link_url']; ?>" class="action-btn view-btn" onclick="markAsRead(<?php echo $notification['notification_id']; ?>)">
                                        <i class="fas fa-eye"></i> View Details
                                    </a>
                                <?php endif; ?>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </main>
    </div>

    <script>
    function markAsRead(notificationId) {
        fetch('../../backend/php/mark_notification_read.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
            },
            body: JSON.stringify({
                notification_id: notificationId
            })
        });
    }
    </script>
</body>
</html>
