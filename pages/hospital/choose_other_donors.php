<?php
session_start();
require_once '../../config/db_connect.php';

// Check if hospital is logged in
if (!isset($_SESSION['hospital_logged_in']) || !$_SESSION['hospital_logged_in']) {
    header("Location: ../../pages/hospital_login.php");
    exit();
}

$hospital_id = $_SESSION['hospital_id'];
$donor_id = isset($_GET['donor_id']) ? (int)$_GET['donor_id'] : 0;

if (!$donor_id) {
    header("Location: choose_donors_for_matches.php");
    exit();
}

// Fetch donor details with hospital information
try {
    $stmt = $conn->prepare("
        SELECT d.*, h.hospital_name, h.phone as hospital_phone, h.address as hospital_address,
               ha.status as approval_status, ha.organ_type
        FROM donor d
        JOIN hospital_donor_approvals ha ON d.donor_id = ha.donor_id
        JOIN hospitals h ON ha.hospital_id = h.hospital_id
        WHERE d.donor_id = ? AND ha.status = 'Approved'
    ");
    $stmt->execute([$donor_id]);
    $donor = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$donor) {
        header("Location: choose_donors_for_matches.php");
        exit();
    }

    // Check if request already exists
    $stmt = $conn->prepare("
        SELECT status 
        FROM donor_and_recipient_requests 
        WHERE donor_id = ? AND requesting_hospital_id = ? 
        ORDER BY request_date DESC LIMIT 1
    ");
    $stmt->execute([$donor_id, $hospital_id]);
    $existingRequest = $stmt->fetch(PDO::FETCH_ASSOC);

} catch(PDOException $e) {
    error_log("Error fetching donor details: " . $e->getMessage());
    header("Location: choose_donors_for_matches.php");
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Donor Details - LifeLink</title>
    <link rel="stylesheet" href="../../assets/css/hospital-dashboard.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        .donor-details {
            background: white;
            border-radius: 15px;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.1);
            margin: 2rem;
            padding: 2rem;
        }

        .donor-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 2rem;
            padding-bottom: 1rem;
            border-bottom: 2px solid #eee;
        }

        .donor-info {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 2rem;
        }

        .info-card {
            background: #f8f9fa;
            padding: 1.5rem;
            border-radius: 10px;
            transition: all 0.3s ease;
        }

        .info-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
        }

        .info-card h3 {
            color: var(--primary-blue);
            margin-bottom: 1rem;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }

        .info-item {
            margin: 0.5rem 0;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }

        .info-label {
            font-weight: 500;
            color: #666;
            min-width: 120px;
        }

        .request-btn {
            padding: 0.8rem 2rem;
            border: none;
            border-radius: 8px;
            cursor: pointer;
            background: linear-gradient(135deg, var(--primary-blue), var(--primary-green));
            color: white;
            font-size: 1rem;
            transition: all 0.3s ease;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }

        .request-btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.2);
        }

        .request-btn:disabled {
            background: #ccc;
            cursor: not-allowed;
            transform: none;
            box-shadow: none;
        }

        .request-status {
            padding: 0.5rem 1rem;
            border-radius: 20px;
            font-size: 0.9rem;
            font-weight: 500;
        }

        .status-pending {
            background: #fff3cd;
            color: #856404;
        }

        .status-approved {
            background: #d4edda;
            color: #155724;
        }

        .status-rejected {
            background: #f8d7da;
            color: #721c24;
        }

        .back-btn {
            padding: 0.5rem 1rem;
            border: none;
            border-radius: 5px;
            background: #f8f9fa;
            color: #666;
            cursor: pointer;
            transition: all 0.3s ease;
            display: flex;
            align-items: center;
            gap: 0.5rem;
            text-decoration: none;
        }

        .back-btn:hover {
            background: #e9ecef;
        }
    </style>
</head>
<body>
    <div class="dashboard-container">
        <!-- Sidebar -->
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
                        <a href="make_matches.php" class="active">
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

        <!-- Main Content -->
        <main class="main-content">
            <div class="dashboard-header">
                <div class="header-left">
                    <a href="choose_donors_for_matches.php" class="back-btn">
                        <i class="fas fa-arrow-left"></i>
                        Back to Donor List
                    </a>
                </div>
            </div>

            <div class="donor-details">
                <div class="donor-header">
                    <h2>Donor Information</h2>
                    <?php if ($existingRequest): ?>
                        <span class="request-status status-<?php echo strtolower($existingRequest['status']); ?>">
                            Request <?php echo $existingRequest['status']; ?>
                        </span>
                    <?php else: ?>
                        <button class="request-btn" onclick="sendRequest(<?php echo $donor_id; ?>)"
                                <?php echo ($donor['hospital_id'] == $hospital_id) ? 'disabled' : ''; ?>>
                            <i class="fas fa-paper-plane"></i>
                            Send Request
                        </button>
                    <?php endif; ?>
                </div>

                <div class="donor-info">
                    <!-- Personal Information -->
                    <div class="info-card">
                        <h3><i class="fas fa-user"></i> Personal Details</h3>
                        <div class="info-item">
                            <span class="info-label">Name:</span>
                            <span><?php echo htmlspecialchars($donor['name']); ?></span>
                        </div>
                        <div class="info-item">
                            <span class="info-label">Blood Group:</span>
                            <span><?php echo htmlspecialchars($donor['blood_group']); ?></span>
                        </div>
                        <div class="info-item">
                            <span class="info-label">Gender:</span>
                            <span><?php echo htmlspecialchars($donor['gender']); ?></span>
                        </div>
                        <div class="info-item">
                            <span class="info-label">Age:</span>
                            <span><?php 
                                $dob = new DateTime($donor['dob']);
                                $now = new DateTime();
                                echo $dob->diff($now)->y . " years";
                            ?></span>
                        </div>
                    </div>

                    <!-- Medical Information -->
                    <div class="info-card">
                        <h3><i class="fas fa-heartbeat"></i> Medical Information</h3>
                        <div class="info-item">
                            <span class="info-label">Organ Type:</span>
                            <span><?php echo htmlspecialchars($donor['organ_type']); ?></span>
                        </div>
                        <div class="info-item">
                            <span class="info-label">Medical Conditions:</span>
                            <span><?php echo htmlspecialchars($donor['medical_conditions'] ?: 'None'); ?></span>
                        </div>
                    </div>

                    <!-- Hospital Information -->
                    <div class="info-card">
                        <h3><i class="fas fa-hospital"></i> Hospital Information</h3>
                        <div class="info-item">
                            <span class="info-label">Hospital:</span>
                            <span><?php echo htmlspecialchars($donor['hospital_name']); ?></span>
                        </div>
                        <div class="info-item">
                            <span class="info-label">Phone:</span>
                            <span><?php echo htmlspecialchars($donor['hospital_phone']); ?></span>
                        </div>
                        <div class="info-item">
                            <span class="info-label">Address:</span>
                            <span><?php echo htmlspecialchars($donor['hospital_address']); ?></span>
                        </div>
                    </div>
                </div>
            </div>
        </main>
    </div>

    <script>
        function sendRequest(donorId) {
            if (!confirm('Are you sure you want to send a request for this donor?')) {
                return;
            }

            fetch('../../ajax/send_donor_request.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify({
                    donor_id: donorId
                })
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    alert('Request sent successfully!');
                    location.reload();
                } else {
                    alert('Error sending request: ' + data.message);
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('An error occurred while sending the request');
            });
        }
    </script>
</body>
</html>
