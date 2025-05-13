<?php
// Include the database connection
include 'DBConnection.php';

// Set content type to JSON
header('Content-Type: application/json');

try {
    // Query to fetch all payees
    $query = "SELECT payee_id, payee_name, bank_acc_no, tin_no, address, nature, contact_no, payee_type FROM payee ORDER BY payee_name ASC";
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
    
    // Return the payees as JSON
    echo json_encode($payees);
    
} catch (Exception $e) {
    // Return error message
    echo json_encode([
        'error' => true,
        'message' => $e->getMessage()
    ]);
}

// Close the connection
$connection->close();
?> 