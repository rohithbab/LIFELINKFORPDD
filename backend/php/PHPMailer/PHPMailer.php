<?php
namespace PHPMailer\PHPMailer;

class PHPMailer {
    public $Version = '6.8.0';
    public $ErrorInfo = '';
    public $Mailer = 'mail';
    public $Host = 'localhost';
    public $Port = 25;
    public $SMTPAuth = false;
    public $Username = '';
    public $Password = '';
    public $SMTPSecure = '';
    public $Sender = '';
    public $Subject = '';
    public $Body = '';
    public $AltBody = '';
    public $From = 'root@localhost';
    public $FromName = 'Root User';
    protected $to = array();
    protected $MIMEBody = '';
    protected $MIMEHeader = '';
    protected $smtp = null;
    protected $isHTML = true;

    public function __construct($exceptions = null) {
        $this->exceptions = ($exceptions == true);
    }

    public function isSMTP() {
        $this->Mailer = 'smtp';
        return true;
    }

    public function setFrom($address, $name = '') {
        $this->From = $address;
        $this->FromName = $name;
        return true;
    }

    public function addAddress($address, $name = '') {
        $this->to[] = array($address, $name);
        return true;
    }

    public function isHTML($isHTML = true) {
        $this->isHTML = $isHTML;
    }

    public function send() {
        try {
            if ($this->Mailer == 'smtp') {
                return $this->smtpSend();
            } else {
                return $this->mailSend();
            }
        } catch (Exception $e) {
            $this->ErrorInfo = $e->getMessage();
            return false;
        }
    }

    protected function smtpSend() {
        $this->smtp = new SMTP();
        
        // Enable debug output
        echo "Connecting to SMTP server...\n";
        
        if (!$this->smtp->connect($this->Host, $this->Port)) {
            throw new Exception('SMTP connection failed: ' . implode(", ", $this->smtp->getError()));
        }
        
        echo "Connected to SMTP server\n";
        
        if ($this->SMTPAuth) {
            echo "Authenticating...\n";
            if (!$this->smtp->authenticate($this->Username, $this->Password)) {
                throw new Exception('SMTP authentication failed: ' . implode(", ", $this->smtp->getError()));
            }
            echo "Authentication successful\n";
        }

        foreach ($this->to as $recipient) {
            list($address, $name) = $recipient;
            if (!$this->smtp->mail($this->From)) {
                throw new Exception('SMTP MAIL FROM command failed: ' . implode(", ", $this->smtp->getError()));
            }
            if (!$this->smtp->recipient($address)) {
                throw new Exception('SMTP RCPT TO command failed: ' . implode(", ", $this->smtp->getError()));
            }
            if (!$this->smtp->data($this->createHeader() . $this->Body)) {
                throw new Exception('SMTP DATA command failed: ' . implode(", ", $this->smtp->getError()));
            }
        }

        $this->smtp->quit();
        return true;
    }

    protected function mailSend() {
        $to = '';
        foreach ($this->to as $recipient) {
            list($address, $name) = $recipient;
            $to .= ($to ? ', ' : '') . ($name ? "$name <$address>" : $address);
        }
        
        $headers = $this->createHeader();
        return mail($to, $this->Subject, $this->Body, $headers);
    }

    protected function createHeader() {
        $header = "From: {$this->FromName} <{$this->From}>\r\n";
        $header .= "Reply-To: {$this->From}\r\n";
        $header .= "Subject: {$this->Subject}\r\n";
        $header .= "MIME-Version: 1.0\r\n";
        if ($this->isHTML) {
            $header .= "Content-Type: text/html; charset=UTF-8\r\n";
        } else {
            $header .= "Content-Type: text/plain; charset=UTF-8\r\n";
        }
        $header .= "\r\n";
        return $header;
    }
}
