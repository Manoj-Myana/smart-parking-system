<?php
$servername = "localhost";
$username = "your_user_name";
$password = "your_password";
$database = "smart_parking";

$conn = new mysqli($servername, $username, $password, $database);

if ($conn->connect_error) {
    die("Database connection failed: " . $conn->connect_error);
}
?>
