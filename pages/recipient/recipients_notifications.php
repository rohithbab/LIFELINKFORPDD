<?php
session_start();
require_once '../../config/db_connect.php';

// Check if recipient is logged in
if (!isset($_SESSION['is_recipient']) || !$_SESSION['is_recipient']) {
    header("Location: ../recipient_login.php");
    exit();
}

$recipient_id = $_SESSION['recipient_id'];

// Sync notifications from existing data
try {
    require_once '../../backend/php/sync_recipient_notifications.php';
} catch(Exception $e) {
    error_log("Error syncing notifications: " . $e->getMessage());
}

// Get filter from URL parameter, default to 'all'
$filter = isset($_GET['filter']) ? $_GET['filter'] : 'all';

// Get notifications
try {
    $where_clause = "";
    if ($filter === 'read') {
        $where_clause = "AND is_read = 1";
    } elseif ($filter === 'unread') {
        $where_clause = "AND is_read = 0";
    }

    $stmt = $conn->prepare("
        SELECT * FROM recipient_notifications 
        WHERE recipient_id = ? $where_clause
        ORDER BY created_at DESC
    ");
    $stmt->execute([$recipient_id]);
    $notifications = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch(PDOException $e) {
    error_log("Error fetching notifications: " . $e->getMessage());
    $notifications = [];
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Notifications - LifeLink</title>
    <link rel="stylesheet" href="../../assets/css/donor-dashboard.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        body {
            font-family: 'Poppins', sans-serif;
            margin: 0;
            padding: 0;
            background: #f4f6f9;
        }

        .dashboard-container {
            display: flex;
            min-height: 100vh;
        }

        .main-content {
            flex: 1;
            margin-left: var(--sidebar-width);
            padding: 2rem;
            background: #f4f6f9;
            width: calc(100% - var(--sidebar-width));
            box-sizing: border-box;
        }

        .requests-container {
            width: 85%;
            margin: 0 auto;
            padding: 20px;
            max-width: 1400px;
        }

        .page-header {
            text-align: center;
            margin-bottom: 30px;
            position: relative;
            padding: 0 20px;
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
            text-decoration: none;
        }

        .filter-btn.active {
            background: linear-gradient(135deg, #4CAF50, #2196F3);
            color: white;
        }

        .filter-btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 10px rgba(0,0,0,0.2);
        }

        .request-card {
            background: white;
            border-radius: 12px;
            padding: 25px;
            margin-bottom: 20px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.1);
            transition: transform 0.2s;
            border-left: 4px solid transparent;
            position: relative;
            display: flex;
            gap: 20px;
            width: 100%;
            box-sizing: border-box;
        }

        .request-card:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(0,0,0,0.15);
        }

        .request-icon {
            font-size: 24px;
            color: #2196F3;
            background: rgba(33, 150, 243, 0.1);
            width: 60px;
            height: 60px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            flex-shrink: 0;
        }

        .request-content {
            flex: 1;
            padding-right: 20px;
        }

        .request-message {
            margin: 0 0 15px 0;
            color: #333;
            font-size: 1.1rem;
            line-height: 1.6;
        }

        .request-time {
            color: #666;
            font-size: 0.9em;
            display: flex;
            align-items: center;
            gap: 5px;
        }

        .request-type {
            display: inline-block;
            padding: 3px 8px;
            border-radius: 12px;
            font-size: 0.85em;
            margin-right: 8px;
        }

        .type-request {
            background-color: #E3F2FD;
            color: #1565C0;
        }

        .type-match {
            background-color: #FFF3E0;
            color: #E65100;
        }

        .empty-state {
            text-align: center;
            padding: 40px;
            color: #666;
            background: white;
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }

        .empty-state i {
            font-size: 48px;
            color: #ccc;
            margin-bottom: 15px;
        }

        .action-buttons {
            position: absolute;
            top: 20px;
            right: 20px;
            display: flex;
            gap: 10px;
        }

        .read-toggle, .delete-btn, .read-indicator {
            background: none;
            border: none;
            font-size: 20px;
            width: 35px;
            height: 35px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            transition: all 0.3s ease;
        }

        .read-toggle {
            cursor: pointer;
            color: #ccc;
        }

        .read-toggle:hover {
            background: rgba(0,0,0,0.05);
            color: #4CAF50;
            transform: scale(1.1);
        }

        .read-indicator {
            color: #4CAF50;
            cursor: default;
        }

        .delete-btn {
            color: #ff4444;
            cursor: pointer;
        }

        .delete-btn:hover {
            background: rgba(255,0,0,0.05);
            transform: scale(1.1);
        }

        /* Responsive Design */
        @media (max-width: 1200px) {
            .requests-container {
                width: 90%;
            }
        }

        @media (max-width: 768px) {
            .requests-container {
                width: 95%;
                padding: 15px;
            }

            .request-card {
                padding: 20px;
                gap: 15px;
            }

            .request-icon {
                width: 50px;
                height: 50px;
                font-size: 20px;
            }

            .page-title {
                font-size: 28px;
            }

            .filter-buttons {
                flex-wrap: wrap;
            }

            .filter-btn {
                width: 100%;
            }

            .request-card {
                flex-direction: column;
            }

            .request-icon {
                margin: 0 auto 10px;
            }

            .request-message {
                text-align: center;
            }

            .request-time {
                justify-content: center;
            }

            .action-buttons {
                position: relative;
                top: auto;
                right: auto;
                margin: 10px auto 0;
                justify-content: center;
            }
        }
    </style>
