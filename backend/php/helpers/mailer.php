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
            $mail->addAddress($email);
            $mail->Subject = 'Your ODML ID Assignment - LifeLink';
            
            // Load and populate template
            $template = file_get_contents($this->getTemplatePath('odml_assignment.html'));
            $template = str_replace('{NAME}', $name, $template);
            $template = str_replace('{ODML_ID}', $odmlId, $template);
            $template = str_replace('{USER_TYPE}', ucfirst($userType), $template);
            
            $mail->Body = $template;
            $mail->send();
            return true;
        } catch (Exception $e) {
            throw new Exception("Email could not be sent. Mailer Error: {$mail->ErrorInfo}");
        }
    }
    
    public function sendApprovalNotification($email, $name, $userType, $odmlId) {
        try {
            $mail = $this->createMailer();
            $mail->addAddress($email);
            $mail->Subject = 'Account Approved - LifeLink';
            
            // Load and populate template
            $template = file_get_contents($this->getTemplatePath('approval_notification.html'));
            $template = str_replace('{NAME}', $name, $template);
            $template = str_replace('{USER_TYPE}', ucfirst($userType), $template);
            $template = str_replace('{ODML_ID}', $odmlId, $template);
            
            $mail->Body = $template;
            $mail->send();
            return true;
        } catch (Exception $e) {
            throw new Exception("Email could not be sent. Mailer Error: {$mail->ErrorInfo}");
        }
    }
    
    public function sendRejectionNotification($email, $name, $userType, $reason) {
        try {
            $mail = $this->createMailer();
            $mail->addAddress($email);
            $mail->Subject = 'Account Status Update - LifeLink';
            
            // Load and populate template
            $template = file_get_contents($this->getTemplatePath('rejection_notification.html'));
            $template = str_replace('{NAME}', $name, $template);
            $template = str_replace('{USER_TYPE}', ucfirst($userType), $template);
            $template = str_replace('{REASON}', $reason, $template);
            
            $mail->Body = $template;
            $mail->send();
            return true;
        } catch (Exception $e) {
            throw new Exception("Email could not be sent. Mailer Error: {$mail->ErrorInfo}");
        }
    }
}
