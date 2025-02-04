<?php
// Check if recipient is logged in
if (!isset($_SESSION['is_recipient']) || !$_SESSION['is_recipient']) {
    header("Location: ../recipient_login.php");
    exit();
}

// Get current page name for active state
$current_page = basename($_SERVER['PHP_SELF']);
?>
<aside class="sidebar">
    <div class="sidebar-header">
        <div class="logo-container">
            <i class="fas fa-heartbeat heart-icon"></i>
            <h2 class="logo-text">LifeLink</h2>
        </div>
    </div>
    <nav class="sidebar-nav">
        <ul>
            <li>
                <a href="../recipient/recipient_dashboard.php" <?php echo $current_page == 'recipient_dashboard.php' ? 'class="active"' : ''; ?>>
                    <i class="fas fa-home"></i>
                    <span>Dashboard</span>
                </a>
            </li>
            <li>
                <a href="../recipient/recipient_personal_details.php" <?php echo $current_page == 'recipient_personal_details.php' ? 'class="active"' : ''; ?>>
                    <i class="fas fa-user"></i>
                    <span>Profile</span>
                </a>
            </li>
            <li>
                <a href="../recipient/search_hospitals_for_recipient.php" <?php echo $current_page == 'search_hospitals_for_recipient.php' ? 'class="active"' : ''; ?>>
                    <i class="fas fa-hospital-alt"></i>
                    <span>Search Hospitals</span>
                </a>
            </li>
            <li>
                <a href="../recipient/my_requests_for_recipients.php" <?php echo $current_page == 'my_requests_for_recipients.php' ? 'class="active"' : ''; ?>>
                    <i class="fas fa-clipboard-list"></i>
                    <span>My Requests</span>
                </a>
            </li>
            <li>
                <a href="../recipient/recipients_notifications.php" <?php echo $current_page == 'recipients_notifications.php' ? 'class="active"' : ''; ?>>
                    <i class="fas fa-bell"></i>
                    <span>Notifications</span>
                    <?php if(isset($unread_count) && $unread_count > 0): ?>
                        <span class="notification-badge"><?php echo $unread_count; ?></span>
                    <?php endif; ?>
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

<style>
:root {
    --primary-green: #28a745;
    --primary-blue: #4a90e2;
    --hover-gradient: linear-gradient(135deg, var(--primary-green) 0%, var(--primary-blue) 100%);
    --hover-color: rgba(255, 255, 255, 0.1);
    --active-color: rgba(255, 255, 255, 0.2);
    --sidebar-width: 260px;
}

.sidebar {
    width: var(--sidebar-width);
    min-width: var(--sidebar-width);
    height: 100vh;
    background: white;
    color: #333;
    padding: 1.5rem 1rem;
    position: fixed;
    left: 0;
    top: 0;
    z-index: 1000;
    box-shadow: 4px 0 10px rgba(0, 0, 0, 0.1);
    overflow-y: auto;
}

.sidebar-header {
    padding: 1rem;
    text-align: center;
    margin-bottom: 2rem;
    background: linear-gradient(135deg, var(--primary-green) 0%, var(--primary-blue) 100%);
    border-radius: 10px;
    box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
}

.logo-container {
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 10px;
}

.heart-icon {
    font-size: 1.4rem;
    color: white;
    animation: pulse 1.5s ease-in-out infinite;
}

.logo-text {
    font-size: 1.8rem;
    font-weight: 700;
    margin: 0;
    color: white;
}

.sidebar-nav ul {
    list-style: none;
    padding: 0;
    margin: 0;
}

.sidebar-nav li {
    margin-bottom: 0.5rem;
    width: 100%;
}

.sidebar-nav a {
    display: flex;
    align-items: center;
    padding: 1rem;
    color: #333;
    text-decoration: none;
    border-radius: 10px;
    transition: all 0.3s ease;
    background: white;
    border-left: 4px solid transparent;
    width: 100%;
}

.sidebar-nav a:hover {
    background: #f0f9f2;
    color: var(--primary-green);
    transform: translateX(5px);
    box-shadow: 0 2px 5px rgba(0, 0, 0, 0.05);
    border-left: 4px solid var(--primary-green);
}

.sidebar-nav a.active {
    background: #f0f9f2;
    color: var(--primary-green);
    font-weight: 500;
    box-shadow: 0 2px 5px rgba(0, 0, 0, 0.05);
    border-left: 4px solid var(--primary-green);
}

.sidebar-nav i {
    width: 24px;
    margin-right: 10px;
    text-align: center;
    font-size: 1.1rem;
    color: #666;
    transition: color 0.3s ease;
}

.sidebar-nav a:hover i {
    color: var(--primary-green);
}

.sidebar-nav a.active i {
    color: var(--primary-green);
}

.notification-badge {
    background: #ff4444;
    color: white;
    border-radius: 50%;
    padding: 0.2rem 0.5rem;
    font-size: 0.8rem;
    margin-left: auto;
}

@keyframes pulse {
    0% {
        transform: scale(1);
    }
    50% {
        transform: scale(1.1);
    }
    100% {
        transform: scale(1);
    }
}
</style>
