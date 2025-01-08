<?php
session_start();
require_once '../../config/db_connect.php';

// Check if admin is logged in
if (!isset($_SESSION['admin_logged_in']) || !$_SESSION['admin_logged_in']) {
    header("Location: ../admin_login.php");
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Organ Matches - Admin Dashboard</title>
    <link rel="stylesheet" href="../../assets/css/admin-dashboard.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>
<body>
    <div class="dashboard-container">
        <?php include '../../includes/admin_sidebar.php'; ?>
        
        <main class="main-content">
            <div class="dashboard-header">
                <h1>Organ Matches</h1>
            </div>
        </main>
    </div>
</body>
</html>
