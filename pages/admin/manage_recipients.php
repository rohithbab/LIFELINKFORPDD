<?php
session_start();
require_once '../../backend/php/connection.php';
require_once '../../backend/php/queries.php';

// Check if admin is logged in
if (!isset($_SESSION['admin_id'])) {
    header('Location: ../admin_login.php');
    exit();
}

// Get all recipients
$stmt = $conn->prepare("SELECT * FROM recipients ORDER BY created_at DESC");
$stmt->execute();
$recipients = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Recipients - LifeLink Admin</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link rel="stylesheet" href="../../assets/css/styles.css">
    <link rel="stylesheet" href="../../assets/css/admin-dashboard.css">
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
                <li class="nav-item">
                    <a href="manage_hospitals.php" class="nav-link">
                        <i class="fas fa-hospital"></i>
                        Manage Hospitals
                    </a>
                </li>
                <li class="nav-item">
                    <a href="manage_donors.php" class="nav-link">
                        <i class="fas fa-hand-holding-heart"></i>
                        Manage Donors
                    </a>
                </li>
                <li class="nav-item">
                    <a href="manage_recipients.php" class="nav-link active">
                        <i class="fas fa-user-plus"></i>
                        Manage Recipients
                    </a>
                </li>
                <li class="nav-item">
                    <a href="analytics.php" class="nav-link">
                        <i class="fas fa-chart-line"></i>
                        Analytics
                    </a>
                </li>
                <li class="nav-item">
                    <a href="notifications.php" class="nav-link">
                        <i class="fas fa-bell"></i>
                        Notifications
                    </a>
                </li>
                <li class="nav-item">
                    <a href="settings.php" class="nav-link">
                        <i class="fas fa-cog"></i>
                        Settings
                    </a>
                </li>
                <li class="nav-item">
                    <a href="../logout.php" class="nav-link">
                        <i class="fas fa-sign-out-alt"></i>
                        Logout
                    </a>
                </li>
            </ul>
        </div>

        <!-- Main Content -->
        <div class="main-content">
            <div class="content-header">
                <h1>Manage Recipients</h1>
            </div>

            <!-- Recipients Table -->
            <div class="table-container">
                <table class="dashboard-table">
                    <thead>
                        <tr>
                            <th>Name</th>
                            <th>Email</th>
                            <th>Blood Type</th>
                            <th>Needed Organ</th>
                            <th>Urgency Level</th>
                            <th>Status</th>
                            <th>Registration Date</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($recipients as $recipient): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($recipient['name']); ?></td>
                            <td><?php echo htmlspecialchars($recipient['email']); ?></td>
                            <td><?php echo htmlspecialchars($recipient['blood_type']); ?></td>
                            <td><?php echo htmlspecialchars($recipient['needed_organ']); ?></td>
                            <td>
                                <span class="status-badge status-<?php echo strtolower($recipient['urgency_level']); ?>">
                                    <?php echo ucfirst($recipient['urgency_level']); ?>
                                </span>
                            </td>
                            <td>
                                <span class="status-badge status-<?php echo strtolower($recipient['status']); ?>">
                                    <?php echo ucfirst($recipient['status']); ?>
                                </span>
                            </td>
                            <td><?php echo date('M d, Y', strtotime($recipient['created_at'])); ?></td>
                            <td>
                                <button class="btn-action btn-view" onclick="viewRecipient(<?php echo $recipient['id']; ?>)">
                                    <i class="fas fa-eye"></i>
                                </button>
                                <button class="btn-action btn-edit" onclick="editRecipient(<?php echo $recipient['id']; ?>)">
                                    <i class="fas fa-edit"></i>
                                </button>
                                <?php if ($recipient['status'] !== 'deleted'): ?>
                                <button class="btn-action btn-delete" onclick="deleteRecipient(<?php echo $recipient['id']; ?>)">
                                    <i class="fas fa-trash"></i>
                                </button>
                                <?php endif; ?>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <script>
        function viewRecipient(id) {
            // Implement view recipient details
            window.location.href = `view_recipient.php?id=${id}`;
        }

        function editRecipient(id) {
            // Implement edit recipient details
            window.location.href = `edit_recipient.php?id=${id}`;
        }

        function deleteRecipient(id) {
            if (confirm('Are you sure you want to delete this recipient?')) {
                // Implement delete recipient functionality
                fetch(`delete_recipient.php?id=${id}`, {
                    method: 'POST'
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        location.reload();
                    } else {
                        alert('Error deleting recipient');
                    }
                });
            }
        }
    </script>
</body>
</html>
