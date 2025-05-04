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
    // Get batch ADA header information
    $query = "SELECT ba.*, an.account_name, an.account_number, an.account_id,
             dp.balances AS current_balance, dp.draft_id,
             DATE_FORMAT(ba.payment_date, '%M %d, %Y') AS payment_date_formatted
             FROM batch_ada ba
             JOIN batch_ada_dvs bad ON bad.batch_id = ba.batch_id
             JOIN dv ON dv.dv_id = bad.dv_id
             JOIN account_name an ON an.account_id = dv.account_id
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
    
    // Get vouchers included in this ADA payment
    $vouchers_query = "SELECT bad.*, dv.dv_no, dv.payee_name, dv.purpose,
                      (dv.gross_amount - dv.withholding_tax) AS net_amount
                      FROM batch_ada_dvs bad
                      LEFT JOIN disbursement_voucher dv ON dv.dv_id = bad.dv_id
                      WHERE bad.batch_id = ?
                      ORDER BY dv.dv_no ASC";
    
    $stmt = $connection->prepare($vouchers_query);
    $stmt->bind_param("i", $batch_id);
    $stmt->execute();
    $vouchers_result = $stmt->get_result();
    
    $vouchers = [];
    while ($voucher = $vouchers_result->fetch_assoc()) {
        $vouchers[] = $voucher;
    }
    
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