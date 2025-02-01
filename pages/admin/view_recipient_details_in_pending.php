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
        /* Custom Modal Styles */
        .custom-modal {
            border-radius: 15px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.2);
        }
        .modal-content {
            padding: 20px 0;
        }
        .recipient-info {
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
                title: '<h2 style="color: #2196F3;">Approve Recipient</h2>',
                html: `
                    <div class="modal-content">
                        <div class="recipient-info">
                            <div class="info-item">
                                <i class="fas fa-user" style="color: #2196F3;"></i>
                                <span><strong>Name:</strong> ${recipientName}</span>
                            </div>
                            <div class="info-item">
                                <i class="fas fa-envelope" style="color: #2196F3;"></i>
                                <span><strong>Email:</strong> ${recipientEmail}</span>
                            </div>
                        </div>
                        <div class="input-container">
                            <label for="odml_id" class="input-label">Enter ODML ID</label>
                            <input type="text" id="odml_id" class="custom-input" placeholder="Enter ODML ID">
                        </div>
                        <p class="notification-text">
                            <i class="fas fa-bell" style="color: #666;"></i>
                            An email notification will be sent to the recipient with their ODML ID.
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
                    updateRecipientWithODML(recipientId, result.value);
                }
            });
        }

        function updateRecipientWithODML(recipientId, odmlId) {
            const data = {
                recipient_id: recipientId,
                odml_id: odmlId,
                action: 'approve'
            };
            
            console.log('Sending request to update recipient:', {
                url: '../../backend/php/update_recipient_odml.php',
                method: 'POST',
                data: data
            });
            
            $.ajax({
                url: '../../backend/php/update_recipient_odml.php',
                method: 'POST',
                contentType: 'application/json',
                data: JSON.stringify(data),
                success: function(response) {
                    console.log('Server response:', response);
                    try {
                        const result = typeof response === 'string' ? JSON.parse(response) : response;
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
                            console.error('Server returned error:', result);
                            Swal.fire({
                                icon: 'error',
                                title: 'Error',
                                text: result.message || 'Failed to approve recipient.'
                            });
                        }
                    } catch (e) {
                        console.error('Error parsing response:', e, 'Raw response:', response);
                        Swal.fire({
                            icon: 'error',
                            title: 'Error',
                            text: 'An unexpected error occurred. Check console for details.'
                        });
                    }
                },
                error: function(xhr, status, error) {
                    console.error('AJAX Error:', {
                        status: xhr.status,
                        statusText: xhr.statusText,
                        responseText: xhr.responseText
                    });
                    try {
                        const response = JSON.parse(xhr.responseText);
                        Swal.fire({
                            icon: 'error',
                            title: 'Error',
                            text: response.message || 'Failed to connect to the server.'
                        });
                    } catch (e) {
                        Swal.fire({
                            icon: 'error',
                            title: 'Error',
                            text: 'Failed to connect to the server.'
                        });
                    }
                },
                complete: function() {
                    // Reset button state in case the modal is still open
                    const confirmButton = Swal.getConfirmButton();
                    if (confirmButton) {
                        confirmButton.innerHTML = '<i class="fas fa-check"></i> Approve';
                        confirmButton.disabled = false;
                    }
                }
            });
        }

        function showRejectModal(recipientId, recipientName) {
            Swal.fire({
                title: '<h2 style="color: #dc3545;">Reject Recipient</h2>',
                html: `
                    <div class="modal-content">
                        <div class="recipient-info">
                            <div class="info-item">
                                <i class="fas fa-user" style="color: #dc3545;"></i>
                                <span><strong>Name:</strong> ${recipientName}</span>
                            </div>
                        </div>
                        <div class="input-container">
                            <label for="rejection_reason" class="input-label">Reason for Rejection</label>
                            <textarea id="rejection_reason" class="custom-textarea" placeholder="Enter detailed reason for rejection"></textarea>
                        </div>
                        <p class="notification-text">
                            <i class="fas fa-bell" style="color: #666;"></i>
                            An email notification with the rejection reason will be sent to the recipient.
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
                    rejectRecipient(recipientId, result.value);
                }
            });
        }

        function rejectRecipient(recipientId, reason) {
            $.ajax({
                url: '../../backend/php/update_recipient_status.php',
                method: 'POST',
                contentType: 'application/json',
                data: JSON.stringify({
                    recipient_id: recipientId,
                    request_status: 'rejected',
                    reason: reason
                }),
                success: function(response) {
                    try {
                        const result = typeof response === 'string' ? JSON.parse(response) : response;
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
                        console.error('Error parsing response:', e);
                        Swal.fire({
                            icon: 'error',
                            title: 'Error',
                            text: 'An unexpected error occurred.'
                        });
                    }
                },
                error: function(xhr, status, error) {
                    console.error('AJAX Error:', error);
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
