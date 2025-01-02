<?php
// Check if hospital is logged in
if (!isset($_SESSION['hospital_logged_in']) || !$_SESSION['hospital_logged_in']) {
    header("Location: ../pages/hospital_login.php");
    exit();
}
?>
<aside class="sidebar">
    <div class="sidebar-header">
        <h2 class="logo-text">LifeLink</h2>
        <div class="sub-text">HospitalHub</div>
    </div>
    <nav class="sidebar-nav">
        <ul>
            <li>
                <a href="hospital_dashboard.php">
                    <i class="fas fa-home"></i>
                    <span>Dashboard</span>
                </a>
            </li>
            <li>
                <a href="hospitals_handles_donors_status.php">
                    <i class="fas fa-users"></i>
                    <span>Manage Donors</span>
                </a>
            </li>
            <li>
                <a href="hospitals_handles_recipients_status.php">
                    <i class="fas fa-procedures"></i>
                    <span>Manage Recipients</span>
                </a>
            </li>
            <li>
                <a href="make_matches.php">
                    <i class="fas fa-link"></i>
                    <span>Make Matches</span>
                </a>
            </li>
            <li>
                <a href="check_requests.php">
                    <i class="fas fa-bell"></i>
                    <span>Check Requests</span>
                </a>
            </li>
            <li>
                <a href="../logout.php">
                    <i class="fas fa-sign-out-alt"></i>
                    <span>Logout</span>
                </a>
            </li>
        </ul>
    </nav>
</aside>
