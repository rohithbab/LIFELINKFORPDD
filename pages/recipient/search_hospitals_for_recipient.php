<?php
session_start();
require_once '../../config/db_connect.php';

// Check if user is logged in as recipient
if (!isset($_SESSION['is_recipient']) || !$_SESSION['is_recipient']) {
    header("Location: ../recipient_login.php");
    exit();
}

// Get recipient info from session
$recipient_id = $_SESSION['recipient_id'];

// Fetch recipient details
$stmt = $conn->prepare("SELECT full_name, blood_type, organ_required FROM recipient_registration WHERE id = ?");
$stmt->execute([$recipient_id]);
$recipient = $stmt->fetch(PDO::FETCH_ASSOC);

// Fetch all hospitals by default
$defaultQuery = "SELECT DISTINCT h.*, 
              (SELECT COUNT(*) FROM hospital_donor_approvals hda 
               JOIN donor d ON hda.donor_id = d.donor_id 
               WHERE hda.hospital_id = h.hospital_id 
               AND hda.status = 'approved' 
               AND d.organs_to_donate = ?) as organ_count
              FROM hospitals h
              LEFT JOIN hospital_donor_approvals hda ON h.hospital_id = hda.hospital_id
              LEFT JOIN donor d ON hda.donor_id = d.donor_id
              GROUP BY h.hospital_id 
              ORDER BY organ_count DESC";

