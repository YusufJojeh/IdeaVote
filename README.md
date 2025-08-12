# IdeaVote - Share Ideas, Win Votes, Build Together 

A modern, feature-rich platform for sharing and voting on innovative ideas. Built with PHP, MySQL, and Bootstrap 5, featuring full bilingual support, real-time interactions, and a comprehensive admin system.

## ✨ Features

### 🌍 Multi-Language Support
- **Full Bilingual Interface**: Arabic and English with RTL support
- **Dynamic Language Switching**: Real-time language changes
- **Localized Content**: All text, dates, and numbers properly localized
- **RTL Layout**: Perfect Arabic text rendering and layout

### 🎨 Theme System
- **Dark/Light Mode**: Toggle between themes with persistent preferences
- **Auto Theme Detection**: System preference detection
- **Smooth Transitions**: Beautiful theme switching animations
- **Consistent Styling**: Unified design across all pages

###  Core Features
- **Idea Creation & Management**: Create, edit, and organize innovative ideas
- **Smart Voting System**: Like/dislike with real-time vote counts
- **Emoji Reactions**: Express emotions with reactions (❤️, , , ⭐, etc.)
- **Rich Comments**: Engage in meaningful discussions
- **Category Organization**: Organize ideas by topics and interests
- **Bookmark System**: Save and organize favorite ideas
- **User Profiles**: Comprehensive user profiles with activity history

###  Real-Time Features
- **Live Notifications**: Real-time notification system
- **Dynamic Updates**: Live vote counts and reaction updates
- **Instant Feedback**: Immediate response to user actions
- **WebSocket Integration**: Real-time communication (ready for implementation)

### 👥 Social Features
- **User Following**: Follow other users and see their activity
- **Activity Feeds**: Personalized activity streams
- **User Interactions**: Like, comment, and react on ideas
- **Community Building**: Connect with like-minded innovators

### ️ Admin System
- **Comprehensive Dashboard**: Full-featured admin panel
- **User Management**: View, edit, delete, and manage user accounts
- **Content Moderation**: Review and moderate reported content
- **Idea Management**: Feature, edit, and manage ideas
- **Category Management**: Create and organize categories
- **Audit Logging**: Complete action tracking and history
- **Statistics & Analytics**: Detailed platform insights
- **System Settings**: Configure platform-wide options

### 🔒 Security & Performance
- **Advanced Security**: CSRF protection, SQL injection prevention, XSS protection
- **Rate Limiting**: Prevent abuse and brute force attacks
- **Password Security**: Secure hashing with bcrypt
- **Session Management**: Secure session handling
- **File Upload Security**: Validated and secure file uploads
- **Performance Optimization**: Caching, lazy loading, optimized queries
- **SEO Friendly**: Clean URLs, meta tags, structured data

###  Responsive Design
- **Mobile-First**: Optimized for all device sizes
- **Touch-Friendly**: Perfect mobile interaction
- **Progressive Enhancement**: Works on all browsers
- **Accessibility**: WCAG compliant with keyboard navigation

## 🚀 Quick Start

### Prerequisites
- **PHP**: 8.0 or higher
- **MySQL**: 5.7 or higher (or MariaDB 10.2+)
- **Web Server**: Apache 2.4+ or Nginx
- **Extensions**: mod_rewrite, PDO, JSON, mbstring

### Installation

1. **Clone the Repository**
   ```bash
   git clone https://github.com/yourusername/ideavote.git
   cd ideavote
   ```

2. **Set Up Database**
   ```bash
   # Create database
   mysql -u root -p -e "CREATE DATABASE ideavote CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;"
   
   # Import schema (run in order)
   mysql -u root -p ideavote < db/migrations/001_phase1.sql
   mysql -u root -p ideavote < db/migrations/002_password_reset.sql
   mysql -u root -p ideavote < db/migrations/003_modern_features.sql
   ```

3. **Configure Application**
   ```bash
   # Copy configuration template
   cp config.template.php includes/config.php
   
   # Edit with your settings
   nano includes/config.php
   ```

4. **Set Permissions**
   ```bash
   chmod +x deploy.sh
   ./deploy.sh
   ```

5. **Create Admin User**
   ```bash
   # Register normally through the website
   # Then promote to admin:
   mysql -u root -p ideavote -e "UPDATE users SET is_admin = 1 WHERE username = 'your_username';"
   ```

## 📁 Project Structure

```
ideavote/
├── 📁 actions/                 # AJAX handlers and API endpoints
│   ├── bookmarks.php          # Bookmark management
│   ├── follow.php             # User following system
│   ├── language.php           # Language switching
│   ├── notifications.php      # Notification system
│   ├── password_reset.php     # Password reset functionality
│   ├── reactions.php          # Emoji reactions
│   ├── vote.php               # Voting system
│   └── webhook.php            # Webhook handlers
├── 📁 assets/                 # Static assets
│   ├──  css/               # St
``` 