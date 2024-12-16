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

        /* Dashboard Grid Layout */
        .dashboard-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 20px;
            padding: 20px;
        }

        .card {
            background: white;
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
            padding: 20px;
            height: fit-content;
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

            <!-- Stats Cards -->
            <div class="dashboard-cards">
                <div class="card">
                    <i class="fas fa-hospital card-icon"></i>
                    <h3 class="card-title">Pending Hospitals</h3>
                    <div class="card-value" data-stat="pending_hospitals"><?php echo isset($stats['pending_hospitals']) ? $stats['pending_hospitals'] : 0; ?></div>
                </div>
                <div class="card">
                    <i class="fas fa-user-plus card-icon"></i>
                    <h3 class="card-title">Total Donors</h3>
                    <div class="card-value" data-stat="total_donors"><?php echo isset($stats['total_donors']) ? $stats['total_donors'] : 0; ?></div>
                </div>
                <div class="card">
                    <i class="fas fa-users card-icon"></i>
                    <h3 class="card-title">Total Recipients</h3>
                    <div class="card-value" data-stat="total_recipients"><?php echo isset($stats['total_recipients']) ? $stats['total_recipients'] : 0; ?></div>
                </div>
                <div class="card">
                    <i class="fas fa-handshake card-icon"></i>
                    <h3 class="card-title">Successful Matches</h3>
                    <div class="card-value" data-stat="successful_matches"><?php echo isset($stats['successful_matches']) ? $stats['successful_matches'] : 0; ?></div>
                </div>
                <div class="card">
                    <i class="fas fa-clock card-icon"></i>
                    <h3 class="card-title">Pending Matches</h3>
                    <div class="card-value" data-stat="pending_matches"><?php echo isset($stats['pending_matches']) ? $stats['pending_matches'] : 0; ?></div>
                </div>
                <div class="card">
                    <i class="fas fa-exclamation-triangle card-icon" style="color: #ff9800;"></i>
                    <h3 class="card-title">Urgent Cases</h3>
                    <div class="card-value" data-stat="urgent_recipients"><?php echo isset($stats['urgent_recipients']) ? $stats['urgent_recipients'] : 0; ?></div>
                </div>
            </div>

            <!-- Pending Hospitals Section -->
            <div class="table-container">
                <h2>Pending Hospital Approvals (<span id="pending-hospitals-count"><?php echo count($pendingHospitals); ?></span>)</h2>
                <table class="dashboard-table" id="pending-hospitals-table">
                    <thead>
                        <tr>
                            <th>Hospital Name</th>
                            <th>Email</th>
                            <th>Registration Date</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($pendingHospitals as $hospital): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($hospital['hospital_name']); ?></td>
                            <td><?php echo htmlspecialchars($hospital['email']); ?></td>
                            <td><?php echo date('M d, Y', strtotime($hospital['registration_date'])); ?></td>
                            <td>
                                <button class="btn-action btn-approve" onclick="updateHospitalStatus(<?php echo $hospital['hospital_id']; ?>, 'approved')">
                                    <i class="fas fa-check"></i> Approve
                                </button>
                                <button class="btn-action btn-reject" onclick="updateHospitalStatus(<?php echo $hospital['hospital_id']; ?>, 'rejected')">
                                    <i class="fas fa-times"></i> Reject
                                </button>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>

            <!-- Pending Donors Section -->
            <div class="table-container">
                <h2>Pending Donor Approvals (<span id="pending-donors-count"><?php echo count($pendingDonors); ?></span>)</h2>
                <table class="dashboard-table" id="pending-donors-table">
                    <thead>
                        <tr>
                            <th>Name</th>
                            <th>Email</th>
                            <th>Blood Type</th>
                            <th>Organ Type</th>
                            <th>Registration Date</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($pendingDonors as $donor): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($donor['name']); ?></td>
                            <td><?php echo htmlspecialchars($donor['email']); ?></td>
                            <td><?php echo htmlspecialchars($donor['blood_type']); ?></td>
                            <td><?php echo htmlspecialchars($donor['organ_type']); ?></td>
                            <td><?php echo $donor['formatted_date']; ?></td>
                            <td>
                                <button class="btn-action btn-approve" onclick="updateDonorStatus(<?php echo $donor['id']; ?>, 'approved')">
                                    <i class="fas fa-check"></i> Approve
                                </button>
                                <button class="btn-action btn-reject" onclick="updateDonorStatus(<?php echo $donor['id']; ?>, 'rejected')">
                                    <i class="fas fa-times"></i> Reject
                                </button>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>

            <!-- Pending Recipients Section -->
            <div class="table-container">
                <h2>Pending Recipient Approvals (<span id="pending-recipients-count"><?php echo count($pendingRecipients); ?></span>)</h2>
                <table class="dashboard-table" id="pending-recipients-table">
                    <thead>
                        <tr>
                            <th>Name</th>
                            <th>Email</th>
                            <th>Blood Type</th>
                            <th>Needed Organ</th>
                            <th>Urgency</th>
                            <th>Registration Date</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($pendingRecipients as $recipient): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($recipient['name']); ?></td>
                            <td><?php echo htmlspecialchars($recipient['email']); ?></td>
                            <td><?php echo htmlspecialchars($recipient['blood_type']); ?></td>
                            <td><?php echo htmlspecialchars($recipient['organ_needed']); ?></td>
                            <td>
                                <span class="urgency-badge urgency-<?php echo strtolower($recipient['urgency_level']); ?>">
                                    <?php echo $recipient['urgency_level']; ?>
                                </span>
                            </td>
                            <td><?php echo $recipient['formatted_date']; ?></td>
                            <td>
                                <button class="btn-action btn-approve" onclick="updateRecipientStatus(<?php echo $recipient['id']; ?>, 'approved')">
                                    <i class="fas fa-check"></i> Approve
                                </button>
                                <button class="btn-action btn-reject" onclick="updateRecipientStatus(<?php echo $recipient['id']; ?>, 'rejected')">
                                    <i class="fas fa-times"></i> Reject
                                </button>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>

            <!-- Notifications Section -->
            <div class="notifications-section">
                <h2>Recent Notifications</h2>
                <div id="notifications-container">
                    <?php foreach ($notifications as $notification): ?>
                    <div class="notification-item">
                        <div class="notification-content">
                            <span class="notification-type"><?php echo htmlspecialchars($notification['type']); ?></span>
                            <p><?php echo htmlspecialchars($notification['message']); ?></p>
                            <small><?php echo date('M d, Y H:i', strtotime($notification['created_at'])); ?></small>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
            </div>

            <!-- Recent Activities Table -->
            <div class="table-container">
                <h2>Recent Organ Match Activities</h2>
                <table class="dashboard-table">
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

    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script src="../../assets/js/admin-dashboard.js"></script>
</body>
</html>
