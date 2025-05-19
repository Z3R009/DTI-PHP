<?php
// Include the database connection
include 'DBConnection.php';

// Set content type to JSON
header('Content-Type: application/json');

// Check if database connection exists
if (!isset($connection) || !$connection) {
    http_response_code(500);
    echo json_encode([
        'error' => true,
        'message' => 'Database connection failed'
    ]);
    exit;
}

try {
    // Query to fetch all payees, ordered by name
    $query = "SELECT DISTINCT payee_id, payee_name, bank_acc_no, tin_no, address, nature, contact_no, payee_type 
              FROM payee 
              WHERE payee_name IS NOT NULL 
              AND payee_name != '' 
              AND payee_name != 'DEPARTMENT OF TRADE AND INDUSTRY XII'
              ORDER BY payee_name ASC";
              
    $result = $connection->query($query);
    
    if ($result === false) {
        throw new Exception("Database query error: " . $connection->error);
    }
    
    // Fetch all results into an array
    $payees = [];
    while ($row = $result->fetch_assoc()) {
        // Sanitize the output for JSON
        foreach ($row as $key => $value) {
            $row[$key] = htmlspecialchars($value ?? '', ENT_QUOTES, 'UTF-8');
        }
        $payees[] = $row;
    }
    
    // Add debugging information
    error_log("Number of payees found: " . count($payees));
    
    // Return the payees as JSON
    echo json_encode($payees);
    
} catch (Exception $e) {
    // Log the error
    error_log("Error in get_payees.php: " . $e->getMessage());
    
    // Return error message
    http_response_code(500);
    echo json_encode([
        'error' => true,
        'message' => 'An error occurred while fetching payee data'
    ]);
} finally {
    // Close the connection if it exists
    if (isset($connection) && $connection) {
        $connection->close();
    }
}
?> 