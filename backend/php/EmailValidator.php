<?php
require_once __DIR__ . '/../../vendor/autoload.php';

class EmailValidator {
    private $apiKey;
    
    public function __construct() {
        $config = require __DIR__ . '/../../config/email_config.php';
        $this->apiKey = $config['abstract_api_key'];
    }
    
    public function validateEmail($email) {
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            return [
                'valid' => false,
                'message' => 'Invalid email format'
            ];
        }

        try {
            $client = new GuzzleHttp\Client();
            $response = $client->get("https://emailvalidation.abstractapi.com/v1/", [
                'query' => [
                    'api_key' => $this->apiKey,
                    'email' => $email
                ]
            ]);

            $result = json_decode($response->getBody(), true);

            // Check if email is valid and not disposable
            if ($result['deliverability'] === 'UNDELIVERABLE' || $result['is_disposable_email']['value']) {
                return [
                    'valid' => false,
                    'message' => 'Email address is invalid or disposable'
                ];
            }

            return [
                'valid' => true,
                'message' => 'Email is valid'
            ];
        } catch (Exception $e) {
            // If API call fails, fall back to basic validation
            return [
                'valid' => true,
                'message' => 'Basic validation passed'
            ];
        }
    }
}

// JavaScript function for frontend validation
function getEmailValidationJS() {
    return <<<JS
    async function validateEmail(email) {
        try {
            const response = await fetch('../backend/php/validate_email.php?email=' + encodeURIComponent(email));
            const result = await response.json();
            return result;
        } catch (error) {
            console.error('Email validation error:', error);
            return { valid: false, message: 'Error validating email' };
        }
    }

    async function handleEmailValidation(inputElement) {
        const email = inputElement.value;
        const result = await validateEmail(email);
        
        const feedbackElement = inputElement.nextElementSibling || document.createElement('div');
        feedbackElement.className = 'validation-feedback';
        
        if (!result.valid) {
            feedbackElement.textContent = result.message;
            feedbackElement.style.color = 'red';
            inputElement.setCustomValidity(result.message);
        } else {
            feedbackElement.textContent = '✓';
            feedbackElement.style.color = 'green';
            inputElement.setCustomValidity('');
        }
        
        if (!inputElement.nextElementSibling) {
            inputElement.parentNode.appendChild(feedbackElement);
        }
    }
    JS;
}
?>
