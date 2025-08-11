# IdeaVote - Modern Voting Platform

A full-featured, modern voting and idea sharing platform built with PHP, MySQL, and Bootstrap 5. Features a comprehensive notification system, social features, and advanced user engagement tools.

## 🚀 Modern Features Implemented

### 🔔 **Notifications System**
- **Real-time notifications** for votes, comments, and follows
- **In-app notification dropdown** with unread badges
- **Auto-refresh** every 30 seconds
- **Mark as read** functionality (individual and bulk)
- **Notification types**: vote, comment, follow, mention, system

### 👥 **Social Features**
- **Follow system**: Follow users and categories
- **Reactions**: Beyond simple votes - like, love, fire, laugh, wow, sad, angry
- **Bookmarks**: Save ideas for later
- **User profiles**: Enhanced with bio, avatar, and social stats
- **Activity tracking**: View user engagement history

### 🔐 **Security & Authentication**
- **Full password reset flow** with email tokens
- **CSRF protection** on all forms
- **Session management** with device tracking
- **Rate limiting** for actions
- **Audit logs** for admin actions
- **Content moderation** queue

### 🌐 **Internationalization**
- **Multi-language support** (English/Arabic)
- **RTL layout** support for Arabic
- **Localized dates and numbers**
- **Language switcher** in navbar
- **Comprehensive translation keys**

### 🎨 **Modern UX**
- **Dark mode toggle** with persistent preference
- **Responsive design** with Bootstrap 5
- **Accessibility features** (WCAG 2.1 AA compliant)
- **Micro-interactions** and smooth animations
- **Loading states** and feedback

### 📊 **Analytics & Performance**
- **View counting** for ideas
- **Trending algorithm** with time decay
- **Search functionality** with fulltext indexing
- **Performance optimizations** with database indexes
- **Caching headers** and ETags

### 🔗 **API & Integrations**
- **RESTful webhooks** for external integrations
- **JSON API endpoints** for notifications, follows, reactions
- **Webhook events**: idea.created, vote.created, comment.created, user.registered
- **HMAC signature verification** for webhook security

### 📱 **Mobile-First Design**
- **Touch-friendly** interface
- **Progressive Web App** features
- **Offline capability** for cached content
- **Push notification** ready (infrastructure in place)

## 🛠 **Technical Stack**

- **Backend**: PHP 8.0+ (Procedural)
- **Database**: MySQL 8.0+ with JSON support
- **Frontend**: Bootstrap 5, Font Awesome 6
- **Security**: CSRF tokens, prepared statements, password hashing
- **File Upload**: Image processing with WebP support
- **Email**: SMTP integration ready

## 📁 **Project Structure**

```
IdeaVote/
├── actions/                 # AJAX endpoints
│   ├── notifications.php   # Notification system
│   ├── follow.php          # Follow/unfollow
│   ├── reactions.php       # Emoji reactions
│   ├── bookmarks.php       # Bookmark system
│   ├── password_reset.php  # Password reset flow
│   ├── webhook.php         # Webhook system
│   └── vote.php           # Voting system
├── assets/
│   ├── css/               # Stylesheets
│   ├── images/            # Uploaded images
│   └── lang/              # Translation files
├── db/
│   └── migrations/        # Database migrations
├── includes/              # Core libraries
│   ├── auth.php          # Authentication
│   ├── db.php            # Database connection
│   ├── csrf.php          # CSRF protection
│   ├── i18n.php          # Internationalization
│   ├── notifications.php # Notification helpers
│   └── upload.php        # File upload handling
└── uploads/              # User uploads
```

## 🗄 **Database Schema**

### Core Tables
- `users` - User accounts with preferences
- `ideas` - Ideas with SEO slugs and trending scores
- `categories` - Idea categories
- `votes` - Simple up/down voting
- `comments` - Idea comments
- `messages` - User-to-user messaging

### Modern Features Tables
- `notifications` - User notifications
- `follows` - User following relationships
- `category_follows` - Category following
- `reactions` - Emoji reactions
- `bookmarks` - Saved ideas
- `saved_filters` - User filter preferences
- `audit_logs` - Admin action tracking
- `reported_content` - Content moderation
- `user_sessions` - Device management
- `password_resets` - Password reset tokens

## 🚀 **Installation**

1. **Clone the repository**
   ```bash
   git clone https://github.com/yourusername/IdeaVote.git
   cd IdeaVote
   ```

