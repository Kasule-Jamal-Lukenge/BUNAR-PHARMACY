// ========== update_drug.php ==========
<?php
    session_start();
    header('Content-Type: application/json');

    if (!isset($_SESSION['user_id'])) {
        echo json_encode(['success' => false, 'message' => 'Not authenticated']);
        exit;
    }

    // Establishing the database conection
    require_once('conn.php');

    if ($conn->connect_error) {
        echo json_encode(['success' => false, 'message' => 'Database connection failed']);
        exit;
    }

    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['drug_id']) && isset($_POST['drug_name'])) {
        $drug_id = intval($_POST['drug_id']);
        $new_drug_name = trim($_POST['drug_name']);
        $user_id = $_SESSION['user_id'];
        
        if (empty($new_drug_name)) {
            echo json_encode(['success' => false, 'message' => 'Drug name cannot be empty']);
            exit;
        }
        
        // Get old value for audit trail
        $old_stmt = $conn->prepare("SELECT drug_name FROM drugs WHERE id = ?");
        $old_stmt->bind_param("i", $drug_id);
        $old_stmt->execute();
        $old_result = $old_stmt->get_result();
        $old_drug = $old_result->fetch_assoc();
        $old_drug_name = $old_drug['drug_name'] ?? '';
        $old_stmt->close();
        
        // Check if new name already exists (excluding current drug)
        $check_stmt = $conn->prepare("SELECT id FROM drugs WHERE drug_name = ? AND id != ?");
        $check_stmt->bind_param("si", $new_drug_name, $drug_id);
        $check_stmt->execute();
        $result = $check_stmt->get_result();
        
        if ($result->num_rows > 0) {
            echo json_encode(['success' => false, 'message' => 'A drug with this name already exists']);
            $check_stmt->close();
            $conn->close();
            exit;
        }
        $check_stmt->close();
        
        // Update drug
        $update_stmt = $conn->prepare("UPDATE drugs SET drug_name = ? WHERE id = ?");
        $update_stmt->bind_param("si", $new_drug_name, $drug_id);
        
        if ($update_stmt->execute()) {
            if ($update_stmt->affected_rows > 0) {
                // Log audit trail
                $audit_stmt = $conn->prepare("INSERT INTO drugs_audit (drug_id, action, old_value, new_value, changed_by) VALUES (?, 'UPDATE', ?, ?, ?)");
                $audit_stmt->bind_param("issi", $drug_id, $old_drug_name, $new_drug_name, $user_id);
                $audit_stmt->execute();
                $audit_stmt->close();
                
                echo json_encode(['success' => true, 'message' => 'Drug updated successfully!']);
            } else {
                echo json_encode(['success' => false, 'message' => 'No changes made']);
            }
        } else {
            echo json_encode(['success' => false, 'message' => 'Error updating drug: ' . $conn->error]);
        }
        
        $update_stmt->close();
    } else {
        echo json_encode(['success' => false, 'message' => 'Invalid request']);
    }

    $conn->close();
?>