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
    <link rel="stylesheet" href="../../assets/css/admin.css">
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
    <nav class="navbar">
        <div class="nav-container">
            <div class="logo">
                <span class="logo-text"><span class="logo-gradient">LifeLink</span> Admin</span>
            </div>
            <div class="nav-links">
                <a href="admin_dashboard.php" class="active">Hospital</a>
                <a href="manage_donors.php">Donor</a>
                <a href="manage_recipients.php">Recipients</a>
                <a href="analytics.php">Analytics</a>
                <a href="notifications.php">Notifications</a>
                <a href="../logout.php" class="btn-logout">Logout</a>
            </div>
        </div>
    </nav>

    <div class="dashboard-container">
        <div class="welcome-section">
            <h1>Welcome, Admin!</h1>
            <p>Manage and monitor the organ donation system</p>
        </div>

        <!-- Dashboard Grid -->
        <div class="dashboard-grid">
            <!-- Pending Hospitals Card -->
            <div class="card">
                <h2>Pending Hospital Approvals</h2>
                <div class="pending-list">
                    <?php if (!empty($pendingHospitals)): ?>
                        <?php foreach ($pendingHospitals as $hospital): ?>
                            <div class="pending-item">
                                <div class="hospital-info">
                                    <div class="hospital-name">
                                        <?php echo htmlspecialchars($hospital['name'] ?? $hospital['hospital_name'] ?? 'Unknown Hospital'); ?>
                                    </div>
                                    <div class="hospital-details">
                                        Email: <?php echo htmlspecialchars($hospital['email'] ?? 'N/A'); ?><br>
                                        Address: <?php echo htmlspecialchars($hospital['address'] ?? 'N/A'); ?>
                                    </div>
                                </div>
                                <div class="action-buttons">
                                    <?php if (isset($hospital['hospital_id'])): ?>
                                        <button onclick="approveHospital(<?php echo $hospital['hospital_id']; ?>)" 
                                                class="btn btn-success">
                                            <i class="fas fa-check"></i> Approve
                                        </button>
                                        <button onclick="rejectHospital(<?php echo $hospital['hospital_id']; ?>)" 
                                                class="btn btn-danger">
                                            <i class="fas fa-times"></i> Reject
                                        </button>
                                    <?php endif; ?>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <p>No pending hospital approvals.</p>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Urgent Recipients Card -->
            <div class="card">
                <h2>Urgent Recipients</h2>
                <div class="urgent-list">
                    <?php if (!empty($urgentRecipients)): ?>
                        <?php foreach ($urgentRecipients as $recipient): ?>
                            <div class="urgent-item">
                                <h4><?php echo htmlspecialchars($recipient['name'] ?? 'Unknown Patient'); ?></h4>
                                <p>Blood Type: <?php echo htmlspecialchars($recipient['blood_type'] ?? 'N/A'); ?></p>
                                <p>Organ Needed: <?php echo htmlspecialchars($recipient['needed_organ'] ?? 'N/A'); ?></p>
                            </div>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <p>No urgent recipients at this time.</p>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <!-- Hospital Management Section -->
        <div class="dashboard-section">
            <div class="section-header">
                <h2><i class="fas fa-hospital"></i> Hospital Management</h2>
                <a href="manage_hospitals.php" class="btn btn-primary">View All</a>
            </div>
            
            <div class="table-responsive">
                <table class="table">
                    <thead>
                        <tr>
                            <th>Hospital Name</th>
                            <th>Email</th>
                            <th>Phone</th>
                            <th>Registration Date</th>
                            <th>License</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($pendingHospitals as $hospital): ?>
                            <tr class="<?php echo isset($hospital['is_new']) && $hospital['is_new'] ? 'new-registration' : ''; ?>">
                                <td>
                                    <?php echo htmlspecialchars($hospital['name'] ?? $hospital['hospital_name'] ?? 'Unknown Hospital'); ?>
                                    <?php if (isset($hospital['is_new']) && $hospital['is_new']): ?>
                                        <span class="new-badge">New</span>
                                    <?php endif; ?>
                                </td>
                                <td><?php echo htmlspecialchars($hospital['email'] ?? 'N/A'); ?></td>
                                <td><?php echo htmlspecialchars($hospital['phone'] ?? 'N/A'); ?></td>
                                <td>
                                    <?php 
                                    echo isset($hospital['created_at']) 
                                        ? date('Y-m-d H:i', strtotime($hospital['created_at'])) 
                                        : 'N/A'; 
                                    ?>
                                </td>
                                <td>
                                    <?php if (isset($hospital['hospital_id'])): ?>
                                        <a href="../view_license.php?hospital_id=<?php echo $hospital['hospital_id']; ?>" 
                                           target="_blank" 
                                           class="btn btn-sm btn-primary">
                                            <i class="fas fa-file-medical"></i> View License
                                        </a>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <?php if (isset($hospital['hospital_id'])): ?>
                                        <button onclick="approveHospital(<?php echo $hospital['hospital_id']; ?>)" 
                                                class="btn btn-sm btn-success">
                                            <i class="fas fa-check"></i> Approve
                                        </button>
                                        <button onclick="rejectHospital(<?php echo $hospital['hospital_id']; ?>)" 
                                                class="btn btn-sm btn-danger">
                                            <i class="fas fa-times"></i> Reject
                                        </button>
                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script src="../../assets/js/admin.js"></script>
</body>
</html>
