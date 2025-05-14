<?php
include '../DBConnection.php';

// Enable error reporting for debugging
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Set proper content type for JSON response
header('Content-Type: application/json');

// Log the request
error_log("get_filtered_services.php called with POST data: " . print_r($_POST, true));

if (isset($_POST['oopap_id'])) {
    $oopap_id = $_POST['oopap_id'];
    error_log("Processing request for oopap_id: " . $oopap_id);
    
    // Validate that oopap_id is a valid integer
    if (!is_numeric($oopap_id) || $oopap_id <= 0) {
        error_log("Invalid oopap_id provided: " . $oopap_id);
        echo json_encode(['error' => 'Invalid oopap_id provided']);
        exit;
    }
    
    $sql = "SELECT services_id, services_name, code 
            FROM services 
            WHERE oopap_id = ?
            ORDER BY services_name";
    
    $stmt = $connection->prepare($sql);
    if (!$stmt) {
        error_log("Prepare failed: " . $connection->error);
        echo json_encode(['error' => 'Database prepare error: ' . $connection->error]);
        exit;
    }
    
    $stmt->bind_param("i", $oopap_id);
    if (!$stmt->execute()) {
        error_log("Execute failed: " . $stmt->error);
        echo json_encode(['error' => 'Database execute error: ' . $stmt->error]);
        exit;
    }
    
    $result = $stmt->get_result();
    
    $services = array();
    while ($row = $result->fetch_assoc()) {
        $services[] = array(
            'services_id' => $row['services_id'],
            'services_name' => $row['services_name'],
            'code' => $row['code']
        );
    }
    
    error_log("Found " . count($services) . " services for oopap_id: " . $oopap_id);
    
    // Always return a JSON array, even if empty
    echo json_encode($services);
    
    $stmt->close();
    $connection->close();
} else {
    error_log("No oopap_id provided in request");
    echo json_encode([]); // Return empty array instead of error
}
?> 