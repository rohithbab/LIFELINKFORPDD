function validateEmail(emailInput, validationEndpoint) {
    const email = emailInput.value.trim();
    const feedbackElement = emailInput.nextElementSibling;
    
    // Reset validation state
    emailInput.classList.remove('is-valid', 'is-invalid');
    if (feedbackElement) {
        feedbackElement.remove();
    }

    // Create feedback element
    const feedback = document.createElement('div');
    feedback.style.position = 'absolute';
    feedback.style.right = '10px';
    feedback.style.top = '50%';
    feedback.style.transform = 'translateY(-50%)';
    
    // Basic format validation
    const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
    if (!emailRegex.test(email)) {
        showInvalid(emailInput, feedback, 'Please enter a valid email address');
        return;
    }

    // Show loading spinner
    feedback.innerHTML = '<i class="fas fa-spinner fa-spin"></i>';
    feedback.className = 'validation-feedback';
    emailInput.parentElement.appendChild(feedback);

    // Call the validation endpoint
    fetch(validationEndpoint + '?email=' + encodeURIComponent(email))
        .then(response => response.json())
        .then(data => {
            if (data.valid) {
                showValid(emailInput, feedback);
            } else {
                showInvalid(emailInput, feedback, data.message);
            }
        })
        .catch(error => {
            console.error('Email validation error:', error);
            showInvalid(emailInput, feedback, 'Error validating email');
        });
}

function showValid(input, feedback) {
    input.classList.add('is-valid');
    input.classList.remove('is-invalid');
    feedback.innerHTML = '<i class="fas fa-check-circle" style="color: #28a745;"></i>';
}

function showInvalid(input, feedback, message) {
    input.classList.add('is-invalid');
    input.classList.remove('is-valid');
    feedback.innerHTML = '<i class="fas fa-times-circle" style="color: #dc3545;"></i>';
    
    // Show tooltip with error message
    const tooltip = document.createElement('div');
    tooltip.className = 'invalid-tooltip';
    tooltip.textContent = message;
    feedback.appendChild(tooltip);
}

// Debounce function to limit API calls
function debounce(func, wait) {
    let timeout;
    return function executedFunction(...args) {
        const later = () => {
            clearTimeout(timeout);
            func(...args);
        };
        clearTimeout(timeout);
        timeout = setTimeout(later, wait);
    };
}

// Initialize email validation on forms
document.addEventListener('DOMContentLoaded', function() {
    const emailInputs = document.querySelectorAll('input[type="email"]');
    emailInputs.forEach(input => {
        const form = input.closest('form');
        const validationEndpoint = form.dataset.validationEndpoint || '/backend/php/validate_email.php';
        
        // Add validation styles to input container
        const container = input.parentElement;
        container.style.position = 'relative';
        
        // Add debounced validation on input
        input.addEventListener('input', debounce(function() {
            validateEmail(input, validationEndpoint);
        }, 500));
    });
});
