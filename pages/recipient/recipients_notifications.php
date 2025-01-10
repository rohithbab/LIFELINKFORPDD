<?php
session_start();
require_once '../../config/db_connect.php';

// Check if recipient is logged in
if (!isset($_SESSION['is_recipient']) || !$_SESSION['is_recipient']) {
    header("Location: ../recipient_login.php");
    exit();
}

$recipient_id = $_SESSION['recipient_id'];
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Notifications - LifeLink</title>
    <link rel="stylesheet" href="../../assets/css/recipient-dashboard.css">
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
                        <a href="recipient_dashboard.php">
                            <i class="fas fa-home"></i>
                            <span>Dashboard</span>
                        </a>
                    </li>
                    <li>
                        <a href="recipient_personal_details.php">
                            <i class="fas fa-user"></i>
                            <span>Profile</span>
                        </a>
                    </li>
                    <li>
                        <a href="search_hospitals_for_recipients.php">
                            <i class="fas fa-search"></i>
                            <span>Search Hospitals</span>
                        </a>
                    </li>
                    <li>
                        <a href="my_requests_for_recipients.php">
                            <i class="fas fa-list"></i>
                            <span>My Requests</span>
                        </a>
                    </li>
                    <li>
                        <a href="recipients_notifications.php" class="active">
                            <i class="fas fa-bell"></i>
                            <span>Notifications</span>
                        </a>
                    </li>
                    <li>
                        <a href="../recipient_login.php">
                            <i class="fas fa-sign-out-alt"></i>
                            <span>Logout</span>
                        </a>
                    </li>
                </ul>
            </nav>
        </aside>

        <!-- Main Content -->
        <main class="main-content">
            <!-- Content will be added later -->
        </main>
    </div>
</body>
</html>
