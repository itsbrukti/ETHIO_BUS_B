// Index Page Specific JavaScript
// Animations, interactions, button handlers

// Smooth scroll for anchor links
function initSmoothScroll() {
    document.querySelectorAll('a[href^="#"]').forEach(anchor => {
        anchor.addEventListener('click', function(e) {
            e.preventDefault();
            const target = document.querySelector(this.getAttribute('href'));
            if (target) {
                target.scrollIntoView({ behavior: 'smooth', block: 'start' });
            }
        });
    });
}

// Feature card hover animation (CSS handles this, but adding click handlers)
function initFeatureCards() {
    const cards = document.querySelectorAll('.feature-card');
    cards.forEach(card => {
        card.addEventListener('click', () => {
            // You can add functionality here later
            console.log('Feature card clicked');
        });
    });
}

// Button click handlers
function initButtons() {
    const bookNowBtn = document.getElementById('bookNowBtn');
    const learnMoreBtn = document.getElementById('learnMoreBtn');
    const getStartedBtn = document.getElementById('getStartedBtn');
    
    if (bookNowBtn) {
        bookNowBtn.addEventListener('click', () => {
            window.location.href = 'signup.html';
        });
    }
    
    if (learnMoreBtn) {
        learnMoreBtn.addEventListener('click', () => {
            window.location.href = 'about.html';
        });
    }
    
    if (getStartedBtn) {
        getStartedBtn.addEventListener('click', () => {
            window.location.href = 'signup.html';
        });
    }
}

// Fade in animation on scroll
function initScrollAnimation() {
    const observerOptions = {
        threshold: 0.1,
        rootMargin: '0px 0px -50px 0px'
    };
    
    const observer = new IntersectionObserver((entries) => {
        entries.forEach(entry => {
            if (entry.isIntersecting) {
                entry.target.style.opacity = '1';
                entry.target.style.transform = 'translateY(0)';
            }
        });
    }, observerOptions);
    
    const animatedElements = document.querySelectorAll('.feature-card, .cta-section');
    animatedElements.forEach(el => {
        el.style.opacity = '0';
        el.style.transform = 'translateY(30px)';
        el.style.transition = 'all 0.6s ease-out';
        observer.observe(el);
    });
}

// Initialize everything when DOM is ready
document.addEventListener('DOMContentLoaded', () => {
    initSmoothScroll();
    initFeatureCards();
    initButtons();
    initScrollAnimation();
});