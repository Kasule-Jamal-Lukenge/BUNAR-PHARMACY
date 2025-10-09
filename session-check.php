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