$stmt = $conn->prepare($defaultQuery);
$stmt->execute([$recipient['organ_required']]);
$allHospitals = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Handle AJAX search request
if (isset($_GET['search']) && isset($_GET['filter'])) {
    $search = '%' . $_GET['search'] . '%';
    $filter = $_GET['filter'];
    
    // Base query to get hospitals with approved donors for the required organ
    $baseQuery = "SELECT DISTINCT h.*, 
                  (SELECT COUNT(*) FROM hospital_donor_approvals hda 
                   JOIN donor d ON hda.donor_id = d.donor_id 
                   WHERE hda.hospital_id = h.hospital_id 
                   AND hda.status = 'approved' 
                   AND d.organs_to_donate = ?) as organ_count
                  FROM hospitals h
                  LEFT JOIN hospital_donor_approvals hda ON h.hospital_id = hda.hospital_id
                  LEFT JOIN donor d ON hda.donor_id = d.donor_id
                  WHERE 1=1";

    $params = [$recipient['organ_required']];

    // Add filter conditions
    switch($filter) {
        case 'name':
            $baseQuery .= " AND h.name LIKE ?";
            $params[] = $search;
            break;
        case 'address':
            $baseQuery .= " AND h.address LIKE ?";
            $params[] = $search;
            break;
        case 'phone':
            $baseQuery .= " AND h.phone LIKE ?";
            $params[] = $search;
            break;
        case 'organ':
            $baseQuery .= " AND EXISTS (
                SELECT 1 FROM hospital_donor_approvals hda2 
                JOIN donor d2 ON hda2.donor_id = d2.donor_id
                WHERE hda2.hospital_id = h.hospital_id 
                AND hda2.status = 'approved' 
                AND d2.organs_to_donate = ?)";
            $params[] = $recipient['organ_required'];
            break;
    }

    $baseQuery .= " GROUP BY h.hospital_id ORDER BY organ_count DESC";
    
    $stmt = $conn->prepare($baseQuery);
    $stmt->execute($params);
    $hospitals = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Return results as JSON
    header('Content-Type: application/json');
    echo json_encode(['hospitals' => $hospitals]);
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Search Hospitals - LifeLink</title>
    <link rel="stylesheet" href="../../assets/css/donor-dashboard.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        body {
            font-family: 'Poppins', sans-serif;
            margin: 0;
            padding: 0;
            background: #f4f6f9;
        }

        .dashboard-container {
            display: flex;
            min-height: 100vh;
        }

        .main-content {
            flex: 1;
            margin-left: var(--sidebar-width);
            padding: 2rem;
            background: #f4f6f9;
            width: calc(100% - var(--sidebar-width));
            box-sizing: border-box;
        }

        .search-container {
            background: white;
            border-radius: 15px;
            padding: 30px;
            box-shadow: 0 4px 20px rgba(0,0,0,0.1);
            margin-bottom: 30px;
            width: 100%;
            box-sizing: border-box;
        }

        .search-header {
            margin-bottom: 20px;
        }

        .search-header h2 {
            color: var(--primary-color);
            margin-bottom: 10px;
            font-size: 24px;
        }

        .search-subtitle {
            display: flex;
            align-items: center;
            gap: 8px;
            color: var(--text-secondary);
            margin-bottom: 20px;
        }

        .search-subtitle i {
            color: var(--primary-color);
            font-size: 16px;
        }

        .search-subtitle p {
            margin: 0;
            font-size: 14px;
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
            flex-wrap: wrap;
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

        .organ-availability {
            background: rgba(40, 167, 69, 0.1);
            color: #28a745;
            padding: 8px 12px;
            border-radius: 6px;
            margin-bottom: 15px;
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .organ-availability i {
            color: #28a745;
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

        .no-results {
            text-align: center;
            padding: 40px;
            color: var(--text-secondary);
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
                <i class="fas fa-hospital"></i>
            </div>
            <h2 class="modal-title">Confirm Hospital Request</h2>
            <p class="modal-message">
                You are about to submit an organ recipient request to <strong id="hospitalName"></strong>. Upon submission:
                <br><br>
                • The hospital will review your medical information<br>
                • You'll receive an email notification of their decision<br>
                • If approved, you'll be registered as a potential recipient<br>
                • If not approved, you'll receive detailed feedback
            </p>
            <div class="modal-buttons">
                <button class="modal-btn cancel-btn" onclick="closeModal()">Cancel</button>
                <button class="modal-btn confirm-btn" onclick="submitRequest()">Confirm Request</button>
            </div>
        </div>
    </div>

    <div class="dashboard-container">
        <?php include '../../includes/recipient_sidebar.php'; ?>

        <main class="main-content">
            <!-- Success Card -->
            <div class="success-card" id="successCard">
                <i class="fas fa-check-circle"></i>
                <h3>Request Submitted Successfully!</h3>
                <p>Your organ recipient request has been sent to the hospital. You will receive an email notification once they review your application. Thank you for choosing LifeLink.</p>
            </div>
            <div class="search-container">
                <div class="search-header">
                    <h2>Search Hospitals</h2>
                    <div class="search-subtitle">
                        <i class="fas fa-info-circle"></i>
                        <p>Find hospitals with available <?php echo htmlspecialchars(strtolower($recipient['organ_required'])); ?> donors</p>
                    </div>
                </div>
                <div class="search-box">
                    <i class="fas fa-search search-icon"></i>
                    <input type="text" class="search-input" placeholder="Search hospitals..." id="searchInput">
                </div>
                <div class="filter-buttons">
                    <button class="filter-btn active" data-filter="name">Name</button>
                    <button class="filter-btn" data-filter="address">Address</button>
                    <button class="filter-btn" data-filter="phone">Phone</button>
                    <button class="filter-btn" data-filter="organ">Organ Availability</button>
                </div>
                <div class="hospitals-grid" id="hospitalsGrid">
                    <?php if (empty($allHospitals)): ?>
                        <div class="no-results">
                            <i class="fas fa-hospital" style="font-size: 48px; color: #ddd; margin-bottom: 20px;"></i>
                            <h3>No Hospitals Available</h3>
                            <p>There are currently no hospitals registered in the system.</p>
                        </div>
                    <?php else: ?>
                        <?php foreach ($allHospitals as $hospital): ?>
                            <div class="hospital-card">
                                <div class="hospital-header">
                                    <div class="hospital-icon">
                                        <i class="fas fa-hospital"></i>
                                    </div>
                                    <h3 class="hospital-name"><?php echo htmlspecialchars($hospital['name']); ?></h3>
                                </div>
                                <div class="hospital-info">
                                    <div class="info-item">
                                        <i class="fas fa-map-marker-alt"></i>
                                        <span><?php echo htmlspecialchars($hospital['address']); ?></span>
                                    </div>
                                    <div class="info-item">
                                        <i class="fas fa-phone"></i>
                                        <span><?php echo htmlspecialchars($hospital['phone']); ?></span>
                                    </div>
                                </div>
                                <div class="organ-availability">
                                    <i class="fas fa-check-circle"></i>
                                    <span><?php echo $hospital['organ_count']; ?> potential <?php echo $hospital['organ_count'] === 1 ? 'donor' : 'donors'; ?> available</span>
                                </div>
                                <button class="request-btn" onclick="makeRequest('<?php echo $hospital['hospital_id']; ?>', '<?php echo htmlspecialchars($hospital['name']); ?>'); return false;">
                                    Make Request
                                </button>
                            </div>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>
            </div>
        </main>
    </div>

    <script>
        let currentFilter = 'name';
        let searchTimeout = null;

        // Filter button click handler
        document.querySelectorAll('.filter-btn').forEach(button => {
            button.addEventListener('click', () => {
                document.querySelectorAll('.filter-btn').forEach(btn => btn.classList.remove('active'));
                button.classList.add('active');
                currentFilter = button.dataset.filter;
                performSearch();
            });
        });

        // Search input handler
        document.getElementById('searchInput').addEventListener('input', (e) => {
            clearTimeout(searchTimeout);
            searchTimeout = setTimeout(() => performSearch(), 300);
        });

        // Perform search function
        function performSearch() {
            const searchTerm = document.getElementById('searchInput').value;
            
            fetch(`search_hospitals_for_recipient.php?search=${encodeURIComponent(searchTerm)}&filter=${currentFilter}`)
                .then(response => response.json())
                .then(data => {
                    const hospitalsGrid = document.getElementById('hospitalsGrid');
                    hospitalsGrid.innerHTML = '';

                    if (data.hospitals.length === 0) {
                        hospitalsGrid.innerHTML = `
                            <div class="no-results">
                                <i class="fas fa-search" style="font-size: 48px; color: #ddd; margin-bottom: 20px;"></i>
                                <h3>No hospitals found</h3>
                                <p>Try adjusting your search criteria</p>
                            </div>`;
                        return;
                    }

                    data.hospitals.forEach(hospital => {
                        const card = document.createElement('div');
                        card.className = 'hospital-card';
                        card.innerHTML = `
                            <div class="hospital-header">
                                <div class="hospital-icon">
                                    <i class="fas fa-hospital"></i>
                                </div>
                                <h3 class="hospital-name">${hospital.name}</h3>
                            </div>
                            <div class="hospital-info">
                                <div class="info-item">
                                    <i class="fas fa-map-marker-alt"></i>
                                    <span>${hospital.address}</span>
                                </div>
                                <div class="info-item">
                                    <i class="fas fa-phone"></i>
                                    <span>${hospital.phone}</span>
                                </div>
                            </div>
                            <div class="organ-availability">
                                <i class="fas fa-check-circle"></i>
                                <span>${hospital.organ_count} potential ${hospital.organ_count === 1 ? 'donor' : 'donors'} available</span>
                            </div>
                            <button class="request-btn" onclick="makeRequest('${hospital.hospital_id}', '${hospital.name}'); return false;">
                                Make Request
                            </button>
                        `;
                        hospitalsGrid.appendChild(card);
                    });
                })
                .catch(error => {
                    console.error('Error:', error);
                });
        }

        // Initial search on page load
        performSearch();

        // Confirmation dialog for request
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
    </script>
</body>
</html>
