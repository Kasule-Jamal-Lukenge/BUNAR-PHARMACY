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

    if (isset($_POST['id']) && is_numeric($_POST['id'])) {
        $blog_id = intval($_POST['id']);
        
        // Getting blog image path before deleting
        $get_stmt = $conn->prepare("SELECT image_url FROM blogs WHERE id = ?");
        $get_stmt->bind_param("i", $blog_id);
        $get_stmt->execute();
        $result = $get_stmt->get_result();
        $blog = $result->fetch_assoc();
        $get_stmt->close();
        
        // Deleting blog
        $delete_stmt = $conn->prepare("DELETE FROM blogs WHERE id = ?");
        $delete_stmt->bind_param("i", $blog_id);
        
        if ($delete_stmt->execute()) {
            if ($delete_stmt->affected_rows > 0) {
                // Deleting image file
                if (!empty($blog['image_url']) && file_exists($blog['image_url'])) {
                    unlink($blog['image_url']);
                }
                echo json_encode(['success' => true, 'message' => 'Blog post deleted successfully!']);
            } else {
                echo json_encode(['success' => false, 'message' => 'Blog post not found']);
            }
        } else {
            echo json_encode(['success' => false, 'message' => 'Error deleting blog post: ' . $conn->error]);
        }
        
        $delete_stmt->close();
    } else {
        echo json_encode(['success' => false, 'message' => 'Invalid blog ID']);
    }

    $conn->close();
?>