<?php
// Include database connection
require_once 'db_connection.php';

// Check if the request method is POST and if merge_id is provided
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['merge_id'])) {
    $merge_id = intval($_POST['merge_id']);
    
    // Validate the merge_id
    if ($merge_id <= 0) {
        $response = [
            'success' => false,
            'message' => 'Invalid merge ID provided.'
        ];
    } else {
        // Start a transaction
        $connection->begin_transaction();
        
        try {
            // Check if the merged payee exists
            $check_query = "SELECT merge_id, merge_name FROM merged_payees WHERE merge_id = ?";
            $check_stmt = $connection->prepare($check_query);
            $check_stmt->bind_param("i", $merge_id);
            $check_stmt->execute();
            $result = $check_stmt->get_result();
            
            if ($result->num_rows === 0) {
                throw new Exception("Merged payee with ID $merge_id not found.");
            }
            
            $row = $result->fetch_assoc();
            $merge_name = $row['merge_name'];
            
            // Reset the processed status
            $update_query = "UPDATE merged_payees SET processed = 0 WHERE merge_id = ?";
            $update_stmt = $connection->prepare($update_query);
            $update_stmt->bind_param("i", $merge_id);
            $update_stmt->execute();
            
            // Commit the transaction
            $connection->commit();
            
            $response = [
                'success' => true,
                'message' => "Merged payee '$merge_name' has been reset and is now visible in the pending payments list."
            ];
        } catch (Exception $e) {
            // Rollback the transaction on error
            $connection->rollback();
            
            $response = [
                'success' => false,
                'message' => 'Error: ' . $e->getMessage()
            ];
        }
    }
    
    // Set content type to JSON
    header('Content-Type: application/json');
    echo json_encode($response);
    exit;
} else {
    // If accessed directly or without merge_id
    $response = [
        'success' => false,
        'message' => 'Invalid request. Please provide a merge_id parameter via POST.'
    ];
    
    // Set content type to JSON
    header('Content-Type: application/json');
    echo json_encode($response);
    exit;
}
?> 