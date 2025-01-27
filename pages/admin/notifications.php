<?php
session_start();
require_once '../../backend/php/connection.php';
require_once '../../backend/php/queries.php';

// Check if admin is logged in
if (!isset($_SESSION['admin_id'])) {
    header('Location: ../admin_login.php');
    exit();
}

// Get all notifications
$notifications = getAdminNotifications($conn, 50);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Notifications - LifeLink Admin</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link rel="stylesheet" href="../../assets/css/styles.css">
    <style>
        body {
            background-color: #f5f5f5;
            margin: 0;
            padding: 20px;
            font-family: Arial, sans-serif;
        }

        .notifications-container {
            max-width: 1000px;
            margin: 0 auto;
            padding: 20px;
        }

        .page-header {
            text-align: center;
            margin-bottom: 30px;
            position: relative;
        }

        .page-title {
            font-size: 36px;
            margin: 0;
            background: linear-gradient(135deg, #4CAF50, #2196F3);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            display: inline-block;
        }

        .back-btn {
            position: absolute;
            right: 0;
            top: 50%;
            transform: translateY(-50%);
            padding: 10px 20px;
            border-radius: 25px;
            text-decoration: none;
            color: white;
            background: linear-gradient(135deg, #4CAF50, #2196F3);
            transition: all 0.3s ease;
            display: flex;
            align-items: center;
            gap: 8px;
            box-shadow: 0 2px 5px rgba(0,0,0,0.1);
        }

        .back-btn:hover {
            transform: translateY(-52%);
            box-shadow: 0 4px 10px rgba(0,0,0,0.2);
        }

        .filter-buttons {
            display: flex;
            justify-content: center;
            gap: 15px;
            margin-bottom: 30px;
        }

        .filter-btn {
            padding: 10px 25px;
            border: none;
            border-radius: 20px;
            cursor: pointer;
            font-size: 16px;
            transition: all 0.3s ease;
            background: white;
            color: #666;
            box-shadow: 0 2px 5px rgba(0,0,0,0.1);
        }

        .filter-btn.active {
            background: linear-gradient(135deg, #4CAF50, #2196F3);
            color: white;
        }

        .filter-btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 10px rgba(0,0,0,0.2);
        }

        .notification-card {
            background: white;
            border-radius: 8px;
            padding: 15px;
            margin-bottom: 15px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
            transition: transform 0.2s;
            border-left: 4px solid transparent;
            position: relative;
        }

        .notification-card:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 8px rgba(0,0,0,0.15);
        }

        .notification-card.unread {
            background-color: #e3f2fd;
            border-left-color: #2196F3;
        }

        .notification-actions {
            position: absolute;
            right: 15px;
            top: 50%;
            transform: translateY(-50%);
            display: flex;
            gap: 10px;
            align-items: center;
        }

        .delete-btn {
            color: #dc3545;
            background: none;
            border: none;
            cursor: pointer;
            padding: 5px;
            border-radius: 50%;
            width: 32px;
            height: 32px;
            display: flex;
            align-items: center;
            justify-content: center;
            transition: all 0.3s ease;
            opacity: 0;
            visibility: hidden;
        }

        .notification-card:not(.unread):hover .delete-btn {
            opacity: 1;
            visibility: visible;
        }

        .delete-btn:hover {
            background-color: #ffebee;
            transform: scale(1.1);
        }

        .delete-btn i {
            font-size: 1.2rem;
        }

        .notification-time {
            color: #666;
            font-size: 0.9em;
            margin-top: 5px;
        }

        .notification-type {
            display: inline-block;
            padding: 3px 8px;
            border-radius: 12px;
            font-size: 0.85em;
            margin-right: 8px;
        }

        .type-hospital {
            background-color: #E3F2FD;
            color: #1565C0;
        }

        .type-donor {
            background-color: #FCE4EC;
            color: #C2185B;
        }

        .type-recipient {
            background-color: #E8F5E9;
            color: #2E7D32;
        }

        .type-organ_match {
            background-color: #FFF3E0;
            color: #E65100;
        }

        .empty-notifications {
            text-align: center;
            padding: 40px;
            color: #666;
        }

        .mark-read-mini-btn {
            background-color: #4CAF50;
            color: white;
            border: none;
            padding: 8px 12px;
            border-radius: 20px;
            cursor: pointer;
            font-size: 16px;
            transition: all 0.3s ease;
            box-shadow: 0 2px 5px rgba(0,0,0,0.1);
        }

        .mark-read-mini-btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 10px rgba(0,0,0,0.2);
        }
    </style>
