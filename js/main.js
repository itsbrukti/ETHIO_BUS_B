// Load header and footer components
async function loadComponent(elementId, componentPath) {
    try {
        const response = await fetch(componentPath);
        if (!response.ok) throw new Error(`Failed to load ${componentPath}`);
        const html = await response.text();
        document.getElementById(elementId).innerHTML = html;
        return true;
    } catch (error) {
        console.error('Error loading component:', error);
        return false;
    }
}

// Mobile menu toggle
function initMobileMenu() {
    const menuToggle = document.getElementById('mobileMenu');
    const navLinks = document.getElementById('navLinks');
    
    if (menuToggle && navLinks) {
        menuToggle.addEventListener('click', () => {
            navLinks.classList.toggle('active');
        });
    }
}

// Set active nav link
function setActiveNavLink() {
    const currentPage = window.location.pathname.split('/').pop() || 'index.html';
    setTimeout(() => {
        const navLinks = document.querySelectorAll('.nav-link');
        navLinks.forEach(link => {
            const href = link.getAttribute('href');
            if (href === currentPage) {
                link.classList.add('active');
            }
        });
    }, 100);
}

// Initialize everything
document.addEventListener('DOMContentLoaded', async () => {
    await loadComponent('header-placeholder', 'components/header.html');
    await loadComponent('footer-placeholder', 'components/footer.html');
    
    setTimeout(() => {
        setActiveNavLink();
        initMobileMenu();
        
        if (typeof initLanguageSwitcher === 'function') {
            initLanguageSwitcher();
        }
        
        if (typeof updateActiveLangButton === 'function') {
            updateActiveLangButton();
        }
        
        if (typeof translatePage === 'function') {
            translatePage();
        }
    }, 100);
});