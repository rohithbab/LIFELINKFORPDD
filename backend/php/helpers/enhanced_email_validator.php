<?php

class EnhancedEmailValidator {
    private $disposable_domains = [
        'tempmail.com', 'temp-mail.org', 'guerrillamail.com', 'sharklasers.com',
        'mailinator.com', 'yopmail.com', 'throwawaymail.com', '10minutemail.com',
        'tempmail.net', 'tempmail.dev', 'dispostable.com', 'mailnesia.com',
        'tempmailaddress.com', 'tempmail.io', 'temp-mail.net', 'fakeinbox.com'
    ];

    private $common_typos = [
        'gmial' => 'gmail',
        'gmal' => 'gmail',
        'gamil' => 'gmail',
        'gnail' => 'gmail',
        'gmaill' => 'gmail',
        'yahooo' => 'yahoo',
        'yaho' => 'yahoo',
        'yaaho' => 'yahoo',
        'hotmial' => 'hotmail',
        'hotmal' => 'hotmail',
        'hotmai' => 'hotmail',
        'outlok' => 'outlook',
        'outlock' => 'outlook',
        'outloook' => 'outlook'
    ];

    private $pdo;
    private $api_key;

    public function __construct($pdo, $api_key = null) {
        $this->pdo = $pdo;
        $this->api_key = $api_key;
    }

    public function validateEmailAPI($email) {
        if (!$this->api_key) {
            return true; // Skip API validation if no key provided
        }

        // Using Abstract API's email validation
        $url = "https://emailvalidation.abstractapi.com/v1/?api_key=" . $this->api_key . "&email=" . urlencode($email);
        
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
        
        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        if ($httpCode === 200) {
            $result = json_decode($response, true);
            
            // Check if the email is deliverable
            if (isset($result['deliverability']) && $result['deliverability'] === "UNDELIVERABLE") {
                throw new Exception("This email address appears to be invalid or inactive.");
            }
            
            // Check if it's a disposable email
            if (isset($result['is_disposable_email']) && $result['is_disposable_email'] === true) {
                throw new Exception("Disposable email addresses are not allowed.");
            }
            
            // Check if it's a free email
            if (isset($result['is_free_email']) && $result['is_free_email'] === false) {
                // You might want to log non-free email domains for verification
                error_log("Non-free email domain detected: " . $email);
            }
            
            return true;
        }
        
        return true; // If API fails, fallback to basic validation
    }

    public function validateEmail($email, $table, $column = 'email') {
        // Convert to lowercase
        $email = strtolower(trim($email));

        // Basic format validation
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            throw new Exception("Invalid email format. Please enter a valid email address.");
        }

        // Extract domain
        $domain = substr(strrchr($email, "@"), 1);

        // Check for disposable email domains
        if (in_array($domain, $this->disposable_domains)) {
            throw new Exception("Disposable email addresses are not allowed. Please use your regular email address.");
        }

        // Check domain has valid MX record
        if (!checkdnsrr($domain, "MX")) {
            throw new Exception("Invalid email domain. Please check your email address.");
        }

        // API validation if key is provided
        $this->validateEmailAPI($email);

        // Check for common typos and suggest corrections
        $local_part = substr($email, 0, strpos($email, '@'));
        $suggested_domain = null;
        foreach ($this->common_typos as $typo => $correct) {
            if (strpos($domain, $typo) !== false) {
                $suggested_domain = str_replace($typo, $correct, $domain);
                throw new Exception("Did you mean " . $local_part . "@" . $suggested_domain . "?");
            }
        }

        // Check for duplicate email
        $stmt = $this->pdo->prepare("SELECT COUNT(*) FROM $table WHERE $column = ?");
        $stmt->execute([$email]);
        if ($stmt->fetchColumn() > 0) {
            throw new Exception("This email address is already registered. Please use a different email or try logging in.");
        }

        return true;
    }

    public function validateEmailFormat($email) {
        $email = strtolower(trim($email));
        return filter_var($email, FILTER_VALIDATE_EMAIL) !== false;
    }

    public function suggestCorrection($email) {
        $suggestions = [];
        $domain = substr(strrchr($email, "@"), 1);
        $local_part = substr($email, 0, strpos($email, '@'));

        foreach ($this->common_typos as $typo => $correct) {
            if (strpos($domain, $typo) !== false) {
                $suggestions[] = $local_part . "@" . str_replace($typo, $correct, $domain);
            }
        }

        return $suggestions;
    }

    public function isDisposableEmail($email) {
        $domain = substr(strrchr($email, "@"), 1);
        return in_array($domain, $this->disposable_domains);
    }
}
