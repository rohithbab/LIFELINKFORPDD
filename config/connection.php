<?php
// Database configuration
$db_host = 'localhost';
$db_user = 'root';
$db_pass = '';
$db_name = 'lifelink_db';

// Try to establish connection using mysqli first
try {
    if (class_exists('mysqli')) {
        $conn = new mysqli($db_host, $db_user, $db_pass, $db_name);
        
        // Check connection
        if ($conn->connect_error) {
            throw new Exception("Connection failed: " . $conn->connect_error);
        }
        
        // Set charset to utf8mb4
        $conn->set_charset("utf8mb4");
    } else {
        // If mysqli is not available, try PDO
        $dsn = "mysql:host=$db_host;dbname=$db_name;charset=utf8mb4";
        $conn = new PDO($dsn, $db_user, $db_pass);
        $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    }
} catch (Exception $e) {
    die("Database connection failed: " . $e->getMessage());
}
