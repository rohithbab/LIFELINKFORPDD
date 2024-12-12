<?php
session_start();
require_once '../config/db_connect.php';

// Function to sanitize input
function sanitize_input($data) {
    $data = trim($data);
    $data = stripslashes($data);
    $data = htmlspecialchars($data);
    return $data;
}

// Function to handle file upload
function handle_file_upload($file, $target_dir) {
    // Create directory if it doesn't exist
    if (!is_dir($target_dir)) {
        mkdir($target_dir, 0777, true);
    }
    
    // Check if file was actually uploaded
    if (!isset($file['tmp_name']) || empty($file['tmp_name'])) {
        return null;
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
        throw new Exception("Error uploading file.");
    }
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    try {
        // Basic validation
        if (empty($_POST['fullName']) || empty($_POST['gender']) || empty($_POST['dob']) || 
            empty($_POST['bloodGroup']) || empty($_POST['address']) || empty($_POST['phone']) || 
            empty($_POST['email']) || empty($_POST['username']) || empty($_POST['password'])) {
            throw new Exception("Please fill in all required fields.");
        }

        // Validate password match
        if ($_POST['password'] !== $_POST['confirmPassword']) {
            throw new Exception("Passwords do not match.");
        }

        // Validate organ selection
        if (!isset($_POST['organs']) || empty($_POST['organs'])) {
            throw new Exception("Please select at least one organ to donate.");
        }

        // Sanitize inputs
        $fullName = sanitize_input($_POST['fullName']);
        $gender = sanitize_input($_POST['gender']);
        $dob = sanitize_input($_POST['dob']);
        $bloodGroup = sanitize_input($_POST['bloodGroup']);
        $address = sanitize_input($_POST['address']);
        $phone = sanitize_input($_POST['phone']);
        $email = sanitize_input($_POST['email']);
        $medicalConditions = isset($_POST['medicalConditions']) ? sanitize_input($_POST['medicalConditions']) : '';
        $organs = implode(',', $_POST['organs']);
        $donationReason = sanitize_input($_POST['donationReason']);
        $username = sanitize_input($_POST['username']);
        $password = password_hash($_POST['password'], PASSWORD_DEFAULT);

        // Optional guardian details
        $guardianName = isset($_POST['guardianName']) ? sanitize_input($_POST['guardianName']) : null;
        $guardianEmail = isset($_POST['guardianEmail']) ? sanitize_input($_POST['guardianEmail']) : null;
        $guardianPhone = isset($_POST['guardianPhone']) ? sanitize_input($_POST['guardianPhone']) : null;
        $guardianConfirmation = isset($_POST['guardianConfirmation']) ? 1 : 0;

        // Check if username or email already exists
        $stmt = $conn->prepare("SELECT id FROM donors WHERE username = ? OR email = ?");
        $stmt->execute([$username, $email]);
        if ($stmt->rowCount() > 0) {
            throw new Exception("Username or email already exists.");
        }

        // Base upload directory
        $baseUploadDir = __DIR__ . '/../../uploads/donors/' . $username . '/';

        // Handle ID Proof upload
        $idProofPath = null;
        if (isset($_FILES['idProof']) && $_FILES['idProof']['error'] !== UPLOAD_ERR_NO_FILE) {
            $idProofPath = handle_file_upload($_FILES['idProof'], $baseUploadDir . 'id_proof/');
        }

        // Handle Medical Reports upload
        $medicalReportsPath = null;
        if (isset($_FILES['medicalReports']) && $_FILES['medicalReports']['error'][0] !== UPLOAD_ERR_NO_FILE) {
            $medicalReportPaths = [];
            foreach ($_FILES['medicalReports']['tmp_name'] as $key => $tmp_name) {
                if (!empty($tmp_name)) {
                    $file = [
                        'name' => $_FILES['medicalReports']['name'][$key],
                        'type' => $_FILES['medicalReports']['type'][$key],
                        'tmp_name' => $tmp_name,
                        'error' => $_FILES['medicalReports']['error'][$key],
                        'size' => $_FILES['medicalReports']['size'][$key]
                    ];
                    $medicalReportPaths[] = handle_file_upload($file, $baseUploadDir . 'medical_reports/');
                }
            }
            $medicalReportsPath = !empty($medicalReportPaths) ? implode(',', $medicalReportPaths) : null;
        }

        // Handle Guardian ID Proof upload
        $guardianIdProofPath = null;
        if (isset($_FILES['guardianIdProof']) && $_FILES['guardianIdProof']['error'] !== UPLOAD_ERR_NO_FILE) {
            $guardianIdProofPath = handle_file_upload($_FILES['guardianIdProof'], $baseUploadDir . 'guardian_proof/');
        }

        // Insert into database
        $stmt = $conn->prepare("INSERT INTO donors (
            full_name, gender, date_of_birth, blood_group, address, phone, email,
            medical_conditions, organs_to_donate, medical_reports_path, id_proof_path,
            donation_reason, username, password, guardian_name, guardian_email,
            guardian_phone, guardian_id_proof_path, guardian_confirmation,
            policy_agreement, terms_agreement
        ) VALUES (
            ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?
        )");

        $stmt->execute([
            $fullName, $gender, $dob, $bloodGroup, $address, $phone, $email,
            $medicalConditions, $organs, $medicalReportsPath, $idProofPath,
            $donationReason, $username, $password, $guardianName, $guardianEmail,
            $guardianPhone, $guardianIdProofPath, $guardianConfirmation,
            isset($_POST['policyAgreement']) ? 1 : 0,
            isset($_POST['termsAgreement']) ? 1 : 0
        ]);

        $_SESSION['success'] = "Registration successful! You can now login.";
        header("Location: ../../pages/donor_login.php");
        exit();

    } catch(Exception $e) {
        $_SESSION['error'] = $e->getMessage();
        header("Location: ../../pages/donor_registration.php");
        exit();
    }
} else {
    header("Location: ../../pages/donor_registration.php");
    exit();
}
?>
