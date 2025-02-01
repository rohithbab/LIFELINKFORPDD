<?php
require_once __DIR__ . '/../config/db_connect.php';

try {
    // Read the SQL file
    $sql = file_get_contents(__DIR__ . '/add_request_date_to_donor_approvals.sql');
    
    // Execute the SQL
    $conn->exec($sql);
    echo "Successfully added request_date column to hospital_donor_approvals table\n";
    
} catch(PDOException $e) {
    die("Error adding column: " . $e->getMessage());
}
?>
