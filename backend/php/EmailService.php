<?php
require_once 'connection.php';

class EmailService {
    private $conn;
    private $mailer;

    public function __construct($conn) {
        $this->conn = $conn;
        
        // Initialize PHPMailer (we'll need to install this)
        $this->mailer = new PHPMailer(true);
        $this->mailer->isSMTP();
        // SMTP configuration will go here
    }

    public function validateEmail($email) {
        // Basic validation
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            return false;
        }

        // Check for disposable email domains
        $domain = substr(strrchr($email, "@"), 1);
        $stmt = $this->conn->prepare("SELECT COUNT(*) FROM disposable_domains WHERE domain = ?");
        $stmt->execute([$domain]);
        if ($stmt->fetchColumn() > 0) {
            return false;
        }

        // Additional validation can be added here
        // We can integrate with email validation APIs later

        return true;
    }

    public function sendEmail($to, $subject, $body) {
        try {
            $this->mailer->addAddress($to);
            $this->mailer->Subject = $subject;
            $this->mailer->Body = $body;
            $this->mailer->send();
            return true;
        } catch (Exception $e) {
            error_log("Email sending failed: " . $e->getMessage());
            return false;
        }
    }

    public function sendApprovalEmail($type, $email, $name, $odmlId, $additionalContent = '') {
        require_once __DIR__ . '/../../email_templates/' . $type . '_approval.php';
        $templateFunction = 'get' . ucfirst($type) . 'ApprovalTemplate';
        
        if (function_exists($templateFunction)) {
            $emailContent = $templateFunction($name, $odmlId, $additionalContent);
            return $this->sendEmail($email, substr($emailContent, strpos($emailContent, "Subject: ") + 9, strpos($emailContent, "\n")), $emailContent);
        }
        return false;
    }

    public function sendRejectionEmail($type, $email, $name, $reason) {
        require_once __DIR__ . '/../../email_templates/rejection.php';
        $emailContent = getRejectionTemplate($name, $type, $reason);
        return $this->sendEmail($email, substr($emailContent, strpos($emailContent, "Subject: ") + 9, strpos($emailContent, "\n")), $emailContent);
    }
}
?>
