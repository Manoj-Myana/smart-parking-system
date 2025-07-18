<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
header('Content-Type: application/json');

$conn = new mysqli("localhost", "root", "", "smart_parking");

if ($conn->connect_error) {
    echo json_encode(["success" => false, "message" => "❌ Database connection failed."]);
    exit;
}

// ✅ Read request data
$data = json_decode(file_get_contents("php://input"), true);
$booking_id = isset($data["booking_id"]) ? (int) $data["booking_id"] : null;
$parking_owner_id = isset($data["parking_owner_id"]) ? (int) $data["parking_owner_id"] : null;

// 🚨 Debugging Log
error_log("Received booking_id: " . $booking_id);
error_log("Received parking_owner_id: " . $parking_owner_id);

// 🚨 Check for missing values
if (!$booking_id || !$parking_owner_id) {
    echo json_encode(["success" => false, "message" => "❌ Invalid slot allocation request."]);
    exit;
}

// ✅ Fetch Booking Details
$bookingQuery = "SELECT pb.vehicle_owner_id, pb.entry_time, pb.exit_time, pb.vehicle_type, po.owner_name
                 FROM parking_bookings pb
                 JOIN parking_owners po ON pb.parking_owner_id = po.owner_id
                 WHERE pb.booking_id = ?";
$stmt = $conn->prepare($bookingQuery);
$stmt->bind_param("i", $booking_id);
$stmt->execute();
$result = $stmt->get_result();
$booking = $result->fetch_assoc();

if (!$booking) {
    echo json_encode(["success" => false, "message" => "❌ Booking not found."]);
    exit;
}

$vehicle_type = $booking["vehicle_type"];

// ✅ Get the first available slot
$query = "SELECT slot_id, slot_number FROM parking_slots 
          WHERE parking_owner_id = ? AND vehicle_type = ? AND is_occupied = 0 
          ORDER BY slot_number ASC LIMIT 1";
$stmt = $conn->prepare($query);
$stmt->bind_param("is", $parking_owner_id, $vehicle_type);
$stmt->execute();
$result = $stmt->get_result();
$slot = $result->fetch_assoc();

if (!$slot) {
    echo json_encode(["success" => false, "message" => "❌ No available slots. Payment will be refunded."]);
    exit;
}

$slot_id = $slot["slot_id"];
$slot_number = $slot["slot_number"];

// ✅ Update slot status to occupied
$updateSlotQuery = "UPDATE parking_slots SET is_occupied = 1 WHERE slot_id = ?";
$updateSlotStmt = $conn->prepare($updateSlotQuery);
$updateSlotStmt->bind_param("i", $slot_id);
$updateSlotStmt->execute();

// ✅ Update booking with allocated slot
$updateBookingQuery = "UPDATE parking_bookings SET slot_number = ?, booked_status = 'Confirmed' WHERE booking_id = ?";
$updateBookingStmt = $conn->prepare($updateBookingQuery);
$updateBookingStmt->bind_param("ii", $slot_number, $booking_id);
$updateBookingStmt->execute();

// ✅ Send response
echo json_encode([
    "success" => true,
    "message" => "✅ Slot allocated successfully.",
    "slot_number" => $slot_number,
    "vehicle_owner_name" => $booking["owner_name"],
    "parking_owner_id" => $parking_owner_id
]);

$stmt->close();
$updateSlotStmt->close();
$updateBookingStmt->close();
$conn->close();
?>

