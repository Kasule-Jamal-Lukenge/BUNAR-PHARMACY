<?php
    // ========== authenticate.php ==========
    session_start();

    // Database connection
    require_once('conn.php');

    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $username = trim($_POST['username']);
        $password = $_POST['password'];
        $remember = isset($_POST['remember']);
        
        // Validate input
        if (empty($username) || empty($password)) {
            header("Location: login.php?error=" . urlencode("Please fill in all fields"));
            exit;
        }
        
        // Check user credentials
        $stmt = $conn->prepare("SELECT id, username, email, password, role, status, last_login FROM admin_users WHERE (username = ? OR email = ?) AND status = 'active'");
        $stmt->bind_param("ss", $username, $username);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows === 1) {
            $user = $result->fetch_assoc();
            
            // Verify password
            if (password_verify($password, $user['password'])) {
                // Login successful
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['username'] = $user['username'];
                $_SESSION['email'] = $user['email'];
                $_SESSION['role'] = $user['role'];
                $_SESSION['login_time'] = time();
                
                // Update last login
                $update_stmt = $conn->prepare("UPDATE admin_users SET last_login = NOW() WHERE id = ?");
                $update_stmt->bind_param("i", $user['id']);
                $update_stmt->execute();
                $update_stmt->close();
                
                // Set remember me cookie if checked
                if ($remember) {
                    $token = bin2hex(random_bytes(32));
                    $expires = time() + (30 * 24 * 60 * 60); // 30 days
                    
                    // Store token in database
                    $token_stmt = $conn->prepare("INSERT INTO remember_tokens (user_id, token, expires_at) VALUES (?, ?, FROM_UNIXTIME(?)) ON DUPLICATE KEY UPDATE token = ?, expires_at = FROM_UNIXTIME(?)");
                    $token_stmt->bind_param("issis", $user['id'], $token, $expires, $token, $expires);
                    $token_stmt->execute();
                    $token_stmt->close();
                    
                    // Set cookie
                    setcookie('remember_token', $token, $expires, '/', '', true, true);
                }
                
                // Redirect to dashboard
                header("Location: data-management.php?success=" . urlencode("Login successful! Welcome back, " . $user['username']));
                exit;
                
            } else {
                // Invalid password
                header("Location: login.php?error=" . urlencode("Invalid username or password"));
                exit;
            }
        } else {
            // User not found
            header("Location: login.php?error=" . urlencode("Invalid username or password"));
            exit;
        }
        
        $stmt->close();
    } else {
        header("Location: login.php");
        exit;
    }

    $conn->close();
?>

// ========== logout.php ==========
<?php
    session_start();

    // Database connection for token cleanup
    require_once('conn.php');

    // Clear remember me token if exists
    if (isset($_COOKIE['remember_token'])) {
        $token = $_COOKIE['remember_token'];
        
        if ($conn && !$conn->connect_error) {
            $stmt = $conn->prepare("DELETE FROM remember_tokens WHERE token = ?");
            $stmt->bind_param("s", $token);
            $stmt->execute();
            $stmt->close();
        }
        
        // Clear cookie
        setcookie('remember_token', '', time() - 3600, '/', '', true, true);
    }

    // Clear session
    session_unset();
    session_destroy();

    if ($conn) {
        $conn->close();
    }

    // Redirect to login
    header("Location: login.php?success=" . urlencode("You have been logged out successfully"));
    exit;
?>

