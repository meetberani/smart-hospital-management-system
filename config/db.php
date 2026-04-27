<?php
// Database configuration
$host = "localhost";
$user = "root";
$pass = "";
$db   = "hospital_db";

// Create connection
$conn = mysqli_connect($host, $user, $pass, $db);

// Check connection
if (!$conn) {
    die("❌ Database connection failed: " . mysqli_connect_error());
}

// Set charset (recommended)
mysqli_set_charset($conn, "utf8");
?>
