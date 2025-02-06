<?php
require_once __DIR__ . '/../PHPMailer/PHPMailer.php';
require_once __DIR__ . '/../PHPMailer/SMTP.php';
require_once __DIR__ . '/../PHPMailer/Exception.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;
use PHPMailer\PHPMailer\Exception;

class Mailer {
    private $testMode = false;
    private $shouldFail = false;
    private $mailer = null;
    private $lastError = null;

    public function setTestMode($enabled, $shouldFail = false) {
        $this->testMode = $enabled;
        $this->shouldFail = $shouldFail;
    }

    public function getLastError() {
        return $this->lastError;
    }

    public function testConnection() {
        try {
            error_log("Testing SMTP connection...");
            $mail = $this->createMailer();
            
            if (!$mail->smtpConnect()) {
                $this->lastError = "Failed to connect to SMTP server";
                error_log($this->lastError);
                return false;
            }
            
            error_log("SMTP connection test successful");
            return true;
            
        } catch (Exception $e) {
            $this->lastError = "SMTP Connection Error: " . $e->getMessage();
            error_log($this->lastError);
            return false;
        }
    }

    private function createMailer() {
        try {
            error_log("Creating new mailer instance");
            
            if ($this->testMode && $this->shouldFail) {
                throw new Exception("Test mode: Simulating email failure");
            }

            // Close existing connection if any
            if ($this->mailer !== null) {
                try {
                    $this->mailer->smtpClose();
                } catch (Exception $e) {
                    error_log("Warning: Failed to close existing SMTP connection: " . $e->getMessage());
                }
            }

            $mail = new PHPMailer(true);
            
            // Enable verbose debug output
            $mail->SMTPDebug = SMTP::DEBUG_SERVER;
            $mail->Debugoutput = function($str, $level) {
                error_log("PHPMailer ($level): $str");
            };
            
            $mail->isSMTP();
            $mail->Host = 'smtp.gmail.com';
            $mail->Port = 587;
            $mail->SMTPAuth = true;
            $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
            
            // Gmail credentials
            $mail->Username = 'yourlifelink.org@gmail.com';
            $mail->Password = 'abiy ulmu umbf owhn';
            
            // Add extra debugging and SSL options
            $mail->SMTPOptions = array(
                'ssl' => array(
                    'verify_peer' => false,
                    'verify_peer_name' => false,
                    'allow_self_signed' => true
                )
            );
            
            // Default settings
            $mail->setFrom('yourlifelink.org@gmail.com', 'LifeLink Admin');
            $mail->isHTML(true);
            $mail->CharSet = 'UTF-8';
            
            // Store the mailer instance
            $this->mailer = $mail;
            
            error_log("Mailer instance created successfully");
            return $mail;
            
        } catch (Exception $e) {
            $this->lastError = "Email Configuration Error: " . $e->getMessage();
            error_log($this->lastError);
            throw new Exception($this->lastError);
        }
    }

    private function sendEmail($to, $subject, $body) {
        error_log("Attempting to send email to: " . $to);
        error_log("Subject: " . $subject);
        
        try {
            $mail = $this->createMailer();
            
            // Clear all recipients first
            $mail->clearAllRecipients();
            
            // Set the recipient
            $mail->addAddress($to);
            error_log("Added recipient: " . $to);
            
            // Set subject and body
            $mail->Subject = $subject;
            $mail->Body = $body;
            
            error_log("Attempting to send email...");
            
            // Try to send
            if (!$mail->send()) {
                $this->lastError = "Email Send Failed: " . $mail->ErrorInfo;
                error_log($this->lastError);
                throw new Exception($this->lastError);
            }
            
            error_log("Email sent successfully to: " . $to);
            return true;
            
        } catch (Exception $e) {
            $this->lastError = "Send Email Failed: " . $e->getMessage();
            error_log($this->lastError);
            throw $e;
            
        } finally {
            // Always try to close the connection
            if ($this->mailer !== null) {
                try {
                    $this->mailer->smtpClose();
                    error_log("SMTP connection closed");
                } catch (Exception $e) {
                    error_log("Warning: Failed to close SMTP connection: " . $e->getMessage());
                }
            }
        }
    }

    public function sendHospitalApprovalEmail($email, $hospitalName, $odmlId) {
        error_log("Preparing hospital approval email for: " . $hospitalName);
        
        $subject = "Hospital Registration Approved - LifeLink";
        
        $body = "
        <div style='font-family: Arial, sans-serif; max-width: 600px; margin: 0 auto;'>
            <h2 style='color: #2c3e50;'>Hospital Registration Approved</h2>
            <p>Dear {$hospitalName},</p>
            <p>We are pleased to inform you that your hospital registration with LifeLink has been approved.</p>
            <p><strong>Your ODML ID is: {$odmlId}</strong></p>
            <p>Please keep this ID for your records. You will need it for future interactions with the LifeLink system.</p>
            <p>You can now log in to your account and start using our services.</p>
            <p>Best regards,<br>The LifeLink Team</p>
        </div>";
        
        return $this->sendEmail($email, $subject, $body);
    }

    public function sendDonorApprovalEmail($email, $donorName, $odmlId) {
        error_log("Preparing donor approval email for: " . $donorName);
        
        $subject = "Donor Registration Approved - LifeLink";
        
        $body = "
        <div style='font-family: Arial, sans-serif; max-width: 600px; margin: 0 auto;'>
            <h2 style='color: #2c3e50;'>Donor Registration Approved</h2>
            <p>Dear {$donorName},</p>
            <p>We are pleased to inform you that your donor registration with LifeLink has been approved.</p>
            <p><strong>Your ODML ID is: {$odmlId}</strong></p>
            <p>Please keep this ID for your records. You will need it for future interactions with the LifeLink system.</p>
            <p>Thank you for your noble decision to become an organ donor.</p>
            <p>Best regards,<br>The LifeLink Team</p>
        </div>";
        
        return $this->sendEmail($email, $subject, $body);
    }

    public function sendRecipientApprovalEmail($email, $recipientName, $odmlId) {
        error_log("Preparing recipient approval email for: " . $recipientName);
        
        $subject = "Recipient Registration Approved - LifeLink";
        
        $body = "
        <div style='font-family: Arial, sans-serif; max-width: 600px; margin: 0 auto;'>
            <h2 style='color: #2c3e50;'>Recipient Registration Approved</h2>
            <p>Dear {$recipientName},</p>
            <p>We are pleased to inform you that your recipient registration with LifeLink has been approved.</p>
            <p><strong>Your ODML ID is: {$odmlId}</strong></p>
            <p>Please keep this ID for your records. You will need it for future interactions with the LifeLink system.</p>
            <p>We will notify you when a suitable organ match becomes available.</p>
            <p>Best regards,<br>The LifeLink Team</p>
        </div>";
        
        return $this->sendEmail($email, $subject, $body);
    }
}
