<?php
// Establishing the database connection
$host = "localhost";
$user = "root";
$pass = "";
$db   = "bunar_pharmacy";
$conn = new mysqli($host, $user, $pass, $db);
?>
<!DOCTYPE html>
<html>
<head>
    <title>Excel Import</title>
</head>
<body>
    <h2>Import Excel File</h2>
    <form action="import.php" method="post" enctype="multipart/form-data">
        <input type="file" name="excel_file" accept=".xls,.xlsx" required>
        <button type="submit" name="import">Upload & Import</button>
    </form>

     <h3>Stored Records</h3>
    <table>
        <tr>
            <th>ID</th>
            <th>Name</th>
            <th>Email</th>
            <th>Phone</th>
        </tr>
        <?php
        $result = $conn->query("SELECT * FROM users ORDER BY id DESC");
        if ($result->num_rows > 0) {
            while ($row = $result->fetch_assoc()) {
                echo "<tr>
                        <td>{$row['id']}</td>
                        <td>{$row['name']}</td>
                        <td>{$row['email']}</td>
                        <td>{$row['phone']}</td>
                      </tr>";
            }
        } else {
            echo "<tr><td colspan='4'>No records found</td></tr>";
        }
        ?>
    </table>
</body>
</html>