// ========== session_check.php (Include this in protected pages) ==========
<?php
    if (session_status() == PHP_SESSION_NONE) {
        session_start();
    }

    function checkAuthentication() {
        // Check if user is logged in via session
        if (isset($_SESSION['user_id']) && !empty($_SESSION['user_id'])) {
            return true;
        }
        
        // Check remember me token
        if (isset($_COOKIE['remember_token'])) {
            require_once('conn.php');
            
            if (!$conn->connect_error) {
                $token = $_COOKIE['remember_token'];
                $stmt = $conn->prepare("
                    SELECT u.id, u.username, u.email, u.role 
                    FROM admin_users u 
                    JOIN remember_tokens rt ON u.id = rt.user_id 
                    WHERE rt.token = ? AND rt.expires_at > NOW() AND u.status = 'active'
                ");
                $stmt->bind_param("s", $token);
                $stmt->execute();
                $result = $stmt->get_result();
                
                if ($result->num_rows === 1) {
                    $user = $result->fetch_assoc();
                    
                    // Restore session
                    $_SESSION['user_id'] = $user['id'];
                    $_SESSION['username'] = $user['username'];
                    $_SESSION['email'] = $user['email'];
                    $_SESSION['role'] = $user['role'];
                    $_SESSION['login_time'] = time();
                    
                    $stmt->close();
                    $conn->close();
                    return true;
                }
                
                $stmt->close();
            }
            
            if ($conn) {
                $conn->close();
            }
        }
        
        return false;
    }

    // Check authentication
    if (!checkAuthentication()) {
        header("Location: login.php?error=" . urlencode("Please log in to access this page"));
        exit;
    }

    // Optional: Check session timeout (30 minutes)
    $timeout_duration = 1800; // 30 minutes
    if (isset($_SESSION['login_time']) && (time() - $_SESSION['login_time']) > $timeout_duration) {
        session_unset();
        session_destroy();
        header("Location: login.php?error=" . urlencode("Session expired. Please log in again."));
        exit;
    }

    // Update last activity
    $_SESSION['login_time'] = time();
?>

// ========== create_admin_user.php (Run this once to create an admin user) ==========
<?php
    // Database connection
    $host = "localhost";
    $user = "root";
    $pass = "";
    $db = "bunar_pharmacy";
    $conn = new mysqli($host, $user, $pass, $db);

    if ($conn->connect_error) {
        die("Connection failed: " . $conn->connect_error);
    }

    // Admin user details
    $admin_username = "admin";
    $admin_email = "admin@bumarpharmacy.com";
    $admin_password = "admin123"; // Change this to a secure password
    $admin_role = "administrator";

    // Hash password
    $hashed_password = password_hash($admin_password, PASSWORD_DEFAULT);

    try {
        // Insert admin user
        $stmt = $conn->prepare("INSERT INTO admin_users (username, email, password, role, status, created_at) VALUES (?, ?, ?, ?, 'active', NOW())");
        $stmt->bind_param("ssss", $admin_username, $admin_email, $hashed_password, $admin_role);
        
        if ($stmt->execute()) {
            echo "Admin user created successfully!<br>";
            echo "Username: $admin_username<br>";
            echo "Email: $admin_email<br>";
            echo "Password: $admin_password<br>";
            echo "<strong>Please change the password after first login!</strong>";
        } else {
            echo "Error creating admin user: " . $stmt->error;
        }
        
        $stmt->close();
    } catch (Exception $e) {
        echo "Error: " . $e->getMessage();
    }

    $conn->close();
?>

// ========== database_setup_auth.sql ==========
/*
-- Run this SQL to set up the authentication tables

USE bunar_pharmacy;

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
*/

// ========== forgot_password.php ==========
<?php
    session_start();

    // Database connection
    $host = "localhost";
    $user = "root";
    $pass = "";
    $db = "bunar_pharmacy";
    $conn = new mysqli($host, $user, $pass, $db);

    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $email = trim($_POST['email']);
        
        if (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
            header("Location: forgot-password-form.php?error=" . urlencode("Please enter a valid email address"));
            exit;
        }
        
        // Check if email exists
        $stmt = $conn->prepare("SELECT id, username, email FROM admin_users WHERE email = ? AND status = 'active'");
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows === 1) {
            $user = $result->fetch_assoc();
            
            // Generate reset token
            $token = bin2hex(random_bytes(32));
            $expires = date('Y-m-d H:i:s', time() + 3600); // 1 hour from now
            
            // Store reset token
            $reset_stmt = $conn->prepare("INSERT INTO password_resets (user_id, token, expires_at) VALUES (?, ?, ?) ON DUPLICATE KEY UPDATE token = ?, expires_at = ?");
            $reset_stmt->bind_param("issss", $user['id'], $token, $expires, $token, $expires);
            $reset_stmt->execute();
            $reset_stmt->close();
            
            // In a real application, send email here
            // For now, we'll just show the reset link
            $reset_link = "http://" . $_SERVER['HTTP_HOST'] . "/reset-password.php?token=" . $token;
            
            header("Location: forgot-password-form.php?success=" . urlencode("Password reset link has been sent to your email. Link: " . $reset_link));
        } else {
            // Don't reveal if email exists or not for security
            header("Location: forgot-password-form.php?success=" . urlencode("If the email exists, a password reset link has been sent"));
        }
        
        $stmt->close();
    } else {
        header("Location: forgot-password-form.php");
    }

    $conn->close();
?>

