<?php
require_once 'connection.php';

// Get dashboard statistics
function getDashboardStats($conn, $tables = ['hospitals', 'donor', 'recipient_registration', 'organ_matches']) {
    $stats = [
        'total_hospitals' => 0,
        'total_donors' => 0,
        'total_recipients' => 0,
        'pending_hospitals' => 0,
        'successful_matches' => 0,
        'pending_matches' => 0,
        'urgent_recipients' => 0,
        'approved_hospitals' => 0
    ];
    
    try {
        // Check if tables exist before querying
        $tables = $conn->query("SHOW TABLES")->fetchAll(PDO::FETCH_COLUMN);
        
        if (in_array('hospitals', $tables)) {
            // Get total hospitals (approved only)
            $stmt = $conn->query("SELECT COUNT(*) FROM hospitals WHERE status = 'approved'");
            $stats['total_hospitals'] = $stmt->fetchColumn();
            
            // Get pending hospitals count
            $stmt = $conn->query("SELECT COUNT(*) FROM hospitals WHERE status = 'Pending'");
            $stats['pending_hospitals'] = $stmt->fetchColumn();
            
            // Get approved hospitals
            $stmt = $conn->query("SELECT COUNT(*) FROM hospitals WHERE status = 'approved'");
            $stats['approved_hospitals'] = $stmt->fetchColumn();
        }
        
        if (in_array('donor', $tables)) {
            // Get total donors (approved only)
            $stmt = $conn->query("SELECT COUNT(*) FROM donor WHERE status = 'approved'");
            $stats['total_donors'] = $stmt->fetchColumn();
        }
        
        if (in_array('recipient_registration', $tables)) {
            // Get total recipients (accepted only)
            $stmt = $conn->query("SELECT COUNT(*) FROM recipient_registration WHERE request_status = 'accepted'");
            $stats['total_recipients'] = $stmt->fetchColumn();
            
            // Get urgent recipients count (only from accepted recipients)
            $stmt = $conn->query("SELECT COUNT(*) FROM recipient_registration WHERE urgency_level = 'High' AND request_status = 'accepted'");
            $stats['urgent_recipients'] = $stmt->fetchColumn();
        }
        
        if (in_array('organ_matches', $tables)) {
            // Get successful matches
            $stmt = $conn->query("SELECT COUNT(*) FROM organ_matches WHERE status = 'Confirmed'");
            $stats['successful_matches'] = $stmt->fetchColumn();
            
            // Get pending matches
            $stmt = $conn->query("SELECT COUNT(*) FROM organ_matches WHERE status = 'Pending'");
            $stats['pending_matches'] = $stmt->fetchColumn();
        }
        
        return $stats;
    } catch (PDOException $e) {
        error_log("Error getting dashboard stats: " . $e->getMessage());
        return $stats; // Return default values if there's an error
    }
}

