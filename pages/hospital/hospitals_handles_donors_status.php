<?php
session_start();
require_once '../../config/db_connect.php';

if (!isset($_SESSION['hospital_id'])) {
    header("Location: ../hospital_login.php");
    exit();
}

$hospital_id = $_SESSION['hospital_id'];

// Fetch approved donors for this hospital
try {
    $stmt = $conn->prepare("
        SELECT hda.*, d.name as donor_name, d.phone as donor_phone, 
               d.blood_group, hda.request_date, hda.approval_date
        FROM hospital_donor_approvals hda
        JOIN donor d ON hda.donor_id = d.donor_id
        WHERE hda.hospital_id = ? AND hda.status = 'Approved'
        ORDER BY hda.approval_date DESC");
    
    $stmt->execute([$hospital_id]);
    $approved_donors = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch(PDOException $e) {
    die("Error fetching donors: " . $e->getMessage());
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Donors - LifeLink</title>
    <link rel="stylesheet" href="../../assets/css/donor-dashboard.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <style>
        .table-responsive {
            margin: 20px 0;
            background: white;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }
        .modern-table {
            width: 100%;
            border-collapse: collapse;
        }
        .modern-table th, .modern-table td {
            padding: 12px;
            text-align: left;
            border-bottom: 1px solid #dee2e6;
        }
        .modern-table th {
            background-color: #f8f9fa;
            font-weight: 600;
        }
        .modern-table tbody tr:hover {
            background-color: #f8f9fa;
        }
        .reject-btn {
            background-color: #dc3545;
            color: white;
            border: none;
            padding: 8px 16px;
            border-radius: 4px;
            cursor: pointer;
            transition: background-color 0.3s;
        }
        .reject-btn:hover {
            background-color: #c82333;
        }
        .modal {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(0,0,0,0.5);
            z-index: 1000;
        }
        .modal-content {
            position: relative;
            background-color: white;
            margin: 10% auto;
            padding: 20px;
            width: 50%;
            border-radius: 8px;
            box-shadow: 0 4px 6px rgba(0,0,0,0.1);
        }
        .close-btn {
            position: absolute;
            right: 20px;
            top: 10px;
            font-size: 24px;
            cursor: pointer;
        }
        .rejection-form {
            margin-top: 20px;
        }
        .rejection-form textarea {
            width: 100%;
            padding: 10px;
            margin: 10px 0;
            border: 1px solid #ddd;
            border-radius: 4px;
            min-height: 100px;
        }
        .rejection-form button {
            background-color: #dc3545;
            color: white;
            border: none;
            padding: 10px 20px;
            border-radius: 4px;
            cursor: pointer;
        }
        .blood-type {
            background-color: #e9ecef;
            padding: 4px 8px;
            border-radius: 4px;
            font-weight: 500;
        }
        .date-cell {
            white-space: nowrap;
        }
    </style>
</head>
<body>
    <div class="dashboard-container">
        <aside class="sidebar">
            <div class="sidebar-header">
                <i class="fas fa-heartbeat"></i>
                <span>LifeLink</span>
            </div>
            <nav class="sidebar-nav">
                <ul>
                    <li>
                        <a href="hospital_dashboard.php">
                            <i class="fas fa-home"></i>
                            <span>Dashboard</span>
                        </a>
                    </li>
                    <li>
                        <a href="hospitals_handles_donors_status.php" class="active">
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

        <main class="main-content">
            <div class="main-section">
                <header class="dashboard-header">
                    <h1>Manage Approved Donors</h1>
                </header>

                <div class="table-responsive">
                    <table class="modern-table">
                        <thead>
                            <tr>
                                <th>Donor Name</th>
                                <th>Phone</th>
                                <th>Organ Type</th>
                                <th>Blood Type</th>
                                <th>Request Date</th>
                                <th>Approval Date</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (count($approved_donors) > 0): ?>
                                <?php foreach ($approved_donors as $donor): ?>
                                    <tr>
                                        <td><?php echo htmlspecialchars($donor['donor_name']); ?></td>
                                        <td><?php echo htmlspecialchars($donor['donor_phone']); ?></td>
                                        <td><?php echo htmlspecialchars($donor['organ_type']); ?></td>
                                        <td>
                                            <span class="blood-type">
                                                <?php echo htmlspecialchars($donor['blood_group']); ?>
                                            </span>
                                        </td>
                                        <td class="date-cell">
                                            <?php echo date('Y-m-d H:i', strtotime($donor['request_date'])); ?>
                                        </td>
                                        <td class="date-cell">
                                            <?php echo date('Y-m-d H:i', strtotime($donor['approval_date'])); ?>
                                        </td>
                                        <td>
                                            <button class="reject-btn" onclick="showRejectionModal(<?php echo $donor['donor_id']; ?>)">
                                                <i class="fas fa-times"></i> Reject
                                            </button>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="7" style="text-align: center; padding: 20px;">
                                        No approved donors found
                                    </td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </main>
    </div>

    <!-- Rejection Modal -->
    <div id="rejectionModal" class="modal">
        <div class="modal-content">
            <span class="close-btn" onclick="closeRejectionModal()">&times;</span>
            <h2>Reject Donor</h2>
            <p>Please provide a reason for rejection:</p>
            <form id="rejectionForm" class="rejection-form">
                <input type="hidden" id="donorId" name="donor_id">
                <textarea id="rejectionReason" name="rejection_reason" required 
                          placeholder="Enter reason for rejection..."></textarea>
                <button type="submit">Confirm Rejection</button>
            </form>
        </div>
    </div>

    <script>
        function showRejectionModal(donorId) {
            document.getElementById('donorId').value = donorId;
            document.getElementById('rejectionModal').style.display = 'block';
        }

        function closeRejectionModal() {
            document.getElementById('rejectionModal').style.display = 'none';
            document.getElementById('rejectionForm').reset();
        }

        document.getElementById('rejectionForm').addEventListener('submit', function(e) {
            e.preventDefault();
            
            const donorId = document.getElementById('donorId').value;
            const rejectionReason = document.getElementById('rejectionReason').value;

            $.ajax({
                url: '../../ajax/update_donor_status.php',
                method: 'POST',
                data: {
                    donor_id: donorId,
                    status: 'Rejected',
                    rejection_reason: rejectionReason
                },
                success: function(response) {
                    const result = JSON.parse(response);
                    if (result.success) {
                        alert('Donor rejected successfully');
                        location.reload();
                    } else {
                        alert('Error: ' + result.message);
                    }
                },
                error: function() {
                    alert('An error occurred while processing your request');
                }
            });

            closeRejectionModal();
        });
    </script>
</body>
</html>
