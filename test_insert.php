<?php
require_once 'rems/includes/config.php';

// Mock session
$_SESSION['user_id'] = 1;

$name = 'Test Property';
$address = '123 Test St';
$city = 'Test City';
$state = 'TS';
$zip = '12345';
$type = 'residential';
$totalUnits = 10;
$status = 'active';

$stmt = db()->prepare("INSERT INTO properties (property_name, address, city, state, zip, property_type, total_units, status, owner_id) VALUES (:name, :address, :city, :state, :zip, :type, :total_units, :status, :owner_id)");
$stmt->bindParam(':owner_id', $_SESSION['user_id']);
$stmt->bindParam(':name', $name);
$stmt->bindParam(':address', $address);
$stmt->bindParam(':city', $city);
$stmt->bindParam(':state', $state);
$stmt->bindParam(':zip', $zip);
$stmt->bindParam(':type', $type);
$stmt->bindParam(':total_units', $totalUnits);
$stmt->bindParam(':status', $status);

if ($stmt->execute()) {
    echo "Success!";
} else {
    echo "Failed";
}