// Get pending hospitals
function getPendingHospitals($conn) {
    try {
        $stmt = $conn->prepare("
            SELECT 
                hospital_id, 
                name as hospital_name, 
                email,
                phone,
                registration_date,
                odml_id,
                status,
                created_at,
                updated_at
            FROM hospitals 
            WHERE status = 'pending'
            ORDER BY created_at DESC
        ");
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
        error_log("Error getting pending hospitals: " . $e->getMessage());
        return [];
    }
}

// Get pending donors
function getPendingDonors($conn) {
    try {
        $stmt = $conn->prepare("
            SELECT 
                donor_id,
                name,
                email,
                blood_group as blood_type,
                organs_to_donate as organ_type,
                DATE_FORMAT(created_at, '%Y-%m-%d') as registration_date,
                status
            FROM 
                donor 
            WHERE 
                status = 'pending'
            ORDER BY 
                created_at DESC
        ");
        $stmt->execute();
        $result = $stmt->fetchAll(PDO::FETCH_ASSOC);
        error_log("Pending Donors Query Result: " . json_encode($result));
        return $result;
    } catch (PDOException $e) {
        error_log("Error getting pending donors: " . $e->getMessage());
        return [];
    }
}

// Get pending recipients
function getPendingRecipients($conn) {
    try {
        $stmt = $conn->prepare("
            SELECT 
                id,
                full_name as name,
                email,
                blood_type,
                organ_required as needed_organ,
                urgency_level as urgency,
                request_status
            FROM 
                recipient_registration 
            WHERE 
                request_status = 'pending'
            ORDER BY 
                CASE 
                    WHEN urgency_level = 'High' THEN 1
                    WHEN urgency_level = 'Medium' THEN 2
                    ELSE 3
                END,
                id DESC
        ");
        $stmt->execute();
        $result = $stmt->fetchAll(PDO::FETCH_ASSOC);
        error_log("Pending Recipients Query Result: " . json_encode($result));
        return $result;
    } catch (PDOException $e) {
        error_log("Error getting pending recipients: " . $e->getMessage());
        return [];
    }
}

// Get urgent recipients with details
function getUrgentRecipients($conn) {
    try {
        $stmt = $conn->prepare("
            SELECT 
                id,
                full_name as name,
                email,
                blood_type,
                organ_required,
                urgency_level,
                request_status,
                ODML_ID as odml_id
            FROM 
                recipient_registration
            WHERE 
                urgency_level = 'High'
                AND request_status = 'accepted'
            ORDER BY 
                id DESC
        ");
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
        error_log("Error getting urgent recipients: " . $e->getMessage());
        return [];
    }
}

// Update hospital status
function updateHospitalStatus($conn, $hospital_id, $status) {
    try {
        // Convert status to proper case
        $status = ucfirst(strtolower($status)); // This will convert 'approved' to 'Approved', 'rejected' to 'Rejected'
        
        $stmt = $conn->prepare("UPDATE hospitals SET status = ? WHERE hospital_id = ?");
        $result = $stmt->execute([$status, $hospital_id]);
        
        if ($result) {
            // Add notification
            $message = "Hospital #$hospital_id has been " . strtolower($status);
            addSystemNotification($conn, 'hospital_status', $message);
        }
        
        return $result;
    } catch (PDOException $e) {
        error_log("Error updating hospital status: " . $e->getMessage());
        return false;
    }
}

// Get all donors with filters
function getDonors($conn, $filters = []) {
    $query = "SELECT * FROM donor WHERE 1=1";
    $params = [];
    
    if (!empty($filters['blood_type'])) {
        $query .= " AND blood_type = :blood_type";
        $params[':blood_type'] = $filters['blood_type'];
    }
    
    if (!empty($filters['organ_type'])) {
        $query .= " AND organ_type = :organ_type";
        $params[':organ_type'] = $filters['organ_type'];
    }
    
    $stmt = $conn->prepare($query);
    $stmt->execute($params);
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

// Match donors with recipients
function findPotentialMatches($conn, $donor_id) {
    $stmt = $conn->prepare("
        SELECT r.* FROM recipient_registration r
        JOIN donor d ON d.blood_type = r.blood_type
        WHERE d.donor_id = :donor_id
        AND d.organ_type = r.organ_required
        AND r.request_status = 'waiting'
        ORDER BY r.urgency_level DESC, r.registration_date ASC
    ");
    $stmt->execute(['donor_id' => $donor_id]);
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

// Add notification
function addNotification($conn, $type, $message, $user_id = null) {
    $stmt = $conn->prepare("INSERT INTO notifications (type, message, user_id) VALUES (?, ?, ?)");
    return $stmt->execute([$type, $message, $user_id]);
}

// Get admin notifications
function getAdminNotifications($conn, $limit = 10) {
    $stmt = $conn->prepare("
        SELECT 
            notification_id,
            type,
            action,
            entity_id,
            message,
            is_read,
            created_at,
            link_url,
            DATE_FORMAT(created_at, '%Y-%m-%d %h:%i %p') as formatted_time
        FROM notifications 
        ORDER BY created_at DESC 
        LIMIT :limit
    ");
    $stmt->bindParam(':limit', $limit, PDO::PARAM_INT);
    $stmt->execute();
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

// Function to add system notification
function addSystemNotification($conn, $type, $message) {
    // Check if notifications table exists
    $stmt = $conn->prepare("
        SELECT COUNT(*) 
        FROM information_schema.tables 
        WHERE table_schema = DATABASE() 
        AND table_name = 'notifications'
    ");
    $stmt->execute();
    $exists = $stmt->fetchColumn();

    if (!$exists) {
        // Silently return if table doesn't exist
        return;
    }

    try {
        $stmt = $conn->prepare("
            INSERT INTO notifications (type, message, created_at, is_read) 
            VALUES (:type, :message, NOW(), 0)
        ");
        $stmt->bindParam(':type', $type);
        $stmt->bindParam(':message', $message);
        return $stmt->execute();
    } catch (PDOException $e) {
        // Log error but don't throw exception
        error_log("Error adding notification: " . $e->getMessage());
        return false;
    }
}

// Update donor status
function updateDonorStatus($conn, $donor_id, $status) {
    try {
        // First get the donor's email
        $stmt = $conn->prepare("SELECT email FROM donor WHERE donor_id = ?");
        $stmt->execute([$donor_id]);
        $donor = $stmt->fetch(PDO::FETCH_ASSOC);
        $donor_email = $donor ? $donor['email'] : 'Unknown';

        // Update the status
        $stmt = $conn->prepare("UPDATE donor SET status = ? WHERE donor_id = ?");
        $result = $stmt->execute([$status, $donor_id]);
        
        if ($result) {
            addSystemNotification($conn, 'donor_status', "Donor ($donor_email) status updated to $status");
            return ['success' => true];
        }
        return ['success' => false, 'message' => 'Failed to update status'];
    } catch (PDOException $e) {
        error_log("Error updating donor status: " . $e->getMessage());
        return ['success' => false, 'message' => 'Database error'];
    }
}

// Update recipient status
function updateRecipientStatus($conn, $recipient_id, $status) {
    try {
        $stmt = $conn->prepare("UPDATE recipient_registration SET request_status = ?, updated_at = NOW() WHERE id = ?");
        return $stmt->execute([$status, $recipient_id]);
    } catch (PDOException $e) {
        error_log("Error updating recipient status: " . $e->getMessage());
        return false;
    }
}

// Analytics Functions
function getMonthlyStats($conn) {
    $stats = [];
    
    // Get average match time (days since match was made)
    $stmt = $conn->query("
        SELECT COALESCE(AVG(TIMESTAMPDIFF(DAY, match_date, CURRENT_TIMESTAMP)), 0) as avg_match_time
        FROM made_matches_by_hospitals
    ");
    $result = $stmt->fetch(PDO::FETCH_ASSOC);
    $stats['avg_match_time'] = round($result['avg_match_time']);

    // Get total matches and calculate success rate (assuming all matches in the system are successful)
    $stmt = $conn->query("
        SELECT COUNT(*) as total_matches
        FROM made_matches_by_hospitals
    ");
    $result = $stmt->fetch(PDO::FETCH_ASSOC);
    $stats['success_rate'] = 100; // All matches in the system are considered successful

    // Get active donors
    $stmt = $conn->query("
        SELECT COUNT(*) as active_donors
        FROM donor
        WHERE status = 'Approved'
    ");
    $result = $stmt->fetch(PDO::FETCH_ASSOC);
    $stats['active_donors'] = $result['active_donors'];

    // Get urgent cases
    $stmt = $conn->query("
        SELECT COUNT(*) as urgent_cases
        FROM recipient_registration
        WHERE request_status = 'accepted'
        AND urgency_level = 'High'
    ");
    $result = $stmt->fetch(PDO::FETCH_ASSOC);
    $stats['urgent_cases'] = $result['urgent_cases'];

    // Get monthly registrations
    $stmt = $conn->query("
        SELECT DATE_FORMAT(created_at, '%Y-%m') as month,
        COUNT(*) as count
        FROM hospitals
        WHERE created_at >= DATE_SUB(NOW(), INTERVAL 12 MONTH)
        GROUP BY month
        ORDER BY month
    ");
    $stats['registrations'] = $stmt->fetchAll(PDO::FETCH_KEY_PAIR);
    
    // Get monthly matches
    $stmt = $conn->query("
        SELECT DATE_FORMAT(match_date, '%Y-%m') as month,
        COUNT(*) as count
        FROM made_matches_by_hospitals
        WHERE match_date >= DATE_SUB(NOW(), INTERVAL 12 MONTH)
        GROUP BY month
        ORDER BY month
    ");
    $stats['matches'] = $stmt->fetchAll(PDO::FETCH_KEY_PAIR);
    
    return $stats;
}

function getOrganTypeStats($conn) {
    $stmt = $conn->query("
        SELECT organs_to_donate as organ_type, COUNT(*) as count
        FROM donor
        WHERE status = 'Approved'
        GROUP BY organs_to_donate
    ");
    return $stmt->fetchAll(PDO::FETCH_KEY_PAIR);
}

function getBloodTypeStats($conn) {
    $stats = [];
    
    // Get donor blood type stats
    $stmt = $conn->query("
        SELECT blood_group as blood_type, COUNT(*) as count
        FROM donor
        WHERE status = 'Approved'
        GROUP BY blood_group
    ");
    $stats['donors'] = $stmt->fetchAll(PDO::FETCH_KEY_PAIR);
    
    // Get recipient blood type stats
    $stmt = $conn->query("
        SELECT blood_type, COUNT(*) as count
        FROM recipient_registration
        WHERE request_status = 'accepted'
        GROUP BY blood_type
    ");
    $stats['recipients'] = $stmt->fetchAll(PDO::FETCH_KEY_PAIR);
    
    return $stats;
}

function getSuccessfulMatches($conn) {
    $stmt = $conn->query("
        SELECT 
            COUNT(*) as total,
            COALESCE(AVG(TIMESTAMPDIFF(DAY, match_date, CURRENT_TIMESTAMP)), 0) as avg_days
        FROM made_matches_by_hospitals
    ");
    return $stmt->fetch(PDO::FETCH_ASSOC);
}

function getRegionalStats($conn) {
    $stmt = $conn->query("
        SELECT 
            h.region,
            COUNT(DISTINCT h.hospital_id) as hospitals,
            COUNT(DISTINCT hda.donor_id) as donors,
            COUNT(DISTINCT hra.recipient_id) as recipients
        FROM hospitals h
        LEFT JOIN hospital_donor_approvals hda ON hda.hospital_id = h.hospital_id AND hda.status = 'Approved'
        LEFT JOIN hospital_recipient_approvals hra ON hra.hospital_id = h.hospital_id AND hra.status = 'approved'
        GROUP BY h.region
    ");
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

// Function to update hospital ODML ID
function updateHospitalODMLID($conn, $hospital_id, $odml_id) {
    try {
        $stmt = $conn->prepare("UPDATE hospitals SET odml_id = ? WHERE hospital_id = ?");
        $result = $stmt->execute([$odml_id, $hospital_id]);
        return $result;
    } catch (PDOException $e) {
        error_log("Error updating hospital ODML ID: " . $e->getMessage());
        return false;
    }
}

// Function to update donor ODML ID
function updateDonorODMLID($conn, $donor_id, $odml_id) {
    try {
        $stmt = $conn->prepare("UPDATE donor SET odml_id = ? WHERE donor_id = ?");
        return $stmt->execute([$odml_id, $donor_id]);
    } catch (PDOException $e) {
        error_log("Error updating donor ODML ID: " . $e->getMessage());
        return false;
    }
}

// Function to update recipient ODML ID
function updateRecipientODMLID($conn, $recipient_id, $odml_id) {
    try {
        $stmt = $conn->prepare("UPDATE recipient_registration SET odml_id = ?, request_status = 'accepted', updated_at = NOW() WHERE id = ?");
        return $stmt->execute([$odml_id, $recipient_id]);
    } catch (PDOException $e) {
        error_log("Error updating recipient ODML ID: " . $e->getMessage());
        return false;
    }
}

// Function to create notification
function createNotification($conn, $type, $action, $entity_id, $message = '') {
    try {
        // If no custom message provided, create default message
        if (empty($message)) {
            $message = ucfirst($type);
            switch ($action) {
                case 'registered':
                    $message .= " has registered";
                    break;
                case 'accepted':
                    $message .= " has been accepted";
                    break;
                case 'rejected':
                    $message .= " has been rejected";
                    break;
            }
        }

        // Generate link URL based on type
        $link_url = '';
        switch ($type) {
            case 'hospital':
                $link_url = "view_hospital.php?id=" . $entity_id;
                break;
            case 'donor':
                $link_url = "view_donor.php?id=" . $entity_id;
                break;
            case 'recipient':
                $link_url = "view_recipient_details.php?id=" . $entity_id;
                break;
        }

        $stmt = $conn->prepare("
            INSERT INTO notifications (type, action, entity_id, message, link_url)
            VALUES (?, ?, ?, ?, ?)
        ");
        
        return $stmt->execute([$type, $action, $entity_id, $message, $link_url]);
    } catch (Exception $e) {
        error_log("Error creating notification: " . $e->getMessage());
        return false;
    }
}

function getAnalyticsDonorStats($conn) {
    $stats = [];
    
    // Get approved donors count
    $stmt = $conn->query("SELECT COUNT(*) as count FROM donor WHERE status = 'Approved'");
    $result = $stmt->fetch(PDO::FETCH_ASSOC);
    $stats['approved'] = $result['count'];

    // Get rejected donors count
    $stmt = $conn->query("SELECT COUNT(*) as count FROM donor WHERE status = 'Rejected'");
    $result = $stmt->fetch(PDO::FETCH_ASSOC);
    $stats['rejected'] = $result['count'];

    // Get pending donors count
    $stmt = $conn->query("SELECT COUNT(*) as count FROM donor WHERE status = 'Pending'");
    $result = $stmt->fetch(PDO::FETCH_ASSOC);
    $stats['pending'] = $result['count'];

    return $stats;
}

function getAnalyticsRecipientStats($conn) {
    $stats = [];
    
    // Get approved recipients count
    $stmt = $conn->query("SELECT COUNT(*) as count FROM recipient_registration WHERE request_status = 'accepted'");
    $result = $stmt->fetch(PDO::FETCH_ASSOC);
    $stats['approved'] = $result['count'];

    // Get rejected recipients count
    $stmt = $conn->query("SELECT COUNT(*) as count FROM recipient_registration WHERE request_status = 'rejected'");
    $result = $stmt->fetch(PDO::FETCH_ASSOC);
    $stats['rejected'] = $result['count'];

    // Get pending recipients count
    $stmt = $conn->query("SELECT COUNT(*) as count FROM recipient_registration WHERE request_status = 'pending'");
    $result = $stmt->fetch(PDO::FETCH_ASSOC);
    $stats['pending'] = $result['count'];

    return $stats;
}

function getAnalyticsHospitalStats($conn) {
    $stats = [];
    
    // Get approved hospitals count
    $stmt = $conn->query("SELECT COUNT(*) as count FROM hospitals WHERE status = 'Approved'");
    $result = $stmt->fetch(PDO::FETCH_ASSOC);
    $stats['approved'] = $result['count'];

    // Get rejected hospitals count
    $stmt = $conn->query("SELECT COUNT(*) as count FROM hospitals WHERE status = 'Rejected'");
    $result = $stmt->fetch(PDO::FETCH_ASSOC);
    $stats['rejected'] = $result['count'];

    // Get pending hospitals count
    $stmt = $conn->query("SELECT COUNT(*) as count FROM hospitals WHERE status = 'Pending'");
    $result = $stmt->fetch(PDO::FETCH_ASSOC);
    $stats['pending'] = $result['count'];

    return $stats;
}

function getAnalyticsOrganMatchStats($conn) {
    $stats = [];
    
    // For now, let's consider all matches in the system as successful
    $stmt = $conn->query("SELECT COUNT(*) as count FROM made_matches_by_hospitals");
    $result = $stmt->fetch(PDO::FETCH_ASSOC);
    $stats['approved'] = $result['count'];

    // For now, set these to 0 as we don't track failed/pending matches
    $stats['rejected'] = 0;
    $stats['pending'] = 0;

    return $stats;
}

function getAnalyticsTotalUsersStats($conn) {
    $stats = [];
    
    // Get total donors count
    $stmt = $conn->query("SELECT COUNT(*) as count FROM donor");
    $result = $stmt->fetch(PDO::FETCH_ASSOC);
    $stats['donors'] = $result['count'];

    // Get total recipients count
    $stmt = $conn->query("SELECT COUNT(*) as count FROM recipient_registration");
    $result = $stmt->fetch(PDO::FETCH_ASSOC);
    $stats['recipients'] = $result['count'];

    // Get total hospitals count
    $stmt = $conn->query("SELECT COUNT(*) as count FROM hospitals");
    $result = $stmt->fetch(PDO::FETCH_ASSOC);
    $stats['hospitals'] = $result['count'];

    return $stats;
}

function getAnalyticsRejectionStats($conn) {
    $stats = [];
    
    // Get donor rejections count
    $stmt = $conn->query("SELECT COUNT(*) as count FROM donor WHERE status = 'Rejected'");
    $result = $stmt->fetch(PDO::FETCH_ASSOC);
    $stats['donor_rejections'] = $result['count'];

    // Get recipient rejections count
    $stmt = $conn->query("SELECT COUNT(*) as count FROM recipient_registration WHERE request_status = 'rejected'");
    $result = $stmt->fetch(PDO::FETCH_ASSOC);
    $stats['recipient_rejections'] = $result['count'];

    // Get hospital rejections count
    $stmt = $conn->query("SELECT COUNT(*) as count FROM hospitals WHERE status = 'Rejected'");
    $result = $stmt->fetch(PDO::FETCH_ASSOC);
    $stats['hospital_rejections'] = $result['count'];

    return $stats;
}

?>
