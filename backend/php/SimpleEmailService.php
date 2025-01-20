<?php
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require 'PHPMailer/PHPMailer.php';
require 'PHPMailer/SMTP.php';
require 'PHPMailer/Exception.php';

class SimpleEmailService {
    private $mailer;
    private $config;

    public function __construct() {
        $this->config = require __DIR__ . '/../../config/email_config.php';
        $this->mailer = new PHPMailer(true);
        
        // Server settings
        $this->mailer->isSMTP();
        $this->mailer->Host = $this->config['smtp']['host'];
        $this->mailer->SMTPAuth = true;
        $this->mailer->Username = $this->config['smtp']['username'];
        $this->mailer->Password = $this->config['smtp']['password'];
        $this->mailer->SMTPSecure = $this->config['smtp']['encryption'];
        $this->mailer->Port = $this->config['smtp']['port'];
    }

    public function sendEmail($to, $subject, $body, $isHTML = true) {
        try {
            // Recipients
            $this->mailer->setFrom($this->config['smtp']['username'], 'LifeLink System');
            $this->mailer->addAddress($to);

            // Content
            $this->mailer->isHTML($isHTML);
            $this->mailer->Subject = $subject;
            $this->mailer->Body = $body;

            $this->mailer->send();
            return ['success' => true, 'message' => 'Email sent successfully'];
        } catch (Exception $e) {
            return ['success' => false, 'message' => "Email could not be sent. Mailer Error: {$this->mailer->ErrorInfo}"];
        }
    }
}

// Example usage:
/*
$emailService = new SimpleEmailService();
$result = $emailService->sendEmail(
    'recipient@example.com',
    'Test Subject',
    'This is a test email from LifeLink'
);
if ($result['success']) {
    echo "Email sent!";
} else {
    echo "Error: " . $result['message'];
}
*/
?>
