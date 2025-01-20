<?php

class EmailValidator {
    private $api_key = '744f54c7cc58474d8b0417694eead3b5'; // Replace with your actual API key

    public function validateEmail($email) {
        $url = "https://emailvalidation.abstractapi.com/v1/?api_key=" . $this->api_key . "&email=" . urlencode($email);
        
        // Use file_get_contents with stream context
        $context = stream_context_create([
            'http' => [
                'method' => 'GET',
                'header' => [
                    'User-Agent: PHP'
                ]
            ],
            'ssl' => [
                'verify_peer' => false,
                'verify_peer_name' => false
            ]
        ]);
        
        $response = @file_get_contents($url, false, $context);
        
        if ($response === false) {
            throw new Exception("Failed to validate email: Unable to connect to validation service");
        }
        
        $result = json_decode($response, true);
        
        // Check if the API request was successful
        if (!$result || !isset($result['deliverability'])) {
            throw new Exception("Failed to validate email: Invalid API response");
        }
        
        // Check various validation aspects
        $is_valid = (
            $result['deliverability'] === 'DELIVERABLE' &&
            $result['is_disposable_email']['value'] === false &&
            $result['is_free_email']['value'] === true && // Allow free email providers like Gmail
            $result['is_valid_format']['value'] === true
        );
        
        if (!$is_valid) {
            $reasons = [];
            if ($result['deliverability'] !== 'DELIVERABLE') {
                $reasons[] = "Email address appears to be undeliverable";
            }
            if ($result['is_disposable_email']['value'] === true) {
                $reasons[] = "Disposable email addresses are not allowed";
            }
            if ($result['is_valid_format']['value'] === false) {
                $reasons[] = "Invalid email format";
            }
            
            throw new Exception("Invalid email: " . implode(", ", $reasons));
        }
        
        return true;
    }
}
