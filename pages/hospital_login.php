<?php
session_start();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Hospital Login - LifeLink</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link rel="stylesheet" href="../assets/css/styles.css">
    <style>
        .login-container {
            max-width: 400px;
            margin: 8rem auto;
            padding: 2rem;
            background: white;
            border-radius: 15px;
            box-shadow: 0 4px 20px rgba(0,0,0,0.1);
        }

        .login-title {
            font-size: 2rem;
            text-align: center;
            margin-bottom: 2rem;
            background: linear-gradient(45deg, var(--primary-blue), var(--primary-green));
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
        }

        .form-group {
            margin-bottom: 1.5rem;
        }

        .form-group label {
            display: block;
            margin-bottom: 0.5rem;
            color: var(--dark-gray);
            font-weight: 500;
        }

        .form-group input {
            width: 100%;
            padding: 0.8rem;
            border: 1px solid #ddd;
            border-radius: 5px;
            font-size: 1rem;
            transition: all 0.3s ease;
        }

        .form-group input:focus {
            border-color: var(--primary-blue);
            box-shadow: 0 0 0 2px rgba(33, 150, 243, 0.1);
            outline: none;
        }

        .login-button {
            width: 100%;
            padding: 1rem;
            background: var(--primary-blue);
            color: white;
            border: none;
            border-radius: 5px;
            font-size: 1.1rem;
            cursor: pointer;
            transition: all 0.3s ease;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 0.5rem;
        }

        .login-button:hover {
            background: var(--primary-green);
            transform: translateY(-2px);
        }

        .error-message {
            color: #dc3545;
            font-size: 0.9rem;
            margin-top: 0.3rem;
            display: none;
        }

        .server-error {
            background-color: #dc3545;
            color: white;
            padding: 1rem;
            border-radius: 5px;
            margin-bottom: 1.5rem;
            text-align: center;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 0.5rem;
        }

        .register-link {
            text-align: center;
            margin-top: 1.5rem;
        }

        .register-link a {
            color: var(--primary-blue);
            text-decoration: none;
            font-weight: 500;
            transition: all 0.3s ease;
        }

        .register-link a:hover {
            color: var(--primary-green);
        }
    </style>
</head>
<body>
    <!-- Navigation -->
    <nav class="navbar">
        <div class="nav-container">
            <a href="../index.php" class="logo">
                <span class="logo-life">LifeLink</span>
            </a>
        </div>
    </nav>

    <div class="login-container">
        <h2 class="login-title">Hospital Login</h2>
        
        <?php
        if (isset($_SESSION['error'])) {
            echo '<div class="server-error">
                    <i class="fas fa-exclamation-circle"></i>
                    ' . $_SESSION['error'] . '
                  </div>';
            unset($_SESSION['error']);
        }
        ?>

        <form action="../backend/php/hospital_login_process.php" method="POST" id="hospitalLoginForm">
            <div class="form-group">
                <label for="email">Email Address</label>
                <input type="email" id="email" name="email" required>
                <div class="error-message" id="emailError">Please enter a valid email address</div>
            </div>
            <div class="form-group">
                <label for="password">Password</label>
                <input type="password" id="password" name="password" required>
                <div class="error-message" id="passwordError">Password is required</div>
            </div>
            <button type="submit" class="login-button">
                <i class="fas fa-sign-in-alt"></i> Login
            </button>
        </form>

        <div class="register-link">
            Not registered yet? <a href="hospital_registration.php">Register your hospital</a>
        </div>
    </div>

    <script>
        document.getElementById('hospitalLoginForm').addEventListener('submit', function(e) {
            let isValid = true;
            const email = document.getElementById('email');
            const password = document.getElementById('password');
            const emailError = document.getElementById('emailError');
            const passwordError = document.getElementById('passwordError');

            // Reset error messages
            emailError.style.display = 'none';
            passwordError.style.display = 'none';

            // Email validation
            if (!email.value || !email.value.match(/^[\w-]+(\.[\w-]+)*@([\w-]+\.)+[a-zA-Z]{2,7}$/)) {
                emailError.style.display = 'block';
                isValid = false;
            }

            // Password validation
            if (!password.value) {
                passwordError.style.display = 'block';
                isValid = false;
            }

            if (!isValid) {
                e.preventDefault();
            }
        });
    </script>
</body>
</html>