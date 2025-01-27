<?php
// Enable error reporting
error_reporting(E_ALL);
ini_set('display_errors', 1);

require_once 'backend/php/EmailValidator.php';
require_once 'vendor/autoload.php';
require_once 'backend/php/helpers/mailer.php';

// Test email validation
$validator = new EmailValidator();

// Test cases
$emails = [
    'test@gmail.com',
    'invalid-email',
    'disposable@temp-mail.org',
    'yourlifelink.org@gmail.com'
];

echo "Testing Email Validation:\n";
foreach ($emails as $email) {
    $result = $validator->validateEmail($email);
    echo "\nTesting email: " . $email . "\n";
    echo "Result: " . ($result['valid'] ? 'Valid' : 'Invalid') . "\n";
    echo "Message: " . $result['message'] . "\n";
}

// Test email sending
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

$config = require 'config/email_config.php';

echo "\n\nTesting Email Sending:\n";
try {
    $mail = new PHPMailer(true);
    
    //Server settings
    $mail->isSMTP();
    $mail->Host       = $config['smtp']['host'];
    $mail->SMTPAuth   = true;
    $mail->Username   = $config['smtp']['username'];
    $mail->Password   = $config['smtp']['password'];
    $mail->SMTPSecure = $config['smtp']['encryption'];
    $mail->Port       = $config['smtp']['port'];

    //Recipients
    $mail->setFrom($config['smtp']['username'], 'LifeLink System');
    $mail->addAddress($config['smtp']['username']); // Sending to self for testing

    //Content
    $mail->isHTML(true);
    $mail->Subject = 'LifeLink Email Test';
    $mail->Body    = 'This is a test email from LifeLink system. If you receive this, the email configuration is working correctly.';

    $mail->send();
    echo "Test email sent successfully!\n";
} catch (Exception $e) {
    echo "Error sending test email: {$mail->ErrorInfo}\n";
}

try {
    $mailer = new Mailer();
    // Change this to your email address
    $result = $mailer->sendTestEmail('yourlifelink.org@gmail.com');
    if ($result) {
        echo "<h2 style='color: green;'>Test email sent successfully!</h2>";
        echo "<p>Please check your email inbox (and spam folder).</p>";
    } else {
        echo "<h2 style='color: red;'>Failed to send email.</h2>";
    }
} catch (Exception $e) {
    echo "<h2 style='color: red;'>Error sending email:</h2>";
    echo "<pre>" . $e->getMessage() . "</pre>";
}
?>
