<?php
include '../database/db_config.php';
include 'send_email.php';
include 'send_sms.php';

$data = json_decode(file_get_contents("php://input"), true);

$name = $data['name'];
$email = $data['email'];
$phone = $data['phone'];
$vehicle_number = $data['vehicle_number'];
$parking_id = $data['parking_id'];
$entry_time = $data['entry_time'];
$exit_time = $data['exit_time'];
$qr_code = $data['qr_code'];  // QR code image URL

// Insert booking into database
$query = "INSERT INTO bookings (name, email, phone, vehicle_number, parking_id, entry_time, exit_time, qr_code) VALUES (?, ?, ?, ?, ?, ?, ?, ?)";
$stmt = $conn->prepare($query);
$stmt->bind_param("ssssssss", $name, $email, $phone, $vehicle_number, $parking_id, $entry_time, $exit_time, $qr_code);

if ($stmt->execute()) {
    // Send Email & SMS
    sendBookingEmail($email, $name, $vehicle_number, $parking_id, $entry_time, $exit_time, $qr_code);
    sendBookingSMS($phone, $name, $vehicle_number, $parking_id, $entry_time, $exit_time);
    
    echo json_encode(["status" => "success", "message" => "Booking confirmed! Email & SMS sent."]);
} else {
    echo json_encode(["status" => "error", "message" => "Booking failed!"]);
}
?>
