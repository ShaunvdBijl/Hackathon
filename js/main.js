// Main JavaScript File - Belgium Campus iTversity Hackathon Hub

// Global state
let currentUser = null;

// DOM Content Loaded
document.addEventListener('DOMContentLoaded', function() {
    console.log('DOM loaded, initializing app...');
    initializeApp();
});

// Initialize authentication from localStorage
function initializeAuth() {
    const storedUser = localStorage.getItem('currentUser');
    if (storedUser) {
        try {
            currentUser = JSON.parse(storedUser);
            console.log('User session restored from localStorage:', currentUser);
        } catch (e) {
            console.error('Error parsing stored user data:', e);
            localStorage.removeItem('currentUser');
            currentUser = null;
        }
    }
}

// Initialize the application
function initializeApp() {
    console.log('Initializing application...');

    // Initialize authentication
    if (typeof initializeAuth === 'function') {
        initializeAuth();
    }

    // Setup navigation
    if (typeof setupNavigation === 'function') {
        setupNavigation();
    } else {
        console.warn('setupNavigation function not found');
    }

    // Setup mobile menu
    if (typeof setupMobileMenu === 'function') {
        setupMobileMenu();
    } else {
        console.warn('setupMobileMenu function not found');
    }

    // Setup smooth scrolling for anchor links
    setupSmoothScrolling();

    // Setup loading states for buttons
    setupButtonLoadingStates();

    console.log('Application initialized successfully');
}

// Setup smooth scrolling for anchor links
function setupSmoothScrolling() {
    const anchorLinks = document.querySelectorAll('a[href^="#"]');
    anchorLinks.forEach(link => {
        link.addEventListener('click', function(e) {
            const href = this.getAttribute('href');
            if (href === '#') return;
            
            const targetElement = document.querySelector(href);
            if (targetElement) {
                e.preventDefault();
                targetElement.scrollIntoView({
                    behavior: 'smooth',
                    block: 'start'
                });
            }
        });
    });
}

// Setup loading states for buttons
function setupButtonLoadingStates() {
    const buttons = document.querySelectorAll('.btn');
    buttons.forEach(button => {
        button.addEventListener('click', function() {
            // Add loading state
            const originalText = this.innerHTML;
            this.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Loading...';
            this.disabled = true;
            
            // Remove loading state after a short delay
            setTimeout(() => {
                this.innerHTML = originalText;
                this.disabled = false;
            }, 2000);
        });
    });
}

// Notification system
function showNotification(message, type = 'info') {
    // Create notification element
    const notification = document.createElement('div');
    notification.className = `notification notification-${type}`;
    notification.innerHTML = `
        <div class="notification-content">
            <i class="fas fa-${type === 'success' ? 'check-circle' : type === 'error' ? 'exclamation-circle' : 'info-circle'}"></i>
            <span>${message}</span>
        </div>
    `;
    
    // Add styles
    notification.style.cssText = `
        position: fixed;
        top: 20px;
        right: 20px;
        background: ${type === 'success' ? '#4CAF50' : type === 'error' ? '#f44336' : '#2196F3'};
        color: white;
        padding: 1rem 1.5rem;
        border-radius: 8px;
        box-shadow: 0 4px 12px rgba(0,0,0,0.15);
        z-index: 10000;
        transform: translateX(100%);
        transition: transform 0.3s ease;
        max-width: 300px;
    `;
    
    // Add to page
    document.body.appendChild(notification);
    
    // Animate in
    setTimeout(() => {
        notification.style.transform = 'translateX(0)';
    }, 100);
    
    // Auto-remove after 5 seconds
    setTimeout(() => {
        notification.style.transform = 'translateX(100%)';
        setTimeout(() => {
            if (notification.parentNode) {
                notification.parentNode.removeChild(notification);
            }
        }, 300);
    }, 5000);
    
    // Allow manual dismissal
    notification.addEventListener('click', () => {
        notification.style.transform = 'translateX(100%)';
        setTimeout(() => {
            if (notification.parentNode) {
                notification.parentNode.removeChild(notification);
            }
        }, 300);
    });
}

// Test function for debugging
function testFunction() {
    alert('Test function works!');
    console.log('Test function called successfully');
}

// Export functions to global scope
window.showNotification = showNotification;
window.testFunction = testFunction;

// Debug logs
console.log('main.js loaded successfully');
console.log('showNotification function available:', typeof window.showNotification);
console.log('testFunction function available:', typeof window.testFunction);
