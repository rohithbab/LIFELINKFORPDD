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
    
    $imageFileType = strtolower(pathinfo($file["name"], PATHINFO_EXTENSION));
    
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
        chmod($target_file, 0666); // Set proper permissions
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
        // Debug: Print all data
        error_log("POST data: " . print_r($_POST, true));
        error_log("FILES data: " . print_r($_FILES, true));
        
        // Debug file upload paths
        $base_upload_dir = __DIR__ . '/../../uploads/donors/';
        error_log("Base upload directory: " . $base_upload_dir);
        
        // Create upload directories if they don't exist
        $upload_dirs = [
            $base_upload_dir . 'id_proof_path/',
            $base_upload_dir . 'medical_reports_path/',
            $base_upload_dir . 'guardian_id_proof_path/'
        ];
        
        foreach ($upload_dirs as $dir) {
            if (!file_exists($dir)) {
                mkdir($dir, 0777, true);
                error_log("Created directory: " . $dir);
            }
        }

        // Validate required fields
        $required_fields = ['fullName', 'gender', 'dob', 'bloodGroup', 'email', 'phone', 'address', 'organs', 'password'];
        foreach ($required_fields as $field) {
            if (empty($_POST[$field])) {
                throw new Exception("Required field missing: " . $field);
            }
        }

        // Get and sanitize input
        $name = sanitize_input($_POST['fullName']);
        $gender = sanitize_input($_POST['gender']);
        $dob = sanitize_input($_POST['dob']);
        $blood_group = sanitize_input($_POST['bloodGroup']);
        $email = filter_var($_POST['email'], FILTER_SANITIZE_EMAIL);
        $phone = sanitize_input($_POST['phone']);
        $address = sanitize_input($_POST['address']);
        $medical_conditions = isset($_POST['medicalConditions']) ? sanitize_input($_POST['medicalConditions']) : null;
        $organs = implode(',', $_POST['organs']);
        $reason = isset($_POST['reason']) ? sanitize_input($_POST['reason']) : null;
        $password = password_hash($_POST['password'], PASSWORD_DEFAULT);

        // Handle ID proof
        $id_proof_path = null;
        error_log("Checking ID proof upload...");
        error_log("ID proof file data: " . print_r($_FILES['id_proof'], true));
        
        if (!isset($_FILES['id_proof'])) {
            throw new Exception("No ID proof file was sent");
        }
        
        if ($_FILES['id_proof']['error'] === UPLOAD_ERR_NO_FILE) {
            throw new Exception("No ID proof file was selected");
        }
        
        if ($_FILES['id_proof']['error'] !== UPLOAD_ERR_OK) {
            $upload_errors = [
                UPLOAD_ERR_INI_SIZE => "The file is larger than PHP's upload_max_filesize",
                UPLOAD_ERR_FORM_SIZE => "The file is larger than the form's MAX_FILE_SIZE",
                UPLOAD_ERR_PARTIAL => "The file was only partially uploaded",
                UPLOAD_ERR_NO_TMP_DIR => "Missing a temporary folder",
                UPLOAD_ERR_CANT_WRITE => "Failed to write file to disk",
                UPLOAD_ERR_EXTENSION => "A PHP extension stopped the file upload"
            ];
            $error_message = isset($upload_errors[$_FILES['id_proof']['error']]) 
                ? $upload_errors[$_FILES['id_proof']['error']]
                : "Unknown upload error";
            throw new Exception("ID proof upload error: " . $error_message);
        }
        
        try {
            $id_proof_path = handle_file_upload(
                $_FILES['id_proof'], 
                $base_upload_dir . 'id_proof_path/'
            );
            error_log("ID proof uploaded successfully: " . $id_proof_path);
        } catch (Exception $e) {
            throw new Exception("Error processing ID proof: " . $e->getMessage());
        }
        
        // Handle medical reports (optional)
        $medical_reports_path = null;
        if (isset($_FILES['medical_reports']) && $_FILES['medical_reports']['error'] !== UPLOAD_ERR_NO_FILE) {
            try {
                $medical_reports_path = handle_file_upload(
                    $_FILES['medical_reports'], 
                    $base_upload_dir . 'medical_reports_path/'
                );
                error_log("Medical reports uploaded successfully: " . $medical_reports_path);
            } catch (Exception $e) {
                // Medical reports are optional, so just log the error
                error_log("Error uploading medical reports: " . $e->getMessage());
            }
        }
        
        // Handle guardian ID proof (optional)
        $guardian_id_proof_path = null;
        if (isset($_FILES['guardian_id_proof']) && $_FILES['guardian_id_proof']['error'] !== UPLOAD_ERR_NO_FILE) {
            try {
                $guardian_id_proof_path = handle_file_upload(
                    $_FILES['guardian_id_proof'], 
                    $base_upload_dir . 'guardian_id_proof_path/'
                );
                error_log("Guardian ID proof uploaded successfully: " . $guardian_id_proof_path);
            } catch (Exception $e) {
                // Guardian ID proof is optional, so just log the error
                error_log("Error uploading guardian ID proof: " . $e->getMessage());
            }
        }

        // Handle guardian details (optional)
        $guardian_name = !empty($_POST['guardianName']) ? sanitize_input($_POST['guardianName']) : null;
        $guardian_email = !empty($_POST['guardianEmail']) ? filter_var($_POST['guardianEmail'], FILTER_SANITIZE_EMAIL) : null;
        $guardian_phone = !empty($_POST['guardianPhone']) ? sanitize_input($_POST['guardianPhone']) : null;
        $guardian_confirmation = isset($_POST['guardianConfirmation']) ? 1 : 0;

        // Insert into database
        $stmt = $conn->prepare("INSERT INTO donor (
            name, gender, dob, blood_group, email, phone, address,
            medical_conditions, organs_to_donate, medical_reports_path,
            id_proof_path, reason_for_donation, password,
            guardian_name, guardian_email, guardian_phone, guardian_id_proof_path
        ) VALUES (
            :name, :gender, :dob, :blood_group, :email, :phone, :address,
            :medical_conditions, :organs_to_donate, :medical_reports_path,
            :id_proof_path, :reason_for_donation, :password,
            :guardian_name, :guardian_email, :guardian_phone, :guardian_id_proof_path
        )");

        $stmt->execute([
            ':name' => $name,
            ':gender' => $gender,
            ':dob' => $dob,
            ':blood_group' => $blood_group,
            ':email' => $email,
            ':phone' => $phone,
            ':address' => $address,
            ':medical_conditions' => $medical_conditions,
            ':organs_to_donate' => $organs,
            ':medical_reports_path' => $medical_reports_path,
            ':id_proof_path' => $id_proof_path,
            ':reason_for_donation' => $reason,
            ':password' => $password,
            ':guardian_name' => $guardian_name,
            ':guardian_email' => $guardian_email,
            ':guardian_phone' => $guardian_phone,
            ':guardian_id_proof_path' => $guardian_id_proof_path
        ]);

        if ($stmt->rowCount() > 0) {
            $_SESSION['registration_success'] = true;
            $_SESSION['donor_email'] = $email;
            header("Location: ../../pages/donor/donor_registration_success.php");
            exit();
        } else {
            throw new Exception("Error inserting data into database");
        }

    } catch (Exception $e) {
        error_log("Error in donor registration: " . $e->getMessage());
        $_SESSION['error'] = $e->getMessage();
        header("Location: ../../pages/donor_registration.php");
        exit();
    }
} else {
    header("Location: ../../pages/donor_registration.php");
    exit();
}
