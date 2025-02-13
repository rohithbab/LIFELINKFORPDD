<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
header('Content-Type: application/json');

require_once 'connection.php';
require_once __DIR__ . '/helpers/email_sender.php';

use LifeLink\Helpers\EmailSender;

try {
    // Get JSON input
    $input = json_decode(file_get_contents('php://input'), true);
    error_log("Received input: " . json_encode($input));
    
    if (!isset($input['type']) || !isset($input['id']) || !isset($input['odmlId'])) {
        throw new Exception('Missing required parameters');
    }

    $type = $input['type'];
    $id = $input['id'];
    $odmlId = trim($input['odmlId']);

    // Only check if ODML ID is not empty
    if (empty($odmlId)) {
        throw new Exception('ODML ID cannot be empty');
    }

    $pdo = getConnection();
    $emailSender = new EmailSender();

    // Update ODML ID based on type
    switch($type) {
        case 'donor':
            $table = 'donor';
            $idColumn = 'donor_id';
            $nameField = 'name';
            $statusField = 'status';
            $statusValue = 'approved';
            break;
        case 'recipient':
            $table = 'recipient_registration';
            $idColumn = 'id';
            $nameField = 'full_name';
            $statusField = 'request_status';
            $statusValue = 'accepted';
            break;
        case 'hospital':
            $table = 'hospitals';
            $idColumn = 'hospital_id';
            $nameField = 'name';
            $statusField = 'status';
            $statusValue = 'approved';
            break;
        default:
            throw new Exception('Invalid type specified');
    }

    // Begin transaction
    $pdo->beginTransaction();

    try {
        // Update the record
        $query = "UPDATE $table SET odml_id = ?, $statusField = ? WHERE $idColumn = ?";
        error_log("Executing query: $query with params: " . json_encode([$odmlId, $statusValue, $id]));
        
        $stmt = $pdo->prepare($query);
        $stmt->execute([$odmlId, $statusValue, $id]);

        error_log("Update result - Rows affected: " . $stmt->rowCount());

        if ($stmt->rowCount() === 0) {
            throw new Exception('No record found to update');
        }

        // Get user details for email
        $query = "SELECT $nameField as name, email FROM $table WHERE $idColumn = ?";
        error_log("Fetching user details with query: $query with id: $id");
        
        $stmt = $pdo->prepare($query);
        $stmt->execute([$id]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        error_log("User details fetched: " . json_encode($user));

        if (!$user) {
            throw new Exception('User not found');
        }

        // Send email notification
        $result = $emailSender->sendODMLUpdateEmail($user['email'], $user['name'], $type, $odmlId);
        
        if ($result) {
            // Commit transaction
            $pdo->commit();
            echo json_encode([
                'success' => true,
                'message' => 'ODML ID updated successfully and notification email sent'
            ]);
        } else {
            // Rollback the database changes if email failed
            $pdo->rollBack();
            throw new Exception('Failed to send email notification');
        }
    } catch (Exception $e) {
        // Rollback transaction on error
        $pdo->rollBack();
        throw $e;
    }

} catch (Exception $e) {
    error_log("Error in update_odml.php: " . $e->getMessage());
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}
