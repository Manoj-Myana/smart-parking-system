<?php
include 'db_connect.php';

$booking_id = $_GET['booking_id'] ?? null;

if (!$booking_id) {
    echo json_encode(["success" => false, "message" => "❌ Missing booking ID."]);
    exit();
}

// ✅ Fetch Booking Details
$sql = "SELECT 
            pb.booking_id, pb.slot_number, pb.entry_time, pb.exit_time, pb.qr_code, 
            po.owner_name AS parking_name, po.area, 
            vo.vehicle_number, 
            pb.parking_owner_id
        FROM parking_bookings pb
        JOIN parking_owners po ON pb.parking_owner_id = po.owner_id
        JOIN vehicle_owners vo ON pb.vehicle_owner_id = vo.owner_id
        WHERE pb.booking_id = ?";

$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $booking_id);
$stmt->execute();
$result = $stmt->get_result();
$data = $result->fetch_assoc();

if (!$data) {
    echo json_encode(["success" => false, "message" => "❌ Booking details not found."]);
} else {
    echo json_encode(["success" => true, "data" => $data]);
}

$stmt->close();
$conn->close();
?>
