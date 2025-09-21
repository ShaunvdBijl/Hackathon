// Hackathons Management System
let currentTab = 'upcoming';

// Load and display hackathons
function loadHackathons() {
    displayHackathons('upcoming');
    displayHackathons('past');
}

// Display hackathons for a specific tab
function displayHackathons(tab) {
    const container = document.getElementById(`${tab}-hackathons`);
    if (!container) return;

    const hackathons = hackathonsData[tab];
    
    if (hackathons.length === 0) {
        container.innerHTML = `
            <div class="text-center" style="grid-column: 1 / -1; padding: 3rem;">
                <i class="fas fa-calendar-times" style="font-size: 4rem; color: var(--bc-gray); margin-bottom: 1rem;"></i>
                <h3 style="color: var(--bc-gray);">No ${tab} hackathons</h3>
                <p style="color: var(--bc-gray);">Check back later for upcoming events!</p>
            </div>
        `;
        return;
    }

    container.innerHTML = hackathons.map(hackathon => createHackathonCard(hackathon)).join('');
}

// Create modern hackathon card
function createHackathonCard(hackathon) {
    console.log('🔍 Creating hackathon card for:', hackathon.title);
    console.log('📊 Hackathon data:', hackathon);
    
    const statusClass = hackathon.status === 'upcoming' ? 'status-upcoming' : 'status-past';
    const statusText = hackathon.status === 'upcoming' ? 'Upcoming' : 'Past';
    
    // Check if user is logged in and registered
    const currentUser = JSON.parse(localStorage.getItem('currentUser') || 'null');
    console.log('👤 Current user:', currentUser);
    
    const isLoggedIn = currentUser && currentUser.email;
    console.log('🔑 Is logged in:', isLoggedIn);
    
    // Check if hackathonRegistration is available
    if (typeof hackathonRegistration === 'undefined') {
        console.error('❌ hackathonRegistration is not defined!');
    } else {
        console.log('✅ hackathonRegistration is available');
    }
    
    const isRegistered = isLoggedIn ? hackathonRegistration.isUserRegistered(hackathon.id, currentUser.email) : false;
    console.log('📝 Is registered:', isRegistered);
    
    const isFull = hackathon.maxParticipants !== null ? hackathon.currentParticipants >= hackathon.maxParticipants : false;
    console.log('👥 Is full:', isFull, `(${hackathon.currentParticipants}/${hackathon.maxParticipants || 'No limit'})`);
    
    const isDeadlinePassed = new Date() > new Date(hackathon.registrationDeadline);
    console.log('⏰ Deadline passed:', isDeadlinePassed, `(deadline: ${hackathon.registrationDeadline})`);
    
    // Determine registration button state and text
    let registrationButton = '';
    if (hackathon.status === 'upcoming') {
        if (isRegistered) {
            registrationButton = `
                <button class="btn btn-success" onclick="unregisterFromHackathon('${hackathon.id}')" style="margin-top: 0.5rem;">
                    <i class="fas fa-check-circle"></i> Registered
                </button>
            `;
            console.log('🟢 Showing: Registered button');
        } else if (isFull) {
            registrationButton = `
                <button class="btn btn-secondary" disabled style="margin-top: 0.5rem;">
                    <i class="fas fa-users"></i> Full
                </button>
            `;
            console.log('🔴 Showing: Full button');
        } else if (isDeadlinePassed) {
            registrationButton = `
                <button class="btn btn-secondary" disabled style="margin-top: 0.5rem;">
                    <i class="fas fa-clock"></i> Deadline Passed
                </button>
            `;
            console.log('⏰ Showing: Deadline passed button');
        } else if (isLoggedIn) {
            registrationButton = `
                <button class="btn btn-primary" onclick="registerForHackathon('${hackathon.id}')" style="margin-top: 0.5rem;">
                    <i class="fas fa-user-plus"></i> Register
                </button>
            `;
            console.log('🔵 Showing: Register button');
        } else {
            registrationButton = `
                <button class="btn btn-primary" onclick="showLoginPrompt()" style="margin-top: 0.5rem;">
                    <i class="fas fa-sign-in-alt"></i> Login to Register
                </button>
            `;
            console.log('🟡 Showing: Login to Register button');
        }
    } else {
        console.log('📅 Hackathon is not upcoming, no registration button');
    }
    
    console.log('🎯 Final registration button HTML:', registrationButton);
    
    return `
        <div class="hackathon-card animate-fade-in-up">
            <div class="hackathon-card-header">
                <div class="hackathon-card-title">${hackathon.title}</div>
                <div class="hackathon-card-date">
                    <i class="fas fa-calendar-alt"></i> ${hackathon.date}
                </div>
                <div class="hackathon-card-status ${statusClass}">
                    <i class="fas fa-${hackathon.status === 'upcoming' ? 'clock' : 'check-circle'}"></i>
                    ${statusText}
                </div>
            </div>
            <div class="hackathon-card-body">
                <div class="hackathon-card-description">
                    ${hackathon.description}
                </div>
                <div class="hackathon-card-meta">
                    <div class="hackathon-card-participants">
                        <i class="fas fa-users"></i>
                        <span>${hackathon.currentParticipants}/${hackathon.maxParticipants || 'No limit'} participants</span>
                    </div>
                    <div class="hackathon-card-prize">
                        <i class="fas fa-trophy"></i>
                        <span>${hackathon.prize}</span>
                    </div>
                </div>
                ${hackathon.requirements ? `
                    <div class="hackathon-card-requirements" style="margin-top: 1rem; font-size: 0.9rem; color: #666;">
                        <strong>Requirements:</strong>
                        <ul style="margin: 0.5rem 0 0 1rem; padding: 0;">
                            ${hackathon.requirements.map(req => `<li>${req}</li>`).join('')}
                        </ul>
                    </div>
                ` : ''}
                <div style="text-align: center; margin-top: 1rem;">
                    <button class="btn btn-primary" onclick="showHackathonDetails('${hackathon.id}')">
                        <i class="fas fa-info-circle"></i> See Details
                    </button>
                    ${registrationButton}
                </div>
            </div>
        </div>
    `;
}

