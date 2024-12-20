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
    $stmt = $conn->prepare("SELECT * FROM hospitals WHERE hospital_id = ? AND status = 'pending'");
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
    <style>
        .details-container {
            background: white;
            border-radius: 10px;
            padding: 30px;
            margin: 20px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
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
            padding: 10px 20px;
            background: #6c757d;
            color: white;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            text-decoration: none;
            transition: background-color 0.3s;
        }

        .back-btn:hover {
            background: #5a6268;
        }

        .approve-btn, .reject-btn {
            padding: 10px 20px;
            color: white;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            transition: background-color 0.3s;
        }

        .approve-btn {
            background: #28a745;
        }

        .approve-btn:hover {
            background: #218838;
        }

        .reject-btn {
            background: #dc3545;
        }

        .reject-btn:hover {
            background: #c82333;
        }
    </style>
</head>
<body>
    <div class="admin-container">
        <!-- Sidebar -->
        <div class="sidebar">
            <div class="sidebar-header">
                <h2><span class="logo-gradient">LifeLink</span> Admin</h2>
            </div>
            <ul class="nav-menu">
                <li class="nav-item">
                    <a href="admin_dashboard.php" class="nav-link">
                        <i class="fas fa-tachometer-alt"></i>
                        Dashboard
                    </a>
                </li>
                <!-- Other sidebar items -->
            </ul>
        </div>

        <!-- Main Content -->
        <div class="main-content">
            <div class="details-container">
                <div class="details-header">
                    <h2>Hospital Details</h2>
                </div>

                <div class="details-grid">
                    <div class="detail-item">
                        <h3>Hospital Name</h3>
                        <p><?php echo htmlspecialchars($hospital['hospital_name']); ?></p>
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
                        <h3>City</h3>
                        <p><?php echo htmlspecialchars($hospital['city']); ?></p>
                    </div>
                    <div class="detail-item">
                        <h3>State</h3>
                        <p><?php echo htmlspecialchars($hospital['state']); ?></p>
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
                    <button class="approve-btn" onclick="updateHospitalStatus(<?php echo $hospital['hospital_id']; ?>, 'approved')">
                        <i class="fas fa-check"></i> Approve
                    </button>
                    <button class="reject-btn" onclick="updateHospitalStatus(<?php echo $hospital['hospital_id']; ?>, 'rejected')">
                        <i class="fas fa-times"></i> Reject
                    </button>
                </div>
            </div>
        </div>
    </div>

    <script>
        function updateHospitalStatus(hospitalId, status) {
            if (confirm(`Are you sure you want to ${status} this hospital?`)) {
                fetch('../../backend/php/update_hospital_status.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/x-www-form-urlencoded',
                    },
                    body: `hospital_id=${hospitalId}&status=${status}`
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        alert(`Hospital ${status} successfully`);
                        window.location.href = 'admin_dashboard.php';
                    } else {
                        alert('Failed to update hospital status: ' + data.message);
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    alert('Error updating hospital status');
                });
            }
        }
    </script>
</body>
</html>
