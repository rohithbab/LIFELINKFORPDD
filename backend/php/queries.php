<?php
require_once 'connection.php';

// Get dashboard statistics
function getDashboardStats($conn) {
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
            // Get total hospitals
            $stmt = $conn->query("SELECT COUNT(*) FROM hospitals");
            $stats['total_hospitals'] = $stmt->fetchColumn();
            
            // Get pending hospital approvals
            $stmt = $conn->query("SELECT COUNT(*) FROM hospitals WHERE status = 'pending'");
            $stats['pending_hospitals'] = $stmt->fetchColumn();
            
            // Get approved hospitals
            $stmt = $conn->query("SELECT COUNT(*) FROM hospitals WHERE status = 'approved'");
            $stats['approved_hospitals'] = $stmt->fetchColumn();
        }
        
        if (in_array('donors', $tables)) {
            // Get total donors (active only)
            $stmt = $conn->query("SELECT COUNT(*) FROM donors WHERE status = 'active'");
            $stats['total_donors'] = $stmt->fetchColumn();
        }
        
        if (in_array('recipients', $tables)) {
            // Get total recipients (active only)
            $stmt = $conn->query("SELECT COUNT(*) FROM recipients WHERE status = 'active'");
            $stats['total_recipients'] = $stmt->fetchColumn();
            
            // Get urgent recipients count
            $stmt = $conn->query("SELECT COUNT(*) FROM recipients WHERE urgency_level = 'High' AND status = 'active'");
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

// Get pending hospital approvals
function getPendingHospitals($conn) {
    try {
        $query = "SELECT h.*, 
                  CASE 
                    WHEN h.status = 'pending' AND h.created_at > NOW() - INTERVAL 24 HOUR 
                    THEN 1 ELSE 0 
                  END as is_new 
                  FROM hospitals h 
                  WHERE h.status = 'pending' 
                  ORDER BY h.created_at DESC";
        
        $stmt = $conn->prepare($query);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
        
    } catch (Exception $e) {
        error_log("Error in getPendingHospitals: " . $e->getMessage());
        return array();
    }
}

function updateHospitalStatus($conn, $hospital_id, $status) {
    try {
        $stmt = $conn->prepare("UPDATE hospitals SET status = ?, updated_at = NOW() WHERE hospital_id = ?");
        $stmt->execute([$status, $hospital_id]);
        return true;
    } catch (Exception $e) {
        error_log("Error in updateHospitalStatus: " . $e->getMessage());
        return false;
    }
}

// Get all donors with filters
function getDonors($conn, $filters = []) {
    $query = "SELECT * FROM donors WHERE 1=1";
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

// Get urgent recipients with details
function getUrgentRecipients($conn) {
    try {
        $stmt = $conn->prepare("
            SELECT 
                r.*,
                COALESCE(om.match_count, 0) as potential_matches
            FROM 
                recipients r
                LEFT JOIN (
                    SELECT recipient_id, COUNT(*) as match_count 
                    FROM organ_matches 
                    WHERE status = 'Pending'
                    GROUP BY recipient_id
                ) om ON r.id = om.recipient_id
            WHERE 
                r.urgency_level = 'High'
                AND r.status = 'active'
            ORDER BY 
                r.registration_date DESC
        ");
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
        error_log("Error getting urgent recipients: " . $e->getMessage());
        return [];
    }
}

// Match donors with recipients
function findPotentialMatches($conn, $donor_id) {
    $stmt = $conn->prepare("
        SELECT r.* FROM recipients r
        JOIN donors d ON d.blood_type = r.blood_type
        WHERE d.id = :donor_id
        AND d.organ_type = r.needed_organ
        AND r.status = 'waiting'
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
        SELECT * FROM notifications 
        WHERE user_id IS NULL 
        ORDER BY created_at DESC 
        LIMIT :limit
    ");
    $stmt->bindParam(':limit', $limit, PDO::PARAM_INT);
    $stmt->execute();
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

// Get pending donors
function getPendingDonors($conn) {
    try {
        $stmt = $conn->prepare("
            SELECT 
                d.*,
                COALESCE(om.match_count, 0) as potential_matches,
                DATE_FORMAT(d.registration_date, '%M %d, %Y') as formatted_date
            FROM 
                donors d
                LEFT JOIN (
                    SELECT donor_id, COUNT(*) as match_count 
                    FROM organ_matches 
                    WHERE status = 'Pending'
                    GROUP BY donor_id
                ) om ON d.id = om.donor_id
            WHERE 
                d.status = 'pending'
            ORDER BY 
                d.registration_date DESC
        ");
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
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
                r.*,
                COALESCE(om.match_count, 0) as potential_matches,
                DATE_FORMAT(r.registration_date, '%M %d, %Y') as formatted_date
            FROM 
                recipients r
                LEFT JOIN (
                    SELECT recipient_id, COUNT(*) as match_count 
                    FROM organ_matches 
                    WHERE status = 'Pending'
                    GROUP BY recipient_id
                ) om ON r.id = om.recipient_id
            WHERE 
                r.status = 'pending'
            ORDER BY 
                r.urgency_level DESC,
                r.registration_date DESC
        ");
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
        error_log("Error getting pending recipients: " . $e->getMessage());
        return [];
    }
}

// Update donor status
function updateDonorStatus($conn, $donor_id, $status) {
    try {
        $stmt = $conn->prepare("UPDATE donors SET status = :status WHERE id = :donor_id");
        return $stmt->execute(['status' => $status, 'donor_id' => $donor_id]);
    } catch (PDOException $e) {
        error_log("Error updating donor status: " . $e->getMessage());
        return false;
    }
}

// Update recipient status
function updateRecipientStatus($conn, $recipient_id, $status) {
    try {
        $stmt = $conn->prepare("UPDATE recipients SET status = :status WHERE id = :recipient_id");
        return $stmt->execute(['status' => $status, 'recipient_id' => $recipient_id]);
    } catch (PDOException $e) {
        error_log("Error updating recipient status: " . $e->getMessage());
        return false;
    }
}

// Analytics Functions
function getMonthlyStats($conn) {
    $stats = [
        'registrations' => [],
        'matches' => [],
        'completions' => []
    ];
    
    // Get monthly hospital registrations
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
        FROM organ_matches
        WHERE match_date >= DATE_SUB(NOW(), INTERVAL 12 MONTH)
        GROUP BY month
        ORDER BY month
    ");
    $stats['matches'] = $stmt->fetchAll(PDO::FETCH_KEY_PAIR);
    
    return $stats;
}

function getOrganTypeStats($conn) {
    $stmt = $conn->query("
        SELECT organ_type, COUNT(*) as count
        FROM donors
        GROUP BY organ_type
    ");
    return $stmt->fetchAll(PDO::FETCH_KEY_PAIR);
}

function getBloodTypeStats($conn) {
    $stats = [];
    
    // Get donor blood type stats
    $stmt = $conn->query("
        SELECT blood_type, COUNT(*) as count
        FROM donors
        GROUP BY blood_type
    ");
    $stats['donors'] = $stmt->fetchAll(PDO::FETCH_KEY_PAIR);
    
    return $stats;
}

function getSuccessfulMatches($conn) {
    $stmt = $conn->query("
        SELECT COUNT(*) as total,
        AVG(TIMESTAMPDIFF(DAY, match_date, completion_date)) as avg_days
        FROM organ_matches
        WHERE status = 'completed'
    ");
    return $stmt->fetch(PDO::FETCH_ASSOC);
}

function getRegionalStats($conn) {
    $stmt = $conn->query("
        SELECT 
            h.region,
            COUNT(DISTINCT h.hospital_id) as hospitals,
            COUNT(DISTINCT d.id) as donors,
            COUNT(DISTINCT r.id) as recipients
        FROM hospitals h
        LEFT JOIN donors d ON d.hospital_id = h.hospital_id
        LEFT JOIN recipients r ON r.hospital_id = h.hospital_id
        GROUP BY h.region
    ");
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}
?>
