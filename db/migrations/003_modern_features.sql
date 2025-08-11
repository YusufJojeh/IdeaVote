-- 003_modern_features.sql
-- Modern features migration for IdeaVote

-- Add slug to ideas table for SEO-friendly URLs
ALTER TABLE ideas ADD COLUMN slug VARCHAR(255) UNIQUE AFTER title;
UPDATE ideas SET slug = CONCAT(LOWER(REPLACE(REPLACE(REPLACE(title, ' ', '-'), '.', ''), ',', '')), '-', id) WHERE slug IS NULL;

-- Add view count to ideas
ALTER TABLE ideas ADD COLUMN views_count INT DEFAULT 0 AFTER votes_count;

-- Create notifications table
CREATE TABLE notifications (
    id INT PRIMARY KEY AUTO_INCREMENT,
    user_id INT NOT NULL,
    type ENUM('vote', 'comment', 'follow', 'mention', 'system') NOT NULL,
    title VARCHAR(255) NOT NULL,
    message TEXT NOT NULL,
    data JSON,
    is_read BOOLEAN DEFAULT FALSE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    INDEX idx_user_read (user_id, is_read),
    INDEX idx_created_at (created_at)
);

-- Create follows table (users following users)
CREATE TABLE follows (
    id INT PRIMARY KEY AUTO_INCREMENT,
    follower_id INT NOT NULL,
    following_id INT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (follower_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (following_id) REFERENCES users(id) ON DELETE CASCADE,
    UNIQUE KEY unique_follow (follower_id, following_id),
    INDEX idx_follower (follower_id),
    INDEX idx_following (following_id)
);

-- Create category_follows table (users following categories)
CREATE TABLE category_follows (
    id INT PRIMARY KEY AUTO_INCREMENT,
    user_id INT NOT NULL,
    category_id INT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (category_id) REFERENCES categories(id) ON DELETE CASCADE,
    UNIQUE KEY unique_category_follow (user_id, category_id),
    INDEX idx_user (user_id),
    INDEX idx_category (category_id)
);

-- Create reactions table (beyond simple votes)
CREATE TABLE reactions (
    id INT PRIMARY KEY AUTO_INCREMENT,
    user_id INT NOT NULL,
    idea_id INT NOT NULL,
    reaction_type ENUM('like', 'love', 'fire', 'laugh', 'wow', 'sad', 'angry') NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (idea_id) REFERENCES ideas(id) ON DELETE CASCADE,
    UNIQUE KEY unique_reaction (user_id, idea_id, reaction_type),
    INDEX idx_idea_type (idea_id, reaction_type),
    INDEX idx_user (user_id)
);

-- Create bookmarks table
CREATE TABLE bookmarks (
    id INT PRIMARY KEY AUTO_INCREMENT,
    user_id INT NOT NULL,
    idea_id INT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (idea_id) REFERENCES ideas(id) ON DELETE CASCADE,
    UNIQUE KEY unique_bookmark (user_id, idea_id),
    INDEX idx_user (user_id),
    INDEX idx_idea (idea_id)
);

-- Create saved_filters table
CREATE TABLE saved_filters (
    id INT PRIMARY KEY AUTO_INCREMENT,
    user_id INT NOT NULL,
    name VARCHAR(255) NOT NULL,
    filter_data JSON NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    INDEX idx_user (user_id)
);

-- Create audit_logs table for admin actions
CREATE TABLE audit_logs (
    id INT PRIMARY KEY AUTO_INCREMENT,
    admin_id INT NOT NULL,
    action VARCHAR(100) NOT NULL,
    table_name VARCHAR(50) NOT NULL,
    record_id INT,
    old_data JSON,
    new_data JSON,
    ip_address VARCHAR(45),
    user_agent TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (admin_id) REFERENCES users(id) ON DELETE CASCADE,
    INDEX idx_admin_action (admin_id, action),
    INDEX idx_table_record (table_name, record_id),
    INDEX idx_created_at (created_at)
);

-- Create reported_content table for moderation
CREATE TABLE reported_content (
    id INT PRIMARY KEY AUTO_INCREMENT,
    reporter_id INT NOT NULL,
    content_type ENUM('idea', 'comment', 'user') NOT NULL,
    content_id INT NOT NULL,
    reason ENUM('spam', 'inappropriate', 'harassment', 'copyright', 'other') NOT NULL,
    description TEXT,
    status ENUM('pending', 'reviewed', 'resolved', 'dismissed') DEFAULT 'pending',
    admin_notes TEXT,
    reviewed_by INT,
    reviewed_at TIMESTAMP NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (reporter_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (reviewed_by) REFERENCES users(id) ON DELETE SET NULL,
    INDEX idx_content (content_type, content_id),
    INDEX idx_status (status),
    INDEX idx_reporter (reporter_id)
);

-- Create user_sessions table for device management
CREATE TABLE user_sessions (
    id INT PRIMARY KEY AUTO_INCREMENT,
    user_id INT NOT NULL,
    session_id VARCHAR(255) NOT NULL,
    device_info JSON,
    ip_address VARCHAR(45),
    user_agent TEXT,
    is_active BOOLEAN DEFAULT TRUE,
    last_activity TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    UNIQUE KEY unique_session (session_id),
    INDEX idx_user_active (user_id, is_active),
    INDEX idx_last_activity (last_activity)
);

-- Add language preference to users
ALTER TABLE users ADD COLUMN language ENUM('en', 'ar') DEFAULT 'en' AFTER email;

-- Add theme preference to users
ALTER TABLE users ADD COLUMN theme ENUM('light', 'dark', 'auto') DEFAULT 'auto' AFTER language;

-- Add email preferences to users
ALTER TABLE users ADD COLUMN email_notifications BOOLEAN DEFAULT TRUE AFTER theme;

-- Add bio to users if not exists
SET @col := (
    SELECT COUNT(*)
    FROM INFORMATION_SCHEMA.COLUMNS
    WHERE TABLE_SCHEMA = DATABASE()
    AND TABLE_NAME = 'users'
    AND COLUMN_NAME = 'bio'
);
SET @sql := IF(@col = 0, 'ALTER TABLE users ADD COLUMN bio TEXT AFTER email', 'SELECT "bio column already exists"');
PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

-- Add avatar to users if not exists
SET @col := (
    SELECT COUNT(*)
    FROM INFORMATION_SCHEMA.COLUMNS
    WHERE TABLE_SCHEMA = DATABASE()
    AND TABLE_NAME = 'users'
    AND COLUMN_NAME = 'avatar'
);
SET @sql := IF(@col = 0, 'ALTER TABLE users ADD COLUMN avatar VARCHAR(255) AFTER bio', 'SELECT "avatar column already exists"');
PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

-- Add tags to ideas
ALTER TABLE ideas ADD COLUMN tags JSON AFTER description;

-- Add featured flag to ideas
ALTER TABLE ideas ADD COLUMN is_featured BOOLEAN DEFAULT FALSE AFTER is_approved;

-- Add trending score to ideas (calculated field)
ALTER TABLE ideas ADD COLUMN trending_score DECIMAL(10,4) DEFAULT 0.0000 AFTER views_count;

-- Create indexes for performance
CREATE INDEX idx_ideas_featured ON ideas(is_featured);
CREATE INDEX idx_ideas_trending ON ideas(trending_score DESC);
CREATE INDEX idx_ideas_created ON ideas(created_at DESC);
CREATE INDEX idx_votes_created ON votes(created_at DESC);
CREATE INDEX idx_comments_created ON comments(created_at DESC);

-- Add fulltext search to ideas
ALTER TABLE ideas ADD FULLTEXT(title, description);

-- Create view for trending ideas calculation
CREATE VIEW trending_ideas AS
SELECT 
    i.*,
    (
        (i.votes_count * 10) + 
        (i.views_count * 0.1) + 
        (COUNT(c.id) * 5) +
        (TIMESTAMPDIFF(HOUR, i.created_at, NOW()) * -0.1)
    ) as calculated_score
FROM ideas i
LEFT JOIN comments c ON i.id = c.idea_id
WHERE i.is_approved = 1
GROUP BY i.id
ORDER BY calculated_score DESC;

-- Insert sample data for testing
INSERT INTO categories (name, description) VALUES 
('Technology', 'Tech innovations and ideas'),
('Environment', 'Environmental solutions'),
('Education', 'Educational improvements'),
('Health', 'Healthcare innovations'),
('Transportation', 'Transport and mobility solutions')
ON DUPLICATE KEY UPDATE name = VALUES(name);

-- Update existing ideas with sample tags
UPDATE ideas SET tags = JSON_ARRAY('innovation', 'community') WHERE tags IS NULL LIMIT 10;

-- Create webhook events table
CREATE TABLE webhook_events (
    id INT AUTO_INCREMENT PRIMARY KEY,
    event_type VARCHAR(100) NOT NULL,
    payload JSON NOT NULL,
    processed TINYINT(1) DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    processed_at TIMESTAMP NULL,
    INDEX idx_event_type (event_type),
    INDEX idx_processed (processed),
    INDEX idx_created_at (created_at)
);
