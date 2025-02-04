<?php
session_start();
require_once '../../config/db_connect.php';

// Check if user is logged in as recipient
if (!isset($_SESSION['is_recipient']) || !$_SESSION['is_recipient']) {
    header("Location: ../recipient_login.php");
    exit();
}

// Check if recipient_id is set in session
if (!isset($_SESSION['recipient_id'])) {
    die("Error: Recipient ID not found in session. Please login again.");
}

// Get recipient details
$recipient_id = $_SESSION['recipient_id'];
try {
    $stmt = $conn->prepare("SELECT * FROM recipient_registration WHERE id = :recipient_id");
    $stmt->execute([':recipient_id' => $recipient_id]);
    $recipient = $stmt->fetch();

    if (!$recipient) {
        error_log("No recipient found with ID: " . $recipient_id);
        die("Recipient not found");
    }
} catch(PDOException $e) {
    error_log("Error fetching recipient details: " . $e->getMessage());
    die("An error occurred while fetching your details");
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Profile Details - LifeLink</title>
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
            background: #f4f6f9;
        }

        .main-content {
            flex: 1;
            margin-left: var(--sidebar-width);
            padding: 2rem;
            background: #f4f6f9;
        }

        .container {
            width: 100%;
            max-width: 1200px;
            margin: 0 auto;
        }

        .details-container {
            max-width: 1200px;
            margin: 2rem auto;
            padding: 0 1.5rem;
        }

        .details-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 2rem;
        }

        .details-header h1 {
            font-size: 2rem;
            color: #2c3e50;
            margin: 0;
        }

        .back-btn {
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            padding: 0.75rem 1.5rem;
            background: #f8f9fa;
            color: #2c3e50;
            border-radius: 8px;
            text-decoration: none;
            transition: all 0.3s ease;
        }

        .back-btn:hover {
            background: #e9ecef;
            transform: translateY(-2px);
        }

        .details-section {
            background: white;
            border-radius: 12px;
            box-shadow: 0 2px 12px rgba(0, 0, 0, 0.1);
            padding: 2rem;
            margin-bottom: 2rem;
        }

        .details-section h2 {
            color: #2c3e50;
            font-size: 1.5rem;
            margin-bottom: 1.5rem;
            display: flex;
            align-items: center;
            gap: 0.75rem;
        }

        .details-section h2 i {
            color: #28a745;
        }

        .info-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 1.5rem;
        }

        .info-item {
            padding: 1rem;
            background: #f8f9fa;
            border-radius: 8px;
            transition: all 0.3s ease;
        }

        .info-item:hover {
            background: #fff;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.05);
        }

        .info-item label {
            display: block;
            color: #6c757d;
            font-size: 0.9rem;
            margin-bottom: 0.5rem;
        }

        .info-item p {
            color: #2c3e50;
            font-size: 1.1rem;
            margin: 0;
            font-weight: 500;
        }

        .info-item.full-width {
            grid-column: 1 / -1;
        }

        .documents-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 1.5rem;
            margin-top: 1rem;
        }

        .document-card {
            background: #f8f9fa;
            border-radius: 12px;
            padding: 1.5rem;
            display: flex;
            align-items: center;
            gap: 1.5rem;
            transition: all 0.3s ease;
        }

        .document-card:hover {
            background: #fff;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
            transform: translateY(-2px);
        }

        .document-icon {
            width: 50px;
            height: 50px;
            background: rgba(40, 167, 69, 0.1);
            border-radius: 10px;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .document-icon i {
            font-size: 1.5rem;
            color: #28a745;
        }

        .document-info {
            flex: 1;
        }

        .document-info h3 {
            margin: 0 0 0.75rem 0;
            color: #2c3e50;
            font-size: 1.1rem;
        }

        .view-btn {
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            padding: 0.5rem 1rem;
            background: rgba(40, 167, 69, 0.1);
            color: #28a745;
            border-radius: 6px;
            text-decoration: none;
            font-size: 0.9rem;
            transition: all 0.3s ease;
        }

        .view-btn:hover {
            background: rgba(40, 167, 69, 0.2);
            transform: translateY(-2px);
        }

        .file-name {
            font-size: 0.9rem;
            color: #6c757d;
            margin: 0.5rem 0 0 0;
        }

        .no-doc {
            color: #6c757d;
            font-style: italic;
            margin: 0;
        }

        .edit-request-section {
            text-align: center;
            margin-top: 2rem;
        }

        .edit-request-btn {
            display: inline-flex;
            align-items: center;
            gap: 0.75rem;
            padding: 1rem 2rem;
            background: linear-gradient(135deg, #28a745, #20c997);
            color: white;
            border: none;
            border-radius: 10px;
            font-size: 1.1rem;
            font-weight: 500;
            cursor: pointer;
            transition: all 0.3s ease;
        }

        .edit-request-btn:hover {
            transform: translateY(-3px);
            box-shadow: 0 4px 15px rgba(40, 167, 69, 0.3);
        }

        /* Modal Styles */
        .modal {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0, 0, 0, 0.5);
            z-index: 1000;
        }

        .modal-content {
            position: relative;
            background: white;
            margin: 5% auto;
            padding: 2rem;
            width: 90%;
            max-width: 600px;
            border-radius: 12px;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.2);
        }

        .close {
            position: absolute;
            right: 1.5rem;
            top: 1.5rem;
            font-size: 1.5rem;
            cursor: pointer;
            color: #6c757d;
            transition: color 0.3s ease;
        }

        .close:hover {
            color: #343a40;
        }

        .form-group {
            margin-bottom: 1.5rem;
        }

        .form-group label {
            display: block;
            margin-bottom: 0.5rem;
            color: #2c3e50;
            font-weight: 500;
        }

        .form-group textarea {
            width: 100%;
            padding: 0.75rem;
            border: 1px solid #ced4da;
            border-radius: 8px;
            resize: vertical;
            min-height: 100px;
            font-family: inherit;
        }

        .form-group textarea:focus {
            outline: none;
            border-color: #28a745;
            box-shadow: 0 0 0 3px rgba(40, 167, 69, 0.2);
        }

        .submit-btn {
            background: #28a745;
            color: white;
            border: none;
            padding: 0.75rem 1.5rem;
            border-radius: 8px;
            font-size: 1rem;
            font-weight: 500;
            cursor: pointer;
            transition: all 0.3s ease;
        }

        .submit-btn:hover {
            background: #218838;
            transform: translateY(-2px);
        }
    </style>
