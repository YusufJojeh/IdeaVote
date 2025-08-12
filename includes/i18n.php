<?php
/**
 * Enhanced Internationalization functions
 */

// Default language
$default_language = 'en';

// Available languages
$available_languages = ['en', 'ar'];

// Enhanced translations
$translations = [
    'en' => [
        // Common
        'Home' => 'Home',
        'Ideas' => 'Ideas',
        'My Ideas' => 'My Ideas',
        'Dashboard' => 'Dashboard',
        'Profile' => 'Profile',
        'Login' => 'Login',
        'Register' => 'Register',
        'Logout' => 'Logout',
        'Contact Us' => 'Contact Us',
        'About' => 'About',
        'Submit' => 'Submit',
        'Cancel' => 'Cancel',
        'Save' => 'Save',
        'Delete' => 'Delete',
        'Edit' => 'Edit',
        'Search' => 'Search',
        'Filter' => 'Filter',
        'Sort' => 'Sort',
        'Category' => 'Category',
        'Categories' => 'Categories',
        'Tags' => 'Tags',
        'Comments' => 'Comments',
        'Votes' => 'Votes',
        'Views' => 'Views',
        'Trending' => 'Trending',
        'Popular' => 'Popular',
        'Recent' => 'Recent',
        'Featured' => 'Featured',
        'Public' => 'Public',
        'Private' => 'Private',
        'Admin' => 'Admin',
        'User' => 'User',
        'Settings' => 'Settings',
        'Notifications' => 'Notifications',
        'Language' => 'Language',
        'Theme' => 'Theme',
        'Light' => 'Light',
        'Dark' => 'Dark',
        'Auto' => 'Auto',
        'Email' => 'Email',
        'Password' => 'Password',
        'Confirm Password' => 'Confirm Password',
        'Remember Me' => 'Remember Me',
        'Forgot Password?' => 'Forgot Password?',
        'Reset Password' => 'Reset Password',
        'Username' => 'Username',
        'Bio' => 'Bio',
        'Profile Image' => 'Profile Image',
        'Created At' => 'Created At',
        'Updated At' => 'Updated At',
        'Title' => 'Title',
        'Description' => 'Description',
        'Image' => 'Image',
        'Submit Idea' => 'Submit Idea',
        'Edit Idea' => 'Edit Idea',
        'Delete Idea' => 'Delete Idea',
        'Comment' => 'Comment',
        'Add Comment' => 'Add Comment',
        'Edit Comment' => 'Edit Comment',
        'Delete Comment' => 'Delete Comment',
        'Like' => 'Like',
        'Dislike' => 'Dislike',
        'Follow' => 'Follow',
        'Unfollow' => 'Unfollow',
        'Followers' => 'Followers',
        'Following' => 'Following',
        'Bookmark' => 'Bookmark',
        'Bookmarked' => 'Bookmarked',
        'Remove Bookmark' => 'Remove Bookmark',
        'Report' => 'Report',
        'Share' => 'Share',
        'Toggle Theme' => 'Toggle Theme',
        'Read' => 'Read',
        'General' => 'General',
        'View all notifications' => 'View all notifications',
        'Failed to load notifications' => 'Failed to load notifications',
        'Please refresh the page' => 'Please refresh the page',

        // Landing / marketing
        'The community where ideas win' => 'The community where ideas win',
        'Voteapp by IdeaVote — Share Ideas, Win Votes, Build Together' => 'Voteapp by IdeaVote — Share Ideas, Win Votes, Build Together',
        'Post ideas, gather reactions, and climb the trending board. Collaborate with a global community to turn sparks into products.' => 'Post ideas, gather reactions, and climb the trending board. Collaborate with a global community to turn sparks into products.',
        'Submit your idea' => 'Submit your idea',
        'Explore trending' => 'Explore trending',
        'Join for free' => 'Join for free',
        'Browse ideas' => 'Browse ideas',
        'Ideas shared' => 'Ideas shared',
        'Members' => 'Members',
        'Votes cast' => 'Votes cast',
        'Trending now' => 'Trending now',
        'Fresh ideas getting the most love right now.' => 'Fresh ideas getting the most love right now.',
        'See all' => 'See all',
        'No trending ideas yet. Be the first to post!' => 'No trending ideas yet. Be the first to post!',
        'How it works' => 'How it works',
        'Share your idea' => 'Share your idea',
        'Gather feedback' => 'Gather feedback',
        'Climb the trends' => 'Climb the trends',
        'Made by makers, worldwide' => 'Made by makers, worldwide',
        'Loved by creators' => 'Loved by creators',
        'FAQ' => 'FAQ',
        'Is Voteapp free?' => 'Is Voteapp free?',
        'Do I keep ownership of my ideas?' => 'Do I keep ownership of my ideas?',
        'How does trending work?' => 'How does trending work?',
        'Yes. You can register, post ideas, react, and comment for free.' => 'Yes. You can register, post ideas, react, and comment for free.',
        'Absolutely. You own your content. Public ideas are visible to the community.' => 'Absolutely. You own your content. Public ideas are visible to the community.',
        'A mix of votes, comments, views, and recency — designed to surface quality.' => 'A mix of votes, comments, views, and recency — designed to surface quality.',
        'Ready to launch your idea?' => 'Ready to launch your idea?',
        'Join thousands of makers using Voteapp to validate, build, and grow.' => 'Join thousands of makers using Voteapp to validate, build, and grow.',
        'Open dashboard' => 'Open dashboard',
        'Create your account' => 'Create your account',
        'Contact' => 'Contact',
        'Browse Ideas' => 'Browse Ideas',
        'Join IdeaVote Now' => 'Join IdeaVote Now',

        // Steps descriptions
        'Share your idea desc' => 'Post a concise pitch with images, tags, and goals. Your idea gets a clean public page.',
        'Gather feedback desc' => 'Collect reactions (👍 ❤️ 🔥 😂 🤯), comments, and bookmarks from the community.',
        'Climb the trends desc' => 'Quality engagement boosts your trending score so more people discover it.',

        // Testimonials
        'quote1' => '"Voteapp gave our side-project the first 1,000 users. The feedback loop is golden."',
        'quote2' => '"We validated 3 features before writing any code. The community is amazing."',
        'quote3' => '"The reactions and trending score keep the best ideas on top. Simple and effective."',
        'Made with love by makers.' => 'Made with love by makers.',
        
        // Contact page
        'Get In Touch' => 'Get In Touch',
        'Have questions, feedback, or need assistance? We\'re here to help. Reach out to our team using the form below.' => 'Have questions, feedback, or need assistance? We\'re here to help. Reach out to our team using the form below.',
        'Send Us a Message' => 'Send Us a Message',
        'Thank you! Your message has been sent successfully. We\'ll get back to you soon.' => 'Thank you! Your message has been sent successfully. We\'ll get back to you soon.',
        'Your Name' => 'Your Name',
        'Email Address' => 'Email Address',
        'Subject' => 'Subject',
        'Message' => 'Message',
        'Send Message' => 'Send Message',
        'Contact Information' => 'Contact Information',
        'Our Location' => 'Our Location',
        'Phone Number' => 'Phone Number',
        'Working Hours' => 'Working Hours',
        
        // Dashboard
        'Manage Your Ideas' => 'Manage Your Ideas',
        'Create, edit, and track the performance of your innovative ideas' => 'Create, edit, and track the performance of your innovative ideas',
        'Total Ideas' => 'Total Ideas',
        'Total Votes' => 'Total Votes',
        'Total Views' => 'Total Views',
        'Public Ideas' => 'Public Ideas',
        'Submit New Idea' => 'Submit New Idea',
        'Idea Title' => 'Idea Title',
        'Enter a catchy title for your idea' => 'Enter a catchy title for your idea',
        'Upload Image' => 'Upload Image',
        'Browse...' => 'Browse...',
        'No file selected.' => 'No file selected.',
        
        // Ideas page
        'All Ideas' => 'All Ideas',
        'Discover innovative ideas from creators worldwide' => 'Discover innovative ideas from creators worldwide',
        'Filter by category' => 'Filter by category',
        'All Categories' => 'All Categories',
        'Sort by' => 'Sort by',
        'Most Popular' => 'Most Popular',
        'Most Recent' => 'Most Recent',
        'Most Trending' => 'Most Trending',
        'Search ideas...' => 'Search ideas...',
        'No ideas found' => 'No ideas found',
        'Try adjusting your search or filters' => 'Try adjusting your search or filters',
        
        // Profile
        'Profile' => 'Profile',
        'Edit Profile' => 'Edit Profile',
        'Change Password' => 'Change Password',
        'Account Settings' => 'Account Settings',
        'Joined' => 'Joined',
        'Ideas' => 'Ideas',
        'Followers' => 'Followers',
        'Following' => 'Following',
        'No ideas yet' => 'No ideas yet',
        'This user hasn\'t shared any ideas yet' => 'This user hasn\'t shared any ideas yet',
        
        // Notifications
        'Notifications' => 'Notifications',
        'Mark all as read' => 'Mark all as read',
        'No notifications' => 'No notifications',
        'Failed to load notifications' => 'Failed to load notifications',
        'Please refresh the page' => 'Please refresh the page',
        'View all notifications' => 'View all notifications',
        'Mark as read' => 'Mark as read',
        'New' => 'New',
        'View Idea' => 'View Idea',
        'Are you sure you want to mark all notifications as read?' => 'Are you sure you want to mark all notifications as read?',
        'Refresh' => 'Refresh',
        'All types' => 'All types',
        'All status' => 'All status',
        'Unread only' => 'Unread only',
        'Read only' => 'Read only',
        'No notifications yet' => 'No notifications yet',
        'When you receive notifications, they will appear here' => 'When you receive notifications, they will appear here',
        'Explore Ideas' => 'Explore Ideas',
        'Join thousands of innovators worldwide' => 'Join thousands of innovators worldwide',
        'Active Users' => 'Active Users',
        'Ideas Shared' => 'Ideas Shared',
        'Votes Cast' => 'Votes Cast',
        'Countries' => 'Countries',
        'What creators are saying' => 'What creators are saying',
        'Product Designer' => 'Product Designer',
        'Entrepreneur' => 'Entrepreneur',
        'Tech Lead' => 'Tech Lead',
        'Other' => 'Other',
        'New Category Name' => 'New Category Name',
        'Enter new category name' => 'Enter new category name',
        'Enter a descriptive name for your new category' => 'Enter a descriptive name for your new category',
        'Please select a category or enter a new one.' => 'Please select a category or enter a new one.',
        'Please enter a name for the new category.' => 'Please enter a name for the new category.',
        'Category name must be at least 2 characters long.' => 'Category name must be at least 2 characters long.',
        'Failed to create new category. Please try again.' => 'Failed to create new category. Please try again.',

        // Profile page translations
        'Your Profile' => 'Your Profile',
        'Welcome back,' => 'Welcome back,',
        'Manage your account settings and personal information.' => 'Manage your account settings and personal information.',
        'Submit New Idea' => 'Submit New Idea',
        'Explore Ideas' => 'Explore Ideas',
        'Ideas shared' => 'Ideas shared',
        'Votes cast' => 'Votes cast',
        'Comments' => 'Comments',
        'Likes given' => 'Likes given',
        'Dislikes' => 'Dislikes',
        'No bio yet' => 'No bio yet',
        'Profile updated successfully!' => 'Profile updated successfully!',
        'Personal Information' => 'Personal Information',
        'Update your profile details and account settings' => 'Update your profile details and account settings',
        'Profile Image' => 'Profile Image',
        'Username' => 'Username',
        'Username cannot be changed' => 'Username cannot be changed',
        'Email Address' => 'Email Address',
        'Bio' => 'Bio',
        'Tell us about yourself, your interests, or what you do...' => 'Tell us about yourself, your interests, or what you do...',
        'Share a bit about yourself with the community' => 'Share a bit about yourself with the community',
        'Upload a new profile image (optional)' => 'Upload a new profile image (optional)',
        'New Password' => 'New Password',
        'Leave blank to keep current' => 'Leave blank to keep current',
        'Confirm Password' => 'Confirm Password',
        'Save Changes' => 'Save Changes',
        'Reset' => 'Reset',
        'Cancel' => 'Cancel',
        'Edit Profile' => 'Edit Profile',
        'Member Since' => 'Member Since',
        'Danger Zone' => 'Danger Zone',
        'Once you delete your account, there is no going back. Please be certain.' => 'Once you delete your account, there is no going back. Please be certain.',
        'Delete Account' => 'Delete Account',
        'Are you sure you want to delete your account? This action cannot be undone.' => 'Are you sure you want to delete your account? This action cannot be undone.',
        'Passwords do not match' => 'Passwords do not match',
        'Email already exists.' => 'Email already exists.',
        'Upload failed' => 'Upload failed',
        'Failed to submit idea. Please try again.' => 'Failed to submit idea. Please try again.',
        'Title must be at least 3 characters.' => 'Title must be at least 3 characters.',
        'Description must be at least 10 characters.' => 'Description must be at least 10 characters.',
        'Please select a category.' => 'Please select a category.',
        'Please select a category or enter a new one.' => 'Please select a category or enter a new one.',
        'Please enter a name for the new category.' => 'Please enter a name for the new category.',
        'Category name must be at least 2 characters long.' => 'Category name must be at least 2 characters long.',
        'Failed to create new category. Please try again.' => 'Failed to create new category. Please try again.',
        'Other' => 'Other',
        'New Category Name' => 'New Category Name',
        'Enter new category name' => 'Enter new category name',
        'Enter a descriptive name for your new category' => 'Enter a descriptive name for your new category',
        'Empowering innovation through community voting' => 'Empowering innovation through community voting',
        'All rights reserved' => 'All rights reserved',

        // Admin Panel Translations
        'Admin Dashboard' => 'Admin Dashboard',
        'Dashboard' => 'Dashboard',
        'Users Management' => 'Users Management',
        'Ideas Management' => 'Ideas Management',
        'Categories' => 'Categories',
        'Reported Content' => 'Reported Content',
        'Audit Log' => 'Audit Log',
        'Settings' => 'Settings',
        
        // Dashboard Stats
        'Total Users' => 'Total Users',
        'Total Ideas' => 'Total Ideas',
        'Total Comments' => 'Total Comments',
        'Total Votes' => 'Total Votes',
        'Total Reactions' => 'Total Reactions',
        'Total Bookmarks' => 'Total Bookmarks',
        'Recent Activity' => 'Recent Activity',
        'Pending Reports' => 'Pending Reports',
        'View All' => 'View All',
        'No recent activity' => 'No recent activity',
        'No pending reports' => 'No pending reports',
        
        // User Management
        'ID' => 'ID',
        'Username' => 'Username',
        'Email' => 'Email',
        'Role' => 'Role',
        'Joined' => 'Joined',
        'Actions' => 'Actions',
        'You' => 'You',
        'Admin' => 'Admin',
        'User' => 'User',
        'View' => 'View',
        'Remove Admin' => 'Remove Admin',
        'Make Admin' => 'Make Admin',
        'Delete' => 'Delete',
        'Are you sure you want to remove admin privileges from this user?' => 'Are you sure you want to remove admin privileges from this user?',
        'Are you sure you want to make admin this user?' => 'Are you sure you want to make admin this user?',
        'Are you sure you want to delete this user? This action cannot be undone.' => 'Are you sure you want to delete this user? This action cannot be undone.',
        'You cannot delete your own account.' => 'You cannot delete your own account.',
        'You cannot change your own admin status.' => 'You cannot change your own admin status.',
        'User and all related data deleted successfully.' => 'User and all related data deleted successfully.',
        'User admin status updated successfully.' => 'User admin status updated successfully.',
        
        // Ideas Management
        'Title' => 'Title',
        'Author' => 'Author',
        'Category' => 'Category',
        'Status' => 'Status',
        'Votes' => 'Votes',
        'Created' => 'Created',
        'Approved' => 'Approved',
        'Pending' => 'Pending',
        'Featured' => 'Featured',
        'Not Featured' => 'Not Featured',
        'Uncategorized' => 'Uncategorized',
        'Are you sure you want to delete this idea?' => 'Are you sure you want to delete this idea?',
        'Idea deleted successfully.' => 'Idea deleted successfully.',
        'Idea status updated successfully.' => 'Idea status updated successfully.',
        'Are you sure you want to change this status?' => 'Are you sure you want to change this status?',
        
        // Categories Management
        'Add New Category' => 'Add New Category',
        'English Name' => 'English Name',
        'Arabic Name' => 'Arabic Name',
        'Description' => 'Description',
        'Add Category' => 'Add Category',
        'Category added successfully.' => 'Category added successfully.',
        'Category name is required.' => 'Category name is required.',
        'Category with this name already exists.' => 'Category with this name already exists.',
        'Ideas Count' => 'Ideas Count',
        'Cannot delete (has ideas)' => 'Cannot delete (has ideas)',
        'Are you sure you want to delete this category?' => 'Are you sure you want to delete this category?',
        'Cannot delete category that has ideas. Please reassign ideas first.' => 'Cannot delete category that has ideas. Please reassign ideas first.',
        'Category deleted successfully.' => 'Category deleted successfully.',
        
        // Reported Content
        'Content Type' => 'Content Type',
        'Reporter' => 'Reporter',
        'Reason' => 'Reason',
        'Reported' => 'Reported',
        'Review' => 'Review',
        'Resolve' => 'Resolve',
        'Dismiss' => 'Dismiss',
        'No reported content' => 'No reported content',
        'All content is clean and follows community guidelines.' => 'All content is clean and follows community guidelines.',
        
        // Audit Log
        'Admin' => 'Admin',
        'Action' => 'Action',
        'Table' => 'Table',
        'Record ID' => 'Record ID',
        'IP Address' => 'IP Address',
        'Date' => 'Date',
        
        // Common Actions
        'Edit' => 'Edit',
        'Copy' => 'Copy',
        'Save' => 'Save',
        'Cancel' => 'Cancel',
        'Back' => 'Back',
        'Next' => 'Next',
        'Previous' => 'Previous',
        'Search' => 'Search',
        'Filter' => 'Filter',
        'Sort' => 'Sort',
        'Export' => 'Export',
        'Import' => 'Import',
        
        // Messages
        'Success' => 'Success',
        'Error' => 'Error',
        'Warning' => 'Warning',
        'Info' => 'Info',
        'Loading...' => 'Loading...',
        'No data available' => 'No data available',
        'Records per page' => 'Records per page',
        'Showing' => 'Showing',
        'to' => 'to',
        'of' => 'of',
        'entries' => 'entries',
        
        // Status Badges
        'Active' => 'Active',
        'Inactive' => 'Inactive',
        'Suspended' => 'Suspended',
        'Banned' => 'Banned',
        'Verified' => 'Verified',
        'Unverified' => 'Unverified',
        
        // Time Formats
        'Just now' => 'Just now',
        'minutes ago' => 'minutes ago',
        'hours ago' => 'hours ago',
        'days ago' => 'days ago',
        'weeks ago' => 'weeks ago',
        'months ago' => 'months ago',
        'years ago' => 'years ago',
    ],
    'ar' => [
        // Common
        'Home' => 'الرئيسية',
        'Ideas' => 'الأفكار',
        'My Ideas' => 'أفكاري',
        'Dashboard' => 'لوحة التحكم',
        'Profile' => 'الملف الشخصي',
        'Login' => 'تسجيل الدخول',
        'Register' => 'تسجيل',
        'Logout' => 'تسجيل الخروج',
        'Contact Us' => 'اتصل بنا',
        'About' => 'عن الموقع',
        'Submit' => 'إرسال',
        'Cancel' => 'إلغاء',
        'Save' => 'حفظ',
        'Delete' => 'حذف',
        'Edit' => 'تعديل',
        'Search' => 'بحث',
        'Filter' => 'تصفية',
        'Sort' => 'ترتيب',
        'Category' => 'التصنيف',
        'Categories' => 'التصنيفات',
        'Tags' => 'الوسوم',
        'Comments' => 'التعليقات',
        'Votes' => 'الأصوات',
        'Views' => 'المشاهدات',
        'Trending' => 'الرائج',
        'Popular' => 'الأكثر شعبية',
        'Recent' => 'الأحدث',
        'Featured' => 'المميز',
        'Public' => 'عام',
        'Private' => 'خاص',
        'Admin' => 'مدير',
        'User' => 'مستخدم',
        'Settings' => 'الإعدادات',
        'Notifications' => 'الإشعارات',
        'Language' => 'اللغة',
        'Theme' => 'المظهر',
        'Light' => 'فاتح',
        'Dark' => 'داكن',
        'Auto' => 'تلقائي',
        'Email' => 'البريد الإلكتروني',
        'Password' => 'كلمة المرور',
        'Confirm Password' => 'تأكيد كلمة المرور',
        'Remember Me' => 'تذكرني',
        'Forgot Password?' => 'نسيت كلمة المرور؟',
        'Reset Password' => 'إعادة تعيين كلمة المرور',
        'Username' => 'اسم المستخدم',
        'Bio' => 'نبذة',
        'Profile Image' => 'صورة الملف الشخصي',
        'Created At' => 'تاريخ الإنشاء',
        'Updated At' => 'تاريخ التحديث',
        'Title' => 'العنوان',
        'Description' => 'الوصف',
        'Image' => 'الصورة',
        'Submit Idea' => 'إرسال فكرة',
        'Edit Idea' => 'تعديل الفكرة',
        'Delete Idea' => 'حذف الفكرة',
        'Comment' => 'تعليق',
        'Add Comment' => 'إضافة تعليق',
        'Edit Comment' => 'تعديل التعليق',
        'Delete Comment' => 'حذف التعليق',
        'Like' => 'إعجاب',
        'Dislike' => 'عدم إعجاب',
        'Follow' => 'متابعة',
        'Unfollow' => 'إلغاء المتابعة',
        'Followers' => 'المتابعون',
        'Following' => 'يتابع',
        'Bookmark' => 'حفظ',
        'Bookmarked' => 'محفوظ',
        'Remove Bookmark' => 'إزالة الحفظ',
        'Report' => 'إبلاغ',
        'Share' => 'مشاركة',
        'Toggle Theme' => 'تبديل المظهر',
        'Read' => 'قراءة',
        'General' => 'عام',
        'View all notifications' => 'عرض جميع الإشعارات',
        'Failed to load notifications' => 'فشل في تحميل الإشعارات',
        'Please refresh the page' => 'يرجى تحديث الصفحة',

        // Landing / marketing
        'The community where ideas win' => 'مجتمع تنتصر فيه الأفكار',
        'Voteapp by IdeaVote — Share Ideas, Win Votes, Build Together' => 'Voteapp من IdeaVote — شارك أفكارك، اكسب الأصوات، وابنِ معًا',
        'Post ideas, gather reactions, and climb the trending board. Collaborate with a global community to turn sparks into products.' => 'انشر أفكارك، واجمع التفاعلات، وتصدّر قائمة الرائج. تَعاون مع مجتمع عالمي لتحويل الشرارة إلى منتج.',
        'Submit your idea' => 'قدّم فكرتك',
        'Explore trending' => 'استكشف الرائج',
        'Join for free' => 'انضم مجانًا',
        'Browse ideas' => 'تصفّح الأفكار',
        'Ideas shared' => 'أفكار مُشاركة',
        'Members' => 'أعضاء',
        'Votes cast' => 'أصوات مُدلى بها',
        'Trending now' => 'الرائج الآن',
        'Fresh ideas getting the most love right now.' => 'أحدث الأفكار التي تحظى بأكبر قدر من التفاعل الآن.',
        'See all' => 'عرض الكل',
        'No trending ideas yet. Be the first to post!' => 'لا توجد أفكار رائجة بعد. كن الأول في النشر!',
        'How it works' => 'كيف يعمل',
        'Share your idea' => 'شارك فكرتك',
        'Gather feedback' => 'اجمع الآراء والتفاعلات',
        'Climb the trends' => 'تقدّم في قائمة الرائج',
        'Made by makers, worldwide' => 'من صنع مبدعين حول العالم',
        'Loved by creators' => 'محبوب من المبدعين',
        'FAQ' => 'الأسئلة الشائعة',
        'Is Voteapp free?' => 'هل Voteapp مجاني؟',
        'Do I keep ownership of my ideas?' => 'هل أحتفظ بملكية أفكاري؟',
        'How does trending work?' => 'كيف تعمل خاصية الرائج؟',
        'Yes. You can register, post ideas, react, and comment for free.' => 'نعم. يمكنك التسجيل ونشر الأفكار والتفاعل والتعليق مجانًا.',
        'Absolutely. You own your content. Public ideas are visible to the community.' => 'بالتأكيد. تظل أفكارك مِلكًا لك. تظهر الأفكار العامة للمجتمع.',
        'A mix of votes, comments, views, and recency — designed to surface quality.' => 'مزيج من الأصوات والتعليقات والمشاهدات وحداثة النشر — لعرض الأفضل جودة.',
        'Ready to launch your idea?' => 'هل أنت مستعد لإطلاق فكرتك؟',
        'Join thousands of makers using Voteapp to validate, build, and grow.' => 'انضم إلى آلاف المبدعين الذين يستخدمون Voteapp للتحقق والبناء والنمو.',
        'Open dashboard' => 'افتح لوحة التحكم',
        'Create your account' => 'أنشئ حسابك',
        'Contact' => 'اتصل',
        'Browse Ideas' => 'تصفّح الأفكار',
        'Join IdeaVote Now' => 'انضم إلى IdeaVote الآن',

        // Steps descriptions
        'Share your idea desc' => 'قدّم عرضًا موجزًا مع الصور والوسوم والأهداف. ستحصل فكرتك على صفحة عامة أنيقة.',
        'Gather feedback desc' => 'اجمع التفاعلات (👍 ❤️ 🔥 😂 🤯) والتعليقات والحفظ من المجتمع.',
        'Climb the trends desc' => 'يزيد التفاعل الجيد من نقاط الرواج ليكتشف فكرتك المزيد من الناس.',

        // Testimonials
        'quote1' => '"منصّة Voteapp منحت مشروعنا الجانبي أول 1000 مستخدم. دورة التغذية الراجعة مذهلة."',
        'quote2' => '"تحققنا من 3 ميزات قبل كتابة أي سطر برمجي. المجتمع رائع."',
        'quote3' => '"التفاعلات ونقاط الرواج تُبقي أفضل الأفكار في القمة. بسيطة وفعّالة."',
        'Made with love by makers.' => 'صُنع بحب بواسطة المبدعين.',
        
        // Contact page
        'Get In Touch' => 'تواصل معنا',
        'Have questions, feedback, or need assistance? We\'re here to help. Reach out to our team using the form below.' => 'هل لديك أسئلة أو ملاحظات أو تحتاج إلى مساعدة؟ نحن هنا للمساعدة. تواصل مع فريقنا باستخدام النموذج أدناه.',
        'Send Us a Message' => 'أرسل لنا رسالة',
        'Thank you! Your message has been sent successfully. We\'ll get back to you soon.' => 'شكرا لك! تم إرسال رسالتك بنجاح. سنرد عليك قريبًا.',
        'Your Name' => 'اسمك',
        'Email Address' => 'البريد الإلكتروني',
        'Subject' => 'الموضوع',
        'Message' => 'الرسالة',
        'Send Message' => 'إرسال الرسالة',
        'Contact Information' => 'معلومات الاتصال',
        'Our Location' => 'موقعنا',
        'Phone Number' => 'رقم الهاتف',
        'Working Hours' => 'ساعات العمل',
        
        // Dashboard
        'Manage Your Ideas' => 'إدارة أفكارك',
        'Create, edit, and track the performance of your innovative ideas' => 'أنشئ وعدّل وتتبع أداء أفكارك المبتكرة',
        'Total Ideas' => 'إجمالي الأفكار',
        'Total Votes' => 'إجمالي الأصوات',
        'Total Views' => 'إجمالي المشاهدات',
        'Public Ideas' => 'الأفكار العامة',
        'Submit New Idea' => 'إرسال فكرة جديدة',
        'Idea Title' => 'عنوان الفكرة',
        'Enter a catchy title for your idea' => 'أدخل عنوانًا جذابًا لفكرتك',
        'Upload Image' => 'رفع صورة',
        'Browse...' => 'تصفح...',
        'No file selected.' => 'لم يتم اختيار ملف.',
        
        // Ideas page
        'All Ideas' => 'جميع الأفكار',
        'Discover innovative ideas from creators worldwide' => 'اكتشف أفكارًا مبتكرة من مبدعين حول العالم',
        'Filter by category' => 'تصفية حسب التصنيف',
        'All Categories' => 'جميع التصنيفات',
        'Sort by' => 'ترتيب حسب',
        'Most Popular' => 'الأكثر شعبية',
        'Most Recent' => 'الأحدث',
        'Most Trending' => 'الأكثر رواجًا',
        'Search ideas...' => 'البحث في الأفكار...',
        'No ideas found' => 'لم يتم العثور على أفكار',
        'Try adjusting your search or filters' => 'حاول تعديل البحث أو التصفية',
        
        // Profile
        'Profile' => 'الملف الشخصي',
        'Edit Profile' => 'تعديل الملف الشخصي',
        'Change Password' => 'تغيير كلمة المرور',
        'Account Settings' => 'إعدادات الحساب',
        'Joined' => 'انضم',
        'Ideas' => 'الأفكار',
        'Followers' => 'المتابعون',
        'Following' => 'يتابع',
        'No ideas yet' => 'لا توجد أفكار بعد',
        'This user hasn\'t shared any ideas yet' => 'لم يشارك هذا المستخدم أي أفكار بعد',
        
        // Notifications
        'Notifications' => 'الإشعارات',
        'Mark all as read' => 'تحديد الكل كمقروء',
        'No notifications' => 'لا توجد إشعارات',
        'Failed to load notifications' => 'فشل في تحميل الإشعارات',
        'Please refresh the page' => 'يرجى تحديث الصفحة',
        'View all notifications' => 'عرض جميع الإشعارات',
        'Mark as read' => 'تحديد كمقروء',
        'New' => 'جديد',
        'View Idea' => 'عرض الفكرة',
        'Are you sure you want to mark all notifications as read?' => 'هل أنت متأكد من تحديد جميع الإشعارات كمقروءة؟',
        'Refresh' => 'تحديث',
        'All types' => 'جميع الأنواع',
        'All status' => 'جميع الحالات',
        'Unread only' => 'غير المقروءة فقط',
        'Read only' => 'المقروءة فقط',
        'No notifications yet' => 'لا توجد إشعارات بعد',
        'When you receive notifications, they will appear here' => 'عندما تتلقى إشعارات، ستظهر هنا',
        'Explore Ideas' => 'استكشف الأفكار',
        'Join thousands of innovators worldwide' => 'انضم إلى آلاف المبتكرين حول العالم',
        'Active Users' => 'المستخدمون النشطون',
        'Ideas Shared' => 'الأفكار المُشاركة',
        'Votes Cast' => 'الأصوات المُدلى بها',
        'Countries' => 'الدول',
        'What creators are saying' => 'ماذا يقول المبدعون',
        'Product Designer' => 'مصمم المنتجات',
        'Entrepreneur' => 'رجل أعمال',
        'Tech Lead' => 'قائد تقني',
        'Other' => 'أخرى',
        'New Category Name' => 'اسم التصنيف الجديد',
        'Enter new category name' => 'أدخل اسم التصنيف الجديد',
        'Enter a descriptive name for your new category' => 'أدخل اسمًا وصفيًا للتصنيف الجديد',
        'Please select a category or enter a new one.' => 'يرجى اختيار تصنيف أو إدخال تصنيف جديد.',
        'Please enter a name for the new category.' => 'يرجى إدخال اسم للتصنيف الجديد.',
        'Category name must be at least 2 characters long.' => 'يجب أن يكون اسم التصنيف مكونًا من حرفين على الأقل.',
        'Failed to create new category. Please try again.' => 'فشل في إنشاء التصنيف الجديد. يرجى المحاولة مرة أخرى.',

        // Profile page translations
        'Your Profile' => 'ملفك الشخصي',
        'Welcome back,' => 'مرحباً بعودتك،',
        'Manage your account settings and personal information.' => 'إدارة إعدادات حسابك والمعلومات الشخصية.',
        'Submit New Idea' => 'إرسال فكرة جديدة',
        'Explore Ideas' => 'استكشف الأفكار',
        'Ideas shared' => 'أفكار مُشاركة',
        'Votes cast' => 'أصوات مُدلى بها',
        'Comments' => 'التعليقات',
        'Likes given' => 'إعجابات مُعطاة',
        'Dislikes' => 'عدم إعجابات',
        'No bio yet' => 'لا توجد نبذة بعد',
        'Profile updated successfully!' => 'تم تحديث الملف الشخصي بنجاح!',
        'Personal Information' => 'المعلومات الشخصية',
        'Update your profile details and account settings' => 'تحديث تفاصيل ملفك الشخصي وإعدادات الحساب',
        'Profile Image' => 'صورة الملف الشخصي',
        'Username' => 'اسم المستخدم',
        'Username cannot be changed' => 'لا يمكن تغيير اسم المستخدم',
        'Email Address' => 'عنوان البريد الإلكتروني',
        'Bio' => 'نبذة',
        'Tell us about yourself, your interests, or what you do...' => 'أخبرنا عن نفسك، اهتماماتك، أو ما تفعله...',
        'Share a bit about yourself with the community' => 'شارك قليلاً عن نفسك مع المجتمع',
        'Upload a new profile image (optional)' => 'رفع صورة ملف شخصي جديدة (اختياري)',
        'New Password' => 'كلمة مرور جديدة',
        'Leave blank to keep current' => 'اترك فارغاً للاحتفاظ بالحالية',
        'Confirm Password' => 'تأكيد كلمة المرور',
        'Save Changes' => 'حفظ التغييرات',
        'Reset' => 'إعادة تعيين',
        'Cancel' => 'إلغاء',
        'Edit Profile' => 'تعديل الملف الشخصي',
        'Member Since' => 'عضو منذ',
        'Danger Zone' => 'منطقة الخطر',
        'Once you delete your account, there is no going back. Please be certain.' => 'بمجرد حذف حسابك، لا يمكن التراجع. يرجى التأكد.',
        'Delete Account' => 'حذف الحساب',
        'Are you sure you want to delete your account? This action cannot be undone.' => 'هل أنت متأكد من حذف حسابك؟ لا يمكن التراجع عن هذا الإجراء.',
        'Passwords do not match' => 'كلمات المرور غير متطابقة',
        'Email already exists.' => 'البريد الإلكتروني موجود بالفعل.',
        'Upload failed' => 'فشل الرفع',
        'Failed to submit idea. Please try again.' => 'فشل في إرسال الفكرة. يرجى المحاولة مرة أخرى.',
        'Title must be at least 3 characters.' => 'يجب أن يكون العنوان مكوناً من 3 أحرف على الأقل.',
        'Description must be at least 10 characters.' => 'يجب أن يكون الوصف مكوناً من 10 أحرف على الأقل.',
        'Please select a category.' => 'يرجى اختيار تصنيف.',
        'Please select a category or enter a new one.' => 'يرجى اختيار تصنيف أو إدخال تصنيف جديد.',
        'Please enter a name for the new category.' => 'يرجى إدخال اسم للتصنيف الجديد.',
        'Category name must be at least 2 characters long.' => 'يجب أن يكون اسم التصنيف مكوناً من حرفين على الأقل.',
        'Failed to create new category. Please try again.' => 'فشل في إنشاء التصنيف الجديد. يرجى المحاولة مرة أخرى.',
        'Other' => 'أخرى',
        'New Category Name' => 'اسم التصنيف الجديد',
        'Enter new category name' => 'أدخل اسم التصنيف الجديد',
        'Enter a descriptive name for your new category' => 'أدخل اسماً وصفي للتصنيف الجديد',
        'Empowering innovation through community voting' => 'تمكين الابتكار من خلال التصويت المجتمعي',
        'All rights reserved' => 'جميع الحقوق محفوظة',

        // Admin Panel Translations
        'Admin Dashboard' => 'لوحة تحكم المدير',
        'Dashboard' => 'لوحة التحكم',
        'Users Management' => 'إدارة المستخدمين',
        'Ideas Management' => 'إدارة الأفكار',
        'Categories' => 'الفئات',
        'Reported Content' => 'المحتوى المبلغ عنه',
        'Audit Log' => 'سجل التدقيق',
        'Settings' => 'الإعدادات',
        
        // Dashboard Stats
        'Total Users' => 'إجمالي المستخدمين',
        'Total Ideas' => 'إجمالي الأفكار',
        'Total Comments' => 'إجمالي التعليقات',
        'Total Votes' => 'إجمالي الأصوات',
        'Total Reactions' => 'إجمالي التفاعلات',
        'Total Bookmarks' => 'إجمالي الإشارات المرجعية',
        'Recent Activity' => 'النشاط الأخير',
        'Pending Reports' => 'التقارير المعلقة',
        'View All' => 'عرض الكل',
        'No recent activity' => 'لا يوجد نشاط حديث',
        'No pending reports' => 'لا توجد تقارير معلقة',
        
        // User Management
        'ID' => 'الرقم',
        'Username' => 'اسم المستخدم',
        'Email' => 'البريد الإلكتروني',
        'Role' => 'الدور',
        'Joined' => 'تاريخ الانضمام',
        'Actions' => 'الإجراءات',
        'You' => 'أنت',
        'Admin' => 'مدير',
        'User' => 'مستخدم',
        'View' => 'عرض',
        'Remove Admin' => 'إزالة صلاحيات المدير',
        'Make Admin' => 'تعيين كمدير',
        'Delete' => 'حذف',
        'Are you sure you want to remove admin privileges from this user?' => 'هل أنت متأكد من إزالة صلاحيات المدير من هذا المستخدم؟',
        'Are you sure you want to make admin this user?' => 'هل أنت متأكد من تعيين هذا المستخدم كمدير؟',
        'Are you sure you want to delete this user? This action cannot be undone.' => 'هل أنت متأكد من حذف هذا المستخدم؟ لا يمكن التراجع عن هذا الإجراء.',
        'You cannot delete your own account.' => 'لا يمكنك حذف حسابك الخاص.',
        'You cannot change your own admin status.' => 'لا يمكنك تغيير حالة المدير الخاصة بك.',
        'User and all related data deleted successfully.' => 'تم حذف المستخدم وجميع البيانات المرتبطة بنجاح.',
        'User admin status updated successfully.' => 'تم تحديث حالة مدير المستخدم بنجاح.',
        
        // Ideas Management
        'Title' => 'العنوان',
        'Author' => 'الكاتب',
        'Category' => 'الفئة',
        'Status' => 'الحالة',
        'Votes' => 'الأصوات',
        'Created' => 'تاريخ الإنشاء',
        'Approved' => 'موافق عليه',
        'Pending' => 'في الانتظار',
        'Featured' => 'مميز',
        'Not Featured' => 'غير مميز',
        'Uncategorized' => 'غير مصنف',
        'Are you sure you want to delete this idea?' => 'هل أنت متأكد من حذف هذه الفكرة؟',
        'Idea deleted successfully.' => 'تم حذف الفكرة بنجاح.',
        'Idea status updated successfully.' => 'تم تحديث حالة الفكرة بنجاح.',
        'Are you sure you want to change this status?' => 'هل أنت متأكد من تغيير هذه الحالة؟',
        
        // Categories Management
        'Add New Category' => 'إضافة فئة جديدة',
        'English Name' => 'الاسم بالإنجليزية',
        'Arabic Name' => 'الاسم بالعربية',
        'Description' => 'الوصف',
        'Add Category' => 'إضافة فئة',
        'Category added successfully.' => 'تم إضافة الفئة بنجاح.',
        'Category name is required.' => 'اسم الفئة مطلوب.',
        'Category with this name already exists.' => 'الفئة بهذا الاسم موجودة بالفعل.',
        'Ideas Count' => 'عدد الأفكار',
        'Cannot delete (has ideas)' => 'لا يمكن الحذف (يحتوي على أفكار)',
        'Are you sure you want to delete this category?' => 'هل أنت متأكد من حذف هذه الفئة؟',
        'Cannot delete category that has ideas. Please reassign ideas first.' => 'لا يمكن حذف فئة تحتوي على أفكار. يرجى إعادة تعيين الأفكار أولاً.',
        'Category deleted successfully.' => 'تم حذف الفئة بنجاح.',
        
        // Reported Content
        'Content Type' => 'نوع المحتوى',
        'Reporter' => 'المبلغ',
        'Reason' => 'السبب',
        'Reported' => 'تاريخ البلاغ',
        'Review' => 'مراجعة',
        'Resolve' => 'حل',
        'Dismiss' => 'رفض',
        'No reported content' => 'لا يوجد محتوى مبلغ عنه',
        'All content is clean and follows community guidelines.' => 'جميع المحتوى نظيف ويتبع إرشادات المجتمع.',
        
        // Audit Log
        'Admin' => 'المدير',
        'Action' => 'الإجراء',
        'Table' => 'الجدول',
        'Record ID' => 'رقم السجل',
        'IP Address' => 'عنوان IP',
        'Date' => 'التاريخ',
        
        // Common Actions
        'Edit' => 'تعديل',
        'Copy' => 'نسخ',
        'Save' => 'حفظ',
        'Cancel' => 'إلغاء',
        'Back' => 'رجوع',
        'Next' => 'التالي',
        'Previous' => 'السابق',
        'Search' => 'بحث',
        'Filter' => 'تصفية',
        'Sort' => 'ترتيب',
        'Export' => 'تصدير',
        'Import' => 'استيراد',
        
        // Messages
        'Success' => 'نجح',
        'Error' => 'خطأ',
        'Warning' => 'تحذير',
        'Info' => 'معلومات',
        'Loading...' => 'جاري التحميل...',
        'No data available' => 'لا توجد بيانات متاحة',
        'Records per page' => 'السجلات في الصفحة',
        'Showing' => 'عرض',
        'to' => 'إلى',
        'of' => 'من',
        'entries' => 'إدخالات',
        
        // Status Badges
        'Active' => 'نشط',
        'Inactive' => 'غير نشط',
        'Suspended' => 'معلق',
        'Banned' => 'محظور',
        'Verified' => 'موثق',
        'Unverified' => 'غير موثق',
        
        // Time Formats
        'Just now' => 'الآن',
        'minutes ago' => 'دقائق مضت',
        'hours ago' => 'ساعات مضت',
        'days ago' => 'أيام مضت',
        'weeks ago' => 'أسابيع مضت',
        'months ago' => 'أشهر مضت',
        'years ago' => 'سنوات مضت',
    ]
];

