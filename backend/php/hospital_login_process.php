<?php
session_start();
require_once '../../config/connection.php';

if ($_SERVER['REQUEST_METHOD'] != 'POST') {
    header("Location: ../../pages/hospital_login.php");
    exit();
}

$email = $_POST['email'] ?? '';
$odml_id = $_POST['odml_id'] ?? '';
$password = $_POST['password'] ?? '';

try {
    // Validate all fields are present
    if (empty($email) || empty($odml_id) || empty($password)) {
        $_SESSION['error'] = "All fields are required";
        header("Location: ../../pages/hospital_login.php");
        exit();
    }

    // First check if the hospital exists and get its status
    $stmt = $conn->prepare("
        SELECT id, name, email, password, status, odml_id
        FROM hospitals
        WHERE email = ? AND odml_id = ?
    ");
    
    if (!$stmt) {
        throw new Exception("Database error: " . $conn->error);
    }
    
    $stmt->bind_param("ss", $email, $odml_id);
    if (!$stmt->execute()) {
        throw new Exception("Database error: " . $stmt->error);
    }
    
    $result = $stmt->get_result();
    $hospital = $result->fetch_assoc();

    if (!$hospital) {
        $_SESSION['error'] = "Invalid email, ODML ID, or password";
        header("Location: ../../pages/hospital_login.php");
        exit();
    }

    // Check hospital status
    if ($hospital['status'] === 'pending') {
        $_SESSION['error'] = "Your registration is still pending approval";
        header("Location: ../../pages/hospital_login.php");
        exit();
    }

    if ($hospital['status'] === 'rejected') {
        $_SESSION['error'] = "Your registration has been rejected";
        header("Location: ../../pages/hospital_login.php");
        exit();
    }

    // Verify password
    if (!password_verify($password, $hospital['password'])) {
        $_SESSION['error'] = "Invalid email, ODML ID, or password";
        header("Location: ../../pages/hospital_login.php");
        exit();
    }

    // Set session variables
    $_SESSION['hospital_id'] = $hospital['id'];
    $_SESSION['hospital_name'] = $hospital['name'];
    $_SESSION['hospital_email'] = $hospital['email'];
    $_SESSION['odml_id'] = $hospital['odml_id'];

    // Redirect to hospital dashboard
    header("Location: ../../pages/hospital/dashboard.php");
    exit();

} catch (Exception $e) {
    error_log("Login error: " . $e->getMessage());
    $_SESSION['error'] = "An error occurred. Please try again later.";
    header("Location: ../../pages/hospital_login.php");
    exit();
}
?>
