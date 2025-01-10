<?php
session_start();
require_once '../../config/db_connect.php';

// Check if donor is logged in
if (!isset($_SESSION['is_donor']) || !$_SESSION['is_donor']) {
    header("Location: ../donor_login.php");
    exit();
}

$donor_id = $_SESSION['donor_id'];

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
        SELECT * FROM donor_notifications 
        WHERE donor_id = ? $where_clause
        ORDER BY created_at DESC
    ");
    $stmt->execute([$donor_id]);
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
    <link rel="stylesheet" href="../../assets/css/donor-notifications.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>
<body>
    <div class="dashboard-container">
        <!-- Sidebar -->
        <aside class="sidebar">
            <div class="sidebar-header">
                <i class="fas fa-heartbeat"></i>
                <span>LifeLink</span>
            </div>
            <nav class="sidebar-nav">
                <ul>
                    <li>
                        <a href="donor_dashboard.php">
                            <i class="fas fa-home"></i>
                            <span>Dashboard</span>
                        </a>
                    </li>
                    <li>
                        <a href="donor_personal_details.php">
                            <i class="fas fa-user"></i>
                            <span>Profile</span>
                        </a>
                    </li>
                    <li>
                        <a href="search_hospitals_for_donors.php">
                            <i class="fas fa-search"></i>
                            <span>Search Hospitals</span>
                        </a>
                    </li>
                    <li>
                        <a href="my_requests_for_donors.php">
                            <i class="fas fa-list"></i>
                            <span>My Requests</span>
                        </a>
                    </li>
                    <li>
                        <a href="donors_notifications.php" class="active">
                            <i class="fas fa-bell"></i>
                            <span>Notifications</span>
                        </a>
                    </li>
                    <li>
                        <a href="../donor_login.php">
                            <i class="fas fa-sign-out-alt"></i>
                            <span>Logout</span>
                        </a>
                    </li>
                </ul>
            </nav>
        </aside>
        
        <!-- Main Content -->
        <main class="main-content">
            <div class="dashboard-header">
                <h1 class="gradient-text">Notifications</h1>
            </div>

            <div class="notifications-container">
                <!-- Filter Buttons -->
                <div class="notification-filters">
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

                <!-- Notifications List -->
                <?php if (empty($notifications)): ?>
                    <div class="empty-state">
                        <i class="fas fa-bell-slash"></i>
                        <h2>No Notifications</h2>
                        <p>You don't have any <?php echo $filter !== 'all' ? $filter . ' ' : ''; ?>notifications at the moment.</p>
                    </div>
                <?php else: ?>
                    <?php foreach ($notifications as $notification): ?>
                        <div class="notification-card">
                            <div class="notification-icon">
                                <i class="fas <?php echo $notification['type'] === 'request_status' ? 'fa-file-medical' : 'fa-handshake'; ?>"></i>
                            </div>
                            <div class="notification-content">
                                <p class="notification-message"><?php echo htmlspecialchars($notification['message']); ?></p>
                                <span class="notification-time">
                                    <i class="far fa-clock"></i>
                                    <?php echo date('M d, Y h:i A', strtotime($notification['created_at'])); ?>
                                </span>
                            </div>
                            <button class="read-toggle <?php echo $notification['is_read'] ? 'read' : ''; ?>" 
                                    onclick="toggleRead(<?php echo $notification['notification_id']; ?>, this)"
                                    title="<?php echo $notification['is_read'] ? 'Mark as unread' : 'Mark as read'; ?>">
                                <i class="fas fa-check-circle"></i>
                            </button>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </main>
    </div>

    <script>
    function toggleRead(notificationId, button) {
        const isCurrentlyRead = button.classList.contains('read');
        const newReadStatus = !isCurrentlyRead;
        
        fetch('../../backend/php/toggle_donor_notification_read.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
            },
            body: JSON.stringify({
                notification_id: notificationId,
                is_read: newReadStatus ? 1 : 0
            })
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                button.classList.toggle('read');
                button.title = newReadStatus ? 'Mark as unread' : 'Mark as read';
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
