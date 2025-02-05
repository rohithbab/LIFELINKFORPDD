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
            $mail = new PHPMailer(true);
            
            // Server settings with debugging
            $mail->SMTPDebug = SMTP::DEBUG_SERVER;
            $mail->Debugoutput = function($str, $level) {
                error_log("PHPMailer Debug ($level): $str");
            };
            
            $mail->isSMTP();
            $mail->Host = 'smtp.gmail.com';
            $mail->Port = 587;
            $mail->SMTPAuth = true;
            $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
            
            // Gmail credentials
            $mail->Username = 'yourlifelink.org@gmail.com';
            // Replace this with your new App Password
            $mail->Password = 'wxhj ppdl ebsh wing';
            
            // Default settings
            $mail->setFrom('yourlifelink.org@gmail.com', 'LifeLink Admin');
            $mail->isHTML(true);
            $mail->CharSet = 'UTF-8';
            
            // Test SMTP connection before proceeding
            if (!$mail->smtpConnect()) {
                error_log("SMTP Connection Failed: Unable to connect to SMTP server");
                throw new Exception("Failed to connect to SMTP server. Please check your email settings.");
            }
            $mail->smtpClose();
            
            return $mail;
        } catch (Exception $e) {
            error_log("Mailer creation error: " . $e->getMessage());
            throw new Exception("Email configuration error: " . $e->getMessage());
        }
    }

    private function getEmailTemplate($templateName) {
        $templatePath = __DIR__ . '/../../../email_templates/' . $templateName;
        if (!file_exists($templatePath)) {
            error_log("Template not found: " . $templatePath);
            throw new Exception("Email template not found: " . $templateName);
        }
        $content = file_get_contents($templatePath);
        if ($content === false) {
            error_log("Failed to read template: " . $templatePath);
            throw new Exception("Failed to read email template: " . $templateName);
        }
        return $content;
    }

    public function testConnection() {
        try {
            error_log("Starting SMTP connection test...");
            $mail = $this->createMailer();
            
            // Try to connect to SMTP server
            if(!$mail->smtpConnect()) {
                error_log("SMTP connection failed");
                throw new Exception("Failed to connect to SMTP server");
            }
            
            // If we got here, connection successful
            $mail->smtpClose();
            error_log("SMTP connection successful");
            return true;
        } catch (Exception $e) {
            error_log("Error in testConnection: " . $e->getMessage());
            throw new Exception("SMTP Connection test failed: " . $e->getMessage());
        }
    }

    public function sendTestEmail($to) {
        try {
            error_log("Starting test email send to: $to");
            
            $mail = $this->createMailer();
            $mail->clearAddresses();
            $mail->addAddress($to);
            $mail->Subject = 'LifeLink Test Email';
            $mail->Body = '<h2>LifeLink Email Test</h2>
                          <p>This is a test email from the LifeLink system.</p>
                          <p>If you received this email, it means your email configuration is working correctly!</p>';
            
            error_log("Attempting to send email...");
            if (!$mail->send()) {
                error_log("Email send failed: " . $mail->ErrorInfo);
                throw new Exception($mail->ErrorInfo);
            }
            
            error_log("Email sent successfully to: $to");
            return true;
        } catch (Exception $e) {
            error_log("Error in sendTestEmail: " . $e->getMessage());
            throw new Exception("Failed to send test email: " . $e->getMessage());
        }
    }

    public function sendHospitalApprovalEmail($to, $hospitalName, $odmlId) {
        try {
            error_log("Starting hospital approval email send to: $to");
            
            $mail = $this->createMailer();
            $mail->clearAddresses();
            $mail->addAddress($to);
            $mail->Subject = 'LifeLink - Hospital Registration Approved';
            
            error_log("Loading approval template...");
            $template = $this->getEmailTemplate('hospital_approval.html');
            error_log("Template loaded successfully");
            
            error_log("Replacing template variables...");
            $template = str_replace('{HOSPITAL_NAME}', $hospitalName, $template);
            $template = str_replace('{ODML_ID}', $odmlId, $template);
            
            $mail->Body = $template;
            error_log("Attempting to send email...");
            
            if (!$mail->send()) {
                error_log("Email send failed: " . $mail->ErrorInfo);
                throw new Exception("Failed to send email: " . $mail->ErrorInfo);
            }
            
            error_log("Email sent successfully to: $to");
            return true;
        } catch (Exception $e) {
            error_log("Error in sendHospitalApprovalEmail: " . $e->getMessage());
            throw new Exception("Failed to send approval email: " . $e->getMessage());
        }
    }

    public function sendHospitalRejectionEmail($to, $hospitalName, $reason) {
        try {
            error_log("Starting hospital rejection email send to: $to");
            
            $mail = $this->createMailer();
            $mail->clearAddresses();
            $mail->addAddress($to);
            $mail->Subject = 'LifeLink - Hospital Registration Status Update';
            
            error_log("Loading rejection template...");
            $template = $this->getEmailTemplate('hospital_rejection.html');
            error_log("Template loaded successfully");
            
            error_log("Replacing template variables...");
            $template = str_replace('{HOSPITAL_NAME}', $hospitalName, $template);
            $template = str_replace('{REASON}', $reason, $template);
            
            $mail->Body = $template;
            error_log("Attempting to send email...");
            
            if (!$mail->send()) {
                error_log("Email send failed: " . $mail->ErrorInfo);
                throw new Exception($mail->ErrorInfo);
            }
            
            error_log("Email sent successfully to: $to");
            return true;
        } catch (Exception $e) {
            error_log("Error in sendHospitalRejectionEmail: " . $e->getMessage());
            throw new Exception("Failed to send rejection email: " . $e->getMessage());
        }
    }

    public function sendRejectionNotification($email, $name, $reason, $type) {
        try {
            error_log("Starting rejection notification email send to: $email");
            
            $mail = $this->createMailer();
            $mail->addAddress($email);
            $mail->Subject = ucfirst($type) . ' Registration Status - LifeLink';
            
            error_log("Loading rejection notification template...");
            $template = $this->getEmailTemplate('rejection_notification.html');
            error_log("Template loaded successfully");
            
            error_log("Replacing template variables...");
            $template = str_replace('{NAME}', $name, $template);
            $template = str_replace('{USER_TYPE}', ucfirst($type), $template);
            $template = str_replace('{REASON}', $reason, $template);
            
            $mail->Body = $template;
            error_log("Attempting to send email...");
            
            if (!$mail->send()) {
                error_log("Email send failed: " . $mail->ErrorInfo);
                throw new Exception($mail->ErrorInfo);
            }
            
            error_log("Email sent successfully to: $email");
            return true;
        } catch (Exception $e) {
            error_log("Error in sendRejectionNotification: " . $e->getMessage());
            throw new Exception("Failed to send rejection notification email: " . $e->getMessage());
        }
    }

    public function sendDonorApproval($email, $donorName, $odmlId) {
        try {
            error_log("Starting donor approval email send to: $email");
            
            $mail = $this->createMailer();
            $mail->addAddress($email);
            $mail->Subject = 'Donor Registration Approved - LifeLink';
            
            error_log("Loading approval template...");
            $template = $this->getEmailTemplate('approval_notification.html');
            error_log("Template loaded successfully");
            
            error_log("Replacing template variables...");
            $template = str_replace('{NAME}', $donorName, $template);
            $template = str_replace('{USER_TYPE}', 'Donor', $template);
            $template = str_replace('{ODML_ID}', $odmlId, $template);
            
            $mail->Body = $template;
            error_log("Attempting to send email...");
            
            if (!$mail->send()) {
                error_log("Email send failed: " . $mail->ErrorInfo);
                throw new Exception($mail->ErrorInfo);
            }
            
            error_log("Email sent successfully to: $email");
            return true;
        } catch (Exception $e) {
            error_log("Error in sendDonorApproval: " . $e->getMessage());
            throw new Exception("Failed to send donor approval email: " . $e->getMessage());
        }
    }

    public function sendRecipientApproval($email, $recipientName, $odmlId) {
        try {
            error_log("Starting recipient approval email send to: $email");
            
            $mail = $this->createMailer();
            $mail->addAddress($email);
            $mail->Subject = 'Recipient Registration Approved - LifeLink';
            
            error_log("Loading approval template...");
            $template = $this->getEmailTemplate('approval_notification.html');
            error_log("Template loaded successfully");
            
            error_log("Replacing template variables...");
            $template = str_replace('{NAME}', $recipientName, $template);
            $template = str_replace('{USER_TYPE}', 'Recipient', $template);
            $template = str_replace('{ODML_ID}', $odmlId, $template);
            
            $mail->Body = $template;
            error_log("Attempting to send email...");
            
            if (!$mail->send()) {
                error_log("Email send failed: " . $mail->ErrorInfo);
                throw new Exception($mail->ErrorInfo);
            }
            
            error_log("Email sent successfully to: $email");
            return true;
        } catch (Exception $e) {
            error_log("Error in sendRecipientApproval: " . $e->getMessage());
            throw new Exception("Failed to send recipient approval email: " . $e->getMessage());
        }
    }

    public function sendODMLUpdateEmail($email, $name, $type, $odmlId) {
        try {
            error_log("Starting ODML update email send to: $email");
            
            $mail = $this->createMailer();
            $mail->addAddress($email);
            $mail->Subject = ucfirst($type) . ' Registration Approved - ODML ID Assigned';
            
            error_log("Loading ODML update template...");
            $template = $this->getEmailTemplate('odml_assignment.html');
            error_log("Template loaded successfully");
            
            error_log("Replacing template variables...");
            $template = str_replace('{NAME}', $name, $template);
            $template = str_replace('{USER_TYPE}', ucfirst($type), $template);
            $template = str_replace('{ODML_ID}', $odmlId, $template);
            
            $mail->Body = $template;
            error_log("Attempting to send email...");
            
            if (!$mail->send()) {
                error_log("Email send failed: " . $mail->ErrorInfo);
                throw new Exception($mail->ErrorInfo);
            }
            
            error_log("Email sent successfully to: $email");
            return true;
        } catch (Exception $e) {
            error_log("Error in sendODMLUpdateEmail: " . $e->getMessage());
            throw new Exception("Failed to send ODML update email: " . $e->getMessage());
        }
    }

    public function sendHospitalApproval($email, $hospitalName, $odmlId) {
        try {
            error_log("Starting hospital approval email send to: $email");
            
            $mail = $this->createMailer();
            $mail->addAddress($email);
            $mail->Subject = 'Hospital Registration Approved - LifeLink';
            
            error_log("Loading approval template...");
            $template = $this->getEmailTemplate('approval_notification.html');
            error_log("Template loaded successfully");
            
            error_log("Replacing template variables...");
            $template = str_replace('{NAME}', $hospitalName, $template);
            $template = str_replace('{USER_TYPE}', 'Hospital', $template);
            $template = str_replace('{ODML_ID}', $odmlId, $template);
            
            $mail->Body = $template;
            error_log("Attempting to send email...");
            
            if (!$mail->send()) {
                error_log("Email send failed: " . $mail->ErrorInfo);
                throw new Exception($mail->ErrorInfo);
            }
            
            error_log("Email sent successfully to: $email");
            return true;
        } catch (Exception $e) {
            error_log("Error in sendHospitalApproval: " . $e->getMessage());
            throw new Exception("Failed to send hospital approval email: " . $e->getMessage());
        }
    }
}
