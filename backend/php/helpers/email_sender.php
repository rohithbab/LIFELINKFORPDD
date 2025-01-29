use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require __DIR__ . '/../../../vendor/autoload.php'; // Adjusted path to autoload.php

class EmailSender {
    private $mail;

    public function __construct() {
        $this->mail = new PHPMailer(true);
        // SMTP configuration
        $this->mail->isSMTP();
        $this->mail->Host = 'smtp.gmail.com'; // Corrected SMTP server
        $this->mail->SMTPAuth = true;
        $this->mail->Username = 'yourlifelink.org@gmail.com'; // Corrected email
        $this->mail->Password = 'rnda lowl zgel ddim';
        $this->mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
        $this->mail->Port = 587; // TCP port to connect to
    }

    public function sendODMLUpdateEmail($email, $name, $type, $odmlId) {
        try {
            // Reset any previous configurations
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

            // Send the email
            $result = $this->mail->send();
            
            if (!$result) {
                error_log("Email sending failed for $type to $email: " . $this->mail->ErrorInfo);
                return false;
            }

            error_log("ODML Update Email sent successfully to $email for $type");
            return true;
        } catch (Exception $e) {
            error_log("ODML Update Email Error for $type: " . $e->getMessage());
            return false;
        }
    }
}