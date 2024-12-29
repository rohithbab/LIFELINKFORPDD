 <?php
session_start();
require_once '../../config/db_connect.php';

// Check if user is logged in as recipient
if (!isset($_SESSION['is_recipient']) || !$_SESSION['is_recipient']) {
    header("Location: ../recipient_login.php");
    exit();
}

// Get recipient info from session
$recipient_id = $_SESSION['recipient_id'];

// Fetch recipient details from database
try {
    $stmt = $conn->prepare("SELECT id, full_name, date_of_birth, gender, phone_number, email, address, 
                           medical_condition, blood_type, organ_required, urgency_level, request_status 
                           FROM recipient_registration WHERE id = ?");
    $stmt->execute([$recipient_id]);
    $recipient = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$recipient) {
        die("Recipient not found");
    }
} catch(PDOException $e) {
    error_log("Error fetching recipient details: " . $e->getMessage());
    die("An error occurred while fetching your details");
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Recipient Dashboard - LifeLink</title>
    <link rel="stylesheet" href="../../assets/css/recipient-dashboard.css">
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
                        <a href="recipient_dashboard.php" class="<?php echo basename($_SERVER['PHP_SELF']) == 'recipient_dashboard.php' ? 'active' : ''; ?>">
                            <i class="fas fa-home"></i>
                            <span>Dashboard</span>
                        </a>
                    </li>
                    <li>
                        <a href="recipient_personal_details.php">
                            <i class="fas fa-user"></i>
                            <span>Personal Details</span>
                        </a>
                    </li>
                    <li>
                        <a href="search_hospitals_for_recipient.php">
                            <i class="fas fa-hospital"></i>
                            <span>Search Hospitals</span>
                        </a>
                    </li>
                    <li>
                        <a href="my_requests.php">
                            <i class="fas fa-notes-medical"></i>
                            <span>My Requests</span>
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

        <main class="main-content">
            <div class="main-section">
                <header class="dashboard-header">
                    <div class="header-left">
                        <h1>Welcome, <?php echo htmlspecialchars($recipient['full_name']); ?></h1>
                    </div>
                    <div class="header-right">
                        <div class="notification-icon">
                            <i class="fas fa-bell"></i>
                            <span class="badge" id="headerNotificationCount">0</span>
                        </div>
                        <div class="profile-section">
                            <button class="profile-trigger" onclick="toggleProfile()">
                                <div class="profile-icon">
                                    <?php if($recipient['gender'] === 'Male'): ?>
                                        <i class="fas fa-male"></i>
                                    <?php else: ?>
                                        <i class="fas fa-female"></i>
                                    <?php endif; ?>
                                </div>
                                <span class="profile-name"><?php echo htmlspecialchars($recipient['full_name']); ?></span>
                            </button>
                            
                            <div class="profile-card modern">
                                <div class="profile-header">
                                    <div class="header-overlay"></div>
                                    <div class="profile-avatar">
                                        <?php if($recipient['gender'] === 'Male'): ?>
                                            <i class="fas fa-male"></i>
                                        <?php else: ?>
                                            <i class="fas fa-female"></i>
                                        <?php endif; ?>
                                    </div>
                                    <h2><?php echo htmlspecialchars($recipient['full_name']); ?></h2>
                                </div>
                                
                                <div class="profile-content">
                                    <div class="info-grid">
                                        <div class="info-item">
                                            <div class="info-icon">
                                                <i class="fas fa-envelope"></i>
                                            </div>
                                            <div class="info-text">
                                                <label>Email</label>
                                                <span><?php echo htmlspecialchars($recipient['email']); ?></span>
                                            </div>
                                        </div>
                                        
                                        <div class="info-item">
                                            <div class="info-icon">
                                                <i class="fas fa-phone"></i>
                                            </div>
                                            <div class="info-text">
                                                <label>Phone</label>
                                                <span><?php echo htmlspecialchars($recipient['phone_number']); ?></span>
                                            </div>
                                        </div>
                                        
                                        <div class="info-item">
                                            <div class="info-icon">
                                                <i class="fas fa-tint"></i>
                                            </div>
                                            <div class="info-text">
                                                <label>Blood Type</label>
                                                <span><?php echo htmlspecialchars($recipient['blood_type']); ?></span>
                                            </div>
                                        </div>
                                        
                                        <div class="info-item">
                                            <div class="info-icon">
                                                <i class="fas fa-heartbeat"></i>
                                            </div>
                                            <div class="info-text">
                                                <label>Organ Required</label>
                                                <span><?php echo htmlspecialchars($recipient['organ_required']); ?></span>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </header>

                <!-- Modern Table Section -->
                <div class="table-container">
                    <div class="table-header">
                        <h2>Your Request Status</h2>
                    </div>
                    <div class="status-card <?php echo strtolower($recipient['request_status']); ?>">
                        <div class="status-icon">
                            <?php if($recipient['request_status'] === 'accepted'): ?>
                                <i class="fas fa-check-circle"></i>
                            <?php elseif($recipient['request_status'] === 'pending'): ?>
                                <i class="fas fa-clock"></i>
                            <?php else: ?>
                                <i class="fas fa-times-circle"></i>
                            <?php endif; ?>
                        </div>
                        <div class="status-info">
                            <h3>Current Status</h3>
                            <p><?php echo ucfirst(htmlspecialchars($recipient['request_status'])); ?></p>
                        </div>
                        <div class="urgency-level">
                            <span class="urgency-badge <?php echo strtolower($recipient['urgency_level']); ?>">
                                <?php echo ucfirst(htmlspecialchars($recipient['urgency_level'])); ?> Priority
                            </span>
                        </div>
                    </div>
                </div>
            </div>
        </main>
    </div>

    <script>
        function toggleProfile() {
            const profileCard = document.querySelector('.profile-card');
            profileCard.classList.toggle('show');

            // Close profile card when clicking outside
            document.addEventListener('click', function(event) {
                const profileSection = document.querySelector('.profile-section');
                const isClickInside = profileSection.contains(event.target);
                
                if (!isClickInside && profileCard.classList.contains('show')) {
                    profileCard.classList.remove('show');
                }
            });
        }
    </script>
</body>
</html>