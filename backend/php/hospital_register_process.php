<?php
session_start();
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Create a log file for debugging
$log_file = __DIR__ . '/debug.log';
function debug_log($message) {
    global $log_file;
    file_put_contents($log_file, date('Y-m-d H:i:s') . ': ' . $message . "\n", FILE_APPEND);
}

debug_log('Registration process started');
debug_log('POST data: ' . print_r($_POST, true));
debug_log('FILES data: ' . print_r($_FILES, true));

require_once '../../config/connection.php';

if ($_SERVER['REQUEST_METHOD'] != 'POST') {
    debug_log('Not a POST request');
    header("Location: ../../pages/hospital_registration.php");
    exit();
}

try {
    // Debug connection
    if (!$conn) {
        debug_log('Database connection failed');
        throw new Exception("Database connection failed");
    }
    debug_log('Database connection successful');

    // Get and sanitize input
    $hospital_name = filter_var($_POST['hospital_name'] ?? '', FILTER_SANITIZE_STRING);
    $email = filter_var($_POST['hospital_email'] ?? '', FILTER_SANITIZE_EMAIL);
    $phone = filter_var($_POST['hospital_phone'] ?? '', FILTER_SANITIZE_STRING);
    
    // Debug sanitized inputs
    debug_log("Sanitized inputs: hospital_name=$hospital_name, email=$email, phone=$phone");
    
    // Combine address fields
    $address = '';
    if (isset($_POST['street']) && isset($_POST['city']) && isset($_POST['state']) && 
        isset($_POST['postal_code']) && isset($_POST['country'])) {
        $address = filter_var(
            $_POST['street'] . ', ' . 
            $_POST['city'] . ', ' . 
            $_POST['state'] . ' ' . 
            $_POST['postal_code'] . ', ' . 
            $_POST['country'], 
            FILTER_SANITIZE_STRING
        );
    }
    debug_log("Combined address: $address");
    
    // Get region from state
    $region = filter_var($_POST['state'] ?? '', FILTER_SANITIZE_STRING);
    
    $license_number = filter_var($_POST['license_number'] ?? '', FILTER_SANITIZE_STRING);
    $password = $_POST['password'] ?? '';

    // Debug other inputs
    debug_log("Other inputs: license=$license_number, region=$region");

    // Validate required fields
    if (empty($hospital_name) || empty($email) || empty($phone) || empty($address)) {
        $missing = [];
        if (empty($hospital_name)) $missing[] = 'hospital_name';
        if (empty($email)) $missing[] = 'email';
        if (empty($phone)) $missing[] = 'phone';
        if (empty($address)) $missing[] = 'address';
        
        debug_log("Missing required fields: " . implode(', ', $missing));
        throw new Exception("Required fields missing: " . implode(', ', $missing));
    }

    // Validate email format
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        debug_log("Invalid email format: $email");
        throw new Exception("Invalid email format: $email");
    }

    // Check if email already exists
    $stmt = $conn->prepare("SELECT id FROM hospitals WHERE email = ?");
    if (!$stmt) {
        debug_log("Prepare failed for email check: " . $conn->error);
        throw new Exception("Database error");
    }
    $stmt->bind_param("s", $email);
    if (!$stmt->execute()) {
        debug_log("Execute failed for email check: " . $stmt->error);
        throw new Exception("Database error");
    }
    if ($stmt->get_result()->num_rows > 0) {
        debug_log("Email already exists: $email");
        throw new Exception("This email is already registered");
    }

    // Handle file upload
    if (!isset($_FILES['license_document']) || $_FILES['license_document']['error'] === UPLOAD_ERR_NO_FILE) {
        debug_log("License document not uploaded");
        throw new Exception("License document is required");
    }

    $license_file = $_FILES['license_document'];
    $upload_dir = "../../uploads/licenses/";
    
    // Create upload directory if it doesn't exist
    if (!file_exists($upload_dir)) {
        if (!mkdir($upload_dir, 0777, true)) {
            debug_log("Failed to create upload directory: $upload_dir");
            throw new Exception("Failed to create upload directory");
        }
        debug_log("Created upload directory: $upload_dir");
    }

    // Get file info
    $file_info = getimagesize($license_file['tmp_name']);
    $file_mime = $file_info ? $file_info['mime'] : mime_content_type($license_file['tmp_name']);
    
    debug_log("File MIME type: " . $file_mime);

    // Validate file
    $allowed_types = ['image/jpeg', 'image/png', 'image/jpg'];
    if (!in_array($file_mime, $allowed_types)) {
        debug_log("Invalid file type: $file_mime");
        throw new Exception("Invalid file type. Only JPEG and PNG files are allowed");
    }

    // Check file size (5MB limit)
    if ($license_file['size'] > 5 * 1024 * 1024) {
        debug_log("File too large: " . $license_file['size'] . " bytes");
        throw new Exception("File size exceeds 5MB limit");
    }

    // Generate unique filename
    $file_extension = strtolower(pathinfo($license_file['name'], PATHINFO_EXTENSION));
    if (!in_array($file_extension, ['jpg', 'jpeg', 'png'])) {
        $file_extension = 'jpg'; // Default to jpg if extension not recognized
    }
    $file_name = $license_number . '_' . time() . '.' . $file_extension;
    $file_path = $upload_dir . $file_name;

    debug_log("Attempting to move uploaded file to: $file_path");
    // Move uploaded file
    if (!move_uploaded_file($license_file['tmp_name'], $file_path)) {
        debug_log("Failed to move uploaded file. Upload error code: " . $license_file['error']);
        throw new Exception("Failed to upload license file");
    }
    debug_log("File uploaded successfully");

    // Start transaction
    if (!$conn->begin_transaction()) {
        debug_log("Could not start transaction: " . $conn->error);
        throw new Exception("Database error");
    }
    debug_log("Transaction started");

    // Insert hospital data
    $sql = "INSERT INTO hospitals (
        name, email, phone, address, region,
        license_number, license_file, password,
        status, registration_date
    ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, 'pending', NOW())";
    
    debug_log("Preparing SQL: $sql");
    
    $stmt = $conn->prepare($sql);
    if (!$stmt) {
        debug_log("Prepare failed: " . $conn->error);
        throw new Exception("Database error");
    }

    // Hash the password
    $hashed_password = password_hash($password, PASSWORD_DEFAULT);

    debug_log("Binding parameters for hospital insert");
    $stmt->bind_param(
        "ssssssss",
        $hospital_name,
        $email,
        $phone,
        $address,
        $region,
        $license_number,
        $file_name,
        $hashed_password
    );
    
    if (!$stmt->execute()) {
        debug_log("Execute failed for hospital insert: " . $stmt->error);
        throw new Exception("Failed to save hospital data");
    }
    
    $hospital_id = $conn->insert_id;
    debug_log("Hospital inserted with ID: $hospital_id");

    // Store registration data in session for status page
    $_SESSION['registration'] = [
        'hospital_id' => $hospital_id,
        'hospital_name' => $hospital_name,
        'email' => $email,
        'registration_date' => date('Y-m-d H:i:s')
    ];

    // Commit transaction
    if (!$conn->commit()) {
        debug_log("Commit failed: " . $conn->error);
        throw new Exception("Database error");
    }
    debug_log("Transaction committed successfully");

    // Redirect to registration status page
    debug_log("Registration successful, redirecting to status page");
    header("Location: ../../pages/process-hospital-registration.php");
    exit();

} catch (Exception $e) {
    debug_log("Error in hospital registration: " . $e->getMessage());
    
    // Rollback changes if needed
    if ($conn && $conn->ping()) {
        $conn->rollback();
        debug_log("Transaction rolled back");
    }

    // Delete uploaded file if it exists
    if (isset($file_path) && file_exists($file_path)) {
        unlink($file_path);
        debug_log("Uploaded file deleted");
    }

    $_SESSION['error'] = $e->getMessage();
    debug_log("Redirecting back to registration page with error");
    header("Location: ../../pages/hospital_registration.php");
    exit();
}
?>
