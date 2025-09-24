<?php
require 'vendor/autoload.php'; // PhpSpreadsheet autoload

use PhpOffice\PhpSpreadsheet\IOFactory;

// Database connection
$host = "localhost";
$user = "root";
$pass = "";
$db   = "bunar_pharmacy";  // change to your database
$conn = new mysqli($host, $user, $pass, $db);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

if (isset($_POST['import'])) {
    $fileName = $_FILES['excel_file']['tmp_name'];

    if ($_FILES['excel_file']['size'] > 0) {
        try {
            $spreadsheet = IOFactory::load($fileName);
            $sheetData = $spreadsheet->getActiveSheet()->toArray();

            // Skip header row, start from row 1
            for ($i = 1; $i < count($sheetData); $i++) {
                $name  = $sheetData[$i][0];
                $email = $sheetData[$i][1];
                $phone = $sheetData[$i][2];

                if (!empty($name) && !empty($email)) {
                    $stmt = $conn->prepare("INSERT INTO users (name, email, phone) VALUES (?, ?, ?)");
                    $stmt->bind_param("sss", $name, $email, $phone);
                    $stmt->execute();
                }
            }
            echo "Data imported successfully!";
        } catch (Exception $e) {
            echo "Error loading file: " . $e->getMessage();
        }
    } else {
        echo "Please upload a valid Excel file.";
    }
}
