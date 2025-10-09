<?php
    // Database connection
    require_once('conn.php');

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