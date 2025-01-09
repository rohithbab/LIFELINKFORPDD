<?php
require_once '../../config/db_connect.php';

try {
    $conn->beginTransaction();

    // Get all hospitals
    $stmt = $conn->query("SELECT hospital_id FROM hospitals");
    $hospitals = $stmt->fetchAll(PDO::FETCH_ASSOC);

    foreach ($hospitals as $hospital) {
        $hospital_id = $hospital['hospital_id'];

        // Get existing donors for this hospital
        $stmt = $conn->prepare("
            SELECT d.*, ha.organ_type
            FROM donors d
            JOIN hospital_donor_approvals ha ON ha.donor_id = d.donor_id
            WHERE ha.hospital_id = ?
            AND NOT EXISTS (
                SELECT 1 FROM hospital_notifications n 
                WHERE n.hospital_id = ? 
                AND n.type = 'donor_registration' 
                AND n.related_id = d.donor_id
            )
        ");
        $stmt->execute([$hospital_id, $hospital_id]);
        $donors = $stmt->fetchAll(PDO::FETCH_ASSOC);

        // Create notifications for donors
        foreach ($donors as $donor) {
            $message = "New donor {$donor['name']} ({$donor['blood_group']}) has registered for {$donor['organ_type']} donation";
            $stmt = $conn->prepare("
                INSERT INTO hospital_notifications 
                (hospital_id, type, message, is_read, created_at, link_url, related_id)
                VALUES 
                (?, 'donor_registration', ?, 0, ?, ?, ?)
            ");
            $stmt->execute([
                $hospital_id,
                $message,
                $donor['registration_date'],
                "../pages/hospital/view_donor.php?id=" . $donor['donor_id'],
                $donor['donor_id']
            ]);
        }

        // Get existing recipients for this hospital
        $stmt = $conn->prepare("
            SELECT r.*, ha.organ_type
            FROM recipients r
            JOIN hospital_recipient_approvals ha ON ha.recipient_id = r.recipient_id
            WHERE ha.hospital_id = ?
            AND NOT EXISTS (
                SELECT 1 FROM hospital_notifications n 
                WHERE n.hospital_id = ? 
                AND n.type = 'recipient_registration' 
                AND n.related_id = r.recipient_id
            )
        ");
        $stmt->execute([$hospital_id, $hospital_id]);
        $recipients = $stmt->fetchAll(PDO::FETCH_ASSOC);

        // Create notifications for recipients
        foreach ($recipients as $recipient) {
            $message = "New recipient {$recipient['name']} ({$recipient['blood_group']}) has registered needing {$recipient['organ_type']}";
            $stmt = $conn->prepare("
                INSERT INTO hospital_notifications 
                (hospital_id, type, message, is_read, created_at, link_url, related_id)
                VALUES 
                (?, 'recipient_registration', ?, 0, ?, ?, ?)
            ");
            $stmt->execute([
                $hospital_id,
                $message,
                $recipient['registration_date'],
                "../pages/hospital/view_recipient.php?id=" . $recipient['recipient_id'],
                $recipient['recipient_id']
            ]);
        }
    }

    $conn->commit();
    echo "Successfully created notifications for existing registrations";
} catch (PDOException $e) {
    $conn->rollBack();
    error_log("Error creating existing notifications: " . $e->getMessage());
    echo "Error: " . $e->getMessage();
}
?>
