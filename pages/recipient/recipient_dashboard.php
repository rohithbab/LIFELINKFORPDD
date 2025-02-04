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

// Then, get hospital requests - only pending ones for dashboard
$stmt = $conn->prepare("
    SELECT 
        r.full_name,
        r.blood_type,
        r.organ_required,
        h.name AS hospital_name,
        h.email AS hospital_email,
        h.address AS hospital_address,
        h.phone AS hospital_number,
        hra.status,
        hra.approval_date AS request_date,
        hra.approval_id AS request_id,
        r.recipient_medical_reports AS medical_reports,
        r.id_document
    FROM hospital_recipient_approvals hra
    JOIN hospitals h ON hra.hospital_id = h.hospital_id
    JOIN recipient_registration r ON hra.recipient_id = r.id
    WHERE hra.recipient_id = ? AND hra.status = 'pending'
    ORDER BY hra.approval_date DESC
");
$stmt->execute([$recipient_id]);
$requests = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Recipient Dashboard - LifeLink</title>
    <link rel="stylesheet" href="../../assets/css/donor-dashboard.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        body {
            font-family: 'Poppins', sans-serif;
            margin: 0;
            padding: 0;
            background: #f4f6f9;
        }

        .dashboard-container {
            display: flex;
            min-height: 100vh;
        }

        .main-content {
            flex: 1;
            margin-left: var(--sidebar-width);
            padding: 2rem;
            background: #f4f6f9;
        }

        .table-container {
            background: #ffffff;
            border-radius: 10px;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
            padding: 20px;
            margin-top: 20px;
        }

        .table-header {
            padding: 20px;
            border-bottom: 1px solid #eee;
            display: flex;
            justify-content: space-between;
            align-items: center;
            background: white;
            border-radius: 15px 15px 0 0;
        }

        .table-header h2 {
            margin: 0;
            color: #333;
            font-size: 1.5rem;
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .table-header h2 i {
            color: #28a745;
        }

        .search-box {
            position: relative;
            width: 250px;
        }

        .search-box input {
            width: 100%;
            padding: 10px 35px 10px 20px;
            border: 1px solid #ddd;
            border-radius: 8px;
            font-size: 0.9rem;
            background: white;
        }

        .search-box input::placeholder {
            color: #666;
            font-size: 0.9rem;
        }

        .search-box i {
            position: absolute;
            right: 12px;
            top: 50%;
            transform: translateY(-50%);
            color: #666;
            pointer-events: none;
            font-size: 0.9rem;
        }

        .modern-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 1rem;
            background: #fff;
            border-radius: 8px;
            overflow: hidden;
        }

        .modern-table th {
            background: linear-gradient(135deg, #28a745, #2196F3);
            padding: 15px 20px;
            text-align: left;
            color: white;
            font-weight: 600;
            border: none;
        }

        .modern-table th:first-child {
            border-top-left-radius: 8px;
        }

        .modern-table th:last-child {
            border-top-right-radius: 8px;
        }

        .modern-table td {
            padding: 15px 20px;
            border-bottom: 1px solid #eee;
            color: #666;
        }

        .blood-type-badge {
            background: #e9ecef;
            padding: 5px 10px;
            border-radius: 6px;
            font-weight: 500;
            color: #495057;
        }

        .status-badge {
            padding: 5px 10px;
            border-radius: 6px;
            font-weight: 500;
            text-align: center;
            display: inline-block;
            min-width: 100px;
        }

        .status-badge.pending {
            background: #fff3cd;
            color: #856404;
        }

        .action-buttons {
            display: flex;
            gap: 8px;
        }

        .action-btn {
            width: 32px;
            height: 32px;
            border: none;
            border-radius: 6px;
            display: flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            transition: all 0.3s ease;
        }

        .action-btn:hover {
            transform: translateY(-2px);
        }

        .view-btn {
            background: rgba(40, 167, 69, 0.1);
            color: #28a745;
        }

        .message-btn {
            background: rgba(13, 110, 253, 0.1);
            color: #0d6efd;
        }

        .no-data {
            text-align: center;
            padding: 40px !important;
        }

        .no-data-content {
            display: flex;
            flex-direction: column;
            align-items: center;
            gap: 15px;
        }

        .no-data-content i {
            font-size: 48px;
            color: #ddd;
        }

        .no-data-content p {
            color: #666;
            margin: 0;
        }

        .modern-btn {
            padding: 8px 16px;
            border: none;
            border-radius: 8px;
            background: #28a745;
            color: white;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 8px;
            font-weight: 500;
            transition: all 0.3s ease;
        }

        .modern-btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 10px rgba(0,0,0,0.1);
        }
    </style>
