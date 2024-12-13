<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Hospital Login - LifeLink</title>
    <link rel="stylesheet" href="../assets/css/styles.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
    <style>
        .auth-card {
            max-width: 400px;
            margin: 2rem auto;
            padding: 2rem;
            box-shadow: 0 0 10px rgba(0,0,0,0.1);
            border-radius: 8px;
            background: white;
        }
        .auth-header {
            text-align: center;
            margin-bottom: 2rem;
        }
        .auth-logo {
            max-width: 150px;
            margin-bottom: 1rem;
        }
        .form-group {
            margin-bottom: 1.5rem;
        }
        .input-group {
            position: relative;
            margin-top: 0.5rem;
        }
        .input-group i {
            position: absolute;
            left: 1rem;
            top: 50%;
            transform: translateY(-50%);
            color: #666;
        }
        .input-group input {
            width: 100%;
            padding: 0.8rem 1rem 0.8rem 2.5rem;
            border: 1px solid #ddd;
            border-radius: 4px;
            font-size: 1rem;
        }
        .btn-primary {
            background: #007bff;
            color: white;
            border: none;
            padding: 0.8rem;
            border-radius: 4px;
            width: 100%;
            font-size: 1rem;
            cursor: pointer;
            transition: background 0.3s;
        }
        .btn-primary:hover {
            background: #0056b3;
        }
        .auth-links {
            margin-top: 1.5rem;
            text-align: center;
            font-size: 0.9rem;
        }
        .auth-links a {
            color: #007bff;
            text-decoration: none;
            margin: 0 0.5rem;
        }
        .auth-links a:hover {
            text-decoration: underline;
        }
        .alert {
            padding: 1rem;
            margin-bottom: 1rem;
            border-radius: 4px;
            text-align: center;
        }
        .alert-danger {
            background: #f8d7da;
            color: #721c24;
            border: 1px solid #f5c6cb;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="auth-card">
            <div class="auth-header">
                <img src="../assets/images/logo.png" alt="LifeLink Logo" class="auth-logo">
                <h1>Hospital Login</h1>
            </div>
            
            <?php
            session_start();
            // Display error message if any
            if (isset($_SESSION['error'])) {
                echo '<div class="alert alert-danger">' . $_SESSION['error'] . '</div>';
                unset($_SESSION['error']);
            }
            ?>
            
            <form action="../backend/php/hospital_login_process.php" method="POST" class="auth-form">
                <div class="form-group">
                    <label for="email">Email Address</label>
                    <div class="input-group">
                        <i class="fas fa-envelope"></i>
                        <input type="email" id="email" name="email" required>
                    </div>
                </div>

                <div class="form-group">
                    <label for="odml_id">ODML ID</label>
                    <div class="input-group">
                        <i class="fas fa-id-card"></i>
                        <input type="text" id="odml_id" name="odml_id" required>
                    </div>
                </div>
                
                <div class="form-group">
                    <label for="password">Password</label>
                    <div class="input-group">
                        <i class="fas fa-lock"></i>
                        <input type="password" id="password" name="password" required>
                    </div>
                </div>
                
                <div class="form-group">
                    <button type="submit" class="btn btn-primary btn-block">
                        Login
                    </button>
                </div>
                
                <div class="auth-links">
                    <a href="hospital_registration.php">Register as a Hospital</a>
                    <a href="forgot_password.php">Forgot Password?</a>
                </div>
            </form>
        </div>
    </div>
</body>
</html>