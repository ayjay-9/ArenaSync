document.addEventListener('DOMContentLoaded', () => {
    const hamburger = document.getElementById('hamburger');
    const navLinks = document.getElementById('nav-links');
    hamburger.addEventListener('click', () => {
        navLinks.classList.toggle('show');
        hamburger.classList.toggle('active');
    });
});

const params = new URLSearchParams(window.location.search);
const statusMsg = document.getElementById('statusMsg');

if (statusMsg) {
  const error = params.get('error');
  if (error === 'invalid_credentials') {
    statusMsg.textContent = 'Invalid email or password.';
    statusMsg.style.color = '#e94560';
  } else if (error === 'missing_fields') {
    statusMsg.textContent = 'Please fill in all fields.';
    statusMsg.style.color = '#e94560';
  }
}
