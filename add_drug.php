// ========== add_drug.php ==========
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

    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['drug_name'])) {
        $drug_name = trim($_POST['drug_name']);
        $user_id = $_SESSION['user_id'];
        
        if (empty($drug_name)) {
            echo json_encode(['success' => false, 'message' => 'Drug name cannot be empty']);
            exit;
        }
        
        // Checking if drug already exists
        $check_stmt = $conn->prepare("SELECT id FROM drugs WHERE drug_name = ?");
        $check_stmt->bind_param("s", $drug_name);
        $check_stmt->execute();
        $result = $check_stmt->get_result();
        
        if ($result->num_rows > 0) {
            echo json_encode(['success' => false, 'message' => 'This drug already exists in the database']);
            $check_stmt->close();
            $conn->close();
            exit;
        }
        $check_stmt->close();
        
        // Inserting new drug
        $insert_stmt = $conn->prepare("INSERT INTO drugs (drug_name, added_by) VALUES (?, ?)");
        $insert_stmt->bind_param("si", $drug_name, $user_id);
        
        if ($insert_stmt->execute()) {
            // Logging audit trail
            $drug_id = $insert_stmt->insert_id;
            $audit_stmt = $conn->prepare("INSERT INTO drugs_audit (drug_id, action, new_value, changed_by) VALUES (?, 'INSERT', ?, ?)");
            $audit_stmt->bind_param("isi", $drug_id, $drug_name, $user_id);
            $audit_stmt->execute();
            $audit_stmt->close();
            var_dump($drug_name);
            return;
            echo json_encode(['success' => true, 'message' => "Drug '$drug_name' added successfully!"]);
        } else {
            echo json_encode(['success' => false, 'message' => "Error adding drug: '.$drug_name.'"  . $conn->error]);
        }
        
        $insert_stmt->close();
    } else {
        echo json_encode(['success' => false, 'message' => 'Invalid request']);
    }

    $conn->close();
?>