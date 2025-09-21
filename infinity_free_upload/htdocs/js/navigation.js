// Navigation Functions
function setupNavigation() {
    const navLinks = document.querySelectorAll('.nav-link');
    navLinks.forEach(link => {
        link.addEventListener('click', function(e) {
            const href = this.getAttribute('href');
            if (href && href.startsWith('#')) {
                e.preventDefault();
                const targetPage = href.substring(1);
                showPage(targetPage);
                navLinks.forEach(l => l.classList.remove('active'));
                this.classList.add('active');
            }
        });
    });
}

function showPage(pageId) {
    // Hide all pages
    const pages = document.querySelectorAll('.page');
    pages.forEach(page => page.classList.remove('active'));
    
    // Show target page
    const targetPage = document.getElementById(pageId);
    if (targetPage) {
        targetPage.classList.add('active');
        
        // Load content for specific pages
        if (pageId === 'hackathons') {
            loadHackathons();
        }
        
        // Special handling for hackathon-details page
        if (pageId === 'hackathon-details') {
            // Ensure the grid layout is properly applied
            const gridContainer = targetPage.querySelector('.hackathons-grid.hackathon-details-grid');
            if (gridContainer) {
                // Force grid layout
                gridContainer.style.display = 'grid';
                gridContainer.style.gridTemplateColumns = 'repeat(2, 1fr)';
                gridContainer.style.gap = '2rem';
                gridContainer.style.backgroundColor = '#ffffff';
                gridContainer.style.padding = '2rem';
                gridContainer.style.borderRadius = '15px';
                gridContainer.style.boxShadow = '0 8px 25px rgba(0,0,0,0.15)';
                gridContainer.style.maxWidth = '1000px';
                gridContainer.style.margin = '0 auto';
            }
        }
    }
}

// Mobile Menu
function setupMobileMenu() {
    const hamburger = document.querySelector('.hamburger');
    const navMenu = document.querySelector('.nav-menu');
    
    hamburger.addEventListener('click', function() {
        hamburger.classList.toggle('active');
        navMenu.classList.toggle('active');
    });
    
    // Close mobile menu when clicking on a link
    const navLinks = document.querySelectorAll('.nav-link');
    navLinks.forEach(link => {
        link.addEventListener('click', function() {
            hamburger.classList.remove('active');
            navMenu.classList.remove('active');
        });
    });
}
