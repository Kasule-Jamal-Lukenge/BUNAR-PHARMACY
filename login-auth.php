<?php
    // ========== authenticate.php ==========
    session_start();

    // Database connection
    require_once('conn.php');

    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            print_r($_POST);

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
            // var_dump($user);
            // return;
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