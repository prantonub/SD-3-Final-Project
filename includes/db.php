<?php
$host = "localhost";      // XAMPP default
$user = "root";           // XAMPP default
$password = "";           // No password in XAMPP
$database = "electronics_shop";

// Connect to database server
$conn = new mysqli($host, $user, $password, $database);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}
?>
