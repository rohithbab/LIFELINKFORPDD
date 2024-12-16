<?php
session_start();
require_once '../../backend/php/connection.php';

// Check if hospital is logged in
if (!isset($_SESSION['hospital_logged_in']) || !$_SESSION['hospital_logged_in']) {
    header("Location: ../../pages/hospital_login.php");
    exit();
}

$hospital_name = $_SESSION['hospital_name'];
$hospital_email = $_SESSION['hospital_email'];
$odml_id = $_SESSION['odml_id'];
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Hospital Dashboard - LifeLink</title>
    
    <!-- CSS Files -->
    <link rel="stylesheet" href="../../assets/css/styles.css">
    <link rel="stylesheet" href="../../assets/css/hospital-dashboard.css">
    
    <!-- Font Awesome for icons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    
    <!-- Chart.js for analytics -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
</head>
<body>
    <div class="dashboard-container">
        <!-- Sidebar -->
        <aside class="sidebar">
            <div class="sidebar-header">
                <img src="../../assets/images/logo.png" alt="LifeLink Logo" class="logo">
                <h2>LifeLink</h2>
            </div>
            
            <nav class="sidebar-nav">
                <ul>
                    <li class="active">
                        <a href="#" data-section="dashboard">
                            <i class="fas fa-home"></i>
                            <span>Dashboard</span>
                        </a>
                    </li>
                    <li>
                        <a href="#" data-section="donors">
                            <i class="fas fa-user-plus"></i>
                            <span>Donors</span>
                        </a>
                    </li>
                    <li>
                        <a href="#" data-section="recipients">
                            <i class="fas fa-users"></i>
                            <span>Recipients</span>
                        </a>
                    </li>
                    <li>
                        <a href="#" data-section="analytics">
                            <i class="fas fa-chart-bar"></i>
                            <span>Analytics</span>
                        </a>
                    </li>
                    <li>
                        <a href="#" data-section="notifications">
                            <i class="fas fa-bell"></i>
                            <span>Notifications</span>
                            <span class="notification-badge" id="notificationCount">0</span>
                        </a>
                    </li>
                    <li>
                        <a href="#" data-section="settings">
                            <i class="fas fa-cog"></i>
                            <span>Settings</span>
                        </a>
                    </li>
                </ul>
            </nav>
        </aside>

        <!-- Main Content -->
        <main class="main-content">
            <!-- Header -->
            <header class="dashboard-header">
                <div class="header-left">
                    <button id="sidebar-toggle" class="sidebar-toggle">
                        <i class="fas fa-bars"></i>
                    </button>
                    <h1><?php echo htmlspecialchars($hospital_name); ?></h1>
                </div>
                <div class="header-right">
                    <div class="notifications-dropdown">
                        <button class="notifications-btn">
                            <i class="fas fa-bell"></i>
                            <span class="notification-badge">0</span>
                        </button>
                        <div class="notifications-content">
                            <div class="notifications-header">
                                <h3>Notifications</h3>
                                <button class="mark-all-read">Mark all as read</button>
                            </div>
                            <div class="notifications-list">
                                <!-- Notifications will be dynamically loaded here -->
                            </div>
                        </div>
                    </div>
                    <div class="user-dropdown">
                        <button class="user-btn">
                            <i class="fas fa-user-circle"></i>
                            <span>Profile</span>
                        </button>
                        <div class="user-dropdown-content">
                            <a href="#" class="profile-link">
                                <i class="fas fa-user"></i>
                                Profile
                            </a>
                            <a href="../../backend/php/logout.php" class="logout-link">
                                <i class="fas fa-sign-out-alt"></i>
                                Logout
                            </a>
                        </div>
                    </div>
                </div>
            </header>

            <!-- Dashboard Content -->
            <div class="dashboard-content">
                <!-- Dashboard Overview Section -->
                <section id="dashboard" class="content-section active">
                    <div class="metrics-grid">
                        <div class="metric-card">
                            <i class="fas fa-user-plus"></i>
                            <div class="metric-info">
                                <h3>Total Donors</h3>
                                <p class="metric-number" id="totalDonors">0</p>
                            </div>
                        </div>
                        <div class="metric-card">
                            <i class="fas fa-users"></i>
                            <div class="metric-info">
                                <h3>Total Recipients</h3>
                                <p class="metric-number" id="totalRecipients">0</p>
                            </div>
                        </div>
                        <div class="metric-card">
                            <i class="fas fa-clock"></i>
                            <div class="metric-info">
                                <h3>Pending Requests</h3>
                                <p class="metric-number" id="pendingRequests">0</p>
                            </div>
                        </div>
                        <div class="metric-card">
                            <i class="fas fa-check-circle"></i>
                            <div class="metric-info">
                                <h3>Approved Donations</h3>
                                <p class="metric-number" id="approvedDonations">0</p>
                            </div>
                        </div>
                    </div>

                    <div class="dashboard-grid">
                        <!-- Recent Activities -->
                        <div class="card recent-activities">
                            <h2>Recent Activities</h2>
                            <div class="activities-list" id="recentActivities">
                                <!-- Activities will be loaded dynamically -->
                            </div>
                        </div>

                        <!-- Urgent Cases -->
                        <div class="card urgent-cases">
                            <h2>Urgent Cases</h2>
                            <div class="urgent-list" id="urgentCases">
                                <!-- Urgent cases will be loaded dynamically -->
                            </div>
                        </div>
                    </div>
                </section>

                <!-- Other sections will be loaded dynamically -->
                <section id="donors" class="content-section"></section>
                <section id="recipients" class="content-section"></section>
                <section id="analytics" class="content-section"></section>
                <section id="notifications" class="content-section"></section>
                <section id="settings" class="content-section"></section>
            </div>
        </main>
    </div>

    <!-- JavaScript Files -->
    <script src="../../assets/js/hospital-dashboard.js"></script>
    <script src="../../assets/js/donors.js"></script>
    <script src="../../assets/js/recipients.js"></script>
</body>
</html>
