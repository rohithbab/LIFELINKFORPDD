<?php
require_once __DIR__ . '/../PHPMailer/PHPMailer.php';
require_once __DIR__ . '/../PHPMailer/SMTP.php';
require_once __DIR__ . '/../PHPMailer/Exception.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;
use PHPMailer\PHPMailer\Exception;

class Mailer {
    private function createMailer() {
        $mail = new PHPMailer(true);
        
        // Enable debug output
        $mail->SMTPDebug = 2; // Debug mode
        $mail->Debugoutput = function($str, $level) {
            echo "$str\n";
        };
        
        // Server settings
        $mail->isSMTP();
        $mail->Host = 'smtp.gmail.com';
        $mail->SMTPAuth = true;
        $mail->Username = 'yourlifelink.org@gmail.com';  // Your Gmail address
        $mail->Password = 'gfnb wnxc pmgj eikm';        // Your app password
        $mail->SMTPSecure = 'tls';
        $mail->Port = 587;
        
        // Default settings
        $mail->isHTML(true);
        $mail->setFrom('yourlifelink.org@gmail.com', 'LifeLink Admin');
        
        return $mail;
    }
    
    private function getTemplatePath($template) {
        return dirname(dirname(dirname(__DIR__))) . '/email_templates/' . $template;
    }
    
    public function sendODMLAssignment($email, $name, $odmlId, $userType) {
        try {
            $mail = $this->createMailer();
            
            echo "Loading template...\n";
            $templatePath = $this->getTemplatePath('odml_assignment.html');
            if (!file_exists($templatePath)) {
                throw new Exception("Template file not found at: $templatePath");
            }
            
            $template = file_get_contents($templatePath);
            if ($template === false) {
                throw new Exception("Could not read template file");
            }
            
            echo "Setting up email...\n";
            $mail->addAddress($email);
            $mail->Subject = 'Your ODML ID Assignment - LifeLink';
            
            // Populate template
            $template = str_replace('{NAME}', $name, $template);
            $template = str_replace('{ODML_ID}', $odmlId, $template);
            $template = str_replace('{USER_TYPE}', ucfirst($userType), $template);
            
            $mail->Body = $template;
            
            echo "Attempting to send email to $email...\n";
            if ($mail->send()) {
                echo "Email sent successfully!\n";
                return true;
            }
        } catch (Exception $e) {
            echo "Error details: " . $e->getMessage() . "\n";
            if (isset($mail)) {
                echo "SMTP Error Info: " . $mail->ErrorInfo . "\n";
            }
            throw $e;
        }
    }
    
    public function sendApprovalNotification($email, $name, $userType, $odmlId) {
        try {
            $mail = $this->createMailer();
            
            echo "Loading template...\n";
            $templatePath = $this->getTemplatePath('approval_notification.html');
            if (!file_exists($templatePath)) {
                throw new Exception("Template file not found at: $templatePath");
            }
            
            $template = file_get_contents($templatePath);
            if ($template === false) {
                throw new Exception("Could not read template file");
            }
            
            echo "Setting up email...\n";
            $mail->addAddress($email);
            $mail->Subject = 'Account Approved - LifeLink';
            
            // Populate template
            $template = str_replace('{NAME}', $name, $template);
            $template = str_replace('{USER_TYPE}', ucfirst($userType), $template);
            $template = str_replace('{ODML_ID}', $odmlId, $template);
            
            $mail->Body = $template;
            
            echo "Attempting to send email to $email...\n";
            if ($mail->send()) {
                echo "Email sent successfully!\n";
                return true;
            }
        } catch (Exception $e) {
            echo "Error details: " . $e->getMessage() . "\n";
            if (isset($mail)) {
                echo "SMTP Error Info: " . $mail->ErrorInfo . "\n";
            }
            throw $e;
        }
    }
    
    public function sendRejectionNotification($email, $name, $userType, $reason) {
        try {
            $mail = $this->createMailer();
            
            echo "Loading template...\n";
            $templatePath = $this->getTemplatePath('rejection_notification.html');
            if (!file_exists($templatePath)) {
                throw new Exception("Template file not found at: $templatePath");
            }
            
            $template = file_get_contents($templatePath);
            if ($template === false) {
                throw new Exception("Could not read template file");
            }
            
            echo "Setting up email...\n";
            $mail->addAddress($email);
            $mail->Subject = 'Account Status Update - LifeLink';
            
            // Populate template
            $template = str_replace('{NAME}', $name, $template);
            $template = str_replace('{USER_TYPE}', ucfirst($userType), $template);
            $template = str_replace('{REASON}', $reason, $template);
            
            $mail->Body = $template;
            
            echo "Attempting to send email to $email...\n";
            if ($mail->send()) {
                echo "Email sent successfully!\n";
                return true;
            }
        } catch (Exception $e) {
            echo "Error details: " . $e->getMessage() . "\n";
            if (isset($mail)) {
                echo "SMTP Error Info: " . $mail->ErrorInfo . "\n";
            }
            throw $e;
        }
    }
    
    public function sendRejectionNotification2($email, $name, $type, $reason) {
        $subject = "Your {$type} registration has been rejected";
        $message = "
        Dear {$name},
        
        We regret to inform you that your {$type} registration has been rejected.
        
        Reason for rejection:
        {$reason}
        
        If you have any questions or would like to submit a new application, please contact us.
        
        Best regards,
        LIFELINK Team
        ";
        
        return $this->sendEmail($email, $subject, $message);
    }
    
    private function sendEmail($email, $subject, $message) {
        try {
            $mail = $this->createMailer();
            
            echo "Setting up email...\n";
            $mail->addAddress($email);
            $mail->Subject = $subject;
            $mail->Body = $message;
            
            echo "Attempting to send email to $email...\n";
            if ($mail->send()) {
                echo "Email sent successfully!\n";
                return true;
            }
        } catch (Exception $e) {
            echo "Error details: " . $e->getMessage() . "\n";
            if (isset($mail)) {
                echo "SMTP Error Info: " . $mail->ErrorInfo . "\n";
            }
            throw $e;
        }
    }
    
    public function sendTestEmail($to) {
        try {
            $mail = $this->createMailer();
            
            $mail->addAddress($to);
            $mail->Subject = 'LifeLink Email Test';
            $mail->Body = '
                <h2>LifeLink Email Test</h2>
                <p>This is a test email from LifeLink system.</p>
                <p>If you received this email, it means your email configuration is working correctly!</p>
                <br>
                <p>Best regards,<br>LifeLink Team</p>
            ';
            
            return $mail->send();
        } catch (Exception $e) {
            error_log("Error sending test email: " . $e->getMessage());
            throw $e;
        }
    }
}
