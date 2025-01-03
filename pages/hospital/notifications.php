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
            dr.request_date,
            dr.status,
            dr.response_date,
            dr.response_message,
            h.name as hospital_name,
            d.name as donor_name,
            d.blood_group,
            ha.organ_type
        FROM donor_requests dr
        JOIN hospitals h ON (h.hospital_id = dr.requesting_hospital_id OR h.hospital_id = dr.donor_hospital_id)
        JOIN donor d ON d.donor_id = dr.donor_id
        JOIN hospital_donor_approvals ha ON ha.donor_id = d.donor_id
        WHERE (dr.requesting_hospital_id = ? OR dr.donor_hospital_id = ?)
        ORDER BY dr.request_date DESC
        LIMIT 50
    ");
    $stmt->execute([$hospital_id, $hospital_id]);
    $notifications = $stmt->fetchAll(PDO::FETCH_ASSOC);

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
                        <div class="notification-card">
                            <div class="notification-header">
                                <span class="notification-type">
                                    <?php if ($notification['type'] === 'donor_request'): ?>
                                        <i class="fas fa-user"></i> Donor Request
                                    <?php endif; ?>
                                </span>
                                <span class="notification-date">
                                    <?php echo date('M d, Y h:i A', strtotime($notification['request_date'])); ?>
                                </span>
                            </div>
                            <div class="notification-content">
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
                            </div>
                            <div class="notification-actions">
                                <?php if ($notification['type'] === 'donor_request'): ?>
                                    <a href="donor_requests.php?type=<?php echo $notification['requesting_hospital_id'] == $hospital_id ? 'outgoing' : 'incoming'; ?>" class="action-btn view-btn">
                                        View Details
                                    </a>
                                <?php endif; ?>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </main>
    </div>
</body>
</html>
