<?php
session_start();
require_once '../../config/connection.php';

// Check if admin is logged in
if (!isset($_SESSION['admin_id'])) {
    header("Location: admin_login.php");
    exit();
}

// Fetch pending hospital registrations
$stmt = $conn->prepare("
    SELECT id, name, email, phone, address, license_number, registration_date
    FROM hospitals 
    WHERE status = 'pending'
    ORDER BY registration_date DESC
");
$stmt->execute();
$pending_hospitals = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Hospital Registrations - LifeLink Admin</title>
    <link rel="stylesheet" href="../../assets/css/admin.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        .hospital-card {
            background: white;
            border-radius: 10px;
            padding: 20px;
            margin-bottom: 20px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }

        .hospital-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 15px;
        }

        .hospital-name {
            font-size: 1.2rem;
            font-weight: 600;
            color: #2c3e50;
        }

        .registration-date {
            color: #7f8c8d;
            font-size: 0.9rem;
        }

        .hospital-details {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 15px;
            margin-bottom: 20px;
        }

        .detail-item {
            display: flex;
            flex-direction: column;
        }

        .detail-label {
            font-size: 0.9rem;
            color: #7f8c8d;
            margin-bottom: 5px;
        }

        .detail-value {
            color: #2c3e50;
        }

        .license-section {
            background: #f8f9fa;
            padding: 15px;
            border-radius: 8px;
            margin-bottom: 20px;
        }

        .action-buttons {
            display: flex;
            gap: 10px;
        }

        .btn {
            padding: 8px 15px;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            font-size: 0.9rem;
            display: flex;
            align-items: center;
            gap: 5px;
            transition: all 0.3s ease;
        }

        .btn-view {
            background: #3498db;
            color: white;
        }

        .btn-approve {
            background: #2ecc71;
            color: white;
        }

        .btn-reject {
            background: #e74c3c;
            color: white;
        }

        .btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 2px 5px rgba(0,0,0,0.2);
        }

        .modal {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0,0,0,0.5);
            justify-content: center;
            align-items: center;
        }

        .modal-content {
            background: white;
            padding: 30px;
            border-radius: 10px;
            max-width: 500px;
            width: 90%;
        }

        .odml-form {
            display: flex;
            flex-direction: column;
            gap: 15px;
        }

        .odml-input {
            padding: 10px;
            border: 1px solid #ddd;
            border-radius: 5px;
            font-size: 1rem;
        }

        .modal-buttons {
            display: flex;
            justify-content: flex-end;
            gap: 10px;
            margin-top: 20px;
        }

        #generateODML {
            background: #3498db;
            color: white;
            border: none;
            padding: 5px 10px;
            border-radius: 3px;
            cursor: pointer;
        }
    </style>
</head>
<body>
    <div class="admin-container">
        <?php include 'admin_sidebar.php'; ?>

        <main class="admin-main">
            <h1>Manage Hospital Registrations</h1>
            
            <?php if (empty($pending_hospitals)): ?>
                <p>No pending hospital registrations.</p>
            <?php else: ?>
                <?php foreach ($pending_hospitals as $hospital): ?>
                    <div class="hospital-card">
                        <div class="hospital-header">
                            <span class="hospital-name"><?php echo htmlspecialchars($hospital['name']); ?></span>
                            <span class="registration-date">
                                Registered: <?php echo date('M j, Y g:i A', strtotime($hospital['registration_date'])); ?>
                            </span>
                        </div>

                        <div class="hospital-details">
                            <div class="detail-item">
                                <span class="detail-label">Email</span>
                                <span class="detail-value"><?php echo htmlspecialchars($hospital['email']); ?></span>
                            </div>
                            <div class="detail-item">
                                <span class="detail-label">Phone</span>
                                <span class="detail-value"><?php echo htmlspecialchars($hospital['phone']); ?></span>
                            </div>
                            <div class="detail-item">
                                <span class="detail-label">License Number</span>
                                <span class="detail-value"><?php echo htmlspecialchars($hospital['license_number']); ?></span>
                            </div>
                        </div>

                        <div class="license-section">
                            <div class="detail-label">Hospital Address</div>
                            <div class="detail-value"><?php echo nl2br(htmlspecialchars($hospital['address'])); ?></div>
                        </div>

                        <div class="action-buttons">
                            <button class="btn btn-view" onclick="viewLicense('<?php echo $hospital['license_number']; ?>')">
                                <i class="fas fa-eye"></i> View License
                            </button>
                            <button class="btn btn-approve" onclick="showApprovalModal(<?php echo $hospital['id']; ?>, '<?php echo htmlspecialchars($hospital['name']); ?>')">
                                <i class="fas fa-check"></i> Approve & Generate ODML ID
                            </button>
                            <button class="btn btn-reject" onclick="rejectHospital(<?php echo $hospital['id']; ?>)">
                                <i class="fas fa-times"></i> Reject
                            </button>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </main>
    </div>

    <!-- Approval Modal -->
    <div id="approvalModal" class="modal">
        <div class="modal-content">
            <h2>Generate ODML ID</h2>
            <p id="hospitalName"></p>
            <form id="odmlForm" class="odml-form">
                <input type="hidden" id="hospitalId" name="hospitalId">
                <div>
                    <label for="odmlId">ODML ID</label>
                    <div style="display: flex; gap: 10px;">
                        <input type="text" id="odmlId" name="odmlId" class="odml-input" required>
                        <button type="button" id="generateODML">
                            <i class="fas fa-sync"></i>
                        </button>
                    </div>
                </div>
                <div class="modal-buttons">
                    <button type="button" class="btn" onclick="closeModal()">Cancel</button>
                    <button type="submit" class="btn btn-approve">Approve & Send</button>
                </div>
            </form>
        </div>
    </div>

    <script>
        function generateODMLId() {
            const prefix = 'ODML';
            const timestamp = Date.now().toString().slice(-6);
            const random = Math.floor(Math.random() * 1000).toString().padStart(3, '0');
            return `${prefix}${timestamp}${random}`;
        }

        document.getElementById('generateODML').addEventListener('click', function() {
            document.getElementById('odmlId').value = generateODMLId();
        });

        function showApprovalModal(hospitalId, hospitalName) {
            const modal = document.getElementById('approvalModal');
            document.getElementById('hospitalId').value = hospitalId;
            document.getElementById('hospitalName').textContent = `Approving: ${hospitalName}`;
            document.getElementById('odmlId').value = generateODMLId();
            modal.style.display = 'flex';
        }

        function closeModal() {
            document.getElementById('approvalModal').style.display = 'none';
        }

        document.getElementById('odmlForm').addEventListener('submit', function(e) {
            e.preventDefault();
            const formData = new FormData(this);
            
            fetch('../../backend/php/approve_hospital.php', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    alert('Hospital approved successfully!');
                    location.reload();
                } else {
                    alert('Error: ' + data.error);
                }
            })
            .catch(error => {
                alert('Error: ' + error.message);
            });
        });

        function rejectHospital(hospitalId) {
            if (confirm('Are you sure you want to reject this hospital registration?')) {
                fetch('../../backend/php/reject_hospital.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                    },
                    body: JSON.stringify({ hospitalId: hospitalId })
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        alert('Hospital registration rejected.');
                        location.reload();
                    } else {
                        alert('Error: ' + data.error);
                    }
                })
                .catch(error => {
                    alert('Error: ' + error.message);
                });
            }
        }

        function viewLicense(licenseNumber) {
            window.open(`../../uploads/licenses/${licenseNumber}`, '_blank');
        }
    </script>
</body>
</html>
