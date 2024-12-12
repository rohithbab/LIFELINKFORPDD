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

        // Check if donor exists with the given email and ODM ID
        $stmt = $conn->prepare("SELECT id, password, full_name, odm_id, is_verified FROM donors WHERE email = ? AND odm_id = ?");
        $stmt->execute([$email, $odmId]);
        $donor = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$donor) {
            throw new Exception("Invalid email, ODM ID, or password.");
        }

        // Check if donor is verified
        if (!$donor['is_verified']) {
            throw new Exception("Your account is pending verification. Please contact the admin.");
        }

        // Verify password
        if (!password_verify($password, $donor['password'])) {
            throw new Exception("Invalid email, ODM ID, or password.");
        }

        // Set session variables
        $_SESSION['donor_id'] = $donor['id'];
        $_SESSION['donor_name'] = $donor['full_name'];
        $_SESSION['is_donor'] = true;

        // Redirect to donor dashboard
        header("Location: ../../pages/donor_dashboard.php");
        exit();

    } catch(Exception $e) {
        $_SESSION['error'] = $e->getMessage();
        header("Location: ../../pages/donor_login.php");
        exit();
    }
} else {
    header("Location: ../../pages/donor_login.php");
    exit();
}
?>