// ========== enhanced_authenticate.php (with rate limiting) ==========
<?php
    session_start();

    // Database connection
    $host = "localhost";
    $user = "root";
    $pass = "";
    $db = "bunar_pharmacy";
    $conn = new mysqli($host, $user, $pass, $db);

    if ($conn->connect_error) {
        die("Connection failed: " . $conn->connect_error);
    }

    // Rate limiting function
    function checkRateLimit($conn, $identifier, $ip) {
        $max_attempts = 5;
        $lockout_time = 900; // 15 minutes
        
        // Clean old attempts
        $cleanup_stmt = $conn->prepare("DELETE FROM login_attempts WHERE attempted_at < DATE_SUB(NOW(), INTERVAL ? SECOND)");
        $cleanup_stmt->bind_param("i", $lockout_time);
        $cleanup_stmt->execute();
        $cleanup_stmt->close();
        
        // Check recent attempts
        $check_stmt = $conn->prepare("SELECT COUNT(*) as attempts FROM login_attempts WHERE (username_or_email = ? OR ip_address = ?) AND success = FALSE AND attempted_at > DATE_SUB(NOW(), INTERVAL ? SECOND)");
        $check_stmt->bind_param("ssi", $identifier, $ip, $lockout_time);
        $check_stmt->execute();
        $result = $check_stmt->get_result();
        $row = $result->fetch_assoc();
        $check_stmt->close();
        
        return $row['attempts'] < $max_attempts;
    }

    function logLoginAttempt($conn, $identifier, $ip, $success) {
        $user_agent = $_SERVER['HTTP_USER_AGENT'] ?? '';
        $stmt = $conn->prepare("INSERT INTO login_attempts (username_or_email, ip_address, user_agent, success) VALUES (?, ?, ?, ?)");
        $stmt->bind_param("sssi", $identifier, $ip, $user_agent, $success);
        $stmt->execute();
        $stmt->close();
    }

    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $username = trim($_POST['username']);
        $password = $_POST['password'];
        $remember = isset($_POST['remember']);
        $ip_address = $_SERVER['REMOTE_ADDR'];
        
        // Validate input
        if (empty($username) || empty($password)) {
            logLoginAttempt($conn, $username, $ip_address, false);
            header("Location: login.php?error=" . urlencode("Please fill in all fields"));
            exit;
        }
        
        // Check rate limiting
        if (!checkRateLimit($conn, $username, $ip_address)) {
            logLoginAttempt($conn, $username, $ip_address, false);
            header("Location: login.php?error=" . urlencode("Too many failed login attempts. Please try again in 15 minutes."));
            exit;
        }
        
        // Check user credentials
        $stmt = $conn->prepare("SELECT id, username, email, password, role, status, last_login FROM admin_users WHERE (username = ? OR email = ?) AND status = 'active'");
        $stmt->bind_param("ss", $username, $username);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows === 1) {
            $user = $result->fetch_assoc();
            
            // Verify password
            if (password_verify($password, $user['password'])) {
                // Login successful
                logLoginAttempt($conn, $username, $ip_address, true);
                
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['username'] = $user['username'];
                $_SESSION['email'] = $user['email'];
                $_SESSION['role'] = $user['role'];
                $_SESSION['login_time'] = time();
                $_SESSION['ip_address'] = $ip_address;
                
                // Update last login
                $update_stmt = $conn->prepare("UPDATE admin_users SET last_login = NOW() WHERE id = ?");
                $update_stmt->bind_param("i", $user['id']);
                $update_stmt->execute();
                $update_stmt->close();
                
                // Handle remember me
                if ($remember) {
                    $token = bin2hex(random_bytes(32));
                    $expires = time() + (30 * 24 * 60 * 60); // 30 days
                    
                    $token_stmt = $conn->prepare("INSERT INTO remember_tokens (user_id, token, expires_at) VALUES (?, ?, FROM_UNIXTIME(?)) ON DUPLICATE KEY UPDATE token = ?, expires_at = FROM_UNIXTIME(?)");
                    $token_stmt->bind_param("issis", $user['id'], $token, $expires, $token, $expires);
                    $token_stmt->execute();
                    $token_stmt->close();
                    
                    setcookie('remember_token', $token, $expires, '/', '', true, true);
                }
                
                // Clear failed attempts for this user
                $clear_stmt = $conn->prepare("DELETE FROM login_attempts WHERE username_or_email = ? AND success = FALSE");
                $clear_stmt->bind_param("s", $username);
                $clear_stmt->execute();
                $clear_stmt->close();
                
                header("Location: data-management.php?success=" . urlencode("Welcome back, " . $user['username'] . "!"));
                exit;
                
            } else {
                logLoginAttempt($conn, $username, $ip_address, false);
                header("Location: login.php?error=" . urlencode("Invalid username or password"));
                exit;
            }
        } else {
            logLoginAttempt($conn, $username, $ip_address, false);
            header("Location: login.php?error=" . urlencode("Invalid username or password"));
            exit;
        }
        
        $stmt->close();
    } else {
        header("Location: login.php");
        exit;
    }

    $conn->close();
?>