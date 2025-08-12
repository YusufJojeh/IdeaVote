/**
 * IdeaVote Theme Manager
 * Handles theme switching between light and dark mode
 */

const ThemeManager = {
    // Initialize the theme manager
    init: function() {
        const savedTheme = localStorage.getItem('theme');
        
        if (savedTheme) {
            this.setTheme(savedTheme);
        } else {
            // Check for system preference
            const prefersDark = window.matchMedia('(prefers-color-scheme: dark)').matches;
            this.setTheme(prefersDark ? 'dark' : 'light');
        }
        
        // Add event listeners for theme toggle buttons
        document.addEventListener('DOMContentLoaded', () => {
            const toggleButtons = document.querySelectorAll('.theme-toggle');
            toggleButtons.forEach(button => {
                button.addEventListener('click', () => this.toggleTheme());
            });
            // Set icon state on load
            this.updateToggleIcons();
        });
    },
    
    // Toggle between light and dark themes
    toggleTheme: function() {
        const currentTheme = document.documentElement.getAttribute('data-theme') || 'light';
        const newTheme = currentTheme === 'light' ? 'dark' : 'light';
        
        this.setTheme(newTheme);
    },
    
    // Set a specific theme
    setTheme: function(theme) {
        document.documentElement.setAttribute('data-theme', theme);
        localStorage.setItem('theme', theme);
        this.updateToggleIcons();
        
        // Dispatch event for other components to react to theme change
        const event = new CustomEvent('themeChanged', { detail: { theme } });
        document.dispatchEvent(event);
    },

    updateToggleIcons: function() {
        const theme = document.documentElement.getAttribute('data-theme') || 'light';
        const toggleButtons = document.querySelectorAll('.theme-toggle');
        toggleButtons.forEach(button => {
            if (theme === 'dark') {
                button.innerHTML = '<i class="bi bi-sun-fill"></i>';
            } else {
                button.innerHTML = '<i class="bi bi-moon-fill"></i>';
            }
        });
    }
};

// Initialize theme on page load
ThemeManager.init();
