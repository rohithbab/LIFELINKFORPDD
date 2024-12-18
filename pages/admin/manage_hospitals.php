<?php
session_start();
require_once '../../config/connection.php';

// Check if admin is logged in
if (!isset($_SESSION['admin_id'])) {
    header("Location: ../admin_login.php");
    exit();
}

// Handle hospital rejection
if (isset($_POST['reject_hospital'])) {
    $hospital_id = $_POST['hospital_id'];
    $rejection_reason = $_POST['rejection_reason'];
    
    // Update hospital status
    $update_query = "UPDATE hospitals SET status = 'rejected' WHERE hospital_id = ?";
    $stmt = $conn->prepare($update_query);
    $stmt->bind_param("i", $hospital_id);
    $stmt->execute();
    
    // Store rejection details in session
    $_SESSION['rejection_details'][$hospital_id] = [
        'reason' => $rejection_reason,
        'date' => date('Y-m-d H:i:s'),
        'email_sent' => false
    ];
    
    // Send email notification
    $get_hospital = "SELECT name, email FROM hospitals WHERE hospital_id = ?";
    $stmt = $conn->prepare($get_hospital);
    $stmt->bind_param("i", $hospital_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $hospital = $result->fetch_assoc();
    
    $to = $hospital['email'];
    $subject = "Hospital Registration Status Update - LifeLink";
    $message = "Dear " . $hospital['name'] . ",\n\n";
    $message .= "Your hospital registration with LifeLink has been reviewed by the admin.\n\n";
    $message .= "Status: REJECTED\n";
    $message .= "Reason: " . $rejection_reason . "\n\n";
    $message .= "If you wish to reapply, please ensure to address the above concerns.\n\n";
    $message .= "Best Regards,\nLifeLink Admin Team";
    
    mail($to, $subject, $message);
    $_SESSION['rejection_details'][$hospital_id]['email_sent'] = true;
}

// Fetch all hospitals with their status
$query = "
    SELECT 
        hospital_id,
        name,
        email,
        phone,
        address,
        license_number,
        license_file,
        status,
        created_at,
        CASE 
            WHEN status = 'pending' AND created_at > NOW() - INTERVAL 24 HOUR 
            THEN 1 ELSE 0 
        END as is_new
    FROM hospitals 
    ORDER BY 
        CASE status
            WHEN 'pending' THEN 1
            WHEN 'approved' THEN 2
            WHEN 'rejected' THEN 3
        END,
        created_at DESC
";

$result = $conn->query($query);
$hospitals = [];
if ($result) {
    while ($row = $result->fetch_assoc()) {
        $hospitals[] = $row;
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Hospitals - LifeLink Admin</title>
    <link rel="stylesheet" href="../../assets/css/admin.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        .admin-container {
            display: flex;
            min-height: 100vh;
        }
        
        .admin-main {
            flex: 1;
            padding: 20px;
            position: relative;
        }
        
        .back-button {
            position: absolute;
            top: 20px;
            right: 20px;
            display: inline-flex;
            align-items: center;
            padding: 10px 20px;
            background: linear-gradient(135deg, #1a73e8, #34a853);
            color: white;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            text-decoration: none;
            font-weight: 500;
            transition: transform 0.2s;
        }

        .back-button:hover {
            transform: translateY(-2px);
        }
        
        .page-title {
            font-size: 2.5em;
            margin: 40px 0 30px;
            background: linear-gradient(135deg, #1a73e8, #34a853);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            font-weight: bold;
            text-align: center;
        }
        
        .hospital-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
            background: white;
            box-shadow: 0 1px 3px rgba(0,0,0,0.1);
        }
        
        .hospital-table th {
            background: linear-gradient(135deg, #1a73e8, #34a853);
            color: white;
            padding: 15px;
            text-align: left;
            font-weight: 600;
        }
        
        .hospital-table td {
            padding: 12px 15px;
            border-bottom: 1px solid #eee;
            color: #333;
            font-weight: 500;
        }
        
        .status-badge {
            padding: 5px 10px;
            border-radius: 15px;
            font-size: 0.85em;
            font-weight: 600;
        }
        
        .pending { background-color: #ffd700; color: #000; }
        .approved { background-color: #34a853; color: white; }
        .rejected { background-color: #dc3545; color: white; }
        
        .action-buttons button {
            padding: 5px 10px;
            border: none;
            border-radius: 3px;
            cursor: pointer;
            margin-right: 5px;
            font-weight: 500;
        }
        
        .view-btn {
            background-color: #1a73e8;
            color: white;
        }
        
        .reject-btn {
            background-color: #dc3545;
            color: white;
        }
        
        /* Modal Styles */
        .modal {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(0,0,0,0.5);
        }
        
        .modal-content {
            background-color: white;
            margin: 15% auto;
            padding: 20px;
            width: 50%;
            border-radius: 5px;
        }
        
        .close {
            float: right;
            cursor: pointer;
            font-size: 24px;
        }
        
        .filter-buttons {
            margin: 20px 0;
            text-align: center;
        }
        
        .filter-btn {
            padding: 8px 15px;
            margin-right: 10px;
            border: none;
            border-radius: 3px;
            cursor: pointer;
            background-color: #f0f0f0;
            color: #333;
            font-weight: 500;
            transition: all 0.3s ease;
        }
        
        .filter-btn.active {
            background: linear-gradient(135deg, #1a73e8, #34a853);
            color: white;
        }

        .new-badge {
            background: #ff6b6b;
            color: white;
            padding: 2px 6px;
            border-radius: 10px;
            font-size: 0.7em;
            margin-left: 5px;
            font-weight: bold;
        }
    </style>
</head>
<body>
    <div class="admin-container">
        <main class="admin-main">
            <a href="admin_dashboard.php" class="back-button">
                <i class="fas fa-arrow-left"></i>&nbsp; Back to Dashboard
            </a>
            
            <h1 class="page-title">Manage Hospitals</h1>
            
            <div class="filter-buttons">
                <button class="filter-btn active" data-status="all">All</button>
                <button class="filter-btn" data-status="pending">Pending</button>
                <button class="filter-btn" data-status="approved">Approved</button>
                <button class="filter-btn" data-status="rejected">Rejected</button>
            </div>
            
            <table class="hospital-table">
                <thead>
                    <tr>
                        <th>Hospital Name</th>
                        <th>Email</th>
                        <th>Phone</th>
                        <th>License Number</th>
                        <th>Registration Date</th>
                        <th>Status</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($hospitals as $hospital): ?>
                        <tr class="hospital-row <?php echo $hospital['status']; ?>">
                            <td>
                                <?php echo htmlspecialchars($hospital['name']); ?>
                                <?php if ($hospital['is_new']): ?>
                                    <span class="new-badge">New</span>
                                <?php endif; ?>
                            </td>
                            <td><?php echo htmlspecialchars($hospital['email']); ?></td>
                            <td><?php echo htmlspecialchars($hospital['phone']); ?></td>
                            <td><?php echo htmlspecialchars($hospital['license_number']); ?></td>
                            <td><?php echo date('Y-m-d', strtotime($hospital['created_at'])); ?></td>
                            <td>
                                <span class="status-badge <?php echo $hospital['status']; ?>">
                                    <?php echo ucfirst($hospital['status']); ?>
                                </span>
                            </td>
                            <td class="action-buttons">
                                <button class="view-btn" onclick="viewHospital(<?php echo $hospital['hospital_id']; ?>)">
                                    <i class="fas fa-eye"></i> View
                                </button>
                                <?php if ($hospital['status'] !== 'rejected'): ?>
                                    <button class="reject-btn" onclick="showRejectModal(<?php echo $hospital['hospital_id']; ?>)">
                                        <i class="fas fa-times"></i> Reject
                                    </button>
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </main>
    </div>

    <!-- Reject Hospital Modal -->
    <div id="rejectModal" class="modal">
        <div class="modal-content">
            <span class="close">&times;</span>
            <h3>Reject Hospital</h3>
            <form id="rejectForm" method="POST">
                <input type="hidden" name="hospital_id" id="reject_hospital_id">
                <div>
                    <label for="rejection_reason">Reason for Rejection:</label>
                    <textarea name="rejection_reason" id="rejection_reason" rows="4" required></textarea>
                </div>
                <button type="submit" name="reject_hospital" class="reject-btn">
                    Confirm Rejection
                </button>
            </form>
        </div>
    </div>

    <script>
        // Filter functionality
        document.querySelectorAll('.filter-btn').forEach(button => {
            button.addEventListener('click', function() {
                // Update active button
                document.querySelectorAll('.filter-btn').forEach(btn => btn.classList.remove('active'));
                this.classList.add('active');
                
                // Filter table rows
                const status = this.dataset.status;
                document.querySelectorAll('.hospital-row').forEach(row => {
                    if (status === 'all' || row.classList.contains(status)) {
                        row.style.display = '';
                    } else {
                        row.style.display = 'none';
                    }
                });
            });
        });

        // Modal functionality
        const modal = document.getElementById('rejectModal');
        const span = document.getElementsByClassName('close')[0];

        function showRejectModal(hospitalId) {
            document.getElementById('reject_hospital_id').value = hospitalId;
            modal.style.display = 'block';
        }

        span.onclick = function() {
            modal.style.display = 'none';
        }

        window.onclick = function(event) {
            if (event.target == modal) {
                modal.style.display = 'none';
            }
        }

        function viewHospital(hospitalId) {
            // Implement view functionality
            // This will show detailed hospital information
            window.location.href = `view_hospital.php?id=${hospitalId}`;
        }
    </script>
</body>
</html>
