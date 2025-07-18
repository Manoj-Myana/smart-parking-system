<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
header('Content-Type: application/json');

$conn = new mysqli("localhost", "root", "", "smart_parking");

if ($conn->connect_error) {
    echo json_encode(["success" => false, "message" => "DB connection failed."]);
    exit;
}

$data = json_decode(file_get_contents("php://input"), true);
$slot_number = $data['slot_id'] ?? '';

if (!$slot_number) {
    echo json_encode(["success" => false, "message" => "Missing slot number."]);
    exit;
}

// Get current is_parked status
$getQuery = $conn->prepare("SELECT is_parked FROM parking_slots WHERE slot_number = ?");
$getQuery->bind_param("s", $slot_number);
$getQuery->execute();
$getResult = $getQuery->get_result()->fetch_assoc();

if (!$getResult) {
    echo json_encode(["success" => false, "message" => "Slot not found."]);
    exit;
}

$newStatus = $getResult['is_parked'] == 0 ? 1 : 0;

$update = $conn->prepare("UPDATE parking_slots SET is_parked = ? WHERE slot_number = ?");
$update->bind_param("is", $newStatus, $slot_number);

if ($update->execute()) {
    echo json_encode(["success" => true, "message" => $newStatus == 1 ? "Vehicle parked." : "Vehicle exited."]);
} else {
    echo json_encode(["success" => false, "message" => "Update failed."]);
}

$conn->close();
?>
