-- Portfolio Database Schema (PHP Version)
-- Run this file to set up the database:
--   mysql -u root -p < schema.sql

CREATE DATABASE IF NOT EXISTS portfolio_db;
USE portfolio_db;

-- Admin Users
CREATE TABLE IF NOT EXISTS admin_users (
  id INT AUTO_INCREMENT PRIMARY KEY,
  username VARCHAR(50) NOT NULL UNIQUE,
  password VARCHAR(255) NOT NULL,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Projects
CREATE TABLE IF NOT EXISTS projects (
  id INT AUTO_INCREMENT PRIMARY KEY,
  title VARCHAR(200) NOT NULL,
  description TEXT NOT NULL,
  image_url VARCHAR(500) DEFAULT NULL,
  tech_stack VARCHAR(500) DEFAULT NULL,
  live_url VARCHAR(500) DEFAULT NULL,
  github_url VARCHAR(500) DEFAULT NULL,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Memories (Gallery)
CREATE TABLE IF NOT EXISTS memories (
  id INT AUTO_INCREMENT PRIMARY KEY,
  title VARCHAR(200) NOT NULL,
  description TEXT DEFAULT NULL,
  image_url VARCHAR(500) NOT NULL,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Blogs
CREATE TABLE IF NOT EXISTS blogs (
  id INT AUTO_INCREMENT PRIMARY KEY,
  title VARCHAR(300) NOT NULL,
  content TEXT NOT NULL,
  cover_image VARCHAR(500) DEFAULT NULL,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Seed default admin (password: admin123 - CHANGE IN PRODUCTION)
-- Uses PHP password_hash() compatible bcrypt hash
INSERT INTO admin_users (username, password) VALUES
('admin', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi')
ON DUPLICATE KEY UPDATE username = username;

-- Sample projects
INSERT INTO projects (title, description, image_url, tech_stack) VALUES
('Stock Dashboard', 'A complete IPO tracking and profit management system built with real-time calculation and analytics dashboard.', NULL, 'HTML,CSS,JavaScript,Chart.js'),
('MeroSutra', 'A smart municipal and property management system designed for local administrative operations.', NULL, 'PHP,MySQL,Bootstrap'),
('BCA Notes Hub', 'A student learning platform that organizes BCA notes, resources, and academic materials.', NULL, 'HTML,CSS,JavaScript'),
('ERP System', 'Full school ERP system with student management, marks, attendance, and reporting modules.', NULL, 'PHP,MySQL,JavaScript'),
('School Management System', 'A full-stack school management system connecting administration, teachers, and students with role-based dashboards.', NULL, 'Node.js,Express,MySQL,React')
ON DUPLICATE KEY UPDATE title = title;
