<?php
session_start();
require_once '../../backend/php/connection.php';

// Check if admin is logged in
if (!isset($_SESSION['admin_id'])) {
    header('Location: ../admin_login.php');
    exit();
}

// Check if recipient ID is provided
if (!isset($_GET['id'])) {
    header('Location: admin_dashboard.php');
    exit();
}

$recipient_id = $_GET['id'];

// Get recipient details
try {
    $stmt = $conn->prepare("SELECT * FROM recipient_registration WHERE id = ? AND request_status = 'pending'");
    $stmt->execute([$recipient_id]);
    $recipient = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$recipient) {
        header('Location: admin_dashboard.php');
        exit();
    }
} catch (PDOException $e) {
    die("Error: " . $e->getMessage());
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Recipient Details - LifeLink Admin</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link rel="stylesheet" href="../../assets/css/styles.css">
    <link rel="stylesheet" href="../../assets/css/admin-dashboard.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css">
    <style>
        .details-container {
            max-width: 800px;
            margin: 2rem auto;
            padding: 2rem;
            background: white;
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }
        .details-section {
            margin-bottom: 2rem;
            padding-bottom: 1rem;
            border-bottom: 1px solid #eee;
        }
        .details-section h3 {
            color: var(--primary-blue);
            margin-bottom: 1rem;
        }
        .details-grid {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 1rem;
        }
        .detail-item {
            margin-bottom: 1rem;
        }
        .detail-label {
            font-weight: bold;
            color: #666;
        }
        .detail-value {
            margin-top: 0.25rem;
        }
        .document-preview {
            max-width: 200px;
            margin-top: 0.5rem;
        }
        .action-buttons {
            display: flex;
            gap: 1rem;
            margin-top: 2rem;
            justify-content: center;
        }
        .action-buttons button {
            padding: 12px 30px;
            border: none;
            border-radius: 8px;
            cursor: pointer;
            font-weight: bold;
            font-size: 1.1rem;
            transition: all 0.3s ease;
            display: flex;
            align-items: center;
            gap: 8px;
        }
        .back-btn {
            position: absolute;
            top: 20px;
            right: 20px;
            padding: 10px 20px;
            background: linear-gradient(135deg, #2196F3, #00BCD4);
            color: white;
            border: none;
            border-radius: 6px;
            cursor: pointer;
            font-weight: bold;
            transition: all 0.3s ease;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 8px;
            font-size: 1rem;
        }
        .back-btn:hover {
            background: linear-gradient(135deg, #1976D2, #0097A7);
            transform: translateY(-2px);
            box-shadow: 0 4px 8px rgba(0,0,0,0.2);
            text-decoration: none;
            color: white;
        }
        .approve-btn {
            background: linear-gradient(135deg, #4CAF50, #45a049);
            color: white;
            border: none;
        }
        .approve-btn:hover {
            background: linear-gradient(135deg, #45a049, #3d8b40);
            transform: translateY(-2px);
            box-shadow: 0 4px 8px rgba(0,0,0,0.2);
        }
        .reject-btn {
            background: linear-gradient(135deg, #f44336, #e53935);
            color: white;
            border: none;
        }
        .reject-btn:hover {
            background: linear-gradient(135deg, #e53935, #d32f2f);
            transform: translateY(-2px);
            box-shadow: 0 4px 8px rgba(0,0,0,0.2);
        }
    </style>
</head>
<body>
    <div class="details-container">
        <h2 class="text-center">Recipient Details</h2>
        
        <!-- Personal Information -->
        <div class="details-section">
            <h3><i class="fas fa-user"></i> Personal Information</h3>
            <div class="details-grid">
                <div class="detail-item">
                    <div class="detail-label">Full Name</div>
                    <div class="detail-value"><?php echo htmlspecialchars($recipient['full_name']); ?></div>
                </div>
                <div class="detail-item">
                    <div class="detail-label">Date of Birth</div>
                    <div class="detail-value"><?php echo htmlspecialchars($recipient['date_of_birth']); ?></div>
                </div>
                <div class="detail-item">
                    <div class="detail-label">Gender</div>
                    <div class="detail-value"><?php echo htmlspecialchars($recipient['gender']); ?></div>
                </div>
                <div class="detail-item">
                    <div class="detail-label">Blood Type</div>
                    <div class="detail-value"><?php echo htmlspecialchars($recipient['blood_type']); ?></div>
                </div>
            </div>
        </div>

        <!-- Contact Information -->
        <div class="details-section">
            <h3><i class="fas fa-address-card"></i> Contact Information</h3>
            <div class="details-grid">
                <div class="detail-item">
                    <div class="detail-label">Email</div>
                    <div class="detail-value"><?php echo htmlspecialchars($recipient['email']); ?></div>
                </div>
                <div class="detail-item">
                    <div class="detail-label">Phone Number</div>
                    <div class="detail-value"><?php echo htmlspecialchars($recipient['phone_number']); ?></div>
                </div>
                <div class="detail-item">
                    <div class="detail-label">Address</div>
                    <div class="detail-value"><?php echo htmlspecialchars($recipient['address']); ?></div>
                </div>
            </div>
        </div>

        <!-- Medical Information -->
        <div class="details-section">
            <h3><i class="fas fa-heartbeat"></i> Medical Information</h3>
            <div class="details-grid">
                <div class="detail-item">
                    <div class="detail-label">Medical Condition</div>
                    <div class="detail-value"><?php echo htmlspecialchars($recipient['medical_condition']); ?></div>
                </div>
                <div class="detail-item">
                    <div class="detail-label">Organ Required</div>
                    <div class="detail-value"><?php echo htmlspecialchars($recipient['organ_required']); ?></div>
                </div>
                <div class="detail-item">
                    <div class="detail-label">Urgency Level</div>
                    <div class="detail-value"><?php echo htmlspecialchars($recipient['urgency_level']); ?></div>
                </div>
                <div class="detail-item">
                    <div class="detail-label">ODML ID</div>
                    <div class="detail-value"><?php echo htmlspecialchars($recipient['odml_id'] ?? 'Not Assigned'); ?></div>
                </div>
            </div>
            <div class="detail-item" style="margin-top: 1rem;">
                <div class="detail-label">Reason for Organ Requirement</div>
                <div class="detail-value"><?php echo nl2br(htmlspecialchars($recipient['organ_reason'])); ?></div>
            </div>
        </div>

        <!-- Documents -->
        <div class="details-section">
            <h3><i class="fas fa-file-medical"></i> Documents</h3>
            <div class="details-grid">
                <div class="detail-item">
                    <div class="detail-label">Medical Reports</div>
                    <div class="detail-value">
                        <?php if ($recipient['recipient_medical_reports']): ?>
                            <a href="../../uploads/recipient_registration/recipient_medical_reports/<?php echo htmlspecialchars($recipient['recipient_medical_reports']); ?>" target="_blank" class="view-btn">
                                <i class="fas fa-eye"></i> View Medical Reports
                            </a>
                        <?php else: ?>
                            <span class="text-muted">No medical reports uploaded</span>
                        <?php endif; ?>
                    </div>
                </div>
                <div class="detail-item">
                    <div class="detail-label">ID Document</div>
                    <div class="detail-value">
                        <?php if ($recipient['id_document']): ?>
                            <a href="../../uploads/recipient_registration/id_document/<?php echo htmlspecialchars($recipient['id_document']); ?>" target="_blank" class="view-btn">
                                <i class="fas fa-eye"></i> View ID Document
                            </a>
                        <?php else: ?>
                            <span class="text-muted">No ID document uploaded</span>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>

        <!-- Action Buttons -->
        <div class="action-buttons">
            <button class="approve-btn" onclick="showApproveModal('<?php echo htmlspecialchars($recipient['id']); ?>', '<?php echo htmlspecialchars($recipient['full_name']); ?>', '<?php echo htmlspecialchars($recipient['email']); ?>')">
                <i class="fas fa-check"></i> Approve
            </button>
            <button class="reject-btn" onclick="showRejectModal('<?php echo htmlspecialchars($recipient['id']); ?>', '<?php echo htmlspecialchars($recipient['full_name']); ?>')">
                <i class="fas fa-times"></i> Reject
            </button>
        </div>
        
        <a href="admin_dashboard.php" class="back-btn">
            <i class="fas fa-arrow-left"></i> Back to Dashboard
        </a>
    </div>

    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script>
        function showApproveModal(recipientId, recipientName, recipientEmail) {
            Swal.fire({
                title: 'Approve Recipient',
                html: `
                    <div>
                        <p><strong>Recipient Name:</strong> ${recipientName}</p>
                        <p><strong>Email:</strong> ${recipientEmail}</p>
                        <div style="margin: 20px 0;">
                            <input type="text" id="odml_id" class="swal2-input" placeholder="Enter ODML ID">
                        </div>
                        <p style="font-size: 0.9em; color: #666;">
                            An email notification will be sent to the recipient upon approval.
                        </p>
                    </div>
                `,
                showCancelButton: true,
                confirmButtonText: 'Approve & Update',
                cancelButtonText: 'Cancel',
                confirmButtonColor: '#28a745',
                cancelButtonColor: '#6c757d',
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
                    updateRecipientWithODML(recipientId, result.value);
                }
            });
        }

        function showRejectModal(recipientId, recipientName) {
            Swal.fire({
                title: 'Reject Recipient',
                html: `
                    <div>
                        <p><strong>Recipient Name:</strong> ${recipientName}</p>
                        <div style="margin: 20px 0;">
                            <textarea id="rejection_reason" class="swal2-textarea" placeholder="Enter reason for rejection"></textarea>
                        </div>
                        <p style="font-size: 0.9em; color: #666;">
                            An email notification with the rejection reason will be sent to the recipient.
                        </p>
                    </div>
                `,
                showCancelButton: true,
                confirmButtonText: 'Reject',
                cancelButtonText: 'Cancel',
                confirmButtonColor: '#dc3545',
                cancelButtonColor: '#6c757d',
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
                    rejectRecipient(recipientId, result.value);
                }
            });
        }

        function updateRecipientWithODML(recipientId, odmlId) {
            $.ajax({
                url: '../../backend/php/update_recipient_odml.php',
                method: 'POST',
                data: {
                    recipient_id: recipientId,
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
                                text: 'Recipient has been approved successfully.',
                                showConfirmButton: false,
                                timer: 1500
                            }).then(() => {
                                window.location.href = 'admin_dashboard.php';
                            });
                        } else {
                            Swal.fire({
                                icon: 'error',
                                title: 'Error',
                                text: result.message || 'Failed to approve recipient.'
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

        function rejectRecipient(recipientId, reason) {
            $.ajax({
                url: '../../backend/php/update_recipient_status.php',
                method: 'POST',
                data: {
                    recipient_id: recipientId,
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
                                text: 'Recipient has been rejected successfully.',
                                showConfirmButton: false,
                                timer: 1500
                            }).then(() => {
                                window.location.href = 'admin_dashboard.php';
                            });
                        } else {
                            Swal.fire({
                                icon: 'error',
                                title: 'Error',
                                text: result.message || 'Failed to reject recipient.'
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
