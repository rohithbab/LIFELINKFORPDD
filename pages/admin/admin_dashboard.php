<?php
session_start();
require_once '../../backend/php/connection.php';
require_once '../../backend/php/queries.php';

// Check if admin is logged in
if (!isset($_SESSION['admin_id'])) {
    header('Location: ../admin_login.php');
    exit();
}

$stats = getDashboardStats($conn);
$notifications = getAdminNotifications($conn, 5);
$pendingHospitals = getPendingHospitals($conn);
$pendingDonors = getPendingDonors($conn);
$pendingRecipients = getPendingRecipients($conn);
$urgentRecipients = getUrgentRecipients($conn);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard - LifeLink</title>
    
    <!-- CSS Files -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link rel="stylesheet" href="../../assets/css/styles.css">
    <link rel="stylesheet" href="../../assets/css/admin-dashboard.css">
    
    <!-- Custom Styles -->
    <style>
        .odml-input {
            width: 150px;
            padding: 8px;
            border: 2px solid #e0e0e0;
            border-radius: 4px;
            transition: border-color 0.3s ease;
            font-size: 14px;
            margin-right: 8px;
        }

        .odml-input:focus {
            border-color: #1a73e8;
            outline: none;
            box-shadow: 0 0 5px rgba(26, 115, 232, 0.3);
        }

        .update-btn {
            padding: 6px 12px;
            background: #1a73e8;
            color: white;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            transition: background-color 0.3s ease;
            font-size: 14px;
        }

        .update-btn:hover {
            background: #1557b0;
        }

        .view-btn {
            padding: 8px 16px;
            background: #4CAF50;
            color: white;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            text-decoration: none;
            display: inline-block;
            transition: background-color 0.3s ease;
            font-size: 14px;
        }

        .view-btn:hover {
            background: #388E3C;
            text-decoration: none;
            color: white;
        }

        .action-cell {
            display: flex;
            gap: 8px;
            align-items: center;
        }

        .table-container {
            background: white;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
            margin-bottom: 20px;
        }

        .table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 15px;
        }

        .table th {
            background: #f8f9fa;
            padding: 12px;
            text-align: left;
            font-weight: 600;
            color: #333;
            border-bottom: 2px solid #dee2e6;
        }

        .table td {
            padding: 12px;
            border-bottom: 1px solid #dee2e6;
            vertical-align: middle;
        }

        .approve-btn, .reject-btn {
            padding: 8px 16px;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            font-size: 14px;
            transition: background-color 0.3s ease;
            color: white;
        }

        .approve-btn {
            background: #28a745;
        }

        .approve-btn:hover {
            background: #218838;
        }

        .reject-btn {
            background: #dc3545;
        }

        .reject-btn:hover {
            background: #c82333;
        }
        
        /* Update gradient header for pending hospitals table */
        .table-container h2 {
            background: linear-gradient(135deg, #4CAF50, #2196F3);
            color: white;
            padding: 15px;
            border-radius: 5px 5px 0 0;
            margin: 0;
        }
        
        #pending-hospitals-table thead tr {
            background: linear-gradient(135deg, #4CAF50, #2196F3);
            color: white;
        }
        
        #pending-hospitals-table thead th {
            padding: 12px;
            font-weight: 600;
        }
    </style>

    <!-- JavaScript Dependencies -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
