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
                error_log("SMTP Debug: $str");
                echo "SMTP Debug: $str<br>";
            };
            
            $mail->isSMTP();
            $mail->Host = 'smtp.gmail.com';
            $mail->Port = 587;
            $mail->SMTPAuth = true;
            $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
            
            // Gmail credentials with new App Password
            $mail->Username = 'yourlifelink.org@gmail.com';
            $mail->Password = 'wxhj ppdl ebsh wing';
            
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
            $mail->clearAddresses();
            $mail->addAddress($to);
            $mail->Subject = 'LifeLink Test Email';
            $mail->Body = '<h2>LifeLink Email Test</h2>
                          <p>This is a test email from the LifeLink system.</p>
                          <p>If you received this email, it means your email configuration is working correctly!</p>';
            
            $result = $mail->send();
            if (!$result) {
                error_log("Mailer Error: " . $mail->ErrorInfo);
                throw new Exception($mail->ErrorInfo);
            }
            return true;
        } catch (Exception $e) {
            error_log("Error sending test email: " . $e->getMessage());
            throw new Exception("Failed to send test email: " . $e->getMessage());
        }
    }

    private function getEmailTemplate($templateName) {
        $templatePath = __DIR__ . '/../../../email_templates/' . $templateName;
        if (!file_exists($templatePath)) {
            throw new Exception("Email template not found: " . $templateName);
        }
        return file_get_contents($templatePath);
    }

    public function sendHospitalApprovalEmail($to, $hospitalName, $odmlId) {
        try {
            $mail = $this->createMailer();
            $mail->clearAddresses();
            $mail->addAddress($to);
            $mail->Subject = 'LifeLink - Hospital Registration Approved';
            
            $template = $this->getEmailTemplate('approval_notification.html');
            $template = str_replace('{NAME}', $hospitalName, $template);
            $template = str_replace('{USER_TYPE}', 'Hospital', $template);
            $template = str_replace('{ODML_ID}', $odmlId, $template);
            
            $mail->Body = $template;
            
            $result = $mail->send();
            if (!$result) {
                error_log("Mailer Error: " . $mail->ErrorInfo);
                throw new Exception($mail->ErrorInfo);
            }
            return true;
        } catch (Exception $e) {
            error_log("Error sending hospital approval email: " . $e->getMessage());
            throw new Exception("Failed to send hospital approval email: " . $e->getMessage());
        }
    }

    public function sendHospitalRejectionEmail($to, $hospitalName, $reason) {
        try {
            $mail = $this->createMailer();
            $mail->clearAddresses();
            $mail->addAddress($to);
            $mail->Subject = 'LifeLink - Hospital Registration Status Update';
            
            $template = $this->getEmailTemplate('rejection_notification.html');
            $template = str_replace('{NAME}', $hospitalName, $template);
            $template = str_replace('{USER_TYPE}', 'Hospital', $template);
            $template = str_replace('{REASON}', $reason, $template);
            
            $mail->Body = $template;
            
            $result = $mail->send();
            if (!$result) {
                error_log("Mailer Error: " . $mail->ErrorInfo);
                throw new Exception($mail->ErrorInfo);
            }
            return true;
        } catch (Exception $e) {
            error_log("Error sending hospital rejection email: " . $e->getMessage());
            throw new Exception("Failed to send hospital rejection email: " . $e->getMessage());
        }
    }

    public function sendRejectionNotification($email, $name, $reason, $type) {
        try {
            $mail = $this->createMailer();
            $mail->addAddress($email);
            $mail->Subject = ucfirst($type) . ' Registration Status - LifeLink';
            
            $template = $this->getEmailTemplate('rejection_notification.html');
            $template = str_replace('{NAME}', $name, $template);
            $template = str_replace('{USER_TYPE}', ucfirst($type), $template);
            $template = str_replace('{REASON}', $reason, $template);
            
            $mail->Body = $template;
            $result = $mail->send();
            if (!$result) {
                error_log("Mailer Error: " . $mail->ErrorInfo);
                throw new Exception($mail->ErrorInfo);
            }
            return true;
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
            
            $template = $this->getEmailTemplate('approval_notification.html');
            $template = str_replace('{NAME}', $donorName, $template);
            $template = str_replace('{USER_TYPE}', 'Donor', $template);
            $template = str_replace('{ODML_ID}', $odmlId, $template);
            
            $mail->Body = $template;
            $result = $mail->send();
            if (!$result) {
                error_log("Mailer Error: " . $mail->ErrorInfo);
                throw new Exception($mail->ErrorInfo);
            }
            return true;
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
            
            $template = $this->getEmailTemplate('approval_notification.html');
            $template = str_replace('{NAME}', $recipientName, $template);
            $template = str_replace('{USER_TYPE}', 'Recipient', $template);
            $template = str_replace('{ODML_ID}', $odmlId, $template);
            
            $mail->Body = $template;
            $result = $mail->send();
            if (!$result) {
                error_log("Mailer Error: " . $mail->ErrorInfo);
                throw new Exception($mail->ErrorInfo);
            }
            return true;
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
            
            $template = $this->getEmailTemplate('odml_assignment.html');
            $template = str_replace('{NAME}', $name, $template);
            $template = str_replace('{USER_TYPE}', ucfirst($type), $template);
            $template = str_replace('{ODML_ID}', $odmlId, $template);
            
            $mail->Body = $template;
            $result = $mail->send();
            if (!$result) {
                error_log("Mailer Error: " . $mail->ErrorInfo);
                throw new Exception($mail->ErrorInfo);
            }
            return true;
        } catch (Exception $e) {
            error_log("Error sending ODML update email: " . $e->getMessage());
            throw new Exception("Failed to send ODML update email: " . $e->getMessage());
        }
    }

    public function sendHospitalApproval($email, $hospitalName, $odmlId) {
        try {
            $mail = $this->createMailer();
            $mail->addAddress($email);
            $mail->Subject = 'Hospital Registration Approved - LifeLink';
            
            $template = $this->getEmailTemplate('approval_notification.html');
            $template = str_replace('{NAME}', $hospitalName, $template);
            $template = str_replace('{USER_TYPE}', 'Hospital', $template);
            $template = str_replace('{ODML_ID}', $odmlId, $template);
            
            $mail->Body = $template;
            $result = $mail->send();
            if (!$result) {
                error_log("Mailer Error: " . $mail->ErrorInfo);
                throw new Exception($mail->ErrorInfo);
            }
            return true;
        } catch (Exception $e) {
            error_log("Error sending hospital approval: " . $e->getMessage());
            throw new Exception("Failed to send hospital approval email: " . $e->getMessage());
        }
    }
}
