# Usage Guide - Belgium Campus iTversity Hackathon Hub

## 🚀 How to Use the Website

### **Option 1: Full Modular Website (Recommended)**
Open `index-modular.html` in your browser for the complete experience:
- All pages and functionality
- Dynamic component loading
- Full navigation between pages

### **Option 2: Original Simple Version**
Open `index.html` in your browser:
- Single file with all content
- Same functionality as modular version
- Easier for simple deployment

### **Option 3: Individual Page Testing**
For testing individual pages with CSS:

#### **Standalone Pages (with CSS)**
- `pages/home-standalone.html` - Home page with full styling
- `pages/hackathons-standalone.html` - Hackathons page with full styling  
- `pages/login-standalone.html` - Login page with full styling
- `pages/student-portal-standalone.html` - Student portal page with full styling

#### **Test Pages**
- `test-css.html` - Simple test page to verify CSS is working
- `test-navigation.html` - Comprehensive test page for navigation between standalone pages

### **Option 4: Component Fragments (for development)**
The files in `pages/` directory are HTML fragments:
- `pages/home.html` - Home page fragment (no CSS)
- `pages/hackathons.html` - Hackathons page fragment (no CSS)
- `pages/login.html` - Login page fragment (no CSS)
- `pages/student-portal.html` - Student portal fragment (no CSS)

**Note**: These fragments are designed to be loaded into `index-modular.html`, not opened directly.

## 🔧 Troubleshooting

### **CSS Not Loading?**
1. **Check file paths**: Make sure you're opening files from the correct directory
2. **Use standalone versions**: Try `pages/home-standalone.html` instead of `pages/home.html`
3. **Test CSS**: Open `test-css.html` to verify CSS is working
4. **Check browser console**: Look for any error messages

### **JavaScript Not Working?**
1. **Use full version**: Open `index-modular.html` for complete functionality
2. **Check file paths**: Ensure all JS files are in the correct `js/` directory
3. **Browser compatibility**: Use a modern browser (Chrome, Firefox, Safari, Edge)

### **Navigation Issues?**
1. **Use main file**: Open `index-modular.html` for proper navigation
2. **Check links**: Standalone pages link back to the main modular version

## 📁 File Structure Quick Reference

```
├── index.html                    # Simple version (all-in-one)
├── index-modular.html           # Modular version (recommended)
├── test-css.html                # CSS test page
├── pages/
│   ├── home.html                # Home fragment (no CSS)
│   ├── home-standalone.html     # Home page with CSS
│   ├── hackathons.html          # Hackathons fragment (no CSS)
│   ├── hackathons-standalone.html # Hackathons page with CSS
│   ├── login.html               # Login fragment (no CSS)
│   ├── login-standalone.html    # Login page with CSS
│   ├── student-portal.html      # Student portal fragment (no CSS)
│   └── student-portal-standalone.html # Student portal page with CSS
├── css/                         # All CSS files
└── js/                          # All JavaScript files
```

## 🎯 Quick Start

1. **For full website**: Open `index-modular.html`
2. **For testing navigation**: Open `test-navigation.html`
3. **For testing CSS**: Open `test-css.html`
4. **For individual pages**: Open `pages/*-standalone.html` files
5. **For development**: Use the fragment files in `pages/` directory

## 🔑 Demo Credentials

- **Email**: `student@example.com`
- **Password**: `password123`

---

**Remember**: The modular structure is designed for development and maintainability. For the best user experience, always use `index-modular.html` or `index.html`.
