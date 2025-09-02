-- BudgetFlix database schema (MariaDB/MySQL)
-- Compatible with InfinityFree (phpMyAdmin import)

CREATE TABLE IF NOT EXISTS users (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  name VARCHAR(120) NOT NULL,
  email VARCHAR(190) NOT NULL UNIQUE,
  password_hash VARCHAR(255) NOT NULL,
  created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS transactions (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  user_id INT UNSIGNED NOT NULL,
  occurred_on DATE NOT NULL,
  type ENUM('income','expense') NOT NULL,
  category VARCHAR(120) NOT NULL,
  amount DECIMAL(12,2) NOT NULL,
  note VARCHAR(255) NULL,
  assigned_to VARCHAR(120) NULL, -- for family member or self
  created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  INDEX idx_user_date (user_id, occurred_on),
  CONSTRAINT fk_tx_user FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS goals (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  user_id INT UNSIGNED NOT NULL,
  name VARCHAR(160) NOT NULL,
  target_amount DECIMAL(12,2) NOT NULL,
  current_amount DECIMAL(12,2) NOT NULL DEFAULT 0,
  due_on DATE NULL,
  created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  INDEX idx_goals_user (user_id),
  CONSTRAINT fk_goals_user FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS recurring (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  user_id INT UNSIGNED NOT NULL,
  type ENUM('income','expense') NOT NULL,
  category VARCHAR(120) NOT NULL,
  amount DECIMAL(12,2) NOT NULL,
  frequency ENUM('weekly','biweekly','monthly','quarterly','yearly') NOT NULL,
  next_due DATE NOT NULL,
  note VARCHAR(255) NULL,
  created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  INDEX idx_rec_user (user_id),
  CONSTRAINT fk_rec_user FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Optional demo seed (remove in production)
INSERT INTO users (name, email, password_hash) VALUES
('Demo User', 'demo@example.com', '$2y$10$abcdefghijklmnopqrstuvS9n1ZzvZlCqf7J9z0V7i6X5k1f2y3z4a5');


