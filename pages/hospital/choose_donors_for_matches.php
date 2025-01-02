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

// Fetch hospital's donors
try {
    $stmt = $conn->prepare("
        SELECT d.*, ha.status as approval_status, ha.organ_type
        FROM donor d
        JOIN hospital_donor_approvals ha ON d.donor_id = ha.donor_id
        WHERE ha.hospital_id = ? AND ha.status = 'Approved'
        ORDER BY d.name ASC
    ");
    $stmt->execute([$hospital_id]);
    $donors = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch(PDOException $e) {
    error_log("Error fetching donors: " . $e->getMessage());
    $donors = [];
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Choose Donors - LifeLink</title>
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

        .search-results {
            background: white;
            border-radius: 8px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
            display: none;
            position: absolute;
            width: 100%;
            z-index: 1000;
            max-height: 300px;
            overflow-y: auto;
        }

        .search-result-item {
            padding: 1rem;
            border-bottom: 1px solid #eee;
            display: flex;
            justify-content: space-between;
            align-items: center;
            cursor: pointer;
        }

        .search-result-item:hover {
            background: #f8f9fa;
        }

        .info-icon {
            color: var(--primary-blue);
            cursor: pointer;
        }

        .donors-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 1rem;
            background: white;
        }

        .donors-table th,
        .donors-table td {
            padding: 1rem;
            text-align: left;
            border-bottom: 1px solid #eee;
        }

        .donors-table th {
            background: linear-gradient(135deg, var(--primary-blue), var(--primary-green));
            color: white;
            font-weight: 500;
        }

        .donors-table tr:hover {
            background-color: #f8f9fa;
        }

        .select-btn {
            padding: 0.5rem 1rem;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            background: linear-gradient(135deg, var(--primary-blue), var(--primary-green));
            color: white;
            transition: all 0.3s ease;
        }

        .select-btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
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
                    <li>
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
                        <a href="make_matches.php" class="active">
                            <i class="fas fa-link"></i>
                            <span>Make Matches</span>
                        </a>
                    </li>
                    <li>
                        <a href="check_requests.php">
                            <i class="fas fa-bell"></i>
                            <span>Check Requests</span>
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
            <div class="dashboard-header">
                <div class="header-left">
                    <h1>Choose Donors</h1>
                </div>
            </div>

            <div class="search-section">
                <div class="search-container">
                    <i class="fas fa-search search-icon"></i>
                    <input type="text" class="search-input" placeholder="Search for donors..." id="searchInput">
                </div>

                <div class="filter-buttons">
                    <button class="filter-btn active" data-filter="name">Name</button>
                    <button class="filter-btn" data-filter="address">Address</button>
                    <button class="filter-btn" data-filter="phone">Phone</button>
                    <button class="filter-btn" data-filter="organs">Organs</button>
                    <button class="filter-btn" data-filter="blood_group">Blood Group</button>
                </div>

                <?php if (empty($donors)): ?>
                    <div class="empty-state">
                        <i class="fas fa-user-plus fa-3x mb-3"></i>
                        <h2>No Donors Found</h2>
                        <p>There are no approved donors in your hospital at the moment.</p>
                    </div>
                <?php else: ?>
                    <table class="donors-table">
                        <thead>
                            <tr>
                                <th>Donor Name</th>
                                <th>Blood Group</th>
                                <th>Organ Type</th>
                                <th>Phone</th>
                                <th>Address</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($donors as $donor): ?>
                                <tr>
                                    <td><?php echo htmlspecialchars($donor['name']); ?></td>
                                    <td><?php echo htmlspecialchars($donor['blood_group']); ?></td>
                                    <td><?php echo htmlspecialchars($donor['organ_type']); ?></td>
                                    <td><?php echo htmlspecialchars($donor['phone']); ?></td>
                                    <td><?php echo htmlspecialchars($donor['address']); ?></td>
                                    <td>
                                        <button class="select-btn" onclick="selectDonor(<?php echo $donor['donor_id']; ?>)">
                                            Select
                                        </button>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                <?php endif; ?>
            </div>
            <div id="searchResults" class="mt-4">
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th>Hospital Name</th>
                                <th>Contact Details</th>
                                <th>Available Donors</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody id="resultsBody">
                            <!-- Results will be populated here -->
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
        let currentFilter = 'name';

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
                fetch('../../ajax/search_donors.php', {
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
                const tr = document.createElement('tr');
                tr.innerHTML = `
                    <td>
                        <strong>${hospital.hospital_name}</strong>
                    </td>
                    <td>
                        <div><i class="fas fa-phone"></i> ${hospital.hospital_phone}</div>
                        <div><i class="fas fa-map-marker-alt"></i> ${hospital.hospital_address}</div>
                    </td>
                    <td>
                        <div><strong>Donors:</strong> ${hospital.donor_count}</div>
                        <div><strong>Blood Groups:</strong> ${hospital.blood_groups.join(', ')}</div>
                        <div><strong>Organs:</strong> ${hospital.organ_types.join(', ')}</div>
                    </td>
                    <td>
                        <button class="btn btn-info btn-sm view-donors" 
                                onclick="viewHospitalDonors(${hospital.hospital_id})">
                            <i class="fas fa-eye"></i> View Donors
                        </button>
                    </td>
                `;
                tbody.appendChild(tr);
            });
        }

        function selectDonor(donorId) {
            // Redirect back to make_matches.php with the selected donor
            window.location.href = `make_matches.php?donor=${donorId}`;
        }

        function viewHospitalDonors(hospitalId) {
            // Redirect to choose_other_donors.php with the selected hospital
            window.location.href = `choose_other_donors.php?hospital_id=${hospitalId}`;
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
