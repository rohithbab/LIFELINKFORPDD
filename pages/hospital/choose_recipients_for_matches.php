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

        .search-results {
            display: none;
            background: white;
            border-radius: 10px;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.1);
            margin: 2rem;
            padding: 1rem;
        }

        .results-table {
            width: 100%;
            border-collapse: collapse;
        }

        .results-table th,
        .results-table td {
            padding: 1rem;
            text-align: left;
            border-bottom: 1px solid #eee;
        }

        .results-table th {
            background: linear-gradient(135deg, var(--primary-blue), var(--primary-green));
            color: white;
            font-weight: 500;
        }

        .contact-info {
            display: flex;
            flex-direction: column;
            gap: 0.5rem;
            color: #666;
        }

        .recipient-info {
            display: flex;
            flex-direction: column;
            gap: 0.5rem;
        }

        .recipient-count {
            font-weight: bold;
            color: var(--primary-blue);
        }

        .recipient-details {
            color: #666;
            font-size: 0.9rem;
        }

        .view-btn {
            display: inline-block;
            padding: 0.5rem 1rem;
            border: none;
            border-radius: 5px;
            background: linear-gradient(135deg, var(--primary-blue), var(--primary-green));
            color: white;
            text-decoration: none;
            cursor: pointer;
            transition: all 0.3s ease;
        }

        .view-btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
        }

        .empty-state {
            text-align: center;
            padding: 3rem;
        }

        .empty-state i {
            font-size: 3rem;
            color: #ccc;
            margin-bottom: 1rem;
        }

        .empty-state h3 {
            color: #666;
            margin-bottom: 0.5rem;
        }

        .empty-state p {
            color: #999;
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
                    <button class="filter-btn active" data-filter="blood_group">
                        <i class="fas fa-tint"></i> Blood Group
                    </button>
                    <button class="filter-btn" data-filter="organs">
                        <i class="fas fa-heart"></i> Organs
                    </button>
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

            <div id="searchResults" class="search-results">
                <table class="results-table">
                    <thead>
                        <tr>
                            <th>Hospital Name</th>
                            <th>Contact Details</th>
                            <th>Available Recipients</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody id="resultsBody">
                        <!-- Results will be populated here -->
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
        let currentFilter = 'blood_group';

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
                
                // Hospital Name
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
                        <a href="choose_other_recipients.php?hospital_id=${hospital.hospital_id}" class="view-btn">
                            View Recipients
                        </a>
                    </td>
                `;
                
                tbody.appendChild(row);
            });
            
            document.getElementById('searchResults').style.display = results.length ? 'block' : 'none';
        }

        function selectRecipient(recipientId) {
            // Redirect back to make_matches.php with the selected recipient
            window.location.href = `make_matches.php?recipient=${recipientId}`;
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