// Show specific tab content
function showTab(tabName) {
    // Hide all tab contents
    const tabContents = document.querySelectorAll('.tab-content');
    tabContents.forEach(content => content.classList.remove('active'));
    
    // Remove active class from all tab buttons
    const tabButtons = document.querySelectorAll('.tab-btn');
    tabButtons.forEach(button => button.classList.remove('active'));
    
    // Show selected tab content
    const selectedContent = document.getElementById(tabName);
    if (selectedContent) {
        selectedContent.classList.add('active');
    }
    
    // Add active class to clicked button
    const clickedButton = event.target;
    clickedButton.classList.add('active');
    
    currentTab = tabName;
}

// Show hackathon details page
function showHackathonDetails(hackathonId) {
    // Hide all pages
    const pages = document.querySelectorAll('.page');
    pages.forEach(page => page.classList.remove('active'));
    
    // Show hackathon details page
    const detailsPage = document.getElementById('hackathon-details');
    if (detailsPage) {
        detailsPage.classList.add('active');
        
        // Add animation
        detailsPage.classList.add('animate-fade-in-up');
        
        // Remove animation class after animation completes
        setTimeout(() => {
            detailsPage.classList.remove('animate-fade-in-up');
        }, 600);
    }
    
    // Update navigation
    updateNavigation('hackathon-details');
}

// Show hackathons list
function showHackathonsList() {
    // Hide all pages
    const pages = document.querySelectorAll('.page');
    pages.forEach(page => page.classList.remove('active'));
    
    // Show hackathons page
    const hackathonsPage = document.getElementById('hackathons');
    if (hackathonsPage) {
        hackathonsPage.classList.add('active');
        
        // Add animation
        hackathonsPage.classList.add('animate-fade-in-up');
        
        // Remove animation class after animation completes
        setTimeout(() => {
            hackathonsPage.classList.remove('animate-fade-in-up');
        }, 600);
    }
    
    // Update navigation
    updateNavigation('hackathons');
}

// Update navigation active state
function updateNavigation(activeSection) {
    const navLinks = document.querySelectorAll('.nav-link');
    navLinks.forEach(link => {
        link.classList.remove('active');
        
        // Check if this link corresponds to the active section
        if (link.getAttribute('href') === `#${activeSection}`) {
            link.classList.add('active');
        }
    });
}

// Initialize hackathons when DOM is loaded
document.addEventListener('DOMContentLoaded', function() {
    // Add click event listeners to tab buttons
    const tabButtons = document.querySelectorAll('.tab-btn');
    tabButtons.forEach(button => {
        button.addEventListener('click', function() {
            const tabName = this.getAttribute('onclick').match(/'([^']+)'/)[1];
            showTab(tabName);
        });
    });
    
    // Load hackathons
    loadHackathons();
});

// Export functions for global use
window.showTab = showTab;
window.showHackathonDetails = showHackathonDetails;
window.showHackathonsList = showHackathonsList;

// Hackathon registration functions
async function registerForHackathon(hackathonId) {
    try {
        const currentUser = JSON.parse(localStorage.getItem('currentUser') || 'null');
        if (!currentUser || !currentUser.email) {
            showNotification('Please log in to register for hackathons', 'error');
            return;
        }

        // Show registration modal
        showRegistrationModal(hackathonId);
    } catch (error) {
        console.error('Error in registration:', error);
        showNotification('Registration failed. Please try again.', 'error');
    }
}

