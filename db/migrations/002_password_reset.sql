-- Password reset support

CREATE TABLE IF NOT EXISTS password_resets (
  id INT AUTO_INCREMENT PRIMARY KEY,
  user_id INT NOT NULL,
  token_hash VARBINARY(32) NOT NULL,
  expires_at DATETIME NOT NULL,
  created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  INDEX (user_id),
  INDEX (expires_at),
  INDEX (token_hash),
  FOREIGN KEY (user_id) REFERENCES users(id)
);

-- Optional maintenance: remove expired tokens
DELETE FROM password_resets WHERE expires_at <= NOW();


