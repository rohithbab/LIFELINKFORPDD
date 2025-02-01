<?php
require_once '../config/db_connect.php';

try {
    // Read the SQL file
    $sql = file_get_contents(__DIR__ . '/tables/hospital_donor_approvals.sql');
    
    // Execute the SQL
    $conn->exec($sql);
    echo "Successfully created hospital_donor_approvals table with request_date column\n";
    
} catch(PDOException $e) {
    die("Error creating table: " . $e->getMessage());
}
?>
