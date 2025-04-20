<?php
// Get all endorsed DVs that don't have payments yet
require_once 'db_connection.php';

/**
 * Retrieves all disbursement vouchers pending for payment
 * 
 * @return mysqli_result The result object containing pending DV records
 */
function getPendingVouchers() {
    global $connection;
    
    // Query to get all endorsed DVs without payments
    $pending_query = "SELECT d.*, p.payee_name, o.ors_no, o.purpose, o.notes
                     FROM dv d
                     JOIN ors o ON d.ors_id = o.ors_id
                     JOIN payee p ON o.payee_id = p.payee_id
                     WHERE (d.status = 'Endorsed' OR d.chief_accountant IS NOT NULL)
                     AND d.dv_id NOT IN (SELECT dv_id FROM payment WHERE status != 'Rejected')
                     ORDER BY d.date DESC";

    $pending_result = mysqli_query($connection, $pending_query);
    
    if (!$pending_result) {
        die("Database query failed: " . mysqli_error($connection));
    }
    
    return $pending_result;
}

/**
 * Get pending vouchers as an array instead of mysqli_result object
 * 
 * @return array Array of pending DV records
 */
function getPendingVouchersArray() {
    $pending_result = getPendingVouchers();
    $result = [];
    
    if ($pending_result) {
        while ($row = mysqli_fetch_assoc($pending_result)) {
            $result[] = $row;
        }
        mysqli_data_seek($pending_result, 0); // Reset the pointer
    }
    
    return $result;
}

// If this file is called directly, return JSON data
if (basename($_SERVER['PHP_SELF']) == basename(__FILE__)) {
    // Return JSON data if requested via AJAX
    if (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest') {
        header('Content-Type: application/json');
        echo json_encode(getPendingVouchersArray());
        exit;
    }
} 