<?php
session_start();
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Create a log file for debugging
$log_file = __DIR__ . '/../backend/php/debug.log';
function debug_log($message) {
    global $log_file;
    $log_message = date('Y-m-d H:i:s') . ': ' . (is_array($message) ? print_r($message, true) : $message) . "\n";
    file_put_contents($log_file, $log_message, FILE_APPEND);
}

// Define web paths
$web_root = '/LIFELINKFORPDD-main/LIFELINKFORPDD';
$registration_page = $web_root . '/pages/hospital_registration.php';
$index_page = $web_root . '/index.php';
$styles_css = $web_root . '/assets/css/styles.css';

// Debug session variables
debug_log('Session data on success page: ' . print_r($_SESSION, true));

// If no success flag in session, redirect to registration page
if (!isset($_SESSION['registration_success']) || $_SESSION['registration_success'] !== true) {
    debug_log('No success flag in session, redirecting to registration page');
    header("Location: $registration_page");
    exit();
}

// Get the hospital name from session
$hospital_name = $_SESSION['hospital_name'] ?? 'your hospital';

// Clear the session variables
unset($_SESSION['registration_success']);
unset($_SESSION['hospital_name']);
session_write_close();

// Write to debug log that we're displaying success page
debug_log('Displaying success page for hospital: ' . $hospital_name);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Registration Successful - LifeLink</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link rel="stylesheet" href="<?php echo $styles_css; ?>">
    <style>
        .success-container {
            max-width: 600px;
            margin: 50px auto;
            padding: 30px;
            background-color: #ffffff;
            border-radius: 10px;
            box-shadow: 0 0 20px rgba(0, 0, 0, 0.1);
            text-align: center;
        }

        .success-icon {
            font-size: 4rem;
            color: #4CAF50;
            margin-bottom: 20px;
        }

        .success-title {
            font-size: 2rem;
            color: #333;
            margin-bottom: 15px;
            background: linear-gradient(45deg, var(--primary-blue), var(--primary-green));
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
        }

        .success-message {
            font-size: 1.1rem;
            color: #666;
            margin-bottom: 30px;
            line-height: 1.6;
        }

        .next-steps {
            background-color: #f9f9f9;
            padding: 20px;
            border-radius: 8px;
            margin-bottom: 30px;
            text-align: left;
        }

        .next-steps h3 {
            color: #333;
            margin-bottom: 15px;
            font-size: 1.2rem;
        }

        .next-steps ul {
            list-style-type: none;
            padding: 0;
        }

        .next-steps li {
            margin-bottom: 10px;
            padding-left: 25px;
            position: relative;
            color: #555;
        }

        .next-steps li i {
            position: absolute;
            left: 0;
            top: 4px;
            color: var(--primary-blue);
        }

        .btn {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            padding: 12px 24px;
            border: none;
            border-radius: 5px;
            font-size: 1rem;
            cursor: pointer;
            transition: all 0.3s ease;
            text-decoration: none;
        }

        .btn-primary {
            background: linear-gradient(45deg, var(--primary-blue), var(--primary-green));
            color: white;
        }

        .btn-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
        }
    </style>
</head>
<body>
    <!-- Navigation -->
    <nav class="navbar">
        <div class="nav-container">
            <a href="<?php echo $index_page; ?>" class="logo">
                <span class="logo-text">Life<span class="logo-gradient">Link</span></span>
            </a>
        </div>
    </nav>

    <div class="success-container">
        <i class="fas fa-check-circle success-icon"></i>
        <h1 class="success-title">Registration Successful!</h1>
        <p class="success-message">
            Thank you for registering <?php echo htmlspecialchars($hospital_name); ?> with LifeLink. 
            Your registration is currently under review by our admin team.
        </p>

        <div class="next-steps">
            <h3><i class="fas fa-list-ul"></i> Next Steps</h3>
            <ul>
                <li>
                    <i class="fas fa-clock"></i>
                    Please wait for our admin team to review your registration
                </li>
                <li>
                    <i class="fas fa-envelope"></i>
                    You will receive an email notification once your registration is approved
                </li>
                <li>
                    <i class="fas fa-sign-in-alt"></i>
                    After approval, you can log in using your registered email and password
                </li>
            </ul>
        </div>
        
        <a href="<?php echo $index_page; ?>" class="btn btn-primary">
            <i class="fas fa-home"></i> Return to Homepage
        </a>
    </div>
</body>
</html>
