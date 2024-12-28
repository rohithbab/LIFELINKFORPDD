<?php
session_start();
require_once '../../config/db_connect.php';

// Check if hospital is logged in
if (!isset($_SESSION['hospital_logged_in']) || !$_SESSION['hospital_logged_in']) {
    header("Location: ../../pages/hospital_login.php");
    exit();
}

$hospital_id = $_SESSION['hospital_id']; // Make sure we have hospital_id
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
    <style>
        .status-select {
            padding: 8px;
            border: 1px solid #ddd;
            border-radius: 4px;
            background-color: white;
            cursor: pointer;
            min-width: 120px;
        }

        .status-select option {
            padding: 8px;
        }

        .status-select option[value="Pending"] {
            color: #f0ad4e;
        }

        .status-select option[value="Approved"] {
            color: #5cb85c;
        }

        .status-select option[value="Rejected"] {
            color: #d9534f;
        }
        
        .btn-approve, .btn-reject {
            padding: 6px 12px;
            margin: 0 4px;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            font-size: 14px;
            display: inline-flex;
            align-items: center;
            gap: 4px;
        }

        .btn-approve {
            background-color: #28a745;
            color: white;
        }

        .btn-reject {
            background-color: #dc3545;
            color: white;
        }

        .btn-approve:hover {
            background-color: #218838;
        }

        .btn-reject:hover {
            background-color: #c82333;
        }

        .actions {
            white-space: nowrap;
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
                        <a href="hospital_dashboard.php" class="active">
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
                                // Debug information
                                error_log("Hospital ID: " . $hospital_id);
                                
                                // Fetch pending donor requests
                                $stmt = $conn->prepare("
                                    SELECT hda.*, d.name as donor_name, d.blood_group 
                                    FROM hospital_donor_approvals hda
                                    JOIN donor d ON hda.donor_id = d.donor_id
                                    WHERE hda.hospital_id = ? AND hda.status = 'Pending'
                                    ORDER BY hda.request_date DESC
                                ");
                                $stmt->execute([$hospital_id]);
                                $donor_requests = $stmt->fetchAll(PDO::FETCH_ASSOC);
                                
                                error_log("Number of requests found: " . count($donor_requests));
                                if (count($donor_requests) === 0) {
                                    error_log("No requests found for hospital_id: " . $hospital_id);
                                }
                                if (count($donor_requests) > 0) {
                                    foreach ($donor_requests as $request) {
                                        ?>
                                        <tr>
                                            <td><?php echo htmlspecialchars($request['donor_name']); ?></td>
                                            <td><?php echo htmlspecialchars($request['organ_type']); ?></td>
                                            <td>
                                                <span class="blood-badge">
                                                    <?php echo htmlspecialchars($request['blood_group']); ?>
                                                </span>
                                            </td>
                                            <td><?php echo htmlspecialchars($request['location']); ?></td>
                                            <td><?php echo date('M d, Y', strtotime($request['request_date'])); ?></td>
                                            <td>
                                                <span class="status-badge <?php echo strtolower($request['status']); ?>">
                                                    <?php echo htmlspecialchars($request['status']); ?>
                                                </span>
                                            </td>
                                            <td class="actions">
                                                <button onclick="updateDonorStatus(<?php echo $request['approval_id']; ?>, 'Approved')" class="btn-approve">
                                                    <i class="fas fa-check"></i> Approve
                                                </button>
                                                <button onclick="updateDonorStatus(<?php echo $request['approval_id']; ?>, 'Rejected')" class="btn-reject">
                                                    <i class="fas fa-times"></i> Reject
                                                </button>
                                            </td>
                                        </tr>
                                        <?php
                                    }
                                } else {
                                    ?>
                                    <tr>
                                        <td colspan="7" class="no-data">No pending donor approvals</td>
                                    </tr>
                                    <?php
                                }
                            } catch (PDOException $e) {
                                echo "<tr><td colspan='7' class='error'>Error fetching donor requests</td></tr>";
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
                                $stmt->bindParam(':hospital_id', $hospital_id);
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

            <script>
                function viewDonorDetails(details) {
                    // Create and show modal with donor details
                    const modal = document.createElement('div');
                    modal.className = 'modal';
                    modal.innerHTML = `
                        <div class="modal-content">
                            <div class="modal-header">
                                <h3>Donor Details</h3>
                                <button onclick="this.closest('.modal').remove()" class="close-btn">&times;</button>
                            </div>
                            <div class="modal-body">
                                <div class="donor-info">
                                    <div class="info-group">
                                        <label>Name:</label>
                                        <span>${details.donor_name}</span>
                                    </div>
                                    <div class="info-group">
                                        <label>Blood Group:</label>
                                        <span class="blood-badge">${details.blood_group}</span>
                                    </div>
                                    <div class="info-group">
                                        <label>Organ Type:</label>
                                        <span>${details.organ_type}</span>
                                    </div>
                                    <div class="info-group">
                                        <label>Location:</label>
                                        <span>${details.location}</span>
                                    </div>
                                    <div class="info-group">
                                        <label>Phone:</label>
                                        <span>${details.phone}</span>
                                    </div>
                                    <div class="info-group">
                                        <label>Email:</label>
                                        <span>${details.email}</span>
                                    </div>
                                    <div class="info-group">
                                        <label>Medical Conditions:</label>
                                        <span>${details.medical_conditions || 'None'}</span>
                                    </div>
                                    <div class="info-group">
                                        <label>Documents:</label>
                                        <div class="document-links">
                                            ${details.medical_reports ? 
                                                `<a href="../../uploads/hospitals_donors/medical_reports/${details.medical_reports}" target="_blank" class="doc-link">
                                                    <i class="fas fa-file-medical"></i> Medical Reports
                                                </a>` : ''}
                                            ${details.id_proof ? 
                                                `<a href="../../uploads/hospitals_donors/id_proof/${details.id_proof}" target="_blank" class="doc-link">
                                                    <i class="fas fa-id-card"></i> ID Proof
                                                </a>` : ''}
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    `;
                    document.body.appendChild(modal);
                }

                function approveDonor(approvalId) {
                    if (confirm('Are you sure you want to approve this donor request?')) {
                        fetch('../../ajax/handle_donor_approval.php', {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/x-www-form-urlencoded',
                            },
                            body: `approval_id=${approvalId}&action=approve`
                        })
                        .then(response => response.json())
                        .then(data => {
                            if (data.success) {
                                alert('Donor request approved successfully!');
                                location.reload();
                            } else {
                                alert('Error: ' + data.error);
                            }
                        })
                        .catch(error => {
                            alert('Error processing request');
                        });
                    }
                }

                function showRejectionModal(approvalId) {
                    const modal = document.getElementById('rejectionModal');
                    const form = modal.querySelector('form');
                    form.onsubmit = (e) => {
                        e.preventDefault();
                        const reason = form.querySelector('textarea').value;
                        rejectDonor(approvalId, reason);
                    };
                    modal.style.display = 'block';
                }

                function rejectDonor(approvalId, reason) {
                    fetch('../../ajax/handle_donor_approval.php', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/x-www-form-urlencoded',
                        },
                        body: `approval_id=${approvalId}&action=reject&reason=${encodeURIComponent(reason)}`
                    })
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            alert('Donor request rejected successfully!');
                            location.reload();
                        } else {
                            alert('Error: ' + data.error);
                        }
                    })
                    .catch(error => {
                        alert('Error processing request');
                    });
                }

                // Close modal when clicking outside
                window.onclick = function(event) {
                    if (event.target.className === 'modal') {
                        event.target.remove();
                    }
                }
            </script>

            <script>
                function updateDonorStatus(approvalId, status) {
                    const confirmMessage = status === 'Approved' ? 
                        'Are you sure you want to approve this donor?' : 
                        'Are you sure you want to reject this donor?';
                        
                    if (confirm(confirmMessage)) {
                        fetch('../../ajax/update_donor_status.php', {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/x-www-form-urlencoded',
                            },
                            body: `approval_id=${approvalId}&status=${status}`
                        })
                        .then(response => response.json())
                        .then(data => {
                            if (data.success) {
                                location.reload(); // Reload to show updated status
                            } else {
                                alert('Error: ' + data.error);
                            }
                        })
                        .catch(error => {
                            alert('Error updating status');
                        });
                    }
                }
            </script>
        </main>
    </div>
</body>
</html>

<?php
try {
    // Debug information
    error_log("Hospital ID: " . $hospital_id);
    
    // Fetch pending donor requests
    $stmt = $conn->prepare("
        SELECT hda.*, d.name as donor_name, d.blood_group 
        FROM hospital_donor_approvals hda
        JOIN donor d ON hda.donor_id = d.donor_id
        WHERE hda.hospital_id = ? AND hda.status = 'Pending'
        ORDER BY hda.request_date DESC
    ");
    $stmt->execute([$hospital_id]);
    $donor_requests = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    error_log("Number of requests found: " . count($donor_requests));
    if (count($donor_requests) === 0) {
        error_log("No requests found for hospital_id: " . $hospital_id);
    }
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
    $stmt->bindParam(':hospital_id', $hospital_id);
    $stmt->execute();
    $recipient_requests = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    $recipient_requests = array();
}
?>
