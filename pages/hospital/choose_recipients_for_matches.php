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
        (
            -- Regular recipients
            SELECT 
                r.*,
                ha.organ_type,
                'Own' as recipient_type,
                NULL as shared_from_hospital
            FROM recipient r
            JOIN hospital_recipient_approvals ha ON r.recipient_id = ha.recipient_id
            WHERE ha.hospital_id = ? 
            AND ha.status = 'Approved'
            AND ha.is_matched = FALSE
        )
        UNION
        (
            -- Shared recipients
            SELECT 
                r.*,
                sra.organ_type,
                'Shared' as recipient_type,
                h2.name as shared_from_hospital
            FROM recipient r
            JOIN shared_recipient_approvals sra ON r.recipient_id = sra.recipient_id
            JOIN hospitals h2 ON h2.hospital_id = sra.from_hospital_id
            WHERE sra.to_hospital_id = ?
            AND sra.is_matched = FALSE
        )
        ORDER BY name ASC
    ");
    
    $stmt->execute([$hospital_id, $hospital_id]);
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
            gap: 1rem;
            margin-bottom: 1.5rem;
            flex-wrap: wrap;
        }

        .filter-btn {
            padding: 0.5rem 1.5rem;
            border: none;
            border-radius: 20px;
            cursor: pointer;
            transition: all 0.3s ease;
            background: #f0f0f0;
            color: #666;
        }

        .filter-btn.active {
            background: linear-gradient(135deg, var(--primary-blue), var(--primary-green));
            color: white;
        }

        .shared-recipient {
            background-color: rgba(33, 150, 243, 0.05);
            border-left: 4px solid var(--primary-blue);
        }
        
        .shared-badge {
            display: inline-block;
            background: var(--primary-blue);
            color: white;
            padding: 0.3rem 0.8rem;
            border-radius: 15px;
            font-size: 0.85em;
            margin-top: 0.5rem;
        }

        .shared-badge i {
            margin-right: 0.3rem;
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

        .recipients-table tr:hover {
            background-color: #f8f9fa;
        }

        .empty-state {
            text-align: center;
            padding: 3rem;
            color: #666;
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
                    <button class="filter-btn active" data-filter="all">All Recipients</button>
                    <button class="filter-btn" data-filter="own">Own Recipients</button>
                    <button class="filter-btn" data-filter="shared">Shared Recipients</button>
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
                                <th>Organ Type</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($recipients as $recipient): ?>
                                <tr class="<?php echo $recipient['recipient_type'] === 'Shared' ? 'shared-recipient' : ''; ?>">
                                    <td>
                                        <?php echo htmlspecialchars($recipient['name']); ?>
                                        <?php if ($recipient['recipient_type'] === 'Shared'): ?>
                                            <div class="shared-badge">
                                                <i class="fas fa-share-alt"></i> 
                                                Shared from <?php echo htmlspecialchars($recipient['shared_from_hospital']); ?>
                                            </div>
                                        <?php endif; ?>
                                    </td>
                                    <td><?php echo htmlspecialchars($recipient['blood_group']); ?></td>
                                    <td><?php echo htmlspecialchars($recipient['organ_type']); ?></td>
                                    <td>
                                        <button class="action-btn" onclick="selectRecipient(<?php echo $recipient['recipient_id']; ?>)">
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
        function selectRecipient(recipientId) {
            // TODO: Implement recipient selection logic
            console.log('Selected recipient:', recipientId);
        }

        // Search functionality
        const searchInput = document.getElementById('searchInput');
        searchInput.addEventListener('input', function() {
            const searchTerm = this.value.toLowerCase();
            const rows = document.querySelectorAll('.recipients-table tbody tr');
            
            rows.forEach(row => {
                const name = row.querySelector('td:first-child').textContent.toLowerCase();
                const bloodGroup = row.querySelector('td:nth-child(2)').textContent.toLowerCase();
                const organType = row.querySelector('td:nth-child(3)').textContent.toLowerCase();
                
                if (name.includes(searchTerm) || bloodGroup.includes(searchTerm) || organType.includes(searchTerm)) {
                    row.style.display = '';
                } else {
                    row.style.display = 'none';
                }
            });
        });

        // Filter functionality
        const filterButtons = document.querySelectorAll('.filter-btn');
        filterButtons.forEach(button => {
            button.addEventListener('click', function() {
                // Update active button
                filterButtons.forEach(btn => btn.classList.remove('active'));
                this.classList.add('active');

                const filter = this.dataset.filter;
                const rows = document.querySelectorAll('.recipients-table tbody tr');

                rows.forEach(row => {
                    if (filter === 'all') {
                        row.style.display = '';
                    } else if (filter === 'own') {
                        row.style.display = row.classList.contains('shared-recipient') ? 'none' : '';
                    } else if (filter === 'shared') {
                        row.style.display = row.classList.contains('shared-recipient') ? '' : 'none';
                    }
                });
            });
        });
    </script>
</body>
</html>
