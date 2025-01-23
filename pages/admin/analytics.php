<?php
session_start();
require_once '../../backend/php/connection.php';
require_once '../../backend/php/queries.php';

// Check if admin is logged in
if (!isset($_SESSION['admin_id'])) {
    header('Location: ../admin_login.php');
    exit();
}

// Get analytics data
$donorStats = getAnalyticsDonorStats($conn);
$recipientStats = getAnalyticsRecipientStats($conn);
$hospitalStats = getAnalyticsHospitalStats($conn);
$organMatchStats = getAnalyticsOrganMatchStats($conn);
$totalUsersStats = getAnalyticsTotalUsersStats($conn);
$rejectionStats = getAnalyticsRejectionStats($conn);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Analytics - LifeLink</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link rel="stylesheet" href="../../assets/css/styles.css">
    <link rel="stylesheet" href="../../assets/css/admin-dashboard.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <style>
        .analytics-grid {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 20px;
            padding: 20px;
        }
        .chart-card {
            background: white;
            border-radius: 10px;
            padding: 20px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }
        .chart-container {
            position: relative;
            height: 300px;
        }
    </style>
</head>
<body>
    <div class="admin-container">
        <!-- Sidebar -->
        <?php include '../../components/admin_sidebar.php'; ?>

        <!-- Main Content -->
        <div class="main-content">
            <div class="analytics-grid">
                <!-- Donors Chart -->
                <div class="chart-card">
                    <h2>Donor Statistics</h2>
                    <div class="chart-container">
                        <canvas id="donorsChart"></canvas>
                    </div>
                </div>

                <!-- Recipients Chart -->
                <div class="chart-card">
                    <h2>Recipient Statistics</h2>
                    <div class="chart-container">
                        <canvas id="recipientsChart"></canvas>
                    </div>
                </div>

                <!-- Hospitals Chart -->
                <div class="chart-card">
                    <h2>Hospital Statistics</h2>
                    <div class="chart-container">
                        <canvas id="hospitalsChart"></canvas>
                    </div>
                </div>

                <!-- Organ Matches Chart -->
                <div class="chart-card">
                    <h2>Organ Match Statistics</h2>
                    <div class="chart-container">
                        <canvas id="matchesChart"></canvas>
                    </div>
                </div>

                <!-- Total Users Chart -->
                <div class="chart-card">
                    <h2>Total Users Distribution</h2>
                    <div class="chart-container">
                        <canvas id="usersChart"></canvas>
                    </div>
                </div>

                <!-- Rejections Chart -->
                <div class="chart-card">
                    <h2>Rejection Statistics</h2>
                    <div class="chart-container">
                        <canvas id="rejectionsChart"></canvas>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        const chartColors = {
            approved: '#4CAF50',
            rejected: '#F44336',
            pending: '#FFC107',
            donors: '#2196F3',
            recipients: '#9C27B0',
            hospitals: '#FF9800'
        };

        document.addEventListener('DOMContentLoaded', function() {
            // Donors Chart
            new Chart(document.getElementById('donorsChart'), {
                type: 'doughnut',
                data: {
                    labels: ['Approved', 'Rejected', 'Pending'],
                    datasets: [{
                        data: [<?php echo $donorStats['approved']; ?>, <?php echo $donorStats['rejected']; ?>, <?php echo $donorStats['pending']; ?>],
                        backgroundColor: [chartColors.approved, chartColors.rejected, chartColors.pending]
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    cutout: '70%'
                }
            });

            // Recipients Chart
            new Chart(document.getElementById('recipientsChart'), {
                type: 'doughnut',
                data: {
                    labels: ['Approved', 'Rejected', 'Pending'],
                    datasets: [{
                        data: [<?php echo $recipientStats['approved']; ?>, <?php echo $recipientStats['rejected']; ?>, <?php echo $recipientStats['pending']; ?>],
                        backgroundColor: [chartColors.approved, chartColors.rejected, chartColors.pending]
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    cutout: '70%'
                }
            });

            // Hospitals Chart
            new Chart(document.getElementById('hospitalsChart'), {
                type: 'doughnut',
                data: {
                    labels: ['Approved', 'Rejected', 'Pending'],
                    datasets: [{
                        data: [<?php echo $hospitalStats['approved']; ?>, <?php echo $hospitalStats['rejected']; ?>, <?php echo $hospitalStats['pending']; ?>],
                        backgroundColor: [chartColors.approved, chartColors.rejected, chartColors.pending]
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    cutout: '70%'
                }
            });

            // Organ Matches Chart
            new Chart(document.getElementById('matchesChart'), {
                type: 'doughnut',
                data: {
                    labels: ['Successful', 'Failed', 'Pending'],
                    datasets: [{
                        data: [<?php echo $organMatchStats['approved']; ?>, <?php echo $organMatchStats['rejected']; ?>, <?php echo $organMatchStats['pending']; ?>],
                        backgroundColor: [chartColors.approved, chartColors.rejected, chartColors.pending]
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    cutout: '70%'
                }
            });

            // Total Users Chart
            new Chart(document.getElementById('usersChart'), {
                type: 'doughnut',
                data: {
                    labels: ['Donors', 'Recipients', 'Hospitals'],
                    datasets: [{
                        data: [<?php echo $totalUsersStats['donors']; ?>, <?php echo $totalUsersStats['recipients']; ?>, <?php echo $totalUsersStats['hospitals']; ?>],
                        backgroundColor: [chartColors.donors, chartColors.recipients, chartColors.hospitals]
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    cutout: '70%'
                }
            });

            // Rejections Chart
            new Chart(document.getElementById('rejectionsChart'), {
                type: 'doughnut',
                data: {
                    labels: ['Donor Rejections', 'Recipient Rejections', 'Hospital Rejections'],
                    datasets: [{
                        data: [<?php echo $rejectionStats['donor_rejections']; ?>, <?php echo $rejectionStats['recipient_rejections']; ?>, <?php echo $rejectionStats['hospital_rejections']; ?>],
                        backgroundColor: [chartColors.donors, chartColors.recipients, chartColors.hospitals]
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    cutout: '70%'
                }
            });
        });
    </script>
</body>
</html>
