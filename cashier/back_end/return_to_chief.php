<?php
// Process the return to Chief Accountant functionality
require_once 'db_connection.php';
require_once 'utils.php';

// Initialize response array
$response = [
    'success' => false,
    'message' => '',
    'redirect' => ''
];

// Check if the request is POST and the action is to return to chief
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Get the submitted data and sanitize
    $dv_id = isset($_POST['dv_id']) ? sanitizeInput($_POST['dv_id']) : null;
    $return_remarks = isset($_POST['return_remarks']) ? sanitizeInput($_POST['return_remarks']) : null;
    
    // Validate input
    if (!$dv_id || !$return_remarks) {
        $response['message'] = "Missing required fields.";
    } else {
        // Check DV status before updating
        $current_status = getDvStatus($dv_id);
        
        if ($current_status != 'Endorsed' && $current_status != 'Processed') {
            $response['message'] = "This DV cannot be returned as it is already in " . $current_status . " status.";
        } else {
            // Update DV status to indicate it's returned
            $update_query = "UPDATE dv SET 
                            status = 'Returned', 
                            return_remarks = ?, 
                            return_date = NOW()
                            WHERE dv_id = ?";
            
            $stmt = $connection->prepare($update_query);
            $stmt->bind_param("si", $return_remarks, $dv_id);
            
            if ($stmt->execute()) {
                // Log the action
                logAction('DV Returned', "DV ID: $dv_id returned to Chief Accountant.", null);
                
                $response['success'] = true;
                $response['message'] = "DV has been returned to Chief Accountant successfully!";
                $response['redirect'] = "../pending_payments.php?success=2";
            } else {
                $response['message'] = "Error returning DV: " . $connection->error;
            }
        }
    }
    
    // Return JSON response if it's an AJAX request
    if (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest') {
        header('Content-Type: application/json');
        echo json_encode($response);
        exit;
    } else {
        // Handle normal form submission
        if ($response['success']) {
            header('Location: ' . $response['redirect']);
            exit;
        } else {
            // Redirect back with error
            header('Location: ../pending_payments.php?error=' . urlencode($response['message']));
            exit;
        }
    }
} 