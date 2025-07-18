<?php
include '../database/db_config.php';

$data = json_decode(file_get_contents("php://input"), true);
$bookingId = $data['bookingId'];

$query = "UPDATE bookings SET fine_paid = 1 WHERE id = ?";
$stmt = $conn->prepare($query);
$stmt->bind_param("i", $bookingId);
if ($stmt->execute()) {
    echo json_encode(["status" => "success"]);
} else {
    echo json_encode(["status" => "error"]);
}
?>
