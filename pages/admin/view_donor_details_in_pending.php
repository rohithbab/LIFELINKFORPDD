<?php
session_start();
require_once '../../backend/php/connection.php';
require_once '../../backend/php/queries.php';

// Check if admin is logged in
if (!isset($_SESSION['admin_id'])) {
    header('Location: ../admin_login.php');
    exit();
}

// Get donor ID from URL
$donor_id = isset($_GET['id']) ? $_GET['id'] : null;

if (!$donor_id) {
    header('Location: admin_dashboard.php');
    exit();
}

// Get donor details
try {
    $stmt = $conn->prepare("SELECT * FROM donor WHERE donor_id = ? AND status = 'pending'");
    $stmt->execute([$donor_id]);
    $donor = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$donor) {
        header('Location: admin_dashboard.php');
        exit();
    }
} catch (PDOException $e) {
    error_log("Error fetching donor details: " . $e->getMessage());
    header('Location: admin_dashboard.php');
    exit();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Donor Details - LifeLink Admin</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link rel="stylesheet" href="../../assets/css/styles.css">
    <link rel="stylesheet" href="../../assets/css/admin-dashboard.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css">
    <style>
        .admin-container {
            display: flex;
            flex-direction: column;
            min-height: 100vh;
        }
        .main-content {
            flex: 1;
            padding: 20px;
            margin-left: 0;
            width: 100%;
        }
        .details-container {
            background: white;
            border-radius: 10px;
            padding: 30px;
            margin: 20px auto;
            max-width: 1200px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        .details-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 30px;
            border-bottom: 2px solid #eee;
            padding-bottom: 15px;
        }
        .action-buttons {
            display: flex;
            align-items: center;
            gap: 10px;
        }
        .details-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 20px;
        }
        .detail-item {
            padding: 15px;
            background: #f8f9fa;
            border-radius: 8px;
            border-left: 4px solid var(--primary-blue);
        }
        .detail-item h3 {
            color: var(--primary-blue);
            margin-bottom: 10px;
            font-size: 1.1rem;
        }
        .detail-item p {
            color: #333;
            margin: 0;
        }
        .action-button {
            padding: 10px 20px;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            font-weight: 500;
            display: inline-flex;
            align-items: center;
            gap: 8px;
            transition: all 0.3s ease;
        }
        .approve-button {
            background-color: #28a745;
            color: white;
        }
        .reject-button {
            background-color: #dc3545;
            color: white;
        }
        .action-button:hover {
            transform: translateY(-2px);
            box-shadow: 0 2px 5px rgba(0,0,0,0.2);
        }
        .back-btn {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            padding: 10px 20px;
            border-radius: 5px;
            text-decoration: none;
            color: white;
            font-weight: 500;
            background: linear-gradient(135deg, #4CAF50 0%, #2196F3 100%);
            transition: all 0.3s ease;
            margin-right: 15px;
        }
        .back-btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 8px rgba(0,0,0,0.2);
        }
        .back-btn i {
            font-size: 0.9rem;
        }
        .view-document {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            color: var(--primary-blue);
            text-decoration: none;
            padding: 5px 10px;
            border-radius: 4px;
            transition: background-color 0.3s;
        }
        .view-document:hover {
            background-color: rgba(0, 123, 255, 0.1);
        }
        .view-document i {
            font-size: 0.9rem;
        }
        /* Custom Modal Styles */
        .custom-modal {
            border-radius: 15px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.2);
        }
        .modal-content {
            padding: 20px 0;
        }
        .donor-info {
            background: #f8f9fa;
            padding: 15px;
            border-radius: 10px;
            margin-bottom: 20px;
        }
        .info-item {
            display: flex;
            align-items: center;
            gap: 10px;
            margin-bottom: 10px;
        }
        .info-item:last-child {
            margin-bottom: 0;
        }
        .info-item i {
            width: 20px;
        }
        .input-container {
            margin: 20px 0;
            text-align: left;
        }
        .input-label {
            display: block;
            margin-bottom: 8px;
            color: #333;
            font-weight: 500;
        }
        .custom-input {
            width: 100%;
            padding: 12px;
            border: 2px solid #e0e0e0;
            border-radius: 8px;
            font-size: 1rem;
            transition: border-color 0.3s;
            outline: none;
        }
        .custom-input:focus {
            border-color: #2196F3;
        }
        .custom-textarea {
            width: 100%;
            min-height: 100px;
            padding: 12px;
            border: 2px solid #e0e0e0;
            border-radius: 8px;
            font-size: 1rem;
            transition: border-color 0.3s;
            outline: none;
            resize: vertical;
        }
        .custom-textarea:focus {
            border-color: #dc3545;
        }
        .notification-text {
            color: #666;
            font-size: 0.9rem;
            margin-top: 15px;
            display: flex;
            align-items: center;
            gap: 8px;
        }
        .custom-confirm-button, .custom-cancel-button {
            padding: 12px 24px;
            font-weight: 500;
            border-radius: 8px;
            display: inline-flex;
            align-items: center;
            gap: 8px;
            transition: transform 0.2s;
        }
        .custom-confirm-button:hover, .custom-cancel-button:hover {
            transform: translateY(-2px);
        }
        @keyframes fadeInDown {
            from {
                opacity: 0;
                transform: translate3d(0, -20px, 0);
            }
            to {
                opacity: 1;
                transform: translate3d(0, 0, 0);
            }
        }
        @keyframes fadeOutUp {
            from {
                opacity: 1;
                transform: translate3d(0, 0, 0);
            }
            to {
                opacity: 0;
                transform: translate3d(0, -20px, 0);
            }
        }
    </style>
