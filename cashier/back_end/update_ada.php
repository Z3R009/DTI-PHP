<?php
include '../../DBConnection.php';

// Check if the form was submitted
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['update_ada'])) {
    // Get form data
    $original_reference = $_POST['original_reference'];
    $new_reference_no = $_POST['reference_no'];
    $payment_date = $_POST['payment_date'];
    $remarks = $_POST['remarks'] ?? '';
    
    // Initialize response
    $response = [
        'success' => false,
        'message' => '',
        'redirect' => '../ada_records.php'
    ];
    
    // Start a transaction
    $connection->begin_transaction();
    
    try {
        // First, get the batch_id for the original reference
        $batch_query = "SELECT batch_id FROM batch_ada WHERE reference_no = ?";
        $batch_stmt = $connection->prepare($batch_query);
        $batch_stmt->bind_param("s", $original_reference);
        $batch_stmt->execute();
        $batch_result = $batch_stmt->get_result();
        
        if ($batch_result->num_rows == 0) {
            throw new Exception("ADA record not found for reference: " . $original_reference);
        }
        
        $batch_row = $batch_result->fetch_assoc();
        $batch_id = $batch_row['batch_id'];
        
        // Update the batch_ada table
        $update_batch_query = "UPDATE batch_ada 
                              SET reference_no = ?, payment_date = ?, remarks = ?, updated_at = CURRENT_TIMESTAMP 
                              WHERE batch_id = ?";
        
        $update_batch_stmt = $connection->prepare($update_batch_query);
        $update_batch_stmt->bind_param("sssi", $new_reference_no, $payment_date, $remarks, $batch_id);
        $update_batch_stmt->execute();
        
        // Update the batch_ada_dvs table with the new reference number
        $update_dvs_query = "UPDATE batch_ada_dvs 
                            SET reference_no = ? 
                            WHERE batch_id = ?";
        
        $update_dvs_stmt = $connection->prepare($update_dvs_query);
        $update_dvs_stmt->bind_param("si", $new_reference_no, $batch_id);
        $update_dvs_stmt->execute();
        
        // Update the payment table for all related payments
        // First get all the DVs associated with this batch
        $get_dvs_query = "SELECT dv_id FROM batch_ada_dvs WHERE batch_id = ?";
        $get_dvs_stmt = $connection->prepare($get_dvs_query);
        $get_dvs_stmt->bind_param("i", $batch_id);
        $get_dvs_stmt->execute();
        $get_dvs_result = $get_dvs_stmt->get_result();
        
        // Update payments for each DV in this batch
        while ($dv_row = $get_dvs_result->fetch_assoc()) {
            $update_payment_query = "UPDATE payment 
                                  SET reference_no = ?, payment_date = ?, remarks = ?, ada_no = ? 
                                  WHERE dv_id = ? AND payment_type = 'ADA'";
            
            $update_payment_stmt = $connection->prepare($update_payment_query);
            $update_payment_stmt->bind_param("ssssi", $new_reference_no, $payment_date, $remarks, $new_reference_no, $dv_row['dv_id']);
            $update_payment_stmt->execute();
        }
        
        // Commit the transaction
        $connection->commit();
        
        // Set success response
        $response['success'] = true;
        $response['message'] = "ADA record has been updated successfully!";
        $response['redirect'] = "../ada_records.php?update_success=1&reference=" . urlencode($new_reference_no);
        
    } catch (Exception $e) {
        // Rollback the transaction
        $connection->rollback();
        
        // Set error message
        $response['success'] = false;
        $response['message'] = "Error updating ADA record: " . $e->getMessage();
        $response['redirect'] = "../ada_records.php?error=" . urlencode($response['message']);
        
        // Log the error for debugging
        error_log("Error in update_ada.php: " . $e->getMessage());
    }
    
    // Redirect with appropriate message
    if ($response['success']) {
        header("Location: " . $response['redirect']);
    } else {
        header("Location: " . $response['redirect']);
    }
    exit();
} else {
    // If accessed directly without form submission
    header("Location: ../ada_records.php?error=Invalid request");
    exit();
}
?> 