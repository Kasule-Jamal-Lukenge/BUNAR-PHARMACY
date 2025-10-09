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