</head>
<body>
    <div class="admin-container">
        <!-- Main Content -->
        <div class="main-content">
            <div class="details-container">
                <div class="details-header">
                    <h2>Donor Details</h2>
                    <div class="action-buttons">
                        <a href="admin_dashboard.php" class="back-btn">
                            <i class="fas fa-arrow-left"></i> Back to Dashboard
                        </a>
                        <button class="action-button approve-button" onclick="showApproveModal('<?php echo htmlspecialchars($donor['donor_id']); ?>', '<?php echo htmlspecialchars($donor['name']); ?>', '<?php echo htmlspecialchars($donor['email']); ?>')">
                            <i class="fas fa-check"></i> Approve
                        </button>
                        <button class="action-button reject-button" onclick="showRejectModal('<?php echo htmlspecialchars($donor['donor_id']); ?>', '<?php echo htmlspecialchars($donor['name']); ?>')">
                            <i class="fas fa-times"></i> Reject
                        </button>
                    </div>
                </div>

                <div class="details-grid">
                    <div class="detail-item">
                        <h3>Full Name</h3>
                        <p><?php echo htmlspecialchars($donor['name']); ?></p>
                    </div>
                    <div class="detail-item">
                        <h3>Email</h3>
                        <p><?php echo htmlspecialchars($donor['email']); ?></p>
                    </div>
                    <div class="detail-item">
                        <h3>Blood Type</h3>
                        <p><?php echo htmlspecialchars($donor['blood_group'] ?? 'Not specified'); ?></p>
                    </div>
                    <div class="detail-item">
                        <h3>Phone</h3>
                        <p><?php echo htmlspecialchars($donor['phone']); ?></p>
                    </div>
                    <div class="detail-item">
                        <h3>Gender</h3>
                        <p><?php echo htmlspecialchars($donor['gender']); ?></p>
                    </div>
                    <div class="detail-item">
                        <h3>Date of Birth</h3>
                        <p><?php echo htmlspecialchars($donor['dob']); ?></p>
                    </div>
                    <div class="detail-item">
                        <h3>Address</h3>
                        <p><?php echo htmlspecialchars($donor['address']); ?></p>
                    </div>
                    <div class="detail-item">
                        <h3>Organs to Donate</h3>
                        <p><?php echo htmlspecialchars($donor['organs_to_donate']); ?></p>
                    </div>
                    <div class="detail-item">
                        <h3>Reason for Donation</h3>
                        <p><?php echo htmlspecialchars($donor['reason_for_donation'] ?? 'Not specified'); ?></p>
                    </div>
                    <?php if (!empty($donor['medical_conditions'])): ?>
                    <div class="detail-item">
                        <h3>Medical Conditions</h3>
                        <p><?php echo htmlspecialchars($donor['medical_conditions']); ?></p>
                    </div>
                    <?php endif; ?>
                    <div class="detail-item">
                        <h3>ID Proof</h3>
                        <p>
                            <a href="../../uploads/donors/id_proof_path/<?php echo htmlspecialchars($donor['id_proof_path']); ?>" 
                               target="_blank" class="view-document">
                                <i class="fas fa-external-link-alt"></i> View ID Proof
                            </a>
                        </p>
                    </div>
                    <?php if (!empty($donor['medical_reports_path'])): ?>
                    <div class="detail-item">
                        <h3>Medical Reports</h3>
                        <p>
                            <a href="../../uploads/donors/medical_reports_path/<?php echo htmlspecialchars($donor['medical_reports_path']); ?>" 
                               target="_blank" class="view-document">
                                <i class="fas fa-external-link-alt"></i> View Medical Reports
                            </a>
                        </p>
                    </div>
                    <?php endif; ?>
                    <?php if (!empty($donor['guardian_name'])): ?>
                    <div class="detail-item">
                        <h3>Guardian Name</h3>
                        <p><?php echo htmlspecialchars($donor['guardian_name']); ?></p>
                    </div>
                    <div class="detail-item">
                        <h3>Guardian Email</h3>
                        <p><?php echo htmlspecialchars($donor['guardian_email']); ?></p>
                    </div>
                    <div class="detail-item">
                        <h3>Guardian Phone</h3>
                        <p><?php echo htmlspecialchars($donor['guardian_phone']); ?></p>
                    </div>
                    <?php if (!empty($donor['guardian_id_proof_path'])): ?>
                    <div class="detail-item">
                        <h3>Guardian ID Proof</h3>
                        <p>
                            <a href="../../uploads/donors/guardian_id_proof_path/<?php echo htmlspecialchars($donor['guardian_id_proof_path']); ?>" 
                               target="_blank" class="view-document">
                                <i class="fas fa-external-link-alt"></i> View Guardian ID Proof
                            </a>
                        </p>
                    </div>
                    <?php endif; ?>
                    <?php endif; ?>
                    <div class="detail-item">
                        <h3>ODML ID</h3>
                        <p><?php echo htmlspecialchars($donor['odml_id'] ?? 'Not assigned'); ?></p>
                    </div>
                    <div class="detail-item">
                        <h3>Registration Date</h3>
                        <p><?php echo htmlspecialchars($donor['created_at']); ?></p>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script>
        function showApproveModal(donorId, donorName, donorEmail) {
            Swal.fire({
                title: '<h2 style="color: #2196F3;">Approve Donor</h2>',
                html: `
                    <div class="modal-content">
                        <div class="donor-info">
                            <div class="info-item">
                                <i class="fas fa-user" style="color: #2196F3;"></i>
                                <span><strong>Name:</strong> ${donorName}</span>
                            </div>
                            <div class="info-item">
                                <i class="fas fa-envelope" style="color: #2196F3;"></i>
                                <span><strong>Email:</strong> ${donorEmail}</span>
                            </div>
                        </div>
                        <div class="input-container">
                            <label for="odml_id" class="input-label">Enter ODML ID</label>
                            <input type="text" id="odml_id" class="custom-input" placeholder="ODML ID">
                        </div>
                        <p class="notification-text">
                            <i class="fas fa-bell" style="color: #666;"></i>
                            An email notification will be sent to the donor upon approval.
                        </p>
                    </div>
                `,
                showCancelButton: true,
                confirmButtonText: '<i class="fas fa-check"></i> Approve & Update',
                cancelButtonText: '<i class="fas fa-times"></i> Cancel',
                confirmButtonColor: '#4CAF50',
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
                    updateDonorWithODML(donorId, result.value);
                }
            });
        }

        function showRejectModal(donorId, donorName) {
            Swal.fire({
                title: '<h2 style="color: #dc3545;">Reject Donor</h2>',
                html: `
                    <div class="modal-content">
                        <div class="donor-info">
                            <div class="info-item">
                                <i class="fas fa-user" style="color: #dc3545;"></i>
                                <span><strong>Name:</strong> ${donorName}</span>
                            </div>
                        </div>
                        <div class="input-container">
                            <label for="rejection_reason" class="input-label">Reason for Rejection</label>
                            <textarea id="rejection_reason" class="custom-textarea" placeholder="Enter detailed reason for rejection"></textarea>
                        </div>
                        <p class="notification-text">
                            <i class="fas fa-bell" style="color: #666;"></i>
                            An email notification with the rejection reason will be sent to the donor.
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
                    rejectDonor(donorId, result.value);
                }
            });
        }

        function updateDonorWithODML(donorId, odmlId) {
            $.ajax({
                url: '../../backend/php/update_donor_odml.php',
                method: 'POST',
                data: {
                    donor_id: donorId,
                    odml_id: odmlId,
                    action: 'approve'
                },
                success: function(response) {
                    try {
                        const result = JSON.parse(response);
                        if (result.success) {
                            Swal.fire({
                                icon: 'success',
                                title: 'Success!',
                                text: 'Donor has been approved successfully.',
                                showConfirmButton: false,
                                timer: 1500
                            }).then(() => {
                                window.location.href = 'admin_dashboard.php';
                            });
                        } else {
                            Swal.fire({
                                icon: 'error',
                                title: 'Error',
                                text: result.message || 'Failed to approve donor.'
                            });
                        }
                    } catch (e) {
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

        function rejectDonor(donorId, reason) {
            $.ajax({
                url: '../../backend/php/update_donor_status.php',
                method: 'POST',
                data: {
                    donor_id: donorId,
                    status: 'rejected',
                    reason: reason
                },
                success: function(response) {
                    try {
                        const result = JSON.parse(response);
                        if (result.success) {
                            Swal.fire({
                                icon: 'success',
                                title: 'Success!',
                                text: 'Donor has been rejected successfully.',
                                showConfirmButton: false,
                                timer: 1500
                            }).then(() => {
                                window.location.href = 'admin_dashboard.php';
                            });
                        } else {
                            Swal.fire({
                                icon: 'error',
                                title: 'Error',
                                text: result.message || 'Failed to reject donor.'
                            });
                        }
                    } catch (e) {
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
