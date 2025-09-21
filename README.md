# Belgium Campus iTversity - Hackathon Hub

A modern, responsive website for hosting hackathons with student portal functionality, branded for Belgium Campus iTversity.

## Features

### 🏠 Welcome Page
- Hero section with call-to-action buttons
- Feature highlights showcasing the platform benefits
- Modern gradient design with smooth animations

### 🏆 Hackathons Page
- **Upcoming Hackathons**: View future coding competitions
- **Past Hackathons**: Browse completed events with winners
- Tabbed interface for easy navigation
- Detailed hackathon cards with:
  - Event dates and descriptions
  - Participant counts and prize information
  - Status indicators (upcoming/past)

### 🔐 Login System
- Student portal authentication
- Demo credentials: `student@example.com` / `password123`
- Secure session management with localStorage
- User-friendly login form with validation

### 👨‍🎓 Student Portal
- Personalized dashboard for logged-in students
- View past hackathon participation history
- Track team performance and results
- Display achievements and rankings

## Technical Features

### 🎨 Modern Design
- Responsive layout that works on all devices
- Belgium Campus iTversity branded color scheme (black, gold, white)
- Beautiful gradient backgrounds and smooth animations
- Font Awesome icons for enhanced visual appeal
- Mobile-first design with hamburger menu

### ⚡ Interactive Elements
- Smooth page transitions
- Dynamic content loading
- Real-time notifications
- Mobile-responsive navigation

### 🔧 JavaScript Functionality
- Single Page Application (SPA) architecture
- Local storage for user sessions
- Dynamic content rendering
- Form validation and error handling

## File Structure

### **Modular Structure (Recommended)**
```
├── index-modular.html      # Main HTML file (modular version)
├── components/             # Reusable HTML components
│   └── navigation.html     # Navigation bar component
├── pages/                  # Page-specific HTML sections
│   ├── home.html          # Home page content
│   ├── hackathons.html    # Hackathons page content
│   ├── login.html         # Login page content
│   └── student-portal.html # Student portal page content
├── css/                    # Modular CSS files
│   ├── styles.css         # Main CSS file (imports all modules)
│   ├── base.css           # Base styles and CSS variables
│   ├── navigation.css     # Navigation-specific styles
│   ├── components.css     # Reusable component styles
│   ├── pages.css          # Page-specific styles
│   ├── responsive.css     # Responsive design styles
│   └── animations.css     # Animation and transition styles
├── js/                     # Modular JavaScript files
│   ├── data.js            # Sample data and constants
│   ├── navigation.js      # Navigation functionality
│   ├── hackathons.js      # Hackathons page functionality
│   ├── auth.js            # Authentication functionality
│   ├── utils.js           # Utility functions
│   └── main.js            # Main application logic
└── README.md              # This documentation
```

### **Original Structure (Simple)**
```
├── index.html          # Main HTML structure
├── css/styles.css      # CSS styling and responsive design
├── js/script.js        # JavaScript functionality
└── README.md           # This documentation
```

## Getting Started

### **Option 1: Modular Version (Recommended)**
1. **Open the website**: Open `index-modular.html` in your web browser
2. **Navigate**: Use the top navigation to explore different sections
3. **Login**: Click "Login" and use the demo credentials to access the student portal
4. **Explore**: Browse upcoming and past hackathons, view your participation history

### **Option 2: Simple Version**
1. **Open the website**: Open `index.html` in your web browser
2. **Navigate**: Use the top navigation to explore different sections
3. **Login**: Click "Login" and use the demo credentials to access the student portal
4. **Explore**: Browse upcoming and past hackathons, view your participation history

## Demo Credentials

- **Email**: `student@example.com`
- **Password**: `password123`

## Browser Compatibility

- Chrome (recommended)
- Firefox
- Safari
- Edge
- Mobile browsers (iOS Safari, Chrome Mobile)

## Customization

### Adding New Hackathons
Edit the `hackathonsData` object in `script.js`:

```javascript
const hackathonsData = {
    upcoming: [
        {
            id: 1,
            title: "Your Hackathon Title",
            date: "Date Range",
            description: "Event description",
            status: "upcoming",
            participants: 100,
            prize: "$5,000"
        }
    ]
};
```

### Styling Changes
Modify `styles.css` to customize:
- Color schemes
- Typography
- Layout spacing
- Animation effects

### Adding Features
Extend `script.js` to add:
- User registration
- Hackathon registration
- Team formation
- Real-time updates

## Features Overview

| Feature | Status | Description |
|---------|--------|-------------|
| Welcome Page | ✅ Complete | Hero section with features |
| Hackathons Listing | ✅ Complete | Upcoming and past events |
| Login System | ✅ Complete | Student authentication |
| Student Portal | ✅ Complete | Personal dashboard |
| Responsive Design | ✅ Complete | Mobile-friendly layout |
| Interactive UI | ✅ Complete | Smooth animations and transitions |

## Future Enhancements

- User registration system
- Hackathon registration functionality
- Team formation features
- Real-time notifications
- Admin dashboard
- Payment integration
- Social features (comments, likes)
- File upload for submissions

## Support

This is a demo website built with HTML, CSS, and JavaScript. For production use, consider adding:
- Backend server integration
- Database connectivity
- User authentication security
- Payment processing
- Email notifications

Enjoy your hackathon platform! 🚀
