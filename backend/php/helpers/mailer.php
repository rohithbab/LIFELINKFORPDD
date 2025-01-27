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
            
            // Basic settings
            $mail->isSMTP();
            $mail->Host = 'smtp.gmail.com';
            
            // Try SSL instead of TLS
            $mail->SMTPSecure = 'ssl';
            $mail->Port = 465;  // SSL port
            
            // Enable debugging
            $mail->SMTPDebug = 2; // Enable verbose debug output
            
            // Auth settings
            $mail->SMTPAuth = true;
            $mail->Username = 'yourlifelink.org@gmail.com';
            $mail->Password = 'rnda lowl zgel ddim';
            
            // Timeout settings
            $mail->Timeout = 30;
            $mail->SMTPKeepAlive = true;
            
            // Email settings
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
            echo "Creating mailer...<br>";
            $mail = $this->createMailer();
            
            echo "Setting up test email...<br>";
            $mail->addAddress($to);
            $mail->Subject = 'LifeLink Test Email';
            $mail->Body = '<h2>LifeLink Email Test</h2>
                          <p>This is a test email from the LifeLink system.</p>
                          <p>If you received this email, it means your email configuration is working correctly!</p>';
            $mail->AltBody = 'This is a test email from LifeLink system.';
            
            echo "Attempting to send...<br>";
            if (!$mail->send()) {
                throw new Exception($mail->ErrorInfo);
            }
            
            echo "Email sent successfully!<br>";
            return true;
            
        } catch (Exception $e) {
            $error = "Failed to send test email: " . $e->getMessage();
            error_log($error);
            echo "<strong>Error:</strong> $error<br>";
            return false;
        }
    }

    public function sendEmail($to, $subject, $template, $data = []) {
        try {
            $mail = $this->createMailer();
            $mail->addAddress($to);
            $mail->Subject = $subject;
            
            // Get template content
            $templatePath = $this->getTemplatePath($template);
            if (!file_exists($templatePath)) {
                throw new Exception("Email template not found: $template");
            }
            
            $content = file_get_contents($templatePath);
            
            // Replace placeholders with data
            foreach ($data as $key => $value) {
                $content = str_replace("{{" . $key . "}}", $value, $content);
            }
            
            $mail->Body = $content;
            return $mail->send();
        } catch (Exception $e) {
            error_log("Error sending email: " . $e->getMessage());
            throw new Exception("Failed to send email: " . $e->getMessage());
        }
    }

    private function getTemplatePath($template) {
        return __DIR__ . '/../../../email_templates/' . $template . '.html';
    }

    // Hospital emails
    public function sendHospitalApproval($email, $hospitalName, $odmlId) {
        try {
            $mail = $this->createMailer();
            $mail->addAddress($email);
            $mail->Subject = 'LifeLink - Hospital Registration Approved';
            
            $replacements = [
                '{HOSPITAL_NAME}' => $hospitalName,
                '{ODML_ID}' => $odmlId
            ];
            
            $mail->Body = $this->loadTemplate('hospital_approval.html', $replacements);
            return $mail->send();
        } catch (Exception $e) {
            error_log("Error sending hospital approval email: " . $e->getMessage());
            throw $e;
        }
    }

    public function sendHospitalRejection($email, $hospitalName, $reason) {
        try {
            $mail = $this->createMailer();
            $mail->addAddress($email);
            $mail->Subject = 'LifeLink - Hospital Registration Update';
            
            $replacements = [
                '{HOSPITAL_NAME}' => $hospitalName,
                '{REASON}' => $reason
            ];
            
            $mail->Body = $this->loadTemplate('hospital_rejection.html', $replacements);
            return $mail->send();
        } catch (Exception $e) {
            error_log("Error sending hospital rejection email: " . $e->getMessage());
            throw $e;
        }
    }

    // Donor emails
    public function sendDonorApproval($email, $donorName, $odmlId) {
        try {
            $mail = $this->createMailer();
            $mail->addAddress($email);
            $mail->Subject = 'LifeLink - Donor Registration Approved';
            
            $replacements = [
                '{DONOR_NAME}' => $donorName,
                '{ODML_ID}' => $odmlId
            ];
            
            $mail->Body = $this->loadTemplate('donor_approval.html', $replacements);
            return $mail->send();
        } catch (Exception $e) {
            error_log("Error sending donor approval email: " . $e->getMessage());
            throw $e;
        }
    }

    public function sendDonorRejection($email, $donorName, $reason) {
        try {
            $mail = $this->createMailer();
            $mail->addAddress($email);
            $mail->Subject = 'LifeLink - Donor Registration Update';
            
            $replacements = [
                '{DONOR_NAME}' => $donorName,
                '{REASON}' => $reason
            ];
            
            $mail->Body = $this->loadTemplate('donor_rejection.html', $replacements);
            return $mail->send();
        } catch (Exception $e) {
            error_log("Error sending donor rejection email: " . $e->getMessage());
            throw $e;
        }
    }

    // Recipient emails
    public function sendRecipientApproval($email, $recipientName, $odmlId) {
        try {
            $mail = $this->createMailer();
            $mail->addAddress($email);
            $mail->Subject = 'LifeLink - Recipient Registration Approved';
            
            $replacements = [
                '{RECIPIENT_NAME}' => $recipientName,
                '{ODML_ID}' => $odmlId
            ];
            
            $mail->Body = $this->loadTemplate('recipient_approval.html', $replacements);
            return $mail->send();
        } catch (Exception $e) {
            error_log("Error sending recipient approval email: " . $e->getMessage());
            throw $e;
        }
    }

    public function sendRecipientRejection($email, $recipientName, $reason) {
        try {
            $mail = $this->createMailer();
            $mail->addAddress($email);
            $mail->Subject = 'LifeLink - Recipient Registration Update';
            
            $replacements = [
                '{RECIPIENT_NAME}' => $recipientName,
                '{REASON}' => $reason
            ];
            
            $mail->Body = $this->loadTemplate('recipient_rejection.html', $replacements);
            return $mail->send();
        } catch (Exception $e) {
            error_log("Error sending recipient rejection email: " . $e->getMessage());
            throw $e;
        }
    }

    private function loadTemplate($templateFile, $replacements) {
        $template = file_get_contents($this->getTemplatePath($templateFile));
        if ($template === false) {
            throw new Exception("Could not load template: $templateFile");
        }
        
        return str_replace(array_keys($replacements), array_values($replacements), $template);
    }
}
