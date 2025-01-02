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
    // Base query structure depends on the filter type
    switch ($filter) {
        case 'name':
        case 'address':
        case 'phone':
            // Search in hospitals table first
            $baseQuery = "
                SELECT DISTINCT 
                    h.hospital_id,
                    h.hospital_name,
                    h.phone as hospital_phone,
                    h.address as hospital_address,
                    GROUP_CONCAT(DISTINCT d.name) as donor_names,
                    GROUP_CONCAT(DISTINCT d.blood_group) as blood_groups,
                    GROUP_CONCAT(DISTINCT ha.organ_type) as organ_types
                FROM hospitals h
                LEFT JOIN hospital_donor_approvals ha ON h.hospital_id = ha.hospital_id
                LEFT JOIN donor d ON ha.donor_id = d.donor_id
                WHERE ha.status = 'Approved'
                AND LOWER(";
            
            // Add specific field to search
            if ($filter === 'name') {
                $baseQuery .= "h.hospital_name) LIKE LOWER(:search)";
            } elseif ($filter === 'address') {
                $baseQuery .= "h.address) LIKE LOWER(:search)";
            } else { // phone
                $baseQuery .= "h.phone) LIKE LOWER(:search)";
            }
            $baseQuery .= " GROUP BY h.hospital_id";
            break;

        case 'organs':
            // Search hospitals by available organ types
            $baseQuery = "
                SELECT DISTINCT 
                    h.hospital_id,
                    h.hospital_name,
                    h.phone as hospital_phone,
                    h.address as hospital_address,
                    GROUP_CONCAT(DISTINCT d.name) as donor_names,
                    GROUP_CONCAT(DISTINCT d.blood_group) as blood_groups,
                    GROUP_CONCAT(DISTINCT ha.organ_type) as organ_types
                FROM hospitals h
                JOIN hospital_donor_approvals ha ON h.hospital_id = ha.hospital_id
                JOIN donor d ON ha.donor_id = d.donor_id
                WHERE ha.status = 'Approved'
                AND LOWER(ha.organ_type) LIKE LOWER(:search)
                GROUP BY h.hospital_id";
            break;

        case 'blood_group':
            // Search hospitals by available blood groups
            $baseQuery = "
                SELECT DISTINCT 
                    h.hospital_id,
                    h.hospital_name,
                    h.phone as hospital_phone,
                    h.address as hospital_address,
                    GROUP_CONCAT(DISTINCT d.name) as donor_names,
                    GROUP_CONCAT(DISTINCT d.blood_group) as blood_groups,
                    GROUP_CONCAT(DISTINCT ha.organ_type) as organ_types
                FROM hospitals h
                JOIN hospital_donor_approvals ha ON h.hospital_id = ha.hospital_id
                JOIN donor d ON ha.donor_id = d.donor_id
                WHERE ha.status = 'Approved'
                AND LOWER(d.blood_group) LIKE LOWER(:search)
                GROUP BY h.hospital_id";
            break;

        default:
            throw new Exception('Invalid filter type');
    }

    // Add order by
    $query = $baseQuery . " ORDER BY h.hospital_name ASC LIMIT 10";
    
    $stmt = $conn->prepare($query);
    $stmt->execute(['search' => "%$searchTerm%"]);
    $results = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Format results
    $formattedResults = array_map(function($row) use ($hospital_id) {
        // Don't show current hospital in results
        if ($row['hospital_id'] == $hospital_id) {
            return null;
        }

        return [
            'hospital_id' => $row['hospital_id'],
            'hospital_name' => htmlspecialchars($row['hospital_name']),
            'hospital_phone' => htmlspecialchars($row['hospital_phone']),
            'hospital_address' => htmlspecialchars($row['hospital_address']),
            'donor_count' => count(array_filter(explode(',', $row['donor_names']))),
            'blood_groups' => array_unique(array_filter(explode(',', $row['blood_groups']))),
            'organ_types' => array_unique(array_filter(explode(',', $row['organ_types'])))
        ];
    }, $results);

    // Remove null entries (current hospital)
    $formattedResults = array_filter($formattedResults);

    echo json_encode([
        'success' => true,
        'results' => array_values($formattedResults) // Reset array keys
    ]);

} catch(Exception $e) {
    error_log("Error in donor search: " . $e->getMessage());
    echo json_encode([
        'success' => false,
        'message' => 'An error occurred while searching'
    ]);
}
?>
