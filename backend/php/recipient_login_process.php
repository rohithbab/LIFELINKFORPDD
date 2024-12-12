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

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    try {
        $email = sanitize($_POST['email']);
        $odm_id = sanitize($_POST['odm_id']);
        $password = $_POST['password'];

        // Validate email
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            throw new Exception("Invalid email format");
        }

        // Check if recipient exists and get their details
        $stmt = $conn->prepare("SELECT id, email, password, odm_id, is_verified, status FROM recipients WHERE email = ?");
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows === 0) {
            throw new Exception("Invalid email or password");
        }

        $recipient = $result->fetch_assoc();

        // Verify ODM ID
        if ($recipient['odm_id'] !== $odm_id) {
            throw new Exception("Invalid ODM ID");
        }

        // Verify password
        if (!password_verify($password, $recipient['password'])) {
            throw new Exception("Invalid email or password");
        }

        // Check if account is verified
        if (!$recipient['is_verified']) {
            throw new Exception("Account not verified. Please wait for admin verification.");
        }

        // Check account status
        if ($recipient['status'] !== 'active') {
            throw new Exception("Account is " . $recipient['status'] . ". Please contact admin.");
        }

        // Set session variables
        $_SESSION['recipient_id'] = $recipient['id'];
        $_SESSION['recipient_email'] = $recipient['email'];
        $_SESSION['recipient_odm_id'] = $recipient['odm_id'];
        $_SESSION['user_type'] = 'recipient';

        // Redirect to recipient dashboard
        header("Location: ../../pages/recipient_dashboard.php");
        exit();

    } catch (Exception $e) {
        $_SESSION['error_message'] = $e->getMessage();
        header("Location: ../../pages/recipient_login.php");
        exit();
    }
} else {
    header("Location: ../../pages/recipient_login.php");
    exit();
}
?>
