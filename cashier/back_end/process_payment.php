<?php
/**
 * process_payment.php
 * 
 * Handles processing standard payments (Cash and Check) for disbursement vouchers
 * This script is included by submit_payment_direct.php when a cash or check payment is submitted
 */

// Include database connection
include_once '../../DBConnection.php';

// Initialize response
$response = array(
    'status' => 'error',
    'message' => 'An unknown error occurred while processing the payment.'
);

// Check if the request contains the necessary data
if (isset($_POST['payment_type'])) {
    
    // Verify database connection
    if (!$connection) {
        $response['message'] = 'Database connection error. Please try again later.';
        error_log("Payment Processing: Database connection failed");
        header('Location: ../pending_payments.php?error=' . urlencode($response['message']));
        exit;
    }
    
    try {
        // Get form data
        $dv_id = isset($_POST['dv_id']) ? intval($_POST['dv_id']) : 0;
        $merge_id = isset($_POST['merge_id']) ? intval($_POST['merge_id']) : 0;
        $payment_type = isset($_POST['payment_type']) ? mysqli_real_escape_string($connection, $_POST['payment_type']) : '';
        $reference_no = isset($_POST['reference_no']) ? mysqli_real_escape_string($connection, $_POST['reference_no']) : '';
        $payment_date = isset($_POST['payment_date']) ? mysqli_real_escape_string($connection, $_POST['payment_date']) : date('Y-m-d');
        $amount = isset($_POST['amount']) ? floatval($_POST['amount']) : 0;
        $remarks = isset($_POST['remarks']) ? mysqli_real_escape_string($connection, $_POST['remarks']) : '';
        
        // Validate essential data
        if (empty($payment_type)) {
            throw new Exception("Payment type is required.");
        }
        
        if (empty($reference_no)) {
            throw new Exception("Reference number is required.");
        }
        
        if ($amount <= 0) {
            throw new Exception("Amount must be greater than zero.");
        }
        
        // Get the logged-in user's ID for audit trail
        session_start();
        $user_id = isset($_SESSION['user_id']) ? $_SESSION['user_id'] : 0;
        
        // Start transaction
        mysqli_begin_transaction($connection);
        
        if ($dv_id > 0) {
            // Process individual DV payment
            
            // First, verify the DV exists and is pending payment
            $check_dv_query = "SELECT dv.*, p.payee_name 
                             FROM disbursement_voucher dv 
                             LEFT JOIN payee p ON dv.payee_id = p.payee_id
                             WHERE dv.dv_id = $dv_id AND dv.status = 'For Payment'";
            $check_dv_result = mysqli_query($connection, $check_dv_query);
            
            if (!$check_dv_result || mysqli_num_rows($check_dv_result) == 0) {
                throw new Exception("Disbursement voucher not found or is not pending payment.");
            }
            
            $dv_data = mysqli_fetch_assoc($check_dv_result);
            
            // Insert payment record
            $insert_query = "INSERT INTO payments (dv_id, reference_no, payment_date, payment_type, amount, remarks, created_by, created_at) 
                           VALUES ($dv_id, '$reference_no', '$payment_date', '$payment_type', $amount, '$remarks', $user_id, NOW())";
            
            if (!mysqli_query($connection, $insert_query)) {
                throw new Exception("Failed to record payment: " . mysqli_error($connection));
            }
            
            $payment_id = mysqli_insert_id($connection);
            
            // Update DV status
            $update_dv_query = "UPDATE disbursement_voucher SET status = 'Paid', payment_id = $payment_id, date_paid = '$payment_date', updated_at = NOW() WHERE dv_id = $dv_id";
            
            if (!mysqli_query($connection, $update_dv_query)) {
                throw new Exception("Failed to update DV status: " . mysqli_error($connection));
            }
            
            // Add audit trail entry
            $audit_action = "Processed " . $payment_type . " payment for DV #" . $dv_data['dv_no'] . ", Payee: " . $dv_data['payee_name'] . ", Amount: ₱" . number_format($amount, 2);
            $audit_query = "INSERT INTO audit_trail (user_id, action, timestamp, ip_address) 
                          VALUES ($user_id, '$audit_action', NOW(), '" . $_SERVER['REMOTE_ADDR'] . "')";
            
            mysqli_query($connection, $audit_query);
            
            // Commit the transaction
            mysqli_commit($connection);
            
            $response['status'] = 'success';
            $response['message'] = 'Payment processed successfully!';
            header('Location: ../pending_payments.php?success=1');
            exit;
            
        } elseif ($merge_id > 0) {
            // For merged payment groups, use the existing merge payment processor
            include 'process_merged_payment.php';
            exit;
        } else {
            // No valid ID provided
            throw new Exception("No valid DV or merged group ID provided.");
        }
        
    } catch (Exception $e) {
        // Rollback the transaction in case of error
        mysqli_rollback($connection);
        
        $response['message'] = 'Error: ' . $e->getMessage();
        error_log("Payment Processing Error: " . $e->getMessage());
        header('Location: ../pending_payments.php?error=' . urlencode($response['message']));
        exit;
    }
    
} else {
    // Missing payment type
    $response['message'] = 'Payment type is required.';
    header('Location: ../pending_payments.php?error=' . urlencode($response['message']));
    exit;
} 