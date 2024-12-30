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
                        <a href="../donor/search_hospitals_for_donors.php" class="sidebar-link">
                         <i class="fas fa-search"></i>
                         <span>Search Hospitals</span>
                        </a>
                    </li>
                    <li>
                        <a href="my_requests_for_donors.php">
                            <i class="fas fa-list"></i>
                            <span>My Requests</span>
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
                                <?php
                                // Fetch donor's requests from hospital_donor_approvals
                                $stmt = $conn->prepare("
                                    SELECT hda.*, h.name as hospital_name, h.email as hospital_email, 
                                           h.address as hospital_address, h.phone as hospital_phone,
                                           d.name as donor_name, d.blood_group
                                    FROM hospital_donor_approvals hda
                                    JOIN hospitals h ON hda.hospital_id = h.hospital_id
                                    JOIN donor d ON hda.donor_id = d.donor_id
                                    WHERE hda.donor_id = ? AND hda.status = 'Pending'
                                    ORDER BY hda.request_date DESC
                                ");
                                $stmt->execute([$donor_id]);
                                $requests = $stmt->fetchAll(PDO::FETCH_ASSOC);

                                if (count($requests) > 0) {
                                    foreach ($requests as $request) {
                                        $status_class = strtolower($request['status']);
                                        ?>
                                        <tr>
                                            <td><?php echo htmlspecialchars($request['donor_name']); ?></td>
                                            <td>
                                                <span class="blood-type"><?php echo htmlspecialchars($request['blood_group']); ?></span>
                                            </td>
                                            <td><?php echo htmlspecialchars($request['organ_type']); ?></td>
                                            <td><?php echo htmlspecialchars($request['hospital_name']); ?></td>
                                            <td><?php echo htmlspecialchars($request['hospital_email']); ?></td>
                                            <td><?php echo htmlspecialchars($request['hospital_address']); ?></td>
                                            <td><?php echo htmlspecialchars($request['hospital_phone']); ?></td>
                                            <td>
                                                <span class="status-badge <?php echo $status_class; ?>">
                                                    <?php echo htmlspecialchars($request['status']); ?>
                                                </span>
                                            </td>
                                            <td class="actions">
                                                <button onclick="rejectRequest(<?php echo $request['approval_id']; ?>)" class="btn-reject">
                                                    <i class="fas fa-times"></i> Reject
                                                </button>
                                            </td>
                                        </tr>
                                        <?php
                                    }
                                } else {
                                    ?>
                                    <tr>
                                        <td colspan="9" class="no-data">
                                            <div class="no-data-message">
                                                <i class="fas fa-info-circle"></i>
                                                <p>No hospital requests found</p>
                                                 
                                                </a>
                                            </div>
                                        </td>
                                    </tr>
                                    <?php
                                }
                                ?>
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

        function rejectRequest(approvalId) {
            if (confirm('Are you sure you want to reject this request? This action cannot be undone.')) {
                fetch('../../ajax/reject_donor_request.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/x-www-form-urlencoded',
                    },
                    body: `approval_id=${approvalId}`
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        alert('Request rejected successfully');
                        location.reload();
                    } else {
                        alert('Error: ' + data.error);
                    }
                })
                .catch(error => {
                    alert('Error rejecting request');
                });
            }
        }

        // Initialize tooltips for rejection reasons
        document.querySelectorAll('.rejection-reason').forEach(elem => {
            elem.addEventListener('mouseover', function(e) {
                const reason = this.getAttribute('title');
                const tooltip = document.createElement('div');
                tooltip.className = 'tooltip';
                tooltip.textContent = reason;
                document.body.appendChild(tooltip);
                
                const rect = this.getBoundingClientRect();
                tooltip.style.top = rect.bottom + 5 + 'px';
                tooltip.style.left = rect.left + (rect.width / 2) - (tooltip.offsetWidth / 2) + 'px';
                
                this.addEventListener('mouseout', function() {
                    tooltip.remove();
                }, { once: true });
            });
        });
    </script>
</body>
</html>