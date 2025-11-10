-- SQL schema for Money Manager

-- SQL schema for Money Manager (MySQL)

-- Create the database and tables. Import this file into MySQL (phpMyAdmin or mysql client).
-- NOTE: If your environment doesn't permit CREATE DATABASE in the import, create the database manually and run the CREATE TABLE statements.

CREATE DATABASE IF NOT EXISTS money_manager CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci;
USE money_manager;

-- users
CREATE TABLE IF NOT EXISTS users (
  id INT AUTO_INCREMENT PRIMARY KEY,
  name VARCHAR(100) NOT NULL,
  email VARCHAR(150) NOT NULL UNIQUE,
  password VARCHAR(255) NOT NULL,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- categories
CREATE TABLE IF NOT EXISTS categories (
  id INT AUTO_INCREMENT PRIMARY KEY,
  user_id INT NOT NULL,
  name VARCHAR(100) NOT NULL,
  color VARCHAR(7) DEFAULT '#4e73df',
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- transactions
CREATE TABLE IF NOT EXISTS transactions (
  id INT AUTO_INCREMENT PRIMARY KEY,
  user_id INT NOT NULL,
  category_id INT NULL,
  type ENUM('income','expense') NOT NULL,
  amount DECIMAL(12,2) NOT NULL,
  date DATE NOT NULL,
  notes TEXT,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
  FOREIGN KEY (category_id) REFERENCES categories(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- budgets
CREATE TABLE IF NOT EXISTS budgets (
  id INT AUTO_INCREMENT PRIMARY KEY,
  user_id INT NOT NULL,
  category_id INT NOT NULL,
  month YEAR NOT NULL,
  amount DECIMAL(12,2) NOT NULL,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
  FOREIGN KEY (category_id) REFERENCES categories(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- savings goals
CREATE TABLE IF NOT EXISTS goals (
  id INT AUTO_INCREMENT PRIMARY KEY,
  user_id INT NOT NULL,
  name VARCHAR(150) NOT NULL,
  target DECIMAL(12,2) NOT NULL,
  saved DECIMAL(12,2) DEFAULT 0,
  deadline DATE,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Sample data guidance:
-- To create a sample user with email demo@example.com and password 'demo123', run the following PHP snippet
-- and paste the resulting hashed password into an INSERT statement, or register using the web UI:
-- <?php echo password_hash('demo123', PASSWORD_DEFAULT); ?>

-- Example insert after generating a hash (replace <HASH> with generated hash):
-- INSERT INTO users (name,email,password) VALUES ('Demo User','demo@example.com','<HASH>');

-- Optionally add categories and transactions after creating a user (replace <USER_ID>):
-- INSERT INTO categories (user_id, name, color) VALUES (<USER_ID>, 'Groceries', '#f6c23e'), (<USER_ID>, 'Salary', '#1cc88a');
-- INSERT INTO transactions (user_id, category_id, type, amount, date, notes) VALUES
-- (<USER_ID>, 1, 'expense', 45.50, '2025-10-01', 'Grocery run'),
-- (<USER_ID>, 2, 'income', 2500.00, '2025-10-01', 'Monthly salary');