<?php

    $host = "localhost";
    $user = "root";
    $pass = "";
    $db = "bunar_pharmacy";
    
    $conn = new mysqli($host, $user, $pass, $db);

    if ($conn->connect_error) {
        die("Connection failed: " . $conn->connect_error);
    }

?>