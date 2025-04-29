<?php
/**
 * ada_payment.php
 * 
 * Handles processing ADA payments for individual disbursement vouchers
 * This script is included by submit_payment_direct.php when an ADA payment is submitted
 */

// Include database connection
include_once '../../DBConnection.php';

// Initialize response
$response = array(
    'status' => 'error',
    'message' => 'An unknown error occurred while processing the ADA payment.'
);

// Check if the request is a valid ADA payment
if (isset($_POST['payment_type']) && $_POST['payment_type'] === 'ADA') {
    
    // Verify database connection
    if (!$connection) {
        $response['message'] = 'Database connection error. Please try again later.';
        error_log("ADA Payment: Database connection failed");
        header('Location: ../pending_payments.php?error=' . urlencode($response['message']));
        exit;
    }
    
    try {
        // Get form data
        $dv_id = isset($_POST['dv_id']) ? intval($_POST['dv_id']) : 0;
        $merge_id = isset($_POST['merge_id']) ? intval($_POST['merge_id']) : 0;
        $reference_no = isset($_POST['reference_no']) ? mysqli_real_escape_string($connection, $_POST['reference_no']) : '';
        $payment_date = isset($_POST['payment_date']) ? mysqli_real_escape_string($connection, $_POST['payment_date']) : date('Y-m-d');
        $amount = isset($_POST['amount']) ? floatval($_POST['amount']) : 0;
        $remarks = isset($_POST['remarks']) ? mysqli_real_escape_string($connection, $_POST['remarks']) : '';
        $payment_type = 'ADA'; // Hardcoded since this is specifically for ADA payments
        
        // Validate essential data
        if (empty($reference_no)) {
            $response['message'] = 'Reference number is required for ADA payments.';
            header('Location: ../pending_payments.php?error=' . urlencode($response['message']));
            exit;
        }
        
        if ($amount <= 0) {
            $response['message'] = 'Amount must be greater than zero.';
            header('Location: ../pending_payments.php?error=' . urlencode($response['message']));
            exit;
        }
        
        // Get the logged-in user's ID for audit trail
        session_start();
        $user_id = isset($_SESSION['user_id']) ? $_SESSION['user_id'] : 0;
        
        // Start transaction
        mysqli_begin_transaction($connection);
        
        if ($dv_id > 0) {
            // Process individual DV ADA payment
            
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
            
            // Create ADA record
            $ada_query = "INSERT INTO ada_records (ada_reference, ada_date, remarks, created_by, created_at) 
                         VALUES ('$reference_no', '$payment_date', '$remarks', $user_id, NOW())";
                         
            if (!mysqli_query($connection, $ada_query)) {
                throw new Exception("Failed to create ADA record: " . mysqli_error($connection));
            }
            
            $ada_id = mysqli_insert_id($connection);
            
            // Insert payment record
            $insert_query = "INSERT INTO payments (dv_id, reference_no, payment_date, payment_type, amount, remarks, created_by, created_at, ada_id) 
                           VALUES ($dv_id, '$reference_no', '$payment_date', '$payment_type', $amount, '$remarks', $user_id, NOW(), $ada_id)";
            
            if (!mysqli_query($connection, $insert_query)) {
                throw new Exception("Failed to record payment: " . mysqli_error($connection));
            }
            
            $payment_id = mysqli_insert_id($connection);
            
            // Update DV status
            $update_dv_query = "UPDATE disbursement_voucher SET status = 'Paid', payment_id = $payment_id, date_paid = '$payment_date', updated_at = NOW() WHERE dv_id = $dv_id";
            
            if (!mysqli_query($connection, $update_dv_query)) {
                throw new Exception("Failed to update DV status: " . mysqli_error($connection));
            }
            
            // Add DVs to ADA
            $add_dv_to_ada_query = "INSERT INTO ada_dv_list (ada_id, dv_id, series) VALUES ($ada_id, $dv_id, '001')";
            
            if (!mysqli_query($connection, $add_dv_to_ada_query)) {
                throw new Exception("Failed to add DV to ADA: " . mysqli_error($connection));
            }
            
            // Add audit trail entry
            $audit_action = "Processed ADA payment for DV #" . $dv_data['dv_no'] . ", Payee: " . $dv_data['payee_name'] . ", Amount: ₱" . number_format($amount, 2);
            $audit_query = "INSERT INTO audit_trail (user_id, action, timestamp, ip_address) 
                          VALUES ($user_id, '$audit_action', NOW(), '" . $_SERVER['REMOTE_ADDR'] . "')";
            
            mysqli_query($connection, $audit_query);
            
            // Generate LDDAP-ADA data for JavaScript processing
            $lddap_data = array(
                'referenceNo' => $reference_no,
                'paymentDate' => $payment_date,
                'remarks' => $remarks,
                'dvs' => array(
                    array(
                        'dv_id' => $dv_id,
                        'dv_no' => $dv_data['dv_no'],
                        'payee_name' => $dv_data['payee_name'],
                        'series' => '001',
                        'gross_amount' => $dv_data['gross_amount'] ?? $amount,
                        'withholding_tax' => $dv_data['withholding_tax'] ?? 0,
                        'net_amount' => $dv_data['net_amount'] ?? $amount
                    )
                ),
                'mergedGroups' => array(),
                'totalGross' => $dv_data['gross_amount'] ?? $amount,
                'totalWithholding' => $dv_data['withholding_tax'] ?? 0,
                'totalNet' => $dv_data['net_amount'] ?? $amount
            );
            
            // Generate a unique storage key for localStorage
            $storage_key = 'lddap_' . time() . '_' . mt_rand(1000, 9999);
            
            // Encode the data for JavaScript
            $encoded_lddap_data = json_encode($lddap_data);
            
            // Commit the transaction
            mysqli_commit($connection);
            
            $response['status'] = 'success';
            $response['message'] = 'ADA payment processed successfully!';
            header('Location: ../pending_payments.php?success=3&lddap_ref=' . urlencode($reference_no) . '&lddap_data=' . urlencode($encoded_lddap_data) . '&storage_key=' . urlencode($storage_key));
            exit;
            
        } elseif ($merge_id > 0) {
            // For merged payment groups, redirect to the batch ADA processing
            header('Location: ../pending_payments.php?error=' . urlencode('Merged payee groups must use the Batch ADA payment option.'));
            exit;
        } else {
            // No valid ID provided
            throw new Exception("No valid DV ID provided.");
        }
        
    } catch (Exception $e) {
        // Rollback the transaction in case of error
        mysqli_rollback($connection);
        
        $response['message'] = 'Error: ' . $e->getMessage();
        error_log("ADA Payment Error: " . $e->getMessage());
        header('Location: ../pending_payments.php?error=' . urlencode($response['message']));
        exit;
    }
    
} else {
    // Not a valid ADA payment request
    $response['message'] = 'Invalid payment type.';
    header('Location: ../pending_payments.php?error=' . urlencode($response['message']));
    exit;
} 