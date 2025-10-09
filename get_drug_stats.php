// ========== get_drug_stats.php ==========
<?php
    session_start();
    header('Content-Type: application/json');

    if (!isset($_SESSION['user_id'])) {
        echo json_encode(['success' => false, 'message' => 'Not authenticated']);
        exit;
    }

    // Establishing the database connection
    require_once('conn.php');

    if ($conn->connect_error) {
        echo json_encode(['success' => false, 'message' => 'Database connection failed']);
        exit;
    }

    try {
        // Getting total drugs
        $total_result = $conn->query("SELECT COUNT(*) as total FROM drugs");
        $total = $total_result->fetch_assoc()['total'];
        
        // Getting drugs added in past month
        $recent_result = $conn->query("SELECT COUNT(*) as recent FROM drugs WHERE created_at >= DATE_SUB(NOW(), INTERVAL 1 MONTH)");
        $recent = $recent_result->fetch_assoc()['recent'];
        
        echo json_encode([
            'success' => true,
            'total' => $total,
            'recent' => $recent
        ]);
        
    } catch (Exception $e) {
        echo json_encode(['success' => false, 'message' => 'Error fetching stats']);
    }

    $conn->close();
?>