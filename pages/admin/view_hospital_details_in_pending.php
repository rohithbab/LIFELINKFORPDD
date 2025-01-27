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
        }
        
        .details-container {
            background: white;
            border-radius: 10px;
            padding: 30px;
            margin: 0 20px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            max-width: 1200px;
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
                    <a href="admin_dashboard.php" class="back-btn">
                        <i class="fas fa-arrow-left"></i> Back to Dashboard
                    </a>
                    <button class="approve-btn" 
                            data-hospital-id="<?php echo $hospital['hospital_id']; ?>"
                            data-name="<?php echo htmlspecialchars($hospital['name']); ?>"
                            data-email="<?php echo htmlspecialchars($hospital['email']); ?>"
                            onclick="showODMLUpdateModal('hospital', '<?php echo $hospital['hospital_id']; ?>', '<?php echo htmlspecialchars($hospital['name']); ?>', '<?php echo htmlspecialchars($hospital['email']); ?>')">
                        <i class="fas fa-check"></i> Approve
                    </button>
                    <button class="reject-btn" 
                            data-hospital-id="<?php echo $hospital['hospital_id']; ?>"
                            data-name="<?php echo htmlspecialchars($hospital['name']); ?>"
                            data-email="<?php echo htmlspecialchars($hospital['email']); ?>"
                            onclick="updateHospitalStatus('<?php echo $hospital['hospital_id']; ?>', 'Rejected')">
                        <i class="fas fa-times"></i> Reject
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Scripts -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11.7.32/dist/sweetalert2.all.min.js"></script>
    <script src="js/admin_rejection.js"></script>
    <script src="../../assets/js/odml-update.js"></script>
    <script>
    function showODMLUpdateModal(type, id, name, email) {
        Swal.fire({
            title: 'Update ODML ID',
            html: `
                <div class="info-section">
                    <div class="icon-container">
                        <i class="fas fa-info-circle"></i>
                    </div>
                    <p class="modal-message">
                        You are about to update the ODML ID for:<br>
                        <strong>${name}</strong>
                    </p>
                </div>
                <div class="odml-input-container">
                    <label for="odmlId">Enter ODML ID:</label>
                    <input type="text" id="odmlId" class="swal2-input" placeholder="Enter ODML ID" required>
                </div>
                <div class="confirmation-text">
                    <i class="fas fa-envelope"></i>
                    An email notification will be sent to <strong>${email}</strong> with the ODML ID details.
                </div>
                <div class="status-update-text">
                    <i class="fas fa-check-circle"></i>
                    This action will approve the ${type}'s registration.
                </div>
            `,
            showCancelButton: true,
            confirmButtonText: 'Approve & Update',
            cancelButtonText: 'Cancel',
            confirmButtonColor: '#28a745',
            cancelButtonColor: '#dc3545',
            showLoaderOnConfirm: true,
            preConfirm: () => {
                const odmlId = document.getElementById('odmlId').value;
                if (!odmlId) {
                    Swal.showValidationMessage('Please enter ODML ID');
                    return false;
                }
                return updateODML(id, type, odmlId);
            }
        }).then((result) => {
            if (result.isConfirmed) {
                Swal.fire({
                    icon: 'success',
                    title: 'Success!',
                    text: 'ODML ID updated successfully',
                    timer: 1500,
                    showConfirmButton: false
                }).then(() => {
                    window.location.href = 'admin_dashboard.php';
                });
            }
        });
    }
    </script>
</body>
</html>
