<?php
include '../database/db_config.php';

$data = json_decode(file_get_contents("php://input"), true);
$qr_data = $data['qr_data'];

$query = "SELECT * FROM bookings WHERE qr_code_data = ?";
$stmt = $conn->prepare($query);
$stmt->bind_param("s", $qr_data);
$stmt->execute();
$result = $stmt->get_result();
$booking = $result->fetch_assoc();

if (!$booking) {
    echo json_encode(["status" => "error", "message" => "Invalid QR Code!"]);
    exit();
}

$current_time = strtotime("now");
$entry_time = strtotime($booking['entry_time']);
$exit_time = strtotime($booking['exit_time']);

if ($current_time >= $entry_time && $current_time <= $exit_time) {
    echo json_encode(["status" => "entry", "message" => "Valid Entry. Vehicle can enter."]);
} elseif ($current_time > $exit_time) {
    $extra_hours = ceil(($current_time - $exit_time) / 3600);
    $fine = $extra_hours * 70;
    echo json_encode(["status" => "exit", "fine" => $fine, "booking_id" => $booking['id'], "message" => "Overstay detected!"]);
} else {
    echo json_encode(["status" => "error", "message" => "Invalid Time for Entry/Exit!"]);
}
?>
