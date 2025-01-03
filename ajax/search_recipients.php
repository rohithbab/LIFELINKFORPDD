<?php
session_start();
require_once '../config/db_connect.php';

// Check if hospital is logged in
if (!isset($_SESSION['hospital_logged_in']) || !$_SESSION['hospital_logged_in']) {
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit();
}

// Get JSON input
$data = json_decode(file_get_contents('php://input'), true);
$searchTerm = $data['search'] ?? '';
$filter = $data['filter'] ?? 'blood_group';
$hospital_id = $_SESSION['hospital_id'];

if (empty($searchTerm)) {
    echo json_encode(['success' => false, 'message' => 'Search term is required']);
    exit();
}

try {
    // Base query structure depends on the filter type
    if ($filter === 'blood_group') {
        $query = "
            SELECT DISTINCT 
                h.hospital_id,
                h.name as hospital_name,
                h.phone as hospital_phone,
                h.address as hospital_address,
                GROUP_CONCAT(DISTINCT r.blood_group) as blood_groups,
                GROUP_CONCAT(DISTINCT ha.required_organ) as organ_types,
                COUNT(DISTINCT r.recipient_id) as recipient_count
            FROM hospitals h
            JOIN hospital_recipient_approvals ha ON h.hospital_id = ha.hospital_id
            JOIN recipient r ON ha.recipient_id = r.recipient_id
            WHERE ha.status = 'Approved'
            AND h.hospital_id != ?
            AND LOWER(r.blood_group) LIKE LOWER(?)
            GROUP BY h.hospital_id
            ORDER BY h.name ASC";
    } else { // organs
        $query = "
            SELECT DISTINCT 
                h.hospital_id,
                h.name as hospital_name,
                h.phone as hospital_phone,
                h.address as hospital_address,
                GROUP_CONCAT(DISTINCT r.blood_group) as blood_groups,
                GROUP_CONCAT(DISTINCT ha.required_organ) as organ_types,
                COUNT(DISTINCT r.recipient_id) as recipient_count
            FROM hospitals h
            JOIN hospital_recipient_approvals ha ON h.hospital_id = ha.hospital_id
            JOIN recipient r ON ha.recipient_id = r.recipient_id
            WHERE ha.status = 'Approved'
            AND h.hospital_id != ?
            AND LOWER(ha.required_organ) LIKE LOWER(?)
            GROUP BY h.hospital_id
            ORDER BY h.name ASC";
    }
    
    $stmt = $conn->prepare($query);
    $stmt->execute([$hospital_id, "%$searchTerm%"]);
    $results = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Format results
    $formattedResults = array_map(function($row) {
        return [
            'hospital_id' => $row['hospital_id'],
            'hospital_name' => htmlspecialchars($row['hospital_name']),
            'phone' => htmlspecialchars($row['hospital_phone']),
            'address' => htmlspecialchars($row['hospital_address']),
            'recipient_count' => $row['recipient_count'],
            'blood_groups' => array_unique(explode(',', $row['blood_groups'])),
            'organ_types' => array_unique(explode(',', $row['organ_types']))
        ];
    }, $results);

    echo json_encode([
        'success' => true,
        'results' => $formattedResults
    ]);

} catch(Exception $e) {
    error_log("Error in recipient search: " . $e->getMessage());
    echo json_encode([
        'success' => false,
        'message' => 'An error occurred while searching'
    ]);
}
?>
