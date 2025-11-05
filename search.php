<?php
session_start();

$host = "localhost";
$user = "root";
$pass = "";
$db = "bunar_pharmacy";
$conn = new mysqli($host, $user, $pass, $db);

$search_term = $_GET['q'] ?? '';
$page_title = !empty($search_term) ? "Search Results for '" . htmlspecialchars($search_term) . "' - Bumar Pharmacy" : "Search Drugs - Bumar Pharmacy";

// Log search
if (!empty($search_term)) {
    $ip = $_SERVER['REMOTE_ADDR'];
    $user_agent = $_SERVER['HTTP_USER_AGENT'];
    
    $log_stmt = $conn->prepare("INSERT INTO search_logs (search_term, ip_address, user_agent) VALUES (?, ?, ?)");
    $log_stmt->bind_param("sss", $search_term, $ip, $user_agent);
    $log_stmt->execute();
    
    // Search drugs
    $search_query = "SELECT * FROM drugs WHERE drug_name LIKE ? OR drug_description LIKE ? OR manufacturer LIKE ? ORDER BY page_views DESC, drug_name ASC";
    $like_term = "%$search_term%";
    $search_stmt = $conn->prepare($search_query);
    $search_stmt->bind_param("sss", $like_term, $like_term, $like_term);
    $search_stmt->execute();
    $results = $search_stmt->get_result();
    
    // Update log with results count
    $update_log = $conn->prepare("UPDATE search_logs SET results_count = ? WHERE id = LAST_INSERT_ID()");
    $results_count = $results->num_rows;
    $update_log->bind_param("i", $results_count);
    $update_log->execute();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $page_title; ?></title>
    <meta name="description" content="Search for medications at Bumar Pharmacy. Fast delivery across Uganda. Licensed pharmacy with quality medications.">
    <meta name="robots" content="index, follow">
    <!-- Include your CSS -->
</head>
<body>
    <!-- Include navigation -->
    
    <div class="container my-5" style="padding-top: 130px;">
        <h1>Search Results<?php echo !empty($search_term) ? ' for "' . htmlspecialchars($search_term) . '"' : ''; ?></h1>
        
        <?php if (isset($results) && $results->num_rows > 0): ?>
            <p>Found <?php echo $results->num_rows; ?> result(s)</p>
            <div class="row">
                <?php while ($drug = $results->fetch_assoc()): ?>
                <div class="col-md-4 mb-4">
                    <div class="card h-100">
                        <div class="card-body">
                            <h5><?php echo htmlspecialchars($drug['drug_name']); ?></h5>
                            <p class="text-muted"><?php echo htmlspecialchars(substr($drug['drug_description'] ?? '', 0, 100)); ?>...</p>
                            <p class="font-weight-bold">UGX <?php echo number_format($drug['price'] ?? 0); ?></p>
                            <a href="drug-detail.php?drug=<?php echo urlencode($drug['slug']); ?>" class="btn btn-primary">
                                View Details
                            </a>
                        </div>
                    </div>
                </div>
                <?php endwhile; ?>
            </div>
        <?php elseif (!empty($search_term)): ?>
            <div class="alert alert-info">
                <h4>No results found</h4>
                <p>We couldn't find any medications matching "<?php echo htmlspecialchars($search_term); ?>". Please try:</p>
                <ul>
                    <li>Checking your spelling</li>
                    <li>Using different keywords</li>
                    <li>Searching for a related medication</li>
                </ul>
                <p>Or <a href="tel:+256749059309">call us</a> to speak with a pharmacist.</p>
            </div>
        <?php endif; ?>
    </div>
    
    <!-- Include footer -->
</body>
</html>
<?php
$conn->close();
?>