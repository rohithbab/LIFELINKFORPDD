<?php
session_start();
require_once '../config/db_connect.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    try {
        // Get and sanitize input
        $email = filter_var($_POST['email'], FILTER_SANITIZE_EMAIL);
        $odmId = filter_var($_POST['odmId'], FILTER_SANITIZE_STRING);
        $password = $_POST['password'];

        // Validate input
        if (empty($email) || empty($odmId) || empty($password)) {
            throw new Exception("All fields are required.");
        }

        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            throw new Exception("Invalid email format.");
        }

        // Check if hospital exists with the given email and ODM ID
        $stmt = $conn->prepare("SELECT id, password, hospital_name, odm_id, is_verified FROM hospitals WHERE email = ? AND odm_id = ?");
        $stmt->execute([$email, $odmId]);
        $hospital = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$hospital) {
            throw new Exception("Invalid email, ODM ID, or password.");
        }

        // Check if hospital is verified
        if (!$hospital['is_verified']) {
            throw new Exception("Your hospital account is pending verification. Please contact the admin.");
        }

        // Verify password
        if (!password_verify($password, $hospital['password'])) {
            throw new Exception("Invalid email, ODM ID, or password.");
        }

        // Set session variables
        $_SESSION['hospital_id'] = $hospital['id'];
        $_SESSION['hospital_name'] = $hospital['hospital_name'];
        $_SESSION['is_hospital'] = true;

        // Redirect to hospital dashboard
        header("Location: ../../pages/hospital_dashboard.php");
        exit();

    } catch(Exception $e) {
        $_SESSION['error'] = $e->getMessage();
        header("Location: ../../pages/hospital_login.php");
        exit();
    }
} else {
    header("Location: ../../pages/hospital_login.php");
    exit();
}
?>
