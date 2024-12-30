<?php
session_start();
require_once '../../config/db_connect.php';

// Check if hospital is logged in
if (!isset($_SESSION['hospital_id'])) {
    header("Location: ../hospital_login.php");
    exit();
}

$hospital_id = $_SESSION['hospital_id'];

// Handle status updates
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['request_id']) && isset($_POST['status'])) {
    try {
        $stmt = $conn->prepare("UPDATE hospital_recipient_approvals SET status = ? WHERE approval_id = ? AND hospital_id = ?");
        $stmt->execute([$_POST['status'], $_POST['request_id'], $hospital_id]);
        
        // Redirect to prevent form resubmission
        header("Location: hospitals_handles_recipients_status.php?success=1");
        exit();
    } catch(PDOException $e) {
        $error = "Error updating status: " . $e->getMessage();
    }
}

// Fetch all recipient requests for this hospital
try {
    $stmt = $conn->prepare("
        SELECT 
            r.full_name,
            r.blood_type,
            r.organ_required,
            r.medical_condition,
            r.urgency_level,
            r.recipient_medical_reports,
            r.id_proof_type,
            r.id_proof_number,
            r.id_document,
            hra.status,
            hra.request_date,
            hra.approval_id AS request_id
        FROM hospital_recipient_approvals hra
        JOIN recipient_registration r ON hra.recipient_id = r.id
        WHERE hra.hospital_id = ?
        ORDER BY 
            CASE 
                WHEN hra.status = 'pending' THEN 1
                WHEN hra.status = 'approved' THEN 2
                ELSE 3
            END,
            hra.request_date DESC
    ");
    $stmt->execute([$hospital_id]);
    $requests = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch(PDOException $e) {
    $error = "Error fetching requests: " . $e->getMessage();
}

// Get hospital info
try {
    $stmt = $conn->prepare("SELECT * FROM hospitals WHERE hospital_id = ?");
    $stmt->execute([$hospital_id]);
    $hospital = $stmt->fetch(PDO::FETCH_ASSOC);
} catch(PDOException $e) {
    die("Error fetching hospital details: " . $e->getMessage());
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Recipient Requests - <?php echo htmlspecialchars($hospital['name']); ?></title>
    <link rel="stylesheet" href="../../assets/css/dashboard.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
    <style>
        .details-section {
            background: #fff;
            padding: 20px;
            border-radius: 8px;
            margin-top: 10px;
            display: none;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }

        .details-grid {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 20px;
            margin-top: 15px;
        }

        .detail-item {
            padding: 15px;
            background: #f8f9fa;
            border-radius: 8px;
            border: 1px solid #e9ecef;
        }

        .detail-item label {
            font-weight: 600;
            color: #495057;
            display: block;
            margin-bottom: 8px;
            font-size: 0.9rem;
        }

        .detail-item p {
            margin: 0;
            color: #6c757d;
        }

        .document-preview {
            max-width: 100%;
            height: auto;
            margin-top: 10px;
            border-radius: 4px;
            border: 1px solid #dee2e6;
        }

        .status-badge {
            padding: 6px 12px;
            border-radius: 20px;
            font-size: 0.85em;
            font-weight: 500;
        }

        .status-pending {
            background: rgba(255, 191, 0, 0.2);
            color: #ffc107;
        }

        .status-approved {
            background: rgba(40, 167, 69, 0.2);
            color: #28a745;
        }

        .status-rejected {
            background: rgba(220, 53, 69, 0.2);
            color: #dc3545;
        }

        .action-buttons {
            display: flex;
            gap: 8px;
        }

        .action-btn {
            padding: 6px 12px;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            font-size: 0.9em;
            display: flex;
            align-items: center;
            gap: 5px;
            transition: all 0.3s ease;
        }

        .view-btn {
            background: #6c757d;
            color: white;
        }

        .approve-btn {
            background: #28a745;
            color: white;
        }

        .reject-btn {
            background: #dc3545;
            color: white;
        }

        .action-btn:hover {
            opacity: 0.9;
        }

        .urgency-badge {
            padding: 4px 8px;
            border-radius: 4px;
            font-size: 0.85em;
            font-weight: 500;
        }

        .urgency-high {
            background: rgba(220, 53, 69, 0.2);
            color: #dc3545;
        }

        .urgency-medium {
            background: rgba(255, 191, 0, 0.2);
            color: #ffc107;
        }

        .urgency-low {
            background: rgba(40, 167, 69, 0.2);
            color: #28a745;
        }
    </style>
</head>
<body>
    <div class="dashboard-container">
        <!-- Include your sidebar here -->
        <?php include '../includes/hospital_sidebar.php'; ?>

        <main class="main-content">
            <div class="container">
                <header class="dashboard-header">
                    <h1>Manage Recipient Requests</h1>
                </header>

                <?php if (isset($error)): ?>
                    <div class="alert alert-danger"><?php echo $error; ?></div>
                <?php endif; ?>

                <?php if (isset($_GET['success'])): ?>
                    <div class="alert alert-success">Status updated successfully!</div>
                <?php endif; ?>

                <div class="table-responsive">
                    <?php if (empty($requests)): ?>
                        <div class="empty-state">
                            <i class="fas fa-inbox"></i>
                            <h3>No Recipient Requests</h3>
                            <p>There are no recipient requests at this time.</p>
                        </div>
                    <?php else: ?>
                        <table class="modern-table">
                            <thead>
                                <tr>
                                    <th>Recipient Name</th>
                                    <th>Blood Type</th>
                                    <th>Organ Required</th>
                                    <th>Urgency Level</th>
                                    <th>Status</th>
                                    <th>Request Date</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($requests as $request): ?>
                                    <tr>
                                        <td><?php echo htmlspecialchars($request['full_name']); ?></td>
                                        <td><?php echo htmlspecialchars($request['blood_type']); ?></td>
                                        <td><?php echo htmlspecialchars($request['organ_required']); ?></td>
                                        <td>
                                            <span class="urgency-badge urgency-<?php echo strtolower($request['urgency_level']); ?>">
                                                <?php echo htmlspecialchars($request['urgency_level']); ?>
                                            </span>
                                        </td>
                                        <td>
                                            <span class="status-badge status-<?php echo strtolower($request['status']); ?>">
                                                <?php echo htmlspecialchars($request['status']); ?>
                                            </span>
                                        </td>
                                        <td><?php echo date('Y-m-d', strtotime($request['request_date'])); ?></td>
                                        <td>
                                            <div class="action-buttons">
                                                <button class="action-btn view-btn" onclick="toggleDetails(<?php echo $request['request_id']; ?>)">
                                                    <i class="fas fa-eye"></i> Details
                                                </button>
                                                <?php if ($request['status'] === 'pending'): ?>
                                                    <button class="action-btn approve-btn" onclick="updateStatus(<?php echo $request['request_id']; ?>, 'approved')">
                                                        <i class="fas fa-check"></i> Approve
                                                    </button>
                                                    <button class="action-btn reject-btn" onclick="updateStatus(<?php echo $request['request_id']; ?>, 'rejected')">
                                                        <i class="fas fa-times"></i> Reject
                                                    </button>
                                                <?php endif; ?>
                                            </div>
                                        </td>
                                    </tr>
                                    <tr>
                                        <td colspan="7">
                                            <div id="details-<?php echo $request['request_id']; ?>" class="details-section">
                                                <h3>Recipient Details</h3>
                                                <div class="details-grid">
                                                    <div class="detail-item">
                                                        <label>Medical Condition</label>
                                                        <p><?php echo htmlspecialchars($request['medical_condition']); ?></p>
                                                    </div>
                                                    <div class="detail-item">
                                                        <label>ID Proof Type</label>
                                                        <p><?php echo htmlspecialchars($request['id_proof_type']); ?></p>
                                                    </div>
                                                    <div class="detail-item">
                                                        <label>ID Proof Number</label>
                                                        <p><?php echo htmlspecialchars($request['id_proof_number']); ?></p>
                                                    </div>
                                                    <div class="detail-item">
                                                        <label>Medical Reports</label>
                                                        <?php if ($request['recipient_medical_reports']): ?>
                                                            <img src="<?php echo htmlspecialchars($request['recipient_medical_reports']); ?>" 
                                                                 alt="Medical Reports" class="document-preview">
                                                        <?php else: ?>
                                                            <p>No medical reports available</p>
                                                        <?php endif; ?>
                                                    </div>
                                                    <div class="detail-item">
                                                        <label>ID Document</label>
                                                        <?php if ($request['id_document']): ?>
                                                            <img src="<?php echo htmlspecialchars($request['id_document']); ?>" 
                                                                 alt="ID Document" class="document-preview">
                                                        <?php else: ?>
                                                            <p>No ID document available</p>
                                                        <?php endif; ?>
                                                    </div>
                                                </div>
                                            </div>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    <?php endif; ?>
                </div>
            </div>
        </main>
    </div>

    <script>
        function toggleDetails(requestId) {
            const detailsSection = document.getElementById(`details-${requestId}`);
            if (detailsSection.style.display === 'none' || !detailsSection.style.display) {
                detailsSection.style.display = 'block';
            } else {
                detailsSection.style.display = 'none';
            }
        }

        function updateStatus(requestId, status) {
            if (confirm(`Are you sure you want to ${status} this request?`)) {
                const form = document.createElement('form');
                form.method = 'POST';
                form.innerHTML = `
                    <input type="hidden" name="request_id" value="${requestId}">
                    <input type="hidden" name="status" value="${status}">
                `;
                document.body.appendChild(form);
                form.submit();
            }
        }
    </script>
</body>
</html>
