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

    try {
        // Getting the total number of blogs
        $total_result = $conn->query("SELECT COUNT(*) as total FROM blogs");
        $total = $total_result->fetch_assoc()['total'];
        
        // Getting published blogs
        $published_result = $conn->query("SELECT COUNT(*) as published FROM blogs WHERE status = 'published'");
        $published = $published_result->fetch_assoc()['published'];
        
        // Getting the most recent blogs
        $recent_result = $conn->query("SELECT COUNT(*) as recent FROM blogs WHERE created_at >= DATE_SUB(NOW(), INTERVAL 30 DAY)");
        $recent = $recent_result->fetch_assoc()['recent'];
        
        echo json_encode([
            'success' => true,
            'total' => $total,
            'published' => $published,
            'recent' => $recent
        ]);
        
    } catch (Exception $e) {
        echo json_encode(['success' => false, 'message' => 'Error fetching stats']);
    }

    $conn->close();
?>