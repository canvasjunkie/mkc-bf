-- MemoryKeep Bot Factory - Database Schema
-- Run this in phpMyAdmin or MySQL CLI

CREATE TABLE IF NOT EXISTS users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    email VARCHAR(255) UNIQUE NOT NULL,
    password_hash VARCHAR(255) NOT NULL,
    auth_token VARCHAR(64) UNIQUE NULL,
    tier ENUM('free', 'starter', 'pro') DEFAULT 'free',
    paypal_subscription_id VARCHAR(100) NULL,
    subscription_status ENUM('active', 'cancelled', 'expired', 'pending') DEFAULT 'active',
    messages_used INT DEFAULT 0,
    messages_reset_date DATE DEFAULT (CURRENT_DATE),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_email (email),
    INDEX idx_auth_token (auth_token),
    INDEX idx_subscription (paypal_subscription_id)
);

-- Optional: Admin table for future use
CREATE TABLE IF NOT EXISTS admin_logs (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT,
    action VARCHAR(100),
    details TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE SET NULL
);
