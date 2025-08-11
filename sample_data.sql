-- Clean minimal seed (schema should be created via db/migrations/001_phase1.sql)

INSERT INTO users (username, email, password, is_admin) VALUES
('admin', 'admin@example.com', '$2y$10$4txxW1kQHblcS0txj7XteO0t1T5D0u9k4o1d9Q1R66wzQb6Z5m1dK', 1)
ON DUPLICATE KEY UPDATE email=VALUES(email);

INSERT INTO categories (name_en, name_ar) VALUES
('Technology', 'تكنولوجيا'),
('Education', 'تعليم')
ON DUPLICATE KEY UPDATE name_en=VALUES(name_en);

INSERT INTO ideas (user_id, category_id, title, description, is_public)
VALUES (1, 1, 'Online Voting System', 'A platform for online idea voting.', 1)
ON DUPLICATE KEY UPDATE title=VALUES(title);

INSERT INTO votes (user_id, idea_id, vote_type) VALUES (1, 1, 'like')
ON DUPLICATE KEY UPDATE vote_type=VALUES(vote_type);

INSERT INTO comments (user_id, idea_id, comment) VALUES (1, 1, 'Welcome to IdeaVote!')
ON DUPLICATE KEY UPDATE comment=VALUES(comment);