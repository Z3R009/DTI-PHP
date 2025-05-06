<?php
include '../../DBConnection.php';

// Set content type to JSON
header('Content-Type: application/json');

// Check if batch_id is provided
if (!isset($_GET['batch_id']) || empty($_GET['batch_id'])) {
    echo json_encode([
        'success' => false,
        'message' => 'Missing batch ID parameter'
    ]);
    exit;
}

$batch_id = intval($_GET['batch_id']);

try {
    // Check if batch exists
    $batch_check = "SELECT COUNT(*) as count FROM batch_ada WHERE batch_id = ?";
    $batch_stmt = $connection->prepare($batch_check);
    $batch_stmt->bind_param("i", $batch_id);
    $batch_stmt->execute();
    $batch_check_result = $batch_stmt->get_result();
    $batch_exists = $batch_check_result->fetch_assoc()['count'] > 0;
    
    // Check if batch has vouchers
    $dvs_check = "SELECT COUNT(*) as count FROM batch_ada_dvs WHERE batch_id = ?";
    $dvs_stmt = $connection->prepare($dvs_check);
    $dvs_stmt->bind_param("i", $batch_id);
    $dvs_stmt->execute();
    $dvs_check_result = $dvs_stmt->get_result();
    $has_dvs = $dvs_check_result->fetch_assoc()['count'] > 0;
    
    // Check if tables exist
    $table_check = "SHOW TABLES LIKE 'batch_ada_dvs'";
    $table_result = $connection->query($table_check);
    $has_batch_ada_dvs = $table_result->num_rows > 0;
    
    // Check batch_ada_dvs columns
    $columns_query = "SHOW COLUMNS FROM batch_ada_dvs";
    $columns_result = $connection->query($columns_query);
    $columns = [];
    if ($columns_result) {
        while ($col = $columns_result->fetch_assoc()) {
            $columns[] = $col['Field'];
        }
    }

    // Get batch ADA header information
    $query = "SELECT ba.*, an.account_name, an.account_number, an.account_id,
             dp.balances AS current_balance, dp.draft_id,
             DATE_FORMAT(ba.payment_date, '%M %d, %Y') AS payment_date_formatted
             FROM batch_ada ba
             LEFT JOIN batch_ada_dvs bad ON bad.batch_id = ba.batch_id
             LEFT JOIN dv ON dv.dv_id = bad.dv_id
             LEFT JOIN account_name an ON an.account_id = dv.account_id
             LEFT JOIN draft_project dp ON dp.account_id = an.account_id
                AND dp.draft_id = (
                    SELECT MAX(dp2.draft_id) FROM draft_project dp2 
                    WHERE dp2.account_id = an.account_id
                )
             WHERE ba.batch_id = ?
             GROUP BY ba.batch_id";
    
    $stmt = $connection->prepare($query);
    $stmt->bind_param("i", $batch_id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows === 0) {
        echo json_encode([
            'success' => false,
            'message' => 'ADA payment not found'
        ]);
        exit;
    }
    
    $ada = $result->fetch_assoc();
    
    // Get vouchers included in this ADA payment - simplified query
    if ($has_dvs) {
        // First get just the batch_ada_dvs records
        $simple_query = "SELECT * FROM batch_ada_dvs WHERE batch_id = ?";
        $stmt = $connection->prepare($simple_query);
        $stmt->bind_param("i", $batch_id);
        $stmt->execute();
        $basic_result = $stmt->get_result();
        
        $vouchers = [];
        while ($voucher = $basic_result->fetch_assoc()) {
            // For each DV, get additional info
            $dv_id = $voucher['dv_id'];
            $dv_query = "SELECT 
                            d.dv_no, 
                            p.payee_name, 
                            o.purpose,
                            IF(mpi.item_id IS NOT NULL, 1, 0) AS is_merged,
                            mp.merge_id, 
                            mp.merge_name
                         FROM dv d
                         LEFT JOIN ors o ON d.ors_id = o.ors_id
                         LEFT JOIN payee p ON o.payee_id = p.payee_id
                         LEFT JOIN merged_payee_items mpi ON mpi.dv_id = d.dv_id
                         LEFT JOIN merged_payees mp ON mp.merge_id = mpi.merge_id
                         WHERE d.dv_id = ?";
            
            $dv_stmt = $connection->prepare($dv_query);
            $dv_stmt->bind_param("i", $dv_id);
            $dv_stmt->execute();
            $dv_result = $dv_stmt->get_result();
            
            // If we have DV details, merge them with the batch_ada_dvs data
            if ($dv_result && $dv_result->num_rows > 0) {
                $dv_details = $dv_result->fetch_assoc();
                $vouchers[] = array_merge($voucher, $dv_details);
            } else {
                // Still include the basic voucher data without DV details
                $voucher['dv_no'] = "DV #" . $dv_id;
                $voucher['payee_name'] = "Unknown";
                $voucher['purpose'] = "Not Available";
                $voucher['is_merged'] = 0;
                $vouchers[] = $voucher;
            }
        }
        
        $voucher_count = count($vouchers);
    } else {
        $vouchers = [];
        $voucher_count = 0;
    }
    
    // Add debug information
    $ada['debug'] = [
        'voucher_count' => $voucher_count,
        'voucher_query' => isset($simple_query) ? $simple_query : "No query executed - no DVs found",
        'batch_id' => $batch_id,
        'has_batch_ada_dvs_table' => $has_batch_ada_dvs,
        'batch_ada_dvs_columns' => $columns,
        'batch_exists' => $batch_exists,
        'has_dvs' => $has_dvs
    ];
    
    // Add vouchers to the response
    $ada['vouchers'] = $vouchers;
    
    // Calculate previous balance
    $ada['previous_balance'] = $ada['current_balance'] + $ada['total_net'];
    
    // Return success with data
    echo json_encode([
        'success' => true,
        'data' => $ada
    ]);
    
} catch (Exception $e) {
    // Log the error
    error_log('Error in get_ada_details.php: ' . $e->getMessage());
    
    // Return error response
    echo json_encode([
        'success' => false,
        'message' => 'An error occurred while retrieving ADA details',
        'error' => $e->getMessage()
    ]);
}
?> 