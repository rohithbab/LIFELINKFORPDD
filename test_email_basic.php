<?php
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;
use PHPMailer\PHPMailer\Exception;

require 'backend/php/PHPMailer/PHPMailer.php';
require 'backend/php/PHPMailer/SMTP.php';
require 'backend/php/PHPMailer/Exception.php';

$mail = new PHPMailer(true);

try {
    // Server settings
    $mail->SMTPDebug = 2;
    $mail->isSMTP();
    $mail->Host = 'smtp.gmail.com';
    $mail->SMTPAuth = true;
    $mail->Username = 'yourlifelink.org@gmail.com';
    $mail->Password = 'gfnb wnxc pmgj eikm';
    $mail->SMTPSecure = 'tls';
    $mail->Port = 587;

    // Recipients
    $mail->setFrom('yourlifelink.org@gmail.com', 'LifeLink Test');
    $mail->addAddress('yourlifelink.org@gmail.com');

    // Content
    $mail->isHTML(true);
    $mail->Subject = 'Simple Test Email from LifeLink';
    $mail->Body = 'This is a test email to verify SMTP settings are working. Time: ' . date('Y-m-d H:i:s');

    echo "Sending test email...\n";
    $mail->send();
    echo "Test email sent successfully!\n";
} catch (Exception $e) {
    echo "Error sending email: {$mail->ErrorInfo}\n";
}
