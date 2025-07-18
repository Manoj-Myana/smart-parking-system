<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
header('Content-Type: application/json');

// Database connection
$conn = new mysqli("localhost", "root", "", "smart_parking");

if ($conn->connect_error) {
    echo json_encode(["success" => false, "message" => "Database connection failed: " . $conn->connect_error]);
    exit;
}

// Read input JSON
$data = json_decode(file_get_contents("php://input"), true);

// ✅ Validate required fields
if (!isset($data["parking_owner_id"], $data["vehicle_owner_id"], $data["entry_time"], $data["exit_time"], $data["vehicle_type"])) {
    echo json_encode(["success" => false, "message" => "Invalid booking data."]);
    exit;
}

// ✅ Prepare data (Sanitize inputs)
$parking_owner_id = (int) $data["parking_owner_id"];
$vehicle_owner_id = (int) $data["vehicle_owner_id"];
$entry_time = $conn->real_escape_string($data["entry_time"]);
$exit_time = $conn->real_escape_string($data["exit_time"]);
$vehicle_type = $conn->real_escape_string($data["vehicle_type"]);

// ✅ Insert into `parking_bookings`
$query = "INSERT INTO parking_bookings (parking_owner_id, vehicle_owner_id, entry_time, exit_time, vehicle_type, booked_status) 
          VALUES (?, ?, ?, ?, ?, 'Pending')";

$stmt = $conn->prepare($query);
if (!$stmt) {
    echo json_encode(["success" => false, "message" => "SQL Error: " . $conn->error]);
    exit;
}

$stmt->bind_param("iisss", $parking_owner_id, $vehicle_owner_id, $entry_time, $exit_time, $vehicle_type);

if ($stmt->execute()) {
    $booking_id = $stmt->insert_id; // ✅ Get the new booking ID

    // ✅ Send JSON response with booking_id (so JavaScript can use it)
    echo json_encode(["success" => true, "booking_id" => $booking_id, "amount" => 50]); // Example amount
} else {
    echo json_encode(["success" => false, "message" => "Booking failed: " . $stmt->error]);
}

// ✅ Close connections
$stmt->close();
$conn->close();
?>
