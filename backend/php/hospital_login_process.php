<?php
session_start();
require_once '../../config/connection.php';

if ($_SERVER['REQUEST_METHOD'] != 'POST') {
    header("Location: ../../pages/hospital_login.php");
    exit();
}

$odml_id = $_POST['odml_id'];
$password = $_POST['password'];

try {
    // First check if the hospital exists and get its status
    $stmt = $conn->prepare("
        SELECT h.id, h.name, h.status, hl.password, hl.odml_id 
        FROM hospitals h
        JOIN hospital_login hl ON h.id = hl.hospital_id
        WHERE hl.odml_id = ?
    ");
    $stmt->bind_param("s", $odml_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $hospital = $result->fetch_assoc();

    if (!$hospital) {
        header("Location: ../../pages/hospital_login.php?error=invalid");
        exit();
    }

    // Check hospital status
    if ($hospital['status'] === 'pending') {
        header("Location: ../../pages/hospital_login.php?error=pending");
        exit();
    }

    if ($hospital['status'] === 'rejected') {
        header("Location: ../../pages/hospital_login.php?error=rejected");
        exit();
    }

    // Verify password
    if (!password_verify($password, $hospital['password'])) {
        header("Location: ../../pages/hospital_login.php?error=invalid");
        exit();
    }

    // Update last login time
    $stmt = $conn->prepare("
        UPDATE hospital_login 
        SET last_login = CURRENT_TIMESTAMP 
        WHERE odml_id = ?
    ");
    $stmt->bind_param("s", $odml_id);
    $stmt->execute();

    // Set session variables
    $_SESSION['hospital_id'] = $hospital['id'];
    $_SESSION['hospital_name'] = $hospital['name'];
    $_SESSION['odml_id'] = $hospital['odml_id'];

    // Check if it's first login (using temporary password)
    if (isset($_SESSION['temp_password']) && $_SESSION['temp_password']) {
        header("Location: ../../pages/change_password.php?first=1");
        exit();
    }

    // Redirect to hospital dashboard
    header("Location: ../../pages/hospital/dashboard.php");
    exit();

} catch (Exception $e) {
    error_log("Login error: " . $e->getMessage());
    header("Location: ../../pages/hospital_login.php?error=system");
    exit();
}
?>
