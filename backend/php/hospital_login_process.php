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

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email = sanitize_input($_POST['email']);
    $password = $_POST['password'];
    
    if (empty($email) || empty($password)) {
        $_SESSION['error'] = "All fields are required";
        header("Location: ../../pages/hospital_login.php");
        exit();
    }

    try {
        // Prepare SQL statement to prevent SQL injection
        $stmt = $conn->prepare("SELECT * FROM hospitals WHERE email = ?");
        $stmt->execute([$email]);
        
        if ($stmt->rowCount() == 1) {
            $hospital = $stmt->fetch();
            
            // Verify password
            if (password_verify($password, $hospital['password'])) {
                // Password is correct, create session
                $_SESSION['hospital_id'] = $hospital['id'];
                $_SESSION['hospital_email'] = $hospital['email'];
                $_SESSION['hospital_name'] = $hospital['hospital_name'];
                
                // Redirect to hospital dashboard
                header("Location: ../../pages/hospital_dashboard.php");
                exit();
            } else {
                // Invalid password
                $_SESSION['error'] = "Invalid email or password";
                header("Location: ../../pages/hospital_login.php");
                exit();
            }
        } else {
            // No hospital found with that email
            $_SESSION['error'] = "Invalid email or password";
            header("Location: ../../pages/hospital_login.php");
            exit();
        }
    } catch(PDOException $e) {
        $_SESSION['error'] = "An error occurred. Please try again later.";
        header("Location: ../../pages/hospital_login.php");
        exit();
    }
} else {
    // If someone tries to access this file directly
    header("Location: ../../pages/hospital_login.php");
    exit();
}
?>
