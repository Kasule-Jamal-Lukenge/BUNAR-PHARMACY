<?php
    session_start();
    header('Content-Type: application/json');

    if (!isset($_SESSION['user_id'])) {
        echo json_encode(['success' => false, 'message' => 'Not authenticated']);
        exit;
    }

    // Establishing database connection
    require_once('./conn.php');

    if ($conn->connect_error) {
        echo json_encode(['success' => false, 'message' => 'Database connection failed']);
        exit;
    }

    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $blog_id = intval($_POST['blog_id']);
        $title = trim($_POST['title']);
        $excerpt = trim($_POST['excerpt']);
        $content = trim($_POST['content']);
        $category = trim($_POST['category']);
        $author = trim($_POST['author']);
        $status = $_POST['status'];
        $current_image = $_POST['current_image'];
        
        // Generate new slug
        $slug = strtolower(trim(preg_replace('/[^A-Za-z0-9-]+/', '-', $title), '-'));
        
        // Handle new image upload
        $image_url = $current_image;
        if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
            $upload_dir = 'uploads/blogs/';
            
            if (!file_exists($upload_dir)) {
                mkdir($upload_dir, 0777, true);
            }
            
            $file_extension = pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION);
            $file_name = $slug . '-' . time() . '.' . $file_extension;
            $target_file = $upload_dir . $file_name;
            
            $allowed_types = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
            if (!in_array(strtolower($file_extension), $allowed_types)) {
                echo json_encode(['success' => false, 'message' => 'Invalid image format']);
                exit;
            }
            
            if ($_FILES['image']['size'] > 5242880) {
                echo json_encode(['success' => false, 'message' => 'Image size too large']);
                exit;
            }
            
            if (move_uploaded_file($_FILES['image']['tmp_name'], $target_file)) {
                // Delete old image
                if (!empty($current_image) && file_exists($current_image)) {
                    unlink($current_image);
                }
                $image_url = $target_file;
            }
        }
        
        // Update blog post
        $stmt = $conn->prepare("UPDATE blogs SET title = ?, slug = ?, excerpt = ?, content = ?, image_url = ?, category = ?, author = ?, status = ? WHERE id = ?");
        $stmt->bind_param("ssssssssi", $title, $slug, $excerpt, $content, $image_url, $category, $author, $status, $blog_id);
        
        if ($stmt->execute()) {
            if ($stmt->affected_rows > 0) {
                echo json_encode(['success' => true, 'message' => 'Blog post updated successfully!']);
            } else {
                echo json_encode(['success' => false, 'message' => 'No changes made']);
            }
        } else {
            echo json_encode(['success' => false, 'message' => 'Error updating blog post: ' . $conn->error]);
        }
        
        $stmt->close();
    } else {
        echo json_encode(['success' => false, 'message' => 'Invalid request method']);
    }

    $conn->close();
?>