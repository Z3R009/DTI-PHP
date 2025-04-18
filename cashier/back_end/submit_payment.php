<?php
// Process individual payment submission
require_once 'db_connection.php';
require_once 'utils.php';

// Initialize response array
$response = [
    'success' => false,
    'message' => '',
    'redirect' => ''
];

// Check if the request is POST and the action is to submit payment
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Get form data and sanitize
    $dv_id = isset($_POST['dv_id']) ? sanitizeInput($_POST['dv_id']) : null;
    $payment_type = isset($_POST['payment_type']) ? sanitizeInput($_POST['payment_type']) : null;
    $reference_no = isset($_POST['reference_no']) ? sanitizeInput($_POST['reference_no']) : null;
    $payment_date = isset($_POST['payment_date']) ? sanitizeInput($_POST['payment_date']) : null;
    $amount = isset($_POST['amount']) ? floatval($_POST['amount']) : null;
    $remarks = isset($_POST['remarks']) ? sanitizeInput($_POST['remarks']) : '';
    
    // Validate required fields
    if (!$dv_id || !$payment_type || !$reference_no || !$payment_date || !$amount) {
        $response['message'] = "Missing required fields.";
    } else {
        // Begin transaction
        $connection->begin_transaction();
        
        try {
            // Check if DV is available for payment
            if (dvHasPayments($dv_id)) {
                throw new Exception("This DV already has a payment record.");
            }
            
            // Insert payment record
            $insert_query = "INSERT INTO payment (dv_id, payment_date, payment_type, reference_no, amount, remarks, created_by, status) 
                            VALUES (?, ?, ?, ?, ?, ?, 'Cashier', 'Pending')";
            
            $stmt = $connection->prepare($insert_query);
            $stmt->bind_param("isssds", $dv_id, $payment_date, $payment_type, $reference_no, $amount, $remarks);
            
            if ($stmt->execute()) {
                // Update DV status to 'Processing'
                $update_dv = "UPDATE dv SET status = 'Processing' WHERE dv_id = ?";
                $update_stmt = $connection->prepare($update_dv);
                $update_stmt->bind_param("i", $dv_id);
                $update_stmt->execute();
                
                // Log the action
                logAction('Payment Recorded', "Payment of " . formatCurrency($amount) . " recorded for DV ID: $dv_id", null);
                
                // Commit transaction
                $connection->commit();
                
                $response['success'] = true;
                $response['message'] = "Payment has been recorded successfully!";
                $response['redirect'] = "../pending_payments.php?success=1";
            } else {
                throw new Exception("Error recording payment: " . $connection->error);
            }
        } catch (Exception $e) {
            // Rollback transaction on error
            $connection->rollback();
            $response['message'] = $e->getMessage();
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