<?php
// This script processes payments for merged payee groups
// It records payments for all DVs in a merged group at once

// Include database connection
require_once '../DBConnection.php';

// Check if merged payees tables exist and create them if needed
require_once 'get_merged_payees.php';
if (function_exists('ensureMergedPayeesTables')) {
    if (!ensureMergedPayeesTables()) {
        $error_message = "Error: Could not ensure database tables exist. Please check your database connection.";
        header("Location: ../pending_payments.php?error=" . urlencode($error_message));
        exit();
    }
}

// Initialize response variables
$success = false;
$message = '';
$redirect_url = '../pending_payments.php';

// Function to validate and sanitize input
function sanitize_input($data) {
    $data = trim($data);
    $data = stripslashes($data);
    $data = htmlspecialchars($data);
    return $data;
}

// Check if form was submitted
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Get form data
    $merge_id = isset($_POST['merge_id']) ? intval($_POST['merge_id']) : 0;
    $payment_type = isset($_POST['payment_type']) ? sanitize_input($_POST['payment_type']) : '';
    $reference_no = isset($_POST['reference_no']) ? sanitize_input($_POST['reference_no']) : '';
    $payment_date = isset($_POST['payment_date']) ? sanitize_input($_POST['payment_date']) : '';
    $amount = isset($_POST['amount']) ? floatval($_POST['amount']) : 0;
    $remarks = isset($_POST['remarks']) ? sanitize_input($_POST['remarks']) : '';
    $processed_by = isset($_SESSION['username']) ? $_SESSION['username'] : 'Cashier';
    
    // Validate input
    if ($merge_id <= 0) {
        $message = "Error: Invalid merged payee group.";
    } elseif (empty($payment_type)) {
        $message = "Error: Payment type is required.";
    } elseif (empty($reference_no)) {
        $message = "Error: Reference number is required.";
    } elseif (empty($payment_date)) {
        $message = "Error: Payment date is required.";
    } elseif ($amount <= 0) {
        $message = "Error: Valid amount is required.";
    } else {
        // Start transaction
        mysqli_begin_transaction($connection);
        
        try {
            // Get merged payee information
            $payee_sql = "SELECT merge_name FROM merged_payees WHERE merge_id = ?";
            $stmt = mysqli_prepare($connection, $payee_sql);
            mysqli_stmt_bind_param($stmt, "i", $merge_id);
            mysqli_stmt_execute($stmt);
            $payee_result = mysqli_stmt_get_result($stmt);
            
            if ($payee_row = mysqli_fetch_assoc($payee_result)) {
                $merge_name = $payee_row['merge_name'];
                
                // Get all DVs in this merged group
                $dvs_sql = "SELECT mpi.dv_id, dv.dv_no, dv.net_amount 
                           FROM merged_payee_items mpi
                           JOIN dv ON mpi.dv_id = dv.dv_id
                           WHERE mpi.merge_id = ?";
                $stmt = mysqli_prepare($connection, $dvs_sql);
                mysqli_stmt_bind_param($stmt, "i", $merge_id);
                mysqli_stmt_execute($stmt);
                $dvs_result = mysqli_stmt_get_result($stmt);
                
                // Check if there are DVs to process
                if (mysqli_num_rows($dvs_result) > 0) {
                    $dv_count = mysqli_num_rows($dvs_result);
                    $individual_amount = $amount / $dv_count;
                    
                    // Insert payment for each DV
                    $insert_sql = "INSERT INTO payment 
                                 (dv_id, payment_date, payment_type, reference_no, amount, remarks, created_by) 
                                 VALUES (?, ?, ?, ?, ?, ?, ?)";
                    $insert_stmt = mysqli_prepare($connection, $insert_sql);
                    
                    while ($dv = mysqli_fetch_assoc($dvs_result)) {
                        $dv_id = $dv['dv_id'];
                        $dv_amount = $individual_amount;
                        
                        mysqli_stmt_bind_param($insert_stmt, "isssdss", $dv_id, $payment_date, $payment_type, $reference_no, $dv_amount, $remarks, $processed_by);
                        if (!mysqli_stmt_execute($insert_stmt)) {
                            throw new Exception("Error recording payment for DV #" . $dv['dv_no'] . ": " . mysqli_error($connection));
                        }
                        
                        // Update DV status to processed
                        $update_dv_sql = "UPDATE dv SET status = 'Processing' WHERE dv_id = ?";
                        $update_stmt = mysqli_prepare($connection, $update_dv_sql);
                        mysqli_stmt_bind_param($update_stmt, "i", $dv_id);
                        mysqli_stmt_execute($update_stmt);
                    }
                    
                    // Commit transaction
                    mysqli_commit($connection);
                    
                    // Set success message
                    $success = true;
                    $message = "Payment for merged payee group '$merge_name' has been processed successfully.";
                    $redirect_url = "../pending_payments.php?success=6";
                } else {
                    throw new Exception("No disbursement vouchers found in this merged payee group.");
                }
            } else {
                throw new Exception("Merged payee group not found.");
            }
        } catch (Exception $e) {
            // Roll back transaction on error
            mysqli_rollback($connection);
            $message = $e->getMessage();
            $redirect_url .= "?error=" . urlencode($message);
        }
    }
    
    header("Location: $redirect_url");
    exit();
} else {
    // If accessed directly without form submission, redirect to pending payments
    header("Location: ../pending_payments.php");
    exit();
}
?> 