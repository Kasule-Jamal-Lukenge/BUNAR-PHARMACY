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

    if (isset($_GET['id']) && is_numeric($_GET['id'])) {
        $blog_id = intval($_GET['id']);
        
        $stmt = $conn->prepare("SELECT * FROM blogs WHERE id = ?");
        $stmt->bind_param("i", $blog_id);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows > 0) {
            $blog = $result->fetch_assoc();
            echo json_encode(['success' => true, 'blog' => $blog]);
        } else {
            echo json_encode(['success' => false, 'message' => 'Blog post not found']);
        }
        
        $stmt->close();
    } else {
        echo json_encode(['success' => false, 'message' => 'Invalid blog ID']);
    }

    $conn->close();
?>