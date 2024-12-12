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
    $stmt = $conn->query("SELECT * FROM hospitals WHERE status = 'pending' ORDER BY registration_date DESC");
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
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
    $stmt->execute([':donor_id' => $donor_id]);
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

// Add notification
function addNotification($conn, $type, $message, $user_id = null) {
    $stmt = $conn->prepare("
        INSERT INTO notifications (type, message, user_id, created_at)
        VALUES (:type, :message, :user_id, NOW())
    ");
    return $stmt->execute([
        ':type' => $type,
        ':message' => $message,
        ':user_id' => $user_id
    ]);
}

// Get admin notifications
function getAdminNotifications($conn, $limit = 10) {
    $stmt = $conn->prepare("
        SELECT * FROM notifications
        WHERE user_id IS NULL
        ORDER BY created_at DESC
        LIMIT :limit
    ");
    $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
    $stmt->execute();
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

// Analytics Functions
function getMonthlyStats($conn) {
    $stats = [];
    
    // Get average match time
    $stmt = $conn->query("
        SELECT AVG(DATEDIFF(match_date, registration_date)) as avg_time
        FROM organ_matches
        WHERE status = 'completed'
        AND match_date >= DATE_SUB(NOW(), INTERVAL 30 DAY)
    ");
    $stats['avg_match_time'] = round($stmt->fetchColumn(), 1);
    
    // Get success rate
    $stmt = $conn->query("
        SELECT 
            (COUNT(CASE WHEN status = 'completed' THEN 1 END) * 100.0 / COUNT(*)) as success_rate
        FROM organ_matches
        WHERE created_at >= DATE_SUB(NOW(), INTERVAL 30 DAY)
    ");
    $stats['success_rate'] = round($stmt->fetchColumn(), 1);
    
    // Get active donors
    $stmt = $conn->query("
        SELECT COUNT(*) FROM donors
        WHERE status = 'active'
    ");
    $stats['active_donors'] = $stmt->fetchColumn();
    
    // Get urgent cases
    $stmt = $conn->query("
        SELECT COUNT(*) FROM recipients
        WHERE urgency_level = 'high'
        AND status = 'waiting'
    ");
    $stats['urgent_cases'] = $stmt->fetchColumn();
    
    return $stats;
}

function getOrganTypeStats($conn) {
    $stmt = $conn->query("
        SELECT organ_type, COUNT(*) as count
        FROM donors
        WHERE status = 'active'
        GROUP BY organ_type
        ORDER BY count DESC
    ");
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

function getBloodTypeStats($conn) {
    $stmt = $conn->query("
        SELECT blood_type, 
               COUNT(CASE WHEN type = 'donor' THEN 1 END) as donors,
               COUNT(CASE WHEN type = 'recipient' THEN 1 END) as recipients
        FROM (
            SELECT blood_type, 'donor' as type FROM donors WHERE status = 'active'
            UNION ALL
            SELECT blood_type, 'recipient' as type FROM recipients WHERE status = 'waiting'
        ) combined
        GROUP BY blood_type
    ");
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

function getSuccessfulMatches($conn) {
    $stmt = $conn->query("
        SELECT 
            DATE_FORMAT(match_date, '%Y-%m') as month,
            COUNT(*) as matches,
            COUNT(CASE WHEN status = 'completed' THEN 1 END) as successful
        FROM organ_matches
        WHERE match_date >= DATE_SUB(NOW(), INTERVAL 12 MONTH)
        GROUP BY month
        ORDER BY month
    ");
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

function getRegionalStats($conn) {
    $stmt = $conn->query("
        SELECT 
            h.region,
            COUNT(DISTINCT d.id) as donors,
            COUNT(DISTINCT r.id) as recipients,
            COUNT(DISTINCT CASE WHEN om.status = 'completed' THEN om.id END) as successful_matches
        FROM hospitals h
        LEFT JOIN donors d ON d.hospital_id = h.id
        LEFT JOIN recipients r ON r.hospital_id = h.id
        LEFT JOIN organ_matches om ON om.hospital_id = h.id
        WHERE h.status = 'approved'
        GROUP BY h.region
    ");
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

?>
