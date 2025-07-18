-- Create the database if it doesn't exist
CREATE DATABASE IF NOT EXISTS smart_parking;
USE smart_parking;

-- Table for Vehicle Owners
CREATE TABLE IF NOT EXISTS vehicle_owners (
    owner_id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(255) NOT NULL,
    vehicle_type ENUM('Car', 'Bike') NOT NULL,
    vehicle_number VARCHAR(20) NOT NULL UNIQUE,
    license_number VARCHAR(20) NOT NULL UNIQUE,
    email VARCHAR(255) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL
);

-- Table for Parking Owners
CREATE TABLE IF NOT EXISTS parking_owners (
    owner_id INT AUTO_INCREMENT PRIMARY KEY,
    parking_name VARCHAR(255) NOT NULL,
    area VARCHAR(255) NOT NULL,
    city VARCHAR(100) NOT NULL,
    pincode VARCHAR(10) NOT NULL,
    owner_name VARCHAR(255) NOT NULL,
    email VARCHAR(255) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    total_car_slots INT NOT NULL,
    total_bike_slots INT NOT NULL
);

-- Table for Parking Slots
CREATE TABLE IF NOT EXISTS parking_slots (
    slot_id INT AUTO_INCREMENT PRIMARY KEY,
    parking_owner_id INT NOT NULL,
    slot_number VARCHAR(50) NOT NULL UNIQUE,
    vehicle_type ENUM('Car', 'Bike') NOT NULL,
    is_available BOOLEAN DEFAULT TRUE,
    FOREIGN KEY (parking_owner_id) REFERENCES parking_owners(owner_id) ON DELETE CASCADE
);

-- Table for Bookings
CREATE TABLE IF NOT EXISTS bookings (
    booking_id INT AUTO_INCREMENT PRIMARY KEY,
    vehicle_owner_id INT NOT NULL,
    parking_owner_id INT NOT NULL,
    slot_id INT NOT NULL,
    booking_time TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    status ENUM('Booked', 'Completed', 'Cancelled') DEFAULT 'Booked',
    FOREIGN KEY (vehicle_owner_id) REFERENCES vehicle_owners(owner_id) ON DELETE CASCADE,
    FOREIGN KEY (parking_owner_id) REFERENCES parking_owners(owner_id) ON DELETE CASCADE,
    FOREIGN KEY (slot_id) REFERENCES parking_slots(slot_id) ON DELETE CASCADE
);

-- Table for Payments (Optional)
CREATE TABLE IF NOT EXISTS payments (
    payment_id INT AUTO_INCREMENT PRIMARY KEY,
    booking_id INT NOT NULL,
    amount DECIMAL(10,2) NOT NULL,
    payment_status ENUM('Pending', 'Completed', 'Failed') DEFAULT 'Pending',
    payment_time TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (booking_id) REFERENCES bookings(booking_id) ON DELETE CASCADE
);
