<?php
session_start(); // To access the session

header("Content-Type: application/json");

// Connect to the database
$conn = new mysqli("localhost", "root", "", "smart_parking");
if ($conn->connect_error) {
    echo json_encode(["success" => false, "message" => "DB connection failed"]);
    exit;
}

// Get owner_id from session
if (!isset($_SESSION['owner_id'])) {
    echo json_encode(["success" => false, "message" => "Owner not logged in"]);
    exit;
}

$owner_id = $_SESSION['owner_id'];

// Step 1: Get total car & bike slots from parking_owners table
$owner_sql = "SELECT total_car_slots, total_bike_slots FROM parking_owners WHERE owner_id = ?";
$stmt = $conn->prepare($owner_sql);
$stmt->bind_param("i", $owner_id);
$stmt->execute();
$result = $stmt->get_result();
if ($result->num_rows === 0) {
    echo json_encode(["success" => false, "message" => "Parking owner not found"]);
    exit;
}
$owner_data = $result->fetch_assoc();

$total_car_slots = (int)$owner_data['total_car_slots'];
$total_bike_slots = (int)$owner_data['total_bike_slots'];

// Step 2: Count how many cars and bikes are currently parked
$parked_sql = "
    SELECT 
        SUM(vehicle_type = 'car' AND is_parked = 1) AS parked_cars,
        SUM(vehicle_type = 'bike' AND is_parked = 1) AS parked_bikes
    FROM parking_slots
    WHERE parking_owner_id = ?
";

$stmt2 = $conn->prepare($parked_sql);
$stmt2->bind_param("i", $owner_id);
$stmt2->execute();
$parked_result = $stmt2->get_result();
$parked_data = $parked_result->fetch_assoc();

$available_car_slots = $total_car_slots - (int)$parked_data['parked_cars'];
$available_bike_slots = $total_bike_slots - (int)$parked_data['parked_bikes'];

// Send data back
echo json_encode([
    "success" => true,
    "data" => [
        "total_car_slots" => $total_car_slots,
        "total_bike_slots" => $total_bike_slots,
        "available_car_slots" => $available_car_slots,
        "available_bike_slots" => $available_bike_slots
    ]
]);
?>
