/**
 * Enhanced JavaScript functionality for ID Card System
 * Includes modern features and improved security
 */

// Global configuration
const IDCARD_CONFIG = {
    maxFileSize: 5 * 1024 * 1024, // 5MB
    allowedImageTypes: ['image/jpeg', 'image/jpg', 'image/png', 'image/webp'],
    passwordMinLength: 8
};

// Enhanced form validation
class FormValidator {
    constructor() {
        this.initializeValidation();
    }

    initializeValidation() {
        // Initialize all forms with validation
        document.querySelectorAll('form').forEach(form => {
            form.addEventListener('submit', (e) => this.validateForm(e));
        });

        // Real-time password strength indicator
        const passwordInputs = document.querySelectorAll('input[type="password"]');
        passwordInputs.forEach(input => {
            if (input.name === 'password') {
                input.addEventListener('input', (e) => this.checkPasswordStrength(e.target));
            }
        });

        // File upload validation
        const fileInputs = document.querySelectorAll('input[type="file"]');
        fileInputs.forEach(input => {
            input.addEventListener('change', (e) => this.validateFileUpload(e.target));
        });
    }

    validateForm(event) {
        const form = event.target;
        let isValid = true;
        const errors = [];

        // Clear previous error messages
        this.clearErrorMessages(form);

        // Validate required fields
        const requiredFields = form.querySelectorAll('[required]');
        requiredFields.forEach(field => {
            if (!field.value.trim()) {
                isValid = false;
                errors.push(`${this.getFieldLabel(field)} is required.`);
                this.markFieldError(field);
            }
        });

        // Email validation
        const emailFields = form.querySelectorAll('input[type="email"]');
        emailFields.forEach(field => {
            if (field.value && !this.isValidEmail(field.value)) {
                isValid = false;
                errors.push('Please enter a valid email address.');
                this.markFieldError(field);
            }
        });

        // Password validation
        const passwordField = form.querySelector('input[name="password"]');
        if (passwordField && passwordField.value) {
            const passwordErrors = this.validatePassword(passwordField.value);
            if (passwordErrors.length > 0) {
                isValid = false;
                errors.push(...passwordErrors);
                this.markFieldError(passwordField);
            }
        }

        // Password confirmation
        const confirmPasswordField = form.querySelector('input[name="confirm_password"]');
        if (passwordField && confirmPasswordField) {
            if (passwordField.value !== confirmPasswordField.value) {
                isValid = false;
                errors.push('Passwords do not match.');
                this.markFieldError(confirmPasswordField);
            }
        }

        if (!isValid) {
            event.preventDefault();
            this.displayErrors(form, errors);
        }

        return isValid;
    }

    validatePassword(password) {
        const errors = [];
        
        if (password.length < IDCARD_CONFIG.passwordMinLength) {
            errors.push(`Password must be at least ${IDCARD_CONFIG.passwordMinLength} characters long.`);
        }
        
        if (!/(?=.*[a-z])/.test(password)) {
            errors.push('Password must contain at least one lowercase letter.');
        }
        
        if (!/(?=.*[A-Z])/.test(password)) {
            errors.push('Password must contain at least one uppercase letter.');
        }
        
        if (!/(?=.*\d)/.test(password)) {
            errors.push('Password must contain at least one number.');
        }

        return errors;
    }

