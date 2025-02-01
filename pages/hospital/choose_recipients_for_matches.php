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
                ha.required_organ,
                ha.blood_group,
                ha.priority_level,
                'Own' as recipient_type,
                NULL as shared_from_hospital,
                ha.hospital_id as recipient_hospital_id
            FROM recipient_registration r
            JOIN hospital_recipient_approvals ha ON r.id = ha.recipient_id
            WHERE ha.hospital_id = ? 
            AND ha.status = 'Approved'
            AND NOT EXISTS (
                SELECT 1 FROM donor_and_recipient_requests 
                WHERE recipient_id = r.id
            )
        )
        UNION
        (
            -- Shared recipients
            SELECT 
                r.*,
                sra.organ_type as required_organ,
                r.blood_type as blood_group,
                ha.priority_level,
                'Shared' as recipient_type,
                h2.name as shared_from_hospital,
                sra.from_hospital_id as recipient_hospital_id
            FROM recipient_registration r
            JOIN shared_recipient_approvals sra ON r.id = sra.recipient_id
            JOIN hospitals h2 ON h2.hospital_id = sra.from_hospital_id
            JOIN hospital_recipient_approvals ha ON r.id = ha.recipient_id AND ha.hospital_id = sra.from_hospital_id
            WHERE sra.to_hospital_id = ?
            AND sra.is_matched = FALSE
            AND NOT EXISTS (
                SELECT 1 FROM donor_and_recipient_requests 
                WHERE recipient_id = r.id
            )
        )
        ORDER BY priority_level DESC, full_name ASC
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
            min-height: 200px;
        }

        .dashboard-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 2rem;
            padding: 1rem 2rem;
            background: white;
            border-radius: 15px;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.1);
        }

        .header-left h2 {
            font-size: 1.8rem;
            font-weight: 600;
            background: linear-gradient(45deg, #28a745, #4a90e2);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            margin: 0;
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

        .search-results {
            margin-top: 2rem;
        }

        .recipients-table {
            width: 100%;
            border-collapse: collapse;
            background: white;
            border-radius: 10px;
            overflow: hidden;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.1);
        }

        .recipients-table th {
            background: linear-gradient(135deg, var(--primary-blue), var(--primary-green));
            color: white;
            padding: 1rem;
            text-align: left;
            font-weight: 500;
        }

        .recipients-table td {
            padding: 1rem;
            border-bottom: 1px solid #eee;
            vertical-align: middle;
        }

        .recipients-table tr:hover {
            background: #f8f9fa;
        }

        .blood-badge {
            display: inline-block;
            padding: 0.3rem 0.8rem;
            border-radius: 15px;
            background: var(--primary-blue);
            color: white;
            font-size: 0.9rem;
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

        .type-badge {
            display: inline-block;
            padding: 0.3rem 0.8rem;
            border-radius: 15px;
            font-size: 0.9rem;
            font-weight: 500;
        }

        .type-own {
            background: #e3f2fd;
            color: var(--primary-blue);
        }

        .type-shared {
            background: #f3e5f5;
            color: #9c27b0;
        }

        .action-btn {
            padding: 0.5rem 1rem;
            border: none;
            border-radius: 5px;
            background: linear-gradient(135deg, var(--primary-blue), var(--primary-green));
            color: white;
            cursor: pointer;
            transition: all 0.3s ease;
            font-size: 0.9rem;
        }

        .action-btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
        }

        .empty-state {
            text-align: center;
            padding: 3rem;
            background: #f8f9fa;
            border-radius: 10px;
            margin: 2rem 0;
        }

        .empty-state i {
            font-size: 3rem;
            color: #ccc;
            margin-bottom: 1rem;
        }

        .empty-state h3 {
            color: #333;
            margin-bottom: 0.5rem;
        }

        .empty-state p {
            color: #666;
        }

        .shared-from {
            color: #666;
            font-size: 0.9rem;
        }

        .recipient-info {
            display: flex;
            flex-direction: column;
            gap: 0.3rem;
        }

        .recipient-info strong {
            color: #333;
            font-size: 1rem;
        }

        .header-right {
            display: flex;
            align-items: center;
            gap: 1rem;
        }

        .switch-list-btn {
            padding: 0.5rem 1.5rem;
            background: linear-gradient(135deg, var(--primary-blue), var(--primary-green));
            color: white;
            border: none;
            border-radius: 25px;
            cursor: pointer;
            font-size: 0.9rem;
            display: flex;
            align-items: center;
            gap: 0.5rem;
            transition: all 0.3s ease;
            text-decoration: none;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
        }

        .switch-list-btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(0, 0, 0, 0.15);
        }

        .switch-list-btn i {
            font-size: 0.9em;
        }
    </style>
