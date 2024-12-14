<?php
session_start();
error_reporting(E_ALL);
ini_set('display_errors', 1);

require_once 'connection.php';

// Function to sanitize input
function sanitize_input($data) {
    $data = trim($data);
    $data = stripslashes($data);
    $data = htmlspecialchars($data);
    return $data;
}

// Function to handle single file upload
function handle_file_upload($file, $target_dir) {
    // Create directory if it doesn't exist
    if (!is_dir($target_dir)) {
        mkdir($target_dir, 0777, true);
    }
    
    // Check if file was actually uploaded
    if (!isset($file['tmp_name']) || empty($file['tmp_name'])) {
        throw new Exception("No file uploaded.");
    }
    
    $target_file = $target_dir . basename($file["name"]);
    $imageFileType = strtolower(pathinfo($target_file, PATHINFO_EXTENSION));
    
    // Generate unique filename
    $filename = uniqid() . '.' . $imageFileType;
    $target_file = $target_dir . $filename;
    
    // Check file size (5MB max)
    if ($file["size"] > 5000000) {
        throw new Exception("File is too large. Maximum size is 5MB.");
    }
    
    // Allow certain file formats
    if($imageFileType != "jpg" && $imageFileType != "png" && $imageFileType != "jpeg" && $imageFileType != "pdf") {
        throw new Exception("Only JPG, JPEG, PNG & PDF files are allowed.");
    }
    
    if (move_uploaded_file($file["tmp_name"], $target_file)) {
        return $filename;
    } else {
        throw new Exception("Error uploading file: " . error_get_last()['message']);
    }
}

