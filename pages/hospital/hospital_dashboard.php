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
    <title>Hospital Dashboard</title>
    <!-- Reset default styles -->
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
    </style>
    <link rel="stylesheet" href="../../assets/css/hospital-dashboard.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>
<body>
    <div class="dashboard-container">
        <!-- Sidebar -->
        <aside class="sidebar">
            <div class="sidebar-header">
                <h2 class="logo-text">LifeLink</h2>
                <div class="sub-text">HospitalHub</div>
            </div>
            <nav class="sidebar-nav">
                <ul>
                    <li class="active">
                        <a href="#">
                            <i class="fas fa-home"></i>
                            <span>Dashboard</span>
                        </a>
                    </li>
                    <li>
                        <a href="#">
                            <i class="fas fa-user-plus"></i>
                            <span>Manage Donors</span>
                        </a>
                    </li>
                    <li>
                        <a href="#">
                            <i class="fas fa-hospital"></i>
                            <span>Manage Hospitals</span>
                        </a>
                    </li>
                    <li>
                        <a href="#">
                            <i class="fas fa-search"></i>
                            <span>Search Organ</span>
                        </a>
                    </li>
                    <li>
                        <a href="#">
                            <i class="fas fa-share"></i>
                            <span>Forward Matches to Admin</span>
                        </a>
                    </li>
                    <li>
                        <a href="#">
                            <i class="fas fa-chart-bar"></i>
                            <span>Analytics</span>
                        </a>
                    </li>
                    <li>
                        <a href="#">
                            <i class="fas fa-bell"></i>
                            <span>Notifications</span>
                        </a>
                    </li>
                    <li>
                        <a href="../../pages/hospital_login.php">
                            <i class="fas fa-sign-out-alt"></i>
                            <span>Logout</span>
                        </a>
                    </li>
                </ul>
            </nav>
        </aside>

        <!-- Main Content -->
        <main class="main-content">
            <header class="dashboard-header">
                <h1>Welcome, <?php echo htmlspecialchars($hospital_name); ?></h1>
            </header>
            
            <div class="dashboard-overview">
                <div class="overview-card">
                    <h3>Total Donors</h3>
                    <p>Manage and track your registered organ donors</p>
                </div>
                <div class="overview-card">
                    <h3>Pending Matches</h3>
                    <p>View and process organ match requests</p>
                </div>
                <div class="overview-card">
                    <h3>Recent Activities</h3>
                    <p>Track your recent organ donation activities</p>
                </div>
                <div class="overview-card">
                    <h3>Analytics Overview</h3>
                    <p>Monitor your hospital's donation statistics</p>
                </div>
            </div>
        </main>
    </div>
</body>
</html>
