<?php
session_start();
require_once '../config/db_connection.php';

// Function to sanitize input
function sanitize($data) {
    $data = trim($data);
    $data = stripslashes($data);
    $data = htmlspecialchars($data);
    return $data;
}

// Function to handle file upload
function handleFileUpload($file, $targetDir, $maxSize, $allowedTypes) {
    if ($file['size'] > $maxSize) {
        return ['success' => false, 'message' => 'File size exceeds the limit'];
    }

    $fileType = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
    if (!in_array($fileType, $allowedTypes)) {
        return ['success' => false, 'message' => 'Invalid file type'];
    }

    // Create unique filename
    $fileName = uniqid() . '.' . $fileType;
    $targetPath = $targetDir . $fileName;

    if (move_uploaded_file($file['tmp_name'], $targetPath)) {
        return ['success' => true, 'path' => $targetPath];
    } else {
        return ['success' => false, 'message' => 'Failed to upload file'];
    }
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    try {
        // Personal Details
        $fullName = sanitize($_POST['fullName']);
        $dob = sanitize($_POST['dob']);
        $gender = sanitize($_POST['gender']);
        $phone = sanitize($_POST['phone']);
        $email = sanitize($_POST['email']);
        $address = sanitize($_POST['address']);

        // Medical Information
        $medicalCondition = sanitize($_POST['medicalCondition']);
        $bloodType = sanitize($_POST['bloodType']);
        $organRequired = sanitize($_POST['organRequired']);

        // ID Information
        $idType = sanitize($_POST['idType']);

        // Authentication Information
        $username = sanitize($_POST['username']);
        $password = password_hash($_POST['password'], PASSWORD_DEFAULT);

        // Handle file uploads
        $uploadDir = '../../uploads/recipients/';
        if (!file_exists($uploadDir)) {
            mkdir($uploadDir, 0777, true);
        }

        // Medical Records Upload
        $medicalRecordsResult = handleFileUpload(
            $_FILES['medicalRecords'],
            $uploadDir . 'medical_records/',
            5 * 1024 * 1024, // 5MB
            ['pdf', 'doc', 'docx']
        );

        if (!$medicalRecordsResult['success']) {
            throw new Exception('Medical Records: ' . $medicalRecordsResult['message']);
        }

        // ID Proof Upload
        $idProofResult = handleFileUpload(
            $_FILES['idProof'],
            $uploadDir . 'id_proofs/',
            2 * 1024 * 1024, // 2MB
            ['pdf', 'jpg', 'jpeg', 'png']
        );

        if (!$idProofResult['success']) {
            throw new Exception('ID Proof: ' . $idProofResult['message']);
        }

        // Check if email or username already exists
        $stmt = $conn->prepare("SELECT id FROM recipients WHERE email = ? OR username = ?");
        $stmt->bind_param("ss", $email, $username);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows > 0) {
            throw new Exception("Email or username already exists!");
        }

        // Insert into database
        $stmt = $conn->prepare("INSERT INTO recipients (
            full_name, date_of_birth, gender, phone, email, address,
            medical_condition, blood_type, organ_required,
            medical_records_path, id_type, id_proof_path,
            username, password, policy_agreement,
            medical_records_consent, terms_agreement
        ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 1, 1, 1)");

        $stmt->bind_param("sssssssssssss",
            $fullName, $dob, $gender, $phone, $email, $address,
            $medicalCondition, $bloodType, $organRequired,
            $medicalRecordsResult['path'], $idType, $idProofResult['path'],
            $username, $password
        );

        if ($stmt->execute()) {
            $_SESSION['success_message'] = "Registration successful! Please wait for verification.";
            header("Location: ../../pages/recipient_login.php");
            exit();
        } else {
            throw new Exception("Error in registration. Please try again.");
        }

    } catch (Exception $e) {
        $_SESSION['error_message'] = $e->getMessage();
        header("Location: ../../pages/recipient_registration.php");
        exit();
    }
}
?>
