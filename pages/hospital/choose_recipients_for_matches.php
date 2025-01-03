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

        .results-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 1rem;
            background: white;
            border-radius: 10px;
            overflow: hidden;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.1);
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
        }

        .contact-info span {
            display: flex;
            align-items: center;
            gap: 0.5rem;
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
            font-size: 0.9rem;
            color: #666;
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

        #searchResults {
            display: none;
            margin-top: 1rem;
        }
    </style>
</head>
<body>
    <div class="dashboard-container">
        <?php include '../../includes/hospital_sidebar.php'; ?>
        
        <main class="main-content">
            <div class="search-section">
                <h2>Search Recipients</h2>
                <div class="search-container">
                    <i class="fas fa-search search-icon"></i>
                    <input type="text" id="searchInput" class="search-input" placeholder="Search by blood group...">
                </div>

                <div class="filter-buttons">
                    <button class="filter-btn active" data-filter="blood_group">Blood Group</button>
                    <button class="filter-btn" data-filter="organs">Organs</button>
                </div>

                <div id="searchResults">
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
                            <?php if (empty($recipients)): ?>
                                <tr>
                                    <td colspan="4" class="text-center">No recipients found matching your search criteria</td>
                                </tr>
                            <?php else: ?>
                                <?php foreach ($recipients as $recipient): ?>
                                    <tr>
                                        <td><?php echo htmlspecialchars($recipient['name']); ?></td>
                                        <td>
                                            <div class="contact-info">
                                                <span><i class="fas fa-phone"></i> <?php echo htmlspecialchars($recipient['phone']); ?></span>
                                                <span><i class="fas fa-map-marker-alt"></i> <?php echo htmlspecialchars($recipient['address']); ?></span>
                                            </div>
                                        </td>
                                        <td>
                                            <div class="recipient-info">
                                                <span class="recipient-count">Recipients: <?php echo htmlspecialchars($recipient['recipient_count']); ?></span>
                                                <span class="recipient-details">Blood Groups: <?php echo htmlspecialchars($recipient['blood_groups']); ?></span>
                                                <span class="recipient-details">Required Organs: <?php echo htmlspecialchars($recipient['organ_types']); ?></span>
                                            </div>
                                        </td>
                                        <td>
                                            <button class="action-btn" onclick="viewRecipients(<?php echo $recipient['recipient_id']; ?>)">
                                                View Recipients
                                            </button>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
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
                        <button class="action-btn" onclick="viewRecipients(${hospital.hospital_id})">
                            View Recipients
                        </button>
                    </td>
                `;
                
                tbody.appendChild(row);
            });
        }

        function viewRecipients(hospitalId) {
            // TODO: Implement view recipients functionality
            console.log('Viewing recipients for hospital:', hospitalId);
        }
    </script>
</body>
</html>
