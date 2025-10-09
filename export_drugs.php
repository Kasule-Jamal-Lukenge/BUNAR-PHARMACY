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