</head>
<body>
    <div class="admin-container">
        <!-- Sidebar -->
        <div class="sidebar">
            <div class="sidebar-header">
                <h2><span class="logo-gradient">LifeLink</span> Admin</h2>
            </div>
            <ul class="nav-menu">
                <li class="nav-item">
                    <a href="admin_dashboard.php" class="nav-link active">
                        <i class="fas fa-tachometer-alt"></i>
                        Dashboard
                    </a>
                </li>
                <li class="nav-item">
                    <a href="manage_hospitals.php" class="nav-link">
                        <i class="fas fa-hospital"></i>
                        Manage Hospitals
                    </a>
                </li>
                <li class="nav-item">
                    <a href="manage_donors.php" class="nav-link">
                        <i class="fas fa-hand-holding-heart"></i>
                        Manage Donors
                    </a>
                </li>
                <li class="nav-item">
                    <a href="manage_recipients.php" class="nav-link">
                        <i class="fas fa-user-plus"></i>
                        Manage Recipients
                    </a>
                </li>
                <li class="nav-item">
                    <a href="analytics.php" class="nav-link">
                        <i class="fas fa-chart-line"></i>
                        Analytics
                    </a>
                </li>
                <li class="nav-item">
                    <a href="notifications.php" class="nav-link">
                        <i class="fas fa-bell"></i>
                        Notifications
                    </a>
                </li>
                <li class="nav-item">
                    <a href="settings.php" class="nav-link">
                        <i class="fas fa-cog"></i>
                        Settings
                    </a>
                </li>
                <li class="nav-item">
                    <a href="../admin_dashboard_logout.php" class="nav-link">
                        <i class="fas fa-sign-out-alt"></i>
                        Logout
                    </a>
                </li>
            </ul>
        </div>

        <!-- Main Content -->
        <div class="main-content">
            <!-- Pending Hospitals Section -->
            <div class="table-container">
                <h2>Pending Hospital Approvals (<span id="pending-hospitals-count"><?php echo count($pendingHospitals); ?></span>)</h2>
                <div class="table-responsive">
                    <table class="table" id="pending-hospitals-table">
                        <thead>
                            <tr>
                                <th>Hospital Name</th>
                                <th>Email</th>
                                <th>ODML ID</th>
                                <th>Actions</th>
                                <th>Details</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($pendingHospitals as $hospital): ?>
                            <tr id="hospital_row_<?php echo $hospital['hospital_id']; ?>">
                                <td><?php echo htmlspecialchars($hospital['hospital_name']); ?></td>
                                <td><?php echo htmlspecialchars($hospital['email']); ?></td>
                                <td>
                                    <div class="action-cell">
                                        <input type="text" 
                                               class="odml-input"
                                               id="odml_id_<?php echo $hospital['hospital_id']; ?>"
                                               value="<?php echo htmlspecialchars($hospital['odml_id'] ?? ''); ?>"
                                               placeholder="Enter ODML ID">
                                        <button class="update-btn" 
                                                data-hospital-id="<?php echo $hospital['hospital_id']; ?>">
                                            <i class="fas fa-save"></i> Update
                                        </button>
                                    </div>
                                </td>
                                <td>
                                    <div class="action-cell">
                                        <button class="approve-btn" onclick="updateHospitalStatus(<?php echo $hospital['hospital_id']; ?>, 'Approved')">
                                            <i class="fas fa-check"></i> Approve
                                        </button>
                                        <button class="reject-btn" onclick="updateHospitalStatus(<?php echo $hospital['hospital_id']; ?>, 'Rejected')">
                                            <i class="fas fa-times"></i> Reject
                                        </button>
                                    </div>
                                </td>
                                <td>
                                    <a href="view_hospital_details_in_pending.php?id=<?php echo $hospital['hospital_id']; ?>" 
                                       class="view-btn">
                                        <i class="fas fa-eye"></i> View Details
                                    </a>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
            
            <!-- Pending Donors Table -->
            <div class="table-container">
                <h2>Pending Donor Approvals (<span id="pending-donors-count"><?php echo count($pendingDonors); ?></span>)</h2>
                <div class="table-responsive">
                    <table class="table" id="pending-donors-table">
                        <thead>
                            <tr>
                                <th>Name</th>
                                <th>Email</th>
                                <th>Blood Type</th>
                                <th>Status</th>
                                <th>Actions</th>
                                <th>Details</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($pendingDonors as $donor): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($donor['name']); ?></td>
                                <td><?php echo htmlspecialchars($donor['email']); ?></td>
                                <td><?php echo htmlspecialchars($donor['blood_type']); ?></td>
                                <td><?php echo htmlspecialchars($donor['status']); ?></td>
                                <td>
                                    <button class="approve-btn" onclick="updateDonorStatus(<?php echo $donor['donor_id']; ?>, 'approved')">Approve</button>
                                    <button class="reject-btn" onclick="updateDonorStatus(<?php echo $donor['donor_id']; ?>, 'rejected')">Reject</button>
                                </td>
                                <td>
                                    <a href="view_all_details.php?type=donor&id=<?php echo $donor['donor_id']; ?>" 
                                       class="details-btn">View Details</a>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Pending Recipients Table -->
            <div class="table-container">
                <h2>Pending Recipient Approvals (<span id="pending-recipients-count"><?php echo count($pendingRecipients); ?></span>)</h2>
                <div class="table-responsive">
                    <table class="table" id="pending-recipients-table">
                        <thead>
                            <tr>
                                <th>Name</th>
                                <th>Email</th>
                                <th>Blood Type</th>
                                <th>Status</th>
                                <th>Actions</th>
                                <th>Details</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($pendingRecipients as $recipient): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($recipient['name']); ?></td>
                                <td><?php echo htmlspecialchars($recipient['email']); ?></td>
                                <td><?php echo htmlspecialchars($recipient['blood_type']); ?></td>
                                <td><?php echo htmlspecialchars($recipient['request_status']); ?></td>
                                <td>
                                    <button class="approve-btn" onclick="updateRecipientStatus(<?php echo $recipient['id']; ?>, 'approved')">Approve</button>
                                    <button class="reject-btn" onclick="updateRecipientStatus(<?php echo $recipient['id']; ?>, 'rejected')">Reject</button>
                                </td>
                                <td>
                                    <a href="view_all_details.php?type=recipient&id=<?php echo $recipient['id']; ?>" 
                                       class="details-btn">View Details</a>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
            <!-- Recent Matches Table -->
            <div class="table-container">
                <h2>Recent Organ Match Activities</h2>
                <div class="table-responsive">
                    <table class="table" id="recent-matches-table">
                        <thead>
                            <tr>
                                <th>Date</th>
                                <th>Donor</th>
                                <th>Recipient</th>
                                <th>Hospital</th>
                                <th>Organ Type</th>
                                <th>Status</th>
                                <th>Urgency</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php 
                            require_once '../../backend/php/organ_matches.php';
                            $recent_matches = getOrganMatches($conn, ['limit' => 5]);
                            foreach ($recent_matches as $match): 
                            ?>
                            <tr>
                                <td><?php echo date('M d, Y', strtotime($match['match_date'])); ?></td>
                                <td><?php echo htmlspecialchars($match['donor_name']); ?></td>
                                <td><?php echo htmlspecialchars($match['recipient_name']); ?></td>
                                <td><?php echo htmlspecialchars($match['hospital_name']); ?></td>
                                <td><?php echo htmlspecialchars($match['organ_type']); ?></td>
                                <td>
                                    <span class="status-badge status-<?php echo strtolower($match['status']); ?>">
                                        <?php echo $match['status']; ?>
                                    </span>
                                </td>
                                <td>
                                    <span class="urgency-badge urgency-<?php echo strtolower($match['urgency_level']); ?>">
                                        <?php echo $match['urgency_level']; ?>
                                    </span>
                                </td>
                                <td>
                                    <button class="btn-action btn-view" onclick="viewMatch(<?php echo $match['match_id']; ?>)">
                                        <i class="fas fa-eye"></i>
                                    </button>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <!-- Notification Bell -->
    <div class="notification-bell" onclick="toggleNotifications()">
        <i class="fas fa-bell"></i>
        <span class="badge" id="notification-count">0</span>
    </div>

    <!-- Notification Popup -->
    <div class="notification-popup" id="notification-popup">
        <div class="p-3">
            <h4>Notifications</h4>
            <div id="notifications-container">
                <?php foreach ($notifications as $notification): ?>
                    <div class="notification-item">
                        <span class="notification-time"><?php echo isset($notification['formatted_time']) ? $notification['formatted_time'] : date('F d, Y \a\t h:i A', strtotime($notification['created_at'])); ?></span>
                        <p><?php echo $notification['message']; ?></p>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
    </div>

    <!-- JavaScript Code -->
    <script>
        $(document).ready(function() {
            // Add event listeners for all dynamic elements
            $('.update-btn').on('click', function(e) {
                e.preventDefault();
                const hospitalId = $(this).data('hospital-id');
                updateHospitalODMLID(hospitalId);
            });
        });

        function updateHospitalODMLID(hospitalId) {
            const odmlId = $(`#odml_id_${hospitalId}`).val();
            
            if (confirm('Are you sure you want to update the ODML ID?')) {
                $.ajax({
                    url: '../../backend/php/update_odml_id.php',
                    method: 'POST',
                    data: {
                        type: 'hospital',
                        id: hospitalId,
                        odml_id: odmlId
                    },
                    success: function(response) {
                        try {
                            const data = typeof response === 'string' ? JSON.parse(response) : response;
                            if (data.success) {
                                alert('ODML ID updated successfully');
                            } else {
                                alert('Failed to update ODML ID: ' + (data.message || 'Unknown error'));
                            }
                        } catch (e) {
                            console.error('Error parsing response:', e);
                            alert('Error updating ODML ID');
                        }
                    },
                    error: function(xhr, status, error) {
                        console.error('Error:', error);
                        alert('Error updating ODML ID');
                    }
                });
            }
        }

        function updateHospitalStatus(hospitalId, status) {
            if (confirm(`Are you sure you want to ${status.toLowerCase()} this hospital?`)) {
                $.ajax({
                    url: '../../backend/php/update_hospital_status.php',
                    method: 'POST',
                    data: {
                        hospital_id: hospitalId,
                        status: status
                    },
                    success: function(response) {
                        try {
                            const data = typeof response === 'string' ? JSON.parse(response) : response;
                            if (data.success) {
                                alert(`Hospital ${status.toLowerCase()} successfully`);
                                $(`#hospital_row_${hospitalId}`).fadeOut(400, function() {
                                    $(this).remove();
                                    const counter = $('#pending-hospitals-count');
                                    counter.text(parseInt(counter.text()) - 1);
                                });
                            } else {
                                alert('Failed to update status: ' + (data.message || 'Unknown error'));
                            }
                        } catch (e) {
                            console.error('Error parsing response:', e);
                            alert('Error updating hospital status');
                        }
                    },
                    error: function(xhr, status, error) {
                        console.error('Error:', error);
                        alert('Error updating hospital status');
                    }
                });
            }
        }
    </script>
</body>
</html>
