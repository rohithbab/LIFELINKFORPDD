<?php
session_start();
require_once '../../backend/php/connection.php';
require_once '../../backend/php/queries.php';

// Check if admin is logged in
if (!isset($_SESSION['admin_id'])) {
    header('Location: ../admin_login.php');
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Organ Matches - Admin Dashboard</title>
    
    <!-- CSS Files -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link rel="stylesheet" href="../../assets/css/styles.css">
    <link rel="stylesheet" href="../../assets/css/admin-dashboard.css">
    <link rel="stylesheet" href="../../assets/css/notification-bell.css">
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
                    <a href="manage_recipients.php" class="nav-link">
                        <i class="fas fa-user-plus"></i>
                        Manage Recipients
                    </a>
                </li>
                <li class="nav-item">
                    <a href="organ_match_info_for_admin.php" class="nav-link active">
                        <i class="fas fa-handshake-angle"></i>
                        Organ Matches
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
        
        <main>
            <div class="dashboard-header">
                <h1>Organ Matches</h1>
            </div>
            
            <?php
            include '../../backend/php/organ_matches.php';

            // Get page parameters
            $page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
            $search = isset($_GET['search']) ? $_GET['search'] : '';
            $sortBy = isset($_GET['sort']) ? $_GET['sort'] : 'match_date';
            $sortOrder = isset($_GET['order']) ? $_GET['order'] : 'DESC';

            // Get matches with pagination and search
            $result = getAllOrganMatches($conn, $page, 10, $search, $sortBy, $sortOrder);
            $matches = $result['matches'];
            $totalPages = $result['pages'];
            ?>

            <div class="container">
                <h2>Organ Match History</h2>
                
                <!-- Search Bar -->
                <div class="search-bar">
                    <input type="text" id="searchInput" placeholder="Search..." value="<?php echo htmlspecialchars($search); ?>">
                    <button onclick="search()">Search</button>
                </div>

                <!-- Matches Table -->
                <div class="table-container">
                    <table class="matches-table">
                        <thead>
                            <tr>
                                <th onclick="sort('match_id')">Match ID 
                                    <?php if($sortBy == 'match_id'): ?>
                                        <i class="fas fa-sort-<?php echo $sortOrder == 'ASC' ? 'up' : 'down'; ?>"></i>
                                    <?php endif; ?>
                                </th>
                                <th onclick="sort('match_made_by_hospital_name')">Hospital (Match Made By)
                                    <?php if($sortBy == 'match_made_by_hospital_name'): ?>
                                        <i class="fas fa-sort-<?php echo $sortOrder == 'ASC' ? 'up' : 'down'; ?>"></i>
                                    <?php endif; ?>
                                </th>
                                <th onclick="sort('donor_name')">Donor Name
                                    <?php if($sortBy == 'donor_name'): ?>
                                        <i class="fas fa-sort-<?php echo $sortOrder == 'ASC' ? 'up' : 'down'; ?>"></i>
                                    <?php endif; ?>
                                </th>
                                <th>Donor Email</th>
                                <th onclick="sort('donor_hospital_name')">Donor Hospital
                                    <?php if($sortBy == 'donor_hospital_name'): ?>
                                        <i class="fas fa-sort-<?php echo $sortOrder == 'ASC' ? 'up' : 'down'; ?>"></i>
                                    <?php endif; ?>
                                </th>
                                <th onclick="sort('recipient_name')">Recipient Name
                                    <?php if($sortBy == 'recipient_name'): ?>
                                        <i class="fas fa-sort-<?php echo $sortOrder == 'ASC' ? 'up' : 'down'; ?>"></i>
                                    <?php endif; ?>
                                </th>
                                <th>Recipient Email</th>
                                <th onclick="sort('recipient_hospital_name')">Recipient Hospital
                                    <?php if($sortBy == 'recipient_hospital_name'): ?>
                                        <i class="fas fa-sort-<?php echo $sortOrder == 'ASC' ? 'up' : 'down'; ?>"></i>
                                    <?php endif; ?>
                                </th>
                                <th onclick="sort('organ_type')">Organ Type
                                    <?php if($sortBy == 'organ_type'): ?>
                                        <i class="fas fa-sort-<?php echo $sortOrder == 'ASC' ? 'up' : 'down'; ?>"></i>
                                    <?php endif; ?>
                                </th>
                                <th onclick="sort('blood_group')">Blood Group
                                    <?php if($sortBy == 'blood_group'): ?>
                                        <i class="fas fa-sort-<?php echo $sortOrder == 'ASC' ? 'up' : 'down'; ?>"></i>
                                    <?php endif; ?>
                                </th>
                                <th onclick="sort('match_date')">Match Date
                                    <?php if($sortBy == 'match_date'): ?>
                                        <i class="fas fa-sort-<?php echo $sortOrder == 'ASC' ? 'up' : 'down'; ?>"></i>
                                    <?php endif; ?>
                                </th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($matches as $match): ?>
                                <tr>
                                    <td><?php echo htmlspecialchars($match['match_id']); ?></td>
                                    <td><?php echo htmlspecialchars($match['match_made_by_hospital_name']); ?></td>
                                    <td><?php echo htmlspecialchars($match['donor_name']); ?></td>
                                    <td><?php echo htmlspecialchars($match['donor_email']); ?></td>
                                    <td><?php echo htmlspecialchars($match['donor_hospital_name']); ?></td>
                                    <td><?php echo htmlspecialchars($match['recipient_name']); ?></td>
                                    <td><?php echo htmlspecialchars($match['recipient_email']); ?></td>
                                    <td><?php echo htmlspecialchars($match['recipient_hospital_name']); ?></td>
                                    <td><?php echo htmlspecialchars($match['organ_type']); ?></td>
                                    <td><?php echo htmlspecialchars($match['blood_group']); ?></td>
                                    <td><?php echo date('Y-m-d', strtotime($match['match_date'])); ?></td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>

                <!-- Pagination -->
                <div class="pagination">
                    <?php if($page > 1): ?>
                        <a href="?page=1&search=<?php echo urlencode($search); ?>&sort=<?php echo $sortBy; ?>&order=<?php echo $sortOrder; ?>">First</a>
                        <a href="?page=<?php echo $page-1; ?>&search=<?php echo urlencode($search); ?>&sort=<?php echo $sortBy; ?>&order=<?php echo $sortOrder; ?>">Previous</a>
                    <?php endif; ?>

                    <?php
                    $start = max(1, $page - 2);
                    $end = min($totalPages, $page + 2);
                    
                    for($i = $start; $i <= $end; $i++): ?>
                        <a href="?page=<?php echo $i; ?>&search=<?php echo urlencode($search); ?>&sort=<?php echo $sortBy; ?>&order=<?php echo $sortOrder; ?>" 
                           class="<?php echo $i == $page ? 'active' : ''; ?>">
                            <?php echo $i; ?>
                        </a>
                    <?php endfor; ?>

                    <?php if($page < $totalPages): ?>
                        <a href="?page=<?php echo $page+1; ?>&search=<?php echo urlencode($search); ?>&sort=<?php echo $sortBy; ?>&order=<?php echo $sortOrder; ?>">Next</a>
                        <a href="?page=<?php echo $totalPages; ?>&search=<?php echo urlencode($search); ?>&sort=<?php echo $sortBy; ?>&order=<?php echo $sortOrder; ?>">Last</a>
                    <?php endif; ?>
                </div>
            </div>

            <style>
                .sidebar {
                    z-index: 1000;
                    position: fixed;
                }

                main {
                    margin-left: 250px;
                    padding: 20px;
                    min-height: 100vh;
                    background: linear-gradient(135deg, rgba(76, 175, 80, 0.1), rgba(33, 150, 243, 0.1));
                    position: relative;
                    z-index: 1;
                }

                .container {
                    padding: 20px;
                    width: 100%;
                    max-width: 100%;
                    box-sizing: border-box;
                    position: relative;
                    z-index: 1;
                }

                /* Fixed header section */
                .dashboard-header {
                    position: sticky;
                    left: 0;
                    background: linear-gradient(135deg, rgba(76, 175, 80, 0.1), rgba(33, 150, 243, 0.1));
                    padding-bottom: 20px;
                }

                h2 {
                    color: #2196F3;
                    margin-bottom: 30px;
                    font-size: 24px;
                    border-bottom: 2px solid #4CAF50;
                    padding-bottom: 10px;
                    width: fit-content;
                }

                /* Fixed search section */
                .search-bar {
                    position: sticky;
                    left: 0;
                    margin-bottom: 20px;
                    display: flex;
                    gap: 10px;
                    width: 100%;
                    max-width: 600px;
                    background: linear-gradient(135deg, rgba(76, 175, 80, 0.1), rgba(33, 150, 243, 0.1));
                    padding-bottom: 20px;
                }

                .search-bar input {
                    padding: 12px;
                    border: 1px solid #4CAF50;
                    border-radius: 6px;
                    width: 100%;
                    font-size: 14px;
                    transition: all 0.3s ease;
                }

                .search-bar button {
                    padding: 12px 20px;
                    background: linear-gradient(135deg, #4CAF50, #2196F3);
                    color: white;
                    border: none;
                    border-radius: 6px;
                    cursor: pointer;
                    font-weight: bold;
                    transition: all 0.3s ease;
                    white-space: nowrap;
                }

                /* Scrollable table container */
                .table-container {
                    width: 100%;
                    overflow-x: auto;
                    margin-top: 20px;
                    border-radius: 8px;
                    box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
                    position: relative;
                    z-index: 1;
                }

                .matches-table {
                    width: 100%;
                    border-collapse: separate;
                    border-spacing: 0;
                    background: white;
                    margin-bottom: 0;
                }

                .matches-table th {
                    background: linear-gradient(135deg, #4CAF50, #2196F3);
                    color: white;
                    font-weight: 600;
                    cursor: pointer;
                    transition: all 0.3s ease;
                    white-space: nowrap;
                    position: sticky;
                    top: 0;
                    z-index: 2;
                    padding: 15px;
                }

                .matches-table td {
                    padding: 15px;
                    text-align: left;
                    border-bottom: 1px solid rgba(0, 0, 0, 0.1);
                    white-space: nowrap;
                }

                /* Fixed pagination section */
                .pagination {
                    position: sticky;
                    left: 0;
                    margin-top: 30px;
                    margin-bottom: 30px;
                    display: flex;
                    justify-content: center;
                    gap: 10px;
                    background: linear-gradient(135deg, rgba(76, 175, 80, 0.1), rgba(33, 150, 243, 0.1));
                    padding: 20px 0;
                }

                .pagination a {
                    padding: 10px 15px;
                    border: 1px solid #4CAF50;
                    border-radius: 6px;
                    text-decoration: none;
                    color: #4CAF50;
                    transition: all 0.3s ease;
                }

                .pagination a.active {
                    background: linear-gradient(135deg, #4CAF50, #2196F3);
                    color: white;
                    border: none;
                }

                /* Custom scrollbar for table */
                .table-container::-webkit-scrollbar {
                    height: 8px;
                }

                .table-container::-webkit-scrollbar-track {
                    background: #f1f1f1;
                    border-radius: 4px;
                }

                .table-container::-webkit-scrollbar-thumb {
                    background: linear-gradient(135deg, #4CAF50, #2196F3);
                    border-radius: 4px;
                }

                @media screen and (max-width: 1024px) {
                    main {
                        margin-left: 0;
                        padding: 15px;
                    }
                }
            </style>
        </main>
    </div>

    <script>
        function search() {
            const searchTerm = document.getElementById('searchInput').value;
            window.location.href = `?page=1&search=${encodeURIComponent(searchTerm)}&sort=<?php echo $sortBy; ?>&order=<?php echo $sortOrder; ?>`;
        }

        function sort(column) {
            const currentSort = '<?php echo $sortBy; ?>';
            const currentOrder = '<?php echo $sortOrder; ?>';
            const newOrder = (column === currentSort && currentOrder === 'ASC') ? 'DESC' : 'ASC';
            
            window.location.href = `?page=<?php echo $page; ?>&search=<?php echo urlencode($search); ?>&sort=${column}&order=${newOrder}`;
        }

        // Enable search on Enter key
        document.getElementById('searchInput').addEventListener('keypress', function(e) {
            if (e.key === 'Enter') {
                search();
            }
        });
    </script>

    <!-- JavaScript Dependencies -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="../../assets/js/notifications.js"></script>
</body>
</html>
