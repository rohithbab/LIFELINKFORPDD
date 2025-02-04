<?php
session_start();
// Simulate recipient login for testing
$_SESSION['is_recipient'] = true;
$_SESSION['recipient_id'] = 1;
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Test Recipient Sidebar</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        body {
            margin: 0;
            padding: 0;
            font-family: 'Arial', sans-serif;
            background: #f4f6f9;
        }
        .main-content {
            margin-left: 280px;
            padding: 2rem;
        }
    </style>
</head>
<body>
    <?php include '../../includes/recipient_sidebar.php'; ?>
    <div class="main-content">
        <h1>Test Page for Recipient Sidebar</h1>
        <p>This is a test page to view the recipient sidebar.</p>
    </div>
</body>
</html>