/**
 * Get current language
 * 
 * @return string Language code
 */
function current_language() {
    global $default_language, $available_languages;

    // Start session if not already started
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }

    // 1) Explicit GET takes precedence: persist in session + cookie
    if (isset($_GET['lang']) && in_array($_GET['lang'], $available_languages)) {
        $lang = $_GET['lang'];
        $_SESSION['language'] = $lang;
        if (!headers_sent()) {
            setcookie('language', $lang, time() + 30 * 24 * 60 * 60, '/');
        }
        return $lang;
    }

    // 2) Session preference
    if (isset($_SESSION['language']) && in_array($_SESSION['language'], $available_languages)) {
        return $_SESSION['language'];
    }

    // 3) Cookie fallback
    if (isset($_COOKIE['language']) && in_array($_COOKIE['language'], $available_languages)) {
        return $_COOKIE['language'];
    }

    // 4) Browser language detection
    if (isset($_SERVER['HTTP_ACCEPT_LANGUAGE'])) {
        $browser_langs = explode(',', $_SERVER['HTTP_ACCEPT_LANGUAGE']);
        foreach ($browser_langs as $browser_lang) {
            $lang_code = substr(trim($browser_lang), 0, 2);
            if (in_array($lang_code, $available_languages)) {
                return $lang_code;
            }
        }
    }

    // 5) Default
    return $default_language;
}

