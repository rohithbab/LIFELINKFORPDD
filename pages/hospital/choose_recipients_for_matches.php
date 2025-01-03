<?php
session_start();
require_once '../../config/db_connect.php';

// Check if hospital is logged in
if (!isset($_SESSION['hospital_logged_in']) || !$_SESSION['hospital_logged_in']) {
    header("Location: ../../pages/hospital_login.php");
    exit();
}

$hospital_id = $_SESSION['hospital_id'];
$hospital_name = $_SESSION['hospital_name'];

// Fetch hospital's recipients
try {
    $stmt = $conn->prepare("
        SELECT 
            r.*,
            ha.required_organ,
            ha.blood_group,
            ha.status as approval_status,
            ha.priority_level
        FROM recipient_registration r
        JOIN hospital_recipient_approvals ha ON r.id = ha.recipient_id
        WHERE ha.hospital_id = ? 
        AND ha.status = 'Approved'
        ORDER BY ha.priority_level DESC, r.full_name ASC
    ");
    
    $stmt->execute([$hospital_id]);
    $recipients = $stmt->fetchAll(PDO::FETCH_ASSOC);

} catch(PDOException $e) {
    error_log("Error fetching recipients: " . $e->getMessage());
    $recipients = [];
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Choose Recipients - LifeLink</title>
    <link rel="stylesheet" href="../../assets/css/hospital-dashboard.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        .search-section {
            background: white;
            padding: 2rem;
            border-radius: 15px;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.1);
            margin: 2rem;
        }

        .search-container {
            position: relative;
            margin-bottom: 1.5rem;
        }

        .search-input {
            width: 100%;
            padding: 1rem;
            padding-left: 3rem;
            border: 2px solid #e0e0e0;
            border-radius: 8px;
            font-size: 1rem;
            transition: all 0.3s ease;
        }

        .search-input:focus {
            border-color: var(--primary-blue);
            box-shadow: 0 0 0 3px rgba(33, 150, 243, 0.1);
            outline: none;
        }

        .search-icon {
            position: absolute;
            left: 1rem;
            top: 50%;
            transform: translateY(-50%);
            color: #666;
        }

        .filter-buttons {
            display: flex;
            gap: 0.5rem;
            margin-bottom: 1rem;
            flex-wrap: wrap;
        }

        .filter-btn {
            padding: 0.5rem 1rem;
            border: none;
            border-radius: 20px;
            cursor: pointer;
            transition: all 0.3s ease;
            background: #f0f0f0;
            color: #666;
            font-size: 0.9rem;
        }

        .filter-btn.active {
            background: linear-gradient(135deg, var(--primary-blue), var(--primary-green));
            color: white;
        }

        .recipients-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 1rem;
            background: white;
            border-radius: 10px;
            overflow: hidden;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.1);
        }

        .recipients-table th,
        .recipients-table td {
            padding: 1rem;
            text-align: left;
            border-bottom: 1px solid #eee;
        }

        .recipients-table th {
            background: linear-gradient(135deg, var(--primary-blue), var(--primary-green));
            color: white;
            font-weight: 500;
        }

        .priority-high {
            color: #dc3545;
            font-weight: bold;
        }

        .priority-medium {
            color: #ffc107;
            font-weight: bold;
        }

        .priority-low {
            color: #28a745;
            font-weight: bold;
        }

        .action-btn {
            padding: 0.5rem 1rem;
            border: none;
            border-radius: 5px;
            background: linear-gradient(135deg, var(--primary-blue), var(--primary-green));
            color: white;
            cursor: pointer;
            transition: all 0.3s ease;
        }

        .action-btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
        }
    </style>
</head>
<body>
    <div class="dashboard-container">
        <?php include '../../includes/hospital_sidebar.php'; ?>
        
        <main class="main-content">
            <div class="search-section">
                <h2>Choose Recipients for Matching</h2>
                <div class="search-container">
                    <i class="fas fa-search search-icon"></i>
                    <input type="text" id="searchInput" class="search-input" placeholder="Search recipients...">
                </div>

                <div class="filter-buttons">
                    <button class="filter-btn active" data-filter="blood_group">Blood Group</button>
                    <button class="filter-btn" data-filter="organs">Organs</button>
                </div>

                <?php if (empty($recipients)): ?>
                    <div class="empty-state">
                        <i class="fas fa-user-plus fa-3x" style="color: #ccc; margin-bottom: 1rem;"></i>
                        <h3>No Recipients Available</h3>
                        <p>There are no recipients available for matching at this time.</p>
                    </div>
                <?php else: ?>
                    <table class="recipients-table">
                        <thead>
                            <tr>
                                <th>Recipient Name</th>
                                <th>Blood Group</th>
                                <th>Required Organ</th>
                                <th>Priority Level</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($recipients as $recipient): ?>
                                <tr>
                                    <td><?php echo htmlspecialchars($recipient['full_name']); ?></td>
                                    <td><?php echo htmlspecialchars($recipient['blood_group']); ?></td>
                                    <td><?php echo htmlspecialchars($recipient['required_organ']); ?></td>
                                    <td class="priority-<?php echo strtolower($recipient['priority_level']); ?>">
                                        <?php echo htmlspecialchars($recipient['priority_level']); ?>
                                    </td>
                                    <td>
                                        <button class="action-btn" onclick="selectRecipient(<?php echo $recipient['id']; ?>)">
                                            Select for Match
                                        </button>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                <?php endif; ?>
            </div>
        </main>
    </div>

    <script>
        const searchInput = document.getElementById('searchInput');
        const filterButtons = document.querySelectorAll('.filter-btn');
        let currentFilter = 'blood_group';

        // Filter button click handling
        filterButtons.forEach(button => {
            button.addEventListener('click', () => {
                filterButtons.forEach(btn => btn.classList.remove('active'));
                button.classList.add('active');
                currentFilter = button.dataset.filter;
                filterRecipients();
            });
        });

        // Search functionality
        searchInput.addEventListener('input', filterRecipients);

        function filterRecipients() {
            const searchTerm = searchInput.value.toLowerCase();
            const rows = document.querySelectorAll('.recipients-table tbody tr');
            
            rows.forEach(row => {
                const bloodGroup = row.querySelector('td:nth-child(2)').textContent.toLowerCase();
                const organ = row.querySelector('td:nth-child(3)').textContent.toLowerCase();
                
                let show = false;
                if (currentFilter === 'blood_group') {
                    show = bloodGroup.includes(searchTerm);
                } else {
                    show = organ.includes(searchTerm);
                }
                
                row.style.display = show ? '' : 'none';
            });
        }

        function selectRecipient(recipientId) {
            // TODO: Implement recipient selection logic
            console.log('Selected recipient:', recipientId);
        }
    </script>
</body>
</html>
