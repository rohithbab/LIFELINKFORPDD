<?php
namespace LifeLink\Helpers;

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;
use PHPMailer\PHPMailer\Exception;
use React\EventLoop\Factory;
use React\Promise\Promise;
use React\Promise\Deferred;

require __DIR__ . '/../../../vendor/autoload.php';

class EmailSender {
    private $mail;
    private $loop;

    public function __construct() {
        $this->mail = new PHPMailer(true);
        $this->loop = Factory::create();

        try {
            // SMTP configuration with extensive debugging
            $this->mail->SMTPDebug = SMTP::DEBUG_SERVER;  // Enable verbose debug output
            $this->mail->Debugoutput = function($str, $level) {
                error_log("PHPMailer Debug ($level): $str");
            };

            $this->mail->isSMTP();
            $this->mail->Host = 'smtp.gmail.com';
            $this->mail->SMTPAuth = true;
            $this->mail->Username = 'yourlifelink.org@gmail.com';
            
            // Use App Password instead of regular password
            $this->mail->Password = 'rnda lowl zgel ddim';
            
            $this->mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
            $this->mail->Port = 587;

            // Additional SMTP configuration
            $this->mail->SMTPOptions = [
                'ssl' => [
                    'verify_peer' => false,
                    'verify_peer_name' => false,
                    'allow_self_signed' => true
                ]
            ];
        } catch (Exception $e) {
            error_log("SMTP Configuration Error: " . $e->getMessage());
        }
    }

    public function sendODMLUpdateEmail($email, $name, $type, $odmlId) {
        $deferred = new Deferred();

        try {
            // Reset previous configurations
            $this->mail->clearAddresses();
            $this->mail->clearAttachments();

            $this->mail->setFrom('yourlifelink.org@gmail.com', 'LifeLink');
            $this->mail->addAddress($email, $name);
            $this->mail->Subject = ucfirst($type) . ' Registration Approved - ODML ID Assigned';

            // Select appropriate email template
            $templatePaths = [
                __DIR__ . '/../../../email_templates/' . $type . '_approval.html',
                __DIR__ . '/../../../email_templates/approval.html'
            ];

            $templateContent = null;
            foreach ($templatePaths as $path) {
                if (file_exists($path)) {
                    $templateContent = file_get_contents($path);
                    break;
                }
            }

            if ($templateContent === null) {
                throw new Exception("No email template found for type: $type");
            }

            // Replace placeholders in the template
            $templateContent = str_replace(['{{name}}', '{{odmlId}}'], [$name, $odmlId], $templateContent);

            $this->mail->Body = $templateContent;
            $this->mail->isHTML(true);

            // Asynchronous email sending
            $this->loop->addTimer(0, function() use ($deferred, $email, $type) {
                try {
                    $result = $this->mail->send();
                    
                    if (!$result) {
                        $errorMessage = "Email sending failed for $type to $email: " . $this->mail->ErrorInfo;
                        error_log($errorMessage);
                        $deferred->reject(new Exception($errorMessage));
                    } else {
                        error_log("ODML Update Email sent successfully to $email for $type");
                        $deferred->resolve(true);
                    }
                } catch (Exception $e) {
                    $errorMessage = "ODML Update Email Error for $type: " . $e->getMessage();
                    error_log($errorMessage);
                    $deferred->reject($e);
                }
            });
        } catch (Exception $e) {
            $deferred->reject($e);
        }

        return $deferred->promise();
    }

    public function sendMultipleEmails($emailData) {
        $promises = [];
        
        foreach ($emailData as $data) {
            $promises[] = $this->sendODMLUpdateEmail(
                $data['email'], 
                $data['name'], 
                $data['type'], 
                $data['odmlId']
            );
        }

        return \React\Promise\all($promises);
    }

    public function run() {
        $this->loop->run();
    }
}