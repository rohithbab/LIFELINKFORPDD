<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

require_once 'backend/php/PHPMailer/PHPMailer.php';
require_once 'backend/php/PHPMailer/SMTP.php';
require_once 'backend/php/PHPMailer/Exception.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;
use PHPMailer\PHPMailer\Exception;

echo "<h2>SMTP Connection Test</h2>";

function testSMTP($port, $secure) {
    echo "<hr><h3>Testing {$secure} on port {$port}</h3>";
    
    try {
        $mail = new PHPMailer();
        $mail->isSMTP();
        
        // Server settings
        $mail->Host = 'smtp.gmail.com';
        $mail->Port = $port;
        $mail->SMTPAuth = true;
        $mail->SMTPSecure = $secure;
        
        // Authentication
        $mail->Username = 'yourlifelink.org@gmail.com';
        $mail->Password = 'rnda lowl zgel ddim';
        
        // Enable debug output
        $mail->SMTPDebug = SMTP::DEBUG_CONNECTION;
        
        // Try to connect
        echo "Attempting connection...<br>";
        if ($mail->smtpConnect()) {
            echo "<p style='color: green'>✅ Connection successful!</p>";
            $mail->smtpClose();
            return true;
        } else {
            echo "<p style='color: red'>❌ Connection failed</p>";
            return false;
        }
    } catch (Exception $e) {
        echo "<p style='color: red'>❌ Error: " . $e->getMessage() . "</p>";
        return false;
    }
}

// Test SSL connection
$ssl_result = testSMTP(465, 'ssl');

// Test TLS connection
$tls_result = testSMTP(587, 'tls');

// If both failed, show additional information
if (!$ssl_result && !$tls_result) {
    echo "<hr><h3>Troubleshooting Information:</h3>";
    echo "<pre>";
    
    // Check OpenSSL
    echo "\nOpenSSL installed: " . (extension_loaded('openssl') ? 'Yes' : 'No');
    echo "\nOpenSSL version: " . OPENSSL_VERSION_TEXT;
    
    // Check if we can reach Gmail
    $fp = fsockopen('smtp.gmail.com', 80, $errno, $errstr, 5);
    echo "\nCan reach Gmail: " . ($fp ? 'Yes' : 'No');
    if ($fp) {
        fclose($fp);
    }
    
    // Show PHP version
    echo "\nPHP version: " . phpversion();
    
    echo "</pre>";
    
    echo "<h3>Suggested Solutions:</h3>";
    echo "<ol>";
    echo "<li>Check if your antivirus or firewall is blocking SMTP connections</li>";
    echo "<li>Try connecting using a mobile hotspot (some ISPs block SMTP)</li>";
    echo "<li>Verify that the Gmail account settings allow less secure app access</li>";
    echo "<li>Double-check the app password</li>";
    echo "</ol>";
}
?>
