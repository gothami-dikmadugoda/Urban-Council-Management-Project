document.addEventListener('DOMContentLoaded', function() {
    const addStaffForm = document.getElementById('addStaffForm');
    const emailInput = addStaffForm.querySelector('input[name="email"]');
    const phoneInput = addStaffForm.querySelector('input[name="phone"]');
    const passwordInput = addStaffForm.querySelector('input[name="password"]');
    
    // Email validation
    let emailTimeout;
    emailInput.addEventListener('input', function() {
        clearTimeout(emailTimeout);
        const email = this.value;
        
        // Remove any existing validation messages
        removeValidationMessage(this);
        
        if (email && isValidEmail(email)) {
            emailTimeout = setTimeout(() => {
                checkEmailExists(email);
            }, 500);
        } else if (email) {
            showValidationMessage(this, 'Please enter a valid email address', 'error');
        }
    });

    // Phone validation
    phoneInput.addEventListener('input', function() {
        const phone = this.value;
        removeValidationMessage(this);
        
        if (phone && !isValidPhone(phone)) {
            showValidationMessage(this, 'Please enter a valid phone number', 'error');
        }
    });

    // Password validation
    passwordInput.addEventListener('input', function() {
        const password = this.value;
        removeValidationMessage(this);
        
        if (password && !isValidPassword(password)) {
            showValidationMessage(this, 'Password must be at least 8 characters long and contain letters and numbers', 'error');
        }
    });

    // Form submission
    addStaffForm.addEventListener('submit', function(e) {
        e.preventDefault();
        
        if (validateForm()) {
            this.submit();
        }
    });

    // Validation functions
    function isValidEmail(email) {
        return /^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(email);
    }

    function isValidPhone(phone) {
        return /^[0-9]{10}$/.test(phone.replace(/[^0-9]/g, ''));
    }

    function isValidPassword(password) {
        return password.length >= 8 && /[A-Za-z]/.test(password) && /[0-9]/.test(password);
    }

    function checkEmailExists(email) {
        fetch('/urban2/api/check-email.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
            },
            body: JSON.stringify({ email: email })
        })
        .then(response => response.json())
        .then(data => {
            if (data.exists) {
                showValidationMessage(emailInput, 'This email is already registered', 'error');
            } else {
                showValidationMessage(emailInput, 'Email is available', 'success');
            }
        })
        .catch(error => {
            console.error('Error:', error);
        });
    }

    function showValidationMessage(element, message, type) {
        removeValidationMessage(element);
        
        const messageDiv = document.createElement('div');
        messageDiv.className = `validation-message ${type}`;
        messageDiv.textContent = message;
        
        if (type === 'error') {
            messageDiv.style.color = '#dc3545';
        } else {
            messageDiv.style.color = '#198754';
        }
        
        element.parentNode.appendChild(messageDiv);
    }

    function removeValidationMessage(element) {
        const existingMessage = element.parentNode.querySelector('.validation-message');
        if (existingMessage) {
            existingMessage.remove();
        }
    }

    function validateForm() {
        let isValid = true;
        const requiredFields = addStaffForm.querySelectorAll('[required]');
        
        requiredFields.forEach(field => {
            if (!field.value.trim()) {
                showValidationMessage(field, 'This field is required', 'error');
                isValid = false;
            }
        });

        // Check for any existing error messages
        const errorMessages = addStaffForm.querySelectorAll('.validation-message.error');
        if (errorMessages.length > 0) {
            isValid = false;
        }

        return isValid;
    }
}); 