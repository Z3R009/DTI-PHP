<?php
// Include database connection
require_once '../../includes/db_connection.php';
require_once '../../includes/functions.php';

// Set headers for JSON response
header('Content-Type: application/json');

// Process only POST requests with JSON data
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Get JSON data
    $json_data = file_get_contents('php://input');
    $data = json_decode($json_data, true);
    
    // Check for required data
    if (!$data || !isset($data['account_id']) || !isset($data['payment_date'])) {
        echo json_encode([
            'success' => false,
            'message' => 'Missing required parameters'
        ]);
        exit;
    }
    
    // Begin transaction
    $conn->begin_transaction();
    
    try {
        // Extract data
        $account_id = $data['account_id'];
        $fund_code = $data['fund_code'] ?? '';
        $bank_info = $data['bank_info'] ?? '';
        $payment_date = $data['payment_date'];
        $remarks = $data['remarks'] ?? '';
        $dvs = $data['dvs'] ?? [];
        $merged_groups = $data['merged_groups'] ?? [];
        
        // Validate at least one payment item is selected
        if (empty($dvs) && empty($merged_groups)) {
            throw new Exception('No payment items selected');
        }
        
        // Create batch payment record
        $batch_query = "INSERT INTO batch_payments 
                       (account_id, fund_code, bank_info, payment_date, remarks, created_by, created_at) 
                       VALUES (?, ?, ?, ?, ?, ?, NOW())";
        
        $stmt = $conn->prepare($batch_query);
        $user_id = $_SESSION['user_id'] ?? 0; // Get current user ID from session
        $stmt->bind_param('issssi', $account_id, $fund_code, $bank_info, $payment_date, $remarks, $user_id);
        $stmt->execute();
        
        $batch_id = $conn->insert_id;
        
        // Process individual DV payments
        if (!empty($dvs)) {
            foreach ($dvs as $dv) {
                if (!isset($dv['id']) || empty($dv['id'])) {
                    continue;
                }
                
                $dv_id = $dv['id'];
                $reference = $dv['reference'] ?? '';
                
                // Insert payment record
                $dv_query = "INSERT INTO dv_payments 
                            (dv_id, batch_id, reference_no, created_by, created_at) 
                            VALUES (?, ?, ?, ?, NOW())";
                            
                $stmt = $conn->prepare($dv_query);
                $stmt->bind_param('iisi', $dv_id, $batch_id, $reference, $user_id);
                $stmt->execute();
                
                // Update DV status
                $update_query = "UPDATE disbursement_vouchers SET 
                                status = 'Paid', 
                                payment_date = ?, 
                                payment_reference = ?,
                                updated_by = ?,
                                updated_at = NOW()
                                WHERE id = ?";
                                
                $stmt = $conn->prepare($update_query);
                $stmt->bind_param('ssii', $payment_date, $reference, $user_id, $dv_id);
                $stmt->execute();
            }
        }
        
        // Process merged group payments
        if (!empty($merged_groups)) {
            foreach ($merged_groups as $group) {
                if (!isset($group['id']) || empty($group['id'])) {
                    continue;
                }
                
                $merge_id = $group['id'];
                $reference = $group['reference'] ?? '';
                
                // Insert merged payment record
                $merge_query = "INSERT INTO merged_payments 
                              (merge_id, batch_id, reference_no, created_by, created_at) 
                              VALUES (?, ?, ?, ?, NOW())";
                              
                $stmt = $conn->prepare($merge_query);
                $stmt->bind_param('iisi', $merge_id, $batch_id, $reference, $user_id);
                $stmt->execute();
                
                // Update merged group status
                $update_merge_query = "UPDATE merged_payees SET 
                                     status = 'Paid', 
                                     payment_date = ?, 
                                     payment_reference = ?,
                                     updated_by = ?,
                                     updated_at = NOW()
                                     WHERE id = ?";
                                     
                $stmt = $conn->prepare($update_merge_query);
                $stmt->bind_param('ssii', $payment_date, $reference, $user_id, $merge_id);
                $stmt->execute();
                
                // Update all DVs in the merged group
                $update_merged_dvs_query = "UPDATE disbursement_vouchers dv
                                          JOIN merged_dv_items mdi ON dv.id = mdi.dv_id
                                          SET dv.status = 'Paid', 
                                              dv.payment_date = ?, 
                                              dv.payment_reference = ?,
                                              dv.updated_by = ?,
                                              dv.updated_at = NOW()
                                          WHERE mdi.merge_id = ?";
                                          
                $stmt = $conn->prepare($update_merged_dvs_query);
                $stmt->bind_param('ssii', $payment_date, $reference, $user_id, $merge_id);
                $stmt->execute();
            }
        }
        
        // Commit transaction
        $conn->commit();
        
        // Return success response
        echo json_encode([
            'success' => true,
            'message' => 'Batch payment processed successfully',
            'batch_id' => $batch_id
        ]);
        
    } catch (Exception $e) {
        // Rollback transaction on error
        $conn->rollback();
        
        // Log error
        error_log('Batch payment error: ' . $e->getMessage());
        
        // Return error response
        echo json_encode([
            'success' => false,
            'message' => 'Error processing batch payment: ' . $e->getMessage()
        ]);
    }
    
} else {
    // Return error for non-POST requests
    echo json_encode([
        'success' => false,
        'message' => 'Invalid request method'
    ]);
}
?> 