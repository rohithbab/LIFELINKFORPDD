<?php
session_start();
require_once '../../backend/php/connection.php';
require_once '../../backend/php/queries.php';

// Check if admin is logged in
if (!isset($_SESSION['admin_id'])) {
    header('Location: ../admin_login.php');
    exit();
}

$stats = getDashboardStats($conn);
$notifications = getAdminNotifications($conn, 5);
$pendingHospitals = getPendingHospitals($conn);
$urgentRecipients = getUrgentRecipients($conn);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard - LifeLink</title>
    <link rel="stylesheet" href="../../assets/css/styles.css">
    <link rel="stylesheet" href="../../assets/css/admin.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
</head>
<body>
    <nav class="navbar">
        <div class="nav-container">
            <a href="#" class="logo">
                <span class="logo-life">Life</span>Link Admin
            </a>
            <div class="nav-links">
                <a href="view-hospitals.php">Hospitals</a>
                <a href="view-donors.php">Donors</a>
                <a href="view-recipients.php">Recipients</a>
                <a href="analytics.php">Analytics</a>
                <a href="notifications.php">Notifications</a>
                <a href="../logout.php" class="btn btn-outline">Logout</a>
            </div>
        </div>
    </nav>

    <div class="container">
        <div class="dashboard-grid">
            <!-- Statistics Cards -->
            <div class="stats-container">
                <div class="card stat-card">
                    <h3>Total Hospitals</h3>
                    <p class="stat-number"><?php echo $stats['total_hospitals']; ?></p>
                </div>
                <div class="card stat-card">
                    <h3>Total Donors</h3>
                    <p class="stat-number"><?php echo $stats['total_donors']; ?></p>
                </div>
                <div class="card stat-card">
                    <h3>Total Recipients</h3>
                    <p class="stat-number"><?php echo $stats['total_recipients']; ?></p>
                </div>
                <div class="card stat-card">
                    <h3>Pending Approvals</h3>
                    <p class="stat-number"><?php echo $stats['pending_hospitals']; ?></p>
                </div>
            </div>

            <!-- Recent Activities -->
            <div class="card recent-activities">
                <h2>Recent Activities</h2>
                <div class="activity-list">
                    <?php foreach ($notifications as $notification): ?>
                    <div class="activity-item">
                        <span class="activity-type"><?php echo htmlspecialchars($notification['type']); ?></span>
                        <p><?php echo htmlspecialchars($notification['message']); ?></p>
                        <small><?php echo date('M d, Y H:i', strtotime($notification['created_at'])); ?></small>
                    </div>
                    <?php endforeach; ?>
                </div>
            </div>

            <!-- Urgent Cases -->
            <div class="card urgent-cases">
                <h2>Urgent Recipients</h2>
                <div class="urgent-list">
                    <?php foreach ($urgentRecipients as $recipient): ?>
                    <div class="urgent-item">
                        <h4><?php echo htmlspecialchars($recipient['name']); ?></h4>
                        <p>Needed Organ: <?php echo htmlspecialchars($recipient['needed_organ']); ?></p>
                        <p>Blood Type: <?php echo htmlspecialchars($recipient['blood_type']); ?></p>
                    </div>
                    <?php endforeach; ?>
                </div>
            </div>

            <!-- Pending Approvals -->
            <div class="card pending-approvals">
                <h2>Pending Hospital Approvals</h2>
                <div class="pending-list">
                    <?php foreach ($pendingHospitals as $hospital): ?>
                    <div class="pending-item">
                        <h4><?php echo htmlspecialchars($hospital['name']); ?></h4>
                        <p><?php echo htmlspecialchars($hospital['address']); ?></p>
                        <div class="approval-actions">
                            <button class="btn btn-primary approve-btn" data-id="<?php echo $hospital['id']; ?>">Approve</button>
                            <button class="btn btn-outline reject-btn" data-id="<?php echo $hospital['id']; ?>">Reject</button>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>
    </div>

    <script src="../../assets/js/admin.js"></script>
</body>
</html>
