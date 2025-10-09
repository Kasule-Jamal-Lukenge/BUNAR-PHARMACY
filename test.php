// ========== import_drugs.php ==========
<?php
    session_start();

    if (!isset($_SESSION['user_id'])) {
        header("Location: login.php");
        exit;
    }

    require_once 'vendor/autoload.php';
    use PhpOffice\PhpSpreadsheet\IOFactory;

    //Establishing the database connection
    require_once('conn.php');

    if ($conn->connect_error) {
        die("Connection failed: " . $conn->connect_error);
    }

    if (isset($_POST['bulk_import']) && isset($_FILES['excel_file'])) {
        $file_tmp = $_FILES['excel_file']['tmp_name'];
        $file_ext = strtolower(pathinfo($_FILES['excel_file']['name'], PATHINFO_EXTENSION));
        
        // Validate file
        if (!in_array($file_ext, ['xls', 'xlsx'])) {
            header("Location: data-management.php?status=danger&message=" . urlencode("Invalid file format. Please upload .xls or .xlsx files only."));
            exit;
        }
        
        if ($_FILES['excel_file']['size'] > 5242880) {
            header("Location: data-management.php?status=danger&message=" . urlencode("File size too large. Maximum size is 5MB."));
            exit;
        }
        
        try {
            $spreadsheet = IOFactory::load($file_tmp);
            $worksheet = $spreadsheet->getActiveSheet();
            $rows = $worksheet->toArray();
            
            $imported = 0;
            $skipped = 0;
            $errors = 0;
            $user_id = $_SESSION['user_id'];
            
            // Skip header row and process data
            for ($i = 1; $i < count($rows); $i++) {
                $drug_name = trim($rows[$i][0] ?? '');
                
                // Skip empty rows
                if (empty($drug_name)) {
                    continue;
                }
                
                // Check if drug already exists
                $check_stmt = $conn->prepare("SELECT id FROM drugs WHERE drug_name = ?");
                $check_stmt->bind_param("s", $drug_name);
                $check_stmt->execute();
                $result = $check_stmt->get_result();
                
                if ($result->num_rows > 0) {
                    $skipped++;
                    $check_stmt->close();
                    continue;
                }
                $check_stmt->close();
                
                // Insert new drug
                $insert_stmt = $conn->prepare("INSERT INTO drugs (drug_name, added_by) VALUES (?, ?)");
                $insert_stmt->bind_param("si", $drug_name, $user_id);
                
                if ($insert_stmt->execute()) {
                    $imported++;
                } else {
                    $errors++;
                }
                $insert_stmt->close();
            }
            
            $message = "Successfully imported $imported drugs.";
            if ($skipped > 0) {
                $message .= " $skipped duplicate drugs were skipped.";
            }
            if ($errors > 0) {
                $message .= " $errors drugs had errors.";
            }
            
            header("Location: data-management.php?status=success&message=" . urlencode($message));
            
        } catch (Exception $e) {
            header("Location: data-management.php?status=danger&message=" . urlencode("Error processing file: " . $e->getMessage()));
        }
    } else {
        header("Location: data-management.php?status=danger&message=" . urlencode("No file uploaded."));
    }

    $conn->close();
?>

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
        
        // Check if drug already exists
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
        
        // Insert new drug
        $insert_stmt = $conn->prepare("INSERT INTO drugs (drug_name, added_by) VALUES (?, ?)");
        $insert_stmt->bind_param("si", $drug_name, $user_id);
        
        if ($insert_stmt->execute()) {
            // Log audit trail
            $drug_id = $insert_stmt->insert_id;
            $audit_stmt = $conn->prepare("INSERT INTO drugs_audit (drug_id, action, new_value, changed_by) VALUES (?, 'INSERT', ?, ?)");
            $audit_stmt->bind_param("isi", $drug_id, $drug_name, $user_id);
            $audit_stmt->execute();
            $audit_stmt->close();
            
            echo json_encode(['success' => true, 'message' => "Drug '$drug_name' added successfully!"]);
        } else {
            echo json_encode(['success' => false, 'message' => 'Error adding drug: ' . $conn->error]);
        }
        
        $insert_stmt->close();
    } else {
        echo json_encode(['success' => false, 'message' => 'Invalid request']);
    }

    $conn->close();
?>

// ========== update_drug.php ==========
<?php
    session_start();
    header('Content-Type: application/json');

    if (!isset($_SESSION['user_id'])) {
        echo json_encode(['success' => false, 'message' => 'Not authenticated']);
        exit;
    }

    // Establishing the database coonection

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

// ========== delete_drug.php ==========
<?php
    session_start();
    header('Content-Type: application/json');

    if (!isset($_SESSION['user_id'])) {
        echo json_encode(['success' => false, 'message' => 'Not authenticated']);
        exit;
    }

    $host = "localhost";
    $user = "root";
    $pass = "";
    $db = "bunar_pharmacy";
    $conn = new mysqli($host, $user, $pass, $db);

    if ($conn->connect_error) {
        echo json_encode(['success' => false, 'message' => 'Database connection failed']);
        exit;
    }

    if (isset($_POST['id']) && is_numeric($_POST['id'])) {
        $drug_id = intval($_POST['id']);
        $user_id = $_SESSION['user_id'];
        
        // Get drug name for audit trail
        $get_stmt = $conn->prepare("SELECT drug_name FROM drugs WHERE id = ?");
        $get_stmt->bind_param("i", $drug_id);
        $get_stmt->execute();
        $result = $get_stmt->get_result();
        $drug = $result->fetch_assoc();
        $drug_name = $drug['drug_name'] ?? '';
        $get_stmt->close();
        
        // Delete drug
        $delete_stmt = $conn->prepare("DELETE FROM drugs WHERE id = ?");
        $delete_stmt->bind_param("i", $drug_id);
        
        if ($delete_stmt->execute()) {
            if ($delete_stmt->affected_rows > 0) {
                // Log audit trail
                $audit_stmt = $conn->prepare("INSERT INTO drugs_audit (drug_id, action, old_value, changed_by) VALUES (?, 'DELETE', ?, ?)");
                $audit_stmt->bind_param("isi", $drug_id, $drug_name, $user_id);
                $audit_stmt->execute();
                $audit_stmt->close();
                
                echo json_encode(['success' => true, 'message' => "Drug '$drug_name' deleted successfully!"]);
            } else {
                echo json_encode(['success' => false, 'message' => 'Drug not found']);
            }
        } else {
            echo json_encode(['success' => false, 'message' => 'Error deleting drug: ' . $conn->error]);
        }
        
        $delete_stmt->close();
    } else {
        echo json_encode(['success' => false, 'message' => 'Invalid ID']);
    }

    $conn->close();