</head>
<body>
    <div class="dashboard-container">
        <?php include '../../includes/recipient_sidebar.php'; ?>
        
        <div class="main-content">
            <div class="container">
                <div class="details-container">
                    <div class="details-header">
                        <h1>Personal Details</h1>
                    </div>
                    
                    <div class="details-section">
                        <h2><i class="fas fa-user-edit"></i> Basic Information</h2>
                        <div class="info-grid">
                            <div class="info-item">
                                <label>Full Name</label>
                                <p><?php echo htmlspecialchars($recipient['full_name'] ?? 'Not provided'); ?></p>
                            </div>

                            <div class="info-item">
                                <label>Date of Birth</label>
                                <p><?php echo htmlspecialchars($recipient['date_of_birth'] ?? 'Not provided'); ?></p>
                            </div>

                            <div class="info-item">
                                <label>Gender</label>
                                <p><?php echo htmlspecialchars($recipient['gender'] ?? 'Not provided'); ?></p>
                            </div>

                            <div class="info-item">
                                <label>Blood Type</label>
                                <p><?php echo htmlspecialchars($recipient['blood_type'] ?? 'Not provided'); ?></p>
                            </div>

                            <div class="info-item">
                                <label>Email</label>
                                <p><?php echo htmlspecialchars($recipient['email'] ?? 'Not provided'); ?></p>
                            </div>

                            <div class="info-item">
                                <label>Phone</label>
                                <p><?php echo htmlspecialchars($recipient['phone_number'] ?? 'Not provided'); ?></p>
                            </div>

                            <div class="info-item full-width">
                                <label>Address</label>
                                <p><?php echo htmlspecialchars($recipient['address'] ?? 'Not provided'); ?></p>
                            </div>
                        </div>
                    </div>

                    <div class="details-section">
                        <h2><i class="fas fa-heartbeat"></i> Medical Information</h2>
                        <div class="info-grid">
                            <div class="info-item">
                                <label>Organ Required</label>
                                <p><?php echo htmlspecialchars($recipient['organ_required'] ?? 'Not provided'); ?></p>
                            </div>

                            <div class="info-item">
                                <label>Urgency Level</label>
                                <p><?php echo htmlspecialchars($recipient['urgency_level'] ?? 'Not provided'); ?></p>
                            </div>

                            <div class="info-item full-width">
                                <label>Medical Condition</label>
                                <p><?php echo htmlspecialchars($recipient['medical_condition'] ?? 'Not provided'); ?></p>
                            </div>

                            <div class="info-item full-width">
                                <label>Reason for Organ Requirement</label>
                                <p><?php echo htmlspecialchars($recipient['organ_reason'] ?? 'Not provided'); ?></p>
                            </div>
                        </div>
                    </div>

                    <div class="details-section">
                        <h2><i class="fas fa-file-alt"></i> Documents</h2>
                        <div class="documents-grid">
                            <div class="document-card">
                                <div class="document-icon">
                                    <i class="fas fa-file-alt"></i>
                                </div>
                                <div class="document-info">
                                    <h3>ID Document (<?php echo htmlspecialchars($recipient['id_proof_type'] ?? 'Not specified'); ?>)</h3>
                                    <?php if (!empty($recipient['id_document'])): ?>
                                        <?php $id_path = "/LIFELINKFORPDD-main/LIFELINKFORPDD/uploads/recipient_registration/id_document/" . $recipient['id_document']; ?>
                                        <a href="javascript:void(0);" onclick="openInNewWindow('<?php echo $id_path; ?>')" class="view-btn">
                                            <i class="fas fa-eye"></i> View Document
                                        </a>
                                    <?php else: ?>
                                        <p class="no-doc">No document uploaded</p>
                                    <?php endif; ?>
                                </div>
                            </div>

                            <div class="document-card">
                                <div class="document-icon">
                                    <i class="fas fa-file-alt"></i>
                                </div>
                                <div class="document-info">
                                    <h3>Medical Reports</h3>
                                    <?php if (!empty($recipient['recipient_medical_reports'])): ?>
                                        <?php $medical_path = "/LIFELINKFORPDD-main/LIFELINKFORPDD/uploads/recipient_registration/recipient_medical_reports/" . $recipient['recipient_medical_reports']; ?>
                                        <a href="javascript:void(0);" onclick="openInNewWindow('<?php echo $medical_path; ?>')" class="view-btn">
                                            <i class="fas fa-eye"></i> View Document
                                        </a>
                                    <?php else: ?>
                                        <p class="no-doc">No document uploaded</p>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="details-section">
                        <h2><i class="fas fa-info-circle"></i> Registration Status</h2>
                        <div class="info-grid">
                            <div class="info-item">
                                <label>ODML ID</label>
                                <p><?php echo htmlspecialchars($recipient['odml_id'] ?? 'Not provided'); ?></p>
                            </div>

                            <div class="info-item">
                                <label>Request Status</label>
                                <p><?php echo htmlspecialchars($recipient['request_status'] ?? 'Not provided'); ?></p>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="edit-request-section">
                    <button type="button" id="editRequestBtn" class="edit-request-btn">
                        <i class="fas fa-edit"></i> Request to Edit Details
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Edit Request Modal -->
    <div id="editRequestModal" class="modal">
        <div class="modal-content">
            <span class="close">&times;</span>
            <h2>Request to Edit Details</h2>
            <form id="editRequestForm">
                <div class="form-group">
                    <label for="fieldsToEdit">What would you like to edit?</label>
                    <textarea id="fieldsToEdit" name="fieldsToEdit" rows="4" required 
                              placeholder="Please specify which details you want to update..."></textarea>
                </div>
                <div class="form-group">
                    <label for="reason">Reason for Edit</label>
                    <textarea id="reason" name="reason" rows="4" required
                              placeholder="Please provide the reason for requesting these changes..."></textarea>
                </div>
                <button type="submit" class="submit-btn">Submit Request</button>
            </form>
        </div>
    </div>

    <script>
        function openInNewWindow(path) {
            window.open(path, '_blank', 'width=800,height=600');
        }

        // Modal functionality
        const modal = document.getElementById('editRequestModal');
        const btn = document.getElementById('editRequestBtn');
        const span = document.getElementsByClassName('close')[0];

        btn.onclick = function() {
            modal.style.display = "block";
        }

        span.onclick = function() {
            modal.style.display = "none";
        }

        window.onclick = function(event) {
            if (event.target == modal) {
                modal.style.display = "none";
            }
        }

        // Handle form submission
        $('#editRequestForm').on('submit', function(e) {
            e.preventDefault();
            // Add your form submission logic here
            alert('Your edit request has been submitted. We will review it shortly.');
            modal.style.display = "none";
        });
    </script>
</body>
</html>
