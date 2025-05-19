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

// Check if bank_account column exists, if not add it
$check_column_query = "SHOW COLUMNS FROM merged_payees LIKE 'bank_account'";
$column_result = $connection->query($check_column_query);

if ($column_result->num_rows == 0) {
    // Add bank_account column
    $alter_table_query = "ALTER TABLE merged_payees ADD COLUMN bank_account VARCHAR(50) AFTER payee_type";
    $connection->query($alter_table_query);
}

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['merge_payees'])) {
    $merge_name = isset($_POST['merge_name']) ? sanitize_input($_POST['merge_name']) : '';
    $description = isset($_POST['merge_description']) ? sanitize_input($_POST['merge_description']) : '';
    $payee_type = isset($_POST['payee_type']) ? sanitize_input($_POST['payee_type']) : 'Internal';
    $selected_dvs = isset($_POST['selected_dvs']) ? $_POST['selected_dvs'] : [];
    $created_by = isset($_SESSION['username']) ? $_SESSION['username'] : 'Cashier';

    // Set bank account for DTI
    $bank_account = "2075-9006-81";

    if (empty($merge_name)) {
        $message = "Error: Merged payee name is required.";
    } elseif (empty($selected_dvs) || !is_array($selected_dvs) || count($selected_dvs) < 2) {
        $message = "Error: You must select at least two vouchers to merge.";
    } else {
        // Verify that the selected payee name exists in the payee table
        $check_payee_sql = "SELECT payee_id FROM payee WHERE payee_name = ?";
        $check_payee_stmt = $connection->prepare($check_payee_sql);
        $check_payee_stmt->bind_param("s", $merge_name);
        $check_payee_stmt->execute();
        $check_payee_result = $check_payee_stmt->get_result();
        
        if ($check_payee_result->num_rows === 0) {
            $message = "Error: Selected payee name does not exist in the system.";
        } else {
            // Verify that all selected DVs are for internal payees
            $internal_check_sql = "SELECT COUNT(*) as external_count 
                                 FROM dv d 
                                 JOIN ors o ON d.ors_id = o.ors_id 
                                 JOIN payee p ON o.payee_id = p.payee_id 
                                 WHERE d.dv_id IN (" . implode(',', array_map('intval', $selected_dvs)) . ") 
                                 AND p.payee_type != 'Internal'";
            $internal_check_result = $connection->query($internal_check_sql);
            $external_count = $internal_check_result->fetch_assoc()['external_count'];

            if ($external_count > 0) {
                $message = "Error: Only internal payees can be merged. Please remove external payees from your selection.";
            } else {
                // Proceed with merging
                $insert_sql = "INSERT INTO merged_payees (merge_name, description, payee_type, created_by, bank_account) VALUES (?, ?, ?, ?, ?)";
                $stmt = $connection->prepare($insert_sql);
                $stmt->bind_param("sssss", $merge_name, $description, $payee_type, $created_by, $bank_account);
                
                if ($stmt->execute()) {
                    $merge_id = $stmt->insert_id;
                    
                    // Insert each selected DV into merged_payee_items
                    $insert_items_sql = "INSERT INTO merged_payee_items (merge_id, dv_id) VALUES (?, ?)";
                    $stmt = $connection->prepare($insert_items_sql);
                    
                    foreach ($selected_dvs as $dv_id) {
                        $stmt->bind_param("ii", $merge_id, $dv_id);
                        if (!$stmt->execute()) {
                            throw new Exception("Error associating DV #$dv_id with merged payee: " . $stmt->error);
                        }
                    }
                    
                    // Commit transaction
                    $connection->commit();
                    
                    // Set success message
                    $success = true;
                    $message = "Merged payee '$merge_name' has been created successfully with " . count($selected_dvs) . " vouchers.";
                    $redirect_url = "../pending_payments.php?success=4&merge_id=$merge_id";
                } else {
                    throw new Exception("Error creating merged payee: " . $stmt->error);
                }
            }
        }
    }
    
    // Redirect with appropriate message
    if (!$success) {
        $redirect_url .= "?error=" . urlencode($message);
    }
    
    header("Location: $redirect_url");
    exit();
} elseif ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['delete_merge_id'])) {
    $merge_id = isset($_POST['delete_merge_id']) ? intval($_POST['delete_merge_id']) : 0;
    
    if ($merge_id > 0) {
        // Delete the merged payee (cascade will handle the items)
        $delete_sql = "DELETE FROM merged_payees WHERE merge_id = ?";
        $stmt = $connection->prepare($delete_sql);
        $stmt->bind_param("i", $merge_id);
        
        if ($stmt->execute()) {
            $success = true;
            $message = "Merged payee group has been deleted successfully.";
            $redirect_url = "../pending_payments.php?success=5";
        } else {
            $message = "Error deleting merged payee: " . $stmt->error;
            $redirect_url = "../pending_payments.php?error=" . urlencode($message);
        }
    } else {
        $message = "Invalid merge ID provided.";
        $redirect_url = "../pending_payments.php?error=" . urlencode($message);
    }
    
    header("Location: $redirect_url");
    exit();
} else {
    header("Location: ../pending_payments.php");
    exit();
}
?> 