</head>
<body>
    <div class="dashboard-container">
        <?php include '../../includes/hospital_sidebar.php'; ?>
        
        <main class="main-content">
            <div class="dashboard-header" style="margin: 2rem;">
                <div class="header-left">
                    <h2>Choose Recipients</h2>
                </div>
                <div class="header-right">
                    <a href="choose_donors_for_matches.php" class="switch-list-btn">
                        <i class="fas fa-exchange-alt"></i>
                        Donors List
                    </a>
                </div>
            </div>

            <div class="search-section">
                <div class="search-container">
                    <i class="fas fa-search search-icon"></i>
                    <input type="text" 
                           id="searchInput" 
                           class="search-input" 
                           placeholder="Search recipients by name, blood group, or required organ...">
                </div>

                <div class="filter-buttons">
                    <button class="filter-btn active" data-filter="all">All</button>
                    <button class="filter-btn" data-filter="own">Own Recipients</button>
                    <button class="filter-btn" data-filter="shared">Shared Recipients</button>
                </div>

                <?php if (empty($recipients)): ?>
                    <div class="empty-state">
                        <i class="fas fa-user-plus fa-3x"></i>
                        <h3>No Recipients Available</h3>
                        <p>There are no approved recipients in your hospital at this time.</p>
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
                                <tr class="<?php echo $recipient['recipient_type'] === 'Shared' ? 'shared-recipient' : ''; ?>" 
                                    data-recipient-type="<?php echo $recipient['recipient_type']; ?>" 
                                    data-hospital-name="<?php echo $recipient['recipient_type'] === 'Shared' ? htmlspecialchars($recipient['shared_from_hospital']) : htmlspecialchars($hospital_name); ?>"
                                    data-hospital-id="<?php echo $recipient['recipient_hospital_id']; ?>">
                                    <td>
                                        <?php echo htmlspecialchars($recipient['full_name']); ?>
                                        <?php if ($recipient['recipient_type'] === 'Shared'): ?>
                                            <div>
                                                <span class="shared-badge">
                                                    <i class="fas fa-share-alt"></i>
                                                    Shared from <?php echo htmlspecialchars($recipient['shared_from_hospital']); ?>
                                                </span>
                                            </div>
                                        <?php endif; ?>
                                    </td>
                                    <td><?php echo htmlspecialchars($recipient['blood_group']); ?></td>
                                    <td><?php echo htmlspecialchars($recipient['required_organ']); ?></td>
                                    <td class="priority-<?php echo strtolower($recipient['priority_level']); ?>">
                                        <?php echo htmlspecialchars($recipient['priority_level']); ?>
                                    </td>
                                    <td>
                                        <button onclick="selectRecipient(<?php echo $recipient['id']; ?>)" class="action-btn">
                                            Select for Match
                                        </button>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                <?php endif; ?>
            </div>

            <div id="searchResults" class="search-results">
                <table class="recipients-table">
                    <thead>
                        <tr>
                            <th>Recipient Name</th>
                            <th>Blood Group</th>
                            <th>Required Organ</th>
                            <th>Priority Level</th>
                            <th>Type</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($recipients)): ?>
                            <tr>
                                <td colspan="6">
                                    <div class="empty-state">
                                        <i class="fas fa-user-slash"></i>
                                        <h3>No Recipients Found</h3>
                                        <p>There are no recipients available for matching at this time.</p>
                                    </div>
                                </td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($recipients as $recipient): ?>
                                <tr>
                                    <td>
                                        <div class="recipient-info">
                                            <strong><?php echo htmlspecialchars($recipient['full_name']); ?></strong>
                                            <?php if ($recipient['recipient_type'] === 'Shared'): ?>
                                                <small class="shared-from">Shared from: <?php echo htmlspecialchars($recipient['shared_from_hospital']); ?></small>
                                            <?php endif; ?>
                                        </div>
                                    </td>
                                    <td>
                                        <span class="blood-badge"><?php echo htmlspecialchars($recipient['blood_group']); ?></span>
                                    </td>
                                    <td><?php echo htmlspecialchars($recipient['required_organ']); ?></td>
                                    <td>
                                        <span class="priority-<?php echo strtolower($recipient['priority_level']); ?>">
                                            <?php echo htmlspecialchars($recipient['priority_level']); ?>
                                        </span>
                                    </td>
                                    <td>
                                        <span class="type-badge type-<?php echo strtolower($recipient['recipient_type']); ?>">
                                            <?php echo htmlspecialchars($recipient['recipient_type']); ?>
                                        </span>
                                    </td>
                                    <td>
                                        <button class="action-btn" onclick="selectRecipient(<?php echo $recipient['id']; ?>)">
                                            Select
                                        </button>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </main>
    </div>

    <script>
        let searchTimeout;
        const searchInput = document.getElementById('searchInput');
        const searchResults = document.getElementById('searchResults');
        const filterButtons = document.querySelectorAll('.filter-btn');
        let currentFilter = 'all';

        // Filter button click handling
        filterButtons.forEach(button => {
            button.addEventListener('click', () => {
                filterButtons.forEach(btn => btn.classList.remove('active'));
                button.classList.add('active');
                currentFilter = button.dataset.filter;
                searchInput.placeholder = `Search by ${currentFilter}...`;
                searchInput.value = '';
                searchResults.style.display = 'none';
            });
        });

        // Real-time search handling
        searchInput.addEventListener('input', () => {
            clearTimeout(searchTimeout);
            const searchTerm = searchInput.value.trim();

            if (searchTerm.length < 2) {
                searchResults.style.display = 'none';
                return;
            }

            searchTimeout = setTimeout(() => {
                fetch('../../ajax/search_recipients.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                    },
                    body: JSON.stringify({
                        search: searchTerm,
                        filter: currentFilter
                    })
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success && data.results.length > 0) {
                        displayResults(data.results);
                        searchResults.style.display = 'block';
                    } else {
                        displayResults([]);
                        searchResults.style.display = 'block';
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    displayResults([]);
                    searchResults.style.display = 'block';
                });
            }, 500);
        });

        function displayResults(results) {
            const tbody = document.getElementById('resultsBody');
            tbody.innerHTML = '';

            if (results.length === 0) {
                tbody.innerHTML = `
                    <tr>
                        <td colspan="4" class="text-center">No hospitals found matching your search criteria</td>
                    </tr>`;
                return;
            }

            results.forEach(hospital => {
                const row = document.createElement('tr');
                
                row.innerHTML = `
                    <td>${hospital.hospital_name}</td>
                    <td>
                        <div class="contact-info">
                            <span><i class="fas fa-phone"></i> ${hospital.phone}</span>
                            <span><i class="fas fa-map-marker-alt"></i> ${hospital.address}</span>
                        </div>
                    </td>
                    <td>
                        <div class="recipient-info">
                            <span class="recipient-count">Recipients: ${hospital.recipient_count}</span>
                            <span class="recipient-details">Blood Groups: ${hospital.blood_groups.join(', ')}</span>
                            <span class="recipient-details">Required Organs: ${hospital.organ_types.join(', ')}</span>
                        </div>
                    </td>
                    <td>
                        <button onclick="window.location.href='choose_other_recipients.php?hospital_id=' + ${hospital.hospital_id}" class="view-btn">
                            View Recipients
                        </button>
                    </td>
                `;
                
                tbody.appendChild(row);
            });
            
            document.getElementById('searchResults').style.display = results.length ? 'block' : 'none';
        }

        function selectRecipient(recipientId) {
            // Get recipient details from the row
            const row = event.target.closest('tr');
            const recipientName = row.cells[0].textContent.trim();
            const bloodGroup = row.cells[1].textContent.trim();
            const requiredOrgan = row.cells[2].textContent.trim();
            const recipientType = row.getAttribute('data-recipient-type');
            
            // Get hospital info based on recipient type
            let hospitalId, hospitalName;
            if (recipientType === 'Own') {
                hospitalId = <?php echo $hospital_id; ?>;
                hospitalName = <?php echo json_encode($hospital_name); ?>;
            } else {
                // For shared recipients, get the original hospital's info
                hospitalName = row.getAttribute('data-hospital-name');
                hospitalId = row.getAttribute('data-hospital-id');
            }

            // Create recipient info object
            const recipientInfo = {
                id: recipientId,
                name: recipientName,
                bloodGroup: bloodGroup,
                requiredOrgan: requiredOrgan,
                hospitalId: hospitalId,
                hospitalName: hospitalName
            };
            
            // Store in session storage
            sessionStorage.setItem('selectedRecipient', JSON.stringify(recipientInfo));
            
            // Redirect to make matches page
            window.location.href = 'make_matches.php?recipient=' + encodeURIComponent(recipientId);
        }

        // Close search results when clicking outside
        document.addEventListener('click', (e) => {
            if (!searchInput.contains(e.target) && !searchResults.contains(e.target)) {
                searchResults.style.display = 'none';
            }
        });
    </script>
</body>
</html>
