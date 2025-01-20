<?php
class EmailValidator {
    private $disposableDomains = [
        'tempmail.com', 'temp-mail.org', 'throwawaymail.com',
        'mailinator.com', 'yopmail.com', 'guerrillamail.com'
    ];
    
    public function validateEmail($email) {
        // Basic format validation
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            return [
                'valid' => false,
                'message' => 'Invalid email format'
            ];
        }

        // Get domain
        $domain = substr(strrchr($email, "@"), 1);

        // Check for disposable email domains
        if (in_array(strtolower($domain), $this->disposableDomains)) {
            return [
                'valid' => false,
                'message' => 'Disposable email addresses are not allowed'
            ];
        }

        // Check domain has valid MX record
        if (!checkdnsrr($domain, 'MX')) {
            return [
                'valid' => false,
                'message' => 'Invalid email domain'
            ];
        }

        return [
            'valid' => true,
            'message' => 'Email is valid'
        ];
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
