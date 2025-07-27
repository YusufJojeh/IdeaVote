# 🚀 IdeaVote - Full-Stack Idea Voting Platform

A modern, responsive web platform for submitting, voting, and discussing innovative ideas. Built with native PHP, MySQL, and Bootstrap 5.

![IdeaVote Platform](https://img.shields.io/badge/PHP-8.0+-777BB4?style=for-the-badge&logo=php&logoColor=white)
![MySQL](https://img.shields.io/badge/MySQL-00000F?style=for-the-badge&logo=mysql&logoColor=white)
![Bootstrap](https://img.shields.io/badge/Bootstrap-563D7C?style=for-the-badge&logo=bootstrap&logoColor=white)
![HTML5](https://img.shields.io/badge/HTML5-E34F26?style=for-the-badge&logo=html5&logoColor=white)
![CSS3](https://img.shields.io/badge/CSS3-1572B6?style=for-the-badge&logo=css3&logoColor=white)

## ✨ Features

### 🎯 Core Functionality
- **User Registration & Authentication** - Secure login/register system with password hashing
- **Idea Submission** - Users can submit ideas with titles, descriptions, categories, and optional images
- **Voting System** - Like/dislike voting on ideas with real-time counters
- **Comments & Discussions** - Engage in conversations about ideas
- **Categories** - Organize ideas by topics and themes
- **Public/Private Ideas** - Control visibility of your submissions

### 🎨 Modern UI/UX
- **Glassmorphism Design** - Beautiful glass-like interface with transparency effects
- **Gold/White/Black Theme** - Luxurious color scheme with gradients
- **Responsive Design** - Mobile-first approach, works on all devices
- **Animated Elements** - Smooth animations and hover effects
- **Bootstrap 5** - Modern CSS framework for consistent styling

### 👥 User Features
- **User Profiles** - Personal profiles with bio, job/education info
- **Profile Images** - Upload custom profile pictures
- **User Chat** - Private messaging between users
- **Idea Management** - Edit and delete your own ideas
- **Account Deletion** - Full account removal capability

### 🔧 Admin Panel
- **Dashboard** - Overview of platform statistics
- **User Management** - Add, edit, delete users and manage roles
- **Idea Management** - Full CRUD operations on ideas
- **Category Management** - Create and manage idea categories
- **Comment Management** - Moderate and edit comments
- **Vote Management** - View and delete votes

### 📱 Additional Features
- **Image Upload** - Support for idea images and profile pictures
- **Real-time Counters** - Animated statistics on landing page
- **Search & Filter** - Find ideas by category and popularity
- **Notifications** - Toast messages for user feedback
- **Security** - SQL injection prevention, XSS protection, input validation

## 🛠️ Installation

### Prerequisites
- PHP 7.4 or higher
- MySQL 5.7 or higher
- Web server (Apache/Nginx)
- XAMPP/WAMP/MAMP (recommended for local development)

### Step 1: Clone/Download
```bash
git clone https://github.com/yourusername/ideavote.git
cd ideavote
```

### Step 2: Database Setup
1. Create a new MySQL database named `idea_vote_platform`
2. Import the database schema:
   ```sql
   -- First, import the base schema
   mysql -u your_username -p idea_vote_platform < sample_data.sql
   
   -- Then, apply updates
   mysql -u your_username -p idea_vote_platform < update_db.sql
   ```

### Step 3: Configuration
1. Edit `includes/config.php`:
   ```php
   define('DB_HOST', 'localhost');
   define('DB_USER', 'your_username');
   define('DB_PASS', 'your_password');
   define('DB_NAME', 'idea_vote_platform');
   ```

### Step 4: File Permissions
```bash
# Create upload directories
mkdir -p assets/images
mkdir -p assets/images/ideas
chmod 755 assets/images
chmod 755 assets/images/ideas
```

### Step 5: Admin Setup
1. Visit `admin_update_password.php` in your browser to set admin password
2. Default admin credentials:
   - Username: `admin`
   - Password: `ab4445`

### Step 6: Access the Platform
Open your browser and navigate to:
```
http://localhost/ideavote/
```

## 📁 Project Structure

```
IdeaVote/
├── assets/
│   ├── css/
│   │   └── landing.css
│   ├── images/
│   │   └── ideas/
│   ├── js/
│   ├── lang/
│   └── svg/
├── includes/
│   ├── auth.php          # Authentication functions
│   ├── config.php        # Database configuration
│   ├── db.php           # Database connection
│   ├── functions.php    # Utility functions
│   └── navbar.php       # Navigation component
├── admin.php            # Admin panel
├── admin_update_password.php
├── contact.php          # Contact form
├── dashboard.php        # User dashboard
├── idea.php            # Single idea view
├── ideas.php           # Ideas listing
├── index.php           # Landing page
├── login.php           # Login form
├── logout.php          # Logout handler
├── profile.php         # User profile
├── profile_others.php  # Other user profiles
├── register.php        # Registration form
├── sample_data.sql     # Database schema
├── update_db.sql       # Database updates
└── README.md
```

## 🗄️ Database Schema

### Tables
- **users** - User accounts and profiles
- **categories** - Idea categories
- **ideas** - Submitted ideas with metadata
- **votes** - User votes on ideas
- **comments** - Comments on ideas
- **messages** - Private chat messages

### Key Fields
- User bio, profile images
- Idea images, public/private flags
- Vote types (like/dislike)
- Timestamps for all activities

## 🎮 Usage Guide

### For Users
1. **Register/Login** - Create an account or sign in
2. **Submit Ideas** - Use the dashboard to create new ideas
3. **Browse Ideas** - Explore ideas by category and popularity
4. **Vote & Comment** - Engage with ideas through voting and discussions
5. **Manage Profile** - Update your information and profile picture
6. **Chat** - Message other users through their profiles

### For Admins
1. **Access Admin Panel** - Use admin credentials to access management tools
2. **Manage Users** - Add, edit, or remove user accounts
3. **Moderate Content** - Review and manage ideas, comments, and votes
4. **Configure Categories** - Create and organize idea categories
5. **Monitor Statistics** - View platform usage and engagement metrics

## 🔒 Security Features

- **Password Hashing** - Bcrypt encryption for all passwords
- **SQL Injection Prevention** - Prepared statements throughout
- **XSS Protection** - Input sanitization and output escaping
- **Session Management** - Secure session handling
- **Input Validation** - Server-side validation for all forms
- **File Upload Security** - Restricted file types and size limits

## 🎨 Design System

### Color Palette
- **Primary Gold**: `#FFD700` - Main accent color
- **Secondary Gold**: `#FFEF8E` - Gradient variations
- **White**: `#FFFFFF` - Background and text
- **Black**: `#181818` - Text and borders

### Typography
- **Font Family**: Inter, Segoe UI, Arial, sans-serif
- **Weights**: 400 (regular), 500 (medium), 700 (bold)

### Components
- **Glass Cards** - Transparent backgrounds with blur effects
- **Gradient Buttons** - Gold gradient with hover animations
- **Animated Icons** - Pulse and float animations
- **Responsive Grid** - Bootstrap 5 grid system

## 🚀 Performance Optimizations

- **Minified CSS/JS** - Optimized asset delivery
- **Image Optimization** - Compressed uploads and thumbnails
- **Database Indexing** - Optimized queries for large datasets
- **Caching** - Session-based caching for user data
- **CDN Integration** - Bootstrap and icon libraries via CDN

## 🔧 Customization

### Adding New Features
1. Create new PHP files in the root directory
2. Include necessary dependencies from `includes/`
3. Follow the existing code structure and naming conventions
4. Update the navbar if needed

### Styling Changes
1. Modify `assets/css/landing.css` for custom styles
2. Update inline styles in PHP files for component-specific styling
3. Follow the existing color scheme and design patterns

### Database Modifications
1. Create SQL migration files
2. Update `includes/functions.php` for new helper functions
3. Test thoroughly before deployment

## 🐛 Troubleshooting

### Common Issues

**Database Connection Error**
- Verify database credentials in `includes/config.php`
- Ensure MySQL service is running
- Check database name exists

**File Upload Issues**
- Verify directory permissions (755 for folders)
- Check PHP upload limits in `php.ini`
- Ensure `assets/images/` directory exists

**Session Errors**
- Check `ob_start()` is called at the top of PHP files
- Verify session configuration in PHP settings

**Admin Access Issues**
- Run `admin_update_password.php` to reset admin password
- Check user role in database (`is_admin = 1`)

### Debug Mode
Enable error reporting by adding to PHP files:
```php
error_reporting(E_ALL);
ini_set('display_errors', 1);
```

## 📈 Future Enhancements

- [ ] Email notifications
- [ ] Advanced search filters
- [ ] Idea sharing functionality
- [ ] Mobile app development
- [ ] API endpoints
- [ ] Multi-language support (Arabic/English)
- [ ] Advanced analytics dashboard
- [ ] Social media integration

## 🤝 Contributing

1. Fork the repository
2. Create a feature branch (`git checkout -b feature/AmazingFeature`)
3. Commit your changes (`git commit -m 'Add some AmazingFeature'`)
4. Push to the branch (`git push origin feature/AmazingFeature`)
5. Open a Pull Request

## 📄 License

This project is licensed under the MIT License - see the [LICENSE](LICENSE) file for details.

## 👨‍💻 Author

**Your Name**
- GitHub: [@yourusername](https://github.com/yourusername)
- Email: your.email@example.com

## 🙏 Acknowledgments

- Bootstrap 5 for the responsive framework
- DiceBear for avatar generation
- Unsplash for stock images
- Bootstrap Icons for the icon set
- CountUp.js for animated counters

---

⭐ **Star this repository if you found it helpful!**

For support, please open an issue or contact the maintainer. 