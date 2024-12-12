<?php
session_start();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Recipient Login - LifeLink</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link rel="stylesheet" href="../assets/css/styles.css">
    <style>
        .login-container {
            max-width: 400px;
            margin: 2rem auto;
            padding: 2rem;
            background: white;
            border-radius: 10px;
            box-shadow: 0 0 20px rgba(0, 0, 0, 0.1);
        }
        .form-group {
            margin-bottom: 1.5rem;
        }
        .form-group label {
            display: block;
            margin-bottom: 0.5rem;
            color: #333;
            font-weight: 500;
        }
        .form-group input {
            width: 100%;
            padding: 0.8rem;
            border: 1px solid #ddd;
            border-radius: 5px;
            font-size: 1rem;
            transition: border-color 0.3s ease;
        }
        .form-group input:focus {
            border-color: var(--primary-blue);
            outline: none;
        }
        .login-btn {
            background: linear-gradient(45deg, var(--primary-blue), var(--primary-green));
            color: white;
            padding: 1rem;
            border: none;
            border-radius: 5px;
            font-size: 1.1rem;
            cursor: pointer;
            transition: opacity 0.3s ease;
            width: 100%;
        }
        .login-btn:hover {
            opacity: 0.9;
        }
        .error-message {
            color: #dc3545;
            font-size: 0.9rem;
            margin-top: 0.3rem;
        }
        .success-message {
            color: #28a745;
            font-size: 0.9rem;
            margin-top: 0.3rem;
        }
        .info-tooltip {
            color: #666;
            font-size: 0.9rem;
            margin-top: 0.3rem;
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
            <div class="nav-links">
                <a href="../index.php" class="btn" style="
                    background: var(--primary-blue);
                    color: var(--white);
                    transition: all 0.3s ease;
                    border: 2px solid var(--primary-blue);
                " onmouseover="
                    this.style.background='transparent';
                    this.style.color='var(--primary-blue)';
                " onmouseout="
                    this.style.background='var(--primary-blue)';
                    this.style.color='var(--white)';
                "><i class="fas fa-home"></i> Back to Home</a>
            </div>
        </div>
    </nav>

    <div class="container">
        <div class="login-container">
            <h2 class="text-center" style="color: var(--primary-blue); margin-bottom: 2rem;">Recipient Login</h2>
            
            <?php if (isset($_SESSION['error_message'])): ?>
                <div class="error-message text-center">
                    <?php 
                        echo $_SESSION['error_message'];
                        unset($_SESSION['error_message']);
                    ?>
                </div>
            <?php endif; ?>

            <?php if (isset($_SESSION['success_message'])): ?>
                <div class="success-message text-center">
                    <?php 
                        echo $_SESSION['success_message'];
                        unset($_SESSION['success_message']);
                    ?>
                </div>
            <?php endif; ?>

            <form action="../backend/php/recipient_login_process.php" method="POST" id="loginForm">
                <div class="form-group">
                    <label for="email">Email Address</label>
                    <input type="email" id="email" name="email" required>
                </div>

                <div class="form-group">
                    <label for="odm_id">ODM ID</label>
                    <input type="text" id="odm_id" name="odm_id" required>
                    <div class="info-tooltip">
                        <i class="fas fa-info-circle"></i> Enter the ODM ID provided after verification
                    </div>
                </div>

                <div class="form-group">
                    <label for="password">Password</label>
                    <input type="password" id="password" name="password" required>
                </div>

                <button type="submit" class="login-btn">Login</button>
            </form>

            <div style="text-align: center; margin-top: 1rem;">
                <p style="color: #666;">
                    Don't have an account? 
                    <a href="recipient_registration.php" style="
                        color: var(--primary-blue);
                        text-decoration: none;
                        font-weight: 500;
                    " onmouseover="this.style.textDecoration='underline'" 
                       onmouseout="this.style.textDecoration='none'">
                        Register here
                    </a>
                </p>
            </div>
        </div>
    </div>

    <script>
        document.getElementById('loginForm').addEventListener('submit', function(e) {
            const email = document.getElementById('email').value;
            const odmId = document.getElementById('odm_id').value;
            const password = document.getElementById('password').value;

            if (!email || !odmId || !password) {
                e.preventDefault();
                alert('Please fill in all fields');
                return;
            }

            // Email validation
            const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
            if (!emailRegex.test(email)) {
                e.preventDefault();
                alert('Please enter a valid email address');
                return;
            }
        });
    </script>
</body>
</html>