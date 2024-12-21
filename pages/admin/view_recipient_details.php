<?php
session_start();
require_once '../../backend/php/connection.php';

// Check if admin is logged in
if (!isset($_SESSION['admin_id'])) {
    header('Location: ../admin_login.php');
    exit();
}

// Get recipient ID from URL
if (!isset($_GET['id'])) {
    header('Location: manage_recipients.php');
    exit();
}

// Get recipient details
try {
    $stmt = $conn->prepare("
        SELECT * FROM recipient_registration 
        WHERE id = ?
    ");
    $stmt->execute([$_GET['id']]);
    $recipient = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$recipient) {
        header('Location: manage_recipients.php');
        exit();
    }
} catch (PDOException $e) {
    error_log("Error fetching recipient details: " . $e->getMessage());
    header('Location: manage_recipients.php');
    exit();
}

// Get medical reports from the recipient_medical_reports column
$medical_reports = [];
if (!empty($recipient['recipient_medical_reports'])) {
    $medical_reports = explode(',', $recipient['recipient_medical_reports']);
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Recipient Details - LifeLink Admin</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link rel="stylesheet" href="../../assets/css/styles.css">
    <style>
        .details-container {
            max-width: 1000px;
            margin: 2rem auto;
            padding: 2rem;
            background: white;
            border-radius: 10px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }

        .page-title {
            text-align: center;
            margin-bottom: 2rem;
            font-size: 2.5rem;
            font-weight: bold;
            background: linear-gradient(to right, #1565C0, #00BCD4);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            text-transform: uppercase;
            letter-spacing: 2px;
        }

        .section {
            margin-bottom: 2rem;
            padding: 1.5rem;
            background: #f8f9fa;
            border-radius: 8px;
        }

        .section-title {
            color: #1565C0;
            font-size: 1.5rem;
            margin-bottom: 1rem;
            padding-bottom: 0.5rem;
            border-bottom: 2px solid #e0e0e0;
        }

        .info-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 1.5rem;
        }

        .info-item {
            margin-bottom: 1rem;
        }

        .info-label {
            font-weight: bold;
            color: #666;
            margin-bottom: 0.5rem;
        }

        .info-value {
            color: #333;
            font-size: 1.1rem;
        }

        .document-link {
            display: inline-flex;
            align-items: center;
            padding: 0.5rem 1rem;
            background: #e3f2fd;
            color: #1565C0;
            border-radius: 4px;
            text-decoration: none;
            margin: 0.5rem 0;
            transition: all 0.3s ease;
        }

        .document-link:hover {
            background: #1565C0;
            color: white;
        }

        .document-link i {
            margin-right: 0.5rem;
        }

        .back-btn {
            display: inline-flex;
            align-items: center;
            padding: 0.75rem 1.5rem;
            background: #1565C0;
            color: white;
            border: none;
            border-radius: 4px;
            text-decoration: none;
            margin-bottom: 2rem;
            transition: all 0.3s ease;
        }

        .back-btn:hover {
            background: #0d47a1;
            transform: translateY(-2px);
        }

        .back-btn i {
            margin-right: 0.5rem;
        }

        .status-badge {
            display: inline-block;
            padding: 0.5rem 1rem;
            border-radius: 20px;
            font-weight: bold;
        }

        .status-pending { background: #e3f2fd; color: #1565c0; }
        .status-accepted { background: #e8f5e9; color: #2e7d32; }
        .status-rejected { background: #ffebee; color: #c62828; }

        .medical-reports-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(200px, 1fr));
            gap: 1rem;
            margin-top: 1rem;
        }

        .report-card {
            background: white;
            padding: 1rem;
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }

        .report-card a {
            display: flex;
            flex-direction: column;
            align-items: center;
            text-decoration: none;
            color: #333;
        }

        .report-card i {
            font-size: 2rem;
            color: #1565C0;
            margin-bottom: 0.5rem;
        }

        .report-name {
            text-align: center;
            word-break: break-word;
        }
    </style>
</head>
<body>
    <div class="container">
        <a href="manage_recipients.php" class="back-btn">
            <i class="fas fa-arrow-left"></i> Back to Recipients
        </a>

        <div class="details-container">
            <h1 class="page-title">Recipient Details</h1>

            <!-- Personal Information -->
            <div class="section">
                <h2 class="section-title">Personal Information</h2>
                <div class="info-grid">
                    <div class="info-item">
                        <div class="info-label">Full Name</div>
                        <div class="info-value"><?php echo htmlspecialchars($recipient['full_name']); ?></div>
                    </div>
                    <div class="info-item">
                        <div class="info-label">Date of Birth</div>
                        <div class="info-value"><?php echo htmlspecialchars($recipient['date_of_birth']); ?></div>
                    </div>
                    <div class="info-item">
                        <div class="info-label">Gender</div>
                        <div class="info-value"><?php echo htmlspecialchars($recipient['gender']); ?></div>
                    </div>
                    <div class="info-item">
                        <div class="info-label">Phone Number</div>
                        <div class="info-value"><?php echo htmlspecialchars($recipient['phone_number']); ?></div>
                    </div>
                    <div class="info-item">
                        <div class="info-label">Email</div>
                        <div class="info-value"><?php echo htmlspecialchars($recipient['email']); ?></div>
                    </div>
                    <div class="info-item">
                        <div class="info-label">Address</div>
                        <div class="info-value"><?php echo htmlspecialchars($recipient['address']); ?></div>
                    </div>
                </div>
            </div>

            <!-- Medical Information -->
            <div class="section">
                <h2 class="section-title">Medical Information</h2>
                <div class="info-grid">
                    <div class="info-item">
                        <div class="info-label">Medical Condition</div>
                        <div class="info-value"><?php echo htmlspecialchars($recipient['medical_condition']); ?></div>
                    </div>
                    <div class="info-item">
                        <div class="info-label">Blood Type</div>
                        <div class="info-value"><?php echo htmlspecialchars($recipient['blood_type']); ?></div>
                    </div>
                    <div class="info-item">
                        <div class="info-label">Required Organ</div>
                        <div class="info-value"><?php echo htmlspecialchars($recipient['organ_required']); ?></div>
                    </div>
                    <div class="info-item">
                        <div class="info-label">Organ Requirement Reason</div>
                        <div class="info-value"><?php echo htmlspecialchars($recipient['organ_reason']); ?></div>
                    </div>
                    <div class="info-item">
                        <div class="info-label">Urgency Level</div>
                        <div class="info-value">
                            <span class="status-badge status-<?php echo strtolower($recipient['urgency_level']); ?>">
                                <?php echo htmlspecialchars($recipient['urgency_level']); ?>
                            </span>
                        </div>
                    </div>
                    <div class="info-item">
                        <div class="info-label">Status</div>
                        <div class="info-value">
                            <span class="status-badge status-<?php echo strtolower($recipient['request_status']); ?>">
                                <?php echo htmlspecialchars($recipient['request_status'] === 'accepted' ? 'Approved' : ucfirst($recipient['request_status'])); ?>
                            </span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Documents -->
            <div class="section">
                <h2 class="section-title">Documents</h2>
                
                <!-- ID Proof -->
                <div class="info-item">
                    <div class="info-label">ID Proof (<?php echo htmlspecialchars($recipient['id_proof_type']); ?>)</div>
                    <div class="info-value">
                        <?php if (!empty($recipient['id_document'])): ?>
                            <a href="../../uploads/recipient_registration/id_documents/<?php echo htmlspecialchars($recipient['id_document']); ?>" 
                               class="document-link" target="_blank">
                                <i class="fas fa-id-card"></i> View ID Document
                            </a>
                        <?php else: ?>
                            <span class="text-muted">No ID document uploaded</span>
                        <?php endif; ?>
                    </div>
                </div>

                <!-- Medical Reports -->
                <div class="info-item">
                    <div class="info-label">Medical Reports</div>
                    <div class="medical-reports-grid">
                        <?php if (!empty($medical_reports)): ?>
                            <?php foreach ($medical_reports as $report): 
                                $report = trim($report);
                                if (!empty($report)):
                            ?>
                                <div class="report-card">
                                    <a href="../../uploads/recipient_registration/recipient_medical_reports/<?php echo htmlspecialchars($report); ?>" 
                                       target="_blank">
                                        <i class="fas fa-file-medical"></i>
                                        <span class="report-name"><?php echo htmlspecialchars($report); ?></span>
                                    </a>
                                </div>
                            <?php 
                                endif;
                            endforeach; 
                            ?>
                        <?php else: ?>
                            <div class="info-value">No medical reports uploaded</div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
</body>
</html>
