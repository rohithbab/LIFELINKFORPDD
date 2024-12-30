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
        /* Table Container Styling */
        .table-container {
            background: #ffffff;
            border-radius: 10px;
            box-shadow: 0 0 20px rgba(0, 0, 0, 0.1);
            padding: 25px;
            margin: 20px 0;
        }

        .section-header {
            margin-bottom: 20px;
            padding-bottom: 15px;
            border-bottom: 2px solid #e9ecef;
        }

        .section-header h2 {
            color: #2C3E50;
            font-size: 1.5rem;
            font-weight: 600;
            margin: 0;
            background: linear-gradient(45deg, #28a745, #4a90e2);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
        }

        /* Modern Table Styling */
        .table-responsive {
            margin: 20px 0;
            background: white;
            padding: 20px;
            border-radius: 12px;
            box-shadow: 0 2px 15px rgba(0, 0, 0, 0.1);
        }

        .modern-table {
            width: 100%;
            border-collapse: separate;
            border-spacing: 0;
            background: #fff;
        }

        .modern-table th {
            background: linear-gradient(45deg, #28a745, #4a90e2);
            color: white;
            font-weight: 600;
            padding: 15px;
            text-align: left;
            font-size: 0.9rem;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            border: none;
        }

        .modern-table th:first-child {
            border-top-left-radius: 10px;
        }

        .modern-table th:last-child {
            border-top-right-radius: 10px;
        }

        .modern-table td {
            padding: 15px;
            border-bottom: 1px solid #e9ecef;
            color: #2C3E50;
            font-size: 0.95rem;
        }

        .modern-table tr:hover {
            background-color: #f8f9fa;
            transform: scale(1.01);
            transition: all 0.2s ease;
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.1);
        }

        /* Status Badge Styling */
        .status-badge {
            padding: 8px 15px;
            border-radius: 50px;
            font-size: 0.85rem;
            font-weight: 500;
            display: inline-block;
            text-transform: capitalize;
            transition: all 0.3s ease;
        }

        .status-approved {
            background: linear-gradient(45deg, #28a745, #34ce57);
            color: white;
            box-shadow: 0 2px 10px rgba(40, 167, 69, 0.2);
        }

        /* Blood Badge Styling */
        .blood-badge {
            padding: 8px 15px;
            border-radius: 50px;
            font-size: 0.85rem;
            font-weight: 600;
            background: linear-gradient(45deg, #dc3545, #fd7e14);
            color: white;
            box-shadow: 0 2px 10px rgba(220, 53, 69, 0.2);
        }

        /* Empty State Styling */
        .empty-state {
            text-align: center;
            padding: 40px 20px;
            background: #f8f9fa;
            border-radius: 10px;
            margin: 20px 0;
        }

        .empty-state i {
            font-size: 3rem;
            color: #4a90e2;
            margin-bottom: 15px;
            background: linear-gradient(45deg, #28a745, #4a90e2);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
        }

        .empty-state h3 {
            color: #2C3E50;
            font-size: 1.5rem;
            margin-bottom: 10px;
        }

        .empty-state p {
            color: #6c757d;
            font-size: 1rem;
        }

        /* Dashboard Header Styling */
        .dashboard-header {
            background: white;
            padding: 20px;
            border-radius: 10px;
            box-shadow: 0 2px 15px rgba(0, 0, 0, 0.1);
            margin-bottom: 30px;
        }

        .dashboard-header h1 {
            margin: 0;
            font-size: 2rem;
            background: linear-gradient(45deg, #28a745, #4a90e2);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
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

                <div class="table-container">
                    <?php if (empty($approved_donors)): ?>
                        <div class="empty-state">
                            <i class="fas fa-inbox"></i>
                            <h3>No Approved Donors</h3>
                            <p>There are no approved donor requests at this time.</p>
                        </div>
                    <?php else: ?>
                        <div class="section-header">
                            <h2>Approved Donors</h2>
                        </div>
                        <div class="table-responsive">
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
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </main>
    </div>
</body>
</html>
