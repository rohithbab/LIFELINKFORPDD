<?php
require_once 'connection.php';

// Get dashboard statistics
function getDashboardStats($conn) {
    $stats = [];
    
    // Get total hospitals
    $stmt = $conn->query("SELECT COUNT(*) FROM hospitals");
    $stats['total_hospitals'] = $stmt->fetchColumn();
    
    // Get total donors
    $stmt = $conn->query("SELECT COUNT(*) FROM donors");
    $stats['total_donors'] = $stmt->fetchColumn();
    
    // Get total recipients
    $stmt = $conn->query("SELECT COUNT(*) FROM recipients");
    $stats['total_recipients'] = $stmt->fetchColumn();
    
    // Get pending approvals
    $stmt = $conn->query("SELECT COUNT(*) FROM hospitals WHERE status = 'pending'");
    $stats['pending_hospitals'] = $stmt->fetchColumn();
    
    return $stats;
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

// Get urgent recipients
function getUrgentRecipients($conn) {
    $stmt = $conn->query("SELECT * FROM recipients WHERE urgency_level = 'high' ORDER BY registration_date DESC");
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
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
