initNavigation();
initDropdowns();
initModeToggle();
initFooterYear();

function initNavigation() {
    const hamburger = document.querySelector('.hamburger');
    const mainNav = document.querySelector('.main-nav');

    if (!hamburger || !mainNav) {
        return;
    }

    hamburger.addEventListener('click', () => {
        const expanded = hamburger.getAttribute('aria-expanded') === 'true';
        hamburger.classList.toggle('active');
        mainNav.classList.toggle('open');
        hamburger.setAttribute('aria-expanded', String(!expanded));
    });

    document.addEventListener('click', (event) => {
        if (!hamburger.contains(event.target) && !mainNav.contains(event.target)) {
            hamburger.classList.remove('active');
            mainNav.classList.remove('open');
            hamburger.setAttribute('aria-expanded', 'false');
        }
    });
}

function initDropdowns() {
    const dropdownToggles = document.querySelectorAll('.dropdown-toggle');
    if (!dropdownToggles.length) {
        return;
    }

    dropdownToggles.forEach((toggle) => {
        toggle.addEventListener('click', (event) => {
            event.stopPropagation();
            const dropdown = toggle.closest('.has-dropdown');
            if (!dropdown) {
                return;
            }
            const isOpen = dropdown.classList.toggle('open');
            toggle.setAttribute('aria-expanded', String(isOpen));
        });
    });

    document.addEventListener('click', (event) => {
        dropdownToggles.forEach((toggle) => {
            const dropdown = toggle.closest('.has-dropdown');
            if (!dropdown) {
                return;
            }
            if (!dropdown.contains(event.target)) {
                dropdown.classList.remove('open');
                toggle.setAttribute('aria-expanded', 'false');
            }
        });
    });
}

function initFooterYear() {
    const yearElement = document.getElementById('year');
    if (yearElement) {
        yearElement.textContent = String(new Date().getFullYear());
    }
}