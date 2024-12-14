<?php
session_start();
require_once '../../config/connection.php';

// Check if admin is logged in
if (!isset($_SESSION['admin_id'])) {
    header("Location: ../admin_login.php");
    exit();
}

// Fetch all hospitals with their status
$query = "
    SELECT 
        hospital_id,
        hospital_name,
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
</head>
<body>
    <div class="admin-container">
        <?php include 'admin_sidebar.php'; ?>

        <main class="admin-main">
            <div class="page-header">
                <h1><i class="fas fa-hospital"></i> Manage Hospitals</h1>
            </div>

            <!-- Status Filter -->
            <div class="filter-section">
                <button class="filter-btn active" data-status="all">All</button>
                <button class="filter-btn" data-status="pending">Pending</button>
                <button class="filter-btn" data-status="approved">Approved</button>
                <button class="filter-btn" data-status="rejected">Rejected</button>
            </div>

            <div class="table-responsive">
                <table class="table">
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
                            <tr class="hospital-row <?php echo $hospital['status']; ?> <?php echo $hospital['is_new'] ? 'new-registration' : ''; ?>"
                                data-status="<?php echo $hospital['status']; ?>">
                                <td>
                                    <?php echo htmlspecialchars($hospital['hospital_name']); ?>
                                    <?php if ($hospital['is_new']): ?>
                                        <span class="new-badge">New</span>
                                    <?php endif; ?>
                                </td>
                                <td><?php echo htmlspecialchars($hospital['email']); ?></td>
                                <td><?php echo htmlspecialchars($hospital['phone']); ?></td>
                                <td><?php echo htmlspecialchars($hospital['license_number']); ?></td>
                                <td><?php echo date('Y-m-d H:i', strtotime($hospital['created_at'])); ?></td>
                                <td>
                                    <span class="status-badge <?php echo $hospital['status']; ?>">
                                        <?php echo ucfirst($hospital['status']); ?>
                                    </span>
                                </td>
                                <td>
                                    <a href="../view_license.php?hospital_id=<?php echo $hospital['hospital_id']; ?>" 
                                       target="_blank" 
                                       class="btn btn-sm btn-primary">
                                        <i class="fas fa-file-medical"></i> View License
                                    </a>
                                    
                                    <?php if ($hospital['status'] === 'pending'): ?>
                                        <button onclick="updateStatus(<?php echo $hospital['hospital_id']; ?>, 'approved')" 
                                                class="btn btn-sm btn-success">
                                            <i class="fas fa-check"></i> Approve
                                        </button>
                                        <button onclick="updateStatus(<?php echo $hospital['hospital_id']; ?>, 'rejected')" 
                                                class="btn btn-sm btn-danger">
                                            <i class="fas fa-times"></i> Reject
                                        </button>
                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </main>
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
                    if (status === 'all' || row.dataset.status === status) {
                        row.style.display = '';
                    } else {
                        row.style.display = 'none';
                    }
                });
            });
        });

        // Update hospital status
        function updateStatus(hospitalId, status) {
            if (!confirm(`Are you sure you want to ${status} this hospital?`)) {
                return;
            }

            fetch('../../backend/php/update_hospital_status.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify({
                    hospital_id: hospitalId,
                    status: status
                })
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    alert(data.message);
                    location.reload();
                } else {
                    alert('Error: ' + data.message);
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('An error occurred while updating the hospital status');
            });
        }
    </script>
</body>
</html>
