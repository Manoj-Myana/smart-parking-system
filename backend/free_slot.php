<?php
include '../database/db_config.php';

$data = json_decode(file_get_contents("php://input"), true);
$slot_id = $data['slot_id'];

$query = "UPDATE parking_slots SET status = 'Available', vehicle_number = NULL WHERE slot_id = ?";
$stmt = $conn->prepare($query);
$stmt->bind_param("i", $slot_id);

if ($stmt->execute()) {
    echo json_encode(["status" => "success", "message" => "Slot freed successfully!"]);
} else {
    echo json_encode(["status" => "error", "message" => "Failed to free the slot!"]);
}
?>
