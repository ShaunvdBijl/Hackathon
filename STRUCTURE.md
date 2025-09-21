# Belgium Campus iTversity - Hackathon Hub
## Modular File Structure

This project has been reorganized into a modular, hierarchical structure for better maintainability and organization.

## 📁 Directory Structure

```
├── index.html                 # Original monolithic file (for reference)
├── index-modular.html         # New modular version
├── components/                # Reusable HTML components
│   └── navigation.html        # Navigation bar component
├── pages/                     # Page-specific HTML sections
│   ├── home.html             # Home page content
│   ├── hackathons.html       # Hackathons page content
│   ├── login.html            # Login page content
│   └── student-portal.html   # Student portal page content
├── partials/                  # HTML partials
│   ├── head.html             # HTML head section
│   └── footer.html           # HTML footer section
├── css/                       # Modular CSS files
│   ├── styles.css            # Main CSS file (imports all modules)
│   ├── base.css              # Base styles and CSS variables
│   ├── navigation.css        # Navigation-specific styles
│   ├── components.css        # Reusable component styles
│   ├── pages.css             # Page-specific styles
│   ├── responsive.css        # Responsive design styles
│   └── animations.css        # Animation and transition styles
├── js/                        # Modular JavaScript files
│   ├── data.js               # Sample data and constants
│   ├── navigation.js         # Navigation functionality
│   ├── hackathons.js         # Hackathons page functionality
│   ├── auth.js               # Authentication functionality
│   ├── utils.js              # Utility functions
│   └── main.js               # Main application logic
├── assets/                    # Static assets
│   ├── images/               # Image files
│   └── icons/                # Icon files
├── README.md                  # Project documentation
└── STRUCTURE.md              # This file
```

## 🎯 Benefits of Modular Structure

### **Maintainability**
- **Separation of Concerns**: Each file has a specific purpose
- **Easy Updates**: Modify specific features without affecting others
- **Code Reusability**: Components can be reused across pages

### **Development**
- **Team Collaboration**: Multiple developers can work on different modules
- **Version Control**: Better tracking of changes to specific features
- **Debugging**: Easier to locate and fix issues

### **Performance**
- **Selective Loading**: Load only necessary CSS/JS modules
- **Caching**: Better browser caching of individual modules
- **Minification**: Easier to minify specific modules

## 📋 File Descriptions

### **HTML Components**

#### `components/navigation.html`
- Reusable navigation bar
- Contains logo, menu items, and mobile hamburger menu
- Can be included in any page

#### `pages/`
- **`home.html`**: Hero section and features grid
- **`hackathons.html`**: Hackathons listing with tabs
- **`login.html`**: Student login form
- **`student-portal.html`**: Student dashboard and history

#### `partials/`
- **`head.html`**: HTML head with meta tags and CSS links
- **`footer.html`**: Footer with JavaScript includes

### **CSS Modules**

#### `css/base.css`
- CSS reset and base styles
- CSS custom properties (variables)
- Container and page management styles

#### `css/navigation.css`
- Navigation bar styling
- Mobile menu styles
- Logo and menu item styles

#### `css/components.css`
- Button styles (primary, secondary)
- Card components (feature cards, hackathon cards)
- Status badges and form elements

#### `css/pages.css`
- Page-specific layouts
- Hero section styling
- Login form styling
- Student portal styling

#### `css/responsive.css`
- Mobile-first responsive design
- Media queries for different screen sizes
- Mobile navigation adjustments

#### `css/animations.css`
- Page transition animations
- Loading states and spinners
- Notification animations

### **JavaScript Modules**

#### `js/data.js`
- Sample hackathon data
- Student hackathon history
- Constants and configuration

#### `js/navigation.js`
- Page navigation functionality
- Mobile menu handling
- Active link management

#### `js/hackathons.js`
- Hackathon listing functionality
- Tab switching
- Card creation and rendering

#### `js/auth.js`
- Login/logout functionality
- Student portal management
- User session handling

#### `js/utils.js`
- Notification system
- Date formatting utilities
- Helper functions

#### `js/main.js`
- Application initialization
- Global state management
- Event listeners setup

## 🚀 Usage

### **Option 1: Modular Version (Recommended)**
Use `index-modular.html` which dynamically loads all components:
```bash
# Open in browser
open index-modular.html
```

### **Option 2: Original Monolithic Version**
Use the original `index.html` for simple deployment:
```bash
# Open in browser
open index.html
```

## 🔧 Customization

### **Adding New Pages**
1. Create HTML file in `pages/` directory
2. Add corresponding CSS in `pages.css`
3. Add JavaScript functionality in appropriate module
4. Update navigation in `components/navigation.html`

### **Adding New Components**
1. Create HTML file in `components/` directory
2. Add styles in `components.css`
3. Add JavaScript functionality in appropriate module

### **Modifying Styles**
- **Brand Colors**: Update CSS variables in `css/base.css`
- **Component Styles**: Modify `css/components.css`
- **Page Layouts**: Update `css/pages.css`
- **Responsive Design**: Adjust `css/responsive.css`

## 🎨 Belgium Campus iTversity Branding

The modular structure maintains the university's branding:
- **Primary Colors**: Black (`#1a1a1a`) and Gold (`#ffd700`)
- **Typography**: Professional, clean fonts
- **Layout**: Modern, responsive design
- **Components**: Consistent styling across all modules

## 📱 Browser Compatibility

- Chrome (recommended)
- Firefox
- Safari
- Edge
- Mobile browsers (iOS Safari, Chrome Mobile)

## 🔄 Migration from Monolithic

To migrate from the original structure:
1. Use `index-modular.html` instead of `index.html`
2. All functionality remains the same
3. Better organization and maintainability
4. Easier to extend and customize

---

**Note**: The modular structure provides the same functionality as the original monolithic version but with better organization, maintainability, and development experience.
