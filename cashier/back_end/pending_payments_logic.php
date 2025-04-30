<?php
/**
 * Pending Payments Backend Logic
 * 
 * This file contains all the backend logic for the pending_payments.php page,
 * including loading pending vouchers, handling success/error messages,
 * and managing merged payees.
 */

// Include the main DB connection file
include_once __DIR__ . '/../../DBConnection.php';
include_once __DIR__ . '/db_connection.php';

// Check connection
$conn_error = null;
if (!isset($connection) || !$connection) {
    $conn_error = "Database connection is not available. Please check the DBConnection.php file.";
    error_log($conn_error);
}

// Handle success messages
$success_message = '';
if(isset($_GET['success']) && $_GET['success'] == '1') {
    $success_message = 'Payment has been recorded successfully!';
} elseif(isset($_GET['success']) && $_GET['success'] == '2') {
    $success_message = 'DV has been returned to Chief Accountant successfully!';
} elseif(isset($_GET['success']) && $_GET['success'] == '3') {
    $success_message = 'Batch ADA payment has been recorded successfully!';
    $lddap_ref = isset($_GET['lddap_ref']) ? $_GET['lddap_ref'] : '';
    $lddap_data = isset($_GET['lddap_data']) ? $_GET['lddap_data'] : '';
    $storage_key = isset($_GET['storage_key']) ? $_GET['storage_key'] : '';
} elseif(isset($_GET['success']) && $_GET['success'] == '4') {
    $success_message = 'Payees have been merged successfully!';
    $merge_id = isset($_GET['merge_id']) ? $_GET['merge_id'] : '';
} elseif(isset($_GET['success']) && $_GET['success'] == '5') {
    $success_message = 'Merged payee group has been deleted successfully!';
} elseif(isset($_GET['success']) && $_GET['success'] == '6') {
    $payment_count = isset($_GET['payment_count']) ? $_GET['payment_count'] : '';
    $total = isset($_GET['total']) ? $_GET['total'] : '';
    $success_message = "Merged payment has been processed successfully! $payment_count vouchers paid totaling ₱$total.";
} elseif(isset($_GET['success']) && $_GET['success'] == '7') {
    $success_message = 'Cash Advance payment has been successfully processed.';
}

// Handle error messages
$error_message = '';
if(isset($_GET['error']) && !empty($_GET['error'])) {
    $error_message = urldecode($_GET['error']);
}

// Get pending vouchers
require_once __DIR__ . '/get_pending_vouchers.php';
$pending_result = getPendingVouchers();

// Get merged payees
$display_merged_payees_error = false;
try {
    if (file_exists(__DIR__ . '/get_merged_payees.php')) {
        require_once __DIR__ . '/get_merged_payees.php';
        if (function_exists('getMergedPayees')) {
            $merged_payees = getMergedPayees();
            if (!is_array($merged_payees)) {
                $merged_payees = [];
                $display_merged_payees_error = true;
            }
        } else {
            $merged_payees = [];
            $display_merged_payees_error = true;
            error_log("getMergedPayees function does not exist");
        }
    } else {
        $merged_payees = [];
        $display_merged_payees_error = true;
        error_log("get_merged_payees.php file not found");
    }
} catch (Exception $e) {
    error_log("Error getting merged payees: " . $e->getMessage());
    $merged_payees = [];
    $display_merged_payees_error = true;
}

$merged_payees_error_message = "Database Connection Error: Could not connect to the database. The merged payees feature may not work properly.";

// Handle POST requests
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (isset($_POST['return_to_chief'])) {
        include __DIR__ . '/return_to_chief.php';
    } elseif (isset($_POST['submit_batch_ada'])) {
        include __DIR__ . '/batch_ada_payment.php';
    }
}

// Prepare data for the view
$pending_count = $pending_result ? mysqli_num_rows($pending_result) : 0;

// Create an array of DV IDs that are already part of merged groups
$merged_dv_ids = array();
if (!empty($merged_payees)) {
    foreach ($merged_payees as $group) {
        if (!empty($group['dvs'])) {
            foreach ($group['dvs'] as $dv) {
                if (isset($dv['dv_id'])) {
                    $merged_dv_ids[] = $dv['dv_id'];
                }
            }
        }
    }
}

// Reset the result pointer
if ($pending_result && mysqli_num_rows($pending_result) > 0) {
    mysqli_data_seek($pending_result, 0);
} 