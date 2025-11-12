CREATE TABLE users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100),
    email VARCHAR(100),
    phone VARCHAR(20)
);

-- Create admin users table
CREATE TABLE IF NOT EXISTS admin_users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) UNIQUE NOT NULL,
    email VARCHAR(255) UNIQUE NOT NULL,
    password VARCHAR(255) NOT NULL,
    role ENUM('administrator', 'manager', 'user') DEFAULT 'user',
    status ENUM('active', 'inactive', 'suspended') DEFAULT 'active',
    last_login DATETIME NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_username (username),
    INDEX idx_email (email),
    INDEX idx_status (status)
);

-- Create remember tokens table for "Remember Me" functionality
CREATE TABLE IF NOT EXISTS remember_tokens (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    token VARCHAR(64) UNIQUE NOT NULL,
    expires_at DATETIME NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES admin_users(id) ON DELETE CASCADE,
    INDEX idx_token (token),
    INDEX idx_expires (expires_at)
);

-- Create login attempts table for security
CREATE TABLE IF NOT EXISTS login_attempts (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username_or_email VARCHAR(255) NOT NULL,
    ip_address VARCHAR(45) NOT NULL,
    user_agent TEXT,
    success BOOLEAN DEFAULT FALSE,
    attempted_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_ip_time (ip_address, attempted_at),
    INDEX idx_username_time (username_or_email, attempted_at)
);

-- Create sessions table (optional, for database-based session storage)
CREATE TABLE IF NOT EXISTS user_sessions (
    id VARCHAR(128) PRIMARY KEY,
    user_id INT,
    ip_address VARCHAR(45),
    user_agent TEXT,
    last_activity TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    data TEXT,
    FOREIGN KEY (user_id) REFERENCES admin_users(id) ON DELETE CASCADE,
    INDEX idx_last_activity (last_activity)
);

-- Insert default admin user (password: admin123)
INSERT IGNORE INTO admin_users (username, email, password, role, status) 
VALUES ('admin', 'admin@bumarpharmacy.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'administrator', 'active');


CREATE TABLE IF NOT EXISTS drugs (
    id INT AUTO_INCREMENT PRIMARY KEY,
    drug_name VARCHAR(255) NOT NULL UNIQUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    added_by INT,
    INDEX idx_drug_name (drug_name),
    INDEX idx_created_at (created_at),
    FOREIGN KEY (added_by) REFERENCES admin_users(id) ON DELETE SET NULL
);

-- Create drugs_audit table for tracking changes
CREATE TABLE IF NOT EXISTS drugs_audit (
    id INT AUTO_INCREMENT PRIMARY KEY,
    drug_id INT,
    action ENUM('INSERT', 'UPDATE', 'DELETE'),
    old_value VARCHAR(255),
    new_value VARCHAR(255),
    changed_by INT,
    changed_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (changed_by) REFERENCES admin_users(id) ON DELETE SET NULL
);

*/

// ========== 1. DATABASE SCHEMA UPDATE ==========
-- Add SEO fields to drugs table

-- ALTER TABLE drugs ADD COLUMN slug VARCHAR(255) UNIQUE AFTER drug_name;
-- ALTER TABLE drugs ADD COLUMN meta_description TEXT AFTER slug;
-- ALTER TABLE drugs ADD COLUMN meta_keywords TEXT AFTER meta_description;
-- ALTER TABLE drugs ADD COLUMN page_views INT DEFAULT 0 AFTER meta_keywords;
-- ALTER TABLE drugs ADD COLUMN last_viewed TIMESTAMP NULL AFTER page_views;
-- ALTER TABLE drugs ADD COLUMN drug_description TEXT AFTER last_viewed;
-- ALTER TABLE drugs ADD COLUMN usage_information TEXT AFTER drug_description;
-- ALTER TABLE drugs ADD COLUMN side_effects TEXT AFTER usage_information;
-- ALTER TABLE drugs ADD COLUMN price DECIMAL(10,2) AFTER side_effects;
-- ALTER TABLE drugs ADD COLUMN in_stock BOOLEAN DEFAULT TRUE AFTER price;
-- ALTER TABLE drugs ADD COLUMN manufacturer VARCHAR(255) AFTER in_stock;

ALTER TABLE drugs ADD COLUMN slug VARCHAR(191) UNIQUE AFTER drug_name;
ALTER TABLE drugs ADD COLUMN meta_description TEXT AFTER slug;
ALTER TABLE drugs ADD COLUMN meta_keywords TEXT AFTER meta_description;
ALTER TABLE drugs ADD COLUMN page_views INT DEFAULT 0 AFTER meta_keywords;
ALTER TABLE drugs ADD COLUMN last_viewed TIMESTAMP NULL AFTER page_views;
ALTER TABLE drugs ADD COLUMN drug_description TEXT AFTER last_viewed;
ALTER TABLE drugs ADD COLUMN usage_information TEXT AFTER drug_description;
ALTER TABLE drugs ADD COLUMN side_effects TEXT AFTER usage_information;
ALTER TABLE drugs ADD COLUMN price DECIMAL(10,2) AFTER side_effects;
ALTER TABLE drugs ADD COLUMN in_stock BOOLEAN DEFAULT TRUE AFTER price;
ALTER TABLE drugs ADD COLUMN manufacturer VARCHAR(255) AFTER in_stock;

-- Create search log table for analytics
-- CREATE TABLE IF NOT EXISTS search_logs (
--     id INT AUTO_INCREMENT PRIMARY KEY,
--     search_term VARCHAR(255) NOT NULL,
--     ip_address VARCHAR(45),
--     user_agent TEXT,
--     results_count INT,
--     searched_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
--     INDEX idx_search_term (search_term),
--     INDEX idx_searched_at (searched_at)
-- );

CREATE TABLE IF NOT EXISTS search_logs (
    id INT AUTO_INCREMENT PRIMARY KEY,
    search_term VARCHAR(191) NOT NULL,  -- Also reduced here
    ip_address VARCHAR(45),
    user_agent TEXT,
    results_count INT,
    searched_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_search_term (search_term),
    INDEX idx_searched_at (searched_at)
);

-- Create popular searches view
CREATE OR REPLACE VIEW popular_searches AS
SELECT search_term, COUNT(*) as search_count
FROM search_logs
WHERE searched_at >= DATE_SUB(NOW(), INTERVAL 30 DAYS)
GROUP BY search_term
ORDER BY search_count DESC
LIMIT 50;

-- // ========== DATABASE SETUP (blog_tables.sql) ==========
CREATE TABLE IF NOT EXISTS blogs (
    id INT AUTO_INCREMENT PRIMARY KEY,
    title VARCHAR(255) NOT NULL,
    slug VARCHAR(191) UNIQUE,
    excerpt TEXT,
    content TEXT NOT NULL,
    image_url VARCHAR(500),
    category VARCHAR(100),
    author VARCHAR(100),
    status ENUM('draft', 'published') DEFAULT 'draft',
    views INT DEFAULT 0,
    created_by INT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_slug (slug),
    INDEX idx_status (status),
    INDEX idx_created_at (created_at),
    FOREIGN KEY (created_by) REFERENCES admin_users(id) ON DELETE SET NULL
);



