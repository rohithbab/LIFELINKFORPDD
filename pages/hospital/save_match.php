<?php
session_start();
require_once '../../config/db_connect.php';

// Check if hospital is logged in
if (!isset($_SESSION['hospital_id'])) {
    echo json_encode(['success' => false, 'message' => 'Not authorized']);
    exit();
}

// Get the logged in hospital ID (this will be the match_made_by)
$match_made_by = $_SESSION['hospital_id'];

try {
    // Prepare the insert statement
    $query = "INSERT INTO made_matches_by_hospitals (
        match_made_by,
        donor_id,
        donor_name,
        donor_hospital_id,
        donor_hospital_name,
        recipient_id,
        recipient_name,
        recipient_hospital_id,
        recipient_hospital_name,
        organ_type,
        blood_group
    ) VALUES (
        :match_made_by,
        :donor_id,
        :donor_name,
        :donor_hospital_id,
        :donor_hospital_name,
        :recipient_id,
        :recipient_name,
        :recipient_hospital_id,
        :recipient_hospital_name,
        :organ_type,
        :blood_group
    )";

    $stmt = $conn->prepare($query);

    // Bind parameters
    $stmt->bindParam(':match_made_by', $match_made_by);
    $stmt->bindParam(':donor_id', $_POST['donor_id']);
    $stmt->bindParam(':donor_name', $_POST['donor_name']);
    $stmt->bindParam(':donor_hospital_id', $_POST['donor_hospital_id']);
    $stmt->bindParam(':donor_hospital_name', $_POST['donor_hospital_name']);
    $stmt->bindParam(':recipient_id', $_POST['recipient_id']);
    $stmt->bindParam(':recipient_name', $_POST['recipient_name']);
    $stmt->bindParam(':recipient_hospital_id', $_POST['recipient_hospital_id']);
    $stmt->bindParam(':recipient_hospital_name', $_POST['recipient_hospital_name']);
    $stmt->bindParam(':organ_type', $_POST['organ_type']);
    $stmt->bindParam(':blood_group', $_POST['blood_group']);

    // Execute the query
    $stmt->execute();

    // Return success response
    echo json_encode(['success' => true]);

} catch(PDOException $e) {
    // Log the error and return failure response
    error_log("Error creating match: " . $e->getMessage());
    echo json_encode(['success' => false, 'message' => 'Database error occurred']);
}
