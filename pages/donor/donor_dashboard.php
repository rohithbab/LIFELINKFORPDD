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
            <div class="main-section">
                <header class="dashboard-header">
                    <div class="header-left">
                        <h1>Welcome, <?php echo htmlspecialchars($donor['name']); ?></h1>
                    </div>
                    <div class="header-right">
                        <div class="notification-icon">
                            <i class="fas fa-bell"></i>
                            <span class="badge" id="headerNotificationCount">0</span>
                        </div>
                        <div class="profile-section">
                            <button class="profile-trigger" onclick="toggleProfile()">
                                <div class="profile-icon">
                                    <i class="fas fa-user-circle"></i>
                                </div>
                            </button>
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
                    </div>
                </header>

                <!-- Modern Table Section -->
                <div class="table-container">
                    <div class="table-header">
                        <h2><i class="fas fa-table"></i> Hospital Requests</h2>
                        <div class="table-actions">
                            <div class="search-box">
                                <i class="fas fa-search"></i>
                                <input type="text" id="tableSearch" placeholder="Search...">
                            </div>
                        </div>
                    </div>
                    
                    <div class="table-responsive">
                        <table class="modern-table">
                            <thead>
                                <tr>
                                    <th>Donor Name</th>
                                    <th>Blood Type</th>
                                    <th>Organ Type</th>
                                    <th>Hospital Name</th>
                                    <th>Hospital Email</th>
                                    <th>Hospital Address</th>
                                    <th>Hospital Number</th>
                                    <th>Status</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <!-- Sample row for testing -->
                                <tr>
                                    <td>John Doe</td>
                                    <td><span class="blood-badge">A+</span></td>
                                    <td>Kidney</td>
                                    <td>City Hospital</td>
                                    <td>city@hospital.com</td>
                                    <td>123 Medical Street</td>
                                    <td>+1234567890</td>
                                    <td><span class="status-badge pending">Pending</span></td>
                                    <td>
                                        <div class="action-buttons">
                                            <button class="btn-view" title="View Details">
                                                <i class="fas fa-eye"></i>
                                            </button>
                                            <button class="btn-edit" title="Edit Request">
                                                <i class="fas fa-edit"></i>
                                            </button>
                                            <button class="btn-delete" title="Cancel Request">
                                                <i class="fas fa-times"></i>
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </main>
    </div>

    <script>
        function toggleGuardianInfo() {
            const guardianInfo = document.querySelector('.guardian-info');
            guardianInfo.style.display = guardianInfo.style.display === 'none' ? 'block' : 'none';
        }

        function toggleProfile() {
            const profileCard = document.querySelector('.profile-card.modern');
            profileCard.classList.toggle('show');
        }

        // Close profile when clicking outside
        document.addEventListener('click', function(event) {
            const profileSection = document.querySelector('.profile-section');
            const profileCard = document.querySelector('.profile-card.modern');
            const isClickInside = profileSection.contains(event.target);
            
            if (!isClickInside && profileCard.classList.contains('show')) {
                profileCard.classList.remove('show');
            }
        });
    </script>
</body>
</html>