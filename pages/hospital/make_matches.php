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
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Make Matches - LifeLink</title>
    <link rel="stylesheet" href="../../assets/css/hospital-dashboard.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        .match-container {
            padding: 2rem;
            max-width: 800px;
            margin: 2rem auto;
            background: white;
            border-radius: 15px;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.1);
        }

        .match-buttons {
            display: flex;
            gap: 2rem;
            justify-content: center;
            margin-top: 2rem;
        }

        .match-button {
            padding: 1.5rem 3rem;
            border: none;
            border-radius: 10px;
            font-size: 1.2rem;
            cursor: pointer;
            transition: all 0.3s ease;
            position: relative;
            overflow: hidden;
            display: flex;
            align-items: center;
            gap: 1rem;
            color: white;
        }

        .match-button.donor {
            background: linear-gradient(135deg, var(--primary-blue), var(--primary-green));
        }

        .match-button.recipient {
            background: linear-gradient(135deg, var(--primary-green), var(--primary-blue));
        }

        .match-button:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.2);
        }

        .match-button.shrink {
            transform: scale(0.8);
            opacity: 0.8;
        }

        .match-button i {
            font-size: 1.5rem;
        }

        .make-match-btn {
            display: block;
            margin: 2rem auto;
            padding: 1rem 2rem;
            background: linear-gradient(135deg, var(--primary-blue), var(--primary-green));
            color: white;
            border: none;
            border-radius: 8px;
            font-size: 1.1rem;
            cursor: pointer;
            opacity: 0.5;
            pointer-events: none;
            transition: all 0.3s ease;
        }

        .make-match-btn.active {
            opacity: 1;
            pointer-events: auto;
        }

        .make-match-btn.active:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.2);
        }

        .selection-status {
            text-align: center;
            margin-top: 2rem;
            color: #666;
        }

        .selected-info {
            background: #f8f9fa;
            padding: 1rem;
            border-radius: 8px;
            margin-top: 1rem;
            display: none;
        }

        .selected-info.show {
            display: block;
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
                    <h1>Make Matches</h1>
                </div>
            </div>

            <div class="match-container">
                <div class="match-buttons">
                    <button class="match-button donor" onclick="navigateToChoose('donor')">
                        <i class="fas fa-user"></i>
                        Choose Donor
                    </button>
                    <button class="match-button recipient" onclick="navigateToChoose('recipient')">
                        <i class="fas fa-procedures"></i>
                        Choose Recipient
                    </button>
                </div>

                <div class="selection-status">
                    <div id="donorInfo" class="selected-info">
                        <h3>Selected Donor</h3>
                        <p>No donor selected</p>
                    </div>
                    <div id="recipientInfo" class="selected-info">
                        <h3>Selected Recipient</h3>
                        <p>No recipient selected</p>
                    </div>
                </div>

                <button id="makeMatchBtn" class="make-match-btn" disabled>
                    Make Match
                </button>
            </div>
        </main>
    </div>

    <script>
        function navigateToChoose(type) {
            if (type === 'donor') {
                window.location.href = 'choose_donors_for_matches.php';
            } else {
                window.location.href = 'choose_recipients_for_matches.php';
            }
        }

        // Check URL parameters for selections
        window.onload = function() {
            const urlParams = new URLSearchParams(window.location.search);
            const selectedDonor = urlParams.get('donor');
            const selectedRecipient = urlParams.get('recipient');

            if (selectedDonor) {
                document.getElementById('donorInfo').classList.add('show');
                // Add donor info display logic here
            }

            if (selectedRecipient) {
                document.getElementById('recipientInfo').classList.add('show');
                // Add recipient info display logic here
            }

            // Enable make match button if both are selected
            if (selectedDonor && selectedRecipient) {
                const makeMatchBtn = document.getElementById('makeMatchBtn');
                makeMatchBtn.classList.add('active');
                makeMatchBtn.disabled = false;
            }
        }
    </script>
</body>
</html>
