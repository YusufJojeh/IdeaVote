-- Database: idea_vote_platform

-- Users Table
CREATE TABLE IF NOT EXISTS users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) NOT NULL UNIQUE,
    email VARCHAR(100) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    is_admin TINYINT(1) DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

INSERT INTO users (username, email, password, is_admin) VALUES
('admin', 'admin@example.com', '$2y$10$demoAdminHash', 1),
('student1', 'student1@example.com', '$2y$10$demoUserHash', 0);

-- Categories Table
CREATE TABLE IF NOT EXISTS categories (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name_en VARCHAR(100) NOT NULL,
    name_ar VARCHAR(100) NOT NULL
);

INSERT INTO categories (name_en, name_ar) VALUES
('Technology', 'تكنولوجيا'),
('Education', 'تعليم'),
('Creativity', 'إبداع');

-- Ideas Table
CREATE TABLE IF NOT EXISTS ideas (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    category_id INT,
    title VARCHAR(255) NOT NULL,
    description TEXT NOT NULL,
    is_public TINYINT(1) DEFAULT 1,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id),
    FOREIGN KEY (category_id) REFERENCES categories(id)
);

INSERT INTO ideas (user_id, category_id, title, description, is_public) VALUES
(2, 1, 'Online Voting System', 'A platform for online idea voting.', 1),
(2, 2, 'Smart Classroom', 'Using technology to enhance education.', 1);

-- Votes Table
CREATE TABLE IF NOT EXISTS votes (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    idea_id INT NOT NULL,
    vote_type ENUM('like','dislike') NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    UNIQUE KEY unique_vote (user_id, idea_id),
    FOREIGN KEY (user_id) REFERENCES users(id),
    FOREIGN KEY (idea_id) REFERENCES ideas(id)
);

INSERT INTO votes (user_id, idea_id, vote_type) VALUES
(2, 1, 'like'),
(2, 2, 'like');

-- Comments Table
CREATE TABLE IF NOT EXISTS comments (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    idea_id INT NOT NULL,
    comment TEXT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id),
    FOREIGN KEY (idea_id) REFERENCES ideas(id)
);

INSERT INTO comments (user_id, idea_id, comment) VALUES
(2, 1, 'Great idea!'),
(2, 2, 'Very innovative.'); 