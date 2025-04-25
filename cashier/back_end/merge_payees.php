<?php

require_once 'db_connection.php';

require_once 'get_merged_payees.php';
if (function_exists('ensureMergedPayeesTables')) {
    if (!ensureMergedPayeesTables()) {
        $error_message = "Error: Could not ensure database tables exist. Please check your database connection.";
        header("Location: ../pending_payments.php?error=" . urlencode($error_message));
        exit();
    }
}

$success = false;
$message = '';
$redirect_url = '../pending_payments.php';

function sanitize_input($data) {
    $data = trim($data);
    $data = stripslashes($data);
    $data = htmlspecialchars($data);
    return $data;
}

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['merge_payees'])) {
    $merge_name = isset($_POST['merge_name']) ? sanitize_input($_POST['merge_name']) : '';
    $description = isset($_POST['merge_description']) ? sanitize_input($_POST['merge_description']) : '';
    $payee_type = isset($_POST['payee_type']) ? sanitize_input($_POST['payee_type']) : 'Internal';
    $selected_dvs = isset($_POST['selected_dvs']) ? $_POST['selected_dvs'] : [];
    $created_by = isset($_SESSION['username']) ? $_SESSION['username'] : 'Cashier';
    if (empty($merge_name)) {
        $message = "Error: Merged payee name is required.";
    } elseif (empty($selected_dvs) || !is_array($selected_dvs) || count($selected_dvs) < 2) {
        $message = "Error: You must select at least two vouchers to merge.";
    } else {
        mysqli_begin_transaction($connection);
        
        try {
            $insert_merge_sql = "INSERT INTO merged_payees (merge_name, description, payee_type, created_by) 
                                VALUES (?, ?, ?, ?)";
            $stmt = mysqli_prepare($connection, $insert_merge_sql);
            mysqli_stmt_bind_param($stmt, "ssss", $merge_name, $description, $payee_type, $created_by);
            
            if (mysqli_stmt_execute($stmt)) {
                $merge_id = mysqli_insert_id($connection);
                
                // Insert each selected DV into merged_payee_items
                $insert_items_sql = "INSERT INTO merged_payee_items (merge_id, dv_id) VALUES (?, ?)";
                $stmt = mysqli_prepare($connection, $insert_items_sql);
                
                foreach ($selected_dvs as $dv_id) {
                    mysqli_stmt_bind_param($stmt, "ii", $merge_id, $dv_id);
                    if (!mysqli_stmt_execute($stmt)) {
                        throw new Exception("Error associating DV #$dv_id with merged payee: " . mysqli_error($connection));
                    }
                }
                
                // Commit transaction
                mysqli_commit($connection);
                
                // Set success message
                $success = true;
                $message = "Merged payee '$merge_name' has been created successfully with " . count($selected_dvs) . " vouchers.";
                $redirect_url = "../pending_payments.php?success=4&merge_id=$merge_id";
            } else {
                throw new Exception("Error creating merged payee: " . mysqli_error($connection));
            }
        } catch (Exception $e) {
            // Roll back transaction on error
            mysqli_rollback($connection);
            $message = $e->getMessage();
        }
    }
    
    // Redirect with appropriate message
    if (!$success) {
        $redirect_url .= "?error=" . urlencode($message);
    }
    
    header("Location: $redirect_url");
    exit();
} elseif ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['delete_merge_id'])) {
    // Handle deletion of a merged payee group
    $merge_id = isset($_POST['delete_merge_id']) ? intval($_POST['delete_merge_id']) : 0;
    
    if ($merge_id > 0) {
        // Delete the merged payee (cascade will handle the items)
        $delete_sql = "DELETE FROM merged_payees WHERE merge_id = ?";
        $stmt = mysqli_prepare($connection, $delete_sql);
        mysqli_stmt_bind_param($stmt, "i", $merge_id);
        
        if (mysqli_stmt_execute($stmt)) {
            $success = true;
            $message = "Merged payee group has been deleted successfully.";
            $redirect_url = "../pending_payments.php?success=5";
        } else {
            $message = "Error deleting merged payee: " . mysqli_error($connection);
            $redirect_url = "../pending_payments.php?error=" . urlencode($message);
        }
    } else {
        $message = "Invalid merge ID provided.";
        $redirect_url = "../pending_payments.php?error=" . urlencode($message);
    }
    
    header("Location: $redirect_url");
    exit();
} else {
    // If accessed directly without form submission, redirect to pending payments
    header("Location: ../pending_payments.php");
    exit();
}
?> 