2. **Set up database**
   ```bash
   # Import the base schema
   mysql -u root -p your_database < db/migrations/001_phase1.sql
   
   # Import password reset support
   mysql -u root -p your_database < db/migrations/002_password_reset.sql
   
   # Import modern features
   mysql -u root -p your_database < db/migrations/003_modern_features.sql
   ```

3. **Configure environment**
   ```bash
   # Copy and edit the environment file
   cp includes/env.example.php includes/env.php
   # Edit database credentials and other settings
   ```

4. **Set up web server**
   - Point document root to the project directory
   - Ensure PHP has write permissions to `uploads/` directory
   - Enable mod_rewrite for clean URLs

5. **Configure email** (optional)
   - Set SMTP settings in `includes/env.php`
   - Configure webhook secret for integrations

## 🔧 **Configuration**

### Environment Variables (`includes/env.php`)
```php
<?php
// Database
$_ENV['DB_HOST'] = 'localhost';
$_ENV['DB_NAME'] = 'ideavote';
$_ENV['DB_USER'] = 'root';
$_ENV['DB_PASS'] = '';

// Email (optional)
$_ENV['SMTP_HOST'] = 'smtp.gmail.com';
$_ENV['SMTP_PORT'] = 587;
$_ENV['SMTP_USER'] = 'your-email@gmail.com';
$_ENV['SMTP_PASS'] = 'your-app-password';

// Webhooks (optional)
$_ENV['WEBHOOK_SECRET'] = 'your-webhook-secret';

// Security
$_ENV['SESSION_SECRET'] = 'your-session-secret';
?>
```

## 📱 **Usage**

### For Users
1. **Register/Login** - Create account or sign in
2. **Submit Ideas** - Share your innovative ideas
3. **Vote & React** - Vote on ideas and use emoji reactions
4. **Follow** - Follow interesting users and categories
5. **Bookmark** - Save ideas for later reading
6. **Engage** - Comment and interact with the community

### For Admins
1. **Moderation** - Review reported content
2. **Analytics** - View platform statistics
3. **User Management** - Manage user accounts
4. **Content Management** - Moderate ideas and comments
5. **Audit Logs** - Track admin actions

## 🔌 **API Endpoints**

### Notifications
- `GET /actions/notifications.php` - Get user notifications
- `POST /actions/notifications.php` - Mark notifications as read

### Social Features
- `POST /actions/follow.php` - Follow/unfollow users/categories
- `POST /actions/reactions.php` - Add/remove reactions
- `POST /actions/bookmarks.php` - Add/remove bookmarks

### Webhooks
- `POST /actions/webhook.php` - Receive webhook events
- Events: `idea.created`, `vote.created`, `comment.created`, `user.registered`

## 🎨 **Customization**

### Themes
- Modify `assets/css/` for custom styling
- Dark mode support built-in
- RTL layout support for Arabic

### Languages
- Add new languages in `assets/lang/`
- Update `includes/i18n.php` for new language support

### Features
- Extend notification types in `includes/notifications.php`
- Add new reaction types in database and UI
- Customize trending algorithm in SQL view

## 🔒 **Security Features**

- **CSRF Protection** - All forms protected
- **SQL Injection Prevention** - Prepared statements
- **XSS Protection** - Output escaping
- **File Upload Security** - Type validation and re-encoding
- **Session Security** - Secure session handling
- **Rate Limiting** - Action throttling
- **Audit Logging** - Admin action tracking

## 📈 **Performance Optimizations**

- **Database Indexing** - Optimized queries
- **Caching Headers** - Browser caching
- **Image Optimization** - WebP support
- **Lazy Loading** - Deferred content loading
- **Minified Assets** - Reduced file sizes

## 🤝 **Contributing**

1. Fork the repository
2. Create a feature branch
3. Make your changes
4. Add tests if applicable
5. Submit a pull request

## 📄 **License**

This project is licensed under the MIT License - see the LICENSE file for details.

## 🆘 **Support**

- **Documentation**: Check this README and inline code comments
- **Issues**: Report bugs via GitHub Issues
- **Discussions**: Use GitHub Discussions for questions

## 🔄 **Changelog**

### v2.0.0 - Modern Features Release
- ✨ Complete notification system
- ✨ Social features (follows, reactions, bookmarks)
- ✨ Full password reset flow
- ✨ Internationalization (EN/AR)
- ✨ Dark mode and modern UX
- ✨ Webhook system for integrations
- ✨ Content moderation tools
- ✨ Performance optimizations
- 🔒 Enhanced security features
- 📱 Mobile-first responsive design

### v1.0.0 - Initial Release
- Basic voting system
- User authentication
- Idea submission and management
- Simple admin panel 