<?php
require_once __DIR__ . '/backend/php/connection.php';
require_once __DIR__ . '/backend/php/helpers/mailer.php';

// Get a pending hospital for testing
$query = "SELECT hospital_id, name, email FROM hospitals WHERE status = 'pending' LIMIT 1";
$result = $conn->query($query);

if ($result->num_rows === 0) {
    die("No pending hospitals found for testing");
}

$hospital = $result->fetch_assoc();

echo "<h2>Testing Hospital Update with Email Failure</h2>";
echo "<p>Testing with Hospital: " . htmlspecialchars($hospital['name']) . "</p>";

// Prepare the POST data
$postData = http_build_query([
    'hospital_id' => $hospital['hospital_id'],
    'action' => 'approve',
    'odml_id' => 'ODML' . rand(1000, 9999),
    'test_mode' => true,
    'should_fail' => true
]);

// Make the request
$ch = curl_init('http://localhost/LIFELINKFORPDD/backend/php/update_hospital_status.php');
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, $postData);

$response = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

echo "<h3>Response:</h3>";
echo "<pre>" . htmlspecialchars($response) . "</pre>";

// Check if hospital status remained unchanged
$query = "SELECT status FROM hospitals WHERE hospital_id = ?";
$stmt = $conn->prepare($query);
$stmt->bind_param("i", $hospital['hospital_id']);
$stmt->execute();
$result = $stmt->get_result();
$currentStatus = $result->fetch_assoc()['status'];

echo "<h3>Database Check:</h3>";
echo "<p>Current hospital status: " . htmlspecialchars($currentStatus) . "</p>";
echo "<p>" . ($currentStatus === 'pending' ? 
    "✅ Test passed: Hospital status remained unchanged when email failed" : 
    "❌ ERROR: Hospital status changed despite email failure!") . "</p>";

$conn->close();
?>
