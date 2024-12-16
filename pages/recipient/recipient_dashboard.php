<?php
session_start();

// Check if user is logged in
if (!isset($_SESSION['recipient_id'])) {
    header("Location: ../recipient_login.php");
    exit();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Recipient Dashboard - LifeLink</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link rel="stylesheet" href="../../assets/css/styles.css">
    <style>
        .dashboard-container {
            max-width: 1200px;
            margin: 2rem auto;
            padding: 2rem;
            background: white;
            border-radius: 10px;
            box-shadow: 0 0 20px rgba(0, 0, 0, 0.1);
        }
        .welcome-section {
            text-align: center;
            margin-bottom: 2rem;
            padding: 2rem;
            background: linear-gradient(45deg, var(--primary-blue), var(--primary-green));
            color: white;
            border-radius: 10px;
        }
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 1.5rem;
            margin-bottom: 2rem;
        }
        .stat-card {
            padding: 1.5rem;
            background: #f8f9fa;
            border-radius: 8px;
            text-align: center;
        }
        .stat-card i {
            font-size: 2rem;
            color: var(--primary-blue);
            margin-bottom: 1rem;
        }
        .logout-btn {
            background: #dc3545;
            color: white;
            padding: 0.5rem 1rem;
            border: none;
            border-radius: 5px;
            text-decoration: none;
            transition: opacity 0.3s;
        }
        .logout-btn:hover {
            opacity: 0.9;
            color: white;
        }
    </style>
</head>
<body>
    <!-- Navigation -->
    <nav class="navbar">
        <div class="nav-container">
            <a href="../../index.php" class="logo">
                <span class="logo-life">LifeLink</span>
            </a>
            <div class="nav-links">
                <a href="../logout.php" class="logout-btn">
                    <i class="fas fa-sign-out-alt"></i> Logout
                </a>
            </div>
        </div>
    </nav>

    <div class="dashboard-container">
        <div class="welcome-section">
            <h1>Welcome, <?php echo htmlspecialchars($_SESSION['recipient_name']); ?>!</h1>
            <p>This is your recipient dashboard where you can manage your organ donation requests and view matches.</p>
        </div>

        <div class="stats-grid">
            <div class="stat-card">
                <i class="fas fa-heart"></i>
                <h3>Donation Status</h3>
                <p>Waiting for Match</p>
            </div>
            <div class="stat-card">
                <i class="fas fa-calendar-check"></i>
                <h3>Registration Date</h3>
                <p>Active Member</p>
            </div>
            <div class="stat-card">
                <i class="fas fa-bell"></i>
                <h3>Notifications</h3>
                <p>No new notifications</p>
            </div>
        </div>

        <div style="text-align: center;">
            <p>More features coming soon!</p>
        </div>
    </div>

    <script>
        // Add any JavaScript functionality here
    </script>
</body>
</html>
