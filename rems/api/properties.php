<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');
require_once '../includes/config.php';
// requireLogin(); // Temporarily disabled for testing

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    exit(0);
}

$method = $_SERVER['REQUEST_METHOD'];

$method = $_SERVER['REQUEST_METHOD'];

$method = $_SERVER['REQUEST_METHOD'];

switch ($method) {
    case 'GET':
        $status = $_GET['status'] ?? null;
        $properties = getProperties($status);
        echo json_encode($properties);
        break;
    case 'POST':
        $data = json_decode(file_get_contents('php://input'), true);
        if (!$data) {
            http_response_code(400);
            echo json_encode(['error' => 'Invalid JSON']);
            exit;
        }
        
        $name = sanitize($data['name']);
        $address = sanitize($data['address']);
        $city = sanitize($data['city']);
        $state = sanitize($data['state']);
        $zip = sanitize($data['zip']);
        $type = $data['type'];
        $totalUnits = (int)$data['total_units'];
        $status = $data['status'];
        
        $stmt = db()->prepare("INSERT INTO properties (property_name, address, city, state, zip, property_type, total_units, status, owner_id) VALUES (:name, :address, :city, :state, :zip, :type, :total_units, :status, :owner_id)");
        $owner_id = $_SESSION['user_id'] ?? 1;
        $stmt->bindParam(':name', $name);
        $stmt->bindParam(':address', $address);
        $stmt->bindParam(':city', $city);
        $stmt->bindParam(':state', $state);
        $stmt->bindParam(':zip', $zip);
        $stmt->bindParam(':type', $type);
        $stmt->bindParam(':total_units', $totalUnits);
        $stmt->bindParam(':status', $status);
        $stmt->bindParam(':owner_id', $owner_id);
        
        if ($stmt->execute()) {
            $id = db()->lastInsertId();
            echo json_encode(['id' => $id, 'message' => 'Property added successfully']);
        } else {
            http_response_code(500);
            echo json_encode(['error' => 'Failed to add property']);
        }
        break;
    default:
        http_response_code(405);
        echo json_encode(['error' => 'Method not allowed']);
}
?>