</head>
<body>
    <div class="dashboard-container">
        <?php include '../../includes/recipient_sidebar.php'; ?>

        <main class="main-content">
            <div class="main-section">
                <header class="dashboard-header">
                    <div class="header-left">
                        <h1>Welcome, <?php echo htmlspecialchars($recipient['full_name']); ?></h1>
                    </div>
                    <div class="header-right">
                        <div class="notification-icon">
                            <?php
                            // Get unread notification count
                            $stmt = $conn->prepare("
                                SELECT COUNT(*) as unread_count 
                                FROM recipient_notifications 
                                WHERE recipient_id = ? AND is_read = 0
                            ");
                            $stmt->execute([$recipient_id]);
                            $result = $stmt->fetch();
                            $unread_count = $result['unread_count'];
                            ?>
                            <a href="recipients_notifications.php" class="notification-link">
                                <i class="fas fa-bell"></i>
                                <?php if ($unread_count > 0): ?>
                                    <span class="notification-badge"><?php echo $unread_count; ?></span>
                                <?php endif; ?>
                            </a>
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
                                        <?php if($recipient['gender'] === 'Male'): ?>
                                            <i class="fas fa-male"></i>
                                        <?php else: ?>
                                            <i class="fas fa-female"></i>
                                        <?php endif; ?>
                                    </div>
                                    <h3><?php echo htmlspecialchars($recipient['full_name']); ?></h3>
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
                                    <div class="profile-actions">
                                        <a href="recipient_personal_details.php" class="modern-btn">
                                            <i class="fas fa-user-edit"></i> Edit Profile
                                        </a>
                                        <a href="../recipient_login.php" class="modern-btn danger">
                                            <i class="fas fa-sign-out-alt"></i> Logout
                                        </a>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </header>

                <!-- Modern Table Section -->
                <div class="table-container">
                    <div class="table-header">
                        <h2><i class="fas fa-hospital"></i> Hospital Requests</h2>
                        <div class="table-actions">
                            <div class="search-box">
                                <input type="text" id="searchInput" placeholder="Search requests...">
                                <i class="fas fa-search"></i>
                            </div>
                        </div>
                    </div>
                    <div class="table-responsive">
                        <table class="modern-table">
                            <thead>
                                <tr>
                                    <th>Hospital Name</th>
                                    <th>Blood Type</th>
                                    <th>Organ Required</th>
                                    <th>Status</th>
                                    <th>Request Date</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (empty($requests)): ?>
                                    <tr>
                                        <td colspan="6" class="no-data">
                                            <div class="no-data-content">
                                                <i class="fas fa-inbox"></i>
                                                <p>No pending requests found</p>
                                                <a href="search_hospitals_for_recipient.php" class="modern-btn">Search Hospitals</a>
                                            </div>
                                        </td>
                                    </tr>
                                <?php else: ?>
                                    <?php foreach ($requests as $request): ?>
                                        <tr>
                                            <td><?php echo htmlspecialchars($request['hospital_name']); ?></td>
                                            <td>
                                                <span class="blood-type-badge">
                                                    <?php echo htmlspecialchars($request['blood_type']); ?>
                                                </span>
                                            </td>
                                            <td><?php echo htmlspecialchars($request['organ_required']); ?></td>
                                            <td>
                                                <span class="status-badge <?php echo strtolower($request['status']); ?>">
                                                    <?php echo ucfirst($request['status']); ?>
                                                </span>
                                            </td>
                                            <td><?php echo date('M d, Y', strtotime($request['request_date'])); ?></td>
                                            <td>
                                                <div class="action-buttons">
                                                    <button class="action-btn view-btn" title="View Details">
                                                        <i class="fas fa-eye"></i>
                                                    </button>
                                                    <button class="action-btn message-btn" title="Send Message">
                                                        <i class="fas fa-envelope"></i>
                                                    </button>
                                                </div>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </tbody>
                        </table>
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

        // Reject request
        function rejectRequest(requestId) {
            if (confirm('Are you sure you want to reject this request? This action cannot be undone.')) {
                const form = document.createElement('form');
                form.method = 'POST';
                form.innerHTML = `
                    <input type="hidden" name="request_id" value="${requestId}">
                    <input type="hidden" name="status" value="rejected">
                    <input type="hidden" name="update_status" value="1">
                `;
                document.body.appendChild(form);
                form.submit();
            }
        }

        document.addEventListener('DOMContentLoaded', function() {
            const notificationIcon = document.getElementById('notificationIcon');
            const notificationDropdown = document.getElementById('notificationDropdown');

            notificationIcon.addEventListener('click', function(e) {
                e.stopPropagation();
                notificationDropdown.classList.toggle('show');
            });

            // Close dropdown when clicking outside
            document.addEventListener('click', function(e) {
                if (!notificationIcon.contains(e.target)) {
                    notificationDropdown.classList.remove('show');
                }
            });

            // Prevent dropdown from closing when clicking inside it
            notificationDropdown.addEventListener('click', function(e) {
                e.stopPropagation();
            });
        });

        // Search functionality
        document.getElementById('searchInput').addEventListener('keyup', function() {
            const searchValue = this.value.toLowerCase();
            const tableRows = document.querySelectorAll('.modern-table tbody tr');
            
            tableRows.forEach(row => {
                const text = row.textContent.toLowerCase();
                row.style.display = text.includes(searchValue) ? '' : 'none';
            });
        });
    </script>
</body>
</html>