async function unregisterFromHackathon(hackathonId) {
    try {
        const currentUser = JSON.parse(localStorage.getItem('currentUser') || 'null');
        if (!currentUser || !currentUser.email) {
            showNotification('Please log in to manage your registrations', 'error');
            return;
        }

        // Confirm unregistration
        if (confirm('Are you sure you want to unregister from this hackathon?')) {
            await hackathonRegistration.unregisterUser(hackathonId, currentUser.email);
            showNotification('Successfully unregistered from hackathon', 'success');
            
            // Refresh the hackathons display
            loadHackathons();
        }
    } catch (error) {
        console.error('Error in unregistration:', error);
        showNotification(error.message || 'Unregistration failed. Please try again.', 'error');
    }
}

function showLoginPrompt() {
    showNotification('Please log in to your student account to register for hackathons', 'info');
    // Optionally redirect to student portal
    setTimeout(() => {
        if (confirm('Would you like to go to the student portal to log in?')) {
            window.location.href = 'student-portal.html';
        }
    }, 2000);
}

function showRegistrationModal(hackathonId) {
    const hackathon = hackathonRegistration.findHackathon(hackathonId);
    if (!hackathon) {
        showNotification('Hackathon not found', 'error');
        return;
    }

    // Create modal HTML
    const modalHTML = `
        <div id="registrationModal" class="modal-overlay" style="position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.7); z-index: 9999; display: flex; align-items: center; justify-content: center;">
            <div class="modal-content" style="background: white; padding: 2rem; border-radius: 15px; max-width: 500px; width: 90%; max-height: 80vh; overflow-y: auto;">
                <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1.5rem;">
                    <h2>Register for ${hackathon.title}</h2>
                    <button onclick="closeRegistrationModal()" style="background: none; border: none; font-size: 1.5rem; cursor: pointer; color: #666;">&times;</button>
                </div>
                
                <form id="registrationForm">
                    <div class="form-group" style="margin-bottom: 1rem;">
                        <label for="teamName">Team Name (optional)</label>
                        <input type="text" id="teamName" name="teamName" placeholder="Enter team name or leave blank for solo">
                    </div>
                    
                    
                    <div class="form-group" style="margin-bottom: 1rem;">
                        <label for="specialRequirements">Special Requirements</label>
                        <textarea id="specialRequirements" name="specialRequirements" rows="3" placeholder="Any special accommodations or requirements?"></textarea>
                    </div>
                    
                    <div class="form-group" style="margin-bottom: 1.5rem;">
                        <label>
                            <input type="checkbox" id="agreeTerms" name="agreeTerms" required>
                            I agree to the hackathon rules and guidelines
                        </label>
                    </div>
                    
                    <div style="display: flex; gap: 1rem; justify-content: flex-end;">
                        <button type="button" onclick="closeRegistrationModal()" class="btn btn-secondary">
                            Cancel
                        </button>
                        <button type="submit" class="btn btn-primary">
                            Confirm Registration
                        </button>
                    </div>
                </form>
            </div>
        </div>
    `;

    // Add modal to page
    document.body.insertAdjacentHTML('beforeend', modalHTML);

    // Handle form submission
    document.getElementById('registrationForm').addEventListener('submit', async function(e) {
        e.preventDefault();
        
        const formData = {
            teamName: document.getElementById('teamName').value.trim(),
            specialRequirements: document.getElementById('specialRequirements').value.trim(),
            agreeTerms: document.getElementById('agreeTerms').checked
        };

        if (!formData.agreeTerms) {
            showNotification('You must agree to the terms to register', 'error');
            return;
        }

        try {
            const currentUser = JSON.parse(localStorage.getItem('currentUser') || 'null');
            const registration = await hackathonRegistration.registerUser(hackathonId, currentUser.email, formData);
            
            showNotification('Successfully registered for hackathon!', 'success');
            closeRegistrationModal();
            
            // Refresh the hackathons display
            loadHackathons();
        } catch (error) {
            showNotification(error.message || 'Registration failed. Please try again.', 'error');
        }
    });
}

function closeRegistrationModal() {
    const modal = document.getElementById('registrationModal');
    if (modal) {
        modal.remove();
    }
}

// Simple notification function
function showNotification(message, type = 'info') {
    const notification = document.createElement('div');
    notification.className = `notification ${type}`;
    notification.textContent = message;
    notification.style.cssText = `
        position: fixed;
        top: 20px;
        right: 20px;
        padding: 1rem 1.5rem;
        border-radius: 8px;
        color: white;
        font-weight: 600;
        z-index: 10000;
        opacity: 0;
        transform: translateX(100%);
        transition: all 0.3s ease;
        background: ${type === 'success' ? '#28a745' : type === 'error' ? '#dc3545' : '#17a2b8'};
    `;
    
    document.body.appendChild(notification);
    
    setTimeout(() => notification.classList.add('show'), 100);
    setTimeout(() => {
        notification.classList.remove('show');
        setTimeout(() => notification.remove(), 300);
    }, 5000);
}

// Export registration functions
window.registerForHackathon = registerForHackathon;
window.unregisterFromHackathon = unregisterFromHackathon;
window.showLoginPrompt = showLoginPrompt;
window.showRegistrationModal = showRegistrationModal;
window.closeRegistrationModal = closeRegistrationModal;
