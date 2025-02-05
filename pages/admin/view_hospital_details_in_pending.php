<?php
session_start();
require_once '../../backend/php/connection.php';
require_once '../../backend/php/queries.php';

// Check if admin is logged in
if (!isset($_SESSION['admin_id'])) {
    header('Location: ../admin_login.php');
    exit();
}

// Get hospital ID from URL
$hospital_id = isset($_GET['id']) ? $_GET['id'] : null;

if (!$hospital_id) {
    header('Location: admin_dashboard.php');
    exit();
}

// Get hospital details
try {
    $stmt = $conn->prepare("SELECT * FROM hospitals WHERE hospital_id = ? AND status = 'Pending'");
    $stmt->execute([$hospital_id]);
    $hospital = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$hospital) {
        header('Location: admin_dashboard.php');
        exit();
    }
} catch (PDOException $e) {
    error_log("Error fetching hospital details: " . $e->getMessage());
    header('Location: admin_dashboard.php');
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Hospital Details - LifeLink Admin</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link rel="stylesheet" href="../../assets/css/styles.css">
    <link rel="stylesheet" href="../../assets/css/admin-dashboard.css">
    <link href="https://cdn.jsdelivr.net/npm/sweetalert2@11.7.32/dist/sweetalert2.min.css" rel="stylesheet">
    <style>
        .main-content {
            margin-left: 0 !important;
            padding: 20px;
            display: flex;
            justify-content: center;
        }
        
        .details-container {
            background: white;
            border-radius: 10px;
            padding: 30px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            max-width: 1200px;
            width: 100%;
            margin: 0 auto;
        }

        .details-header {
            margin-bottom: 30px;
            border-bottom: 2px solid #f0f0f0;
            padding-bottom: 15px;
        }

        .details-header h2 {
            color: #1a73e8;
            margin: 0;
        }

        .details-grid {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 20px;
            margin-bottom: 30px;
        }

        .detail-item {
            padding: 15px;
            background: #f8f9fa;
            border-radius: 8px;
            border: 1px solid #e9ecef;
        }

        .detail-item h3 {
            color: #1a73e8;
            margin: 0 0 10px 0;
            font-size: 1rem;
        }

        .detail-item p {
            margin: 0;
            color: #333;
        }

        .action-buttons {
            display: flex;
            gap: 15px;
            justify-content: flex-end;
            margin-top: 30px;
        }

        .btn-approve {
            background: #4CAF50;
            color: white;
            border: none;
            padding: 10px 20px;
            border-radius: 5px;
            cursor: pointer;
            transition: background 0.3s;
        }

        .btn-reject {
            background: #f44336;
            color: white;
            border: none;
            padding: 10px 20px;
            border-radius: 5px;
            cursor: pointer;
            transition: background 0.3s;
        }

        .btn-approve:hover { background: #43A047; }
        .btn-reject:hover { background: #E53935; }

        .success-card {
            display: none;
            background: linear-gradient(135deg, #4CAF50, #45a049);
            color: white;
            padding: 20px;
            border-radius: 10px;
            margin-bottom: 20px;
            box-shadow: 0 4px 6px rgba(0,0,0,0.1);
            text-align: center;
        }

        .success-card h3 {
            margin: 0 0 10px 0;
            font-size: 1.5rem;
        }

        .success-card p {
            margin: 0;
            font-size: 1.1rem;
        }

        .success-card.rejection {
            background: linear-gradient(135deg, #f44336, #e53935);
        }

        .odml-display {
            background: #e8f5e9;
            padding: 15px;
            border-radius: 5px;
            text-align: center;
            margin-top: 15px;
            display: none;
        }

        .odml-display h4 {
            color: #2e7d32;
            margin: 0 0 5px 0;
        }

        .odml-display p {
            color: #1b5e20;
            font-size: 1.2rem;
            font-weight: bold;
            margin: 0;
        }
    </style>
</head>
<body>
    <div class="admin-container">
        <div class="main-content">
            <div class="details-container">
                <!-- Success Cards -->
                <div id="approvalSuccess" class="success-card">
                    <h3><i class="fas fa-check-circle"></i> Hospital Approved</h3>
                    <p>The hospital has been successfully approved and notified via email.</p>
                    <div class="odml-display">
                        <h4>Assigned ODML ID</h4>
                        <p id="assignedOdmlId"></p>
                    </div>
                </div>
                
                <div id="rejectionSuccess" class="success-card rejection">
                    <h3><i class="fas fa-times-circle"></i> Hospital Rejected</h3>
                    <p>The hospital has been rejected and notified of the decision via email.</p>
                </div>

                <!-- Hospital Details -->
                <div class="details-header">
                    <h2>Hospital Details Review</h2>
                </div>

                <div class="details-grid">
                    <div class="detail-item">
                        <h3>Hospital Name</h3>
                        <p><?php echo htmlspecialchars($hospital['name']); ?></p>
                    </div>
                    <div class="detail-item">
                        <h3>Email</h3>
                        <p><?php echo htmlspecialchars($hospital['email']); ?></p>
                    </div>
                    <div class="detail-item">
                        <h3>Phone</h3>
                        <p><?php echo htmlspecialchars($hospital['phone']); ?></p>
                    </div>
                    <div class="detail-item">
                        <h3>Address</h3>
                        <p><?php echo htmlspecialchars($hospital['address']); ?></p>
                    </div>
                    <div class="detail-item">
                        <h3>License</h3>
                        <p><a href="../../uploads/hospitals/license_file/<?php echo htmlspecialchars($hospital['license_file']); ?>" target="_blank" class="view-license">View License <i class="fas fa-external-link-alt"></i></a></p>
                    </div>
                    <div class="detail-item">
                        <h3>ODML ID</h3>
                        <p><?php echo htmlspecialchars($hospital['odml_id'] ?? 'Not assigned'); ?></p>
                    </div>
                    <div class="detail-item">
                        <h3>Registration Date</h3>
                        <p><?php echo date('F d, Y', strtotime($hospital['registration_date'])); ?></p>
                    </div>
                </div>

                <div class="action-buttons">
                    <button class="btn-approve" onclick="showApproveModal(<?php echo $hospital_id; ?>, '<?php echo htmlspecialchars($hospital['name']); ?>', '<?php echo htmlspecialchars($hospital['email']); ?>')">
                        <i class="fas fa-check"></i> Approve
                    </button>
                    <button class="btn-reject" onclick="showRejectModal(<?php echo $hospital_id; ?>, '<?php echo htmlspecialchars($hospital['name']); ?>', '<?php echo htmlspecialchars($hospital['email']); ?>')">
                        <i class="fas fa-times"></i> Reject
                    </button>
                    <a href="admin_dashboard.php" class="back-btn">
                        <i class="fas fa-arrow-left"></i> Back to Dashboard
                    </a>
                </div>
            </div>
        </div>
    </div>

    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11.7.32/dist/sweetalert2.all.min.js"></script>
    <script>
        function showApproveModal(hospitalId, hospitalName, hospitalEmail) {
            Swal.fire({
                title: '<h2 style="color: #2196F3;">Approve Hospital</h2>',
                html: `
                    <p>You are about to approve <strong>${hospitalName}</strong></p>
                    <div style="margin: 20px 0;">
                        <label for="odmlId" style="display: block; margin-bottom: 5px; text-align: left;">ODML ID:</label>
                        <input type="text" id="odmlId" class="swal2-input" placeholder="Enter ODML ID">
                    </div>
                `,
                showCancelButton: true,
                confirmButtonText: 'Approve',
                confirmButtonColor: '#4CAF50',
                cancelButtonText: 'Cancel',
                preConfirm: () => {
                    const odmlId = document.getElementById('odmlId').value;
                    if (!odmlId) {
                        Swal.showValidationMessage('Please enter an ODML ID');
                        return false;
                    }
                    return odmlId;
                }
            }).then((result) => {
                if (result.isConfirmed) {
                    const odmlId = result.value;
                    approveHospital(hospitalId, odmlId);
                }
            });
        }

        function approveHospital(hospitalId, odmlId) {
            $.ajax({
                url: '../../backend/php/update_hospital_status.php',
                method: 'POST',
                contentType: 'application/json',
                data: JSON.stringify({
                    hospital_id: hospitalId,
                    action: 'approve',
                    odml_id: odmlId
                }),
                success: function(response) {
                    const data = JSON.parse(response);
                    if (data.success) {
                        // Hide the details and action buttons
                        $('.details-grid, .action-buttons').hide();
                        
                        // Show the success card with ODML ID
                        $('#assignedOdmlId').text(odmlId);
                        $('#approvalSuccess').show();
                        $('.odml-display').show();
                        
                        // Redirect after 3 seconds
                        setTimeout(() => {
                            window.location.href = 'admin_dashboard.php';
                        }, 3000);
                    } else {
                        Swal.fire('Error', data.message, 'error');
                    }
                },
                error: function() {
                    Swal.fire('Error', 'Failed to update hospital status', 'error');
                }
            });
        }

        function showRejectModal(hospitalId, hospitalName, hospitalEmail) {
            Swal.fire({
                title: '<h2 style="color: #f44336;">Reject Hospital</h2>',
                html: `
                    <p>You are about to reject <strong>${hospitalName}</strong></p>
                    <div style="margin: 20px 0;">
                        <label for="rejectReason" style="display: block; margin-bottom: 5px; text-align: left;">Reason for Rejection:</label>
                        <textarea id="rejectReason" class="swal2-textarea" placeholder="Enter reason for rejection"></textarea>
                    </div>
                `,
                showCancelButton: true,
                confirmButtonText: 'Reject',
                confirmButtonColor: '#f44336',
                cancelButtonText: 'Cancel',
                preConfirm: () => {
                    const reason = document.getElementById('rejectReason').value;
                    if (!reason) {
                        Swal.showValidationMessage('Please enter a reason for rejection');
                        return false;
                    }
                    return reason;
                }
            }).then((result) => {
                if (result.isConfirmed) {
                    const reason = result.value;
                    rejectHospital(hospitalId, reason);
                }
            });
        }

        function rejectHospital(hospitalId, reason) {
            $.ajax({
                url: '../../backend/php/update_hospital_status.php',
                method: 'POST',
                contentType: 'application/json',
                data: JSON.stringify({
                    hospital_id: hospitalId,
                    action: 'reject',
                    reason: reason
                }),
                success: function(response) {
                    const data = JSON.parse(response);
                    if (data.success) {
                        // Hide the details and action buttons
                        $('.details-grid, .action-buttons').hide();
                        
                        // Show the rejection success card
                        $('#rejectionSuccess').show();
                        
                        // Redirect after 3 seconds
                        setTimeout(() => {
                            window.location.href = 'admin_dashboard.php';
                        }, 3000);
                    } else {
                        Swal.fire('Error', data.message, 'error');
                    }
                },
                error: function() {
                    Swal.fire('Error', 'Failed to update hospital status', 'error');
                }
            });
        }
    </script>
</body>
</html>
