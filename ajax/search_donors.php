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
$filter = $data['filter'] ?? 'name';
$hospital_id = $_SESSION['hospital_id'];

if (empty($searchTerm)) {
    echo json_encode(['success' => false, 'message' => 'Search term is required']);
    exit();
}

try {
    // Base query
    $baseQuery = "
        SELECT DISTINCT d.donor_id, d.name, d.blood_group, d.organs_to_donate, 
               h.hospital_name, h.hospital_id
        FROM donor d
        JOIN hospital_donor_approvals ha ON d.donor_id = ha.donor_id
        JOIN hospitals h ON ha.hospital_id = h.hospital_id
        WHERE ha.status = 'Approved'
    ";

    // Add filter condition
    switch ($filter) {
        case 'name':
            $whereClause = "AND d.name LIKE :search";
            break;
        case 'address':
            $whereClause = "AND d.address LIKE :search";
            break;
        case 'phone':
            $whereClause = "AND d.phone LIKE :search";
            break;
        case 'organs':
            $whereClause = "AND d.organs_to_donate LIKE :search";
            break;
        case 'blood_group':
            $whereClause = "AND d.blood_group LIKE :search";
            break;
        default:
            $whereClause = "AND d.name LIKE :search";
    }

    $query = $baseQuery . $whereClause . " ORDER BY d.name ASC LIMIT 10";
    $stmt = $conn->prepare($query);
    $stmt->execute(['search' => "%$searchTerm%"]);
    $results = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Format results
    $formattedResults = array_map(function($row) use ($hospital_id) {
        return [
            'donor_id' => $row['donor_id'],
            'name' => htmlspecialchars($row['name']),
            'hospital_name' => $row['hospital_id'] == $hospital_id ? 'Your Hospital' : htmlspecialchars($row['hospital_name']),
            'blood_group' => htmlspecialchars($row['blood_group']),
            'organs_to_donate' => htmlspecialchars($row['organs_to_donate'])
        ];
    }, $results);

    echo json_encode([
        'success' => true,
        'results' => $formattedResults
    ]);

} catch(PDOException $e) {
    error_log("Error in donor search: " . $e->getMessage());
    echo json_encode([
        'success' => false,
        'message' => 'An error occurred while searching'
    ]);
}
?>
