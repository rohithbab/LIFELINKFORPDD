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
            max-width: 1000px;
            margin: 2rem auto;
            background: white;
            border-radius: 15px;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.1);
        }

        .match-section {
            display: flex;
            gap: 2rem;
            justify-content: center;
            margin-top: 2rem;
        }

        .match-column {
            flex: 1;
            max-width: 400px;
            display: flex;
            flex-direction: column;
            align-items: center;
            gap: 1rem;
        }

        .match-button {
            width: 100%;
            padding: 1.5rem;
            border: none;
            border-radius: 10px;
            font-size: 1.2rem;
            cursor: pointer;
            transition: all 0.3s ease;
            position: relative;
            overflow: hidden;
            display: flex;
            align-items: center;
            justify-content: center;
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

        .match-button i {
            font-size: 1.5rem;
        }

        .make-match-btn {
            display: block;
            width: 200px;
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

        .selected-info {
            width: 100%;
            display: none;
            margin-top: 1rem;
        }

        .selected-info.show {
            display: block;
        }

        .info-card {
            background: #f8f9fa;
            border-radius: 8px;
            padding: 1rem;
            border-left: 4px solid var(--primary-blue);
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
            position: relative;
        }

        .info-card p {
            margin: 0.5rem 0;
            color: #333;
            font-size: 0.95rem;
        }

        .info-card strong {
            color: var(--primary-blue);
            display: inline-block;
            width: 100px;
        }

        .remove-btn {
            position: absolute;
            top: 0.5rem;
            right: 0.5rem;
            padding: 0.3rem 0.6rem;
            background: #ff4444;
            color: white;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            font-size: 0.8rem;
            transition: all 0.2s ease;
            display: flex;
            align-items: center;
            gap: 0.3rem;
        }

        .remove-btn:hover {
            background: #cc0000;
            transform: translateY(-1px);
        }

        .remove-btn i {
            font-size: 0.8rem;
        }

        .no-selection {
            text-align: center;
            color: #666;
            font-style: italic;
            padding: 1rem;
            background: #f8f9fa;
            border-radius: 8px;
            margin-top: 1rem;
        }

        .modal {
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

        .modal.show {
            display: flex;
        }

        .modal-content {
            background: white;
            padding: 2rem;
            border-radius: 10px;
            width: 90%;
            max-width: 400px;
            text-align: center;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.2);
        }

        .modal-buttons {
            display: flex;
            justify-content: center;
            gap: 1rem;
            margin-top: 1.5rem;
        }

        .modal-btn {
            padding: 0.5rem 1.5rem;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            transition: all 0.2s ease;
        }

        .modal-btn.confirm {
            background: #ff4444;
            color: white;
        }

        .modal-btn.cancel {
            background: #eee;
            color: #333;
        }

        .modal-btn:hover {
            transform: translateY(-1px);
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
        }
    </style>
</head>
<body>
    <div class="dashboard-container">
        <?php include '../../includes/hospital_sidebar.php'; ?>
        
        <main class="main-content">
            <div class="dashboard-header">
                <div class="header-left">
                    <h1>Make Matches</h1>
                </div>
            </div>

            <div class="match-container">
                <div class="match-section">
                    <div class="match-column">
                        <button class="match-button donor" onclick="navigateToChoose('donor')">
                            <i class="fas fa-user"></i>
                            Choose Donor
                        </button>
                        <div id="donorInfo" class="selected-info">
                            <h3>Selected Donor</h3>
                            <div id="donorDetails">No donor selected</div>
                        </div>
                    </div>
                    <div class="match-column">
                        <button class="match-button recipient" onclick="navigateToChoose('recipient')">
                            <i class="fas fa-procedures"></i>
                            Choose Recipient
                        </button>
                        <div id="recipientInfo" class="selected-info">
                            <h3>Selected Recipient</h3>
                            <div id="recipientDetails">No recipient selected</div>
                        </div>
                    </div>
                </div>

                <button id="makeMatchBtn" class="make-match-btn" disabled onclick="makeMatch()">
                    Make Match
                </button>
            </div>
        </main>
    </div>

    <div class="modal" id="removeConfirmationModal">
        <div class="modal-content">
            <h2>Confirm Removal</h2>
            <p>Are you sure you want to remove the selected <span id="remove-type"></span>?</p>
            <div class="modal-buttons">
                <button class="modal-btn confirm" onclick="confirmRemove()">Yes, Remove</button>
                <button class="modal-btn cancel" onclick="cancelRemove()">Cancel</button>
            </div>
        </div>
    </div>

    <script>
        function navigateToChoose(type) {
            if (type === 'donor') {
                window.location.href = 'choose_donors_for_matches.php';
            } else {
                window.location.href = 'choose_recipients_for_matches.php';
            }
        }

        function updateMatchButton() {
            const urlParams = new URLSearchParams(window.location.search);
            const selectedDonorId = urlParams.get('donor');
            const selectedRecipientId = urlParams.get('recipient');
            const storedDonor = sessionStorage.getItem('selectedDonor');
            const storedRecipient = sessionStorage.getItem('selectedRecipient');

            if (selectedDonorId && selectedRecipientId && storedDonor && storedRecipient) {
                const makeMatchBtn = document.getElementById('makeMatchBtn');
                makeMatchBtn.classList.add('active');
                makeMatchBtn.disabled = false;
            }
        }

        // Check URL parameters and session storage for selections
        window.onload = function() {
            const urlParams = new URLSearchParams(window.location.search);
            const selectedDonorId = urlParams.get('donor');
            const selectedRecipientId = urlParams.get('recipient');

            // Get stored donor info
            const storedDonor = sessionStorage.getItem('selectedDonor');
            if (storedDonor && selectedDonorId) {
                const donorInfo = JSON.parse(storedDonor);
                document.getElementById('donorInfo').classList.add('show');
                document.getElementById('donorDetails').innerHTML = `
                    <div class="info-card">
                        <button class="remove-btn" onclick="showRemoveConfirmation('Donor')">
                            <i class="fas fa-times"></i> Remove
                        </button>
                        <p><strong>Name:</strong> ${donorInfo.name}</p>
                        <p><strong>Blood Group:</strong> ${donorInfo.bloodGroup}</p>
                        <p><strong>Organ Type:</strong> ${donorInfo.organType}</p>
                    </div>
                `;
            } else {
                document.getElementById('donorDetails').innerHTML = `
                    <div class="no-selection">No donor selected</div>
                `;
            }

            // Get stored recipient info
            const storedRecipient = sessionStorage.getItem('selectedRecipient');
            if (storedRecipient && selectedRecipientId) {
                const recipientInfo = JSON.parse(storedRecipient);
                document.getElementById('recipientInfo').classList.add('show');
                document.getElementById('recipientDetails').innerHTML = `
                    <div class="info-card">
                        <button class="remove-btn" onclick="showRemoveConfirmation('Recipient')">
                            <i class="fas fa-times"></i> Remove
                        </button>
                        <p><strong>Name:</strong> ${recipientInfo.name}</p>
                        <p><strong>Blood Group:</strong> ${recipientInfo.bloodGroup}</p>
                        <p><strong>Required Organ:</strong> ${recipientInfo.requiredOrgan}</p>
                    </div>
                `;
            } else {
                document.getElementById('recipientDetails').innerHTML = `
                    <div class="no-selection">No recipient selected</div>
                `;
            }

            // Check and update Make Match button
            updateMatchButton();
        }

        function showRemoveConfirmation(type) {
            const modal = document.getElementById('removeConfirmationModal');
            modal.classList.add('show');
            document.getElementById('remove-type').innerText = type;
        }

        function confirmRemove() {
            const modal = document.getElementById('removeConfirmationModal');
            modal.classList.remove('show');
            const removeType = document.getElementById('remove-type').innerText;
            if (removeType === 'Donor') {
                removeDonorSelection();
            } else {
                removeRecipientSelection();
            }
        }

        function cancelRemove() {
            const modal = document.getElementById('removeConfirmationModal');
            modal.classList.remove('show');
        }

        function removeDonorSelection() {
            sessionStorage.removeItem('selectedDonor');
            document.getElementById('donorInfo').classList.remove('show');
            document.getElementById('donorDetails').innerHTML = `
                <div class="no-selection">No donor selected</div>
            `;
            updateMatchButton();
        }

        function removeRecipientSelection() {
            sessionStorage.removeItem('selectedRecipient');
            document.getElementById('recipientInfo').classList.remove('show');
            document.getElementById('recipientDetails').innerHTML = `
                <div class="no-selection">No recipient selected</div>
            `;
            updateMatchButton();
        }

        function makeMatch() {
            const urlParams = new URLSearchParams(window.location.search);
            const donorId = urlParams.get('donor');
            const recipientId = urlParams.get('recipient');

            // TODO: Add your match creation logic here
            console.log('Creating match between donor', donorId, 'and recipient', recipientId);
        }
    </script>
</body>
</html>
