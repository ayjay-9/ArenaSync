'use strict';

document.addEventListener('DOMContentLoaded', () => {

    // ── Toggle password visibility (mirrors login.js) ────────
    const togglePassword = document.getElementById('togglePassword');
    const passwordInput  = document.getElementById('password');

    if (togglePassword && passwordInput) {
        togglePassword.addEventListener('click', () => {
            const isPassword = passwordInput.type === 'password';
            passwordInput.type = isPassword ? 'text' : 'password';
            togglePassword.textContent = isPassword ? 'Hide Password' : 'Show Password';
        });
    }

    // ── Client-side validation ───────────────────────────────
    const form = document.getElementById('personalDetailsForm');

    if (form) {
        form.addEventListener('submit', (e) => {
            let valid = true;

            const firstName = document.getElementById('firstName');
            const lastName  = document.getElementById('lastName');
            const email     = document.getElementById('email');

            const firstNameError = document.getElementById('firstName_error');
            const lastNameError  = document.getElementById('lastName_error');
            const emailError     = document.getElementById('email_error');

            // Clear previous errors
            [firstNameError, lastNameError, emailError].forEach(el => {
                if (el) el.textContent = '';
            });

            // First name
            if (!firstName.value.trim()) {
                firstNameError.textContent = 'First name is required.';
                valid = false;
            }

            // Last name
            if (!lastName.value.trim()) {
                lastNameError.textContent = 'Last name is required.';
                valid = false;
            }

            // Email
            const emailPattern = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
            if (!email.value.trim() || !emailPattern.test(email.value.trim())) {
                emailError.textContent = 'A valid email address is required.';
                valid = false;
            }

            if (!valid) e.preventDefault();
        });
    }

});
