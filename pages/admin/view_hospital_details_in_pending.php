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
        /* Center the main content */
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

        .actions-container {
            margin-top: 30px;
            display: flex;
            gap: 15px;
            justify-content: flex-end;
        }

        .back-btn {
            background: linear-gradient(135deg, #4CAF50, #2196F3);
            color: white;
            padding: 10px 20px;
            border: none;
            border-radius: 5px;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 8px;
            transition: all 0.3s ease;
        }

        .approve-btn {
            background: #2ecc71;
            color: white;
            padding: 10px 20px;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            font-weight: 600;
            transition: all 0.3s ease;
        }

        .reject-btn {
            background: #e74c3c;
            color: white;
            padding: 10px 20px;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            font-weight: 600;
            transition: all 0.3s ease;
        }

        .approve-btn:hover { background: #27ae60; }
        .reject-btn:hover { background: #c0392b; }
        .back-btn:hover { opacity: 0.9; }

        .view-license {
            color: #2196F3;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 8px;
            transition: color 0.3s ease;
        }
        
        .view-license:hover {
            color: #1976D2;
        }
        
        .view-license i {
            font-size: 14px;
        }
        
        .swal2-popup {
            padding: 2em;
        }
        .info-section {
            margin-bottom: 1.5em;
            text-align: center;
        }
        .icon-container {
            margin-bottom: 1em;
        }
        .icon-container i {
            font-size: 2em;
            color: #3498db;
        }
        .modal-message {
            margin-bottom: 1em;
        }
        .odml-input-container {
            margin: 1.5em 0;
        }
        .odml-input-container label {
            display: block;
            margin-bottom: 0.5em;
            font-weight: bold;
        }
        .confirmation-text, .status-update-text {
            margin: 1em 0;
            font-size: 0.9em;
            color: #666;
        }
        .confirmation-text i, .status-update-text i {
            margin-right: 0.5em;
            color: #3498db;
        }
    </style>
</head>
<body>
    <div class="admin-container">
        <!-- Main Content -->
        <div class="main-content">
            <div class="details-container">
                <div class="details-header">
                    <h2>Hospital Details</h2>
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

                <div class="actions-container">
                    <button class="approve-btn" onclick="showApproveModal('<?php echo htmlspecialchars($hospital['hospital_id']); ?>', '<?php echo htmlspecialchars($hospital['name']); ?>', '<?php echo htmlspecialchars($hospital['email']); ?>')">
                        <i class="fas fa-check"></i> Approve
                    </button>
                    <button class="reject-btn" onclick="showRejectModal('<?php echo htmlspecialchars($hospital['hospital_id']); ?>', '<?php echo htmlspecialchars($hospital['name']); ?>')">
                        <i class="fas fa-times"></i> Reject
                    </button>
                    <a href="admin_dashboard.php" class="back-btn">
                        <i class="fas fa-arrow-left"></i> Back to Dashboard
                    </a>
                </div>
            </div>
        </div>
    </div>

    <!-- Scripts -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script>
        function showApproveModal(hospitalId, hospitalName, hospitalEmail) {
            Swal.fire({
                title: '<h2 style="color: #2196F3;">Approve Hospital</h2>',
                html: `
                    <div class="modal-content">
                        <div class="hospital-info">
                            <div class="info-item">
                                <i class="fas fa-hospital" style="color: #2196F3;"></i>
                                <span><strong>Name:</strong> ${hospitalName}</span>
                            </div>
                            <div class="info-item">
                                <i class="fas fa-envelope" style="color: #2196F3;"></i>
                                <span><strong>Email:</strong> ${hospitalEmail}</span>
                            </div>
                        </div>
                        <div class="input-container">
                            <label for="odml_id" class="input-label">Enter ODML ID</label>
                            <input type="text" id="odml_id" class="custom-input" placeholder="Enter ODML ID">
                        </div>
                        <p class="notification-text">
                            <i class="fas fa-bell" style="color: #666;"></i>
                            An email notification will be sent to the hospital with their ODML ID.
                        </p>
                    </div>
                `,
                showCancelButton: true,
                confirmButtonText: '<i class="fas fa-check"></i> Approve',
                cancelButtonText: '<i class="fas fa-times"></i> Cancel',
                confirmButtonColor: '#2196F3',
                cancelButtonColor: '#666',
                customClass: {
                    popup: 'custom-modal',
                    confirmButton: 'custom-confirm-button',
                    cancelButton: 'custom-cancel-button'
                },
                width: '500px',
                backdrop: 'rgba(0,0,0,0.6)',
                showClass: {
                    popup: 'animate__animated animate__fadeInDown'
                },
                hideClass: {
                    popup: 'animate__animated animate__fadeOutUp'
                },
                preConfirm: () => {
                    const odmlId = document.getElementById('odml_id').value;
                    if (!odmlId) {
                        Swal.showValidationMessage('Please enter ODML ID');
                        return false;
                    }
                    return odmlId;
                }
            }).then((result) => {
                if (result.isConfirmed) {
                    const confirmButton = Swal.getConfirmButton();
                    if (confirmButton) {
                        confirmButton.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Processing...';
                        confirmButton.disabled = true;
                    }
                    updateHospitalWithODML(hospitalId, result.value);
                }
            });
        }

        function updateHospitalWithODML(hospitalId, odmlId) {
            const data = {
                hospital_id: hospitalId,
                odml_id: odmlId,
                action: 'approve'
            };
            
            $.ajax({
                url: '../../backend/php/update_hospital_odml.php',
                method: 'POST',
                contentType: 'application/json',
                data: JSON.stringify(data),
                success: function(response) {
                    try {
                        const result = typeof response === 'string' ? JSON.parse(response) : response;
                        if (result.success) {
                            Swal.fire({
                                icon: 'success',
                                title: 'Success!',
                                text: 'Hospital has been approved successfully.',
                                showConfirmButton: false,
                                timer: 1500
                            }).then(() => {
                                window.location.href = 'admin_dashboard.php';
                            });
                        } else {
                            console.error('Server returned error:', result);
                            Swal.fire({
                                icon: 'error',
                                title: 'Error',
                                text: result.message || 'Failed to approve hospital.'
                            });
                        }
                    } catch (e) {
                        console.error('Error parsing response:', e);
                        Swal.fire({
                            icon: 'error',
                            title: 'Error',
                            text: 'An unexpected error occurred.'
                        });
                    }
                },
                error: function(xhr, status, error) {
                    console.error('AJAX Error:', xhr.responseText);
                    Swal.fire({
                        icon: 'error',
                        title: 'Error',
                        text: 'Failed to connect to the server.'
                    });
                }
            });
        }

        function showRejectModal(hospitalId, hospitalName) {
            Swal.fire({
                title: '<h2 style="color: #dc3545;">Reject Hospital</h2>',
                html: `
                    <div class="modal-content">
                        <div class="hospital-info">
                            <div class="info-item">
                                <i class="fas fa-hospital" style="color: #dc3545;"></i>
                                <span><strong>Name:</strong> ${hospitalName}</span>
                            </div>
                        </div>
                        <div class="input-container">
                            <label for="rejection_reason" class="input-label">Reason for Rejection</label>
                            <textarea id="rejection_reason" class="custom-textarea" placeholder="Enter detailed reason for rejection"></textarea>
                        </div>
                        <p class="notification-text">
                            <i class="fas fa-bell" style="color: #666;"></i>
                            An email notification with the rejection reason will be sent to the hospital.
                        </p>
                    </div>
                `,
                showCancelButton: true,
                confirmButtonText: '<i class="fas fa-times"></i> Reject',
                cancelButtonText: '<i class="fas fa-arrow-left"></i> Cancel',
                confirmButtonColor: '#dc3545',
                cancelButtonColor: '#666',
                customClass: {
                    popup: 'custom-modal',
                    confirmButton: 'custom-confirm-button',
                    cancelButton: 'custom-cancel-button'
                },
                width: '500px',
                backdrop: 'rgba(0,0,0,0.6)',
                showClass: {
                    popup: 'animate__animated animate__fadeInDown'
                },
                hideClass: {
                    popup: 'animate__animated animate__fadeOutUp'
                },
                preConfirm: () => {
                    const reason = document.getElementById('rejection_reason').value;
                    if (!reason) {
                        Swal.showValidationMessage('Please enter rejection reason');
                        return false;
                    }
                    return reason;
                }
            }).then((result) => {
                if (result.isConfirmed) {
                    rejectHospital(hospitalId, result.value);
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
                    status: 'rejected',
                    reason: reason,
                    action: 'reject'
                }),
                success: function(response) {
                    try {
                        const result = typeof response === 'string' ? JSON.parse(response) : response;
                        if (result.success) {
                            Swal.fire({
                                icon: 'success',
                                title: 'Hospital Rejected',
                                text: 'The hospital has been rejected and notified.',
                                showConfirmButton: false,
                                timer: 1500
                            }).then(() => {
                                window.location.href = 'admin_dashboard.php';
                            });
                        } else {
                            Swal.fire({
                                icon: 'error',
                                title: 'Error',
                                text: result.message || 'Failed to reject hospital.'
                            });
                        }
                    } catch (e) {
                        console.error('Error parsing response:', e);
                        Swal.fire({
                            icon: 'error',
                            title: 'Error',
                            text: 'An unexpected error occurred.'
                        });
                    }
                },
                error: function() {
                    Swal.fire({
                        icon: 'error',
                        title: 'Error',
                        text: 'Failed to connect to the server.'
                    });
                }
            });
        }
    </script>
</body>
</html>