    checkPasswordStrength(passwordField) {
        const password = passwordField.value;
        let strengthMeter = document.getElementById('passwordStrength');
        
        if (!strengthMeter) {
            strengthMeter = document.createElement('div');
            strengthMeter.id = 'passwordStrength';
            strengthMeter.className = 'password-strength-meter mt-2';
            passwordField.parentNode.appendChild(strengthMeter);
        }

        let strength = 0;
        let strengthText = '';
        let strengthClass = '';

        if (password.length >= IDCARD_CONFIG.passwordMinLength) strength++;
        if (/(?=.*[a-z])/.test(password)) strength++;
        if (/(?=.*[A-Z])/.test(password)) strength++;
        if (/(?=.*\d)/.test(password)) strength++;
        if (/(?=.*[!@#$%^&*])/.test(password)) strength++;

        switch (strength) {
            case 0:
            case 1:
                strengthText = 'Very Weak';
                strengthClass = 'text-danger';
                break;
            case 2:
                strengthText = 'Weak';
                strengthClass = 'text-warning';
                break;
            case 3:
                strengthText = 'Fair';
                strengthClass = 'text-info';
                break;
            case 4:
                strengthText = 'Good';
                strengthClass = 'text-success';
                break;
            case 5:
                strengthText = 'Excellent';
                strengthClass = 'text-success fw-bold';
                break;
        }

        strengthMeter.innerHTML = 
            `<small class="${strengthClass}">Password Strength: ${strengthText}</small>`;
    }

    validateFileUpload(fileInput) {
        const file = fileInput.files[0];
        const errorContainer = fileInput.parentNode.querySelector('.file-error');
        
        if (errorContainer) {
            errorContainer.remove();
        }

        if (!file) return;

        let errors = [];

        if (file.size > IDCARD_CONFIG.maxFileSize) {
            errors.push(`File size must be less than ${IDCARD_CONFIG.maxFileSize / 1024 / 1024}MB`);
        }

        if (!IDCARD_CONFIG.allowedImageTypes.includes(file.type)) {
            errors.push('Only JPEG, PNG, and WebP images are allowed');
        }

        if (errors.length > 0) {
            const errorDiv = document.createElement('div');
            errorDiv.className = 'file-error alert alert-danger mt-2';
            errorDiv.innerHTML = errors.join('<br>');
            fileInput.parentNode.appendChild(errorDiv);
            fileInput.value = '';
        } else {
            this.showImagePreview(fileInput, file);
        }
    }

    showImagePreview(fileInput, file) {
        const previewContainer = fileInput.parentNode.querySelector('.image-preview');
        
        if (previewContainer) {
            previewContainer.remove();
        }

        const reader = new FileReader();
        reader.onload = function(e) {
            const preview = document.createElement('div');
            preview.className = 'image-preview mt-2';
            preview.innerHTML = `
                <img src="${e.target.result}" class="img-thumbnail" style="max-width: 150px; max-height: 150px;" alt="Preview">
                <small class="d-block text-muted mt-1">Preview</small>
            `;
            fileInput.parentNode.appendChild(preview);
        };
        reader.readAsDataURL(file);
    }

    isValidEmail(email) {
        const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
        return emailRegex.test(email);
    }

    getFieldLabel(field) {
        const label = field.parentNode.querySelector('label');
        return label ? label.textContent.replace('*', '').trim() : field.name;
    }

    markFieldError(field) {
        field.classList.add('is-invalid');
    }

    clearErrorMessages(form) {
        form.querySelectorAll('.is-invalid').forEach(field => {
            field.classList.remove('is-invalid');
        });
        form.querySelectorAll('.custom-error').forEach(error => {
            error.remove();
        });
    }

    displayErrors(form, errors) {
        const errorContainer = document.createElement('div');
        errorContainer.className = 'alert alert-danger custom-error';
        errorContainer.innerHTML = `
            <h6><i class="fas fa-exclamation-triangle me-2"></i>Please fix the following errors:</h6>
            <ul class="mb-0">${errors.map(error => `<li>${error}</li>`).join('')}</ul>
        `;
        form.insertBefore(errorContainer, form.firstChild);
        errorContainer.scrollIntoView({ behavior: 'smooth', block: 'center' });
    }
}

// Enhanced UI interactions
class UIEnhancements {
    constructor() {
        this.initializeEnhancements();
    }

    initializeEnhancements() {
        document.querySelectorAll('.alert:not(.alert-permanent)').forEach(alert => {
            setTimeout(() => {
                if (alert.classList.contains('show')) {
                    const bsAlert = new bootstrap.Alert(alert);
                    bsAlert.close();
                }
            }, 5000);
        });

        this.initializeTooltips();
        this.initializeLoadingStates();
    }

    initializeTooltips() {
        const tooltipTriggerList = document.querySelectorAll('[data-bs-toggle="tooltip"]');
        const tooltipList = [...tooltipTriggerList].map(tooltipTriggerEl => 
            new bootstrap.Tooltip(tooltipTriggerEl)
        );
    }

    initializeLoadingStates() {
        document.querySelectorAll('form').forEach(form => {
            form.addEventListener('submit', (e) => {
                const submitBtn = form.querySelector('button[type="submit"]');
                if (submitBtn && !form.classList.contains('has-errors')) {
                    const originalText = submitBtn.innerHTML;
                    submitBtn.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>Processing...';
                    submitBtn.disabled = true;

                    setTimeout(() => {
                        submitBtn.innerHTML = originalText;
                        submitBtn.disabled = false;
                    }, 10000);
                }
            });
        });
    }
}

// Print functionality for ID cards
function printIdCard() {
    const printContent = document.querySelector('.id-card');
    if (printContent) {
        // Create a new window for printing
        const printWindow = window.open('', '_blank', 'width=800,height=600');
        
        printWindow.document.write(`
            <!DOCTYPE html>
            <html>
            <head>
                <title>ID Card Print</title>
                <style>
                    @page {
                        size: A4;
                        margin: 0.5in;
                    }
                    
                    body {
                        margin: 0;
                        padding: 20px;
                        font-family: Arial, sans-serif;
                        background: white;
                    }
                    
                    .print-container {
                        display: flex;
                        justify-content: center;
                        align-items: center;
                        min-height: 100vh;
                    }
                    
                    .id-card {
                        background: linear-gradient(135deg, #ffffff 0%, #f8f9fa 100%);
                        border: 3px solid #2c3e50;
                        border-radius: 12px;
                        padding: 0;
                        box-shadow: 0 8px 32px rgba(0,0,0,0.15);
                        position: relative;
                        overflow: hidden;
                        width: 350px;
                        height: 240px;
                        font-family: 'Arial', sans-serif;
                        display: flex;
                        flex-direction: column;
                    }
                    
                    .id-card::before {
                        content: '';
                        position: absolute;
                        top: 0;
                        left: 0;
                        right: 0;
                        height: 8px;
                        background: linear-gradient(90deg, #1e3c72 0%, #2a5298 50%, #1e3c72 100%);
                        z-index: 1;
                    }
                    
                    .id-card::after {
                        content: '';
                        position: absolute;
                        top: 8px;
                        left: 0;
                        right: 0;
                        height: 2px;
                        background: linear-gradient(90deg, transparent 0%, #ffd700 50%, transparent 100%);
                        z-index: 1;
                    }
                    
                    .id-card-content {
                        padding: 12px;
                        margin-top: 10px;
                        flex: 1;
                        display: flex;
                        flex-direction: column;
                    }
                    
                    .id-card-header {
                        text-align: center;
                        margin-bottom: 8px;
                        padding-bottom: 6px;
                        border-bottom: 1px solid #e9ecef;
                    }
                    
                    .id-card-title {
                        font-size: 1rem;
                        font-weight: bold;
                        color: #1e3c72;
                        margin: 0;
                        text-transform: uppercase;
                        letter-spacing: 0.5px;
                        line-height: 1.1;
                    }
                    
                    .id-card-subtitle {
                        font-size: 0.65rem;
                        color: #6c757d;
                        margin: 2px 0 0 0;
                        font-style: italic;
                    }
                    
                    .id-card-body {
                        display: flex;
                        gap: 10px;
                        align-items: flex-start;
                        flex: 1;
                    }
                    
                    .id-card-photo-section {
                        flex-shrink: 0;
                    }
                    
                    .id-card-info {
                        flex: 1;
                        min-width: 0;
                    }
                    
                    .id-card-field {
                        display: flex;
                        margin-bottom: 3px;
                        align-items: center;
                        min-height: 14px;
                    }
                    
                    .id-card-label {
                        font-weight: bold;
                        color: #2c3e50;
                        min-width: 65px;
                        font-size: 0.65rem;
                        text-transform: uppercase;
                        letter-spacing: 0.3px;
                        flex-shrink: 0;
                    }
                    
                    .id-card-value {
                        color: #495057;
                        font-size: 0.7rem;
                        flex: 1;
                        word-wrap: break-word;
                        overflow-wrap: break-word;
                        line-height: 1.1;
                    }
                    
                    .id-card-photo {
                        width: 80px;
                        height: 100px;
                        border-radius: 6px;
                        object-fit: cover;
                        border: 2px solid #2c3e50;
                        box-shadow: 0 2px 8px rgba(0,0,0,0.2);
                        background: #f8f9fa;
                    }
                    
                    .id-card-photo-placeholder {
                        width: 80px;
                        height: 100px;
                        border-radius: 6px;
                        border: 2px solid #2c3e50;
                        background: linear-gradient(135deg, #e9ecef 0%, #dee2e6 100%);
                        display: flex;
                        align-items: center;
                        justify-content: center;
                        color: #6c757d;
                        font-size: 1.5rem;
                    }
                    
                    .id-card-id-number {
                        background: linear-gradient(135deg, #1e3c72 0%, #2a5298 100%);
                        color: white;
                        padding: 2px 6px;
                        border-radius: 3px;
                        font-weight: bold;
                        font-size: 0.7rem;
                        letter-spacing: 0.5px;
                        display: inline-block;
                    }
                    
                    .id-card-footer {
                        text-align: center;
                        margin-top: 6px;
                        padding-top: 4px;
                        border-top: 1px solid #e9ecef;
                        font-size: 0.6rem;
                        color: #6c757d;
                        font-style: italic;
                        line-height: 1.1;
                    }
                    
                    @media print {
                        body { margin: 0; padding: 0; }
                        .print-container { min-height: auto; }
                    }
                </style>
            </head>
            <body>
                <div class="print-container">
                    ${printContent.outerHTML}
                </div>
            </body>
            </html>
        `);
        
        printWindow.document.close();
        
        // Wait for content to load, then print
        printWindow.onload = function() {
            printWindow.focus();
            printWindow.print();
            printWindow.close();
        };
    }
}

// Initialize everything when DOM is loaded
document.addEventListener('DOMContentLoaded', () => {
    new FormValidator();
    new UIEnhancements();

    window.addEventListener('error', (e) => {
        console.error('JavaScript Error:', e.error);
    });
});

// Export for use in other scripts
window.IDCardSystem = {
    FormValidator,
    UIEnhancements,
    printIdCard,
    IDCARD_CONFIG
};