</head>
<body>
    <div class="notifications-container">
        <div class="page-header">
            <h1 class="page-title">All Notifications</h1>
            <a href="admin_dashboard.php" class="back-btn">
                <i class="fas fa-arrow-left"></i>
                Back to Dashboard
            </a>
        </div>

        <div class="filter-buttons">
            <button class="filter-btn active" onclick="filterNotifications('all')">All</button>
            <button class="filter-btn" onclick="filterNotifications('unread')">Unread</button>
            <button class="filter-btn" onclick="filterNotifications('read')">Read</button>
        </div>

        <div id="notifications-list">
            <?php if (empty($notifications)): ?>
                <div class="empty-notifications">
                    <i class="fas fa-bell" style="font-size: 48px; color: #ccc; margin-bottom: 15px;"></i>
                    <p>No notifications yet</p>
                </div>
            <?php else: ?>
                <?php foreach ($notifications as $notification): ?>
                    <div class="notification-card <?php echo !$notification['is_read'] ? 'unread' : ''; ?>" 
                         data-id="<?php echo $notification['notification_id']; ?>" 
                         data-type="<?php echo !$notification['is_read'] ? 'unread' : 'read'; ?>">
                        <div class="notification-content">
                            <span class="notification-type type-<?php echo strtolower($notification['type']); ?>">
                                <?php echo ucfirst($notification['type']); ?>
                            </span>
                            <div class="message">
                                <?php echo $notification['message']; ?>
                            </div>
                            <div class="notification-time">
                                <?php echo date('M d, Y h:i A', strtotime($notification['created_at'])); ?>
                            </div>
                        </div>
                        <div class="notification-actions">
                            <?php if (!$notification['is_read']): ?>
                                <button class="mark-read-mini-btn" onclick="markAsRead('<?php echo $notification['notification_id']; ?>')">
                                    <i class="fas fa-check"></i>
                                </button>
                            <?php else: ?>
                                <button class="delete-btn" onclick="deleteNotification('<?php echo $notification['notification_id']; ?>')">
                                    <i class="fas fa-trash-alt"></i>
                                </button>
                            <?php endif; ?>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
    </div>

    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script>
        function filterNotifications(type) {
            $('.filter-btn').removeClass('active');
            $(`.filter-btn:contains('${type[0].toUpperCase() + type.slice(1)}')`).addClass('active');
            
            if (type === 'all') {
                $('.notification-card').show();
            } else {
                $('.notification-card').hide();
                $(`.notification-card[data-type="${type}"]`).show();
            }
        }

        function markAsRead(id) {
            $.ajax({
                url: '../../backend/php/mark_notification_read.php',
                method: 'POST',
                data: { notification_id: id },
                success: function(response) {
                    try {
                        const result = JSON.parse(response);
                        if (result.success) {
                            const card = $(`.notification-card[data-id="${id}"]`);
                            card.removeClass('unread');
                            card.attr('data-type', 'read');
                            card.find('.mark-read-mini-btn').replaceWith(`
                                <button class="delete-btn" onclick="deleteNotification('${id}')">
                                    <i class="fas fa-trash-alt"></i>
                                </button>
                            `);
                        }
                    } catch (e) {
                        console.error('Error parsing response:', e);
                    }
                }
            });
        }

        function deleteNotification(id) {
            if (confirm('Are you sure you want to delete this notification?')) {
                $.ajax({
                    url: '../../backend/php/delete_notification.php',
                    method: 'POST',
                    data: { notification_id: id },
                    success: function(response) {
                        try {
                            const result = JSON.parse(response);
                            if (result.success) {
                                const card = $(`.notification-card[data-id="${id}"]`);
                                card.fadeOut(300, function() {
                                    $(this).remove();
                                    if ($('.notification-card').length === 0) {
                                        $('#notifications-list').html(`
                                            <div class="empty-notifications">
                                                <i class="fas fa-bell" style="font-size: 48px; color: #ccc; margin-bottom: 15px;"></i>
                                                <p>No notifications yet</p>
                                            </div>
                                        `);
                                    }
                                });
                            } else {
                                alert(result.message || 'Failed to delete notification');
                            }
                        } catch (e) {
                            console.error('Error parsing response:', e);
                            alert('An error occurred while deleting the notification');
                        }
                    },
                    error: function(xhr) {
                        console.error('Ajax error:', xhr);
                        alert('Failed to connect to the server');
                    }
                });
            }
        }
    </script>
</body>
</html>