// Function to handle multiple file uploads
function handle_multiple_file_uploads($files, $target_dir) {
    if (!is_array($files['name'])) {
        return null;
    }

    $uploaded_files = [];
    
    for ($i = 0; $i < count($files['name']); $i++) {
        if ($files['error'][$i] === UPLOAD_ERR_NO_FILE) {
            continue;
        }

        $file = [
            'name' => $files['name'][$i],
            'type' => $files['type'][$i],
            'tmp_name' => $files['tmp_name'][$i],
            'error' => $files['error'][$i],
            'size' => $files['size'][$i]
        ];

        $uploaded_files[] = handle_file_upload($file, $target_dir);
    }

    return !empty($uploaded_files) ? implode(',', $uploaded_files) : null;
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    try {
        // Debug: Print POST data
        error_log("POST data: " . print_r($_POST, true));
        error_log("FILES data: " . print_r($_FILES, true));

        // Validate required fields
        $required_fields = ['fullName', 'gender', 'dob', 'bloodGroup', 'email', 'phone', 'address'];
        foreach ($required_fields as $field) {
            if (empty($_POST[$field])) {
                throw new Exception("Required field missing: " . $field);
            }
        }

        // Sanitize and validate input
        $name = sanitize_input($_POST['fullName']);
        $gender = sanitize_input($_POST['gender']);
        $dob = sanitize_input($_POST['dob']);
        $blood_group = sanitize_input($_POST['bloodGroup']);
        $email = filter_var($_POST['email'], FILTER_SANITIZE_EMAIL);
        $phone = sanitize_input($_POST['phone']);
        $address = sanitize_input($_POST['address']);
        $medical_conditions = sanitize_input($_POST['medicalConditions']);
        $organs = sanitize_input($_POST['organs']);
        $reason = sanitize_input($_POST['donationReason']);
        $guardian_name = sanitize_input($_POST['guardianName']);
        $guardian_email = filter_var($_POST['guardianEmail'], FILTER_SANITIZE_EMAIL);
        $guardian_phone = sanitize_input($_POST['guardianPhone']);
        
        // Generate a random password for the donor
        $password = bin2hex(random_bytes(8)); // generates a 16-character random password
        $hashed_password = password_hash($password, PASSWORD_DEFAULT);

        // Validate email
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            throw new Exception("Invalid email format.");
        }

        // Check if email already exists
        $stmt = $conn->prepare("SELECT COUNT(*) FROM donor WHERE email = ?");
        $stmt->execute([$email]);
        if ($stmt->fetchColumn() > 0) {
            throw new Exception("Email already registered.");
        }

        // Handle ID proof upload
        $id_proof_path = null;
        if (isset($_FILES['idProof']) && $_FILES['idProof']['error'] !== UPLOAD_ERR_NO_FILE) {
            $upload_dir = __DIR__ . "/../../uploads/donor_documents/";
            $id_proof_path = handle_file_upload($_FILES['idProof'], $upload_dir);
        } else {
            throw new Exception("ID proof is required.");
        }

        // Handle medical reports upload (if provided)
        $medical_reports_path = null;
        if (isset($_FILES['medicalReports'])) {
            $upload_dir = __DIR__ . "/../../uploads/medical_reports/";
            $medical_reports_path = handle_multiple_file_uploads($_FILES['medicalReports'], $upload_dir);
        }

        // Handle guardian ID proof upload (if provided)
        $guardian_id_proof_path = null;
        if (isset($_FILES['guardianIdProof']) && $_FILES['guardianIdProof']['error'] !== UPLOAD_ERR_NO_FILE) {
            $upload_dir = __DIR__ . "/../../uploads/guardian_documents/";
            $guardian_id_proof_path = handle_file_upload($_FILES['guardianIdProof'], $upload_dir);
        }

        // Begin transaction
        $conn->beginTransaction();

        // Debug: Print SQL data
        $sql = "INSERT INTO donor (
            name, gender, dob, blood_group, email, phone, address,
            medical_conditions, organs_to_donate, medical_reports_path,
            id_proof_path, reason_for_donation, guardian_name,
            guardian_email, guardian_phone, guardian_id_proof_path,
            password, status
        ) VALUES (
            ?, ?, ?, ?, ?, ?, ?,
            ?, ?, ?,
            ?, ?, ?,
            ?, ?, ?,
            ?, 'Pending'
        )";
        
        // Debug log the parameters
        $params = [
            $name,                  // name
            $gender,                // gender
            $dob,                   // dob
            $blood_group,           // blood_group
            $email,                 // email
            $phone,                 // phone
            $address,               // address
            $medical_conditions,    // medical_conditions
            $organs,                // organs_to_donate
            $medical_reports_path,  // medical_reports_path
            $id_proof_path,         // id_proof_path
            $reason,                // reason_for_donation
            $guardian_name,         // guardian_name
            $guardian_email,        // guardian_email
            $guardian_phone,        // guardian_phone
            $guardian_id_proof_path, // guardian_id_proof_path
            $hashed_password       // password
        ];
        
        error_log("SQL Query: " . $sql);
        error_log("Parameters: " . print_r($params, true));

        // Insert donor data
        $stmt = $conn->prepare($sql);
        $stmt->execute($params);

        // Commit transaction
        $conn->commit();

        // Store the password in session to show on success page
        $_SESSION['temp_password'] = $password;

        // Set success message and session variables
        $_SESSION['registration_success'] = true;
        $_SESSION['donor_email'] = $email;
        
        // Redirect to success page
        header("Location: /LIFELINKFORPDD-main/LIFELINKFORPDD/pages/donor_registration_success.php");
        exit();

    } catch(Exception $e) {
        // Rollback transaction if active
        if ($conn->inTransaction()) {
            $conn->rollBack();
        }
        
        error_log("Error in donor registration: " . $e->getMessage());
        $_SESSION['error'] = $e->getMessage();
        header("Location: /LIFELINKFORPDD-main/LIFELINKFORPDD/pages/donor_registration.php");
        exit();
    }
} else {
    header("Location: /LIFELINKFORPDD-main/LIFELINKFORPDD/pages/donor_registration.php");
    exit();
}
