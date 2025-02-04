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
        <i class="fas fa-heartbeat"></i>
        <span>LifeLink</span>
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
    --primary-color: #28a745;
    --secondary-color: #2196F3;
    --accent-color: #20c997;
    --text-primary: #333333;
    --text-secondary: #666666;
    --text-light: #ffffff;
    --background-light: #f5f5f5;
    --sidebar-width: 250px;
}

.sidebar {
    width: var(--sidebar-width);
    height: 100vh;
    position: fixed;
    left: 0;
    top: 0;
    background: var(--text-light);
    box-shadow: 4px 0 10px rgba(0,0,0,0.1);
    overflow-y: auto;
    z-index: 1000;
}

.sidebar-header {
    padding: 25px 20px;
    display: flex;
    align-items: center;
    gap: 15px;
    background: linear-gradient(135deg, var(--primary-color), var(--secondary-color));
    color: var(--text-light);
    height: 80px;
}

.sidebar-header i {
    font-size: 28px;
}

.sidebar-header span {
    font-size: 24px;
    font-weight: 600;
    letter-spacing: 0.5px;
}

.sidebar-nav ul {
    list-style: none;
    padding: 15px 0;
    margin: 0;
}

.sidebar-nav li {
    margin: 5px 15px;
}

.sidebar-nav a {
    display: flex;
    align-items: center;
    padding: 12px 20px;
    color: var(--text-primary);
    text-decoration: none;
    border-radius: 12px;
    transition: all 0.3s ease;
    position: relative;
    overflow: hidden;
}

.sidebar-nav a:before {
    content: '';
    position: absolute;
    left: 0;
    top: 0;
    height: 100%;
    width: 0;
    background: linear-gradient(135deg, rgba(40, 167, 69, 0.1), rgba(33, 150, 243, 0.1));
    transition: width 0.3s ease;
}

.sidebar-nav a:hover:before,
.sidebar-nav a.active:before {
    width: 100%;
}

.sidebar-nav a:hover,
.sidebar-nav a.active {
    color: var(--primary-color);
    transform: translateX(5px);
}

.sidebar-nav i {
    width: 24px;
    margin-right: 12px;
    font-size: 18px;
    position: relative;
    z-index: 1;
}

.sidebar-nav span {
    position: relative;
    z-index: 1;
}

.notification-badge {
    background: var(--accent-color);
    color: white;
    padding: 2px 8px;
    border-radius: 12px;
    font-size: 0.8em;
    margin-left: auto;
    position: relative;
    z-index: 1;
}
</style>
