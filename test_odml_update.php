<?php
// Test script for ODML ID update

// First, let's get a hospital to update
require_once 'backend/php/connection.php';

try {
    $conn = getConnection();
    
    // Get a hospital
    $stmt = $conn->query("SELECT hospital_id, name, email, status FROM hospitals LIMIT 1");
    $hospital = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$hospital) {
        die("No hospital found to test with");
    }
    
    echo "Testing ODML ID update for hospital:\n";
    print_r($hospital);
    
    // Prepare the update request
    $data = [
        'type' => 'hospital',
        'id' => $hospital['hospital_id'],
        'odmlId' => 'TEST-ODML-' . time()
    ];
    
    // Create context for the request
    $context = stream_context_create([
        'http' => [
            'method' => 'POST',
            'header' => 'Content-Type: application/json',
            'content' => json_encode($data)
        ]
    ]);
    
    // Make the request
    echo "\nSending update request...\n";
    $response = file_get_contents(
        'http://localhost/LIFELINKFORPDD-main/LIFELINKFORPDD/backend/php/update_odml.php', 
        false, 
        $context
    );
    
    echo "\nResponse:\n";
    print_r(json_decode($response, true));
    
    // Verify the update
    $stmt = $conn->prepare("SELECT hospital_id, name, email, status, odml_id FROM hospitals WHERE hospital_id = ?");
    $stmt->execute([$hospital['hospital_id']]);
    $updated = $stmt->fetch(PDO::FETCH_ASSOC);
    
    echo "\nUpdated hospital data:\n";
    print_r($updated);
    
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
