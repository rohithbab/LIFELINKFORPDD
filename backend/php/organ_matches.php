<?php
require_once 'connection.php';

// Create organ_matches table if it doesn't exist
function createOrganMatchesTable($conn) {
    try {
        $sql = file_get_contents(__DIR__ . '/../sql/create_organ_matches.sql');
        $conn->exec($sql);
        return true;
    } catch (PDOException $e) {
        error_log("Error creating organ_matches table: " . $e->getMessage());
        return false;
    }
}

// Add new organ match
function addOrganMatch($conn, $data) {
    try {
        $sql = "INSERT INTO organ_matches (
            donor_name, donor_email, recipient_name, recipient_email,
            hospital_name, hospital_email, organ_type, match_date,
            status, reason_for_match, admin_notes, donor_id_proof_path,
            recipient_medical_records_path, urgency_level
        ) VALUES (
            :donor_name, :donor_email, :recipient_name, :recipient_email,
            :hospital_name, :hospital_email, :organ_type, :match_date,
            :status, :reason_for_match, :admin_notes, :donor_id_proof_path,
            :recipient_medical_records_path, :urgency_level
        )";

        $stmt = $conn->prepare($sql);
        $stmt->execute($data);
        return $conn->lastInsertId();
    } catch (PDOException $e) {
        error_log("Error adding organ match: " . $e->getMessage());
        return false;
    }
}

// Update organ match status
function updateOrganMatchStatus($conn, $match_id, $status, $admin_notes = null) {
    try {
        $sql = "UPDATE organ_matches SET status = :status";
        if ($admin_notes !== null) {
            $sql .= ", admin_notes = :admin_notes";
        }
        $sql .= " WHERE match_id = :match_id";

        $stmt = $conn->prepare($sql);
        $params = ['status' => $status, 'match_id' => $match_id];
        if ($admin_notes !== null) {
            $params['admin_notes'] = $admin_notes;
        }
        return $stmt->execute($params);
    } catch (PDOException $e) {
        error_log("Error updating organ match status: " . $e->getMessage());
        return false;
    }
}

// Get all organ matches with optional filters
function getOrganMatches($conn, $filters = []) {
    try {
        $sql = "SELECT * FROM organ_matches WHERE 1=1";
        $params = [];

        if (isset($filters['status'])) {
            $sql .= " AND status = :status";
            $params['status'] = $filters['status'];
        }

        if (isset($filters['urgency_level'])) {
            $sql .= " AND urgency_level = :urgency_level";
            $params['urgency_level'] = $filters['urgency_level'];
        }

        if (isset($filters['hospital_email'])) {
            $sql .= " AND hospital_email = :hospital_email";
            $params['hospital_email'] = $filters['hospital_email'];
        }

        $sql .= " ORDER BY urgency_level DESC, match_date DESC";

        $stmt = $conn->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
        error_log("Error getting organ matches: " . $e->getMessage());
        return [];
    }
}

// Get organ match statistics
function getOrganMatchStats($conn) {
    try {
        $stats = [];
        
        // Total matches
        $stmt = $conn->query("SELECT COUNT(*) FROM organ_matches");
        $stats['total_matches'] = $stmt->fetchColumn();

        // Matches by status
        $stmt = $conn->query("SELECT status, COUNT(*) as count FROM organ_matches GROUP BY status");
        $stats['status_counts'] = $stmt->fetchAll(PDO::FETCH_KEY_PAIR);

        // Matches by organ type
        $stmt = $conn->query("SELECT organ_type, COUNT(*) as count FROM organ_matches GROUP BY organ_type");
        $stats['organ_type_counts'] = $stmt->fetchAll(PDO::FETCH_KEY_PAIR);

        // Urgent matches (High priority)
        $stmt = $conn->query("SELECT COUNT(*) FROM organ_matches WHERE urgency_level = 'High'");
        $stats['urgent_matches'] = $stmt->fetchColumn();

        // Successful matches (Confirmed status)
        $stmt = $conn->query("SELECT COUNT(*) FROM organ_matches WHERE status = 'Confirmed'");
        $stats['successful_matches'] = $stmt->fetchColumn();

        return $stats;
    } catch (PDOException $e) {
        error_log("Error getting organ match statistics: " . $e->getMessage());
        return [];
    }
}

