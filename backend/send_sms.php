<?php
require '../vendor/autoload.php';
use Twilio\Rest\Client;

function sendBookingSMS($phone_number, $name, $vehicle_number, $parking_id, $entry_time, $exit_time) {
    $sid = "your-twilio-sid";
    $token = "your-twilio-auth-token";
    $twilio_number = "your-twilio-phone-number";
    
    $client = new Client($sid, $token);
    
    $message = "Hello $name, Your parking slot is booked. Vehicle: $vehicle_number, Parking ID: $parking_id, Entry: $entry_time, Exit: $exit_time.";
    
    try {
        $client->messages->create(
            $phone_number,
            ["from" => $twilio_number, "body" => $message]
        );
        return true;
    } catch (Exception $e) {
        return false;
    }
}
?>