</head>
<body>
    <div class="dashboard-container">
        <?php include '../../includes/recipient_sidebar.php'; ?>

        <main class="main-content">
            <div class="requests-container">
                <div class="page-header">
                    <h1 class="page-title">Notifications</h1>
                </div>

                <div class="filter-buttons">
                    <a href="?filter=all" class="filter-btn <?php echo $filter === 'all' ? 'active' : ''; ?>">
                        All
                    </a>
                    <a href="?filter=unread" class="filter-btn <?php echo $filter === 'unread' ? 'active' : ''; ?>">
                        Unread
                    </a>
                    <a href="?filter=read" class="filter-btn <?php echo $filter === 'read' ? 'active' : ''; ?>">
                        Read
                    </a>
                </div>

                <?php if (empty($notifications)): ?>
                    <div class="empty-state">
                        <i class="fas fa-bell-slash"></i>
                        <h2>No Notifications</h2>
                        <p>You don't have any <?php echo $filter !== 'all' ? $filter . ' ' : ''; ?>notifications at the moment.</p>
                    </div>
                <?php else: ?>
                    <?php foreach ($notifications as $notification): ?>
                        <div class="request-card <?php echo !$notification['is_read'] ? 'unread' : ''; ?>" 
                             data-id="<?php echo $notification['notification_id']; ?>">
                            <div class="request-icon">
                                <i class="fas <?php echo $notification['type'] === 'request_status' ? 'fa-file-medical' : 'fa-handshake'; ?>"></i>
                            </div>
                            <div class="request-content">
                                <span class="request-type <?php echo $notification['type'] === 'request_status' ? 'type-request' : 'type-match'; ?>">
                                    <?php echo $notification['type'] === 'request_status' ? 'Request Update' : 'Match Found'; ?>
                                </span>
                                <p class="request-message"><?php echo htmlspecialchars($notification['message']); ?></p>
                                <span class="request-time">
                                    <i class="far fa-clock"></i>
                                    <?php echo date('M d, Y h:i A', strtotime($notification['created_at'])); ?>
                                </span>
                            </div>
                            <div class="action-buttons">
                                <?php if (!$notification['is_read']): ?>
                                    <button class="read-toggle" 
                                            onclick="toggleRead(<?php echo $notification['notification_id']; ?>, this)"
                                            title="Mark as read">
                                        <i class="fas fa-check-circle"></i>
                                    </button>
                                <?php else: ?>
                                    <span class="read-indicator">
                                        <i class="fas fa-check-circle"></i>
                                    </span>
                                    <button class="delete-btn" 
                                            onclick="deleteNotification(<?php echo $notification['notification_id']; ?>, this)"
                                            title="Delete notification">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                <?php endif; ?>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </main>
    </div>

    <script>
    function toggleRead(notificationId, button) {
        // Show loading state
        button.style.opacity = '0.5';
        button.style.pointerEvents = 'none';
        
        fetch('../../backend/php/toggle_recipient_notification_read.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
            },
            body: JSON.stringify({
                notification_id: notificationId,
                is_read: 1
            })
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                const card = button.closest('.request-card');
                card.classList.remove('unread');
                
                // Replace the toggle button with static indicator and delete button
                const actionButtons = card.querySelector('.action-buttons');
                actionButtons.innerHTML = `
                    <span class="read-indicator">
                        <i class="fas fa-check-circle"></i>
                    </span>
                    <button class="delete-btn" 
                            onclick="deleteNotification(${notificationId}, this)"
                            title="Delete notification">
                        <i class="fas fa-trash"></i>
                    </button>
                `;

                // Animate the change
                actionButtons.style.opacity = '0';
                setTimeout(() => {
                    actionButtons.style.opacity = '1';
                }, 50);
            } else {
                // Reset button state on error
                button.style.opacity = '1';
                button.style.pointerEvents = 'auto';
                console.error('Failed to mark as read:', data.message);
            }
        })
        .catch(error => {
            // Reset button state on error
            button.style.opacity = '1';
            button.style.pointerEvents = 'auto';
            console.error('Error:', error);
        });
    }

    function deleteNotification(notificationId, button) {
        if (!confirm('Are you sure you want to delete this notification?')) {
            return;
        }

        fetch('../../backend/php/delete_recipient_notification.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
            },
            body: JSON.stringify({
                notification_id: notificationId
            })
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                const card = button.closest('.request-card');
                card.style.opacity = '0';
                setTimeout(() => {
                    card.remove();
                    if (document.querySelectorAll('.request-card').length === 0) {
                        location.reload(); // Reload to show empty state
                    }
                }, 300);
            }
        })
        .catch(error => console.error('Error:', error));
    }

    // Auto-refresh notifications every 30 seconds
    setInterval(function() {
        const currentFilter = new URLSearchParams(window.location.search).get('filter') || 'all';
        window.location.href = `?filter=${currentFilter}`;
    }, 30000);
    </script>
</body>
</html>
