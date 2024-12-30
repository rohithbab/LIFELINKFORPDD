<?php
session_start();
require_once '../../config/db_connect.php';

// Check if hospital is logged in
if (!isset($_SESSION['hospital_id'])) {
    header("Location: ../hospital_login.php");
    exit();
}

$hospital_id = $_SESSION['hospital_id'];

// Get hospital info
try {
    $stmt = $conn->prepare("SELECT * FROM hospitals WHERE hospital_id = ?");
    $stmt->execute([$hospital_id]);
    $hospital = $stmt->fetch(PDO::FETCH_ASSOC);
} catch(PDOException $e) {
    die("Error fetching hospital details: " . $e->getMessage());
}

// Handle status updates
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['request_id']) && isset($_POST['status'])) {
    try {
        $stmt = $conn->prepare("UPDATE hospital_recipient_approvals SET status = ? WHERE approval_id = ? AND hospital_id = ?");
        $stmt->execute([$_POST['status'], $_POST['request_id'], $hospital_id]);
        
        // Redirect to prevent form resubmission
        header("Location: hospitals_handles_recipients_status.php?success=1");
        exit();
    } catch(PDOException $e) {
        $error = "Error updating status: " . $e->getMessage();
    }
}

// Fetch all recipient requests for this hospital
try {
    $stmt = $conn->prepare("
        SELECT 
            r.full_name,
            r.blood_type,
            r.organ_required,
            r.medical_condition,
            r.urgency_level,
            r.recipient_medical_reports,
            r.id_proof_type,
            r.id_proof_number,
            r.id_document,
            hra.status,
            hra.request_date,
            hra.approval_id AS request_id,
            hra.required_organ,
            hra.blood_group,
            hra.priority_level,
            hra.medical_reports AS approval_medical_reports
        FROM hospital_recipient_approvals hra
        JOIN recipient_registration r ON hra.recipient_id = r.id
        WHERE hra.hospital_id = ?
        ORDER BY 
            CASE 
                WHEN hra.status = 'pending' THEN 1
                WHEN hra.status = 'approved' THEN 2
                ELSE 3
            END,
            hra.request_date DESC
    ");
    $stmt->execute([$hospital_id]);
    $requests = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch(PDOException $e) {
    $error = "Error fetching requests: " . $e->getMessage();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Recipients - <?php echo htmlspecialchars($hospital['name']); ?></title>
    <link rel="stylesheet" href="../../assets/css/hospital-dashboard.css">
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
            border-bottom: 1px solid #e9ecef;
        }
        .modern-table th {
            background-color: #f8f9fa;
            font-weight: 600;
            color: #495057;
        }
        .modern-table tr:hover {
            background-color: #f8f9fa;
        }
        .status-badge {
            padding: 6px 12px;
            border-radius: 20px;
            font-size: 0.85em;
            font-weight: 500;
        }
        .status-pending {
            background: rgba(255, 193, 7, 0.2);
            color: #ffc107;
        }
        .status-approved {
            background: rgba(40, 167, 69, 0.2);
            color: #28a745;
        }
        .status-rejected {
            background: rgba(220, 53, 69, 0.2);
            color: #dc3545;
        }
        .action-btn {
            padding: 6px 12px;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            margin: 0 4px;
            font-size: 0.9em;
        }
        .btn-approve {
            background-color: #28a745;
            color: white;
        }
        .btn-reject {
            background-color: #dc3545;
            color: white;
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
                        <a href="hospital_dashboard.php">
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
                    <li class="active">
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
            <div class="container">
                <header class="dashboard-header">
                    <h1>Manage Recipients</h1>
                </header>

                <?php if (isset($error)): ?>
                    <div class="alert alert-danger"><?php echo $error; ?></div>
                <?php endif; ?>

                <?php if (isset($_GET['success'])): ?>
                    <div class="alert alert-success">Status updated successfully!</div>
                <?php endif; ?>

                <div class="table-responsive">
                    <?php if (empty($requests)): ?>
                        <div class="empty-state">
                            <i class="fas fa-inbox"></i>
                            <h3>No Recipient Requests</h3>
                            <p>There are no recipient requests at this time.</p>
                        </div>
                    <?php else: ?>
                        <table class="modern-table">
                            <thead>
                                <tr>
                                    <th>Recipient Name</th>
                                    <th>Blood Type</th>
                                    <th>Required Organ</th>
                                    <th>Priority Level</th>
                                    <th>Status</th>
                                    <th>Request Date</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($requests as $request): ?>
                                    <tr>
                                        <td><?php echo htmlspecialchars($request['full_name']); ?></td>
                                        <td><?php echo htmlspecialchars($request['blood_group']); ?></td>
                                        <td><?php echo htmlspecialchars($request['required_organ']); ?></td>
                                        <td>
                                            <span class="priority-badge <?php echo strtolower($request['priority_level']); ?>">
                                                <?php echo htmlspecialchars($request['priority_level']); ?>
                                            </span>
                                        </td>
                                        <td>
                                            <span class="status-badge status-<?php echo strtolower($request['status']); ?>">
                                                <?php echo htmlspecialchars($request['status']); ?>
                                            </span>
                                        </td>
                                        <td><?php echo date('Y-m-d', strtotime($request['request_date'])); ?></td>
                                        <td>
                                            <?php if ($request['status'] === 'pending'): ?>
                                                <button class="action-btn btn-approve" onclick="updateStatus(<?php echo $request['request_id']; ?>, 'approved')">
                                                    <i class="fas fa-check"></i> Approve
                                                </button>
                                                <button class="action-btn btn-reject" onclick="updateStatus(<?php echo $request['request_id']; ?>, 'rejected')">
                                                    <i class="fas fa-times"></i> Reject
                                                </button>
                                            <?php endif; ?>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    <?php endif; ?>
                </div>
            </div>
        </main>
    </div>

    <script>
        function updateStatus(requestId, status) {
            if (confirm(`Are you sure you want to ${status} this request?`)) {
                const form = document.createElement('form');
                form.method = 'POST';
                form.innerHTML = `
                    <input type="hidden" name="request_id" value="${requestId}">
                    <input type="hidden" name="status" value="${status}">
                `;
                document.body.appendChild(form);
                form.submit();
            }
        }
    </script>
</body>
</html>
