<?php
ini_set('display_startup_errors', 1);
ini_set('display_errors', 1);
error_reporting(E_ALL);

include 'db_connect.php';
header('Content-Type: application/json');

// Read and decode incoming JSON
$inputJSON = file_get_contents("php://input");
$input = json_decode($inputJSON, true);

// Debug: log incoming request
error_log("📥 Received Data in fetch_booking_details.php: " . json_encode($input));

// Validate booking_id
$booking_id = $input['booking_id'] ?? null;

if (!$booking_id) {
    echo json_encode(["success" => false, "message" => "❌ Missing booking ID."]);
    exit();
}

// ✅ Correct SQL query (removed duplicate SELECT)
$sql = "SELECT 
            b.booking_id, 
            v.name AS vehicle_owner_name, 
            v.vehicle_number, 
            v.license_number, 
            b.slot_number, 
            b.entry_time, 
            b.exit_time, 
            s.is_parked,
            b.vehicle_type,
            b.parking_owner_id
        FROM parking_bookings b
        JOIN vehicle_owners v ON b.vehicle_owner_id = v.owner_id
        JOIN parking_slots s ON b.slot_number = s.slot_number AND b.parking_owner_id = s.parking_owner_id
        WHERE b.booking_id = ?";

$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $booking_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    echo json_encode(["success" => false, "message" => "❌ Booking details not found."]);
    exit();
}

$row = $result->fetch_assoc();

// Debug logs (optional)
error_log("🔎 Slot Number Retrieved: " . $row['slot_number']);
error_log("🚗 is_parked Status: " . $row['is_parked']);
error_log("🏢 parking_owner_id: " . $row['parking_owner_id']);

// ✅ Final JSON Response
echo json_encode([
    "success" => true, 
    "booking_id" => $row['booking_id'], 
    "vehicle_owner_name" => $row['vehicle_owner_name'], 
    "vehicle_number" => $row['vehicle_number'], 
    "license_number" => $row['license_number'], 
    "slot_number" => $row['slot_number'],  
    "entry_time" => $row['entry_time'], 
    "exit_time" => $row['exit_time'],
    "is_parked" => $row['is_parked'],
    "parking_owner_id" => $row['parking_owner_id'],
    "vehicle_type" => $row['vehicle_type']  // ✅ Fixed missing comma above
]);

$stmt->close();
$conn->close();
?>
