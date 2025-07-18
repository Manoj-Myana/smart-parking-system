<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
header("Content-Type: application/json");

// Database connection
$conn = new mysqli("localhost", "root", "", "smart_parking");

if ($conn->connect_error) {
    echo json_encode(["success" => false, "message" => "Database connection failed: " . $conn->connect_error]);
    exit;
}

// Get search query
$data = json_decode(file_get_contents("php://input"), true);
$searchTerm = isset($data["search"]) ? $conn->real_escape_string($data["search"]) : "";

if (empty($searchTerm)) {
    echo json_encode(["success" => false, "message" => "Search term is required"]);
    exit;
}

// Search for matching parking slots
$query = "SELECT owner_id, parking_name, area, city, pincode 
          FROM parking_owners 
          WHERE parking_name LIKE ? 
             OR area LIKE ? 
             OR city LIKE ? 
             OR pincode LIKE ?";
$stmt = $conn->prepare($query);

$searchPattern = "%$searchTerm%";
$stmt->bind_param("ssss", $searchPattern, $searchPattern, $searchPattern, $searchPattern);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows > 0) {
    $parkingSlots = [];
    while ($row = $result->fetch_assoc()) {
        $parkingSlots[] = $row;
    }
    echo json_encode(["success" => true, "parkings" => $parkingSlots]);
} else {
    echo json_encode(["success" => false, "message" => "No matching parking slots found"]);
}

// Close connection
$stmt->close();
$conn->close();
?>
