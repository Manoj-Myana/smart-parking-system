<?php
include 'db_connect.php';
header('Content-Type: application/json');

// ✅ Read raw JSON input
$json_input = file_get_contents("php://input");
error_log("📥 Raw JSON Received: " . $json_input);

// ✅ Decode JSON
$data = json_decode($json_input, true);
error_log("📥 Decoded JSON: " . json_encode($data));

$booking_id = $data["booking_id"] ?? null;
$slot_number = $data["slot_number"] ?? null;

// ✅ Log extracted values
error_log("🔎 Extracted booking_id: " . json_encode($booking_id));
error_log("🔎 Extracted slot_number: " . json_encode($slot_number));

// 🚨 If missing booking ID, return error
if (!$booking_id || !$slot_number) {
    $response = ["success" => false, "message" => "❌ Missing booking ID or slot number."];
    error_log("❌ Response Sent: " . json_encode($response));
    echo json_encode($response);
    exit();
}

// ✅ Generate QR Code JSON
$qr_data = json_encode([
    "booking_id" => $booking_id,
    "slot_number" => $slot_number
], JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);

// ✅ Generate QR Code Image URL
$qr_code_url = "https://api.qrserver.com/v1/create-qr-code/?size=300x300&data=" . urlencode($qr_data);

// ✅ Update Database with QR Code
$sql_update = "UPDATE parking_bookings SET qr_code = ? WHERE booking_id = ?";
$stmt_update = $conn->prepare($sql_update);
$stmt_update->bind_param("si", $qr_code_url, $booking_id);

if ($stmt_update->execute()) {
    $response = ["success" => true, "qr_code" => $qr_code_url];
    error_log("✅ Response Sent: " . json_encode($response));
    echo json_encode($response);
} else {
    $response = ["success" => false, "message" => "❌ Database update failed."];
    error_log("❌ Response Sent: " . json_encode($response));
    echo json_encode($response);
}

$stmt_update->close();
$conn->close();
?>
