<?php
header("Content-Type: application/json"); // ✅ Set response type to JSON
include 'db_connect.php'; // ✅ Ensure database connection

// ✅ Get POST data safely
$booking_id = $_POST['booking_id'] ?? null;
$vehicle_owner_name = $_POST['vehicle_owner_name'] ?? null;
$slot_number = $_POST['slot_number'] ?? null;
$parking_owner_id = $_POST['parking_owner_id'] ?? null;
$entry_time = $_POST['entry_time'] ?? null;

// ✅ Validate input
if (!$booking_id || !$vehicle_owner_name || !$slot_number || !$parking_owner_id || !$entry_time) {
    echo json_encode(["success" => false, "message" => "❌ Missing required fields"]);
    exit;
}

// ✅ Ensure entry_time is a valid string
$entry_time = strval($entry_time);

// ✅ Prepare booking data in JSON format
$booking_data = [
    "booking_id" => (int) $booking_id,
    "vehicle_owner_name" => $vehicle_owner_name,
    "parking_slot_number" => (int) $slot_number,
    "parking_owner_id" => (int) $parking_owner_id,
    "entry_time" => $entry_time
];

// ✅ Convert array to JSON safely
$qr_code_data = json_encode($booking_data, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);

// ✅ Store raw JSON in the database
$sql = "UPDATE parking_bookings SET qr_code = ? WHERE booking_id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("si", $qr_code_data, $booking_id);

if ($stmt->execute()) {
    echo json_encode(["success" => true, "message" => "✅ QR Code stored successfully", "qr_code_data" => $qr_code_data]);
} else {
    echo json_encode(["success" => false, "message" => "❌ Failed to store QR code"]);
}

// ✅ Close connection
$stmt->close();
$conn->close();
?>
