<?php
// ========== import.php ==========
require_once 'vendor/autoload.php'; // You'll need to install PhpSpreadsheet via Composer

use PhpOffice\PhpSpreadsheet\IOFactory;

// Database connection
$host = "localhost";
$user = "root";
$pass = "";
$db = "bunar_pharmacy";
$conn = new mysqli($host, $user, $pass, $db);

if ($conn->connect_error) {
    die(json_encode(['success' => false, 'message' => 'Connection failed: ' . $conn->connect_error]));
}

if (isset($_POST['import']) && isset($_FILES['excel_file'])) {
    $target_dir = "uploads/";
    
    // Create uploads directory if it doesn't exist
    if (!file_exists($target_dir)) {
        mkdir($target_dir, 0777, true);
    }
    
    $file_name = $_FILES['excel_file']['name'];
    $file_tmp = $_FILES['excel_file']['tmp_name'];
    $file_ext = strtolower(pathinfo($file_name, PATHINFO_EXTENSION));
    
    // Validate file
    if (!in_array($file_ext, ['xls', 'xlsx'])) {
        header("Location: data-management.php?status=danger&message=" . urlencode("Invalid file format. Please upload .xls or .xlsx files only."));
        exit;
    }
    
    if ($_FILES['excel_file']['size'] > 5242880) { // 5MB limit
        header("Location: data-management.php?status=danger&message=" . urlencode("File size too large. Maximum size is 5MB."));
        exit;
    }
    
    try {
        // Load the spreadsheet
        $spreadsheet = IOFactory::load($file_tmp);
        $worksheet = $spreadsheet->getActiveSheet();
        $rows = $worksheet->toArray();
        
        $imported = 0;
        $errors = 0;
        
        // Skip header row and process data
        for ($i = 1; $i < count($rows); $i++) {
            $name = trim($rows[$i][0] ?? '');
            $email = trim($rows[$i][1] ?? '');
            $phone = trim($rows[$i][2] ?? '');
            
            // Skip empty rows
            if (empty($name) && empty($email) && empty($phone)) {
                continue;
            }
            
            // Validate required fields
            if (empty($name) || empty($email)) {
                $errors++;
                continue;
            }
            
            // Check if email already exists
            $check_stmt = $conn->prepare("SELECT id FROM users WHERE email = ?");
            $check_stmt->bind_param("s", $email);
            $check_stmt->execute();
            $result = $check_stmt->get_result();
            
            if ($result->num_rows > 0) {
                // Update existing record
                $update_stmt = $conn->prepare("UPDATE users SET name = ?, phone = ?, updated_at = NOW() WHERE email = ?");
                $update_stmt->bind_param("sss", $name, $phone, $email);
                if ($update_stmt->execute()) {
                    $imported++;
                } else {
                    $errors++;
                }
                $update_stmt->close();
            } else {
                // Insert new record
                $insert_stmt = $conn->prepare("INSERT INTO users (name, email, phone, created_at) VALUES (?, ?, ?, NOW())");
                $insert_stmt->bind_param("sss", $name, $email, $phone);
                if ($insert_stmt->execute()) {
                    $imported++;
                } else {
                    $errors++;
                }
                $insert_stmt->close();
            }
            $check_stmt->close();
        }
        
        $message = "Successfully imported $imported records.";
        if ($errors > 0) {
            $message .= " $errors records had errors and were skipped.";
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

// ========== delete_record.php ==========
<?php
header('Content-Type: application/json');

// Database connection
$host = "localhost";
$user = "root";
$pass = "";
$db = "bunar_pharmacy";
$conn = new mysqli($host, $user, $pass, $db);

if ($conn->connect_error) {
    echo json_encode(['success' => false, 'message' => 'Connection failed: ' . $conn->connect_error]);
    exit;
}

if (isset($_POST['id']) && is_numeric($_POST['id'])) {
    $id = intval($_POST['id']);
    
    $stmt = $conn->prepare("DELETE FROM users WHERE id = ?");
    $stmt->bind_param("i", $id);
    
    if ($stmt->execute()) {
        if ($stmt->affected_rows > 0) {
            echo json_encode(['success' => true, 'message' => 'Record deleted successfully']);
        } else {
            echo json_encode(['success' => false, 'message' => 'Record not found']);
        }
    } else {
        echo json_encode(['success' => false, 'message' => 'Database error: ' . $stmt->error]);
    }
    
    $stmt->close();
} else {
    echo json_encode(['success' => false, 'message' => 'Invalid ID provided']);
}

$conn->close();
?>

// ========== delete_multiple.php ==========
<?php
header('Content-Type: application/json');

// Database connection
$host = "localhost";
$user = "root";
$pass = "";
$db = "bunar_pharmacy";
$conn = new mysqli($host, $user, $pass, $db);

if ($conn->connect_error) {
    echo json_encode(['success' => false, 'message' => 'Connection failed: ' . $conn->connect_error]);
    exit;
}

$input = json_decode(file_get_contents('php://input'), true);

if (isset($input['ids']) && is_array($input['ids']) && count($input['ids']) > 0) {
    $ids = array_filter($input['ids'], 'is_numeric');
    
    if (count($ids) > 0) {
        $placeholders = str_repeat('?,', count($ids) - 1) . '?';
        $stmt = $conn->prepare("DELETE FROM users WHERE id IN ($placeholders)");
        
        $types = str_repeat('i', count($ids));
        $stmt->bind_param($types, ...$ids);
        
        if ($stmt->execute()) {
            $deleted_count = $stmt->affected_rows;
            echo json_encode([
                'success' => true, 
                'message' => "$deleted_count records deleted successfully",
                'deleted_count' => $deleted_count
            ]);
        } else {
            echo json_encode(['success' => false, 'message' => 'Database error: ' . $stmt->error]);
        }
        
        $stmt->close();
    } else {
        echo json_encode(['success' => false, 'message' => 'No valid IDs provided']);
    }
} else {
    echo json_encode(['success' => false, 'message' => 'Invalid input data']);
}

$conn->close();
?>

// ========== export_data.php ==========
<?php
require_once 'vendor/autoload.php';

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

// Database connection
$host = "localhost";
$user = "root";
$pass = "";
$db = "bunar_pharmacy";
$conn = new mysqli($host, $user, $pass, $db);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

try {
    // Fetch all data
    $result = $conn->query("SELECT id, name, email, phone, created_at FROM users ORDER BY id DESC");
    
    // Create new Spreadsheet object
    $spreadsheet = new Spreadsheet();
    $sheet = $spreadsheet->getActiveSheet();
    
    // Set headers
    $sheet->setCellValue('A1', 'ID');
    $sheet->setCellValue('B1', 'Name');
    $sheet->setCellValue('C1', 'Email');
    $sheet->setCellValue('D1', 'Phone');
    $sheet->setCellValue('E1', 'Date Added');
    
    // Style the header row
    $sheet->getStyle('A1:E1')->getFont()->setBold(true);
    $sheet->getStyle('A1:E1')->getFill()->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID);
    $sheet->getStyle('A1:E1')->getFill()->getStartColor()->setRGB('94061b');
    $sheet->getStyle('A1:E1')->getFont()->getColor()->setRGB('FFFFFF');
    
    // Add data
    $row = 2;
    if ($result && $result->num_rows > 0) {
        while ($data = $result->fetch_assoc()) {
            $sheet->setCellValue('A' . $row, $data['id']);
            $sheet->setCellValue('B' . $row, $data['name']);
            $sheet->setCellValue('C' . $row, $data['email']);
            $sheet->setCellValue('D' . $row, $data['phone']);
            $sheet->setCellValue('E' . $row, $data['created_at'] ?? 'N/A');
            $row++;
        }
    }
    
    // Auto-size columns
    foreach (range('A', 'E') as $column) {
        $sheet->getColumnDimension($column)->setAutoSize(true);
    }
    
    // Set filename and headers for download
    $filename = 'bumar_pharmacy_data_' . date('Y-m-d_H-i-s') . '.xlsx';
    
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

// ========== get_stats.php ==========
<?php
header('Content-Type: application/json');

// Database connection
$host = "localhost";
$user = "root";
$pass = "";
$db = "bunar_pharmacy";
$conn = new mysqli($host, $user, $pass, $db);

if ($conn->connect_error) {
    echo json_encode(['success' => false, 'message' => 'Connection failed']);
    exit;
}

try {
    // Get total records
    $total_result = $conn->query("SELECT COUNT(*) as total FROM users");
    $total = $total_result->fetch_assoc()['total'];
    
    // Get today's uploads
    $recent_result = $conn->query("SELECT COUNT(*) as recent FROM users WHERE DATE(created_at) = CURDATE()");
    $recent = $recent_result ? $recent_result->fetch_assoc()['recent'] : 0;
    
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