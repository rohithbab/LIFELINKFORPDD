<?php
session_start();
require_once 'connection.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email = filter_var($_POST['email'], FILTER_SANITIZE_EMAIL);
    $odml_id = trim(filter_var($_POST['odml_id'], FILTER_SANITIZE_STRING));
    $password = $_POST['password'];

    // Debug array to store all relevant information
    $_SESSION['debug_info'] = [
        'input_email' => $email,
        'input_odml' => $odml_id,
        'timestamp' => date('Y-m-d H:i:s')
    ];

    try {
        // First, check if the email exists
        $stmt = $conn->prepare("SELECT * FROM recipient_registration WHERE email = ? AND odml_id = ?");
        $stmt->execute([$email, $odml_id]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        $_SESSION['debug_info']['user_found'] = ($user !== false) ? 'yes' : 'no';

        if ($user) {
            // Store database values for debugging
            $_SESSION['debug_info']['db_data'] = [
                'id' => $user['id'],
                'email' => $user['email'],
                'odml_id' => $user['odml_id'] ?: 'not set',
                'status' => $user['request_status'],
                'password_match' => password_verify($password, $user['password']) ? 'yes' : 'no'
            ];

            // Check password
            if (password_verify($password, $user['password'])) {
                // Check status
                if ($user['request_status'] === 'accepted') {
                    // Login successful
                    $_SESSION['recipient_id'] = $user['id'];
                    $_SESSION['recipient_email'] = $email;
                    $_SESSION['recipient_name'] = $user['name'];
                    unset($_SESSION['debug_info']); // Clear debug info on success
                    header("Location: ../../pages/recipient/recipient_dashboard.php");
                    exit();
                } else if ($user['request_status'] === 'pending') {
                    $_SESSION['error'] = "Your account is still under review. Please wait for admin approval.";
                } else if ($user['request_status'] === 'rejected') {
                    $_SESSION['error'] = "Your registration has been rejected. Please contact support.";
                } else {
                    $_SESSION['error'] = "Invalid account status: " . htmlspecialchars($user['request_status']);
                }
            } else {
                $_SESSION['error'] = "Invalid password";
            }
        } else {
            // Try to find if email exists to give more specific error
            $stmt = $conn->prepare("SELECT odml_id FROM recipient_registration WHERE email = ?");
            $stmt->execute([$email]);
            $emailCheck = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if ($emailCheck) {
                $_SESSION['error'] = "Invalid ODML ID for this email address";
                $_SESSION['debug_info']['db_data'] = [
                    'stored_odml_id' => $emailCheck['odml_id'] ?: 'not set'
                ];
            } else {
                $_SESSION['error'] = "No account found with this email address";
            }
        }

    } catch(PDOException $e) {
        $_SESSION['error'] = "Database error: " . $e->getMessage();
        error_log("Login error: " . $e->getMessage());
    }

    // If we get here, there was an error
    header("Location: ../../pages/recipient_login.php");
    exit();
}

// If not POST request, redirect to login page
header("Location: ../../pages/recipient_login.php");
exit();
?>
