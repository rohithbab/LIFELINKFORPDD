<?php
session_start();
require_once '../../backend/php/connection.php';

// Check if hospital is logged in
if (!isset($_SESSION['hospital_logged_in']) || !$_SESSION['hospital_logged_in']) {
    header("Location: ../../pages/hospital_login.php");
    exit();
}

$hospital_name = $_SESSION['hospital_name'];
$hospital_email = $_SESSION['hospital_email'];
$odml_id = $_SESSION['odml_id'];
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Hospital Dashboard</title>
    <!-- Reset default styles -->
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
    </style>
    <link rel="stylesheet" href="../../assets/css/hospital-dashboard.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
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
                    <li class="active">
                        <a href="#">
                            <i class="fas fa-home"></i>
                            <span>Dashboard</span>
                        </a>
                    </li>
                    <li>
                        <a href="#">
                            <i class="fas fa-user-plus"></i>
                            <span>Manage Donors</span>
                        </a>
                    </li>
                    <li>
                        <a href="#">
                            <i class="fas fa-hospital"></i>
                            <span>Manage Hospitals</span>
                        </a>
                    </li>
                    <li>
                        <a href="#">
                            <i class="fas fa-search"></i>
                            <span>Search Organ</span>
                        </a>
                    </li>
                    <li>
                        <a href="#">
                            <i class="fas fa-share"></i>
                            <span>Forward Matches to Admin</span>
                        </a>
                    </li>
                    <li>
                        <a href="#">
                            <i class="fas fa-chart-bar"></i>
                            <span>Analytics</span>
                        </a>
                    </li>
                    <li>
                        <a href="#">
                            <i class="fas fa-bell"></i>
                            <span>Notifications</span>
                        </a>
                    </li>
                    <li>
                        <a href="../../pages/hospital_login.php">
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
                    <h1>Welcome, <?php echo htmlspecialchars($hospital_name); ?></h1>
                </div>
                <div class="header-right">
                    <button onclick="window.location.href='hospital_profile.php'" class="profile-button">
                        <i class="fas fa-user-circle"></i>
                        <span>My Profile</span>
                    </button>
                </div>
            </div>

            <!-- Pending Donor Approvals -->
            <div class="table-section">
                <div class="section-header">
                    <h2>Pending Donor Approvals</h2>
                </div>
                <div class="table-responsive">
                    <table class="data-table">
                        <thead>
                            <tr>
                                <th>Donor Name</th>
                                <th>Organ Type</th>
                                <th>Blood Group</th>
                                <th>Location</th>
                                <th>Request Date</th>
                                <th>Status</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            try {
                                $donor_query = "
                                    SELECT d.full_name as donor_name, 
                                           dr.organ_type,
                                           d.blood_group,
                                           d.address as location,
                                           dr.request_date,
                                           dr.status
                                    FROM donor_requests dr
                                    JOIN donors d ON dr.donor_id = d.id
                                    WHERE dr.hospital_id = :hospital_id
                                    ORDER BY dr.request_date DESC";
                                
                                $stmt = $conn->prepare($donor_query);
                                $stmt->bindParam(':hospital_id', $_SESSION['hospital_id']);
                                $stmt->execute();
                                $donor_requests = $stmt->fetchAll(PDO::FETCH_ASSOC);

                                if (count($donor_requests) > 0) {
                                    foreach ($donor_requests as $request) {
                                        ?>
                                        <tr>
                                            <td><?php echo htmlspecialchars($request['donor_name']); ?></td>
                                            <td><?php echo htmlspecialchars($request['organ_type']); ?></td>
                                            <td><?php echo htmlspecialchars($request['blood_group']); ?></td>
                                            <td><?php echo htmlspecialchars($request['location']); ?></td>
                                            <td><?php echo date('M d, Y', strtotime($request['request_date'])); ?></td>
                                            <td>
                                                <span class="status-badge <?php echo strtolower($request['status']); ?>">
                                                    <?php echo ucfirst($request['status']); ?>
                                                </span>
                                            </td>
                                            <td class="actions">
                                                <?php if ($request['status'] === 'pending'): ?>
                                                    <button onclick="handleDonorRequest(<?php echo $request['id']; ?>, 'approve')" class="btn-action approve">
                                                        <i class="fas fa-check"></i> Approve
                                                    </button>
                                                    <button onclick="handleDonorRequest(<?php echo $request['id']; ?>, 'reject')" class="btn-action reject">
                                                        <i class="fas fa-times"></i> Reject
                                                    </button>
                                                <?php endif; ?>
                                            </td>
                                        </tr>
                                        <?php
                                    }
                                } else {
                                    echo '<tr><td colspan="7" class="no-data">No pending donor requests</td></tr>';
                                }
                            } catch (PDOException $e) {
                                echo '<tr><td colspan="7" class="no-data">Error loading donor requests</td></tr>';
                            }
                            ?>
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Pending Recipient Approvals -->
            <div class="table-section">
                <div class="section-header">
                    <h2>Pending Recipient Approvals</h2>
                </div>
                <div class="table-responsive">
                    <table class="data-table">
                        <thead>
                            <tr>
                                <th>Recipient Name</th>
                                <th>Required Organ</th>
                                <th>Blood Group</th>
                                <th>Priority Level</th>
                                <th>Location</th>
                                <th>Request Date</th>
                                <th>Status</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            try {
                                $recipient_query = "
                                    SELECT r.full_name as recipient_name,
                                           rr.required_organ,
                                           r.blood_type as blood_group,
                                           rr.priority_level,
                                           r.address as location,
                                           rr.request_date,
                                           rr.status
                                    FROM recipient_requests rr
                                    JOIN recipient_registration r ON rr.recipient_id = r.id
                                    WHERE rr.hospital_id = :hospital_id
                                    ORDER BY rr.request_date DESC";
                                
                                $stmt = $conn->prepare($recipient_query);
                                $stmt->bindParam(':hospital_id', $_SESSION['hospital_id']);
                                $stmt->execute();
                                $recipient_requests = $stmt->fetchAll(PDO::FETCH_ASSOC);

                                if (count($recipient_requests) > 0) {
                                    foreach ($recipient_requests as $request) {
                                        ?>
                                        <tr>
                                            <td><?php echo htmlspecialchars($request['recipient_name']); ?></td>
                                            <td><?php echo htmlspecialchars($request['required_organ']); ?></td>
                                            <td><?php echo htmlspecialchars($request['blood_group']); ?></td>
                                            <td>
                                                <span class="priority-badge <?php echo strtolower($request['priority_level']); ?>">
                                                    <?php echo $request['priority_level']; ?>
                                                </span>
                                            </td>
                                            <td><?php echo htmlspecialchars($request['location']); ?></td>
                                            <td><?php echo date('M d, Y', strtotime($request['request_date'])); ?></td>
                                            <td>
                                                <span class="status-badge <?php echo strtolower($request['status']); ?>">
                                                    <?php echo ucfirst($request['status']); ?>
                                                </span>
                                            </td>
                                            <td class="actions">
                                                <?php if ($request['status'] === 'pending'): ?>
                                                    <button onclick="handleRecipientRequest(<?php echo $request['id']; ?>, 'approve')" class="btn-action approve">
                                                        <i class="fas fa-check"></i> Approve
                                                    </button>
                                                    <button onclick="handleRecipientRequest(<?php echo $request['id']; ?>, 'reject')" class="btn-action reject">
                                                        <i class="fas fa-times"></i> Reject
                                                    </button>
                                                <?php endif; ?>
                                            </td>
                                        </tr>
                                        <?php
                                    }
                                } else {
                                    echo '<tr><td colspan="8" class="no-data">No pending recipient requests</td></tr>';
                                }
                            } catch (PDOException $e) {
                                echo '<tr><td colspan="8" class="no-data">Error loading recipient requests</td></tr>';
                            }
                            ?>
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Rejection Modal -->
            <div id="rejectionModal" class="modal">
                <div class="modal-content">
                    <h3>Rejection Reason</h3>
                    <textarea id="rejectionReason" placeholder="Please provide a reason for rejection"></textarea>
                    <div class="modal-actions">
                        <button onclick="submitRejection()" class="btn-action approve">Submit</button>
                        <button onclick="closeRejectionModal()" class="btn-action reject">Cancel</button>
                    </div>
                </div>
            </div>

            <script>
                let currentRequestId = null;
                let currentRequestType = null;

                function handleDonorRequest(requestId, action) {
                    currentRequestId = requestId;
                    currentRequestType = 'donor';
                    if (action === 'reject') {
                        openRejectionModal();
                    } else {
                        updateRequestStatus(requestId, action, 'donor');
                    }
                }

                function handleRecipientRequest(requestId, action) {
                    currentRequestId = requestId;
                    currentRequestType = 'recipient';
                    if (action === 'reject') {
                        openRejectionModal();
                    } else {
                        updateRequestStatus(requestId, action, 'recipient');
                    }
                }

                function openRejectionModal() {
                    document.getElementById('rejectionModal').style.display = 'flex';
                }

                function closeRejectionModal() {
                    document.getElementById('rejectionModal').style.display = 'none';
                    document.getElementById('rejectionReason').value = '';
                }

                function submitRejection() {
                    const reason = document.getElementById('rejectionReason').value;
                    if (!reason.trim()) {
                        alert('Please provide a reason for rejection');
                        return;
                    }
                    updateRequestStatus(currentRequestId, 'reject', currentRequestType, reason);
                    closeRejectionModal();
                }

                function updateRequestStatus(requestId, action, type, reason = '') {
                    const formData = new FormData();
                    formData.append('request_id', requestId);
                    formData.append('action', action);
                    formData.append('type', type);
                    if (reason) formData.append('reason', reason);

                    fetch('../../backend/php/update_request_status.php', {
                        method: 'POST',
                        body: formData
                    })
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            location.reload(); // Reload to show updated status
                        } else {
                            alert('Error: ' + data.message);
                        }
                    })
                    .catch(error => {
                        console.error('Error:', error);
                        alert('An error occurred while updating the request');
                    });
                }
            </script>
        </main>
    </div>
</body>
</html>

<?php
try {
    $donor_query = "
        SELECT d.full_name as donor_name, 
               dr.organ_type,
               d.blood_group,
               d.address as location,
               dr.request_date,
               dr.status
        FROM donor_requests dr
        JOIN donors d ON dr.donor_id = d.id
        WHERE dr.hospital_id = :hospital_id
        ORDER BY dr.request_date DESC";
    
    $stmt = $conn->prepare($donor_query);
    $stmt->bindParam(':hospital_id', $_SESSION['hospital_id']);
    $stmt->execute();
    $donor_requests = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    $donor_requests = array();
}

try {
    $recipient_query = "
        SELECT r.full_name as recipient_name,
               rr.required_organ,
               r.blood_type as blood_group,
               rr.priority_level,
               r.address as location,
               rr.request_date,
               rr.status
        FROM recipient_requests rr
        JOIN recipient_registration r ON rr.recipient_id = r.id
        WHERE rr.hospital_id = :hospital_id
        ORDER BY rr.request_date DESC";
    
    $stmt = $conn->prepare($recipient_query);
    $stmt->bindParam(':hospital_id', $_SESSION['hospital_id']);
    $stmt->execute();
    $recipient_requests = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    $recipient_requests = array();
}
?>