// Get specific organ match details
function getOrganMatch($conn, $match_id) {
    try {
        $stmt = $conn->prepare("SELECT * FROM organ_matches WHERE match_id = :match_id");
        $stmt->execute(['match_id' => $match_id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
        error_log("Error getting organ match details: " . $e->getMessage());
        return null;
    }
}

// Get recent organ matches from made_matches_by_hospitals table (last 7 days)
function getRecentOrganMatches($conn, $limit = 5) {
    try {
        $sql = "SELECT 
            m.*,
            h.name as match_made_by_hospital_name
        FROM made_matches_by_hospitals m
        LEFT JOIN hospitals h ON m.match_made_by = h.hospital_id
        WHERE m.match_date >= DATE_SUB(CURDATE(), INTERVAL 7 DAY)
        ORDER BY m.match_date DESC
        LIMIT :limit";

        $stmt = $conn->prepare($sql);
        $stmt->bindParam(':limit', $limit, PDO::PARAM_INT);
        $stmt->execute();
        
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
        error_log("Error getting recent organ matches: " . $e->getMessage());
        return [];
    }
}

// Get all organ matches with pagination and search
function getAllOrganMatches($conn, $page = 1, $limit = 10, $search = '', $sortBy = 'match_date', $sortOrder = 'DESC') {
    try {
        // Calculate offset
        $offset = ($page - 1) * $limit;
        
        // Base query
        $sql = "SELECT 
            m.*,
            h.name as match_made_by_hospital_name,
            d.email as donor_email,
            r.email as recipient_email
        FROM made_matches_by_hospitals m
        LEFT JOIN hospitals h ON m.match_made_by = h.hospital_id
        LEFT JOIN donors d ON m.donor_id = d.donor_id
        LEFT JOIN recipient_registration r ON m.recipient_id = r.recipient_id";
        
        // Add search condition if search term is provided
        if (!empty($search)) {
            $sql .= " WHERE 
                m.match_id LIKE :search OR 
                h.name LIKE :search OR 
                m.donor_name LIKE :search OR 
                m.donor_hospital_name LIKE :search OR 
                m.recipient_name LIKE :search OR 
                m.recipient_hospital_name LIKE :search OR 
                m.organ_type LIKE :search OR 
                m.blood_group LIKE :search OR
                d.email LIKE :search OR
                r.email LIKE :search";
        }
        
        // Add sorting
        $sql .= " ORDER BY " . $sortBy . " " . $sortOrder;
        
        // Add pagination
        $sql .= " LIMIT :limit OFFSET :offset";
        
        // Prepare and execute query
        $stmt = $conn->prepare($sql);
        
        // Bind search parameter if exists
        if (!empty($search)) {
            $searchTerm = "%$search%";
            $stmt->bindParam(':search', $searchTerm, PDO::PARAM_STR);
        }
        
        // Bind pagination parameters
        $stmt->bindParam(':limit', $limit, PDO::PARAM_INT);
        $stmt->bindParam(':offset', $offset, PDO::PARAM_INT);
        
        $stmt->execute();
        $matches = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        // Get total count for pagination
        $countSql = "SELECT COUNT(*) FROM made_matches_by_hospitals m";
        if (!empty($search)) {
            $countSql .= " WHERE 
                m.match_id LIKE :search OR 
                m.donor_name LIKE :search OR 
                m.donor_hospital_name LIKE :search OR 
                m.recipient_name LIKE :search OR 
                m.recipient_hospital_name LIKE :search OR 
                m.organ_type LIKE :search OR 
                m.blood_group LIKE :search";
        }
        
        $countStmt = $conn->prepare($countSql);
        if (!empty($search)) {
            $countStmt->bindParam(':search', $searchTerm, PDO::PARAM_STR);
        }
        $countStmt->execute();
        $totalRecords = $countStmt->fetchColumn();
        
        return [
            'matches' => $matches,
            'total' => $totalRecords,
            'pages' => ceil($totalRecords / $limit)
        ];
    } catch (PDOException $e) {
        error_log("Error getting all matches: " . $e->getMessage());
        return [
            'matches' => [],
            'total' => 0,
            'pages' => 0
        ];
    }
}

// Get specific match details
function getMatchDetails($conn, $match_id) {
    try {
        $sql = "SELECT 
            m.*,
            h.name as match_made_by_hospital_name
        FROM made_matches_by_hospitals m
        LEFT JOIN hospitals h ON m.match_made_by = h.hospital_id
        WHERE m.match_id = :match_id";

        $stmt = $conn->prepare($sql);
        $stmt->bindParam(':match_id', $match_id, PDO::PARAM_INT);
        $stmt->execute();
        
        return $stmt->fetch(PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
        error_log("Error in getMatchDetails: " . $e->getMessage());
        return false;
    }
}
?>
