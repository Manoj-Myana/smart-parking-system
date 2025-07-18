<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

$conn = new mysqli("localhost", "root", "", "smart_parking");

if ($conn->connect_error) {
    die("Database connection failed: " . $conn->connect_error);
}

// ✅ Fetch all parking owners
$ownersQuery = "SELECT DISTINCT parking_owner_id FROM parking_bookings";
$ownersResult = $conn->query($ownersQuery);

if ($ownersResult->num_rows == 0) {
    die("⚠️ No parking owners found in the system.");
}

// ✅ Define slot counts
$car_slots = 10;  // Number of car slots per owner
$bike_slots = 5;  // Number of bike slots per owner

while ($row = $ownersResult->fetch_assoc()) {
    $parking_owner_id = $row['parking_owner_id'];

    // ✅ Check if slots already exist
    $checkQuery = "SELECT COUNT(*) as count FROM parking_slots WHERE parking_owner_id = $parking_owner_id";
    $checkResult = $conn->query($checkQuery);
    $existingSlots = $checkResult->fetch_assoc()['count'];

    if ($existingSlots > 0) {
        echo "⚠️ Slots already exist for Parking Owner ID: $parking_owner_id <br>";
        continue;  // Skip to the next owner
    }

    // ✅ Insert Car slots
    for ($i = 1; $i <= $car_slots; $i++) {
        $slot_number = $i;  // Ensure unique slot numbers for this parking owner
        $conn->query("INSERT INTO parking_slots (parking_owner_id, slot_number, vehicle_type, is_occupied) 
                      VALUES ($parking_owner_id, $slot_number, 'Car', 0)");
    }

    // ✅ Insert Bike slots (continue numbering uniquely per owner)
    for ($i = 1; $i <= $bike_slots; $i++) {
        $slot_number = $car_slots + $i;  // Ensure slot_number is unique within this owner
        $conn->query("INSERT INTO parking_slots (parking_owner_id, slot_number, vehicle_type, is_occupied) 
                      VALUES ($parking_owner_id, $slot_number, 'Bike', 0)");
    }

    echo "✅ Added $car_slots Car slots & $bike_slots Bike slots for Parking Owner ID: $parking_owner_id <br>";
}

// ✅ Close connection
$conn->close();
?>