?>

// ========== get_drug_stats.php ==========
<?php
    session_start();
    header('Content-Type: application/json');

    if (!isset($_SESSION['user_id'])) {
        echo json_encode(['success' => false, 'message' => 'Not authenticated']);
        exit;
    }

    $host = "localhost";
    $user = "root";
    $pass = "";
    $db = "bunar_pharmacy";
    $conn = new mysqli($host, $user, $pass, $db);

    if ($conn->connect_error) {
        echo json_encode(['success' => false, 'message' => 'Database connection failed']);
        exit;
    }

    try {
        // Get total drugs
        $total_result = $conn->query("SELECT COUNT(*) as total FROM drugs");
        $total = $total_result->fetch_assoc()['total'];
        
        // Get drugs added in past month
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

// ========== export_drugs.php ==========
<?php
    session_start();

    if (!isset($_SESSION['user_id'])) {
        header("Location: login.php");
        exit;
    }

    require_once 'vendor/autoload.php';
    use PhpOffice\PhpSpreadsheet\Spreadsheet;
    use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

    $host = "localhost";
    $user = "root";
    $pass = "";
    $db = "bunar_pharmacy";
    $conn = new mysqli($host, $user, $pass, $db);

    if ($conn->connect_error) {
        die("Connection failed: " . $conn->connect_error);
    }

    try {
        // Fetch all drugs
        $result = $conn->query("SELECT id, drug_name, created_at FROM drugs ORDER BY drug_name ASC");
        
        // Create new Spreadsheet
        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        
        // Set headers
        $sheet->setCellValue('A1', 'ID');
        $sheet->setCellValue('B1', 'Drug Name');
        $sheet->setCellValue('C1', 'Date Added');
        
        // Style header row
        $sheet->getStyle('A1:C1')->getFont()->setBold(true);
        $sheet->getStyle('A1:C1')->getFill()->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID);
        $sheet->getStyle('A1:C1')->getFill()->getStartColor()->setRGB('94061b');
        $sheet->getStyle('A1:C1')->getFont()->getColor()->setRGB('FFFFFF');
        
        // Add data
        $row = 2;
        if ($result && $result->num_rows > 0) {
            while ($drug = $result->fetch_assoc()) {
                $sheet->setCellValue('A' . $row, $drug['id']);
                $sheet->setCellValue('B' . $row, $drug['drug_name']);
                $sheet->setCellValue('C' . $row, date('M j, Y', strtotime($drug['created_at'])));
                $row++;
            }
        }
        
        // Auto-size columns
        foreach (range('A', 'C') as $column) {
            $sheet->getColumnDimension($column)->setAutoSize(true);
        }
        
        // Set filename and headers for download
        $filename = 'bumar_pharmacy_drugs_' . date('Y-m-d_H-i-s') . '.xlsx';
        
        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment;filename="' . $filename . '"');
        header('Cache-Control: max-age=0');
        
        // Create writer and output file
        $writer = new Xlsx($spreadsheet);
        $writer->save('php://output');
        
    } catch (Exception $e) {
        die('Error creating export file: ' . $e->getMessage());
    }

    $conn->close();
?>

// ========== INSTALLATION INSTRUCTIONS ==========
/*
STEP 1: Install PhpSpreadsheet (if not already installed)
Run this command in your project directory:
    composer require phpoffice/phpspreadsheet

STEP 2: Create the database tables
Run the SQL commands in database_setup_drugs.sql

STEP 3: File Structure
Make sure you have these files in your project:
    - data-management.php (main page)
    - import_drugs.php (bulk upload handler)
    - add_drug.php (single drug addition)
    - update_drug.php (edit drug)
    - delete_drug.php (delete drug)
    - get_drug_stats.php (statistics)
    - export_drugs.php (export to Excel)
    - session_check.php (authentication check)
    - logout.php (logout handler)

STEP 4: Excel File Format
The Excel file should have drug names in the FIRST COLUMN (Column A)
Example:
    Row 1: Drug Name (header - will be skipped)
    Row 2: Paracetamol
    Row 3: Ibuprofen
    Row 4: Aspirin
    ...

STEP 5: Features Summary
✓ User authentication with logout button
✓ Bulk upload drugs from Excel file
✓ Add single drug manually
✓ Edit existing drugs
✓ Delete drugs
✓ Search functionality
✓ Export drugs to Excel
✓ Statistics: Total drugs & drugs added in past month
✓ Audit trail for all changes
✓ "NEW" badge for drugs added in last 7 days
✓ Duplicate prevention
✓ User tracking (who added/modified drugs)

STEP 6: Security Features
✓ Session management
✓ SQL injection protection (prepared statements)
✓ File upload validation
✓ User authentication required
✓ Audit logging
*/