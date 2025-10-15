// Mobile navigation toggle
const hamburger = document.querySelector('.hamburger');
const mainNav = document.querySelector('.main-nav');

hamburger.addEventListener('click', () => {
    hamburger.classList.toggle('active');
    mainNav.classList.toggle('open');
    const expanded = hamburger.getAttribute('aria-expanded') === 'true';
    hamburger.setAttribute('aria-expanded', !expanded);
});

// Close mobile menu when clicking outside
document.addEventListener('click', (e) => {
    if (!hamburger.contains(e.target) && !mainNav.contains(e.target)) {
        hamburger.classList.remove('active');
        mainNav.classList.remove('open');
        hamburger.setAttribute('aria-expanded', 'false');
    }
});

// Dropdown menu functionality
const dropdownToggles = document.querySelectorAll('.dropdown-toggle');
dropdownToggles.forEach(toggle => {
    toggle.addEventListener('click', (e) => {
        e.stopPropagation();
        const dropdown = toggle.closest('.has-dropdown');
        dropdown.classList.toggle('open');
        
        const expanded = toggle.getAttribute('aria-expanded') === 'true';
        toggle.setAttribute('aria-expanded', !expanded);
    });
});

// Close dropdowns when clicking elsewhere
document.addEventListener('click', () => {
    dropdownToggles.forEach(toggle => {
        toggle.setAttribute('aria-expanded', 'false');
        toggle.closest('.has-dropdown').classList.remove('open');
    });
});

// Dark mode toggle
const modeToggle = document.querySelector('.mode-toggle');
modeToggle.addEventListener('click', () => {
    document.body.classList.toggle('dark-mode');
    const pressed = modeToggle.getAttribute('aria-pressed') === 'true';
    modeToggle.setAttribute('aria-pressed', !pressed);
    modeToggle.textContent = pressed ? 'Dark Mode' : 'Light Mode';
});

// Set current year in footer
document.getElementById('year').textContent = new Date().getFullYear();