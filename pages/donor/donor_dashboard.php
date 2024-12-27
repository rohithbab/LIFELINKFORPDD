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

// Fetch donor details from database
try {
    $stmt = $conn->prepare("SELECT name, gender, blood_group, email, phone, guardian_name, guardian_email, guardian_phone 
                           FROM donor WHERE donor_id = ?");
    $stmt->execute([$donor_id]);
    $donor = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$donor) {
        die("Donor not found");
    }
} catch(PDOException $e) {
    error_log("Error fetching donor details: " . $e->getMessage());
    die("An error occurred while fetching your details");
}
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
        <aside class="sidebar">
            <div class="sidebar-header">
                <i class="fas fa-heartbeat"></i>
                <span>LifeLink</span>
            </div>
            <nav class="sidebar-nav">
                <ul>
                    <li>
                        <a href="donor_dashboard.php" class="<?php echo basename($_SERVER['PHP_SELF']) == 'donor_dashboard.php' ? 'active' : ''; ?>">
                            <i class="fas fa-home"></i>
                            <span>Dashboard</span>
                        </a>
                    </li>
                    <li>
                        <a href="donor_personal_details.php">
                            <i class="fas fa-user"></i>
                            <span>Profile</span>
                        </a>
                    </li>
                    <li>
                        <a href="#" id="searchHospitalBtn">
                            <i class="fas fa-hospital"></i>
                            <span>Search Hospitals</span>
                        </a>
                    </li>
                    <li>
                        <a href="#" id="myRequestsBtn">
                            <i class="fas fa-clipboard-list"></i>
                            <span>My Requests</span>
                            <span class="notification-badge">2</span>
                        </a>
                    </li>
                    <li>
                        <a href="#" id="notificationsBtn">
                            <i class="fas fa-bell"></i>
                            <span>Notifications</span>
                            <span class="notification-badge">3</span>
                        </a>
                    </li>
                    <li>
                        <a href="../donor_login.php">
                            <i class="fas fa-sign-out-alt"></i>
                            <span>Logout</span>
                        </a>
                    </li>
                </ul>
            </nav>
        </aside>
        <main class="main-content">
            <div class="dashboard-header">
                <div class="header-left">
                    <h1>Welcome, <?php echo htmlspecialchars($donor['name']); ?></h1>
                </div>
                <div class="header-right">
                    <div class="notification-icon">
                        <i class="fas fa-bell"></i>
                        <span class="badge" id="headerNotificationCount">0</span>
                    </div>
                </div>
            </div>

            <!-- Enhanced Profile Card Section -->
            <div class="profile-section">
                <div class="profile-card modern">
                    <div class="profile-header">
                        <div class="header-overlay"></div>
                        <div class="profile-avatar">
                            <?php if($donor['gender'] === 'Male'): ?>
                                <i class="fas fa-user"></i>
                            <?php else: ?>
                                <i class="fas fa-user"></i>
                            <?php endif; ?>
                        </div>
                        <div class="profile-title">
                            <h2><?php echo htmlspecialchars($donor['name']); ?></h2>
                            <span class="donor-id">Donor ID: <?php echo htmlspecialchars($donor_id); ?></span>
                        </div>
                    </div>
                    
                    <div class="profile-content">
                        <div class="info-grid">
                            <div class="info-item">
                                <div class="info-icon">
                                    <i class="fas fa-envelope"></i>
                                </div>
                                <div class="info-details">
                                    <label>Email Address</label>
                                    <span><?php echo htmlspecialchars($donor['email']); ?></span>
                                </div>
                            </div>
                            
                            <div class="info-item">
                                <div class="info-icon">
                                    <i class="fas fa-phone-alt"></i>
                                </div>
                                <div class="info-details">
                                    <label>Phone Number</label>
                                    <span><?php echo htmlspecialchars($donor['phone']); ?></span>
                                </div>
                            </div>
                            
                            <div class="info-item blood-type">
                                <div class="info-icon">
                                    <i class="fas fa-tint"></i>
                                </div>
                                <div class="info-details">
                                    <label>Blood Group</label>
                                    <span><?php echo htmlspecialchars($donor['blood_group']); ?></span>
                                </div>
                            </div>
                        </div>

                        <div class="guardian-section">
                            <?php if(!empty($donor['guardian_name']) || !empty($donor['guardian_email']) || !empty($donor['guardian_phone'])): ?>
                                <button class="guardian-info-btn modern-btn" onclick="toggleGuardianInfo()">
                                    <i class="fas fa-user-shield"></i> Guardian Information
                                </button>
                                
                                <div class="guardian-info" style="display: none;">
                                    <div class="guardian-grid">
                                        <?php if(!empty($donor['guardian_name'])): ?>
                                            <div class="info-item">
                                                <div class="info-icon">
                                                    <i class="fas fa-user"></i>
                                                </div>
                                                <div class="info-details">
                                                    <label>Guardian Name</label>
                                                    <span><?php echo htmlspecialchars($donor['guardian_name']); ?></span>
                                                </div>
                                            </div>
                                        <?php endif; ?>
                                        
                                        <?php if(!empty($donor['guardian_email'])): ?>
                                            <div class="info-item">
                                                <div class="info-icon">
                                                    <i class="fas fa-envelope"></i>
                                                </div>
                                                <div class="info-details">
                                                    <label>Guardian Email</label>
                                                    <span><?php echo htmlspecialchars($donor['guardian_email']); ?></span>
                                                </div>
                                            </div>
                                        <?php endif; ?>
                                        
                                        <?php if(!empty($donor['guardian_phone'])): ?>
                                            <div class="info-item">
                                                <div class="info-icon">
                                                    <i class="fas fa-phone-alt"></i>
                                                </div>
                                                <div class="info-details">
                                                    <label>Guardian Phone</label>
                                                    <span><?php echo htmlspecialchars($donor['guardian_phone']); ?></span>
                                                </div>
                                            </div>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            <?php else: ?>
                                <div class="no-guardian modern">
                                    <i class="fas fa-info-circle"></i>
                                    <span>No guardian information available</span>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>
            <!-- Rest of your main content -->
        </main>
    </div>

    <script>
    function toggleGuardianInfo() {
        const guardianInfo = document.querySelector('.guardian-info');
        const btn = document.querySelector('.guardian-info-btn');
        
        if (guardianInfo.style.display === 'none') {
            guardianInfo.style.display = 'block';
            btn.innerHTML = '<i class="fas fa-user-shield"></i> Hide Guardian Info';
        } else {
            guardianInfo.style.display = 'none';
            btn.innerHTML = '<i class="fas fa-user-shield"></i> View Guardian Info';
        }
    }
    </script>
</body>
</html>