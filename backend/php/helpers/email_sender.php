<?php
namespace LifeLink\Helpers;

// Direct includes instead of using composer autoloader
require_once __DIR__ . '/../../../vendor/phpmailer/phpmailer/src/PHPMailer.php';
require_once __DIR__ . '/../../../vendor/phpmailer/phpmailer/src/SMTP.php';
require_once __DIR__ . '/../../../vendor/phpmailer/phpmailer/src/Exception.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;
use PHPMailer\PHPMailer\Exception;

class EmailSender {
    private $mail;

    public function __construct() {
        try {
            error_log("Initializing PHPMailer...");
            $this->mail = new PHPMailer(true);

            // Server settings
            $this->mail->SMTPDebug = SMTP::DEBUG_SERVER;  // Enable verbose debug output
            $this->mail->Debugoutput = function($str, $level) {
                error_log("PHPMailer Debug ($level): $str");
            };

            $this->mail->isSMTP();
            $this->mail->Host = 'smtp.gmail.com';
            $this->mail->SMTPAuth = true;
            $this->mail->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS; // Use SSL instead of TLS
            $this->mail->Port = 465; // SSL port
            
            // Gmail credentials
            $this->mail->Username = 'yourlifelink.org@gmail.com';
            $this->mail->Password = 'raeorxsahiysbkxd'; // App password without spaces
            
            // Additional SMTP settings for reliability
            $this->mail->Timeout = 60;
            $this->mail->SMTPKeepAlive = true;
            $this->mail->SMTPOptions = array(
                'ssl' => array(
                    'verify_peer' => false,
                    'verify_peer_name' => false,
                    'allow_self_signed' => true
                )
            );

            $this->mail->setFrom('yourlifelink.org@gmail.com', 'LifeLink');
            error_log("PHPMailer initialized successfully");
        } catch (Exception $e) {
            error_log("SMTP Configuration Error: " . $e->getMessage());
            error_log("Stack trace: " . $e->getTraceAsString());
            throw $e;
        }
    }

    public function sendODMLUpdateEmail($email, $name, $type, $odmlId) {
        try {
            error_log("Starting email send process for $type to $email");
            
            if (!$this->mail) {
                throw new Exception("PHPMailer not properly initialized");
            }
            
            // Reset previous settings
            $this->mail->clearAddresses();
            $this->mail->clearAttachments();
            
            // Set recipient
            $this->mail->addAddress($email, $name);
            $this->mail->Subject = ucfirst($type) . ' Registration Approved - ODML ID Assigned';

            error_log("Email basic setup complete. Looking for template...");

            // Select appropriate email template
            $templatePaths = [
                __DIR__ . '/../../../email_templates/' . $type . '_approval.html',
                __DIR__ . '/../../../email_templates/approval.html'
            ];

            error_log("Checking template paths: " . json_encode($templatePaths));

            $templateContent = null;
            foreach ($templatePaths as $path) {
                error_log("Checking template path: $path");
                if (file_exists($path)) {
                    error_log("Found template at: $path");
                    $templateContent = file_get_contents($path);
                    break;
                }
            }

            if ($templateContent === null) {
                throw new Exception("No email template found for type: $type. Checked paths: " . implode(', ', $templatePaths));
            }

            // Replace placeholders in the template with correct format
            $templateContent = str_replace(
                ['{DONOR_NAME}', '{ODML_ID}'], 
                [$name, $odmlId], 
                $templateContent
            );
            error_log("Template content prepared with replacements");

            $this->mail->isHTML(true);
            $this->mail->Body = $templateContent;

            error_log("Attempting to send email...");
            
            try {
                $result = $this->mail->send();
                error_log("Email send attempt completed");
                
                if (!$result) {
                    $errorMessage = "Email sending failed for $type to $email: " . $this->mail->ErrorInfo;
                    error_log($errorMessage);
                    return false;
                }
                
                error_log("ODML Update Email sent successfully to $email for $type");
                return true;
            } catch (Exception $e) {
                error_log("SMTP Send Error: " . $e->getMessage());
                error_log("SMTP Debug Info: " . $this->mail->ErrorInfo);
                throw $e;
            }

        } catch (Exception $e) {
            error_log("Error in sendODMLUpdateEmail: " . $e->getMessage());
            error_log("Stack trace: " . $e->getTraceAsString());
            return false;
        }
    }

    public function sendMultipleEmails($emailData) {
        $results = [];
        
        foreach ($emailData as $data) {
            $results[] = $this->sendODMLUpdateEmail(
                $data['email'], 
                $data['name'], 
                $data['type'], 
                $data['odmlId']
            );
        }

        return $results;
    }
}