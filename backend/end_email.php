<?php
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require '../vendor/autoload.php';  // Ensure PHPMailer is installed via Composer
include '../database/db_config.php';

function sendBookingEmail($to_email, $name, $vehicle_number, $parking_id, $entry_time, $exit_time, $qr_code) {
    $mail = new PHPMailer(true);
    
    try {
        // SMTP Configuration
        $mail->isSMTP();
        $mail->Host = 'smtp.gmail.com'; 
        $mail->SMTPAuth = true;
        $mail->Username = 'your-email@gmail.com';  // Your email
        $mail->Password = 'your-email-password';  // Your email password
        $mail->SMTPSecure = 'tls';
        $mail->Port = 587;

        // Email Content
        $mail->setFrom('your-email@gmail.com', 'Smart Parking System');
        $mail->addAddress($to_email);
        $mail->Subject = "Parking Slot Booking Confirmation";
        
        $message = "
        <h3>Dear $name,</h3>
        <p>Your parking slot has been successfully booked.</p>
        <p><strong>Vehicle Number:</strong> $vehicle_number</p>
        <p><strong>Parking ID:</strong> $parking_id</p>
        <p><strong>Entry Time:</strong> $entry_time</p>
        <p><strong>Exit Time:</strong> $exit_time</p>
        <p>Please show the following QR Code at the entry & exit:</p>
        <img src='$qr_code' alt='QR Code'>
        <p>Thank you for using Smart Parking System!</p>";

        $mail->isHTML(true);
        $mail->Body = $message;

        $mail->send();
        return true;
    } catch (Exception $e) {
        return false;
    }
}
?>
