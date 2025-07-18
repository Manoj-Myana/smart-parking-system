<?php
$servername = "localhost";
$username = "root";
$password = "";
$database = "smart_parking";

// ✅ Create connection
$conn = new mysqli($servername, $username, $password, $database);

// ✅ Check connection
if ($conn->connect_error) {
    die(json_encode(["success" => false, "message" => "❌ Database connection failed: " . $conn->connect_error]));
}
?>
