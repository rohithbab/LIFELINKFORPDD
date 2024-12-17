<?php
session_start();
require_once 'connection.php';

// Function to sanitize input
function sanitize_input($data) {
    $data = trim($data);
    $data = stripslashes($data);
    $data = htmlspecialchars($data);
    return $data;
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    try {
        // Get and sanitize input
        $full_name = sanitize_input($_POST['fullName']);
        $date_of_birth = sanitize_input($_POST['dob']);
        $gender = sanitize_input($_POST['gender']);
        $phone_number = sanitize_input($_POST['phone']);
        $email = filter_var($_POST['email'], FILTER_SANITIZE_EMAIL);
        $address = sanitize_input($_POST['address']);
        $medical_condition = sanitize_input($_POST['medicalCondition']);
        $blood_type = sanitize_input($_POST['bloodType']);
        $organ_required = sanitize_input($_POST['organRequired']);
        $organ_reason = sanitize_input($_POST['organReason']);
        $urgency_level = sanitize_input($_POST['urgencyLevel']); // Added urgency level
        $id_proof_type = sanitize_input($_POST['idType']);
        $id_proof_number = sanitize_input($_POST['idNumber']);
        
        // Handle file upload for ID document
        $id_document = '';
        if(isset($_FILES['idDocument']) && $_FILES['idDocument']['error'] === UPLOAD_ERR_OK) {
            $upload_dir = '../../uploads/id_documents/';
            if (!file_exists($upload_dir)) {
                mkdir($upload_dir, 0777, true);
            }
            $file_extension = strtolower(pathinfo($_FILES['idDocument']['name'], PATHINFO_EXTENSION));
            $new_filename = uniqid() . '.' . $file_extension;
            $upload_path = $upload_dir . $new_filename;
            
            if(move_uploaded_file($_FILES['idDocument']['tmp_name'], $upload_path)) {
                $id_document = $new_filename;
            } else {
                throw new Exception("Failed to upload ID document. Please try again.");
            }
        } else {
            throw new Exception("ID document is required.");
        }

        // Handle file upload for medical records
        $medical_records = '';
        if(isset($_FILES['medicalRecords']) && $_FILES['medicalRecords']['error'] === UPLOAD_ERR_OK) {
            $allowed_types = ['pdf', 'doc', 'docx', 'jpg', 'jpeg', 'png'];
            $file_extension = strtolower(pathinfo($_FILES['medicalRecords']['name'], PATHINFO_EXTENSION));
            
            if (!in_array($file_extension, $allowed_types)) {
                throw new Exception("Invalid file type for medical records. Allowed types: PDF, DOC, DOCX, JPG, JPEG, PNG");
            }
            
            if ($_FILES['medicalRecords']['size'] > 5 * 1024 * 1024) { // 5MB limit
                throw new Exception("Medical records file size must be less than 5MB");
            }
            
            $upload_dir = '../../uploads/medical_records/';
            if (!file_exists($upload_dir)) {
                mkdir($upload_dir, 0777, true);
            }
            
            $new_filename = uniqid() . '.' . $file_extension;
            $upload_path = $upload_dir . $new_filename;
            
            if(move_uploaded_file($_FILES['medicalRecords']['tmp_name'], $upload_path)) {
                $medical_records = $new_filename;
            } else {
                throw new Exception("Failed to upload medical records. Please try again.");
            }
        } else {
            throw new Exception("Medical records are required.");
        }

        // Generate username from email (part before @)
        $username = explode('@', $email)[0];
        // Add random number if username exists
        $stmt = $conn->prepare("SELECT id FROM recipient_registration WHERE username = ?");
        $stmt->execute([$username]);
        if ($stmt->fetch()) {
            $username .= rand(100, 999);
        }

        $password = password_hash($_POST['password'], PASSWORD_DEFAULT);

        // Validate email
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            throw new Exception("Invalid email format.");
        }

        // Check if email already exists
        $stmt = $conn->prepare("SELECT id FROM recipient_registration WHERE email = ?");
        $stmt->execute([$email]);
        if ($stmt->fetch()) {
            throw new Exception("Email already registered.");
        }

        // Begin transaction
        $conn->beginTransaction();

        // Insert into recipient_registration table
        $sql = "INSERT INTO recipient_registration (
            full_name, date_of_birth, gender, phone_number, email, address,
            medical_condition, blood_type, organ_required, organ_reason, urgency_level,
            id_proof_type, id_proof_number, id_document, medical_records,
            username, password, request_status
        ) VALUES (
            :full_name, :date_of_birth, :gender, :phone_number, :email, :address,
            :medical_condition, :blood_type, :organ_required, :organ_reason, :urgency_level,
            :id_proof_type, :id_proof_number, :id_document, :medical_records,
            :username, :password, 'pending'
        )";

        $stmt = $conn->prepare($sql);
        
        // Bind parameters
        $params = [
            ':full_name' => $full_name,
            ':date_of_birth' => $date_of_birth,
            ':gender' => $gender,
            ':phone_number' => $phone_number,
            ':email' => $email,
            ':address' => $address,
            ':medical_condition' => $medical_condition,
            ':blood_type' => $blood_type,
            ':organ_required' => $organ_required,
            ':organ_reason' => $organ_reason,
            ':urgency_level' => $urgency_level,
            ':id_proof_type' => $id_proof_type,
            ':id_proof_number' => $id_proof_number,
            ':id_document' => $id_document,
            ':medical_records' => $medical_records,
            ':username' => $username,
            ':password' => $password
        ];
        
        if ($stmt->execute($params)) {
            $conn->commit();
            $_SESSION['registration_success'] = true;
            $_SESSION['recipient_email'] = $email;
            header("Location: ../../pages/recipient/recipient_registration_success.php");
            exit();
        }

    } catch(Exception $e) {
        // Rollback transaction on error
        if ($conn->inTransaction()) {
            $conn->rollback();
        }
        $_SESSION['error'] = $e->getMessage();
        header("Location: ../../pages/recipient_registration.php");
        exit();
    }
} else {
    $_SESSION['error'] = "Invalid request method";
    header("Location: ../../pages/recipient_registration.php");
    exit();
}
