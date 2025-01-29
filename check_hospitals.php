<?php
require_once 'backend/php/connection.php';
require_once 'backend/php/queries.php';

$hospitals = getPendingHospitals($conn);

echo "Pending Hospitals:\n";
print_r($hospitals);

// Also check the table structure
try {
    $stmt = $conn->query("DESCRIBE hospitals");
    echo "\nTable Structure:\n";
    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        print_r($row);
    }
} catch (PDOException $e) {
    echo "Error: " . $e->getMessage();
}
?>
