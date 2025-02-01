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
            $template = str_replace(
                ['{HOSPITAL_NAME}', '{ODML_ID}'],
                [$hospitalName, $odmlId],
                $template
            );
            
            $mail->Body = $template;
            return $mail->send();
        } catch (Exception $e) {
            error_log("Error sending hospital approval: " . $e->getMessage());
            throw new Exception("Failed to send hospital approval email: " . $e->getMessage());
        }
    }

    public function sendRejectionNotification($email, $name, $reason, $type) {
        try {
            $mail = $this->createMailer();
            $mail->addAddress($email);
            $mail->Subject = ucfirst($type) . ' Registration Status - LifeLink';
            
            $template = file_get_contents($this->getTemplatePath($type . '_rejection'));
            
            // Replace placeholders based on type
            $placeholders = [
                'recipient' => ['{RECIPIENT_NAME}', '{REASON}'],
                'donor' => ['{DONOR_NAME}', '{REASON}'],
                'hospital' => ['{HOSPITAL_NAME}', '{REASON}']
            ];
            
            if (!isset($placeholders[$type])) {
                throw new Exception("Invalid user type for rejection email");
            }
            
            $template = str_replace(
                $placeholders[$type],
                [$name, $reason],
                $template
            );
            
            $mail->Body = $template;
            return $mail->send();
        } catch (Exception $e) {
            error_log("Error sending {$type} rejection: " . $e->getMessage());
            throw new Exception("Failed to send {$type} rejection email: " . $e->getMessage());
        }
    }

    public function sendDonorApproval($email, $donorName, $odmlId) {
        try {
            $mail = $this->createMailer();
            $mail->addAddress($email);
            $mail->Subject = 'Donor Registration Approved - LifeLink';
            
            $template = file_get_contents($this->getTemplatePath('donor_approval'));
            $template = str_replace(
                ['{DONOR_NAME}', '{ODML_ID}'],
                [$donorName, $odmlId],
                $template
            );
            
            $mail->Body = $template;
            return $mail->send();
        } catch (Exception $e) {
            error_log("Error sending donor approval: " . $e->getMessage());
            throw new Exception("Failed to send donor approval email: " . $e->getMessage());
        }
    }

    public function sendRecipientApproval($email, $recipientName, $odmlId) {
        try {
            $mail = $this->createMailer();
            $mail->addAddress($email);
            $mail->Subject = 'Recipient Registration Approved - LifeLink';
            
            $template = file_get_contents($this->getTemplatePath('recipient_approval'));
            error_log("Template loaded, replacing: RECIPIENT_NAME=$recipientName, ODML_ID=$odmlId");
            $template = str_replace(
                ['{RECIPIENT_NAME}', '{ODML_ID}'],
                [$recipientName, $odmlId],
                $template
            );
            
            $mail->Body = $template;
            return $mail->send();
        } catch (Exception $e) {
            error_log("Error sending recipient approval: " . $e->getMessage());
            throw new Exception("Failed to send recipient approval email: " . $e->getMessage());
        }
    }

    public function sendODMLUpdateEmail($email, $name, $type, $odmlId) {
        try {
            $mail = $this->createMailer();
            $mail->addAddress($email);
            $mail->Subject = ucfirst($type) . ' Registration Approved - ODML ID Assigned';
            
            // Load the approval email template using the common template path method
            $template = file_get_contents($this->getTemplatePath('odml_assignment'));
            
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
        $path = __DIR__ . '/../../email_templates/' . $template . '.html';
        if (!file_exists($path)) {
            throw new Exception("Email template not found: $template");
        }
        return $path;
    }
}
