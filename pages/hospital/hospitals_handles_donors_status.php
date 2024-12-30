<?php
session_start();
require_once '../../config/db_connect.php';

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
    <title>Manage Donors - <?php echo htmlspecialchars($hospital['name']); ?></title>
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
                    <li class="active">
                        <a href="hospitals_handles_donors_status.php">
                            <i class="fas fa-users"></i>
                            <span>Manage Donors</span>
                        </a>
                    </li>
                    <li>
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
                    <h1>Manage Donors</h1>
                </header>

                <div class="table-responsive">
                    <?php if (empty($approved_donors)): ?>
                        <div class="empty-state">
                            <i class="fas fa-inbox"></i>
                            <h3>No Approved Donors</h3>
                            <p>There are no approved donor requests at this time.</p>
                        </div>
                    <?php else: ?>
                        <table class="modern-table">
                            <thead>
                                <tr>
                                    <th>Donor Name</th>
                                    <th>Blood Group</th>
                                    <th>Phone</th>
                                    <th>Request Date</th>
                                    <th>Approval Date</th>
                                    <th>Status</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($approved_donors as $donor): ?>
                                    <tr>
                                        <td><?php echo htmlspecialchars($donor['donor_name']); ?></td>
                                        <td>
                                            <span class="blood-badge">
                                                <?php echo htmlspecialchars($donor['blood_group']); ?>
                                            </span>
                                        </td>
                                        <td><?php echo htmlspecialchars($donor['donor_phone']); ?></td>
                                        <td><?php echo date('Y-m-d', strtotime($donor['request_date'])); ?></td>
                                        <td><?php echo date('Y-m-d', strtotime($donor['approval_date'])); ?></td>
                                        <td>
                                            <span class="status-badge status-approved">
                                                Approved
                                            </span>
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
</body>
</html>
