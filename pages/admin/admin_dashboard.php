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
$pendingDonors = getPendingDonors($conn);
$pendingRecipients = getPendingRecipients($conn);
$urgentRecipients = getUrgentRecipients($conn);

// Get pending hospitals count using PDO
$stmt = $conn->prepare("SELECT COUNT(*) FROM hospitals WHERE status = 'pending'");
$stmt->execute();
$pending_count = $stmt->fetchColumn();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard - LifeLink</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link rel="stylesheet" href="../../assets/css/styles.css">
    <link rel="stylesheet" href="../../assets/css/admin-dashboard.css">
    <style>
        /* Logo and Navigation Styles */
        .navbar {
            background: white;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
            padding: 1rem 2rem;
        }

        .nav-container {
            display: flex;
            justify-content: space-between;
            align-items: center;
            max-width: 1400px;
            margin: 0 auto;
        }

        .logo-text {
            font-size: 1.8rem;
            font-weight: bold;
        }

        .logo-gradient {
            background: linear-gradient(45deg, #28a745, #007bff);
            -webkit-background-clip: text;
            background-clip: text;
            -webkit-text-fill-color: transparent;
            font-weight: bold;
        }

        .nav-links {
            display: flex;
            gap: 2rem;
            align-items: center;
        }

        .nav-links a {
            text-decoration: none;
            color: #333;
            font-weight: 500;
            padding: 0.5rem 1rem;
            position: relative;
            transition: color 0.3s ease;
        }

        .nav-links a:not(.btn)::after {
            content: '';
            position: absolute;
            width: 0;
            height: 2px;
            bottom: 0;
            left: 0;
            background: linear-gradient(45deg, #28a745, #007bff);
            transition: width 0.3s ease;
        }

        .nav-links a:not(.btn):hover::after {
            width: 100%;
        }

        .nav-links .btn-logout {
            background: linear-gradient(45deg, #28a745, #007bff);
            color: white;
            padding: 0.8rem 1.5rem;
            border-radius: 25px;
            font-weight: 500;
            transition: transform 0.3s ease;
            border: none;
            cursor: pointer;
        }

        .nav-links .btn-logout:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 8px rgba(0,0,0,0.1);
        }

        .nav-links a.active {
            color: #007bff;
        }

        /* New Grid Layout */
        .dashboard-layout {
            display: grid;
            grid-template-columns: 1fr;
            gap: 20px;
            padding: 20px;
            max-width: 1800px;
            margin: 0 auto;
        }

        .tables-section {
            display: flex;
            flex-direction: column;
            gap: 20px;
        }

        .table-container {
            background: white;
            border-radius: 10px;
            padding: 20px;
            margin-bottom: 20px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }

        .table-container h2 {
            color: #1a73e8;
            margin-bottom: 20px;
            background: linear-gradient(135deg, #1a73e8, #34a853);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
        }

        .stats-grid {
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            gap: 20px;
            margin-bottom: 20px;
        }

        .stats-grid .stat-card:nth-last-child(-n+3) {
            grid-column: span 1;
        }

        .stat-card {
            background: white;
            border-radius: 10px;
            padding: 20px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            position: relative;
            padding-left: 60px;
            text-align: left;
        }

        .stat-card h3 {
            color: #1a73e8;
            margin-bottom: 10px;
            font-size: 1.1rem;
            background: linear-gradient(135deg, #1a73e8, #34a853);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
        }

        .stat-card i {
            position: absolute;
            left: 20px;
            top: 50%;
            transform: translateY(-50%);
            font-size: 24px;
            color: #007bff;
        }

        .stat-card.urgent i {
            color: #dc3545;
        }

        .stat-card.success i {
            color: #28a745;
        }

        .stat-card.pending i {
            color: #ffc107;
        }

        .pending-list, .urgent-list {
            display: flex;
            flex-direction: column;
            gap: 15px;
            max-height: 500px;
            overflow-y: auto;
        }

        .pending-item, .urgent-item {
            background: #f8f9fa;
            padding: 15px;
            border-radius: 6px;
            border: 1px solid #e9ecef;
        }

        .action-buttons {
            display: flex;
            gap: 10px;
            margin-top: 10px;
        }

        .new-badge {
            background: #28a745;
            color: white;
            padding: 2px 6px;
            border-radius: 4px;
            font-size: 0.8em;
            margin-left: 8px;
        }

        .table-responsive {
            overflow-x: auto;
        }

        .table {
            width: 100%;
            border-collapse: collapse;
        }

        .table th, .table td {
            padding: 12px;
            text-align: left;
            border-bottom: 1px solid #dee2e6;
        }

        .btn {
            padding: 8px 16px;
            border-radius: 4px;
            border: none;
            cursor: pointer;
            font-size: 14px;
            transition: background-color 0.3s;
        }

        .btn-success {
            background: #28a745;
            color: white;
        }

        .btn-danger {
            background: #dc3545;
            color: white;
        }

        .btn-primary {
            background: #007bff;
            color: white;
        }

        /* Notification Bell Styles */
        .notification-bell {
            position: fixed;
            top: 20px;
            right: 20px;
            background: white;
            border-radius: 50%;
            width: 50px;
            height: 50px;
            display: flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            z-index: 1000;
        }

        .notification-bell i {
            font-size: 24px;
            color: #007bff;
        }

        .notification-bell .badge {
            position: absolute;
            top: -5px;
            right: -5px;
            background: #dc3545;
            color: white;
            border-radius: 50%;
            padding: 4px 8px;
            font-size: 12px;
        }

        .notification-popup {
            position: fixed;
            top: 80px;
            right: 20px;
            width: 300px;
            max-height: 400px;
            background: white;
            border-radius: 8px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            z-index: 1000;
            display: none;
            overflow-y: auto;
        }

        .notification-popup.show {
            display: block;
        }

        /* Stats Cards with Icons */
        .stat-card {
            position: relative;
            padding-left: 60px;
            text-align: left;
        }

        .stat-card i {
            position: absolute;
            left: 20px;
            top: 50%;
            transform: translateY(-50%);
            font-size: 24px;
            color: #007bff;
        }

        .stat-card.urgent i {
            color: #dc3545;
        }

        .stat-card.success i {
            color: #28a745;
        }

        .stat-card.pending i {
            color: #ffc107;
        }

        /* Remove Recent Notifications section */
        .notifications-section {
            display: none;
        }

        /* Add this to your existing styles */
        .table thead th {
            background: linear-gradient(135deg, #1a73e8, #34a853);
            color: white;
            padding: 15px;
            font-weight: 500;
            text-transform: uppercase;
            font-size: 0.9rem;
            letter-spacing: 0.5px;
            border: none;
        }

        .table thead tr {
            background: none;
            border: none;
        }

        .table {
            border-collapse: separate;
            border-spacing: 0 5px;
            margin-top: -5px;
        }

        .table tbody tr {
            background: white;
            box-shadow: 0 2px 4px rgba(0,0,0,0.05);
            border-radius: 8px;
            margin-bottom: 5px;
        }

        .table tbody td {
            padding: 15px;
            vertical-align: middle;
            border: none;
        }

        .table tbody tr:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 8px rgba(0,0,0,0.1);
            transition: all 0.3s ease;
        }

        .details-btn {
            display: inline-block;
            padding: 6px 12px;
            background: #3498db;
            color: white;
            text-decoration: none;
            border-radius: 4px;
            transition: background 0.3s;
        }
        .details-btn:hover {
            background: #2980b9;
        }
    </style>
</head>
<body>
    <div class="admin-container">
        <!-- Sidebar -->
        <div class="sidebar">
            <div class="sidebar-header">
                <h2><span class="logo-gradient">LifeLink</span> Admin</h2>
            </div>
            <ul class="nav-menu">
                <li class="nav-item">
                    <a href="admin_dashboard.php" class="nav-link active">
                        <i class="fas fa-tachometer-alt"></i>
                        Dashboard
                    </a>
                </li>
                <li class="nav-item">
                    <a href="manage_hospitals.php" class="nav-link">
                        <i class="fas fa-hospital"></i>
                        Manage Hospitals
                    </a>
                </li>
                <li class="nav-item">
                    <a href="manage_donors.php" class="nav-link">
                        <i class="fas fa-hand-holding-heart"></i>
                        Manage Donors
                    </a>
                </li>
                <li class="nav-item">
                    <a href="manage_recipients.php" class="nav-link">
                        <i class="fas fa-user-plus"></i>
                        Manage Recipients
                    </a>
                </li>
                <li class="nav-item">
                    <a href="analytics.php" class="nav-link">
                        <i class="fas fa-chart-line"></i>
                        Analytics
                    </a>
                </li>
                <li class="nav-item">
                    <a href="notifications.php" class="nav-link">
                        <i class="fas fa-bell"></i>
                        Notifications
                    </a>
                </li>
                <li class="nav-item">
                    <a href="settings.php" class="nav-link">
                        <i class="fas fa-cog"></i>
                        Settings
                    </a>
                </li>
                <li class="nav-item">
                    <a href="../admin_dashboard_logout.php" class="nav-link">
                        <i class="fas fa-sign-out-alt"></i>
                        Logout
                    </a>
                </li>
            </ul>
        </div>

        <!-- Main Content -->
        <div class="main-content">
            <div class="content-header">
                <h1>Dashboard Overview</h1>
            </div>

            <!-- Stats Grid -->
            <div class="stats-grid">
                <div class="stat-card">
                    <i class="fas fa-hospital"></i>
                    <h3>Total Hospitals</h3>
                    <div class="number" data-stat="total_hospitals"><?php echo $stats['total_hospitals']; ?></div>
                </div>
                <div class="stat-card">
                    <i class="fas fa-user-plus"></i>
                    <h3>Total Donors</h3>
                    <div class="number" data-stat="total_donors"><?php echo $stats['total_donors']; ?></div>
                </div>
                <div class="stat-card">
                    <i class="fas fa-users"></i>
                    <h3>Total Recipients</h3>
                    <div class="number" data-stat="total_recipients"><?php echo $stats['total_recipients']; ?></div>
                </div>
                <div class="stat-card pending">
                    <i class="fas fa-clock"></i>
                    <h3>Pending Hospitals</h3>
                    <div class="number" data-stat="pending_hospitals"><?php echo $stats['pending_hospitals']; ?></div>
                </div>
                <div class="stat-card success">
                    <i class="fas fa-handshake"></i>
                    <h3>Successful Matches</h3>
                    <div class="number" data-stat="successful_matches"><?php echo $stats['successful_matches']; ?></div>
                </div>
                <div class="stat-card pending">
                    <i class="fas fa-sync"></i>
                    <h3>Pending Matches</h3>
                    <div class="number" data-stat="pending_matches"><?php echo $stats['pending_matches']; ?></div>
                </div>
                <div class="stat-card urgent">
                    <i class="fas fa-exclamation-triangle"></i>
                    <h3>Urgent Cases</h3>
                    <div class="number" data-stat="urgent_matches"><?php echo $stats['urgent_matches']; ?></div>
                </div>
            </div>

            <!-- Main Dashboard Layout -->
            <div class="dashboard-layout">
                <!-- Tables Section -->
                <div class="tables-section">
                    <!-- Pending Hospitals Table -->
                    <div class="table-container">
                        <h2>Pending Hospital Approvals (<span id="pending-hospitals-count"><?php echo count($pendingHospitals); ?></span>)</h2>
                        <div class="table-responsive">
                            <table class="table" id="pending-hospitals-table">
                                <thead>
                                    <tr>
                                        <th>Hospital Name</th>
                                        <th>Email</th>
                                        <th>Status</th>
                                        <th>Actions</th>
                                        <th>Details</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($pendingHospitals as $hospital): ?>
                                    <tr>
                                        <td><?php echo htmlspecialchars($hospital['hospital_name']); ?></td>
                                        <td><?php echo htmlspecialchars($hospital['email']); ?></td>
                                        <td><?php echo htmlspecialchars($hospital['status']); ?></td>
                                        <td>
                                            <button class="approve-btn" onclick="updateHospitalStatus(<?php echo $hospital['hospital_id']; ?>, 'approved')">Approve</button>
                                            <button class="reject-btn" onclick="updateHospitalStatus(<?php echo $hospital['hospital_id']; ?>, 'rejected')">Reject</button>
                                        </td>
                                        <td>
                                            <a href="view_all_details.php?type=hospital&id=<?php echo $hospital['hospital_id']; ?>" 
                                               class="details-btn">View Details</a>
                                        </td>
                                    </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>

                    <!-- Pending Donors Table -->
                    <div class="table-container">
                        <h2>Pending Donor Approvals (<span id="pending-donors-count"><?php echo count($pendingDonors); ?></span>)</h2>
                        <div class="table-responsive">
                            <table class="table" id="pending-donors-table">
                                <thead>
                                    <tr>
                                        <th>Name</th>
                                        <th>Email</th>
                                        <th>Blood Type</th>
                                        <th>Status</th>
                                        <th>Actions</th>
                                        <th>Details</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($pendingDonors as $donor): ?>
                                    <tr>
                                        <td><?php echo htmlspecialchars($donor['name']); ?></td>
                                        <td><?php echo htmlspecialchars($donor['email']); ?></td>
                                        <td><?php echo htmlspecialchars($donor['blood_type']); ?></td>
                                        <td><?php echo htmlspecialchars($donor['status']); ?></td>
                                        <td>
                                            <button class="approve-btn" onclick="updateDonorStatus(<?php echo $donor['donor_id']; ?>, 'approved')">Approve</button>
                                            <button class="reject-btn" onclick="updateDonorStatus(<?php echo $donor['donor_id']; ?>, 'rejected')">Reject</button>
                                        </td>
                                        <td>
                                            <a href="view_all_details.php?type=donor&id=<?php echo $donor['donor_id']; ?>" 
                                               class="details-btn">View Details</a>
                                        </td>
                                    </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>

                    <!-- Pending Recipients Table -->
                    <div class="table-container">
                        <h2>Pending Recipient Approvals (<span id="pending-recipients-count"><?php echo count($pendingRecipients); ?></span>)</h2>
                        <div class="table-responsive">
                            <table class="table" id="pending-recipients-table">
                                <thead>
                                    <tr>
                                        <th>Name</th>
                                        <th>Email</th>
                                        <th>Blood Type</th>
                                        <th>Status</th>
                                        <th>Actions</th>
                                        <th>Details</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($pendingRecipients as $recipient): ?>
                                    <tr>
                                        <td><?php echo htmlspecialchars($recipient['name']); ?></td>
                                        <td><?php echo htmlspecialchars($recipient['email']); ?></td>
                                        <td><?php echo htmlspecialchars($recipient['blood_type']); ?></td>
                                        <td><?php echo htmlspecialchars($recipient['request_status']); ?></td>
                                        <td>
                                            <button class="approve-btn" onclick="updateRecipientStatus(<?php echo $recipient['id']; ?>, 'approved')">Approve</button>
                                            <button class="reject-btn" onclick="updateRecipientStatus(<?php echo $recipient['id']; ?>, 'rejected')">Reject</button>
                                        </td>
                                        <td>
                                            <a href="view_all_details.php?type=recipient&id=<?php echo $recipient['id']; ?>" 
                                               class="details-btn">View Details</a>
                                        </td>
                                    </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                    <!-- Recent Matches Table -->
                    <div class="table-container">
                        <h2>Recent Organ Match Activities</h2>
                        <div class="table-responsive">
                            <table class="table" id="recent-matches-table">
                                <thead>
                                    <tr>
                                        <th>Date</th>
                                        <th>Donor</th>
                                        <th>Recipient</th>
                                        <th>Hospital</th>
                                        <th>Organ Type</th>
                                        <th>Status</th>
                                        <th>Urgency</th>
                                        <th>Action</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php 
                                    require_once '../../backend/php/organ_matches.php';
                                    $recent_matches = getOrganMatches($conn, ['limit' => 5]);
                                    foreach ($recent_matches as $match): 
                                    ?>
                                    <tr>
                                        <td><?php echo date('M d, Y', strtotime($match['match_date'])); ?></td>
                                        <td><?php echo htmlspecialchars($match['donor_name']); ?></td>
                                        <td><?php echo htmlspecialchars($match['recipient_name']); ?></td>
                                        <td><?php echo htmlspecialchars($match['hospital_name']); ?></td>
                                        <td><?php echo htmlspecialchars($match['organ_type']); ?></td>
                                        <td>
                                            <span class="status-badge status-<?php echo strtolower($match['status']); ?>">
                                                <?php echo $match['status']; ?>
                                            </span>
                                        </td>
                                        <td>
                                            <span class="urgency-badge urgency-<?php echo strtolower($match['urgency_level']); ?>">
                                                <?php echo $match['urgency_level']; ?>
                                            </span>
                                        </td>
                                        <td>
                                            <button class="btn-action btn-view" onclick="viewMatch(<?php echo $match['match_id']; ?>)">
                                                <i class="fas fa-eye"></i>
                                            </button>
                                        </td>
                                    </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Notification Bell -->
    <div class="notification-bell" onclick="toggleNotifications()">
        <i class="fas fa-bell"></i>
        <span class="badge" id="notification-count">0</span>
    </div>

    <!-- Notification Popup -->
    <div class="notification-popup" id="notification-popup">
        <div class="p-3">
            <h4>Notifications</h4>
            <div id="notifications-container">
                <?php foreach ($notifications as $notification): ?>
                    <div class="notification-item">
                        <span class="notification-time"><?php echo isset($notification['formatted_time']) ? $notification['formatted_time'] : date('F d, Y \a\t h:i A', strtotime($notification['created_at'])); ?></span>
                        <p><?php echo $notification['message']; ?></p>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script src="../../assets/js/admin-dashboard.js"></script>
    <script>
        // Notification Bell Functions
        function toggleNotifications() {
            const popup = document.getElementById('notification-popup');
            popup.classList.toggle('show');
        }

        // Close notification popup when clicking outside
        document.addEventListener('click', function(event) {
            const bell = document.querySelector('.notification-bell');
            const popup = document.getElementById('notification-popup');
            
            if (!bell.contains(event.target) && !popup.contains(event.target)) {
                popup.classList.remove('show');
            }
        });

        // Update notification count
        function updateNotificationCount() {
            const count = document.querySelectorAll('.notification-item').length;
            document.getElementById('notification-count').textContent = count;
        }

        // Call this when page loads
        document.addEventListener('DOMContentLoaded', function() {
            updateNotificationCount();
        });

        // Update notifications with new data
        function updateNotifications(notifications) {
            const container = document.getElementById('notifications-container');
            container.innerHTML = notifications.map(notification => `
                <div class="notification-item">
                    <span class="notification-time">${notification.formatted_time}</span>
                    <p>${notification.message}</p>
                </div>
            `).join('');
            updateNotificationCount();
        }
    </script>
</body>
</html>
