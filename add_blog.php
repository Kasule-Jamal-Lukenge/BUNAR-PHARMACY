<?php
session_start();
header('Content-Type: application/json');

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'Not authenticated']);
    exit;
}

//establishing database connection
require_once('./conn.php');

if ($conn->connect_error) {
    echo json_encode(['success' => false, 'message' => 'Database connection failed']);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title = trim($_POST['title']);
    $excerpt = trim($_POST['excerpt']);
    $content = trim($_POST['content']);
    $category = trim($_POST['category']);
    $author = trim($_POST['author']) ?: $_SESSION['username'];
    $status = $_POST['status'];
    $user_id = $_SESSION['user_id'];
    
    // Generate slug
    $slug = strtolower(trim(preg_replace('/[^A-Za-z0-9-]+/', '-', $title), '-'));
    
    // Handle image upload
    $image_url = '';
    if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
        $upload_dir = 'uploads/blogs/';
        
        // Create directory if it doesn't exist
        if (!file_exists($upload_dir)) {
            mkdir($upload_dir, 0777, true);
        }
        
        $file_extension = pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION);
        $file_name = $slug . '-' . time() . '.' . $file_extension;
        $target_file = $upload_dir . $file_name;
        
        // Validate image
        $allowed_types = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
        if (!in_array(strtolower($file_extension), $allowed_types)) {
            echo json_encode(['success' => false, 'message' => 'Invalid image format. Use JPG, PNG, or GIF']);
            exit;
        }
        
        if ($_FILES['image']['size'] > 5242880) { // 5MB
            echo json_encode(['success' => false, 'message' => 'Image size too large. Maximum 5MB']);
            exit;
        }
        
        if (move_uploaded_file($_FILES['image']['tmp_name'], $target_file)) {
            $image_url = $target_file;
        }
    }
    
    // Insert blog post
    $stmt = $conn->prepare("INSERT INTO blogs (title, slug, excerpt, content, image_url, category, author, status, created_by) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)");
    $stmt->bind_param("ssssssssi", $title, $slug, $excerpt, $content, $image_url, $category, $author, $status, $user_id);
    
    if ($stmt->execute()) {
        echo json_encode(['success' => true, 'message' => 'Blog post created successfully!']);
    } else {
        echo json_encode(['success' => false, 'message' => 'Error creating blog post: ' . $conn->error]);
    }
    
    $stmt->close();
} else {
    echo json_encode(['success' => false, 'message' => 'Invalid request method']);
}

$conn->close();
?>
