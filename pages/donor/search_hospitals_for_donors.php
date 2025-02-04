<?php
session_start();
require_once '../../config/db_connect.php';

// Check if user is logged in as donor
if (!isset($_SESSION['is_donor']) || !$_SESSION['is_donor']) {
    header("Location: ../donor_login.php");
    exit();
}

// Get donor info from session
$donor_id = $_SESSION['donor_id'];

// Fetch donor details
$stmt = $conn->prepare("SELECT name, blood_group FROM donor WHERE donor_id = ?");
$stmt->execute([$donor_id]);
$donor = $stmt->fetch(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Search Hospitals - LifeLink</title>
    <link rel="stylesheet" href="../../assets/css/donor-dashboard.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
    <style>
        .search-container {
            background: white;
            border-radius: 15px;
            padding: 30px;
            box-shadow: 0 4px 20px rgba(0,0,0,0.1);
            margin-bottom: 30px;
        }

        .search-header {
            margin-bottom: 20px;
        }

        .search-header h2 {
            color: var(--primary-color);
            margin-bottom: 10px;
        }

        .search-box {
            position: relative;
            margin-bottom: 20px;
        }

        .search-input {
            width: 100%;
            padding: 15px 20px 15px 50px;
            border: 2px solid rgba(0,0,0,0.1);
            border-radius: 10px;
            font-size: 1em;
            transition: all 0.3s ease;
        }

        .search-input:focus {
            outline: none;
            border-color: var(--primary-color);
            box-shadow: 0 0 0 3px rgba(var(--primary-color-rgb), 0.1);
        }

        .search-icon {
            position: absolute;
            left: 20px;
            top: 50%;
            transform: translateY(-50%);
            color: var(--text-secondary);
        }

        .filter-buttons {
            display: flex;
            gap: 10px;
            margin-bottom: 20px;
        }

        .filter-btn {
            padding: 8px 15px;
            border: none;
            border-radius: 20px;
            background: rgba(var(--primary-color-rgb), 0.1);
            color: var(--primary-color);
            cursor: pointer;
            transition: all 0.3s ease;
        }

        .filter-btn.active {
            background: var(--primary-color);
            color: white;
        }

        .hospitals-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
            gap: 20px;
            margin-top: 20px;
        }

        .hospital-card {
            background: white;
            border-radius: 10px;
            padding: 20px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.05);
            transition: all 0.3s ease;
        }

        .hospital-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 5px 20px rgba(0,0,0,0.1);
        }

        .hospital-header {
            display: flex;
            align-items: center;
            gap: 15px;
            margin-bottom: 15px;
        }

        .hospital-icon {
            width: 50px;
            height: 50px;
            background: linear-gradient(135deg, var(--primary-color), var(--secondary-color));
            border-radius: 10px;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-size: 24px;
        }

        .hospital-name {
            font-size: 1.2em;
            color: var(--text-primary);
            margin: 0;
        }

        .hospital-info {
            margin-bottom: 15px;
        }

        .info-item {
            display: flex;
            align-items: center;
            gap: 10px;
            margin-bottom: 8px;
            color: var(--text-secondary);
        }

        .request-btn {
            width: 100%;
            padding: 10px;
            border: none;
            border-radius: 8px;
            background: linear-gradient(135deg, var(--primary-color), var(--secondary-color));
            color: white;
            cursor: pointer;
            transition: all 0.3s ease;
        }

        .request-btn:hover {
            opacity: 0.9;
            transform: translateY(-2px);
        }

        /* Custom Modal Styles */
        .custom-modal {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0, 0, 0, 0.5);
            z-index: 1000;
            justify-content: center;
            align-items: center;
        }

        .modal-content {
            background: white;
            padding: 30px;
            border-radius: 15px;
            max-width: 500px;
            width: 90%;
            text-align: center;
            box-shadow: 0 5px 25px rgba(0, 0, 0, 0.2);
            transform: translateY(-20px);
            opacity: 0;
            transition: all 0.3s ease;
        }

        .modal-content.active {
            transform: translateY(0);
            opacity: 1;
        }

        .modal-icon {
            font-size: 48px;
            margin-bottom: 20px;
            background: linear-gradient(135deg, #4CAF50, #2196F3);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
        }

        .modal-title {
            font-size: 24px;
            margin-bottom: 15px;
            color: #333;
        }

        .modal-message {
            color: #666;
            margin-bottom: 25px;
            line-height: 1.5;
        }

        .modal-buttons {
            display: flex;
            justify-content: center;
            gap: 15px;
        }

        .modal-btn {
            padding: 10px 25px;
            border: none;
            border-radius: 25px;
            cursor: pointer;
            font-weight: 600;
            transition: all 0.3s ease;
        }

        .confirm-btn {
            background: linear-gradient(135deg, #4CAF50, #2196F3);
            color: white;
        }

        .cancel-btn {
            background: #f1f1f1;
            color: #666;
        }

        .modal-btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.1);
        }

        /* Success Card Styles */
        .success-card {
            display: none;
            background: linear-gradient(135deg, #4CAF50, #2196F3);
            color: white;
            padding: 20px;
            border-radius: 15px;
            margin-bottom: 20px;
            box-shadow: 0 5px 20px rgba(0, 0, 0, 0.1);
            animation: slideDown 0.5s ease forwards;
        }

        @keyframes slideDown {
            from {
                transform: translateY(-20px);
                opacity: 0;
            }
            to {
                transform: translateY(0);
                opacity: 1;
            }
        }

        .success-card i {
            font-size: 32px;
            margin-bottom: 15px;
        }

        .success-card h3 {
            margin-bottom: 10px;
            font-size: 20px;
        }

        .success-card p {
            opacity: 0.9;
            line-height: 1.5;
        }
    </style>
</head>
<body>
    <!-- Custom Modal -->
    <div class="custom-modal" id="requestModal">
        <div class="modal-content">
            <div class="modal-icon">
                <i class="fas fa-heart"></i>
            </div>
            <h2 class="modal-title">Confirm Donation Request</h2>
            <p class="modal-message">
                You are about to register as an organ donor with <strong id="hospitalName"></strong>. Upon submission:
                <br><br>
                • The hospital will review your medical information<br>
                • You'll receive an email notification of their decision<br>
                • If approved, you'll be registered as a potential donor<br>
                • If not approved, you'll receive detailed feedback with the reason
            </p>
            <div class="modal-buttons">
                <button class="modal-btn cancel-btn" onclick="closeModal()">Cancel</button>
                <button class="modal-btn confirm-btn" onclick="submitRequest()">Confirm Request</button>
            </div>
        </div>
    </div>

    <div class="dashboard-container">
        <aside class="sidebar">
            <div class="sidebar-header">
                <i class="fas fa-heartbeat"></i>
                <span>LifeLink</span>
            </div>
            <nav class="sidebar-nav">
                <ul>
                    <li>
                        <a href="donor_dashboard.php">
                            <i class="fas fa-home"></i>
                            <span>Dashboard</span>
                        </a>
                    </li>
                    <li>
                        <a href="donor_personal_details.php">
                            <i class="fas fa-user"></i>
                            <span>Profile</span>
                        </a>
                    </li>
                    <li>
                        <a href="search_hospitals_for_donors.php" class="active">
                            <i class="fas fa-search"></i>
                            <span>Search Hospitals</span>
                        </a>
                    </li>
                    <li>
                        <a href="my_requests_for_donors.php">
                            <i class="fas fa-list"></i>
                            <span>My Requests</span>
                        </a>
                    </li>
                    <li>
                    <a href="donors_notifications.php" class="<?php echo basename($_SERVER['PHP_SELF']) == 'donors_notifications.php' ? 'active' : ''; ?>">
                     
                            <i class="fas fa-bell"></i>
                            <span>Notifications</span>
                            <span class="notification-badge">2</span>
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
        
        <main class="main-content">
            <div class="main-section">
                <div class="dashboard-header">
                    <div class="header-left">
                        <h1>Search Hospitals</h1>
                    </div>
                </div>

                <div class="search-container">
                    <div class="search-header">
                        <h2>Find Hospitals</h2>
                        <p>Search for hospitals to make your organ donation request</p>
                    </div>

                    <div class="search-box">
                        <i class="fas fa-search search-icon"></i>
                        <input type="text" id="searchInput" class="search-input" placeholder="Search hospitals...">
                    </div>

                    <div class="filter-buttons">
                        <button class="filter-btn active" data-filter="name">
                            <i class="fas fa-hospital"></i> Name
                        </button>
                        <button class="filter-btn" data-filter="address">
                            <i class="fas fa-map-marker-alt"></i> Address
                        </button>
                        <button class="filter-btn" data-filter="phone">
                            <i class="fas fa-phone"></i> Phone
                        </button>
                    </div>

                    <div class="hospitals-grid" id="hospitalsGrid">
                        <!-- Hospitals will be loaded here dynamically -->
                    </div>
                </div>
            </div>
            <!-- Success Card -->
            <div class="success-card" id="successCard">
                <i class="fas fa-check-circle"></i>
                <h3>Request Submitted Successfully!</h3>
                <p>Your organ donation request has been sent to the hospital. You will receive an email notification once they review your application. Thank you for your noble decision to become a donor.</p>
            </div>
        </main>
    </div>

    <script>
        let currentFilter = 'name';
        let searchTimeout = null;

        // Filter button click handler
        document.querySelectorAll('.filter-btn').forEach(btn => {
            btn.addEventListener('click', () => {
                document.querySelectorAll('.filter-btn').forEach(b => b.classList.remove('active'));
                btn.classList.add('active');
                currentFilter = btn.dataset.filter;
                searchHospitals();
            });
        });

        // Search input handler with debounce
        document.getElementById('searchInput').addEventListener('input', (e) => {
            clearTimeout(searchTimeout);
            searchTimeout = setTimeout(() => {
                searchHospitals();
            }, 300);
        });

        function searchHospitals() {
            const searchTerm = document.getElementById('searchInput').value;
            const grid = document.getElementById('hospitalsGrid');
            
            // Show loading state
            grid.innerHTML = '<div class="loading">Searching hospitals...</div>';
            
            fetch(`../../ajax/search_hospitals.php?term=${encodeURIComponent(searchTerm)}&filter=${currentFilter}`)
                .then(response => response.json())
                .then(response => {
                    grid.innerHTML = '';
                    
                    if (!response.success) {
                        grid.innerHTML = `<div class="error">Error: ${response.error}</div>`;
                        return;
                    }
                    
                    const hospitals = response.data;
                    
                    if (hospitals.length === 0) {
                        grid.innerHTML = `
                            <div class="no-results">
                                <p>No hospitals found matching your search criteria.</p>
                                <p>Try searching with different terms or filters.</p>
                            </div>`;
                        return;
                    }

                    hospitals.forEach(hospital => {
                        const card = createHospitalCard(hospital);
                        grid.appendChild(card);
                    });
                })
                .catch(error => {
                    grid.innerHTML = '<div class="error">Error connecting to server. Please try again.</div>';
                });
        }

        function createHospitalCard(hospital) {
            const div = document.createElement('div');
            div.className = 'hospital-card';
            
            // Escape HTML to prevent XSS
            const escapeHtml = (unsafe) => {
                return unsafe
                    .replace(/&/g, "&amp;")
                    .replace(/</g, "&lt;")
                    .replace(/>/g, "&gt;")
                    .replace(/"/g, "&quot;")
                    .replace(/'/g, "&#039;");
            };
            
            div.innerHTML = `
                <div class="hospital-header">
                    <div class="hospital-icon">
                        <i class="fas fa-hospital"></i>
                    </div>
                    <h3 class="hospital-name">${escapeHtml(hospital.name)}</h3>
                </div>
                <div class="hospital-info">
                    <div class="info-item">
                        <i class="fas fa-envelope"></i>
                        <span>${escapeHtml(hospital.email)}</span>
                    </div>
                    <div class="info-item">
                        <i class="fas fa-map-marker-alt"></i>
                        <span>${escapeHtml(hospital.address)}</span>
                    </div>
                    <div class="info-item">
                        <i class="fas fa-phone"></i>
                        <span>${escapeHtml(hospital.phone)}</span>
                    </div>
                    <div class="info-item">
                        <i class="fas fa-map"></i>
                        <span>${escapeHtml(hospital.region)}</span>
                    </div>
                </div>
                <button class="request-btn" onclick="makeRequest(${hospital.hospital_id}, '${escapeHtml(hospital.name)}')">
                    Make Request
                </button>
            `;
            return div;
        }

        let selectedHospitalId = null;
        let selectedHospitalName = null;

        function showRequestModal(hospitalId, name) {
            selectedHospitalId = hospitalId;
            selectedHospitalName = name;
            document.getElementById('hospitalName').textContent = name;
            document.getElementById('requestModal').style.display = 'flex';
            setTimeout(() => {
                document.querySelector('.modal-content').classList.add('active');
            }, 10);
        }

        function closeModal() {
            document.querySelector('.modal-content').classList.remove('active');
            setTimeout(() => {
                document.getElementById('requestModal').style.display = 'none';
            }, 300);
        }

        function submitRequest() {
            if (!selectedHospitalId) return;
            
            // Here you would add the AJAX call to submit the request to your backend
            // For now, we'll just show the success card
            closeModal();
            document.getElementById('successCard').style.display = 'block';
            
            // Hide the success card after 5 seconds
            setTimeout(() => {
                document.getElementById('successCard').style.display = 'none';
            }, 5000);
        }

        // Update the existing makeRequest function
        function makeRequest(hospitalId, name) {
            showRequestModal(hospitalId, name);
        }

        // Initial search on page load
        searchHospitals();
    </script>
</body>
</html>
