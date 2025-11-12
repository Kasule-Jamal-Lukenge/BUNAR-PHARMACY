<?php
    // Use this in your index.php to display blogs

    $host = "localhost";
    $user = "root";
    $pass = "";
    $db = "bunar_pharmacy";
    $conn = new mysqli($host, $user, $pass, $db);

    if ($conn->connect_error) {
        die("Connection failed: " . $conn->connect_error);
    }

    // Getting latest 3 published blogs
    $blogs_query = "SELECT * FROM blogs WHERE status = 'published' ORDER BY created_at DESC LIMIT 3";
    $blogs_result = $conn->query($blogs_query);

    if ($blogs_result && $blogs_result->num_rows > 0) {
        while ($blog = $blogs_result->fetch_assoc()) {
            $image = !empty($blog['image_url']) ? htmlspecialchars($blog['image_url']) : 'placeholder.jpg';
            $date = date('F j, Y', strtotime($blog['created_at']));
            $title = htmlspecialchars($blog['title']);
            $excerpt = htmlspecialchars($blog['excerpt']);
            
            echo '<div class="col-lg-4 col-md-6 mb-4">
                    <div class="card blog-card h-100">
                        <div class="card-body">
                            <img src="' . $image . '" alt="' . $title . '" class="card-img blog-image">
                            <h5 class="card-title mt-3">' . $title . '</h5>
                            <p class="card-text">' . $excerpt . '</p>
                            <small class="text-muted">Published: <span class="date-posted">' . $date . '</span></small>
                        </div>
                    </div>
                </div>';
        }
    }

    $conn->close();
?>