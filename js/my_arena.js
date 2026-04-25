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

    // ── Sidebar tab switching ────────────────────────────────
    const navLinks = document.querySelectorAll('#sidebar ul li a');
    const sections = document.querySelectorAll('#main section');

    function switchTab(targetId) {
        // Hide all sections, show the target
        sections.forEach(section => {
            section.style.display = section.id === targetId ? 'block' : 'none';
        });

        // Update active class on nav links
        navLinks.forEach(link => {
            const linkTarget = link.getAttribute('href').replace('#', '');
            link.classList.toggle('active', linkTarget === targetId);
        });
    }

    navLinks.forEach(link => {
        link.addEventListener('click', (e) => {
            e.preventDefault();
            const targetId = link.getAttribute('href').replace('#', '');
            switchTab(targetId);
        });
    });

    // Activate the tab matching the URL hash on load, or default to the first
    const initialHash = window.location.hash.replace('#', '');
    const validIds    = Array.from(sections).map(s => s.id);
    switchTab(validIds.includes(initialHash) ? initialHash : validIds[0]);

});
