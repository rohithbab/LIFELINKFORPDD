<?php
require_once __DIR__ . '/../PHPMailer/PHPMailer.php';
require_once __DIR__ . '/../PHPMailer/SMTP.php';
require_once __DIR__ . '/../PHPMailer/Exception.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;
use PHPMailer\PHPMailer\Exception;

class Mailer {
    private function createMailer() {
        try {
            $mail = new PHPMailer();
            
            // Server settings
            $mail->isSMTP();
            $mail->Host = 'smtp.gmail.com';
            $mail->Port = 587;
            $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
            $mail->SMTPAuth = true;
            
            // Gmail credentials
            $mail->Username = 'yourlifelink.org@gmail.com';
            $mail->Password = 'rnda lowl zgel ddim';
            
            // Default settings
            $mail->setFrom('yourlifelink.org@gmail.com', 'LifeLink Admin');
            $mail->isHTML(true);
            $mail->CharSet = 'UTF-8';
            
            return $mail;
        } catch (Exception $e) {
            error_log("Mailer creation error: " . $e->getMessage());
            throw new Exception("Failed to create mailer: " . $e->getMessage());
        }
    }

    public function sendTestEmail($to) {
        try {
            $mail = $this->createMailer();
            $mail->addAddress($to);
            $mail->Subject = 'LifeLink Test Email';
            $mail->Body = '<h2>LifeLink Email Test</h2>
                          <p>This is a test email from the LifeLink system.</p>
                          <p>If you received this email, it means your email configuration is working correctly!</p>';
            
            return $mail->send();
        } catch (Exception $e) {
            error_log("Error sending test email: " . $e->getMessage());
            throw new Exception("Failed to send test email: " . $e->getMessage());
        }
    }

    public function sendHospitalApproval($email, $hospitalName, $odmlId) {
        try {
            $mail = $this->createMailer();
            $mail->addAddress($email);
            $mail->Subject = 'Hospital Registration Approved - LifeLink';
            
            $template = file_get_contents($this->getTemplatePath('hospital_approval'));
            $template = str_replace(['{{hospitalName}}', '{{odmlId}}'], [$hospitalName, $odmlId], $template);
            
            $mail->Body = $template;
            return $mail->send();
        } catch (Exception $e) {
            error_log("Error sending hospital approval: " . $e->getMessage());
            throw new Exception("Failed to send hospital approval email: " . $e->getMessage());
        }
    }

    public function sendHospitalRejection($email, $hospitalName, $reason) {
        try {
            $mail = $this->createMailer();
            $mail->addAddress($email);
            $mail->Subject = 'Hospital Registration Status - LifeLink';
            
            $template = file_get_contents($this->getTemplatePath('hospital_rejection'));
            $template = str_replace(['{{hospitalName}}', '{{reason}}'], [$hospitalName, $reason], $template);
            
            $mail->Body = $template;
            return $mail->send();
        } catch (Exception $e) {
            error_log("Error sending hospital rejection: " . $e->getMessage());
            throw new Exception("Failed to send hospital rejection email: " . $e->getMessage());
        }
    }

    public function sendDonorApproval($email, $donorName, $odmlId) {
        try {
            $mail = $this->createMailer();
            $mail->addAddress($email);
            $mail->Subject = 'Donor Registration Approved - LifeLink';
            
            $template = file_get_contents($this->getTemplatePath('donor_approval'));
            $template = str_replace(['{{donorName}}', '{{odmlId}}'], [$donorName, $odmlId], $template);
            
            $mail->Body = $template;
            return $mail->send();
        } catch (Exception $e) {
            error_log("Error sending donor approval: " . $e->getMessage());
            throw new Exception("Failed to send donor approval email: " . $e->getMessage());
        }
    }

    public function sendDonorRejection($email, $donorName, $reason) {
        try {
            $mail = $this->createMailer();
            $mail->addAddress($email);
            $mail->Subject = 'Donor Registration Status - LifeLink';
            
            $template = file_get_contents($this->getTemplatePath('donor_rejection'));
            $template = str_replace(['{{donorName}}', '{{reason}}'], [$donorName, $reason], $template);
            
            $mail->Body = $template;
            return $mail->send();
        } catch (Exception $e) {
            error_log("Error sending donor rejection: " . $e->getMessage());
            throw new Exception("Failed to send donor rejection email: " . $e->getMessage());
        }
    }

    public function sendRecipientApproval($email, $recipientName, $odmlId) {
        try {
            $mail = $this->createMailer();
            $mail->addAddress($email);
            $mail->Subject = 'Recipient Registration Approved - LifeLink';
            
            $template = file_get_contents($this->getTemplatePath('recipient_approval'));
            $template = str_replace(['{{recipientName}}', '{{odmlId}}'], [$recipientName, $odmlId], $template);
            
            $mail->Body = $template;
            return $mail->send();
        } catch (Exception $e) {
            error_log("Error sending recipient approval: " . $e->getMessage());
            throw new Exception("Failed to send recipient approval email: " . $e->getMessage());
        }
    }

    public function sendRecipientRejection($email, $recipientName, $reason) {
        try {
            $mail = $this->createMailer();
            $mail->addAddress($email);
            $mail->Subject = 'Recipient Registration Status - LifeLink';
            
            $template = file_get_contents($this->getTemplatePath('recipient_rejection'));
            $template = str_replace(['{{recipientName}}', '{{reason}}'], [$recipientName, $reason], $template);
            
            $mail->Body = $template;
            return $mail->send();
        } catch (Exception $e) {
            error_log("Error sending recipient rejection: " . $e->getMessage());
            throw new Exception("Failed to send recipient rejection email: " . $e->getMessage());
        }
    }

    public function sendODMLUpdateEmail($email, $name, $type, $odmlId) {
        try {
            $mail = $this->createMailer();
            $mail->addAddress($email);
            $mail->Subject = ucfirst($type) . ' Registration Approved - ODML ID Assigned';
            
            // Load the approval email template
            $template = file_get_contents(__DIR__ . '/../email_templates/approval.html');
            
            // Replace placeholders in the template
            $template = str_replace(['{{name}}', '{{odmlId}}'], [$name, $odmlId], $template);
            
            $mail->Body = $template;
            return $mail->send();
        } catch (Exception $e) {
            error_log("Error sending ODML update email: " . $e->getMessage());
            throw new Exception("Failed to send ODML update email: " . $e->getMessage());
        }
    }

    private function getTemplatePath($template) {
        return __DIR__ . '/../../../email_templates/' . $template . '.html';
    }
}
