-- Phase 1 Migration: security + fundamentals

-- Users
CREATE TABLE IF NOT EXISTS users (
  id INT AUTO_INCREMENT PRIMARY KEY,
  username VARCHAR(50) NOT NULL UNIQUE,
  email VARCHAR(100) NOT NULL UNIQUE,
  password VARCHAR(255) NOT NULL,
  is_admin TINYINT(1) DEFAULT 0,
  bio VARCHAR(255) DEFAULT '',
  image_url VARCHAR(255) DEFAULT NULL,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Categories
CREATE TABLE IF NOT EXISTS categories (
  id INT AUTO_INCREMENT PRIMARY KEY,
  name_en VARCHAR(100) NOT NULL,
  name_ar VARCHAR(100) NOT NULL
);

-- Ideas
CREATE TABLE IF NOT EXISTS ideas (
  id INT AUTO_INCREMENT PRIMARY KEY,
  user_id INT NOT NULL,
  category_id INT,
  title VARCHAR(255) NOT NULL,
  description TEXT NOT NULL,
  is_public TINYINT(1) DEFAULT 1,
  image_url VARCHAR(255) DEFAULT NULL,
  votes_count INT DEFAULT 0,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (user_id) REFERENCES users(id),
  FOREIGN KEY (category_id) REFERENCES categories(id)
);

-- Votes
CREATE TABLE IF NOT EXISTS votes (
  id INT AUTO_INCREMENT PRIMARY KEY,
  user_id INT NOT NULL,
  idea_id INT NOT NULL,
  vote_type ENUM('like','dislike') NOT NULL DEFAULT 'like',
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (user_id) REFERENCES users(id),
  FOREIGN KEY (idea_id) REFERENCES ideas(id)
);

-- Enforce unique and useful indexes (MySQL-safe idempotent creation), placed after all tables and seeds

-- Comments
CREATE TABLE IF NOT EXISTS comments (
  id INT AUTO_INCREMENT PRIMARY KEY,
  user_id INT NOT NULL,
  idea_id INT NOT NULL,
  comment TEXT NOT NULL,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (user_id) REFERENCES users(id),
  FOREIGN KEY (idea_id) REFERENCES ideas(id)
);

-- Messages (used by profile_others chat)
CREATE TABLE IF NOT EXISTS messages (
  id INT AUTO_INCREMENT PRIMARY KEY,
  sender_id INT,
  receiver_id INT,
  message TEXT,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (sender_id) REFERENCES users(id),
  FOREIGN KEY (receiver_id) REFERENCES users(id),
  INDEX (created_at)
);

-- Login attempts for rate limiting
CREATE TABLE IF NOT EXISTS login_attempts (
  id INT AUTO_INCREMENT PRIMARY KEY,
  ip VARBINARY(16) NOT NULL,
  username VARCHAR(190) NULL,
  occurred_at DATETIME NOT NULL,
  INDEX (occurred_at),
  INDEX (username),
  INDEX (ip)
);

-- Minimal seed data
INSERT INTO users (username, email, password, is_admin)
VALUES
  ('admin', 'admin@example.com', '$2y$10$4txxW1kQHblcS0txj7XteO0t1T5D0u9k4o1d9Q1R66wzQb6Z5m1dK', 1)
ON DUPLICATE KEY UPDATE email=VALUES(email);

INSERT INTO categories (name_en, name_ar) VALUES
  ('Technology', 'تكنولوجيا'),
  ('Education', 'تعليم')
ON DUPLICATE KEY UPDATE name_en=VALUES(name_en);

INSERT INTO ideas (user_id, category_id, title, description, is_public)
SELECT id, 1, 'Online Voting System', 'A platform for online idea voting.', 1
FROM users WHERE email='admin@example.com'
ON DUPLICATE KEY UPDATE title=VALUES(title);

INSERT INTO votes (user_id, idea_id, vote_type)
SELECT u.id, i.id, 'like'
FROM users u, ideas i
WHERE u.email='admin@example.com' AND i.title='Online Voting System'
ON DUPLICATE KEY UPDATE vote_type=VALUES(vote_type);

INSERT INTO comments (user_id, idea_id, comment)
SELECT u.id, i.id, 'Welcome to IdeaVote!'
FROM users u, ideas i
WHERE u.email='admin@example.com' AND i.title='Online Voting System'
ON DUPLICATE KEY UPDATE comment=VALUES(comment);

-- Backfill votes_count for existing rows
UPDATE ideas i
SET votes_count = (
  SELECT COUNT(*) FROM votes v WHERE v.idea_id = i.id AND v.vote_type='like'
);
-- users(email) unique
SET @idx := (
  SELECT COUNT(1) FROM information_schema.statistics
  WHERE table_schema = DATABASE() AND table_name = 'users' AND index_name = 'uq_users_email'
);
SET @sql := IF(@idx = 0,
  'CREATE UNIQUE INDEX uq_users_email ON users (email)',
  'SELECT 1'
);
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;

-- ideas(created_at)
SET @idx := (
  SELECT COUNT(1) FROM information_schema.statistics
  WHERE table_schema = DATABASE() AND table_name = 'ideas' AND index_name = 'ix_ideas_created'
);
SET @sql := IF(@idx = 0,
  'CREATE INDEX ix_ideas_created ON ideas (created_at)',
  'SELECT 1'
);
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;

-- ideas(category_id)
SET @idx := (
  SELECT COUNT(1) FROM information_schema.statistics
  WHERE table_schema = DATABASE() AND table_name = 'ideas' AND index_name = 'ix_ideas_category'
);
SET @sql := IF(@idx = 0,
  'CREATE INDEX ix_ideas_category ON ideas (category_id)',
  'SELECT 1'
);
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;

-- votes(user_id, idea_id) unique
SET @idx := (
  SELECT COUNT(1) FROM information_schema.statistics
  WHERE table_schema = DATABASE() AND table_name = 'votes' AND index_name = 'uq_votes_user_idea'
);
SET @sql := IF(@idx = 0,
  'CREATE UNIQUE INDEX uq_votes_user_idea ON votes (user_id, idea_id)',
  'SELECT 1'
);
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;

-- votes(idea_id)
SET @idx := (
  SELECT COUNT(1) FROM information_schema.statistics
  WHERE table_schema = DATABASE() AND table_name = 'votes' AND index_name = 'ix_votes_idea'
);
SET @sql := IF(@idx = 0,
  'CREATE INDEX ix_votes_idea ON votes (idea_id)',
  'SELECT 1'
);
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;

-- comments(idea_id)
SET @idx := (
  SELECT COUNT(1) FROM information_schema.statistics
  WHERE table_schema = DATABASE() AND table_name = 'comments' AND index_name = 'ix_comments_idea'
);
SET @sql := IF(@idx = 0,
  'CREATE INDEX ix_comments_idea ON comments (idea_id)',
  'SELECT 1'
);
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;