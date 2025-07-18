<?php
header("Content-Type: application/json");
$conn = new mysqli("localhost", "root", "", "smart_parking");

if ($conn->connect_error) {
    echo json_encode(["success" => false, "message" => "DB connection failed"]);
    exit;
}

$data = json_decode(file_get_contents("php://input"), true);

$required = ['vehicle_owner_id', 'parking_owner_id', 'entry_time', 'exit_time', 'vehicle_type'];
foreach ($required as $field) {
    if (empty($data[$field])) {
        echo json_encode(["success" => false, "message" => "Missing field: $field"]);
        exit;
    }
}

$vehicle_owner_id = $data['vehicle_owner_id'];
$parking_owner_id = $data['parking_owner_id'];
$entry_time = $data['entry_time'];
$exit_time = $data['exit_time'];
$vehicle_type = $data['vehicle_type'];

// insert booking
$stmt = $conn->prepare("INSERT INTO parking_bookings (vehicle_owner_id, parking_owner_id, entry_time, exit_time, vehicle_type, booked_status) VALUES (?, ?, ?, ?, ?, 1)");
$stmt->bind_param("iisss", $vehicle_owner_id, $parking_owner_id, $entry_time, $exit_time, $vehicle_type);

if ($stmt->execute()) {
    echo json_encode(["success" => true, "booking_id" => $stmt->insert_id]);
} else {
    echo json_encode(["success" => false, "message" => "Failed to create booking"]);
}
?>
