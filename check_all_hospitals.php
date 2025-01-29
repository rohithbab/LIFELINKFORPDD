<?php
require_once 'backend/php/connection.php';

try {
    $stmt = $conn->query("SELECT hospital_id, name, email, status FROM hospitals");
    echo "All Hospitals:\n";
    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        print_r($row);
    }
} catch (PDOException $e) {
    echo "Error: " . $e->getMessage();
}
?>
