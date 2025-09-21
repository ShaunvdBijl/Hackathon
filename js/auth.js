// Authentication Functions
function setupLoginForm() {
    console.log('🔧 setupLoginForm called...');
    const loginForm = document.getElementById('loginForm');
    console.log('Login form element found:', loginForm);
    
    if (loginForm) {
        console.log('Adding submit event listener to login form...');
        loginForm.addEventListener('submit', handleLogin);
        console.log('Submit event listener added successfully!');
    } else {
        console.error('❌ Login form element NOT found!');
    }
}

function handleLogin(e) {
    e.preventDefault();

    const email = document.getElementById('email').value;
    const password = document.getElementById('password').value;

    if (!email || !password) {
        showNotification('Please enter both email and password.', 'error');
        return;
    }

    // Show loading state
    const submitBtn = e.target.querySelector('button[type="submit"]');
    const originalText = submitBtn.innerHTML;
    submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Signing In...';
    submitBtn.disabled = true;

    // Call PHP backend for authentication
    fetch('server/auth.php?action=login', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
        },
        credentials: 'same-origin', // Include cookies for session
        body: JSON.stringify({
            email: email,
            password: password
        })
    })
    .then(response => response.json())
    .then(data => {
        if (data.ok) {
            // Store user info in localStorage for UI convenience
            currentUser = {
                email: data.email,
                name: data.name,
                type: data.type,
                loginTime: new Date().toISOString()
            };
            localStorage.setItem('currentUser', JSON.stringify(currentUser));

            // Show success message and redirect
            showNotification('Login successful! Welcome to your portal.', 'success');

            setTimeout(() => {
                window.location.href = 'dashboard.html';
            }, 1000);
        } else {
            showNotification(data.error || 'Login failed. Please try again.', 'error');
        }
    })
    .catch(error => {
        console.error('Login error:', error);
        showNotification('An error occurred during login. Please try again.', 'error');
    })
    .finally(() => {
        // Restore button state
        submitBtn.innerHTML = originalText;
        submitBtn.disabled = false;
    });
}

function showStudentPortal() {
    if (!currentUser) return;
    
    // Update user info
    const userEmailElement = document.getElementById('user-email');
    if (userEmailElement) {
        userEmailElement.textContent = currentUser.email;
    }
    
    // Load student's hackathons
    loadStudentHackathons();
    
    // Redirect to the new dashboard page instead of showing old student portal
    window.location.href = 'dashboard.html';
}

function loadStudentHackathons() {
    const container = document.getElementById('student-hackathons');
    if (!container) return;
    
    container.innerHTML = '';
    
    studentHackathons.forEach(hackathon => {
        const card = createStudentHackathonCard(hackathon);
        container.appendChild(card);
    });
}

function createStudentHackathonCard(hackathon) {
    const card = document.createElement('div');
    card.className = 'hackathon-card';
    
    const resultClass = hackathon.result === 'Winner' ? 'winner' : 
                       hackathon.result === 'Finalist' ? 'finalist' : 'participant';
    
    card.innerHTML = `
        <h3>${hackathon.title}</h3>
        <div class="date">${hackathon.date}</div>
        <p class="description">${hackathon.description}</p>
        <div class="student-details">
            <span><i class="fas fa-users"></i> Team: ${hackathon.team}</span>
            <span><i class="fas fa-flag"></i> ${hackathon.participation}</span>
        </div>
        <div class="result ${resultClass}">
            <i class="fas fa-${hackathon.result === 'Winner' ? 'crown' : 'medal'}"></i> 
            ${hackathon.result}
        </div>
    `;
    
    return card;
}

function logout() {
    // Call PHP backend to clear server session
    fetch('server/auth.php?action=logout', {
        method: 'POST',
        credentials: 'same-origin'
    })
    .then(response => response.json())
    .then(data => {
        console.log('Server logout response:', data);
    })
    .catch(error => {
        console.error('Server logout error:', error);
    })
    .finally(() => {
        // Always clear local storage and redirect
        try {
            currentUser = null;
            localStorage.removeItem('currentUser');
            showNotification('You have been logged out successfully.', 'success');
        } catch (error) {
            console.error('Error clearing local data:', error);
        }

        // Redirect to login page
        setTimeout(() => {
            window.location.href = 'login.html';
        }, 500);
    });
}

// Check authentication status with server
function checkAuthStatus() {
    return fetch('server/auth.php?action=me', {
        method: 'GET',
        credentials: 'same-origin'
    })
    .then(response => response.json())
    .then(data => {
        if (data.ok) {
            // Update localStorage with server data
            currentUser = {
                email: data.email,
                name: data.name,
                type: data.type,
                studentId: data.studentId,
                createdAt: data.createdAt,
                loginTime: new Date().toISOString()
            };
            localStorage.setItem('currentUser', JSON.stringify(currentUser));
            return { authenticated: true, user: currentUser };
        } else {
            // Clear local storage if server says not authenticated
            currentUser = null;
            localStorage.removeItem('currentUser');
            return { authenticated: false };
        }
    })
    .catch(error => {
        console.error('Auth check error:', error);
        // If server check fails, fall back to localStorage but mark as potentially stale
        const storedUser = localStorage.getItem('currentUser');
        if (storedUser) {
            try {
                currentUser = JSON.parse(storedUser);
                return { authenticated: true, user: currentUser, stale: true };
            } catch (e) {
                console.error('Error parsing stored user:', e);
            }
        }
        return { authenticated: false };
    });
}

// Initialize currentUser from localStorage on page load
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
function login(email, password) {
    if (!email || !password) {
        showNotification("Please enter both email and password.", "error");
        return;
    }

    // Send login request to PHP backend
    fetch('php/login.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/x-www-form-urlencoded'
        },
        body: `email=${encodeURIComponent(email)}&password=${encodeURIComponent(password)}`
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            // Store user session (if needed, e.g. for UI convenience)
            localStorage.setItem("currentUser", JSON.stringify({ email: email }));

            showNotification("Login successful! Redirecting...", "success");
            setTimeout(() => {
                window.location.href = "dashboard.html";
            }, 800);
        } else {
            showNotification(data.message || "Invalid login details!", "error");
        }
    })
    .catch(error => {
        console.error('Login error:', error);
        showNotification("An error occurred during login. Please try again.", "error");
    });
}


