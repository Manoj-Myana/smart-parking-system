<?php
session_start(); // ✅ Start session

error_reporting(E_ALL);
ini_set('display_errors', 1);
header("Content-Type: application/json");

// Database connection
$conn = new mysqli("localhost", "root", "", "smart_parking");
if ($conn->connect_error) {
    echo json_encode(["success" => false, "message" => "❌ Database connection failed: " . $conn->connect_error]);
    exit;
}

// Read input JSON
$input = file_get_contents("php://input");
$data = json_decode($input, true);

if (!$data) {
    echo json_encode(["success" => false, "message" => "❌ Invalid JSON input"]);
    exit;
}

if (!isset($data["email"], $data["password"])) {
    echo json_encode(["success" => false, "message" => "❌ Missing email or password"]);
    exit;
}

$email = $conn->real_escape_string($data["email"]);
$password = $data["password"];

// ✅ Check vehicle owner login
$query_vehicle = "SELECT owner_id, name, vehicle_type, vehicle_number, license_number, email, password FROM vehicle_owners WHERE email = ?";
$stmt = $conn->prepare($query_vehicle);
$stmt->bind_param("s", $email);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows > 0) {
    $user = $result->fetch_assoc();

    if (!password_verify($password, $user["password"])) {
        echo json_encode(["success" => false, "message" => "❌ Incorrect password"]);
        exit;
    }

    echo json_encode([
        "success" => true,
        "message" => "✅ Login successful",
        "userType" => "vehicle_owner",
        "user" => [
            "id" => $user["owner_id"],
            "name" => $user["name"],
            "vehicleType" => $user["vehicle_type"],
            "vehicleNumber" => $user["vehicle_number"],
            "licenseNumber" => $user["license_number"],
            "email" => $user["email"]
        ]
    ]);
    exit;
}

// ✅ Check parking owner login
$query_parking = "SELECT owner_id, parking_name, owner_name, email, password FROM parking_owners WHERE email = ?";
$stmt = $conn->prepare($query_parking);
$stmt->bind_param("s", $email);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows > 0) {
    $user = $result->fetch_assoc();

    if (!password_verify($password, $user["password"])) {
        echo json_encode(["success" => false, "message" => "❌ Incorrect password"]);
        exit;
    }

    $_SESSION["owner_id"] = $user["owner_id"]; // Store parking owner ID in session

    echo json_encode([
        "success" => true,
        "message" => "✅ Login successful",
        "userType" => "parking_owner",
        "user" => [
            "id" => $user["owner_id"],
            "parkingName" => $user["parking_name"],
            "ownerName" => $user["owner_name"],
            "email" => $user["email"]
        ]
    ]);
    exit;
}

// ❌ No user found
echo json_encode(["success" => false, "message" => "❌ User not found"]);
exit;
?>
