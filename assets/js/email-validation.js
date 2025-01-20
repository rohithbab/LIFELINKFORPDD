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