/**
 * Get language direction
 * 
 * @param string|null $lang Language code (null for current language)
 * @return string 'rtl' for right-to-left languages, 'ltr' otherwise
 */
function lang_dir($lang = null) {
    if (!$lang) {
        $lang = current_language();
    }
    
    return $lang === 'ar' ? 'rtl' : 'ltr';
}

/**
 * Check if current language is RTL
 * 
 * @return bool True if RTL language
 */
function is_rtl() {
    return current_language() === 'ar';
}

/**
 * Translate a string
 * 
 * @param string $text Text to translate
 * @param array $params Parameters for string interpolation
 * @param string|null $lang Language code (null for current language)
 * @return string Translated text
 */
function __($text, $params = [], $lang = null) {
    global $translations, $default_language;
    
    if (!$lang) {
        $lang = current_language();
    }
    
    // Get translation
    $translated = $translations[$lang][$text] ?? $translations[$default_language][$text] ?? $text;
    
    // Replace parameters
    if (!empty($params)) {
        foreach ($params as $key => $value) {
            $translated = str_replace(':' . $key, $value, $translated);
        }
    }
    
    return $translated;
}

/**
 * Get language switcher HTML
 * 
 * @return string HTML for language switcher
 */
function language_switcher() {
    $current_lang = current_language();
    $current_url = $_SERVER['REQUEST_URI'];
    
    // Remove existing lang parameter
    $current_url = preg_replace('/[?&]lang=[^&]*/', '', $current_url);
    $separator = strpos($current_url, '?') !== false ? '&' : '?';
    
    $html = '<div class="language-switcher">';
    $html .= '<a href="' . $current_url . $separator . 'lang=en" class="lang-link ' . ($current_lang === 'en' ? 'active' : '') . '">English</a>';
    $html .= '<a href="' . $current_url . $separator . 'lang=ar" class="lang-link ' . ($current_lang === 'ar' ? 'active' : '') . '">العربية</a>';
    $html .= '</div>';
    
    return $html;
}