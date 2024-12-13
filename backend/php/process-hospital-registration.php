<?php
session_start();
require_once '../config/connection.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    try {
        // Get form data
        $name = trim($_POST['hospitalName']);
        $email = trim($_POST['email']);
        $password = $_POST['password'];
        $phone = trim($_POST['phone']);
        $address = trim($_POST['address']);
        $license_number = trim($_POST['licenseNumber']);

        // Validate license file upload
        if (!isset($_FILES['licenseFile']) || $_FILES['licenseFile']['error'] !== UPLOAD_ERR_OK) {
            throw new Exception("Please upload a valid license file");
        }

        // Validate file type
        $allowed_types = ['application/pdf', 'image/jpeg', 'image/png'];
        $file_info = finfo_open(FILEINFO_MIME_TYPE);
        $mime_type = finfo_file($file_info, $_FILES['licenseFile']['tmp_name']);
        finfo_close($file_info);

        if (!in_array($mime_type, $allowed_types)) {
            throw new Exception("Invalid file type. Please upload PDF or image files only.");
        }

        // Upload license file
        $upload_dir = '../uploads/licenses/';
        if (!file_exists($upload_dir)) {
            mkdir($upload_dir, 0777, true);
        }

        $file_extension = pathinfo($_FILES['licenseFile']['name'], PATHINFO_EXTENSION);
        $license_filename = $license_number . '_' . time() . '.' . $file_extension;
        $license_path = $upload_dir . $license_filename;

        if (!move_uploaded_file($_FILES['licenseFile']['tmp_name'], $license_path)) {
            throw new Exception("Error uploading license file");
        }

        // Hash password
        $hashed_password = password_hash($password, PASSWORD_DEFAULT);

        // Begin transaction
        $conn->begin_transaction();

        // Insert into hospitals table
        $stmt = $conn->prepare("
            INSERT INTO hospitals (
                name, email, password, phone, address,
                license_number, status, registration_date
            ) VALUES (?, ?, ?, ?, ?, ?, 'pending', NOW())
        ");

        $stmt->bind_param(
            "ssssss",
            $name,
            $email,
            $hashed_password,
            $phone,
            $address,
            $license_number
        );

        if (!$stmt->execute()) {
            throw new Exception("Error registering hospital");
        }

        $hospital_id = $conn->insert_id;

        // Create notification for admin
        $notification_stmt = $conn->prepare("
            INSERT INTO notifications (
                type, message, created_at
            ) VALUES (
                'new_hospital',
                ?,
                NOW()
            )
        ");

        $notification_message = "New hospital registration: $name (License: $license_number)";
        $notification_stmt->bind_param("s", $notification_message);

        if (!$notification_stmt->execute()) {
            throw new Exception("Error creating notification");
        }

        // Store registration data in session for status page
        $_SESSION['registration_success'] = true;
        $_SESSION['hospital_data'] = [
            'name' => $name,
            'email' => $email,
            'license_number' => $license_number,
            'phone' => $phone,
            'address' => $address,
            'submitted_at' => date('Y-m-d H:i:s')
        ];

        // Commit transaction
        $conn->commit();

        // Redirect to status page
        header("Location: ../pages/process-hospital-registration.php");
        exit();

    } catch (Exception $e) {
        // Rollback transaction on error
        if (isset($conn)) {
            $conn->rollback();
        }

        // Delete uploaded file if it exists
        if (isset($license_path) && file_exists($license_path)) {
            unlink($license_path);
        }

        $_SESSION['error'] = $e->getMessage();
        header("Location: ../pages/hospital_registration.php");
        exit();
    }
} else {
    header("Location: ../pages/hospital_registration.php");
    exit();
}
