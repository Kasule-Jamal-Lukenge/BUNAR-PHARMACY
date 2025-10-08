<?php
    session_start();

    // Database connection for token cleanup
    $host = "localhost";
    $user = "root";
    $pass = "";
    $db = "bunar_pharmacy";
    $conn = new mysqli($host, $user, $pass, $db);

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