<?php
require_once '../config/db_connect.php';

header('Content-Type: application/json');

$term = isset($_GET['term']) ? $_GET['term'] : '';
$filter = isset($_GET['filter']) ? $_GET['filter'] : 'name';

// Validate filter
$allowedFilters = ['name', 'address', 'phone'];
if (!in_array($filter, $allowedFilters)) {
    $filter = 'name';
}

try {
    $searchTerm = "%$term%";
    
    // Build query based on filter
    $sql = "SELECT hospital_id, name, email, address, phone, region, license_number 
            FROM hospitals 
            WHERE status = 'active' AND ";
    
    switch ($filter) {
        case 'address':
            $sql .= "address LIKE :term";
            break;
        case 'phone':
            $sql .= "phone LIKE :term";
            break;
        default:
            $sql .= "name LIKE :term";
    }
    
    $sql .= " ORDER BY name ASC LIMIT 50";
    
    $stmt = $conn->prepare($sql);
    $stmt->bindParam(':term', $searchTerm);
    $stmt->execute();
    
    $hospitals = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo json_encode($hospitals);
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(['error' => 'Database error']);
}
?>
