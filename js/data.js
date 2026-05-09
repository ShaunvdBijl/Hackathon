// Sample hackathon data
const hackathonsData = {
    upcoming: [
        {
            id: "stellenbosch-2025",
            title: "Stellenbosch Campus Hackathon",
            date: "11 October, 08:00 – 20:00",
            description: "Join us for an exciting hackathon at the Stellenbosch campus. Build innovative solutions and compete for amazing prizes!",
            status: "upcoming",
            participants: "Teams of 3",
            prize: "Top 3 Prizes",
            detailsDoc: "First hackathon full details.docx",
            maxParticipants: null, // No participant cap
            currentParticipants: 0,
            registrationDeadline: "2025-12-31", // Extended deadline to end of 2025
            requirements: ["Stellenbosch students only", "Valid student ID", "Team formation allowed"],
            categories: ["Web Development", "Mobile Apps", "AI/ML", "IoT", "Game Development"]
        },
        {
            id: "hackathon-2026-senses",
            title: "Hackathon 2026",
            date: "11 July 2026, 08:00 - 12 July 2026, 08:00 (24 hours)",
            description: "Theme: Senses. Teams choose one sense (taste, sight, hearing, smell, touch) and build a project where that sense triggers a real action.",
            status: "upcoming",
            participants: "Teams of up to 4 (solo allowed)",
            prize: "24-Hour Senses Challenge",
            maxParticipants: null, // No participant cap
            currentParticipants: 0,
            registrationDeadline: "2026-07-10",
            requirements: [
                "Choose one sense: taste, sight, hearing, smell, or touch",
                "Sense input must trigger a measurable outcome",
                "AI use allowed only during scheduled research timeslots",
                "No AI use outside allowed timeslots",
                "Students may arrive with a pre-formed team or create one onsite",
                "Maximum 4 students per team; solo entries are allowed but disadvantaged"
            ],
            categories: ["Taste", "Sight", "Hearing", "Smell", "Touch"]
        }
    ],
    past: []
};

// Student hackathon history (simulated data)
const studentHackathons = [];

// Hackathon registration system
class HackathonRegistration {
    constructor() {
        this.registrations = this.loadRegistrations();
    }

    // Load registrations from localStorage
    loadRegistrations() {
        try {
            const stored = localStorage.getItem('hackathonRegistrations');
            return stored ? JSON.parse(stored) : {};
        } catch (error) {
            console.error('Error loading registrations:', error);
            return {};
        }
    }

    // Save registrations to localStorage
    saveRegistrations() {
        try {
            localStorage.setItem('hackathonRegistrations', JSON.stringify(this.registrations));
        } catch (error) {
            console.error('Error saving registrations:', error);
        }
    }

    // Check if user is registered for a hackathon
    isUserRegistered(hackathonId, userEmail) {
        const hackathonRegs = this.registrations[hackathonId] || [];
        return hackathonRegs.some(reg => reg.userEmail === userEmail);
    }

    // Get user's registration for a hackathon
    getUserRegistration(hackathonId, userEmail) {
        const hackathonRegs = this.registrations[hackathonId] || [];
        return hackathonRegs.find(reg => reg.userEmail === userEmail);
    }

    // Register user for a hackathon
    async registerUser(hackathonId, userEmail, registrationData) {
        try {
            // Check if user is already registered
            if (this.isUserRegistered(hackathonId, userEmail)) {
                throw new Error('You are already registered for this hackathon');
            }

            // Check if hackathon is full (only if there's a participant cap)
            const hackathon = this.findHackathon(hackathonId);
            if (hackathon && hackathon.maxParticipants !== null && hackathon.currentParticipants >= hackathon.maxParticipants) {
                throw new Error('This hackathon is full');
            }

            // Check if registration deadline has passed
            if (hackathon && new Date() > new Date(hackathon.registrationDeadline)) {
                throw new Error('Registration deadline has passed');
            }

            // Create registration
            const registration = {
                id: `reg_${Date.now()}`,
                hackathonId,
                userEmail,
                registrationDate: new Date().toISOString(),
                status: 'registered',
                ...registrationData
            };

            // Add to registrations
            if (!this.registrations[hackathonId]) {
                this.registrations[hackathonId] = [];
            }
            this.registrations[hackathonId].push(registration);

            // Update hackathon participant count (only if tracking is enabled)
            if (hackathon && hackathon.maxParticipants !== null) {
                hackathon.currentParticipants++;
            }

            // Save to localStorage
            this.saveRegistrations();

            // Save to server
            await this.saveToServer(registration);

            return registration;
        } catch (error) {
            throw error;
        }
    }

    // Unregister user from a hackathon
    async unregisterUser(hackathonId, userEmail) {
        try {
            const hackathonRegs = this.registrations[hackathonId] || [];
            const userIndex = hackathonRegs.findIndex(reg => reg.userEmail === userEmail);
            
            if (userIndex === -1) {
                throw new Error('You are not registered for this hackathon');
            }

            // Remove registration
            hackathonRegs.splice(userIndex, 1);

            // Update hackathon participant count (only if tracking is enabled)
            const hackathon = this.findHackathon(hackathonId);
            if (hackathon && hackathon.maxParticipants !== null && hackathon.currentParticipants > 0) {
                hackathon.currentParticipants--;
            }

            // Save to localStorage
            this.saveRegistrations();

            // Update server
            await this.updateServerRegistration(hackathonId, userEmail, 'unregistered');

            return true;
        } catch (error) {
            throw error;
        }
    }

    // Find hackathon by ID
    findHackathon(hackathonId) {
        for (const category of Object.values(hackathonsData)) {
            const hackathon = category.find(h => h.id === hackathonId);
            if (hackathon) return hackathon;
        }
        return null;
    }

    // Save registration to server
    async saveToServer(registration) {
        try {
            const response = await fetch('server/hackathon-registrations.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({
                    action: 'register',
                    registration: registration
                })
            });

            if (!response.ok) {
                throw new Error('Failed to save registration to server');
            }

            return await response.json();
        } catch (error) {
            console.error('Error saving to server:', error);
            // Continue with localStorage even if server fails
        }
    }

    // Update server registration
    async updateServerRegistration(hackathonId, userEmail, status) {
        try {
            const response = await fetch('server/hackathon-registrations.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({
                    action: 'update',
                    hackathonId,
                    userEmail,
                    status
                })
            });

            if (!response.ok) {
                throw new Error('Failed to update server registration');
            }

            return await response.json();
        } catch (error) {
            console.error('Error updating server:', error);
        }
    }

    // Get all registrations for a user
    getUserRegistrations(userEmail) {
        const userRegs = [];
        for (const [hackathonId, registrations] of Object.entries(this.registrations)) {
            const userReg = registrations.find(reg => reg.userEmail === userEmail);
            if (userReg) {
                const hackathon = this.findHackathon(hackathonId);
                userRegs.push({
                    ...userReg,
                    hackathon: hackathon
                });
            }
        }
        return userRegs;
    }
}

// Initialize registration system
const hackathonRegistration = new HackathonRegistration();

// Export for global use
window.hackathonRegistration = hackathonRegistration;
