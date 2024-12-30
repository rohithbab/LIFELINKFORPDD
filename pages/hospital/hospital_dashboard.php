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
        
        /* Modern Table Styling */
        .table-container {
            background: white;
            border-radius: 15px;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.1);
            padding: 25px;
            margin: 20px 0;
        }

        .table-responsive {
            margin-top: 20px;
            border-radius: 12px;
            overflow: hidden;
        }

        .modern-table {
            width: 100%;
            border-collapse: separate;
            border-spacing: 0;
            background: white;
        }

        .modern-table th {
            background: linear-gradient(45deg, #20bf55, #01baef);
            color: white;
            padding: 15px;
            font-weight: 600;
            text-transform: uppercase;
            font-size: 0.9rem;
            letter-spacing: 0.5px;
        }

        .modern-table th:first-child {
            border-top-left-radius: 10px;
        }

        .modern-table th:last-child {
            border-top-right-radius: 10px;
        }

        .modern-table td {
            padding: 15px;
            border-bottom: 1px solid #e9ecef;
            color: #2C3E50;
            font-size: 0.95rem;
            vertical-align: middle;
        }

        .modern-table tr:hover {
            background-color: #f8f9fa;
            transform: scale(1.01);
            transition: all 0.2s ease;
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.1);
        }

        /* Status and Priority Badges */
        .status-badge, .priority-badge {
            padding: 8px 15px;
            border-radius: 50px;
            font-size: 0.85rem;
            font-weight: 500;
            display: inline-block;
            text-transform: capitalize;
            box-shadow: 0 2px 5px rgba(0, 0, 0, 0.1);
        }

        .status-pending {
            background: linear-gradient(45deg, #f1c40f, #f39c12);
            color: white;
        }

        .status-approved {
            background: linear-gradient(45deg, #27ae60, #2ecc71);
            color: white;
        }

        .status-rejected {
            background: linear-gradient(45deg, #e74c3c, #c0392b);
            color: white;
        }

        .priority-high {
            background: linear-gradient(45deg, #e74c3c, #c0392b);
            color: white;
        }

        .priority-medium {
            background: linear-gradient(45deg, #f1c40f, #f39c12);
            color: white;
        }

        .priority-low {
            background: linear-gradient(45deg, #27ae60, #2ecc71);
            color: white;
        }

        /* Action Buttons */
        .action-buttons {
            display: flex;
            gap: 8px;
            justify-content: flex-start;
        }

        .btn-action {
            padding: 8px 15px;
            border: none;
            border-radius: 50px;
            cursor: pointer;
            font-weight: 500;
            display: inline-flex;
            align-items: center;
            gap: 5px;
            transition: all 0.3s ease;
            text-decoration: none;
            box-shadow: 0 2px 5px rgba(0, 0, 0, 0.1);
        }

        .btn-approve {
            background: linear-gradient(45deg, #27ae60, #2ecc71);
            color: white;
        }

        .btn-reject {
            background: linear-gradient(45deg, #e74c3c, #c0392b);
            color: white;
        }

        .btn-action:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.2);
        }

        .btn-action i {
            font-size: 0.9rem;
        }

        /* Empty State */
        .empty-state {
            text-align: center;
            padding: 40px;
            background: #f8f9fa;
            border-radius: 10px;
            margin: 20px 0;
        }

        .empty-state i {
            font-size: 3rem;
            margin-bottom: 15px;
            background: linear-gradient(45deg, #20bf55, #01baef);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
        }

        .empty-state h3 {
            color: #2C3E50;
            font-size: 1.5rem;
            margin-bottom: 10px;
        }

        .empty-state p {
            color: #6c757d;
            font-size: 1rem;
            margin: 0;
        }

        /* Card Header */
        .card-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding-bottom: 20px;
            border-bottom: 2px solid #e9ecef;
            margin-bottom: 20px;
        }

        .card-header h2 {
            margin: 0;
            font-size: 1.5rem;
            color: #2C3E50;
            font-weight: 600;
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
                        <a href="hospitals_handles_recipients_status.php">
                            <i class="fas fa-procedures"></i>
                            <span>Manage Recipients</span>
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
            <div class="table-container">
                <div class="card-header">
                    <h2>Pending Donor Approvals</h2>
                </div>
                <div class="table-responsive">
                    <table class="modern-table">
                        <thead>
                            <tr>
                                <th>Donor Name</th>
                                <th>Organ Type</th>
                                <th>Blood Group</th>
                                <th>Request Date</th>
                                <th>Status</th>
                                <th>Details</th>
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
                                            <td><?php echo date('M d, Y', strtotime($request['request_date'])); ?></td>
                                            <td>
                                                <span class="status-badge <?php echo strtolower($request['status']); ?>">
                                                    <?php echo htmlspecialchars($request['status']); ?>
                                                </span>
                                            </td>
                                            <td>
                                                <a href="hospital_checks_donor_pf.php?id=<?php echo $request['approval_id']; ?>" class="btn-action btn-view">
                                                    <i class="fas fa-eye"></i> View
                                                </a>
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
            <div class="table-container">
                <div class="card-header">
                    <h2>Pending Recipient Approvals</h2>
                </div>
                <div class="table-responsive">
                    <?php
                    // Fetch pending recipient requests
                    try {
                        $stmt = $conn->prepare("
                            SELECT 
                                hra.*,
                                r.full_name,
                                r.blood_type,
                                r.organ_required,
                                r.medical_condition
                            FROM hospital_recipient_approvals hra
                            JOIN recipient_registration r ON hra.recipient_id = r.id
                            WHERE hra.hospital_id = ? AND hra.status = 'pending'
                            ORDER BY hra.request_date DESC
                        ");
                        $stmt->execute([$hospital_id]);
                        $recipient_requests = $stmt->fetchAll(PDO::FETCH_ASSOC);
                    } catch(PDOException $e) {
                        error_log("Error fetching recipient requests: " . $e->getMessage());
                        $error = "Error loading recipient requests";
                    }
                    ?>
                    <?php if (isset($error)): ?>
                        <div class="alert alert-danger"><?php echo $error; ?></div>
                    <?php elseif (empty($recipient_requests)): ?>
                        <div class="empty-state">
                            <i class="fas fa-inbox"></i>
                            <h3>No Pending Requests</h3>
                            <p>There are no pending recipient requests at this time.</p>
                        </div>
                    <?php else: ?>
                        <table class="modern-table">
                            <thead>
                                <tr>
                                    <th>Recipient Name</th>
                                    <th>Required Organ</th>
                                    <th>Blood Group</th>
                                    <th>Priority Level</th>
                                    <th>Request Date</th>
                                    <th>Status</th>
                                    <th>Details</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($recipient_requests as $request): ?>
                                    <tr>
                                        <td><?php echo htmlspecialchars($request['full_name']); ?></td>
                                        <td><?php echo htmlspecialchars($request['organ_required']); ?></td>
                                        <td>
                                            <span class="status-badge">
                                                <?php echo htmlspecialchars($request['blood_type']); ?>
                                            </span>
                                        </td>
                                        <td>
                                            <span class="priority-badge priority-<?php echo strtolower($request['priority_level']); ?>">
                                                <?php echo htmlspecialchars($request['priority_level']); ?>
                                            </span>
                                        </td>
                                        <td><?php echo date('M d, Y', strtotime($request['request_date'])); ?></td>
                                        <td>
                                            <span class="status-badge status-pending">
                                                Pending
                                            </span>
                                        </td>
                                        <td>
                                            <a href="hospital_checks_recipient_pf.php?id=<?php echo $request['approval_id']; ?>" class="btn-action btn-view">
                                                <i class="fas fa-eye"></i> View
                                            </a>
                                        </td>
                                        <td>
                                            <div class="action-buttons">
                                                <button class="btn-action btn-approve" onclick="approveRequest(<?php echo $request['approval_id']; ?>)">
                                                    <i class="fas fa-check"></i> Approve
                                                </button>
                                                <button class="btn-action btn-reject" onclick="rejectRequest(<?php echo $request['approval_id']; ?>)">
                                                    <i class="fas fa-times"></i> Reject
                                                </button>
                                            </div>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    <?php endif; ?>
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

            <script>
                function approveRequest(approvalId) {
                    if (confirm('Are you sure you want to approve this request?')) {
                        $.post('approve_recipient_request.php', {
                            approval_id: approvalId,
                            action: 'approve'
                        }, function(response) {
                            if (response.success) {
                                location.reload();
                            } else {
                                alert('Error: ' + response.message);
                            }
                        });
                    }
                }

                function rejectRequest(approvalId) {
                    const reason = prompt('Please enter a reason for rejection:');
                    if (reason) {
                        $.post('reject_recipient_request.php', {
                            approval_id: approvalId,
                            reason: reason,
                            action: 'reject'
                        }, function(response) {
                            if (response.success) {
                                location.reload();
                            } else {
                                alert('Error: ' + response.message);
                            }
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
    $stmt = $conn->prepare("
        SELECT 
            hra.*,
            r.full_name,
            r.blood_type,
            r.organ_required,
            r.medical_condition
        FROM hospital_recipient_approvals hra
        JOIN recipient_registration r ON hra.recipient_id = r.id
        WHERE hra.hospital_id = ? AND hra.status = 'pending'
        ORDER BY hra.request_date DESC
    ");
    $stmt->execute([$hospital_id]);
    $recipient_requests = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch(PDOException $e) {
    error_log("Error fetching recipient requests: " . $e->getMessage());
    $error = "Error loading recipient requests";
}
?>
