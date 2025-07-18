<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
header("Content-Type: application/json");

// Database connection
mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT); // Better error reporting
$conn = new mysqli("localhost", "root", "", "smart_parking");

if ($conn->connect_error) {
    echo json_encode(["success" => false, "message" => "Database connection failed: " . $conn->connect_error]);
    exit;
}

// Read JSON input
$data = json_decode(file_get_contents("php://input"), true);

if (!$data) {
    echo json_encode(["success" => false, "message" => "Invalid JSON input"]);
    exit;
}

// Check required fields
if (!isset($data["userType"], $data["email"], $data["password"])) {
    echo json_encode(["success" => false, "message" => "Missing required fields"]);
    exit;
}

$email = $conn->real_escape_string($data["email"]);
$password = password_hash($data["password"], PASSWORD_DEFAULT);
$userType = $conn->real_escape_string($data["userType"]);

if ($userType === "vehicle_owner") {
    // ✅ Handle Vehicle Owner Registration
    $name = $conn->real_escape_string($data["name"]);
    $vehicleType = $conn->real_escape_string($data["vehicleType"]);
    $vehicleNumber = $conn->real_escape_string($data["vehicleNumber"]);
    $licenseNumber = $conn->real_escape_string($data["licenseNumber"]);

    // ✅ Check if vehicle number already exists
    $checkQuery = "SELECT vehicle_number FROM vehicle_owners WHERE vehicle_number = ?";
    $checkStmt = $conn->prepare($checkQuery);
    $checkStmt->bind_param("s", $vehicleNumber);
    $checkStmt->execute();
    $checkStmt->store_result();

    if ($checkStmt->num_rows > 0) {
        echo json_encode(["success" => false, "message" => "Vehicle number already registered!"]);
        exit;
    }
    $checkStmt->close();

    // ✅ Insert Vehicle Owner
    $query = "INSERT INTO vehicle_owners (name, vehicle_type, vehicle_number, license_number, email, password) 
              VALUES (?, ?, ?, ?, ?, ?)";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("ssssss", $name, $vehicleType, $vehicleNumber, $licenseNumber, $email, $password);

} elseif ($userType === "parking_owner") {
    // ✅ Handle Parking Owner Registration
    $parkingName = $conn->real_escape_string($data["parkingName"]);
    $area = $conn->real_escape_string($data["area"]);
    $city = $conn->real_escape_string($data["city"]);
    $pincode = $conn->real_escape_string($data["pincode"]);
    $ownerName = $conn->real_escape_string($data["ownerName"]);
    $totalCarSlots = intval($data["totalCars"]);
    $totalBikeSlots = intval($data["totalBikes"]);

    // ✅ Insert Parking Owner
    $query = "INSERT INTO parking_owners (parking_name, area, city, pincode, owner_name, email, password, total_car_slots, total_bike_slots) 
              VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("sssssssii", $parkingName, $area, $city, $pincode, $ownerName, $email, $password, $totalCarSlots, $totalBikeSlots);
} else {
    echo json_encode(["success" => false, "message" => "Invalid user type"]);
    exit;
}

// ✅ Execute query and return response
if ($stmt->execute()) {
    echo json_encode(["success" => true, "message" => "Registration successful"]);
} else {
    echo json_encode(["success" => false, "message" => "Registration failed: " . $stmt->error]);
}

// ✅ Close connections
$stmt->close();
$conn->close();
?>
