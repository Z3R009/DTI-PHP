<?php
include '../DBConnection.php';

header('Content-Type: application/json');

// Enable error reporting for debugging
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Get fund_cluster_id and oopap_id from the request
$fund_cluster_id = isset($_GET['fund_cluster_id']) ? (int)$_GET['fund_cluster_id'] : 0;
$oopap_id = isset($_GET['oopap_id']) ? (int)$_GET['oopap_id'] : 0;

// Validate inputs
if (!$fund_cluster_id || !$oopap_id) {
    echo json_encode([]);
    exit;
}

try {
    // First, check if oopap_id is a column in the services table
    $checkColumn = "SHOW COLUMNS FROM services LIKE 'oopap_id'";
    $checkResult = $connection->query($checkColumn);
    $oopapColumnExists = $checkResult && $checkResult->num_rows > 0;
    
    if ($oopapColumnExists) {
        // If oopap_id exists in services, use direct query
        $query = "SELECT services_id, services_name, code 
                  FROM services 
                  WHERE oopap_id = ?
                  ORDER BY services_name";
        
        $stmt = $connection->prepare($query);
        $stmt->bind_param("i", $oopap_id);
    } else {
        // If oopap_id doesn't exist in services, try through project table
        $query = "SELECT DISTINCT s.services_id, s.services_name, s.code 
                  FROM services s
                  LEFT JOIN project p ON s.services_id = p.services_id
                  WHERE p.oopap_id = ?
                  ORDER BY s.services_name";
        
        $stmt = $connection->prepare($query);
        $stmt->bind_param("i", $oopap_id);
    }
    
    if (!$stmt) {
        throw new Exception("Prepare failed: " . $connection->error);
    }
    
    $stmt->execute();
    $result = $stmt->get_result();
    
    $services = [];
    while ($row = $result->fetch_assoc()) {
        $services[] = [
            'services_id' => $row['services_id'],
            'services_name' => $row['services_name'],
            'code' => $row['code']
        ];
    }
    
    echo json_encode($services);
    
} catch (Exception $e) {
    // Log error
    error_log("Error in get_services.php: " . $e->getMessage());
    
    // Return a more informative error message
    echo json_encode([
        'error' => 'Failed to load services: ' . $e->getMessage()
    ]);
} finally {
    // Close the connection
    if (isset($stmt)) {
        $stmt->close();
    }
} 