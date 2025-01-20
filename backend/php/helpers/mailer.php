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
        $mail->Username = 'yourlifelink.org@gmail.com';
        $mail->Password = 'gfnb wnxc pmgj eikm';
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
}
