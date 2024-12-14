<?php
session_start();

// Check if user is logged in as donor
if (!isset($_SESSION['is_donor']) || !$_SESSION['is_donor']) {
    header("Location: ../donor_login.php");
    exit();
}

// Get donor info from session
$donor_id = $_SESSION['donor_id'];
$donor_name = $_SESSION['donor_name'];
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Donor Dashboard - LifeLink</title>
    <!-- Add your CSS links here -->
</head>
<body>
    <h1>Welcome, <?php echo htmlspecialchars($donor_name); ?>!</h1>
    <!-- Add your dashboard content here -->
</body>
</html>
