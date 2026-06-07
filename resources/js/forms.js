// Form Validation and Handling Logic

class FormValidator {
    constructor(form) {
        this.form = form;
        this.init();
    }

    init() {
        this.form.addEventListener('submit', (e) => this.handleSubmit(e));

        // Add live validation if needed
        const inputs = this.form.querySelectorAll('input, select, textarea');
        inputs.forEach(input => {
            input.addEventListener('blur', () => this.validateInput(input));
            input.addEventListener('input', () => {
                if (input.classList.contains('border-red-500')) {
                    this.validateInput(input);
                }
            });
        });
    }

    handleSubmit(e) {
        let isValid = true;
        const inputs = this.form.querySelectorAll('[required]');

        inputs.forEach(input => {
            if (!this.validateInput(input)) {
                isValid = false;
            }
        });

        if (!isValid) {
            e.preventDefault();
            // Show toast or alert
            console.error('Form validaton failed');
        }
    }

    validateInput(input) {
        const errorContainer = input.parentElement.querySelector('.text-red-500');
        let isValid = true;
        let errorMessage = '';

        if (input.validity.valueMissing) {
            isValid = false;
            errorMessage = 'This field is required';
        } else if (input.type === 'email' && input.validity.typeMismatch) {
            isValid = false;
            errorMessage = 'Please enter a valid email address';
        }

        if (!isValid) {
            input.classList.add('border-red-500');
            input.classList.remove('border-gray-300');
            if (errorContainer) {
                errorContainer.textContent = errorMessage;
                errorContainer.classList.remove('hidden');
            }
        } else {
            input.classList.remove('border-red-500');
            input.classList.add('border-gray-300');
            if (errorContainer) {
                errorContainer.classList.add('hidden');
            }
        }

        return isValid;
    }
}

// Auto-initialize forms with 'validate-form' class
document.addEventListener('DOMContentLoaded', () => {
    document.querySelectorAll('.validate-form').forEach(form => {
        new FormValidator(form);
    });
});
