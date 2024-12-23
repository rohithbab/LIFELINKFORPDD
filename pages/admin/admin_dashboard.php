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
    <link rel="stylesheet" href="../../assets/css/notification-bell.css">
    
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

        .stats-cards {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 20px;
            margin-bottom: 30px;
        }

        .stat-card {
            background: white;
            padding: 20px;
            border-radius: 10px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
            display: flex;
            align-items: center;
            gap: 15px;
            transition: transform 0.3s ease, box-shadow 0.3s ease;
        }

        .stat-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 4px 8px rgba(0,0,0,0.15);
        }

        .stat-card i {
            font-size: 2.5rem;
        }

        .stat-card:nth-child(1) i { /* Total Hospitals */
            color: #2196F3; /* Blue */
        }
        
        .stat-card:nth-child(2) i { /* Total Donors */
            color: #4CAF50; /* Green */
        }
        
        .stat-card:nth-child(3) i { /* Total Recipients */
            color: #9C27B0; /* Purple */
        }
        
        .stat-card:nth-child(4) i { /* Pending Hospitals */
            color: #FF9800; /* Orange */
        }
        
        .stat-card:nth-child(5) i { /* Successful Matches */
            color: #00BCD4; /* Cyan */
        }
        
        .stat-card:nth-child(6) i { /* Pending Matches */
            color: #F44336; /* Red */
        }
        
        .stat-card:nth-child(7) i { /* Urgent Recipients */
            color: #E91E63; /* Pink */
        }

        .stat-card:nth-child(1):hover { border-left: 4px solid #2196F3; }
        .stat-card:nth-child(2):hover { border-left: 4px solid #4CAF50; }
        .stat-card:nth-child(3):hover { border-left: 4px solid #9C27B0; }
        .stat-card:nth-child(4):hover { border-left: 4px solid #FF9800; }
        .stat-card:nth-child(5):hover { border-left: 4px solid #00BCD4; }
        .stat-card:nth-child(6):hover { border-left: 4px solid #F44336; }
        .stat-card:nth-child(7):hover { border-left: 4px solid #E91E63; }

        .stat-info h3 {
            margin: 0;
            font-size: 1rem;
            color: #666;
        }

        .stat-info p {
            margin: 5px 0 0;
            font-size: 1.5rem;
            font-weight: bold;
            color: #333;
        }
        
        /* Added styles for smaller buttons */
        .btn-sm {
            padding: 6px 12px;
            font-size: 14px;
        }
        
        .btn-sm .fas {
            font-size: 1rem;
        }

        .btn-approve {
            background: linear-gradient(135deg, #4CAF50, #45a049);
            color: white;
            border: none;
            transition: all 0.3s ease;
        }

        .btn-approve:hover {
            background: linear-gradient(135deg, #45a049, #3d8b40);
            transform: translateY(-1px);
            box-shadow: 0 2px 4px rgba(0,0,0,0.2);
        }

        .btn-reject {
            background: linear-gradient(135deg, #f44336, #e53935);
            color: white;
            border: none;
            transition: all 0.3s ease;
        }

        .btn-reject:hover {
            background: linear-gradient(135deg, #e53935, #d32f2f);
            transform: translateY(-1px);
            box-shadow: 0 2px 4px rgba(0,0,0,0.2);
        }
        
        .odml-input {
            padding: 6px 10px;
            border: 1px solid #ddd;
            border-radius: 4px;
            font-size: 14px;
            width: 120px;
            margin-right: 5px;
            transition: all 0.3s ease;
        }

        .odml-input:focus {
            border-color: #2196F3;
            box-shadow: 0 0 5px rgba(33, 150, 243, 0.3);
            outline: none;
        }

        .update-odml-btn {
            background: linear-gradient(135deg, #2196F3, #1976D2);
            color: white;
            border: none;
            padding: 6px 12px;
            border-radius: 4px;
            cursor: pointer;
            font-size: 14px;
            transition: all 0.3s ease;
        }

        .update-odml-btn:hover {
            background: linear-gradient(135deg, #1976D2, #1565C0);
            transform: translateY(-1px);
            box-shadow: 0 2px 4px rgba(0,0,0,0.2);
        }

        .update-odml-btn i {
            margin-right: 4px;
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
            <!-- Stats Cards Section -->
            <div class="stats-cards">
                <div class="stat-card">
                    <i class="fas fa-hospital"></i>
                    <div class="stat-info">
                        <h3>Total Hospitals</h3>
                        <p><?php echo $stats['total_hospitals']; ?></p>
                    </div>
                </div>
                <div class="stat-card">
                    <i class="fas fa-user-plus"></i>
                    <div class="stat-info">
                        <h3>Total Donors</h3>
                        <p><?php echo $stats['total_donors']; ?></p>
                    </div>
                </div>
                <div class="stat-card">
                    <i class="fas fa-procedures"></i>
                    <div class="stat-info">
                        <h3>Total Recipients</h3>
                        <p><?php echo $stats['total_recipients']; ?></p>
                    </div>
                </div>
                <div class="stat-card">
                    <i class="fas fa-clock"></i>
                    <div class="stat-info">
                        <h3>Pending Hospitals</h3>
                        <p><?php echo $stats['pending_hospitals']; ?></p>
                    </div>
                </div>
                <div class="stat-card">
                    <i class="fas fa-check-circle"></i>
                    <div class="stat-info">
                        <h3>Successful Matches</h3>
                        <p><?php echo $stats['successful_matches']; ?></p>
                    </div>
                </div>
                <div class="stat-card">
                    <i class="fas fa-hourglass-half"></i>
                    <div class="stat-info">
                        <h3>Pending Matches</h3>
                        <p><?php echo $stats['pending_matches']; ?></p>
                    </div>
                </div>
                <div class="stat-card">
                    <i class="fas fa-exclamation-circle"></i>
                    <div class="stat-info">
                        <h3>Urgent Recipients</h3>
                        <p><?php echo $stats['urgent_recipients']; ?></p>
                    </div>
                </div>
            </div>

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
                                <th>ODML ID</th>
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
                                <td>
                                    <div class="odml-container">
                                        <input type="text" class="odml-input" 
                                               value="<?php echo htmlspecialchars($donor['odml_id'] ?? ''); ?>" 
                                               placeholder="Enter ODML ID">
                                        <button class="update-btn" onclick="updateDonorODMLID('<?php echo htmlspecialchars($donor['donor_id']); ?>', this)">
                                            <i class="fas fa-save"></i> Update
                                        </button>
                                    </div>
                                </td>
                                <td>
                                    <button class="approve-btn" onclick="updateDonorStatus('<?php echo htmlspecialchars($donor['donor_id']); ?>', 'Approved')">
                                        <i class="fas fa-check"></i> Approve
                                    </button>
                                    <button class="reject-btn" onclick="updateDonorStatus('<?php echo htmlspecialchars($donor['donor_id']); ?>', 'Rejected')">
                                        <i class="fas fa-times"></i> Reject
                                    </button>
                                </td>
                                <td>
                                    <a href="view_donor_details_in_pending.php?id=<?php echo htmlspecialchars($donor['donor_id']); ?>" 
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
                                <th>ODML ID</th>
                                <th>Urgency Level</th>
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
                                <td>
                                    <div style="display: flex; align-items: center;">
                                        <input type="text" class="odml-input" 
                                               value="<?php echo htmlspecialchars($recipient['odml_id'] ?? ''); ?>" 
                                               placeholder="Enter ODML ID"
                                               id="odml-<?php echo $recipient['id']; ?>">
                                        <button class="update-odml-btn" onclick="updateRecipientODML(<?php echo $recipient['id']; ?>)">
                                            <i class="fas fa-sync-alt"></i> Update
                                        </button>
                                    </div>
                                </td>
                                <td><?php echo htmlspecialchars($recipient['urgency'] ?? 'Not Set'); ?></td>
                                <td class="action-cell">
                                    <button class="btn btn-sm btn-approve" onclick="updateRecipientStatus(<?php echo $recipient['id']; ?>, 'accepted')" style="margin-right: 5px;">
                                        <i class="fas fa-check"></i> Approve
                                    </button>
                                    <button class="btn btn-sm btn-reject" onclick="updateRecipientStatus(<?php echo $recipient['id']; ?>, 'rejected')">
                                        <i class="fas fa-times"></i> Reject
                                    </button>
                                </td>
                                <td>
                                    <a href="view_recipient_details_in_pending.php?id=<?php echo htmlspecialchars($recipient['id']); ?>" 
                                       class="view-btn">
                                        <i class="fas fa-eye"></i> View
                                    </a>
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
    <div class="notification-bell-container">
        <div class="notification-bell" onclick="toggleNotifications()">
            <i class="fas fa-bell"></i>
            <span class="notification-count" style="display: none;">0</span>
        </div>
        <div class="notification-dropdown">
            <div class="notification-header">
                Notifications
            </div>
            <div class="notification-list">
                <!-- Notifications will be inserted here -->
            </div>
            <div class="notification-footer">
                <a href="notifications.php">See All Notifications</a>
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
        
        function updateDonorStatus(donorId, status) {
            // Capitalize first letter of status
            status = status.charAt(0).toUpperCase() + status.slice(1).toLowerCase();
            
            if (confirm(`Are you sure you want to ${status.toLowerCase()} this donor?`)) {
                $.ajax({
                    url: '../../backend/php/update_donor_status.php',
                    method: 'POST',
                    data: JSON.stringify({
                        donor_id: donorId,
                        status: status
                    }),
                    contentType: 'application/json',
                    dataType: 'json',
                    success: function(response) {
                        console.log('Response:', response); // Debug log
                        if (response.success) {
                            alert(`Donor ${status.toLowerCase()} successfully`);
                            location.reload();
                        } else {
                            alert('Failed to update status: ' + (response.message || 'Unknown error'));
                        }
                    },
                    error: function(xhr, status, error) {
                        console.error('Error:', error);
                        console.error('Response:', xhr.responseText); // Debug log
                        alert('Error updating donor status. Please check the console for details.');
                    }
                });
            }
        }

        function updateDonorODMLID(donorId, button) {
            const odmlId = $(button).prev('input').val();
            
            if (!odmlId) {
                alert('Please enter an ODML ID');
                return;
            }
            
            if (confirm('Are you sure you want to update the ODML ID?')) {
                $.ajax({
                    url: '../../backend/php/update_odml_id.php',
                    method: 'POST',
                    data: {
                        type: 'donor',
                        id: donorId,
                        odml_id: odmlId
                    },
                    success: function(response) {
                        try {
                            const data = typeof response === 'string' ? JSON.parse(response) : response;
                            if (data.success) {
                                alert('ODML ID updated successfully');
                                $(button).prev('input').prop('disabled', true);
                                $(button).prop('disabled', true);
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

        function updateRecipientStatus(recipientId, status) {
            // Capitalize first letter of status
            status = status.charAt(0).toUpperCase() + status.slice(1).toLowerCase();
            
            if (confirm(`Are you sure you want to ${status.toLowerCase()} this recipient?`)) {
                $.ajax({
                    url: '../../backend/php/update_recipient_status.php',
                    method: 'POST',
                    data: JSON.stringify({
                        recipient_id: recipientId,
                        status: status
                    }),
                    contentType: 'application/json',
                    dataType: 'json',
                    success: function(response) {
                        console.log('Response:', response);
                        if (response.success) {
                            alert(`Recipient ${status.toLowerCase()} successfully`);
                            location.reload();
                        } else {
                            alert('Failed to update status: ' + (response.message || 'Unknown error'));
                        }
                    },
                    error: function(xhr, status, error) {
                        console.error('Error:', error);
                        console.error('Response:', xhr.responseText);
                        alert('Error updating recipient status. Please check the console for details.');
                    }
                });
            }
        }

        function updateRecipientODML(recipientId) {
            const odmlId = document.getElementById(`odml-${recipientId}`).value.trim();
            
            if (!odmlId) {
                alert('Please enter an ODML ID');
                return;
            }

            if (confirm('Are you sure you want to update the ODML ID?')) {
                $.ajax({
                    url: '../../backend/php/update_recipient_odml.php',
                    method: 'POST',
                    data: JSON.stringify({
                        recipient_id: recipientId,
                        odml_id: odmlId
                    }),
                    contentType: 'application/json',
                    dataType: 'json',
                    success: function(response) {
                        console.log('Response:', response);
                        if (response.success) {
                            alert('ODML ID updated successfully');
                        } else {
                            alert('Failed to update ODML ID: ' + (response.message || 'Unknown error'));
                        }
                    },
                    error: function(xhr, status, error) {
                        console.error('Error:', error);
                        console.error('Response:', xhr.responseText);
                        alert('Error updating ODML ID. Please check the console for details.');
                    }
                });
            }
        }
    </script>
    <script src="../../assets/js/notifications.js"></script>
</body>
</html>
