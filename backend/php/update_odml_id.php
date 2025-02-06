<?php
session_start();
require_once __DIR__ . '/connection.php';
require_once __DIR__ . '/queries.php';
require_once __DIR__ . '/helpers/mailer.php';

// Check if admin is logged in
if (!isset($_SESSION['admin_id'])) {
    echo json_encode(['success' => false, 'message' => 'Unauthorized access']);
    exit();
}

// Check if required parameters are set
if (!isset($_POST['type']) || !isset($_POST['id']) || !isset($_POST['odml_id'])) {
    echo json_encode(['success' => false, 'message' => 'Missing required parameters']);
    exit();
}

$type = $_POST['type'];
$id = $_POST['id'];
$odml_id = $_POST['odml_id'];

try {
    // Start transaction
    $conn->beginTransaction();
    
    // Flag to track email success
    $emailSent = false;
    
    switch ($type) {
        case 'donor':
            // 1. First verify donor exists and is in pending state
            $stmt = $conn->prepare("SELECT name, email, status FROM donors WHERE donor_id = ? AND status = 'pending' FOR UPDATE");
            $stmt->execute([$id]);
            $donor = $stmt->fetch(PDO::FETCH_ASSOC);

            if (!$donor) {
                throw new Exception("Donor not found or not in pending state");
            }

            // 2. Try to send email first
            $mailer = new Mailer();
            try {
                error_log("Starting email send process for donor: " . $donor['name']);
                
                // Test SMTP connection first
                if (!$mailer->testConnection()) {
                    throw new Exception("Failed to connect to email server");
                }
                
                // Try to send the email
                $mailer->sendDonorApprovalEmail(
                    $donor['email'],
                    $donor['name'],
                    $odml_id
                );
                
                // If we got here, email was sent successfully
                $emailSent = true;
                error_log("Email sent successfully to: " . $donor['email']);
                
            } catch (Exception $e) {
                error_log("Email sending failed: " . $e->getMessage());
                $conn->rollBack(); // Roll back immediately if email fails
                echo json_encode(['success' => false, 'message' => 'Email sending failed: ' . $e->getMessage()]);
                exit();
            }

            // 3. Only if email was sent successfully, update database
            if ($emailSent) {
                error_log("Email sent successfully, proceeding with database update");
                
                // Double check donor is still in pending state
                $stmt = $conn->prepare("SELECT status FROM donors WHERE donor_id = ? AND status = 'pending' FOR UPDATE");
                $stmt->execute([$id]);
                if (!$stmt->fetch()) {
                    throw new Exception("Donor status changed during approval process");
                }
                
                // Update donor status
                $stmt = $conn->prepare("UPDATE donors SET status = 'Approved', odml_id = ?, approved_by = ?, approved_at = NOW() WHERE donor_id = ? AND status = 'pending'");
                if (!$stmt->execute([$odml_id, $_SESSION['admin_id'], $id])) {
                    throw new Exception("Failed to update donor status");
                }

                // Verify the update was successful
                $stmt = $conn->prepare("SELECT status FROM donors WHERE donor_id = ? AND status = 'Approved'");
                $stmt->execute([$id]);
                if (!$stmt->fetch()) {
                    throw new Exception("Failed to verify donor status update");
                }

                // Create notification
                $notification_message = "Donor {$donor['name']} has been approved with ODML ID: {$odml_id}";
                $stmt = $conn->prepare("INSERT INTO notifications (type, action, reference_id, message, created_at) VALUES (?, ?, ?, ?, NOW())");
                $stmt->execute(['donor', 'approve', $id, $notification_message]);
                
                error_log("Database updated successfully");
                
                // Commit only if everything succeeded
                $conn->commit();
                echo json_encode(['success' => true, 'message' => 'Donor approved successfully']);
                exit();
            }
            break;

        case 'hospital':
            error_log("Starting hospital approval process for ID: " . $id);
            
            // 1. First verify hospital exists and is in pending state
            $stmt = $conn->prepare("SELECT name, email, status FROM hospitals WHERE hospital_id = ? AND status = 'pending' FOR UPDATE");
            $stmt->execute([$id]);
            $hospital = $stmt->fetch(PDO::FETCH_ASSOC);

            if (!$hospital) {
                $conn->rollBack();
                echo json_encode(['success' => false, 'message' => 'Hospital not found or not in pending state']);
                exit();
            }

            error_log("Found hospital: " . $hospital['name']);

            // 2. Try to send email first
            $mailer = new Mailer();
            
            try {
                error_log("Testing SMTP connection...");
                if (!$mailer->testConnection()) {
                    throw new Exception("Failed to connect to email server");
                }
                error_log("SMTP connection successful");

                error_log("Sending approval email to: " . $hospital['email']);
                $mailer->sendHospitalApprovalEmail(
                    $hospital['email'],
                    $hospital['name'],
                    $odml_id
                );
                error_log("Email sent successfully");

            } catch (Exception $e) {
                error_log("Email failed: " . $e->getMessage());
                $conn->rollBack();
                echo json_encode(['success' => false, 'message' => 'Email sending failed: ' . $e->getMessage()]);
                exit();
            }

            // 3. If we got here, email was sent successfully. Now update database
            error_log("Proceeding with database update");

            // Double check hospital is still in pending state
            $stmt = $conn->prepare("SELECT status FROM hospitals WHERE hospital_id = ? AND status = 'pending' FOR UPDATE");
            $stmt->execute([$id]);
            if (!$stmt->fetch()) {
                $conn->rollBack();
                echo json_encode(['success' => false, 'message' => 'Hospital status changed during approval']);
                exit();
            }

            // Update hospital status
            $stmt = $conn->prepare("UPDATE hospitals SET status = 'Approved', odml_id = ?, approved_by = ?, approved_at = NOW() WHERE hospital_id = ? AND status = 'pending'");
            if (!$stmt->execute([$odml_id, $_SESSION['admin_id'], $id])) {
                $conn->rollBack();
                echo json_encode(['success' => false, 'message' => 'Failed to update hospital status']);
                exit();
            }

            // Verify the update
            $stmt = $conn->prepare("SELECT status, odml_id FROM hospitals WHERE hospital_id = ?");
            $stmt->execute([$id]);
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if (!$result || $result['status'] !== 'Approved' || $result['odml_id'] !== $odml_id) {
                $conn->rollBack();
                echo json_encode(['success' => false, 'message' => 'Failed to verify hospital update']);
                exit();
            }

            // Create notification
            $notification_message = "Hospital {$hospital['name']} has been approved with ODML ID: {$odml_id}";
            $stmt = $conn->prepare("INSERT INTO notifications (type, action, reference_id, message, created_at) VALUES (?, ?, ?, ?, NOW())");
            $stmt->execute(['hospital', 'approve', $id, $notification_message]);

            // If we got here, everything succeeded
            $conn->commit();
            error_log("Hospital approval completed successfully");
            echo json_encode(['success' => true, 'message' => 'Hospital approved successfully']);
            exit();

        case 'recipient':
            // 1. First verify recipient exists and is in pending state
            $stmt = $conn->prepare("SELECT name, email, status FROM recipients WHERE recipient_id = ? AND status = 'pending' FOR UPDATE");
            $stmt->execute([$id]);
            $recipient = $stmt->fetch(PDO::FETCH_ASSOC);

            if (!$recipient) {
                throw new Exception("Recipient not found or not in pending state");
            }

            // 2. Try to send email first
            $mailer = new Mailer();
            try {
                error_log("Starting email send process for recipient: " . $recipient['name']);
                
                // Test SMTP connection first
                if (!$mailer->testConnection()) {
                    throw new Exception("Failed to connect to email server");
                }
                
                // Try to send the email
                $mailer->sendRecipientApprovalEmail(
                    $recipient['email'],
                    $recipient['name'],
                    $odml_id
                );
                
                // If we got here, email was sent successfully
                $emailSent = true;
                error_log("Email sent successfully to: " . $recipient['email']);
                
            } catch (Exception $e) {
                error_log("Email sending failed: " . $e->getMessage());
                $conn->rollBack(); // Roll back immediately if email fails
                echo json_encode(['success' => false, 'message' => 'Email sending failed: ' . $e->getMessage()]);
                exit();
            }

            // 3. Only if email was sent successfully, update database
            if ($emailSent) {
                error_log("Email sent successfully, proceeding with database update");
                
                // Double check recipient is still in pending state
                $stmt = $conn->prepare("SELECT status FROM recipients WHERE recipient_id = ? AND status = 'pending' FOR UPDATE");
                $stmt->execute([$id]);
                if (!$stmt->fetch()) {
                    throw new Exception("Recipient status changed during approval process");
                }
                
                // Update recipient status
                $stmt = $conn->prepare("UPDATE recipients SET status = 'Approved', odml_id = ?, approved_by = ?, approved_at = NOW() WHERE recipient_id = ? AND status = 'pending'");
                if (!$stmt->execute([$odml_id, $_SESSION['admin_id'], $id])) {
                    throw new Exception("Failed to update recipient status");
                }

                // Verify the update was successful
                $stmt = $conn->prepare("SELECT status FROM recipients WHERE recipient_id = ? AND status = 'Approved'");
                $stmt->execute([$id]);
                if (!$stmt->fetch()) {
                    throw new Exception("Failed to verify recipient status update");
                }

                // Create notification
                $notification_message = "Recipient {$recipient['name']} has been approved with ODML ID: {$odml_id}";
                $stmt = $conn->prepare("INSERT INTO notifications (type, action, reference_id, message, created_at) VALUES (?, ?, ?, ?, NOW())");
                $stmt->execute(['recipient', 'approve', $id, $notification_message]);
                
                error_log("Database updated successfully");
                
                // Commit only if everything succeeded
                $conn->commit();
                echo json_encode(['success' => true, 'message' => 'Recipient approved successfully']);
                exit();
            }
            break;

        default:
            throw new Exception("Invalid type specified");
    }

} catch (Exception $e) {
    // Roll back the transaction on any error
    if ($conn && $conn->inTransaction()) {
        $conn->rollBack();
        error_log("Transaction rolled back due to error: " . $e->getMessage());
    }
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
    exit();
}

// If we get here without success, roll back
if ($conn && $conn->inTransaction()) {
    $conn->rollBack();
}
echo json_encode(['success' => false, 'message' => 'Operation failed']);
?>
