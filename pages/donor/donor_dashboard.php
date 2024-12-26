<?php
session_start();
require_once '../../config/db_connect.php';

// Check if user is logged in as donor
if (!isset($_SESSION['is_donor']) || !$_SESSION['is_donor']) {
    header("Location: ../donor_login.php");
    exit();
}

// Get donor info from session
$donor_id = $_SESSION['donor_id'];
$donor_name = $_SESSION['donor_name'];
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Donor Dashboard - LifeLink</title>
    <link rel="stylesheet" href="../../assets/css/donor-dashboard.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
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
                        <a href="donor_dashboard.php" id="dashboardLink">
                            <i class="fas fa-home"></i>
                            <span>Dashboard</span>
                        </a>
                    </li>
                    <li>
                        <a href="donor_profile.php">
                            <i class="fas fa-user"></i>
                            <span>Profile</span>
                        </a>
                    </li>
                    <li>
                        <a href="#" id="searchHospitalLink">
                            <i class="fas fa-hospital"></i>
                            <span>Search Hospital</span>
                        </a>
                    </li>
                    <li>
                        <a href="#" id="myRequestsLink">
                            <i class="fas fa-clipboard-list"></i>
                            <span>My Requests</span>
                        </a>
                    </li>
                    <li>
                        <a href="#" id="notificationsLink">
                            <i class="fas fa-bell"></i>
                            <span>Notifications</span>
                            <span class="notification-badge" id="notificationCount">0</span>
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

        <!-- Main Content -->
        <main class="main-content">
            <div class="dashboard-header">
                <div class="header-left">
                    <h1>Welcome, <?php echo htmlspecialchars($donor_name); ?></h1>
                </div>
                <div class="header-right">
                    <div class="notification-icon">
                        <i class="fas fa-bell"></i>
                        <span class="badge" id="headerNotificationCount">0</span>
                    </div>
                </div>
            </div>

            <!-- Content Sections -->
            <div class="content-sections">
                <!-- Search Hospital Section -->
                <div class="section" id="searchHospitalSection">
                    <div class="search-container">
                        <input type="text" id="hospitalSearch" placeholder="Search hospitals by name or location...">
                        <button type="button" id="searchBtn">
                            <i class="fas fa-search"></i>
                        </button>
                    </div>
                    <div id="searchResults" class="hospital-results"></div>
                </div>

                <!-- My Requests Section -->
                <div class="section hidden" id="myRequestsSection">
                    <h2>My Donation Requests</h2>
                    <div class="requests-container" id="requestsList"></div>
                </div>

                <!-- Notifications Section -->
                <div class="section hidden" id="notificationsSection">
                    <h2>Notifications</h2>
                    <div class="notifications-container" id="notificationsList"></div>
                </div>
            </div>
        </main>
    </div>

    <!-- Hospital Details Modal -->
    <div id="hospitalModal" class="modal">
        <div class="modal-content">
            <span class="close">&times;</span>
            <h2>Hospital Details</h2>
            <div id="hospitalDetails"></div>
            <button id="sendRequestBtn" class="btn-primary">Send Donation Request</button>
        </div>
    </div>

    <!-- Request Form Modal -->
    <div id="requestModal" class="modal">
        <div class="modal-content">
            <span class="close">&times;</span>
            <h2>Donation Request Form</h2>
            <form id="donationRequestForm">
                <input type="hidden" id="selectedHospitalId">
                <div class="form-group">
                    <label for="organType">Organ Type</label>
                    <select id="organType" required>
                        <option value="">Select Organ Type</option>
                        <option value="kidney">Kidney</option>
                        <option value="liver">Liver</option>
                        <option value="heart">Heart</option>
                        <option value="lungs">Lungs</option>
                        <option value="pancreas">Pancreas</option>
                        <option value="corneas">Corneas</option>
                    </select>
                </div>
                <button type="submit" class="btn-primary">Submit Request</button>
            </form>
        </div>
    </div>

    <script src="../../assets/js/donor-dashboard.js"></script>
</body>
</html>
