<?php
session_start();
require_once '../../config/connection.php';

// Check if admin is logged in
if (!isset($_SESSION['admin_id'])) {
    header("Location: ../admin_login.php");
    exit();
}

// Get donor ID from URL
$donor_id = isset($_GET['id']) ? $_GET['id'] : null;

if (!$donor_id) {
    header("Location: manage_donors.php");
    exit();
}

// Fetch donor details
$query = "SELECT * FROM donors WHERE donor_id = ?";
$stmt = $conn->prepare($query);
$stmt->bind_param("i", $donor_id);
$stmt->execute();
$result = $stmt->get_result();
$donor = $result->fetch_assoc();

// Get rejection details if available
$rejection_details = isset($_SESSION['rejection_details'][$donor_id]) ? $_SESSION['rejection_details'][$donor_id] : null;
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>View Donor Details - LifeLink Admin</title>
    <link rel="stylesheet" href="../../assets/css/admin.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        .container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 20px;
        }
        
        .back-button {
            display: inline-flex;
            align-items: center;
            padding: 10px 20px;
            background: linear-gradient(135deg, #1a73e8, #34a853);
            color: white;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            text-decoration: none;
            margin-bottom: 20px;
            transition: all 0.3s ease;
        }

        .back-button:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 8px rgba(0,0,0,0.15);
        }
        
        .donor-details {
            background: white;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }
        
        .detail-section {
            margin-bottom: 20px;
            padding-bottom: 20px;
            border-bottom: 1px solid #eee;
        }
        
        .detail-section:last-child {
            border-bottom: none;
        }
        
        .section-title {
            color: #1a73e8;
            margin-bottom: 15px;
            font-size: 1.5em;
            font-weight: bold;
        }
        
        .detail-grid {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 20px;
        }
        
        .detail-item {
            margin-bottom: 10px;
        }
        
        .detail-label {
            font-weight: bold;
            color: #666;
            margin-bottom: 5px;
        }
        
        .status-badge {
            display: inline-block;
            padding: 8px 15px;
            border-radius: 15px;
            font-size: 0.9em;
            font-weight: bold;
            display: inline-flex;
            align-items: center;
            gap: 5px;
        }
        
        .pending { 
            background: linear-gradient(135deg, #ffc107, #ffdb4d);
            color: #000; 
        }
        
        .approved { 
            background: linear-gradient(135deg, #28a745, #34ce57);
            color: white; 
        }
        
        .rejected { 
            background: linear-gradient(135deg, #dc3545, #ff4444);
            color: white; 
        }
        
        .rejection-details {
            background-color: #fff3f3;
            padding: 15px;
            border-radius: 5px;
            margin-top: 20px;
        }

        .blood-group {
            color: #dc3545;
            font-weight: bold;
            font-size: 1.1em;
        }

        .organs-list {
            display: flex;
            flex-wrap: wrap;
            gap: 10px;
            margin-top: 5px;
        }

        .organ-badge {
            background: #e9ecef;
            padding: 5px 10px;
            border-radius: 15px;
            font-size: 0.9em;
            color: #495057;
        }

        .contact-info i {
            width: 20px;
            color: #1a73e8;
            margin-right: 5px;
        }

        .action-buttons {
            display: flex;
            gap: 10px;
            margin-top: 20px;
        }

        .action-btn {
            padding: 10px 20px;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            font-weight: 500;
            display: inline-flex;
            align-items: center;
            gap: 5px;
            transition: all 0.3s ease;
        }

        .approve-btn {
            background: linear-gradient(135deg, #28a745, #34ce57);
            color: white;
        }

        .reject-btn {
            background: linear-gradient(135deg, #dc3545, #ff4444);
            color: white;
        }

        .action-btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 8px rgba(0,0,0,0.15);
        }
    </style>
</head>
<body>
    <div class="container">
        <a href="manage_donors.php" class="back-button">
            <i class="fas fa-arrow-left"></i>&nbsp; Back to Donors
        </a>
        
        <div class="donor-details">
            <div class="detail-section">
                <h2 class="section-title">Donor Information</h2>
                <div class="detail-grid">
                    <div class="detail-item">
                        <div class="detail-label">Full Name</div>
                        <div><?php echo htmlspecialchars($donor['name']); ?></div>
                    </div>
                    <div class="detail-item">
                        <div class="detail-label">Status</div>
                        <div>
                            <?php 
                            $statusIcon = '';
                            switch($donor['status']) {
                                case 'approved':
                                    $statusIcon = '<i class="fas fa-check-circle"></i>';
                                    break;
                                case 'rejected':
                                    $statusIcon = '<i class="fas fa-times-circle"></i>';
                                    break;
                                default:
                                    $statusIcon = '<i class="fas fa-clock"></i>';
                            }
                            ?>
                            <span class="status-badge <?php echo $donor['status']; ?>">
                                <?php echo $statusIcon . ' ' . ucfirst($donor['status']); ?>
                            </span>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="detail-section">
                <h2 class="section-title">Medical Information</h2>
                <div class="detail-grid">
                    <div class="detail-item">
                        <div class="detail-label">Blood Group</div>
                        <div class="blood-group"><?php echo htmlspecialchars($donor['blood_group']); ?></div>
                    </div>
                    <div class="detail-item">
                        <div class="detail-label">Gender</div>
                        <div><?php echo htmlspecialchars($donor['gender']); ?></div>
                    </div>
                    <div class="detail-item">
                        <div class="detail-label">Organs to Donate</div>
                        <div class="organs-list">
                            <?php 
                            $organs = explode(',', $donor['organs_to_donate']);
                            foreach($organs as $organ): ?>
                                <span class="organ-badge"><?php echo trim($organ); ?></span>
                            <?php endforeach; ?>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="detail-section">
                <h2 class="section-title">Contact Information</h2>
                <div class="detail-grid">
                    <div class="detail-item contact-info">
                        <div class="detail-label">Email</div>
                        <div><i class="fas fa-envelope"></i><?php echo htmlspecialchars($donor['email']); ?></div>
                    </div>
                    <div class="detail-item contact-info">
                        <div class="detail-label">Phone</div>
                        <div><i class="fas fa-phone"></i><?php echo htmlspecialchars($donor['phone']); ?></div>
                    </div>
                    <div class="detail-item contact-info">
                        <div class="detail-label">Address</div>
                        <div><i class="fas fa-map-marker-alt"></i><?php echo htmlspecialchars($donor['address']); ?></div>
                    </div>
                    <div class="detail-item">
                        <div class="detail-label">Registration Date</div>
                        <div><i class="fas fa-calendar"></i><?php echo date('F j, Y', strtotime($donor['created_at'])); ?></div>
                    </div>
                </div>
            </div>
            
            <?php if ($donor['status'] === 'rejected' && $rejection_details): ?>
            <div class="detail-section">
                <h2 class="section-title">Rejection Details</h2>
                <div class="rejection-details">
                    <div class="detail-item">
                        <div class="detail-label">Rejection Date</div>
                        <div><?php echo date('F j, Y', strtotime($rejection_details['date'])); ?></div>
                    </div>
                    <div class="detail-item">
                        <div class="detail-label">Reason for Rejection</div>
                        <div><?php echo htmlspecialchars($rejection_details['reason']); ?></div>
                    </div>
                    <div class="detail-item">
                        <div class="detail-label">Email Notification</div>
                        <div>
                            <?php echo $rejection_details['email_sent'] ? 
                                '<i class="fas fa-check-circle" style="color: #28a745;"></i> Sent' : 
                                '<i class="fas fa-times-circle" style="color: #dc3545;"></i> Not Sent'; ?>
                        </div>
                    </div>
                </div>
            </div>
            <?php endif; ?>

            <?php if ($donor['status'] === 'pending'): ?>
            <div class="action-buttons">
                <button class="action-btn approve-btn" onclick="updateStatus(<?php echo $donor['donor_id']; ?>, 'approved')">
                    <i class="fas fa-check"></i> Approve Donor
                </button>
                <button class="action-btn reject-btn" onclick="showRejectionModal(<?php echo $donor['donor_id']; ?>)">
                    <i class="fas fa-times"></i> Reject Donor
                </button>
            </div>
            <?php endif; ?>
        </div>
    </div>

    <script>
        function updateStatus(donorId, status) {
            if (confirm(`Are you sure you want to ${status} this donor?`)) {
                window.location.href = `update_donor_status.php?id=${donorId}&status=${status}`;
            }
        }

        function showRejectionModal(donorId) {
            const reason = prompt("Please enter the reason for rejection:");
            if (reason !== null && reason.trim() !== '') {
                window.location.href = `update_donor_status.php?id=${donorId}&status=rejected&reason=${encodeURIComponent(reason)}`;
            }
        }
    </script>
</body>
</html>
